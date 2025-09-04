<?php


namespace Crazy_Automator_Pro;

use Crazy_Automator\Woocommerce_Helpers;
use WC_Countries;
use WC_Product;
use WC_Subscriptions_Product;

/**
 * Class Crazy_Woocommerce_Pro_Helpers
 *
 * @package Crazy_Automator_Pro
 */
class Crazy_Woocommerce_Pro_Helpers extends Woocommerce_Helpers {

	public $shipping_method_details = array();

	/**
	 * Woocommerce_Pro_Helpers constructor.
	 */
	public function __construct( $load_action_hook = true ) {

		if ( true === $load_action_hook ) {
			add_action(
				'wp_ajax_select_variations_from_CRAZYWOOSELECTVARIATION',
				array(
					$this,
					'select_all_product_variations',
				)
			);

			add_action(
				'wp_ajax_select_all_terms_by_CRAZYSELECTED_TAXONOMY',
				array(
					$this,
					'select_all_terms_by_taxonomy',
				)
			);
			add_action(
				'wp_ajax_select_variations_from_CRAZYWOOSELECTVARIATION_with_any_option',
				array(
					$this,
					'select_all_product_variations_with_any',
				)
			);

			add_action(
				'wp_ajax_select_variations_CRAZYWOOSELECTVARIATION_FROM_with_any_option',
				array(
					$this,
					'select_all_product_variations_from_to_to',
				)
			);

			add_filter(
				'cap_option_woocommerce_statuses',
				array(
					$this,
					'add_any_status_option',
				),
				99,
				3
			);
			add_filter(
				'cap_option_all_wc_countries',
				array(
					$this,
					'add_empty_option_to_dd',
				)
			);

			add_filter(
				'cap_option_woocommerce_statuses',
				array(
					$this,
					'remove_option_id_from_dropdown',
				)
			);

			add_filter( 'cap_option_all_wc_variable_products', array( $this, 'remove_rel_tokens_from_dd' ) );
			add_filter( 'crazy_automator_option_select_field', array( $this, 'remove_rel_tokens_from_dd' ) );

		}
	}

	/**
	 * @param $label
	 * @param $option_code
	 *
	 * @return array|mixed|null
	 */
	public function all_wc_products( $label = null, $option_code = 'WOOPRODUCT', $is_any = true, $relevant_tokens = true ) {

		if ( ! $label ) {
			$label = esc_attr__( 'Product', 'crazy-automator' );
		}

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 99999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$options        = Crazy_Automator()->helpers->recipe->options->wp_query( $args, $is_any, esc_attr__( 'Any product', 'crazy-automator' ) );
		$default_tokens = array();
		if ( true === $relevant_tokens ) {
			$default_tokens = array(
				$option_code                => esc_attr__( 'Product title', 'crazy-automator' ),
				$option_code . '_ID'        => esc_attr__( 'Product ID', 'crazy-automator' ),
				$option_code . '_URL'       => esc_attr__( 'Product URL', 'crazy-automator' ),
				$option_code . '_THUMB_ID'  => esc_attr__( 'Product featured image ID', 'crazy-automator' ),
				$option_code . '_THUMB_URL' => esc_attr__( 'Product featured image URL', 'crazy-automator' ),
				$option_code . '_ORDER_QTY' => esc_attr__( 'Product quantity', 'crazy-automator' ),
			);
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => $default_tokens,
		);

		return apply_filters( 'cap_option_all_wc_products', $option );
	}

