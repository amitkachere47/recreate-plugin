<?php

namespace Uncanny_Automator_Pro\Integrations\WooCommerce_Bookings;

use Uncanny_Automator\Integrations\WooCommerce_Bookings\Wc_Bookings_Helpers;

/**
 * Class Wc_Bookings_Helpers_Pro
 *
 * @package Uncanny_Automator_Pro
 */
class Wc_Bookings_Helpers_Pro extends Wc_Bookings_Helpers {

	/**
	 * Booking status related tokens
	 *
	 * @return array[]
	 */
	public function wcb_booking_status_related_tokens() {
		return array(
			array(
				'tokenId'   => 'WCB_BOOKING_OLD_STATUS',
				'tokenName' => __( "Booking's previous status", 'uncanny-automator-pro' ),
				'tokenType' => 'int',
			),
		);
	}

	/**
	 * WC_Bookings get all booking statuses
	 *
	 * @return array
	 */
	public function get_booking_statuses( $is_any = false ) {
		$statuses = array_unique( array_merge( get_wc_booking_statuses( null, true ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
		$options  = array();
		if ( true === $is_any ) {
			$options[] = array(
				'value' => '-1',
				'text'  => esc_attr_x( 'Any status', 'WooCommerce Bookings', 'uncanny-automator-pro' ),
			);
		}
		foreach ( $statuses as $status => $status_label ) {
			$options[] = array(
				'value' => $status,
				'text'  => $status_label,
			);
		}

		return $options;
	}

}
