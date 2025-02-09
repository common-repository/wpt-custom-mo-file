<?php
/**
 * WPT Custom Mo File
 *
 * @package     WPT_Custom_Mo_File
 * @author      WP-Translations Team
 * @copyright   2022 WP-Translations Team
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WPT Custom Mo File
 * Plugin URI:        https://wordpress.org/plugins/wpt-custom-mo-file/
 * Description:       A powerful WordPress plugin that let you use your own translation .mo files. Simple as that.
 * Version:           1.2.2
 * Requires at least: 5.3
 * Tested up to:      6.0
 * Requires PHP:      7.2
 * Author:            WP-Translations Team
 * Author URI:        https://wp-translations.pro/
 * Text Domain:       wpt-custom-mo-file
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright:         2022 WP-Translations Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Check if get_plugin_data() function exists.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Get plugin headers data.
$wpt_customofile_data = get_plugin_data( __FILE__, false, false );

// Set plugin version.
if ( ! defined( 'WPT_CUSTOMOFILE_VERSION' ) ) {
	define( 'WPT_CUSTOMOFILE_VERSION', $wpt_customofile_data['Version'] );
}

// Set plugin name.
if ( ! defined( 'WPT_CUSTOMOFILE_PLUGIN_NAME' ) ) {
	define( 'WPT_CUSTOMOFILE_PLUGIN_NAME', $wpt_customofile_data['Name'] );
}


define( 'WPT_CUSTOMOFILE_SLUG', 'wpt-custom-mo-file' );
define( 'WPT_CUSTOMOFILE_FILE', __FILE__ );
define( 'WPT_CUSTOMOFILE_URL', plugin_dir_url( WPT_CUSTOMOFILE_FILE ) );
define( 'WPT_CUSTOMOFILE_PATH', realpath( plugin_dir_path( WPT_CUSTOMOFILE_FILE ) ) . '/' );
define( 'WPT_CUSTOMOFILE_INC_PATH', realpath( WPT_CUSTOMOFILE_PATH . 'inc' ) . '/' );
define( 'WPT_CUSTOMOFILE_ADMIN_PATH', realpath( WPT_CUSTOMOFILE_INC_PATH . 'admin' ) . '/' );
define( 'WPT_CUSTOMOFILE_ADMIN_UI_PATH', realpath( WPT_CUSTOMOFILE_ADMIN_PATH . 'ui' ) . '/' );
define( 'WPT_CUSTOMOFILE_FUNCTIONS_PATH', realpath( WPT_CUSTOMOFILE_INC_PATH . 'functions' ) . '/' );
define( 'WPT_CUSTOMOFILE_ASSETS_URL', WPT_CUSTOMOFILE_URL . 'assets/' );
define( 'WPT_CUSTOMOFILE_CSS_URL', WPT_CUSTOMOFILE_ASSETS_URL . 'css/' );
define( 'WPT_CUSTOMOFILE_JS_URL', WPT_CUSTOMOFILE_ASSETS_URL . 'js/' );
define( 'WPT_CUSTOMOFILE_IMG_URL', WPT_CUSTOMOFILE_ASSETS_URL . 'images/' );
define( 'WPT_CUSTOMOFILE_LIB_URL', WPT_CUSTOMOFILE_ASSETS_URL . 'lib/' );

/**
 * Tell WP what to do when plugin is loaded.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wpt_customofile_init() {

	load_plugin_textdomain( 'wpt-custom-mo-file', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	if ( is_admin() ) {
		require WPT_CUSTOMOFILE_FUNCTIONS_PATH . 'functions.php';
		require WPT_CUSTOMOFILE_ADMIN_PATH . 'enqueue.php';
		require WPT_CUSTOMOFILE_ADMIN_PATH . 'options.php';
		require WPT_CUSTOMOFILE_ADMIN_UI_PATH . 'options.php';
	}

}
add_action( 'plugins_loaded', 'wpt_customofile_init' );


/**
 * Log available textdomains.
 *
 * @since 1.0.0
 *
 * @param string $domain    Unique identifier for retrieving translated strings.
 * @param string $mo_file   Path to the .mo file.
 *
 * @return void
 */
function wpt_customofile_log_textdomain( $domain, $mo_file ) {

	// Unset unused variable.
	unset( $mo_file );

	if ( ! isset( $GLOBALS['wpt_customofile_text_domains'][ $domain ] ) ) {
		$GLOBALS['wpt_customofile_text_domains'][ $domain ] = $domain;
	}

}
add_action( 'load_textdomain', 'wpt_customofile_log_textdomain', 10, 2 );


/**
 * Tell WP what to do when plugin is activated
 *
 * @since 1.0.0
 *
 * @param boolean $network_wide   Whether to enable the plugin for all sites in the network or just the current site. Multisite only.
 *
 * @return void
 */
function wpt_customofile_activation( $network_wide ) {

	if ( is_multisite() && $network_wide ) {
		global $wpdb;
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			switch_to_blog( $blog_id );
			add_option( 'wpt_customofile_options', array() );
			restore_current_blog();
		}
	} else {
		add_option( 'wpt_customofile_options', array() );
	}

}
register_activation_hook( __FILE__, 'wpt_customofile_activation' );


/**
 * Add wpt_customofile_options when a new blog is create
 *
 * @since 1.0.0
 *
 * @param  int    $blog_id   Blog ID of the created blog.
 * @param  int    $user_id   User ID of the user creating the blog.
 * @param  string $domain    Domain used for the new blog.
 * @param  string $path      Path to the new blog.
 * @param  int    $site_id   Site ID. Only relevant on multi-network installs.
 * @param  array  $meta      Meta data. Used to set initial site options.
 *
 * @return void
 */
function wpt_customofile_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Unset unused variables.
	unset( $user_id, $domain, $path, $site_id, $meta );

	if ( is_plugin_active_for_network( WPT_CUSTOMOFILE_SLUG . '/' . WPT_CUSTOMOFILE_SLUG . '.php' ) ) {
		switch_to_blog( $blog_id );
		add_option( 'wpt_customofile_options', '' );
		restore_current_blog();
	}
}
add_action( 'wpmu_new_blog', 'wpt_customofile_new_blog', 10, 6 );


/**
 * Load overwrites rules.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wpt_customofile_overwrite_domains() {

	$options = get_option( 'wpt_customofile_options' );

	// Since WP 4.7 use get_user_locale().
	$locale = get_user_locale();

	if ( isset( $options['rules'][ $locale ] ) && ! empty( $options['rules'][ $locale ] ) ) {
		foreach ( $options['rules'][ $locale ] as $rule ) {
			if ( 1 === $rule['activate'] && $locale === $rule['language'] ) {
				unload_textdomain( $rule['text_domain'] );
				load_textdomain( $rule['text_domain'], $rule['mo_path'] );
			}
		}
	}

}
add_action( 'init', 'wpt_customofile_overwrite_domains', 0 );
