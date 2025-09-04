<?php
/**
 * Plugin Name:         CrazyAutomator
 * Description:         A crazy automation plugin.
 * Author:              CrazyAutomator Devs
 * Author URI:          https://example.com/
 * Plugin URI:          https://example.com/
 * Text Domain:         crazy-automator
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Version:             5.3
 * Requires at least:   5.4
 * Requires PHP:        7.0
 */

use Crazy_Automator\Services\Recipe\Structure\Actions\Conditions;
use Crazy_Automator\Services\Recipe\Structure\Actions\Action;

use Crazy_Automator\Automator_Functions;
use Crazy_Automator_Pro\Crazy_Automator_Pro_Load;

if ( ! defined( 'CRAZY_AUTOMATOR_PRO_PLUGIN_VERSION' ) ) {
	/**
	 * Specify Automator Pro version.
	 */
	define( 'CRAZY_AUTOMATOR_PRO_PLUGIN_VERSION', '5.3' );
}

if ( ! defined( 'CRAZY_AUTOMATOR_PRO_FILE' ) ) {
	/**
	 * Specify Automator Pro base file.
	 */
	define( 'CRAZY_AUTOMATOR_PRO_FILE', __FILE__ );
}

if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
	add_action( 'admin_notices', 'crazy_automator_pro_version_check_admin_notice', - 99999 + 1 );

	// Function to display admin notice
	function crazy_automator_pro_version_check_admin_notice() {
		?>
		<div class="notice notice-error"
			 style="border-left: 4px solid #dc3232; font-weight: bold; background-color: #fff4e5; color: #000;">
			<p>
				<?php
				//Translators: %s: The version number of Crazy Automator Pro.
				echo sprintf( esc_html__( 'Notice: CrazyAutomator v%s requires PHP 7.0 or higher to run properly. Your current PHP version is below this requirement, so the plugin has been deactivated and all automations have stopped. Please upgrade your PHP version to ensure that your automations and other plugin features work correctly.', 'crazy-automator' ), esc_html( CRAZY_AUTOMATOR_PRO_PLUGIN_VERSION ) );
				?>
			</p>
		</div>
		<?php
	}

	// Stop loading the plugin
	return;
}

/**
 * @param string $class
 *
 * @return void
 */
function crazy_automator_pro_autoloader( $class ) {

	$class = strtolower( $class );

	global $crazy_automator_pro_class_map;

	if ( ! $crazy_automator_pro_class_map ) {
		$crazy_automator_pro_class_map = include_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_classmap.php';
		$crazy_automator_pro_class_map = array_change_key_case( $crazy_automator_pro_class_map, CASE_LOWER );
	}

	if ( isset( $crazy_automator_pro_class_map[ $class ] ) ) {
		include_once $crazy_automator_pro_class_map[ $class ];
	}
}

spl_autoload_register( 'crazy_automator_pro_autoloader' );

// Add other global variables for plugin.
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'globals.php';
// Add InitializePlugin class for other plugins checking for version.
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'legacy.php';

/**
 * If Automator function is not defined AND Automator < 3.0, add Automator fallback
 *
 * @return Automator_Functions
 */
function Crazy_Automator_Pro() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	if ( defined( 'CRAZY_AUTOMATOR_PLUGIN_VERSION' ) && function_exists( 'Crazy_Automator' ) ) {
		return Crazy_Automator();
	}
	// this global variable stores many functions that can be used for integrations, triggers, actions, and closures.
	global $crazy_automator;

	return $crazy_automator;
}

// Include the Automator_Load class and kickstart Automator Pro.
if ( ! class_exists( '\Crazy_Automator_Pro\Crazy_Automator_Pro_Load', false ) ) {
	include_once CAPro_ABSPATH . 'src/class-crazy-automator-pro-load.php';
}

Crazy_Automator_Pro_Load::get_instance();
