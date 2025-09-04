<?php

namespace Crazy_Automator_Pro;

use Crazy_Automator\Recipe;

/**
 * Class WC_SET_VARIATION_SUBSCRIPTION_STATUS
 *
 * @package Crazy_Automator_Pro
 */
class WC_SET_VARIATION_SUBSCRIPTION_STATUS {

	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->setup_action();
		}
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'WC' );
		$this->set_action_code( 'WCVARIATIONSUBSCRIPIONS' );
		$this->set_action_meta( 'WOOVARIATIONSUBS' );
		$this->set_requires_user( true );
		$this->set_is_pro( true );

		/* translators: Action - WooCommerce Subscription */
		$this->set_sentence( sprintf( esc_attr__( "Set the user's subscription to {{a specific:%1\$s}} variation of {{a variable subscription product:%2\$s}} to {{a status:%3\$s}}", 'crazy-automator' ), 'WOOVARIPRODUCT:' . $this->get_action_meta(), $this->get_action_meta(), 'WCS_STATUS' ) );

		/* translators: Action - WooCommerce Subscription */
		$this->set_readable_sentence( esc_attr__( "Set the user's subscription to {{a specific}} variation of {{a variable subscription product}} to {{a status}}", 'crazy-automator' ) );

		$this->set_options_callback( array( $this, 'load_options' ) );
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
					Crazy_Automator()->helpers->recipe->woocommerce->options->pro->all_wc_variation_subscriptions(
						esc_attr__( 'Variable subscription product', 'crazy-automator' ),
						$this->get_action_meta(),
						array(
							'token'        => false,
							'is_ajax'      => true,
							'is_any'       => false,
							'target_field' => 'WOOVARIPRODUCT',
							'endpoint'     => 'select_variations_from_WOOSELECTVARIATION',
						)
					),
					Crazy_Automator()->helpers->recipe->field->select_field_ajax( 'WOOVARIPRODUCT', esc_attr__( 'Variation', 'crazy-automator' ) ),
				),
			),
			'options'       => array(
				Crazy_Automator()->helpers->recipe->woocommerce->options->pro->wc_subscription_statuses( null, 'WCS_STATUS' ),
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
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$subscriptions = wcs_get_users_subscriptions( $user_id );

		if ( empty( $subscriptions ) ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = __( 'No subscription is associated with the user', 'crazy-automator' );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$variation_id                = Crazy_Automator()->parse->text( $action_data['meta']['WOOVARIPRODUCT'], $recipe_id, $user_id, $args );
		$set_status                  = Crazy_Automator()->parse->text( str_replace( 'wc-', '', $action_data['meta']['WCS_STATUS'] ), $recipe_id, $user_id, $args );
		$product_id                  = Crazy_Automator()->parse->text( $action_data['meta'][ $this->get_action_meta() ], $recipe_id, $user_id, $args );
		$subscription_status_updated = false;
		$error_message               = __( 'The user was not a subscriber of the specified product.', 'crazy-automator' );

		foreach ( $subscriptions as $subscription ) {
			$items = $subscription->get_items();
			foreach ( $items as $index => $item ) {
				if ( ( ( intval( '-1' ) ) === intval( $product_id ) || absint( $item->get_product_id() ) === absint( $product_id ) ) && ( ( intval( '-1' ) ) === intval( $variation_id ) || absint( $item->get_variation_id() ) === absint( $variation_id ) ) ) {
					if ( ! $subscription->has_status( array( $set_status ) ) && $subscription->can_be_updated_to( $set_status ) ) {
						$subscription->update_status( $set_status );
						$subscription_status_updated = true;
					} else {
						$error_message = __( 'We are not able to change subscription status.', 'crazy-automator' );
					}
				}
			}
		}

		if ( false === $subscription_status_updated ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$message                             = __( $error_message, 'crazy-automator' );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $message );

			return;
		}

		Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}

}
