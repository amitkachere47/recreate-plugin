<?php

namespace Crazy_Automator_Pro;

use Crazy_Automator\Recipe;

/**
 * Class CRAZY_AFFWP_CREATEREFFORAFFILIATE_A
 *
 * @package Crazy_Automator_Pro
 */
class CRAZY_AFFWP_CREATEREFFORAFFILIATE_A {

	use Recipe\Action_Tokens;

	/**
	 * integration code
	 *
	 * @var string
	 */

	public static $integration = 'AFFWP';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'CREATEAREFERRAL';
		$this->action_meta = 'AFFILIATESREFERRAL';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Crazy_Automator()->get_author_name(),
			'support_link'       => Crazy_Automator()->get_author_support_link( $this->action_code, 'integration/affiliatewp/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'requires_user'      => false,
			/* translators: Actions - Affiliate WP */
			'sentence'           => sprintf( __( 'Create a {{referral:%1$s}} for {{a specific affiliate ID:%2$s}}', 'crazy-automator' ), 'CREATEAREFERRAL:' . $this->action_meta, $this->action_meta ),
			/* translators: Actions - Affiliate WP*/
			'select_option_name' => __( 'Create {{a referral}} for {{a specific affiliate ID}}', 'crazy-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'create_referral_for_affiliate' ),
			'options'            => array(),
			'options_callback'   => array( $this, 'load_options' ),
		);

		$this->set_action_tokens(
			array(
				'REFERRAL_ID'  => array(
					'name' => __( 'Referral ID', 'crazy-automator' ),
					'type' => 'int',
				),
				'REFERRAL_URL' => array(
					'name' => __( 'Referral URL', 'crazy-automator' ),
					'type' => 'url',
				),
			),
			$this->action_code
		);

		Crazy_Automator()->register->action( $action );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		return Crazy_Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					$this->action_meta => array(
						Crazy_Automator()->helpers->recipe->affiliate_wp->options->pro->get_affiliates( null, $this->action_meta ),
						Crazy_Automator()->helpers->recipe->field->text_field(
							'REFERRALAMOUNT',
							__( 'Amount', 'crazy-automator' ),
							true,
							'text',
							'',
							true
						),
						Crazy_Automator()->helpers->recipe->field->select_field(
							'REFERRALTYPE',
							__( 'Referral Type', 'crazy-automator' ),
							array(
								'sale'   => _x( 'Sale', 'AffiliateWP', 'crazy-automator' ),
								'opt-in' => _x( 'Opt-In', 'AffiliateWP', 'crazy-automator' ),
								'lead'   => _x( 'Lead', 'AffiliateWP', 'crazy-automator' ),
							),
							'sale',
							false
						),
						Crazy_Automator()->helpers->recipe->field->text_field(
							'REFERRALDESCRIPTION',
							__( 'Description', 'crazy-automator' ),
							true,
							'text',
							'',
							true
						),
						Crazy_Automator()->helpers->recipe->field->text_field(
							'REFERRALREFERENCE',
							__( 'Reference', 'crazy-automator' ),
							true,
							'text',
							'',
							false
						),
						Crazy_Automator()->helpers->recipe->field->text_field(
							'REFERRALCONTEXT',
							__( 'Context', 'crazy-automator' ),
							true,
							'text',
							'',
							false
						),
						Crazy_Automator()->helpers->recipe->field->select_field(
							'REFERRALSTATUS',
							__( 'Status', 'crazy-automator' ),
							array(
								'unpaid'   => _x( 'Unpaid', 'AffiliateWP', 'crazy-automator' ),
								'paid'     => _x( 'Paid', 'AffiliateWP', 'crazy-automator' ),
								'rejected' => _x( 'Rejected', 'AffiliateWP', 'crazy-automator' ),
								'pending'  => _x( 'Pending', 'AffiliateWP', 'crazy-automator' ),
							),
							'unpaid',
							false
						),
						Crazy_Automator()->helpers->recipe->field->text_field(
							'REFERRALCUSTOM',
							__( 'Custom', 'crazy-automator' ),
							true,
							'text',
							'',
							false,
							esc_html__( 'This action will only run if the user is already an affiliate. The referral date will be set to the date the action is run.', 'crazy-automator' )
						),
					),
				),
			)
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function create_referral_for_affiliate( $user_id, $action_data, $recipe_id, $args ) {
		$affiliate_id      = $action_data['meta'][ $this->action_meta ];
		$affiliate_user_id = affwp_get_affiliate_user_id( $affiliate_id );

		if ( false === $affiliate_user_id && false === affwp_is_affiliate( $affiliate_user_id ) ) {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Crazy_Automator()->complete->action(
				$user_id,
				$action_data,
				$recipe_id,
				__( 'The user is not an affiliate.', 'crazy-automator' )
			);

			return;

		}

		$referral['amount']       = Crazy_Automator()->parse->text( $action_data['meta']['REFERRALAMOUNT'], $recipe_id, $user_id, $args );
		$referral['custom']       = Crazy_Automator()->parse->text( $action_data['meta']['REFERRALCUSTOM'], $recipe_id, $user_id, $args );
		$referral['status']       = $action_data['meta']['REFERRALSTATUS'];
		$referral['context']      = Crazy_Automator()->parse->text( $action_data['meta']['REFERRALCONTEXT'], $recipe_id, $user_id, $args );
		$referral['reference']    = Crazy_Automator()->parse->text( $action_data['meta']['REFERRALREFERENCE'], $recipe_id, $user_id, $args );
		$referral['description']  = Crazy_Automator()->parse->text( $action_data['meta']['REFERRALDESCRIPTION'], $recipe_id, $user_id, $args );
		$referral['type']         = $action_data['meta']['REFERRALTYPE'];
		$referral['affiliate_id'] = $affiliate_id;
		$referral['user_id']      = $user_id;
		$user                     = get_user_by( 'id', $user_id );
		$referral['user_name']    = $user->user_login;
		$referral_id              = affwp_add_referral( $referral );

		if ( false === $referral_id ) {

			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Crazy_Automator()->complete->action(
				$user_id,
				$action_data,
				$recipe_id,
				__( 'We are unable to add referral.', 'crazy-automator' )
			);

			return;
		}
		$this->hydrate_tokens(
			array(
				'REFERRAL_ID'  => $referral_id,
				'REFERRAL_URL' => affwp_admin_url(
					'referrals',
					array(
						'action'      => 'edit_referral',
						'referral_id' => $referral_id,
					)
				),
			)
		);
		Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}

}
