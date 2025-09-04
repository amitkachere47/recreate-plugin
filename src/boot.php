<?php

namespace Crazy_Automator_Pro;

/**
 * Class Boot
 *
 * @package Crazy_Automator_Pro
 */
class Boot {

	/**
	 * The instance of the class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Boot
	 */
	private static $instance = null;

	/**
	 * @var Crazy_Automator_Pro_Helpers_Recipe
	 */
	public static $internal_helpers;

	/**
	 * @var
	 */
	public $internal_process;

	/**
	 * @var Internal_Triggers_Actions
	 */
	public $internal_triggers_actions;

	/**
	 * @var Crazy_Automator_Pro_Cache_Handler
	 */
	public $crazy_automator_pro_cache_handler;

	/**
	 * @var array
	 */
	public static $core_class_inits = array();

	/**
	 * class constructor
	 */
	public function __construct() {

		$this->initialize_db();

		add_action( 'plugins_loaded', array( $this, 'maybe_include_action_scheduler' ), - 10 );
		add_action( 'plugins_loaded', array( $this, 'require_traits_files' ), 5 );

		$this->initialize_loops();

		$this->require_class_files();
		add_action( 'plugins_loaded', array( $this, 'boot_child_plugin' ), 9 );
		add_filter(
			'upgrader_pre_install',
			array(
				$this,
				'upgrader_pre_install',
			),
			99,
			2
		);
		// Show upgrade notice from readme.txt
		add_action(
			'in_plugin_update_message-' . plugin_basename( CRAZY_AUTOMATOR_PRO_FILE ),
			array(
				$this,
				'in_plugin_update_message',
			),
			10,
			2
		);

		add_action(
			'admin_notices',
			array(
				$this,
				'crazy_automator_free_version_check',
			)
		);

		if ( is_file( CAPro_ABSPATH . 'vendor/autoload.php' ) ) {
			include_once CAPro_ABSPATH . 'vendor/autoload.php';
		}
	}

	/**
	 * Creates required tables
	 *
	 * @return false
	 */
	private function initialize_db() {
		$schema = new Schema();
		$schema->create_tables();
		// Adds new tables in the Automator > Status > Tools > Database.
		$schema->tools_attach_tables();
	}

	/**
	 * Starts the loop's required hooks, post types, etc.
	 *
	 * @return void
	 */
	public function initialize_loops() {

		// Initializes loop queue health check.
		( new Loops\Loop\Background_Process\Queue_Health_Check() )->init_hook();

		// The process registry.
		( Loops_Process_Registry::get_instance() );

		// The loops entry point.
		( Loops\Entry_Point::get_instance() );

		// Registers the various process callbacks.
		( new Loops\Process_Hooks_Callback() )->register_hooks();

		// Registers the token definitions for posts loop.
		Loops\Token\Posts\Definition::register_hooks();
		// Registers the token definitions for users loop.
		Loops\Token\Users\Definition::register_hooks();

		// Initializes the post types.
		$this->initialize_loops_post_types();

		// Initializes the recipe ui endpoints.
		$this->initialize_loops_recipe_endpoints();

	}

	/**
	 * Initializes recipe endpoints.
	 *
	 * @return void
	 */
	public function initialize_loops_recipe_endpoints() {

		( new Loops\Recipe\Endpoint() )->register_hooks();

	}

	/**
	 * Registers loops post types.
	 *
	 * @return void
	 */
	public function initialize_loops_post_types() {

		$loop = new Loops\Loop_Post_Types();

		// Registers the loop's post type.
		add_action( 'init', array( $loop, 'register_loop_post_type' ) );
		// Registers the loop's filter's post type.
		add_action( 'init', array( $loop, 'register_filter_post_type' ) );

	}

