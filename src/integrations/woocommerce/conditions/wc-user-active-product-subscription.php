<?php

namespace Crazy_Automator_Pro;

/**
 * Class WC_USER_ACTIVE_PRODUCT_SUBSCRIPTION
 *
 * @package Crazy_Automator_Pro
 */
class WC_USER_ACTIVE_PRODUCT_SUBSCRIPTION extends Action_Condition {

	/**
	 * Define_condition
	 *
	 * @return void
	 */
	public function define_condition() {
		$this->integration = 'WC';
		/*translators: Token */
		$this->name         = __( 'The user has an active subscription to {{a specific product}}', 'crazy-automator' );
		$this->code         = 'ACTIVE_PRODUCT_SUBSCRIPTION';
		$this->dynamic_name = sprintf(
			/* translators: A token matches a value */
			esc_html__( 'The user has an active subscription to {{a specific product:%1$s}}', 'crazy-automator' ),
			'MEMBERSHIP'
		);
		$this->requires_user = true;
		$this->active        = class_exists( 'WC_Subscriptions' );
	}

	/**
	 * fields
	 *
	 * @return array
	 */
	public function fields() {

		$products_field_args = Crazy_Automator()->helpers->recipe->woocommerce->pro->get_subscription_condition_field_args( 'MEMBERSHIP' );

		return array(
			$this->field->select_field_args( $products_field_args ),
		);
	}

	/**
	 * Evaluate_condition
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function evaluate_condition() {

		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			$message = __( 'WooCommerce Subscription plugin is not active.', 'crazy-automator' );
			$this->condition_failed( $message );

			return;
		}

		$product_id = $this->get_parsed_option( 'MEMBERSHIP' );
		$subscribed = Crazy_Automator()->helpers->recipe->woocommerce->pro->evaluate_subscription_product_condition( $product_id, $this->user_id );

		// Condition failed.
		if ( empty( $subscribed ) ) {
			// Generate message.
			if ( $product_id < 0 ) {
				// Check for Any Active subscriptions.
				$message = __( 'The user does not have an active subscription.', 'crazy-automator' );
			} else {
				// Check for specific subscription.
				$message = sprintf(
					/* translators: Readable Option name */
					__( 'The user does not have an active subscription to %s', 'crazy-automator' ),
					$this->get_option( 'MEMBERSHIP_readable' )
				);
			}
			$this->condition_failed( $message );
		}
	}
}
