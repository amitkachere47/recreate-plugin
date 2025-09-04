<?php

namespace Crazy_Automator_Pro;

/**
 * Class CRAZY_ADVANCED_COUPONS_LIFETIME_CREDIT_EXCEEDS_LIMIT
 *
 * @package Crazy_Automator
 */
class CRAZY_ADVANCED_COUPONS_LIFETIME_CREDIT_EXCEEDS_LIMIT {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'ACFWC';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'ACFWCLIFETIMECREDITEXCEEDSLIMIT';
		$this->trigger_meta = 'ACFWCLIFETIMECREDITLIMIT';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Crazy_Automator()->get_author_name(),
			'support_link'        => Crazy_Automator()->get_author_support_link( $this->trigger_code, 'integration/advanced-coupons/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'is_pro'              => true,
			/* translators: Logged-in trigger - Advanced Coupons */
			'sentence'            => sprintf( esc_attr__( "A user's lifetime store credit exceeds {{a specific amount:%1\$s}}", 'crazy-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Advanced Coupons */
			'select_option_name'  => esc_attr__( "A user's lifetime store credit exceeds {{a specific amount}}", 'crazy-automator' ),
			'action'              => 'acfw_create_store_credit_entry',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'credit_check' ),
			'options_callback'    => array( $this, 'load_options' ),
		);
		Crazy_Automator()->register->trigger( $trigger );

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
						'option_code' => $this->trigger_meta,
						'label'       => __( 'Amount', 'crazy-automator' ),
						'token_name'  => __( 'Store credit received', 'crazy-automator' ),
						'input_type'  => 'float',
						'tokens'      => false,
					)
				),
			),
		);
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $data
	 */
	public function credit_check( $data ) {

		if ( isset( $data['type'] ) && 'increase' !== $data['type'] ) {
			return;
		}

		$user_id = ( isset( $data['user_id'] ) ) ? intval( $data['user_id'] ) : 0;

		if ( 0 === $user_id ) {
			// Its a logged in recipe and
			// user ID is 0. Skip process
			return;
		}

		$recipes            = Crazy_Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		$required_balance   = Crazy_Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();
		$order_id           = 0;
		$added_amount       = floatval( $data['amount'] );

		if ( isset( $data['action'] ) && isset( $data['object_id'] ) && intval( $data['object_id'] ) > 1 ) {
			$order_id = intval( $data['object_id'] );
		}
		$cur_balance = Crazy_Automator()->helpers->recipe->advanced_coupons->get_total_credits_of_the_user( $user_id );

		//Add where Point Type & Current Balances Matches
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];
				if ( $cur_balance > $required_balance[ $recipe_id ][ $trigger_id ] ) {
					$matched_recipe_ids[] = array(
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					);
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = array(
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $user_id,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				);

				$args = Crazy_Automator()->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							if ( isset( $result['args'] ) && isset( $result['args']['trigger_log_id'] ) ) {
								$trigger_meta = array(
									'user_id'        => $user_id,
									'trigger_id'     => (int) $result['args']['trigger_id'],
									'trigger_log_id' => $result['args']['trigger_log_id'],
									'run_number'     => $result['args']['run_number'],
								);

								$trigger_meta['meta_key']   = 'ACFWCLIFETIMECREDITLIMIT';
								$trigger_meta['meta_value'] = $added_amount;
								Crazy_Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'USERTOTALCREDIT';
								$trigger_meta['meta_value'] = Crazy_Automator()->helpers->recipe->advanced_coupons->get_current_balance_of_the_customer( $user_id );
								Crazy_Automator()->insert_trigger_meta( $trigger_meta );

								$trigger_meta['meta_key']   = 'USERLIFETIMECREDIT';
								$trigger_meta['meta_value'] = Crazy_Automator()->helpers->recipe->advanced_coupons->get_total_credits_of_the_user( $user_id );
								Crazy_Automator()->insert_trigger_meta( $trigger_meta );

							}
							Crazy_Automator()->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}
		}

	}

}
