<?php

namespace Crazy_Automator_Pro\Loop_Filters;

use Crazy_Automator_Pro\Loops\Filter\Base\Loop_Filter;

/**
 * Class WC_HAS_USER_PURCHASED_PRODUCT
 *
 * @package Crazy_Automator_Pro
 */
class WC_HAS_USER_PURCHASED_PRODUCT extends Loop_Filter {

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function setup() {
		$this->set_integration( 'WC' );
		$this->set_meta( 'WC_HAS_USER_PURCHASED_PRODUCT' );
		$this->set_sentence( esc_html_x( 'The user {{has/has not}} purchased {{a specific product}}', 'Filter sentence', 'crazy-automator' ) );
		$this->set_sentence_readable(
			sprintf(
			/* translators: Filter sentence */
				esc_html_x( 'The user {{has/has not:%1$s}} purchased {{a specific product:%2$s}}', 'Filter sentence', 'crazy-automator' ),
				'CRITERIA',
				$this->get_meta()
			)
		);
		$this->set_fields( array( $this, 'load_options' ) );
		$this->set_entities( array( $this, 'retrieve_users_purchased_product' ) );
	}

	/**
	 * @return mixed[]
	 */
	public function load_options() {
		return array(
			$this->get_meta() => array(
				array(
					'option_code'           => 'CRITERIA',
					'type'                  => 'select',
					'supports_custom_value' => false,
					'label'                 => esc_html_x( 'Criteria', 'WooCommerce', 'crazy-automator' ),
					'options'               => array(
						array(
							'text'  => esc_html_x( 'has', 'WooCommerce', 'crazy-automator' ),
							'value' => esc_html_x( 'has', 'WooCommerce', 'crazy-automator' ),
						),
						array(
							'text'  => esc_html_x( 'has not', 'WooCommerce', 'crazy-automator' ),
							'value' => esc_html_x( 'has-not', 'WooCommerce', 'crazy-automator' ),
						),
					),
				),
				array(
					'option_code'           => $this->get_meta(),
					'type'                  => 'select',
					'label'                 => esc_html_x( 'Product', 'WooCommerce', 'crazy-automator' ),
					'options'               => $this->get_woo_products(),
					'supports_custom_value' => false,
				),
			),
		);

	}

	/**
	 * @return array[]
	 */
	private function get_woo_products() {
		$args    = array(
			'post_type'      => 'product',
			'posts_per_page' => 9999, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);
		$return  = array();
		$options = Crazy_Automator()->helpers->recipe->options->wp_query( $args, true, __( 'Any product' ) );

		foreach ( $options as $id => $text ) {
			$return[] = array(
				'value' => $id,
				'text'  => $text,
			);
		}

		return $return;
	}

	/**
	 * @param array{WC_HAS_USER_PURCHASED_PRODUCT:string,CRITERIA:string} $fields
	 *
	 * @return int[]
	 */
	public function retrieve_users_purchased_product( $fields ) {
		$criteria   = $fields['CRITERIA'];
		$product_id = $fields['WC_HAS_USER_PURCHASED_PRODUCT'];

		if ( empty( $criteria ) || empty( $product_id ) ) {
			return array();
		}

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
		$args     = array(
			'status'    => $statuses,
			'post_type' => 'shop_order',
		);

		if ( intval( '-1' ) !== intval( $product_id ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_product_id',
					'value'   => $product_id,
					'compare' => '=',
				),
			);
		}
		$orders = wc_get_orders( $args );

		if ( empty( $orders ) ) {
			return array();
		}

		$user_ids = array();
		foreach ( $orders as $order ) {
			$user_ids[] = $order->get_user_id();
		}

		$user_ids = array_unique( $user_ids );
		$users    = $user_ids;
		if ( 'has-not' === $criteria ) {
			$all_users    = new \WP_User_Query( array( 'fields' => 'ids' ) );
			$all_user_ids = $all_users->get_results();
			$users        = array_diff( $all_user_ids, $user_ids );
		}

		return ! empty( $users ) ? $users : array();

	}
}
