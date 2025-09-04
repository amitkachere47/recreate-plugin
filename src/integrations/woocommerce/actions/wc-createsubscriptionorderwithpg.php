<?php

namespace Crazy_Automator_Pro;

use WC_Subscriptions_Product;

/**
 * Class WC_CREATESUBSCRIPTIONORDERWITHPG
 *
 * @package Crazy_Automator_Pro
 */
class WC_CREATESUBSCRIPTIONORDERWITHPG {

	use \Crazy_Automator\Recipe\Action_Tokens;

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WC';

	/**
	 * Action code var.
	 *
	 * @var string
	 */
	private $action_code;

	/**
	 * Action meta var.
	 *
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->action_code = 'WCCREATEORDERFORSUBSCRIPTIONWITHPG';
			$this->action_meta = 'CREATEORDERFORSUBSCRIPTIONWITHPG';
			$this->define_action();
		}
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Crazy_Automator()->get_author_name( $this->action_code ),
			'support_link'       => Crazy_Automator()->get_author_support_link( $this->action_code, 'integration/woocommerce/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WooCommerce */
			'sentence'           => sprintf( __( 'Create a subscription order with {{a product:%1$s}} with a payment method', 'crazy-automator' ), $this->action_meta ),
			/* translators: Action - WooCommerce */
			'select_option_name' => __( 'Create a subscription order with {{a product}} with a payment method', 'crazy-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array(
				$this,
				'create_woocommerce_subscription_order_with_pg',
			),
			'options_callback'   => array( $this, 'load_options' ),
		);

		// @see Crazy_Wc_Pro_Tokens::get_order_action_tokens.
		// @see Crazy_Wc_Pro_Tokens::get_order_subscription_tokens.
		$this->set_action_tokens(
			array_merge(
				Crazy_Wc_Pro_Tokens::get_order_action_tokens(),
				Crazy_Wc_Pro_Tokens::get_order_subscription_tokens()
			),
			$this->action_code
		);

		Crazy_Automator()->register->action( $action );

	}

	/**
	 * Load options.
	 *
	 * @return array
	 */
	public function load_options() {

		$defaults = array(
			'option_code'     => 'WCDETAILS',
			'label'           => esc_attr__( 'Use same details for shipping details?', 'crazy-automator' ),
			'input_type'      => 'select',
			'supports_tokens' => false,
			'required'        => true,
			'default_value'   => 'YES',
			'options'         => array(
				'YES' => __( 'Yes', 'crazy-automator' ),
				'NO'  => __( 'No', 'crazy-automator' ),
			),
		);

		$options_array = array(
			'options_group' => array(
				$this->action_meta => Crazy_Automator()->helpers->recipe->woocommerce->pro->load_options_input( true, 'subscription' ),
			),
		);

		return Crazy_Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 *
	 * @throws \WC_Data_Exception
	 */
	public function create_woocommerce_subscription_order_with_pg( $user_id, $action_data, $recipe_id, $args ) {

		// First make sure all required functions and classes exist
		if ( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wcs_create_subscription' ) || ! class_exists( 'WC_Subscriptions' ) ) {
			$error_message                       = __( '`wc_create_order` or `wcs_create_subscription` function is missing.', 'crazy-automator' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$order = Crazy_Automator()->helpers->recipe->woocommerce->pro->wc_create_order( $user_id, $action_data, $recipe_id, $args, true, 'subscription' );

		if ( false === $order ) {
			$error_message                       = __( 'Action execution failed.', 'crazy-automator' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// @see Crazy_Wc_Pro_Tokens::hydrate_order_tokens.
		// @see Crazy_Wc_Pro_Tokens::hydrate_order_subscription_tokens.
		$this->hydrate_tokens(
			array_merge(
				Crazy_Wc_Pro_Tokens::hydrate_order_tokens( $order ),
				Crazy_Wc_Pro_Tokens::hydrate_order_subscription_tokens( $order )
			)
		);

		Crazy_Automator()->complete_action( $user_id, $action_data, $recipe_id );

	}

}
