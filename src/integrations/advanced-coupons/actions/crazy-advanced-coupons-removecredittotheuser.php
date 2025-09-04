<?php

namespace Crazy_Automator_Pro;

use API_Store_Credit_Entry;
use ACFWF\Models\Objects\Store_Credit_Entry;
use ACFWF\Helpers\Plugin_Constants;
use Crazy_Automator\Recipe;

/**
 * Class CRAZY_ADVANCED_COUPONS_REMOVECREDITTOTHEUSER
 *
 * @package Crazy_Automator_Pro
 */
class CRAZY_ADVANCED_COUPONS_REMOVECREDITTOTHEUSER {

	use Recipe\Action_Tokens;

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'ACFWC';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */

	public function __construct() {
		$this->action_code = 'ACFWCREMOVECREDITTOTHEUSER';
		$this->action_meta = 'REMOVECREDITTOTHEUSER';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Crazy_Automator()->get_author_name(),
			'support_link'       => Crazy_Automator()->get_author_support_link( $this->action_code, 'integration/advanced-coupons/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - MemberPress */
			'sentence'           => sprintf( __( "Remove {{a specific amount of:%1\$s}} store credit from the user's account", 'crazy-automator' ), $this->action_meta ),
			/* translators: Action - MemberPress */
			'select_option_name' => __( "Remove {{a specific amount of}} store credit from the user's account", 'crazy-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_credit' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		$this->set_action_tokens(
			array(
				'USERTOTALSTORECREDIT' => array(
					'name' => __( "User's total store credit", 'crazy-automator' ),
					'type' => 'text',
				),
			),
			$this->action_code
		);

		Crazy_Automator()->register->action( $action );
	}

	/**
	 * Load options
	 *
	 * @return array
	 */
	public function load_options() {
		return array(
			'options' => array(
				Crazy_Automator()->helpers->recipe->field->text(
					array(
						'option_code' => $this->action_meta,
						'label'       => __( 'Amount', 'crazy-automator' ),
						'token_name'  => __( 'Store credit amount', 'crazy-automator' ),
						'input_type'  => 'float',
						'tokens'      => true,
					)
				),
			),
		);
	}

	/**
	 * Action method to execute event when any action executed.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_credit( $user_id, $action_data, $recipe_id, $args ) {

		$amount = floatval( Crazy_Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args ) );

		if ( $amount <= 0 ) {
			return;
		}

		$balance = Crazy_Automator()->helpers->recipe->advanced_coupons->get_current_balance_of_the_customer( $user_id );

		if ( $balance < $amount ) {
			$error_message = __( "The user's store credit balance is insufficient.", 'crazy-automator' );
			Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$params = array(
			'user_id'   => $user_id,
			'type'      => 'decrease',
			'amount'    => $amount,
			'object_id' => $user_id,
			'action'    => 'admin_decrease',
			'date'      => gmdate( 'Y-m-d H:i:s' ),
			'note'      => 'Crazy Automator',
		);

		$date_format = isset( $params['date_format'] ) ? $params['date_format'] : Plugin_Constants::DB_DATE_FORMAT;

		// create store credit entry object.
		$store_credit_entry = new Store_Credit_Entry();

		foreach ( $params as $prop => $value ) {
			if ( $value && 'date' === $prop ) {
				$store_credit_entry->set_date_prop( $prop, $value, $date_format );
			} else {
				$store_credit_entry->set_prop( $prop, $value );
			}

			if ( 'action' === $prop && in_array(
				$value,
				array(
					'admin_increase',
					'admin_decrease',
				),
				true
			) ) {
				$store_credit_entry->set_prop( 'object_id', $user_id );
			}
		}
		$check = $store_credit_entry->save();

		if ( is_wp_error( $check ) ) {
			$error_message = __( 'The amount entered is not valid', 'crazy-automator' );
			Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$this->hydrate_tokens(
			array(
				'USERTOTALSTORECREDIT' => Crazy_Automator()->helpers->recipe->advanced_coupons->get_current_balance_of_the_customer( $user_id ),
			)
		);

		Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