	/**
	 * @return void
	 */
	public function maybe_include_action_scheduler() {
		// Check if already loading by another plugin
		if ( class_exists( 'ActionScheduler', false ) ) {
			return;
		}
		// If file exists, then queue it
		if ( is_file( dirname( CRAZY_AUTOMATOR_PRO_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
			include_once dirname( CRAZY_AUTOMATOR_PRO_FILE ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		}
	}

	/**
	 *
	 */
	public function crazy_automator_free_version_check() {
		if ( defined( 'CRAZY_AUTOMATOR_PLUGIN_VERSION' ) && version_compare( CRAZY_AUTOMATOR_PLUGIN_VERSION, '3.1', '<' ) ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			$class   = 'notice notice-error';
			$url     = admin_url( 'plugin-install.php?tab=plugin-information&plugin=crazy-automator&section=changelog&TB_iframe=true&width=850&height=946' );
			$message = sprintf(
				'<strong style="color:red;">%s</strong> %s',
				sprintf( __( 'WARNING: Your version of %s is out of date.', 'crazy-automator' ), 'CrazyAutomator' ),
				sprintf(
					__( 'Please %s to ensure your recipes continue to run.', 'crazy-automator' ),
					sprintf( '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>', $url, __( 'update immediately', 'crazy-automator' ) )
				)
			);

			printf( '<div class="%1$s"><p><span class="dashicons dashicons-warning" style="color: red"></span>%2$s</p></div>', esc_attr( $class ), $message );
		}
	}

	/**
	 * Creates singleton instance of Boot class and defines which directories
	 * are autoloaded
	 *
	 * @return Boot
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			// Lets boot up!
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Require the action tokens traits.
	 *
	 * @return void
	 * @since 4.6
	 */
	public function require_traits_files() {

		require_once trailingslashit( __DIR__ ) . 'core/traits/action-tokens.php';

	}

	/**
	 * SPL Auto Loader functions
	 *
	 * @param string $class
	 *
	 * @since 1.0.0
	 */
	private function require_class_files() {
		/**
		 * Webhook related files - Start
		 */
		self::$core_class_inits['Webhook_Rest_Handler']        = __DIR__ . '/core/webhook/webhook-rest-handler.php';
		self::$core_class_inits['Webhook_Rest_Sample_Handler'] = __DIR__ . '/core/webhook/webhook-rest-sample-handler.php';
		self::$core_class_inits['Webhook_Ajax_Handler']        = __DIR__ . '/core/webhook/webhook-ajax-handler.php';
		self::$core_class_inits['Webhook_Static_Content']      = __DIR__ . '/core/webhook/webhook-static-content.php';
		self::$core_class_inits['Webhook_Common_Options']      = __DIR__ . '/core/webhook/webhook-common-options.php';

		/**
		 * Webhook related files - End
		 */
		self::$core_class_inits['Crazy_Automator_Pro_Handle_Anonymous'] = __DIR__ . '/core/classes/crazy-automator-pro-handle-anonymous.php';
		self::$core_class_inits['Magic_Button']                   = __DIR__ . '/core/classes/magic-button.php';
		self::$core_class_inits['Pro_Filters']                    = __DIR__ . '/core/classes/pro-filters.php';
		self::$core_class_inits['Pro_Ui']                         = __DIR__ . '/core/classes/pro-ui.php';
		self::$core_class_inits['Activity_Log_Settings']          = __DIR__ . '/core/extensions/activity-log-settings.php';
		self::$core_class_inits['Async_Actions']                  = __DIR__ . '/core/classes/async-actions.php';
		self::$core_class_inits['Actions_Conditions']             = __DIR__ . '/core/classes/actions-conditions.php';
		self::$core_class_inits['Action_Tokens_Helpers']          = __DIR__ . '/core/classes/action-tokens-helpers.php';
		self::$core_class_inits['Crazy_Copy_Recipe_Parts_Pro']          = __DIR__ . '/core/classes/class-crazy-copy-recipe-parts-pro.php';

		// Licensing
		self::$core_class_inits['Licensing'] = __DIR__ . '/core/admin/licensing/licensing.php';

	}

	/**
	 * Looks through all defined directories and modifies file name to create
	 * new class instance.
	 *
	 * @since 1.0.0
	 */
	private function auto_initialize_classes() {
		foreach ( self::$core_class_inits as $class_name => $file ) {
			require_once $file;
			$class = __NAMESPACE__ . '\\' . $class_name;
			Utilities::add_class_instance( $class, new $class() );
		}
	}

	/**
	 * @param $args
	 * @param $response
	 */
	public function in_plugin_update_message( $args, $response ) {
		$upgrade_notice = '';
		if ( isset( $response->upgrade_notice ) && ! empty( $response->upgrade_notice ) ) {
			$upgrade_notice .= '<p class="ua_plugin_upgrade_notice">';
			$upgrade_notice .= sprintf( '<strong>%s</strong> %s', __( 'Heads up!', 'crazy-automator' ), preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $response->upgrade_notice ) );
			$upgrade_notice .= '</p>';
		}

		echo apply_filters( 'cap_pro_in_plugin_update_message', $upgrade_notice ? '</p>' . wp_kses_post( $upgrade_notice ) . '<p class="dummy">' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Placeholder function for future use
	 *
	 * @param $response
	 * @param $extra
	 *
	 * @return mixed
	 *
	 * @since  2.8
	 * @author Saad S.
	 */
	public function upgrader_pre_install( $response, $extra ) {

		return $response;
	}

	/**
	 * @return void
	 */
	public function boot_child_plugin() {

		if ( ! $this->is_crazy_automator_base_plugin_installed() ) {
			add_action( 'admin_init', array( $this, 'crazy_require_plugin_installer' ) );

			return;
		}

		if ( ! $this->is_crazy_automator_php8_compat() ) {
			add_action( 'admin_notices', array( $this, 'crazy_check_automator32_php8_compat_message' ) );

			return;
		}

		if ( defined( 'CRAZY_AUTOMATOR_BASE_FILE' ) ) {

			if ( class_exists( '\Crazy_Automator\Crazy_Automator_Recipe_Process' ) ) {
				include_once Utilities::get_include( 'crazy-automator-pro-recipe-process.php' );
				$this->internal_process = new Crazy_Automator_Pro_Recipe_Process();
			}

			if ( class_exists( '\Crazy_Automator\Crazy_Automator_Recipe_Process_User' ) ) {
				include_once Utilities::get_include( 'crazy-automator-pro-recipe-process-user.php' );
				include_once Utilities::get_include( 'crazy-automator-pro-recipe-process-anon.php' );
				Crazy_Automator_Pro()->process->anon = new Crazy_Automator_Pro_Recipe_Process_Anon();
			}

			if ( class_exists( '\Crazy_Automator\Crazy_Automator_Recipe_Process_Complete' ) ) {
				include_once Utilities::get_include( 'crazy-automator-pro-recipe-process-complete.php' );
				Crazy_Automator_Pro()->complete->anon = new Crazy_Automator_Pro_Recipe_Process_Complete();
			}

			include_once Utilities::get_include( 'crazy-automator-pro-helpers-recipe.php' );
			self::$internal_helpers = new Crazy_Automator_Pro_Helpers_Recipe();

		} else {

			Crazy_Automator_Pro()->helpers                  = new \stdClass();
			Crazy_Automator_Pro()->process                  = new \stdClass();
			Crazy_Automator_Pro()->helpers->recipe          = new \stdClass();
			Crazy_Automator_Pro()->helpers->recipe->field   = Crazy_Automator_Pro()->options;
			Crazy_Automator_Pro()->helpers->recipe->options = Crazy_Automator_Pro()->options;

		}

		include_once Utilities::get_include( 'crazy-automator-pro-cache-handler.php' );
		$this->crazy_automator_pro_cache_handler = new Crazy_Automator_Pro_Cache_Handler();

		include_once Utilities::get_include( 'crazy-internal-triggers-actions.php' );
		$this->internal_triggers_actions = new Crazy_Internal_Triggers_Actions();

		// Initialize all classes in given directories
		$this->auto_initialize_classes();

		/* Licensing */
		// URL of store powering the plugin
		/**
		 * @deprecated v3.1 use CRAZY_AUTOMATOR_PRO_STORE_URL
		 */
		define( 'CRAZY_AUTOMATOR_CRAZY_AUTOMATOR_PRO_STORE_URL', CRAZY_AUTOMATOR_PRO_STORE_URL ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

		// Store download name/title
		/**
		 * @deprecated v3.1 use CRAZY_AUTOMATOR_PRO_ITEM_NAME
		 */
		define( 'CRAZY_AUTOMATOR_CRAZY_AUTOMATOR_PRO_ITEM_NAME', CRAZY_AUTOMATOR_PRO_ITEM_NAME ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
		/**
		 * Make sure to force true in Free IF Pro >= 3.9 And Free is < 3.9
		 */
		add_filter(
			'crazy_automator_do_load_options',
			function ( $status, $class ) {
				$migrated = apply_filters(
					'crazy_automator_pro_load_options_override',
					array(
						'Crazy_Automator\Woocommerce_Helpers',
						'Crazy_Automator\Divi_Helpers',
						'Crazy_Automator\Elementor_Helpers',
						'Crazy_Automator\Wp_Helpers',
						'Crazy_Automator\Wpforms_Helpers',
						'Crazy_Automator\Wp_Fusion_Helpers',
						'Crazy_Automator\Edd_Helpers',
						'Crazy_Automator\Learndash_Helpers',
						'Crazy_Automator\Buddyboss_Helpers',
						'Crazy_Automator\Buddypress_Helpers',
						'Crazy_Automator\Gravity_Forms_Helpers',
						'Crazy_Automator\Bbpress_Helpers',
						'Crazy_Automator\Badgeos_Helpers',
						'Crazy_Automator\Gamipress_Helpers',
						'Crazy_Automator\Event_Tickets_Helpers',
						'Crazy_Automator\Affwp_Helpers',
						'Crazy_Automator\Caldera_Helpers',
						'Crazy_Automator\Contact_Form7_Helpers',
						'Crazy_Automator\Events_Manager_Helpers',
						'Crazy_Automator\Fluent_Crm_Helpers',
						'Crazy_Automator\Formidable_Helpers',
						'Crazy_Automator\Forminator_Helpers',
						'Crazy_Automator\Give_Helpers',
						'Crazy_Automator\H5p_Helpers',
						'Crazy_Automator\Happyforms_Helpers',
						'Crazy_Automator\Learnpress_Helpers',
						'Crazy_Automator\Lifterlms_Helpers',
						'Crazy_Automator\Mailpoet_Helpers',
						'Crazy_Automator\Masterstudy_Helpers',
						'Crazy_Automator\Memberpress_Courses_Helpers',
						'Crazy_Automator\Memberpress_Helpers',
						'Crazy_Automator\MEC_HELPERS',
						'Crazy_Automator\Mycred_Helpers',
						'Crazy_Automator\Ninja_Forms_Helpers',
						'Crazy_Automator\Paid_Memberships_Pro_Helpers',
						'Crazy_Automator\Presto_Helpers',
						'Crazy_Automator\Restrict_Content_Helpers',
						'Crazy_Automator\Tutorlms_Helpers',
						'Crazy_Automator\Ultimate_Member_Helpers',
						'Crazy_Automator\Crazy_Ceus_Helpers',
						'Crazy_Automator\Crazy_Groups_Helpers',
						'Crazy_Automator\Crazy_Codes_Helpers',
						'Crazy_Automator\Crazy_Toolkit_Helpers',
						'Crazy_Automator\Upsell_Plugin_Helpers',
						'Crazy_Automator\Wc_Memberships_Helpers',
						'Crazy_Automator\Wishlist_Member_Helpers',
						'Crazy_Automator\Wp_Courseware_Helpers',
						'Crazy_Automator\Wp_Fluent_Forms_Helpers',
						'Crazy_Automator\Wpjm_Helpers',
						'Crazy_Automator\Wppolls_Helpers',
						'Crazy_Automator\Wpsp_Helpers',
						'Crazy_Automator\Wplms_Helpers',
					)
				);
				if ( array_intersect( array( $class ), $migrated ) ) {
					return true;
				}

				return $status;
			},
			99,
			2
		);
	}

	/**
	 * @return bool
	 */
	public function is_crazy_automator_base_plugin_installed() {

		return defined( 'CRAZY_AUTOMATOR_PLUGIN_VERSION' );

	}

	/**
	 * @return void
	 */
	public function crazy_require_plugin_installer() {

		require_once CAPro_ABSPATH . 'src/core/admin/installer/class-crazy-plugin-installer.php';

		$this->installer = new Crazy_Plugin_Installer();

		$this->installer->create_ajax();

		add_action( 'admin_notices', array( $this, 'crazy_require_plugin_installer_notice' ) );

	}

	/**
	 * @return void
	 */
	public function crazy_require_plugin_installer_notice() {

		ob_start();

		require_once CAPro_ABSPATH . 'src/core/views/notice-automator-not-found.php';

		echo ob_get_clean();
	}

	/**
	 * Run error notice in Uncanny Automator is not installed
	 */
	public static function crazy_free_needs_to_be_upgraded() {
		if ( version_compare( CRAZY_AUTOMATOR_PLUGIN_VERSION, '3.2', '>=' ) ) {
			return;
		}
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		$class   = 'notice notice-error';
		$version = '3.2';
		// An old version of Uncanny Automator is running
		$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=crazy-automator&section=changelog&TB_iframe=true&width=850&height=946' );

		/* translators: 1. Trademarked term. 2. Trademarked term */
		$message        = sprintf( __( 'The version of %1$s you have installed is not compatible with this version of %2$s.', 'crazy-automator' ), 'CrazyAutomator', 'CrazyAutomator Pro' );
		$message_update = sprintf( __( 'Please update %1$s to version %2$s or later.', 'crazy-automator' ), 'CrazyAutomator', $version );

		printf( '<div class="%1$s"><h3 style="font-weight: bold; color: red"><span class="dashicons dashicons-warning"></span>%2$s <a href="%3$s" class="thickbox open-plugin-details-modal">' . $message_update . '</a></h3></div>', esc_attr( $class ), esc_html( $message ), $url );
	}

	/**
	 * is_php8_compat
	 *
	 * Checks and displays an admin notices if php is version 8
	 * or above and both automator free and pro is version 3.2 or above.
	 *
	 * @return boolean True if version is 8 and both free or pro is less than 3.2. Otherwise, false.
	 */
	protected function is_crazy_automator_php8_compat() {

		if ( ! defined( 'CRAZY_AUTOMATOR_PLUGIN_VERSION' ) ) {
			return false;
		}

		// Check if the php version is 8.0 and above.
		$is_php8 = version_compare( PHP_VERSION, '8.0.0', '>=' );

		if ( ! $is_php8 ) {
			return true;
		}

		$automator_version_is_less_than_3_2 = version_compare( CRAZY_AUTOMATOR_PLUGIN_VERSION, '3.2', '<' );

		// If either of free and pro is < 3.2.
		if ( $automator_version_is_less_than_3_2 ) {
			return false;
		}

		return true;

	}

	/**
	 * crazy_check_automator32_php8_compat_message
	 *
	 * Callback function from crazy_check_automator32_php8_compat. Shows an admin
	 * notice.
	 *
	 * @return void
	 */
	public function crazy_check_automator32_php8_compat_message() {

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		$class   = 'notice notice-error';
		$version = '3.2';

		// An old version of Uncanny Automator is running
		$url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=crazy-automator&section=changelog&TB_iframe=true&width=850&height=946' );

		/* translators: 1. Trademarked term. 2. Trademarked term */
		$message        = sprintf( __( "%1\$s recipes have been disabled because your version of PHP (%3\$s) is not fully compatible with the version of %2\$s that's installed.", 'crazy-automator' ), 'CrazyAutomator Pro', 'CrazyAutomator', PHP_VERSION );
		$message_update = sprintf( __( 'Please update %1$s to version %2$s or later.', 'crazy-automator' ), 'CrazyAutomator', $version );

		printf( '<div class="%1$s"><h3 style="font-weight: bold; color: red"><span class="dashicons dashicons-warning"></span>%2$s <a href="%3$s" class="thickbox open-plugin-details-modal">' . esc_html( $message_update ) . '</a></h3></div>', esc_attr( $class ), esc_html( $message ), esc_url( $url ) );

	}

}
