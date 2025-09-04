<?php
namespace Crazy_Automator_Pro;

/**
 * Class CRAZY_AUTONAMI_REMOVE_CONTACT_FROM_LIST
 *
 * @package Crazy_Automator
 */
class CRAZY_AUTONAMI_REMOVE_CONTACT_FROM_LIST {

	use \Crazy_Automator\Recipe\Actions;

	public function __construct() {

		$this->helpers = new Crazy_Autonami_Pro_Helpers();
		$this->setup_action();
		$this->register_action();

	}

	/**
	 * Setup Action.
	 *
	 * @return void.
	 */
	protected function setup_action() {

		$this->set_integration( 'AUTONAMI' );
		$this->set_action_code( 'REMOVE_CONTACT_FROM_LIST' );
		$this->set_action_meta( 'LIST' );
		$this->set_is_pro( true );
		$this->set_support_link( $this->helpers->support_link( $this->action_code ) );

		/* translators: tag name */
		$this->set_sentence( sprintf( esc_attr__( 'Remove a contact from {{a list:%1$s}}', 'crazy-automator' ), $this->get_action_meta() ) );

		$this->set_readable_sentence( esc_attr__( 'Remove a contact from {{a list}}', 'crazy-automator' ) );

		$this->set_options_callback( array( $this, 'load_options' ) );

	}

	/**
	 * Method load_options
	 *
	 * @return void
	 */
	public function load_options() {

		$options[] = $this->helpers->get_list_dropdown( false );

		$options[] = $this->helpers->get_email_field();

		return array(
			'options_group' => array(
				$this->get_action_meta() => $options,
			),
		);

	}

	/**
	 * Method process_action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	public function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		try {

			$email_field_value = $action_data['meta']['EMAIL'];
			$parsed_email      = Crazy_Automator()->parse->text( $email_field_value, $recipe_id, $user_id, $args );
			$email             = sanitize_email( $parsed_email );

			$list_id       = absint( $action_data['meta'][ $this->action_meta ] );
			$list_readable = $action_data['meta'][ $this->action_meta . '_readable' ];

			$this->helpers->remove_contact_from_list( $email, $list_id, $list_readable );

			Crazy_Automator()->complete_action( $user_id, $action_data, $list_id );

		} catch ( \Exception $e ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id, $e->getMessage() );
		}
	}

}
