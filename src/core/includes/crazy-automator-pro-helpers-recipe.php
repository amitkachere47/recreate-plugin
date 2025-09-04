<?php


namespace Crazy_Automator_Pro;

/**
 * Class Crazy_Automator_Pro_Helpers_Recipe
 * @package Crazy_Automator_Pro
 */
class Crazy_Automator_Pro_Helpers_Recipe extends \Crazy_Automator\Automator_Helpers_Recipe {

	/**
	 * @var \Crazy_Automator_Pro\Wp_Fusion_Pro_Helpers
	 */
	public $wp_fusion;
	/**
	 * @var \Crazy_Automator_Pro\Zapier_Pro_Helpers
	 */
	public $zapier;
	/**
	 * @var \Crazy_Automator_Pro\Gotowebinar_Pro_Helpers
	 */
	public $gotowebinar;
	/**
	 * @var \Crazy_Automator_Pro\Gototraining_Pro_Helpers
	 */
	public $gototraining;
	/**
	 * @var \Crazy_Automator_Pro\Happyforms_Pro_Helpers
	 */
	public $happyforms;

	/**
	 * @var \Crazy_Automator_Pro\Wpwh_Pro_Helpers
	 */
	public $wp_webhooks;

	/**
	 * @var \Crazy_Automator_Pro\Google_Sheet_Pro_Helpers
	 */
	public $google_sheet;

	/**
	 * @var \Crazy_Automator_Pro\Slack_Pro_Helpers
	 */
	public $slack;

	/**
	 * @var Crazy_Wp_User_Manager_Pro_Helpers
	 */
	public $wp_user_manager;

	/**
	 * @var \Crazy_Automator_Pro\Mailchimp_Pro_Helpers
	 */
	public $mailchimp;

	/**
	 *
	 */
	public static function load_pro_recipe_helpers() {
		//global $crazy_automator;
		$helpers = Utilities::get_all_helper_instances();
		//      $automator_version = \Crazy_Automator\InitializePlugin::PLUGIN_VERSION;
		//      $version_compare   = version_compare( $automator_version, '2.8.2', '<' );
		if ( ! $helpers ) {
			return;
		}
		foreach ( $helpers as $integration => $class ) {
			// Fix in place to avoid fatal errors!
			//              if ( $version_compare && 'integromat' === $integration ) {
			//                  continue;
			//              }

			if ( isset( Crazy_Automator()->helpers->recipe->$integration ) ) {
				if ( property_exists( Crazy_Automator()->helpers->recipe->$integration, 'pro' ) ) {
					Crazy_Automator()->helpers->recipe->$integration->setPro( $class );
				}
			} else {
				Crazy_Automator()->helpers->recipe->$integration = $class;
				if ( method_exists( Crazy_Automator()->helpers->recipe->$integration, 'setOptions' ) ) {
					Crazy_Automator()->helpers->recipe->$integration->setOptions( $class );
				}
				if ( method_exists( Crazy_Automator()->helpers->recipe->$integration, 'setPro' ) ) {
					Crazy_Automator()->helpers->recipe->$integration->setPro( $class );
				}
			}
		}
	}

	/**
	 * Decode data coming from Automator API.
	 *
	 * @param string $message Original message string to decode.
	 * @param string $secret Secret Key used for encription
	 *
	 * @return string|array
	 */
	public static function automator_api_decode_message( $message, $secret ) {
		$tokens = false;
		if ( ! empty( $message ) and ! empty( $secret ) ) {
			$message           = base64_decode( $message );
			$method            = 'AES128';
			$iv                = substr( $message, 0, 16 );
			$encrypted_message = substr( $message, 16 );
			$tokens            = openssl_decrypt( $encrypted_message, $method, $secret, 0, $iv );
			$tokens            = maybe_unserialize( $tokens );
		}

		return $tokens;
	}
}
