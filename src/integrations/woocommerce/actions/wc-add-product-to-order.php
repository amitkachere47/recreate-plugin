<?php

namespace Crazy_Automator_Pro;

use Crazy_Automator\Recipe;

class WC_ADD_PRODUCT_TO_ORDER {
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
		$this->set_integration( 'WC' );
		$this->set_action_code( 'WC_PRODUCT_TO_ORDER' );
		$this->set_action_meta( 'WC_ORDER' );
		$this->set_requires_user( false );
		$this->set_is_pro( true );
		/* translators: Action - WooCommerce */
		$this->set_sentence( sprintf( esc_attr__( 'Add {{a product:%1$s}} to {{an order:%2$s}}', 'crazy-automator' ), 'WC_PRODUCTS', $this->get_action_meta() ) );
		/* translators: Action - WooCommerce */
		$this->set_readable_sentence( esc_attr__( 'Add {{a product}} to {{an order}}', 'crazy-automator' ) );
		$this->set_options_callback( array( $this, 'load_options' ) );
		$this->set_action_tokens( Crazy_Wc_Pro_Tokens::get_product_added_to_order_action_tokens(), $this->action_code );
		$this->register_action();
	}

	/**
	 * load_options
	 *
	 * @return array
	 */
	public function load_options() {

		$options = array(
			'options' => array(
				Crazy_Automator()->helpers->recipe->woocommerce->options->pro->all_wc_products( null, 'WC_PRODUCTS', false, false ),
				Crazy_Automator()->helpers->recipe->field->text(
					array(
						'option_code' => $this->get_action_meta(),
						'label'       => esc_attr_x( 'Order ID', 'WooCommerce', 'crazy-automator' ),
					)
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
		// Get product ID & order ID
		$product_id = isset( $parsed['WC_PRODUCTS'] ) ? sanitize_text_field( $parsed['WC_PRODUCTS'] ) : 0;
		$order_id   = isset( $parsed[ $this->get_action_meta() ] ) ? sanitize_text_field( $parsed[ $this->get_action_meta() ] ) : 0;
		$order      = wc_get_order( $order_id );

		// No order found
		if ( ! $order instanceof \WC_Order ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = sprintf( esc_html_x( 'Order #%d not found.', 'WooCommerce', 'crazy-automator' ), $order_id );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$product = wc_get_product( $product_id );

		//      $in_stock = $product->get_stock_status();
		//      if ( 'instock' !== $in_stock ) {
		//          $action_data['do-nothing']           = true;
		//          $action_data['complete_with_errors'] = true;
		//          $error_message                       = sprintf( esc_html_x( 'The product %s is out of stock.', 'WooCommerce', 'crazy-automator' ), $product->get_title() );
		//          Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

		//          return;
		//      }

		$product_status = $product->get_status();
		if ( 'publish' !== $product_status ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = sprintf( esc_html_x( 'The product %s is not published.', 'WooCommerce', 'crazy-automator' ), $product->get_title() );
			Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$order->add_product( $product );
		$this->hydrate_tokens( Crazy_Wc_Pro_Tokens::hydrate_product_added_to_order_tokens( $order, $product_id ) );
		Crazy_Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}
}
