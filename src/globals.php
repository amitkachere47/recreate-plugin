<?php

if ( ! defined( 'CAPro_ABSPATH' ) ) {
	/**
	 * Automator Pro ABSPATH for file includes
	 */
	define( 'CAPro_ABSPATH', dirname( CRAZY_AUTOMATOR_PRO_FILE ) . DIRECTORY_SEPARATOR ); // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ConstantNotUpperCase
}

if ( ! defined( 'CRAZY_AUTOMATOR_PRO_STORE_URL' ) ) {
	/**
	 * URL of store powering the plugin
	 */
	define( 'CRAZY_AUTOMATOR_PRO_STORE_URL', 'https://example.com/' );
}

if ( ! defined( 'CRAZY_AUTOMATOR_PRO_ITEM_NAME' ) ) {
	/**
	 * Store Item download name/title
	 */
	define( 'CRAZY_AUTOMATOR_PRO_ITEM_NAME', 'CrazyAutomator Pro' );
}

if ( ! defined( 'CRAZY_AUTOMATOR_PRO_ITEM_ID' ) ) {
	/**
	 * Store Item ID
	 */
	define( 'CRAZY_AUTOMATOR_PRO_ITEM_ID', 506 );
}
