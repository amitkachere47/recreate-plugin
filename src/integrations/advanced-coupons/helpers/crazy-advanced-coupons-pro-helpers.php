<?php

namespace Crazy_Automator_Pro;

use Crazy_Automator\Advanced_Coupons_Helpers;

/**
 * Class Crazy_Advanced_Coupons_Pro_Helpers
 *
 * @package Crazy_Automator
 */
class Crazy_Advanced_Coupons_Pro_Helpers extends Advanced_Coupons_Helpers {

	/**
	 * Load options variable.
	 *
	 * @var bool
	 */
	public $load_options;

	/**
	 * Crazy_Advanced_Coupons_Pro_Helpers constructor.
	 */
	public function __construct() {
		// Selectively load options
		if ( property_exists( '\Crazy_Automator\Advanced_Coupons_Helpers', 'load_options' ) ) {
			$this->load_options = Crazy_Automator()->helpers->recipe->maybe_load_trigger_options( __CLASS__ );
		}
	}

	/**
	 * Setpro function is used to set pro variable.
	 *
	 * @param Crazy_Advanced_Coupons_Pro_Helpers $pro
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	public function setPro( Crazy_Advanced_Coupons_Pro_Helpers $pro ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		parent::setPro( $pro );
	}

}
