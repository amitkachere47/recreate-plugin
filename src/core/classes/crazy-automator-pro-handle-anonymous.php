<?php


namespace Crazy_Automator_Pro;


/**
 * Class Crazy_Automator_Pro_Handle_Anonymous
 * @package Crazy_Automator_Pro
 */
class Crazy_Automator_Pro_Handle_Anonymous {

	/**
	 * Crazy_Automator_Pro_Handle_Anonymous constructor.
	 */
	public function __construct() {
		add_filter( 'crazy_automator_recipe_types', [ $this, 'cap_recipe_types_func' ], 10 );
		add_filter( 'cap_error_messages', [ $this, 'cap_error_messages_func' ], 10 );
	}

	/**
	 * @param $recipe_types
	 *
	 * @return array
	 */
	public function cap_recipe_types_func( $recipe_types ) {
		$recipe_types[] = 'anonymous';

		return $recipe_types;
	}

	/**
	 * @param $error_messages
	 *
	 * @return mixed
	 */
	public function cap_error_messages_func( $error_messages ) {

		$error_messages['anon-user-action-do-nothing'] = __( 'Anonymous recipe user action set to do nothing.', 'crazy-automator' );

		return $error_messages;
	}
}
