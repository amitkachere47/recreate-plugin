<?php

namespace Crazy_Automator_Pro;

use Crazy_Automator\Recipe;

/**
 * Class CRAZY_AFFWP_CREATE_AFFILIATE
 *
 * @package Crazy_Automator_Pro
 */
class CRAZY_AFFWP_CREATE_AFFILIATE {

	use Recipe\Actions;
	use Recipe\Action_Tokens;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'AFFWP' );
		$this->set_action_code( 'CREATE_AFFILIATE_CODE' );
		$this->set_action_meta( 'CREATE_AFFILIATE_META' );
		$this->set_requires_user( false );
		$this->set_is_pro( true );

		/* translators: Action - Affiliate WP */
		$this->set_sentence( sprintf( esc_attr__( 'Create {{an affiliate:%1$s}}', 'crazy-automator' ), $this->get_action_meta() ) );

		/* translators: Action - Affiliate WP */
		$this->set_readable_sentence( esc_attr__( 'Create {{an affiliate}}', 'crazy-automator' ) );

		$this->set_options_callback( array( $this, 'load_options' ) );

		$this->set_action_tokens(
			array(
				'AFFILIATE_ID'  => array(
					'name' => __( 'Affiliate ID', 'crazy-automator' ),
					'type' => 'int',
				),
				'AFFILIATE_URL' => array(
					'name' => __( 'Affiliate URL', 'crazy-automator' ),
					'type' => 'url',
				),
			),
			$this->get_action_code()
		);

		$this->register_action();
	}

	/**
	 * load_options
	 *
	 * @return array
	 */
	public function load_options() {

		$options = array(
			'options_group' => array(
				$this->get_action_meta() => array(
					Crazy_Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'user_name',
							'label'       => esc_attr__( 'User login name', 'crazy-automator' ),
							'placeholder' => esc_attr__( 'User login name', 'crazy-automator' ),
						)
					),
					Crazy_Automator()->helpers->recipe->field->select(
						array(
							'option_code' => 'status',
							'label'       => esc_attr__( 'Status', 'crazy-automator' ),
							'options'     => affwp_get_affiliate_statuses(),
						)
					),
					Crazy_Automator()->helpers->recipe->field->select(
						array(
							'option_code' => 'rate_type',
							'label'       => esc_attr__( 'Referral rate type', 'crazy-automator' ),
							'options'     => affwp_get_affiliate_rate_types(),
						)
					),
					Crazy_Automator()->helpers->recipe->field->float(
						array(
							'option_code' => 'rate',
							'label'       => esc_attr__( 'Referral rate', 'crazy-automator' ),
							'placeholder' => esc_attr__( 'Rate (0.15)', 'crazy-automator' ),
						)
					),
					Crazy_Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'payment_email',
							'input_type'  => 'email',
							'label'       => esc_attr__( 'Payment email', 'crazy-automator' ),
							'placeholder' => esc_attr__( 'Payment email', 'crazy-automator' ),
							'required'    => false,
						)
					),
					Crazy_Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'notes',
							'label'       => esc_attr__( 'Affiliate notes', 'crazy-automator' ),
							'required'    => false,
						)
					),
					Crazy_Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'welcome_email',
							'label'       => esc_attr__( 'Send welcome email after creating an affiliate?', 'crazy-automator' ),
							'required'    => false,
							'input_type'  => 'checkbox',
							'is_toggle'   => true,
						)
					),
				),
			),
		);

		return Crazy_Automator()->utilities->keep_order_of_options( $options );
	}

	/**
	 * Process the action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 * @throws \Exception
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$affiliate                  = array();
		$affiliate['user_name']     = isset( $parsed['user_name'] ) ? sanitize_text_field( $parsed['user_name'] ) : '';
		$affiliate['status']        = isset( $parsed['status'] ) ? sanitize_text_field( $parsed['status'] ) : '';
		$affiliate['rate_type']     = isset( $parsed['rate_type'] ) ? sanitize_text_field( $parsed['rate_type'] ) : '';
		$affiliate['rate']          = isset( $parsed['rate'] ) ? sanitize_text_field( $parsed['rate'] ) : '';
		$affiliate['payment_email'] = isset( $parsed['payment_email'] ) ? sanitize_text_field( $parsed['payment_email'] ) : '';
		$affiliate['notes']         = isset( $parsed['notes'] ) ? sanitize_text_field( $parsed['notes'] ) : '';
		$affiliate['welcome_email'] = isset( $parsed['welcome_email'] ) ? sanitize_text_field( $parsed['welcome_email'] ) : '';
		$affiliate['welcome_email'] = ( 'true' === $affiliate['welcome_email'] ) ? true : false;

		$wp_user = get_user_by( 'login', $affiliate['user_name'] );

		if ( false === $wp_user ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$message                             = sprintf( __( 'The user (%s) does not exist on the site.', 'crazy-automator' ), $affiliate['user_name'] );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $message );

			return;
		}

		$is_affiliate = affwp_is_affiliate( $wp_user->data->ID );

		if ( false !== $is_affiliate ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$message                             = sprintf( __( 'The user is already an affiliate - %s.', 'crazy-automator' ), $affiliate['user_name'] );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $message );

			return;
		}

		$affiliate_id = affwp_add_affiliate( $affiliate );

		if ( false === $affiliate_id ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$message                             = sprintf( __( 'We are not able to create new affiliate, please retry later - %s.', 'crazy-automator' ), $affiliate['user_name'] );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $message );

			return;
		}

		$this->hydrate_tokens(
			array(
				'AFFILIATE_ID'  => $affiliate_id,
				'AFFILIATE_URL' => affwp_get_affiliate_referral_url( array( 'affiliate_id' => $affiliate_id ) ),
			)
		);

		Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}

}