	/**
	 * @param Crazy_Woocommerce_Pro_Helpers $pro
	 */
	public function setPro( Crazy_Woocommerce_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Add empty option to WC countries dropdown.
	 *
	 * @param $options
	 *
	 * @return void
	 */
	public function add_empty_option_to_dd( $options ) {

		$option[] = esc_attr__( 'Select country/region', 'crazy-automator' );
		array_unshift( $options, $option );

		return $options;
	}

	/**
	 * @param $option
	 *
	 * @return mixed
	 */
	public function remove_rel_tokens_from_dd( $option ) {

		if ( isset( $option['option_code'] ) && ( 'ANON_WC_VARINVENTORYCHANGED_META' === $option['option_code'] || 'WC_WOOVARIPRODUCT' === $option['option_code'] ) ) {
			$option['relevant_tokens'] = array();
		}

		return $option;
	}

	/**
	 * Adding option to hide option id from dropdown.
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	public function remove_option_id_from_dropdown( $options ) {

		$options['options_show_id'] = false;

		return $options;
	}

	/**
	 * This method is used to list all input options for the action/triggers.
	 *
	 * @param $payment_method
	 *
	 * @return array
	 */
	public function load_options_input( $payment_method = false, $order_type = 'simple' ) {

		$defaults = array(
			'option_code'           => 'WCDETAILS',
			'label'                 => esc_attr__( 'Use the same info for shipping?', 'crazy-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => false,
			'required'              => false,
			'default_value'         => null,
			'placeholder'           => esc_attr__( 'Use the same info for shipping?', 'crazy-automator' ),
			'supports_custom_value' => false,
			'options_show_id'       => false,
			'options'               => array(
				'YES' => __( 'Yes', 'crazy-automator' ),
				'NO'  => __( 'No', 'crazy-automator' ),
			),
		);

		$bl_countries = array(
			'option_code'           => 'WCCOUNTRY',
			'label'                 => esc_attr__( 'Billing country/region', 'crazy-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => true,
			'required'              => false,
			'placeholder'           => esc_attr__( 'Billing country/region', 'crazy-automator' ),
			'supports_custom_value' => true,
			//'options_show_id'       => false,
			'options'               => $this->get_countries(),
		);

		$sp_countries = array(
			'option_code'           => 'WC_SHP_COUNTRY',
			'label'                 => esc_attr__( 'Shipping country/region', 'crazy-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => true,
			'required'              => false,
			'placeholder'           => esc_attr__( 'Shipping country/region', 'crazy-automator' ),
			'supports_custom_value' => true,
			//'options_show_id'       => false,
			'options'               => $this->get_countries(),
		);

		$shipping_methods = array(
			'option_code'           => 'WC_SHP_METHOD',
			'label'                 => esc_attr__( 'Shipping method', 'crazy-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => true,
			'supports_custom_value' => true,
			'required'              => false,
			//'options_show_id'       => false,
			'options'               => $this->get_shipping_methods(),
		);

		$coupons = array(
			'option_code'           => 'WC_COUPONS',
			'label'                 => esc_attr__( 'Coupon', 'crazy-automator' ),
			'input_type'            => 'select',
			'supports_tokens'       => true,
			'supports_custom_value' => true,
			'required'              => false,
			//'options_show_id'       => false,
			'options'               => $this->get_coupons(),
		);

		$options_array = array(
			array(
				'option_code'       => 'WC_PRODUCTS_FIELDS',
				'input_type'        => 'repeater',
				'label'             => __( 'Order items', 'crazy-automator' ),
				'required'          => true,
				'fields'            => array(
					array(
						'option_code' => 'WC_PRODUCT_ID',
						'label'       => __( 'Product', 'crazy-automator' ),
						'input_type'  => 'select',
						'required'    => true,
						'read_only'   => false,
						'options'     => $this->all_wc_products_list( $order_type ),
					),
					Crazy_Automator()->helpers->recipe->field->text(
						array(
							'option_code' => 'WC_PRODUCT_QTY',
							'label'       => __( 'Quantity', 'crazy-automator' ),
							'input_type'  => 'text',
							'tokens'      => true,
						)
					),
				),
				'add_row_button'    => __( 'Add product', 'crazy-automator' ),
				'remove_row_button' => __( 'Remove product', 'crazy-automator' ),
				'hide_actions'      => false,
			),
			$this->wc_order_statuses_pro( __( 'Order status', 'crazy-automator' ) ),
			Crazy_Automator()->helpers->recipe->woocommerce->pro->all_wc_payment_gateways( __( 'Payment gateway', 'crazy-automator' ), 'WOOPAYMENTGATEWAY', array(), false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCORDERNOTE', __( 'Order note', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->select( $coupons ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCFIRST_NAME', __( 'Billing first name', 'crazy-automator' ), true, 'text', '', true ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCLAST_NAME', __( 'Billing last name', 'crazy-automator' ), true, 'text', '', true ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCEMAIL', __( 'Billing email', 'crazy-automator' ), true, 'text', '', true, __( '* The order will be linked to the user that matches the Billing email entered above.', 'crazy-automator' ) ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCCOMPANYNAME', __( 'Billing company name', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCPHONE', __( 'Billing phone number', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCADDRESSONE', __( 'Billing address 1', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCADDRESSTWO', __( 'Billing address 2', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCPOSTALCODE', __( 'Billing zip/postal code', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCCITY', __( 'Billing city', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WCSTATE', __( 'Billing state/province', 'crazy-automator' ), true, 'text', '', false, __( 'Enter the two-letter state or province abbreviation.', 'crazy-automator' ) ),
			Crazy_Automator()->helpers->recipe->field->select( $bl_countries ),
			Crazy_Automator()->helpers->recipe->field->select( $defaults ),
			Crazy_Automator()->helpers->recipe->field->select( $shipping_methods ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_COST', __( 'Shipping cost', 'crazy-automator' ), true, 'text', '', false, __( 'Enter 0 for no shipping cost or leave it empty to use the default value set for the shipping method.', 'ucanny-automator-pro' ) ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_FIRST_NAME', __( 'Shipping first name', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_LAST_NAME', __( 'Shipping last name', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_EMAIL', __( 'Shipping email', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_COMPANYNAME', __( 'Shipping company name', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_PHONE', __( 'Shipping phone number', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_ADDRESSONE', __( 'Shipping address 1', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_ADDRESSTWO', __( 'Shipping address 2', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_POSTALCODE', __( 'Shipping zip/postal code', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_CITY', __( 'Shipping city', 'crazy-automator' ), true, 'text', '', false ),
			Crazy_Automator()->helpers->recipe->field->text_field( 'WC_SHP_STATE', __( 'Shipping state/province', 'crazy-automator' ), true, 'text', '', false, __( 'Enter the two-letter state or province abbreviation.', 'crazy-automator' ) ),
			Crazy_Automator()->helpers->recipe->field->select( $sp_countries ),
		);

		//      if ( 'subscription' === $order_type ) {
		//          unset( $options_array[1] );
		//      }

		if ( false === $payment_method ) {
			unset( $options_array[2] );
		}

		return Crazy_Automator()->utilities->keep_order_of_options( $options_array );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_wc_products_list( $product_type = 'simple' ) {

		if ( 'subscription' === $product_type ) {

			$subscriptions = wc_get_products(
				array(
					'type'  => array( 'subscription', 'variable-subscription' ),
					'limit' => 99999,
				)
			);
			$products_list = array();
			$temp_array    = array();

			foreach ( $subscriptions as $product ) {
				$temp_array['value'] = $product->get_id();
				$temp_array['text']  = $product->get_name();
				array_push( $products_list, $temp_array );
			}
		} else {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => 999999,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options       = Crazy_Automator()->helpers->recipe->options->wp_query( $args, false );
			$products_list = array();
			$temp_array    = array();
			if ( is_array( $options ) ) {
				foreach ( $options as $key => $value ) {
					$temp_array['value'] = $key;
					$temp_array['text']  = __( $value, 'crazy-automator' );
					array_push( $products_list, $temp_array );
				}
			}
		}

		return apply_filters( 'cap_option_all_wc_products_list', $products_list );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function all_wc_subscriptions( $label = null, $option_code = 'WOOSUBSCRIPTIONS', $is_any = true, $is_all = false, $relevant_tokens = true ) {

		if ( ! $label ) {
			$label = __( 'Subscription', 'crazy-automator' );
		}

		// Query all subscription products based on the assigned product_type category (new WC type) and post_type shop_"
		$subscriptions = wc_get_products(
			array(
				'type'  => array( 'subscription', 'variable-subscription' ),
				'limit' => 99999,
			)
		);

		$options = array();

		if ( true === $is_any && false === $is_all ) {
			$options['-1'] = __( 'Any subscription product', 'crazy-automator' );
		}
		if ( true === $is_all && false === $is_any ) {
			$options['-1'] = __( 'All subscription products', 'crazy-automator' );
		}
		if ( $subscriptions ) {
			foreach ( $subscriptions as $product ) {
				$title = $product->get_name();

				if ( empty( $title ) ) {
					/* translators: ID of the Product */
					$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $product->get_id() );
				}

				$options[ $product->get_id() ] = $title;
			}
		}
		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                            => __( 'Product title', 'crazy-automator' ),
				$option_code . '_ID'                    => __( 'Product ID', 'crazy-automator' ),
				$option_code . '_URL'                   => __( 'Product URL', 'crazy-automator' ),
				$option_code . '_THUMB_URL'             => __( 'Product featured image URL', 'crazy-automator' ),
				$option_code . '_THUMB_ID'              => __( 'Product featured image ID', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_ID'       => __( 'Subscription ID', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_STATUS'   => __( 'Subscription status', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_TRIAL_END_DATE' => __( 'Subscription trial end date', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_END_DATE' => __( 'Subscription end date', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_NEXT_PAYMENT_DATE' => __( 'Subscription next payment date', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_RENEWAL_COUNT' => __( 'Subscription renewal count', 'crazy-automator' ),
			),
		);

		if ( false === $relevant_tokens ) {
			$option['relevant_tokens'] = array();
		}

		return apply_filters( 'cap_option_all_wc_subscriptions', $option );
	}

	/**
	 * Get Subscription Condition Select Options.
	 *
	 * @return array
	 */
	public function get_subscription_condition_select_options() {

		// Get the cached options.
		static $condition_options = null;
		if ( ! is_null( $condition_options ) ) {
			return $condition_options;
		}
		$condition_options             = array();
		$all_woo_subscription_products = $this->all_wc_subscriptions();
		$options                       = isset( $all_woo_subscription_products['options'] ) ? $all_woo_subscription_products['options'] : array();
		if ( empty( $options ) ) {
			return $condition_options;
		}

		foreach ( $options as $id => $text ) {
			$condition_options[] = array(
				'value' => $id,
				'text'  => $text,
			);
		}

		return $condition_options;
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wc_variation_subscriptions( $label = null, $option_code = 'WOOSUBSCRIPTIONS', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Variable subscription', 'crazy-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$is_any       = key_exists( 'is_any', $args ) ? $args['is_any'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$options      = array();
		if ( true === $is_any ) {
			$options['-1'] = __( 'Any variable subscription', 'crazy-automator' );
		}

		$subscription_products = array();

		if ( function_exists( 'wc_get_products' ) ) {
			$subscription_products = wc_get_products(
				array(
					'type'    => array( 'variable-subscription' ),
					'limit'   => - 1,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);
		}

		foreach ( $subscription_products as $product ) {

			$title = $product->get_title();

			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $product->get_id() );
			}

			$options[ $product->get_id() ] = $title;

		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                            => __( 'Variation title', 'crazy-automator' ),
				$option_code . '_ID'                    => __( 'Variation ID', 'crazy-automator' ),
				$option_code . '_URL'                   => __( 'Variation URL', 'crazy-automator' ),
				$option_code . '_THUMB_URL'             => __( 'Variation featured image URL', 'crazy-automator' ),
				$option_code . '_THUMB_ID'              => __( 'Variation featured image ID', 'crazy-automator' ),
				$option_code . '_PRODUCT'               => __( 'Product title', 'crazy-automator' ),
				$option_code . '_PRODUCT_ID'            => __( 'Product ID', 'crazy-automator' ),
				$option_code . '_PRODUCT_URL'           => __( 'Product URL', 'crazy-automator' ),
				$option_code . '_PRODUCT_THUMB_URL'     => __( 'Product featured image URL', 'crazy-automator' ),
				$option_code . '_PRODUCT_THUMB_ID'      => __( 'Product featured image ID', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_ID'       => __( 'Subscription ID', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_STATUS'   => __( 'Subscription status', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_TRIAL_END_DATE' => __( 'Subscription trial end date', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_END_DATE' => __( 'Subscription end date', 'crazy-automator' ),
				$option_code . '_SUBSCRIPTION_NEXT_PAYMENT_DATE' => __( 'Subscription next payment date', 'crazy-automator' ),
			),
		);

		return apply_filters( 'cap_option_all_wc_variation_subscriptions', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function all_wc_variable_products( $label = null, $option_code = 'WOOVARIABLEPRODUCTS', $args = array() ) {

		if ( ! $label ) {
			$label = __( 'Product', 'crazy-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$variable_prods = wc_get_products(
			array(
				'type'  => array( 'variable' ),
				'limit' => 99999,
			)
		);
		$options        = array();
		$options['-1']  = __( 'Any product', 'crazy-automator' );
		if ( $variable_prods ) {
			foreach ( $variable_prods as $product ) {
				$title = $product->get_name();
				if ( empty( $title ) ) {
					$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $product->get_id() );
				}
				$options[ $product->get_id() ] = $title;
			}
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code                => __( 'Product title', 'crazy-automator' ),
				$option_code . '_ID'        => __( 'Product ID', 'crazy-automator' ),
				$option_code . '_URL'       => __( 'Product URL', 'crazy-automator' ),
				$option_code . '_THUMB_ID'  => __( 'Product featured image ID', 'crazy-automator' ),
				$option_code . '_THUMB_URL' => __( 'Product featured image URL', 'crazy-automator' ),
			),
		);

		return apply_filters( 'cap_option_all_wc_variable_products', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return mixed|void
	 */
	public function all_wc_product_categories( $label = null, $option_code = 'WOOPRODCAT', $args = array() ) {

		$supports_multiple_values = key_exists( 'supports_multiple_values', $args ) ? $args['supports_multiple_values'] : false;
		$description              = key_exists( 'description', $args ) ? $args['description'] : false;
		$required                 = key_exists( 'required', $args ) ? $args['required'] : true;
		if ( ! $label ) {
			$label = __( 'Categories', 'crazy-automator' );
		}

		global $wpdb;
		$query = "
			SELECT terms.term_id,terms.name FROM $wpdb->terms as terms
			LEFT JOIN $wpdb->term_taxonomy as rel ON (terms.term_id = rel.term_id)
			WHERE rel.taxonomy = 'product_cat'
			ORDER BY terms.name";

		$categories = $wpdb->get_results( $query );

		$options       = array();
		$options['-1'] = __( 'Any category', 'crazy-automator' );

		foreach ( $categories as $category ) {
			$title = $category->name;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $category->term_id );
			}
			$options[ $category->term_id ] = $title;
		}

		$option = array(
			'option_code'              => $option_code,
			'label'                    => $label,
			'description'              => $description,
			'input_type'               => 'select',
			'required'                 => $required,
			'options'                  => $options,
			'supports_multiple_values' => $supports_multiple_values,
			'relevant_tokens'          => array(
				$option_code          => __( 'Category title', 'crazy-automator' ),
				$option_code . '_ID'  => __( 'Category ID', 'crazy-automator' ),
				$option_code . '_URL' => __( 'Category URL', 'crazy-automator' ),
			),
		);

		return apply_filters( 'cap_option_all_wc_product_categories', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return array|mixed|void
	 */
	public function all_wc_payment_gateways( $label = null, $option_code = 'WOOPAYMENTGATEWAY', $args = array(), $is_any = true ) {

		$description = key_exists( 'description', $args ) ? $args['description'] : false;
		$required    = key_exists( 'required', $args ) ? $args['required'] : true;
		if ( ! $label ) {
			$label = __( 'Payment method', 'crazy-automator' );
		}

		$methods = WC()->payment_gateways->payment_gateways();

		$options = array();

		if ( $is_any === true ) {
			$options['-1'] = __( 'Any payment method', 'crazy-automator' );
		}

		foreach ( $methods as $method ) {
			if ( $method->enabled == 'yes' ) {
				$title = $method->title;
				if ( empty( $title ) ) {
					$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $method->id );
				}
				$options[ $method->id ] = $title;
			}
		}

		$option = array(
			'option_code'              => $option_code,
			'label'                    => $label,
			'description'              => $description,
			'input_type'               => 'select',
			'required'                 => $required,
			'options'                  => $options,
			'supports_multiple_values' => false,
			'options_show_id'          => false,
		);

		return apply_filters( 'cap_option_all_wc_payment_gateways', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 *
	 * @return mixed|void
	 */
	public function all_wc_product_tags( $label = null, $option_code = 'WOOPRODTAG' ) {

		if ( ! $label ) {
			$label = __( 'Categories', 'crazy-automator' );
		}

		global $wpdb;
		$query = "
			SELECT terms.term_id,terms.name FROM $wpdb->terms as terms
			LEFT JOIN $wpdb->term_taxonomy as rel ON (terms.term_id = rel.term_id)
			WHERE rel.taxonomy = 'product_tag'
			ORDER BY terms.name";

		$tags = $wpdb->get_results( $query );

		$options       = array();
		$options['-1'] = __( 'Any tag', 'crazy-automator' );

		foreach ( $tags as $tag ) {
			$title = $tag->name;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $tag->term_id );
			}
			$options[ $tag->term_id ] = $title;
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code          => __( 'Tag title', 'crazy-automator' ),
				$option_code . '_ID'  => __( 'Tag ID', 'crazy-automator' ),
				$option_code . '_URL' => __( 'Tag URL', 'crazy-automator' ),
			),
		);

		return apply_filters( 'cap_option_all_wc_product_tags', $option );
	}

	/**
	 * get all variations of selected variable product
	 */
	public function select_all_product_variations() {

		// Nonce and post object validation
		Crazy_Automator()->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$args = array(
				'post_type'      => 'product_variation',
				'post_parent'    => absint( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options = get_posts( $args );
			if ( isset( $options ) && ! empty( $options ) ) {
				foreach ( $options as $option ) {
					$fields[] = array(
						'value' => $option->ID,
						'text'  => ! empty( $option->post_excerpt ) ? $option->post_excerpt : $option->post_title,
					);
				}
			} else {
				$fields[] = array(
					'value' => - 1,
					'text'  => __( 'Any variation', 'crazy-automator' ),
				);
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * get all variations of selected variable product
	 */
	public function select_all_product_variations_with_any() {

		// Nonce and post object validation
		Crazy_Automator()->utilities->ajax_auth_check( $_POST );

		$fields = array();
		if ( isset( $_POST['value'] ) && ! empty( $_POST['value'] ) ) {

			$fields[] = array(
				'value' => - 1,
				'text'  => __( 'Any variation', 'crazy-automator' ),
			);

			$args = array(
				'post_type'      => 'product_variation',
				'post_parent'    => absint( $_POST['value'] ),
				'posts_per_page' => 999,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options = get_posts( $args );
			if ( isset( $options ) && ! empty( $options ) ) {
				foreach ( $options as $option ) {
					$fields[] = array(
						'value' => $option->ID,
						'text'  => ! empty( $option->post_excerpt ) ? $option->post_excerpt : $option->post_title,
					);
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * get all variations of selected variable product
	 */
	public function select_all_product_variations_from_to_to() {
		// Nonce and post object validation
		Crazy_Automator()->utilities->ajax_auth_check();

		$fields = array();

		$fields[] = array(
			'value' => - 1,
			'text'  => __( 'Any variation', 'crazy-automator' ),
		);
		if ( isset( $_POST['values']['WOOVARIPRODUCT'] ) && intval( '-1' ) !== intval( $_POST['values']['WOOVARIPRODUCT'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$args = array(
				'post_type'      => 'product_variation',
				//phpcs:ignore WordPress.Security.NonceVerification.Missing
				'post_parent'    => absint( sanitize_text_field( wp_unslash( $_POST['values']['WOOVARIPRODUCT'] ) ) ),
				//phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'posts_per_page' => 9999,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$options = get_posts( $args );
			if ( ! empty( $options ) ) {
				foreach ( $options as $option ) {
					if (
						//phpcs:ignore WordPress.Security.NonceVerification.Missing
						isset( $_POST['values']['WOOVARIPRODUCT_FROM'] ) &&
						//phpcs:ignore WordPress.Security.NonceVerification.Missing
						absint( $option->ID ) === absint( sanitize_text_field( wp_unslash( $_POST['values']['WOOVARIPRODUCT_FROM'] ) ) )
					) {
						continue;
					}
					$fields[] = array(
						'value' => $option->ID,
						'text'  => ! empty( $option->post_excerpt ) ? $option->post_excerpt : $option->post_title,
					);
				}
			}
		}
		echo wp_json_encode( $fields );
		die();
	}

	/**
	 * Fetch labels for trigger conditions.
	 *
	 * @return array
	 * @since 2.10
	 */
	public function get_trigger_condition_labels() {
		/**
		 * Filters WooCommerce Integrations' trigger conditions.
		 *
		 * @param array $trigger_conditions An array of key-value pairs of action hook handle and human readable label.
		 */
		return apply_filters(
			'cap_wc_trigger_conditions',
			array(
				'woocommerce_payment_complete'       => _x( 'pays for', 'WooCommerce', 'crazy-automator' ),
				'woocommerce_order_status_completed' => _x( 'completes', 'WooCommerce', 'crazy-automator' ),
				'woocommerce_thankyou'               => _x( 'lands on a thank you page for', 'WooCommerce', 'crazy-automator' ),
			)
		);
	}

	/**
	 * Fetch labels for trigger conditions.
	 *
	 * @return array
	 * @since 3.4
	 */
	public function get_order_item_trigger_condition_labels() {
		/**
		 * Filters WooCommerce Integrations' trigger conditions.
		 *
		 * @param array $trigger_conditions An array of key-value pairs of action hook handle and human readable label.
		 */
		return apply_filters(
			'cap_wc_order_item_trigger_conditions',
			array(
				'woocommerce_payment_complete'       => _x( 'paid for', 'WooCommerce', 'crazy-automator' ),
				'woocommerce_order_status_completed' => _x( 'completed', 'WooCommerce', 'crazy-automator' ),
				'woocommerce_thankyou'               => _x( 'thank you page visited', 'WooCommerce', 'crazy-automator' ),
			)
		);
	}

	/**
	 * @param string $code
	 *
	 * @return mixed|void
	 */
	public function get_woocommerce_trigger_conditions( $code = 'TRIGGERCOND' ) {
		$options = array(
			'option_code'     => $code,
			/* translators: Noun */
			'label'           => esc_attr__( 'Trigger condition', 'crazy-automator' ),
			'input_type'      => 'select',
			'required'        => true,
			'options_show_id' => false,
			'options'         => $this->get_trigger_condition_labels(),
		);

		return apply_filters( 'cap_option_woocommerce_trigger_conditions', $options );
	}

	/**
	 * @param string $code
	 *
	 * @return mixed|void
	 */
	public function get_woocommerce_order_item_trigger_conditions( $code = 'TRIGGERCOND' ) {
		$options = array(
			'option_code' => $code,
			/* translators: Noun */
			'label'       => esc_attr__( 'Trigger condition', 'crazy-automator' ),
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $this->get_order_item_trigger_condition_labels(),
		);

		return apply_filters( 'cap_option_woocommerce_order_item_trigger_conditions', $options );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function wc_subscription_statuses( $label = null, $option_code = 'WCS_STATUS', $is_any = true ) {
		if ( ! $label ) {
			$label = __( 'Status', 'crazy-automator' );
		}

		$options = array();

		if ( true === $is_any ) {
			$options['-1'] = __( 'Any status', 'crazy-automator' );
		}

		$options = array(
			'wc-active'         => __( 'Active', 'crazy-automator' ),
			'wc-cancelled'      => __( 'Cancelled', 'crazy-automator' ),
			'wc-expired'        => __( 'Expired', 'crazy-automator' ),
			'wc-on-hold'        => __( 'On hold', 'crazy-automator' ),
			'wc-pending-cancel' => __( 'Pending cancellation', 'crazy-automator' ),
		);

		$option = array(
			'option_code' => $option_code,
			'label'       => $label,
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $options,
		);

		return apply_filters( 'cap_option_all_wc_statuses', $option );

	}

	/**
	 * @param $label
	 * @param $option_code
	 *
	 * @return mixed|void
	 */
	public function all_wc_coupons( $label = null, $option_code = 'WOOCOUPONS', $is_any = false ) {
		if ( ! $label ) {
			$label = esc_attr__( 'Coupon', 'crazy-automator' );
		}

		$args = array(
			'post_type'      => 'shop_coupon',
			'posts_per_page' => 9999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$options = Crazy_Automator()->helpers->recipe->options->wp_query( $args, $is_any, esc_attr__( 'Any coupon', 'crazy-automator' ) );

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'relevant_tokens' => array(),
		);

		return apply_filters( 'cap_option_all_wc_coupons', $option );
	}

	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	public function add_any_status_option( $options ) {
		if ( empty( $options ) ) {
			return $options;
		}

		if ( 'WCORDERSTATUS' !== $options['option_code'] ) {
			return $options;
		}

		$options['options'] = array( '-1' => esc_attr__( 'Any status', 'crazy-automator' ) ) + $options['options'];

		return $options;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function wc_stock_statuses( $label = null, $option_code = 'WCPRODSTOCKSTATUS', $is_any = false ) {

		if ( ! $label ) {
			$label = esc_attr__( 'Product stock status', 'crazy-automator' );
		}

		$status = array();
		if ( true === $is_any ) {
			$status['-1'] = __( 'Any status', 'crazy-automator' );
		}

		$options = array(
			'onbackorder' => esc_attr__( 'On backorder', 'crazy-automator' ),
			'instock'     => esc_attr__( 'In stock', 'crazy-automator' ),
			'outofstock'  => esc_attr__( 'Out of stock', 'crazy-automator' ),
		);

		$status = $status + $options;

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $status,
			'relevant_tokens' => array(),
		);

		return apply_filters( 'cap_option_woocommerce_stock_statuses', $option );
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 *
	 * @return mixed
	 */
	public function wc_order_statuses_pro( $label = null, $option_code = 'WCORDERSTATUS' ) {

		if ( ! $label ) {
			$label = 'Status';
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options_show_id' => false,
			'options'         => wc_get_order_statuses(),
			'default_value'   => 'wc-completed',
		);

		return apply_filters( 'cap_option_woocommerce_pro_statuses', $option );
	}

	/**
	 * @param $state
	 * @param $country
	 *
	 * @return string
	 */
	public function find_state( $state, $country ) {
		$wc_countries_list = WC()->countries->states;
		if ( ! isset( $wc_countries_list[ $country ] ) || 2 === strlen( $state ) ) {
			return $state;
		}

		$states_list = $wc_countries_list[ $country ];

		$state_search = array_search( ucfirst( $state ), $states_list );

		return 2 === strlen( $state_search ) ? strtoupper( $state_search ) : $state_search;
	}

	/**
	 * @param $country
	 *
	 * @return false|int|string
	 */
	public function find_country( $country ) {
		if ( 2 === strlen( $country ) ) {
			return strtoupper( $country );
		}
		if ( 'united states' === strtolower( $country ) ) {
			return 'US';
		}
		if ( 'united kingdom' === strtolower( $country ) ) {
			return 'UK';
		}
		$wc_countries_list = WC()->countries->countries;

		return array_search( ucfirst( $country ), $wc_countries_list );
	}

	/**
	 * Get Subscription Condition Select field args.
	 *
	 * @param string $option_code - The option code identifier.
	 *
	 * @return array
	 */
	public function get_subscription_condition_field_args( $option_code ) {
		return array(
			'option_code'           => $option_code,
			'label'                 => esc_html__( 'Subscription product', 'crazy-automator' ),
			'required'              => true,
			'options'               => $this->get_subscription_condition_select_options(),
			'supports_custom_value' => true,
		);
	}

	/**
	 * Evaluate Subscription Product Conditions
	 *
	 * @param $product_id - WP_Post ID of the subscription product.
	 * @param $user_id - WP_User ID
	 *
	 * @return bool
	 */
	public function evaluate_subscription_product_condition( $product_id, $user_id ) {

		$active_statuses = apply_filters(
			'wcs_reports_active_statuses',
			array(
				'active',
				'pending-cancel',
				/*,'on-hold'*/
			)
		);

		// Check for any Subscription product.
		if ( intval( $product_id ) < 0 ) {
			$active_subscriptions = wcs_get_users_subscriptions( $user_id );

			return ! empty( $active_subscriptions );
		}

		// Check for Specific Subscription product.
		return wcs_user_has_subscription( $user_id, $product_id, $active_statuses );
	}

	/**
	 * @param        $label
	 * @param string $option_code
	 * @param array  $args
	 *
	 * @return array|mixed|null
	 */
	public function wc_taxonomies( $label = null, $option_code = 'WC_TAXONOMIES', $args = array() ) {

		if ( ! $label ) {
			$label = esc_attr__( 'Taxonomy', 'crazy-automator' );
		}

		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';

		$pass_args = array(
			'public'      => true,
			'object_type' => array( 'product' ),
		);

		$output = 'object';

		$taxonomies = get_taxonomies( $pass_args, $output );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$options[ $taxonomy->name ] = esc_html( $taxonomy->labels->singular_name );
			}
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'options'         => $options,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'relevant_tokens' => array(
				$option_code => esc_attr__( 'Product taxonomy', 'crazy-automator' ),
			),
		);

		return apply_filters( 'cap_option_wc_taxonomies', $option );

	}

	/**
	 * @return void
	 */
	public function select_all_terms_by_taxonomy() {
		// Nonce and post object validation
		Crazy_Automator()->utilities->ajax_auth_check();
		$options[] = array(
			'value' => '-1',
			'text'  => __( 'Any term', 'crazy-automator' ),
		);
		if ( ! automator_filter_has_var( 'value', INPUT_POST ) || empty( automator_filter_input( 'value', INPUT_POST ) ) ) {
			echo wp_json_encode( $options );
			die();
		}
		$terms = get_terms(
			array(
				'taxonomy'   => automator_filter_input( 'value', INPUT_POST ),
				'hide_empty' => false,
			)
		);

		foreach ( $terms as $term ) {
			$title = $term->name;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'ID: %s (no title)', 'crazy-automator' ), $term->term_id );
			}
			$options[] = array(
				'value' => $term->term_id,
				'text'  => $title,
			);
		}

		echo wp_json_encode( $options );
		die();
	}
}
