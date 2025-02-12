<?php
/**
 * WP-Members Installation Functions
 *
 * Functions to install and upgrade WP-Members.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2025  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2025
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Installs or upgrades the plugin.
 *
 * @since 2.2.2
 * @since 3.1.6 Returns $wpmem_settings.
 *
 * @return array $wpmem_settings
 */
function wpmem_do_install() {

	/*
	 * If you need to force an install, set $chk_force = true.
	 *
	 * Important notes:
	 *
	 * 1. This will override any settings you already have for any of the plugin settings.
	 * 2. This will not effect any WP settings or registered users.
	 */

	$chk_force = false;

	// Existing settings are held in a separate array so we can update settings as we go.
	$existing_settings = get_option( 'wpmembers_settings' );
	
	if ( false == $existing_settings || $chk_force == true ) {

		// New install.
		$wpmem_settings = wpmem_install_settings();
		wpmem_install_fields();
		wpmem_install_dialogs();

	} else {
		
		// Upgrade.
		$wpmem_settings = wpmem_upgrade_settings();

		if ( version_compare( $existing_settings['version'], '3.5.2', '<' ) ) {
			wpmem_update_autoload_options();
		}

		if ( version_compare( $existing_settings['version'], '3.5.0', '<' ) ) {
			wpmem_add_profile_to_fields( $existing_settings );
			wpmem_update_user_dirs();
			wpmem_upgrade_user_search_crud_table(); // 3.4.7 fixes(?) a db collation issue with the user search CRUD table
		}
		
		if ( version_compare( $existing_settings['version'], '3.1.1', '<' ) ) {
			wpmem_upgrade_dialogs();
			wpmem_upgrade_captcha();
		}

		// Only run this if DB version < 2.3.0
		if ( version_compare( $existing_settings['db_version'], '2.3.0', '<' ) ) {
			wpmem_upgrade_hidden_transient();
		}
		
		// Only run this if DB version < 2.2.1
		if ( version_compare( $existing_settings['db_version'], '2.2.1', '<' ) ) {
			wpmem_upgrade_woo_reg();
		}

		// Only run these if DB version is < 2.2.0
		if ( version_compare( $existing_settings['db_version'], '2.2.0', '<' ) ) {			
			wpmem_upgrade_fields();
			wpmem_upgrade_product_expiration();
		}

		// Remove options no longer used or needed.
		delete_option( 'wpmembers_install_state' );
		delete_option( 'wpmem_enable_field_sc' );
	}
	
	return $wpmem_settings;
}


/**
 * Updates the existing settings if doing an update.
 *
 * @since 3.0.0
 * @since 3.1.0 Changed from wpmem_update_settings() to wpmem_upgrade_settings().
 * @since 3.5.0 Can only directly update 3.0.0 or higher (92% of installs are 3.2 or greater).
 *
 * @return array $wpmem_newsettings
 */
function wpmem_upgrade_settings() {

	$wpmem_settings = get_option( 'wpmembers_settings' );
	
	if ( ! isset( $wpmem_settings['enable_products'] ) ) {
		$wpmem_settings['enable_products'] = 0;
	}
	
	// reCAPTCHA v1 is obsolete.
	if ( isset( $wpmem_settings['captcha'] ) && 1 == $wpmem_settings['captcha'] ) {
		$wpmem_settings['captcha'] = 3;
	}

	// If old auto excerpt settings exists, update it.
	if ( isset( $wpmem_settings['autoex']['auto_ex'] ) ) {
		// Update Autoex setting.
		if ( $wpmem_settings['autoex']['auto_ex'] == 1 || $wpmem_settings['autoex']['auto_ex'] == "1" ) {
			// If Autoex is set, move it to posts/pages.
			$wpmem_settings['autoex']['post'] = array( 'enabled' => 1, 'length' => $wpmem_settings['autoex']['auto_ex_len'] );
			$wpmem_settings['autoex']['page'] = array( 'enabled' => 1, 'length' => $wpmem_settings['autoex']['auto_ex_len'] );
		} else {
			// If it is not turned on (!=1), set it to off in new setting (-1).
			$wpmem_settings['autoex']['post'] = array( 'enabled' => 0, 'length' => '' );
			$wpmem_settings['autoex']['page'] = array( 'enabled' => 0, 'length' => '' );
		}
		unset( $wpmem_settings['autoex']['auto_ex'] );
		unset( $wpmem_settings['autoex']['auto_ex_len'] );
	}
	
	// If post types settings does not exist, set as empty array.
	if ( ! isset( $wpmem_settings['post_types'] ) ) {
		$wpmem_settings['post_types'] = array();
	}
	
	// If form tags is not set, add default.
	if ( ! isset( $wpmem_settings['form_tags'] ) ) {
		$wpmem_settings['form_tags'] = array( 'default' => 'Registration Default' );
	}
	
	// If email is set in the settings array, change it back to the pre-3.1 option.
	if ( isset( $wpmem_settings['email'] ) ) {
		$from = ( is_array( $wpmem_settings['email'] ) ) ? $wpmem_settings['email']['from']      : '';
		$name = ( is_array( $wpmem_settings['email'] ) ) ? $wpmem_settings['email']['from_name'] : '';
		update_option( 'wpmembers_email_wpfrom', $from, false );
		update_option( 'wpmembers_email_wpname', $name, false );
		unset( $wpmem_settings['email'] );
	}
	
	// @since 3.3.0 Upgrade stylesheet setting.
	// @since 3.5.0 Simplified. Only two options: default|use_custom.
	$wpmem_settings = wpmem_upgrade_style_setting( $wpmem_settings );

	// Change 3.4.9 field shortcode option.
	if ( ! isset( $wpmem_settings['shortcode'] ) ) {
		$field_sc = get_option( 'wpmem_enable_fields_sc' );
		$wpmem_settings['shortcodes']['enable_field'] = ( $field_sc ) ? intval( $field_sc ) : 2;
		delete_option( 'wpmem_enable_fields_sc' );
	}
	
	// Version number should be updated no matter what.
	$wpmem_settings['version']    = WPMEM_VERSION;
	$wpmem_settings['db_version'] = WPMEM_DB_VERSION;

	$wpmem_settings['install_state'] = 'update_pending';
	
	update_option( 'wpmembers_settings', $wpmem_settings, true );
	return $wpmem_settings;
}

/**
 * Checks the dialogs array for necessary changes.
 *
 * @since 2.9.3
 * @since 3.0.0 Changed from update_dialogs() to wpmem_update_dialogs().
 * @since 3.1.0 Changed from wpmem_update_dialogs() to wpmem_upgrade_dialogs().
 * @since 3.1.1 Converts numeric dialog array to associative.
 */
function wpmem_upgrade_dialogs() {

	$wpmem_dialogs = get_option( 'wpmembers_dialogs' );
	
	if ( ! array_key_exists( 'restricted_msg', $wpmem_dialogs ) ) {
		// Update is needed.
		$new_arr  = array();
		$new_keys = array( 'restricted_msg', 'user', 'email', 'success', 'editsuccess', 'pwdchangerr', 'pwdchangesuccess', 'pwdreseterr', 'pwdresetsuccess' );
		foreach ( $wpmem_dialogs as $key => $val ) {
			$new_arr[ $new_keys[ $key ] ] = $val;
		}
		update_option( 'wpmembers_dialogs', $new_arr, false );
	}

	return;
}

/**
 * Downgrades dialogs array for pre-3.1.1 version rollback.
 *
 * @since 3.1.1
 */
function wpmem_downgrade_dialogs() {
	
	$wpmem_dialogs = get_option( 'wpmembers_dialogs' );
	
	if ( array_key_exists( 'restricted_msg', $wpmem_dialogs ) ) {
		// Update is needed.
		$new_arr  = array();
		$i = 0;
		foreach ( $wpmem_dialogs as $key => $val ) {
			$new_arr[ $i ] = $val;
			$i++;
		}
		update_option( 'wpmembers_dialogs', $new_arr, false );
	}

	return;
}

/**
 * Checks the captcha settings and updates accordingly.
 *
 * Was update_captcha() since 2.9.5, changed to wpmem_update_captcha() in 3.0.
 *
 * @since 2.9.5
 * @since 3.0.0 Changed from update_captcha() to wpmem_update_captcha().
 * @since 3.1.0 Changed from wpmem_update_captcha() to wpmem_upgrade_captcha().
 */
function wpmem_upgrade_captcha() {

	$captcha_settings = get_option( 'wpmembers_captcha' );

	// If there captcha settings, update them.
	if ( $captcha_settings && ! array_key_exists( 'recaptcha', $captcha_settings ) ) {

		// Check to see if the array keys are numeric.
		$is_numeric = false;
		foreach ( $captcha_settings as $key => $setting ) {
			$is_numeric = ( is_int( $key ) ) ? true : $is_numeric;
		}

		if ( $is_numeric ) {
			$new_captcha = array();
			// These are old recaptcha settings.
			$new_captcha['recaptcha']['public']  = $captcha_settings[0];
			$new_captcha['recaptcha']['private'] = $captcha_settings[1];
			$new_captcha['recaptcha']['theme']   = $captcha_settings[2];
			update_option( 'wpmembers_captcha', $new_captcha, false );
		}
	}
	return;
}

/**
 * Does install of default settings.
 *
 * @since 3.1.5
 * @since 3.1.6 Returns $wpmem_settings
 *
 * @return array $wpmem_settings
 */
function wpmem_install_settings() {
		
	$wpmem_settings = array(
		'install_state' => 'new_install',
		'version'    => WPMEM_VERSION,
		'db_version' => WPMEM_DB_VERSION,
		'block'      => array(
			'post' => ( is_multisite() ) ? 0 : 1,
			'page' => 0,
		),
		'show_excerpt' => array(
			'post' => 0,
			'page' => 0,
		),
		'show_reg' => array(
			'post' => 1,
			'page' => 1,
		),
		'show_login' => array(
			'post' => 1,
			'page' => 1,
		),
		'autoex' => array(
			'post' => array( 'enabled' => 0, 'length' => '' ),
			'page' => array( 'enabled' => 0, 'length' => '' ),
		),
		'enable_products' => 0,
		'clone_menus'     => 0,
		'notify'          => 0,
		'mod_reg'         => 0,
		'captcha'         => 0,
		'use_exp'         => 0,
		'use_trial'       => 0,
		'warnings'        => 0,
		'user_pages'      => array(
			'profile'  => '',
			'register' => '',
			'login'    => '',
		),
		'cssurl'          => '',
		'select_style'    => 'default', // @todo Schedule for deprecation.
		'attrib'          => 0,
		'post_types'      => array(),
		'form_tags'       => array( 'default' => 'Registration Default' ),
		'shortcodes'      => array( 'enable_field' => 0 ),
	);
	
	// Using update_option to allow for forced update.
	update_option( 'wpmembers_settings', $wpmem_settings, true );
	
	return $wpmem_settings;
}

/**
 * Installs default fields.
 *
 * @since 3.1.5
 *
 * @return array $fields {
 *    @type array {
 *        order, 
 *        label, 
 *        meta key, 
 *        type, 
 *        display, 
 *        required, 
 *        native, 
 *        checked value, 
 *        checked by default,
 *     }
 * }
 */
function wpmem_install_fields() {
	$fields = array(
		array( 0,  'Choose a Username', 'username',          'text',     'y', 'y', 'y', 'profile'=>0 ),
		array( 1,  'First Name',        'first_name',        'text',     'y', 'y', 'y', 'profile'=>1 ),
		array( 2,  'Last Name',         'last_name',         'text',     'y', 'y', 'y', 'profile'=>1 ),
		array( 3,  'Address 1',         'billing_address_1', 'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 4,  'Address 2',         'billing_address_2', 'text',     'y', 'n', 'n', 'profile'=>1 ),
		array( 5,  'City',              'billing_city',      'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 6,  'State',             'billing_state',     'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 7,  'Zip',               'billing_postcode',  'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 8,  'Country',           'billing_country',   'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 9,  'Phone',             'billing_phone',     'text',     'y', 'y', 'n', 'profile'=>1 ),
		array( 10, 'Email',             'user_email',        'email',    'y', 'y', 'y', 'profile'=>1 ),
		array( 11, 'Confirm Email',     'confirm_email',     'email',    'n', 'n', 'n', 'profile'=>1 ),
		array( 12, 'Website',           'user_url',          'url',      'n', 'n', 'y', 'profile'=>1 ),
		array( 13, 'Biographical Info', 'description',       'textarea', 'n', 'n', 'y', 'profile'=>1 ),
		array( 14, 'Password',          'password',          'password', 'n', 'n', 'n', 'profile'=>0 ),
		array( 15, 'Confirm Password',  'confirm_password',  'password', 'n', 'n', 'n', 'profile'=>0 ),
		array( 16, 'Terms of Service',  'tos',               'checkbox', 'n', 'n', 'n', 'agree', 'n', 'profile'=>0 ),
	);
	update_option( 'wpmembers_fields', $fields, false ); // using update_option to allow for forced update
	return $fields;
}

/**
 * Installs default dialogs.
 *
 * @since 3.1.5
 */
function wpmem_install_dialogs() {
	$wpmem_dialogs_arr = array(
		'restricted_msg'   => "This content is restricted to site members.  If you are an existing user, please log in.  New users may register below.",
		'user'             => "Sorry, that username is taken, please try another.",
		'email'            => "Sorry, that email address already has an account.<br />Please try another.",
		'success'          => "Congratulations! Your registration was successful.<br /><br />You may now log in using the password that was emailed to you.",
		'editsuccess'      => "Your information was updated!",
		'pwdchangerr'      => "Passwords did not match.<br /><br />Please try again.",
		'pwdchangesuccess' => "Password successfully changed!",
		'pwdreseterr'      => "Either the username or email address do not exist in our records.",
		'pwdresetsuccess'  => "An email with instructions to update your password has been sent to the email address on file for your account.",
	);
	// Insert TOS dialog placeholder.
	$dummy_tos = "Put your TOS (Terms of Service) text here.  You can use HTML markup.";
	update_option( 'wpmembers_tos', $dummy_tos, false );
	update_option( 'wpmembers_dialogs', $wpmem_dialogs_arr, false ); // using update_option to allow for forced update
}

/**
 * Upgrades fields settings.
 *
 * @since 3.2.0
 */
function wpmem_upgrade_fields() {
	$fields = get_option( 'wpmembers_fields' );
	$old_style = false;
	foreach ( $fields as $key => $val ) {
		if ( is_numeric( $key ) ) {
			$old_style = true;
			$check_array[] = $val[2];
		}
	}
	if ( $old_style && ! in_array( 'username', $check_array ) ) {
		$username_array = array( 0, 'Choose a Username', 'username', 'text', 'y', 'y', 'y' );
		array_unshift( $fields, $username_array );
		update_option( 'wpmembers_fields', $fields, false );
	}
}

/**
 * Upgrades the stylesheet setting from pre-3.0.
 *
 * This is a basic fix for users who have a WP-Members packaged stylesheet saved
 * with the full URL. I believe 90% or more users simply use the default stylesheet
 * so this should handle most updates.
 *
 * @since 3.2.7
 * @since 3.5.0 Selector no longer available, so there's only one default, otherwise custom.
 *
 * @param array $settings
 */
function wpmem_upgrade_style_setting( $settings ) {
	if ( 'generic-no-float' == $settings['select_style'] ) {
		// This is the default style.
		$settings['cssurl'] = '';
		$settings['select_style'] = 'default';
	} else {
		// If there is a custom URL, keep it.
		if ( 'use_custom' == $settings['select_style'] ) {
			$settings['cssurl'] = $settings['cssurl'];
			$settings['select_style'] = 'use_custom';
		} else {
			// If it is from the selector but not default, set to "use_custom" and set the url.
			// NOTE: Set select_style last because we're using the saved value to build the cssurl setting first.
			$url = plugin_dir_url ( __DIR__ );
			$settings['cssurl'] = trailingslashit( $url ) . 'assets/css/forms/' . $settings['select_style'] . '.min.css';
			$settings['select_style'] = 'use_custom';
		}
	}
	return $settings;
}

/**
 * Upgrades product expiration meta from a single meta array
 * to individual meta for each product. Single meta array is
 * still maintained for legacy reasons and rollback possiblity.
 *
 * @since 3.3.0
 */
function wpmem_upgrade_product_expiration() {
	$users = get_users( array( 'fields'=>'ID' ) );
	foreach ( $users as $user_id ) {
		$products = get_user_meta( $user_id, '_wpmem_products', true );
		
		// If the user has legacy products, update to new single meta.
		if ( $products ) {
			// Update each product meta.
			foreach ( $products as $key => $product ) {
				// If it's an expiration product, 
				if ( ! is_bool( $product ) ) {
					if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $product ) !== FALSE ) {
						$value = strtotime( $product );
					}
				} else {
					$value = $product;
				}

				// Save new meta
				if ( $key ) {
					update_user_meta( $user_id, '_wpmem_products_' . $key, $value );
				}
			}
		}
	}
}

/**
 * Adds woo_reg settings.
 *
 * @since 3.3.8
 */
function wpmem_upgrade_woo_reg() {
	$wpmem_settings = get_option( 'wpmembers_settings' );
	
	if ( ! isset( $wpmem_settings['woo'] ) ) {
		$wpmem_settings['woo'] = array(
			'add_my_account_fields' => 0,
			'add_checkout_fields' => 0,
			'add_update_fields' => 0,
			'product_restrict' => 0,
		);
		update_option( 'wpmembers_settings', $wpmem_settings, true );
	}
}

function wpmem_upgrade_hidden_transient() {
	if ( ! class_exists( 'WP_Members' ) ) {
		require_once( 'class-wp-members.php' );
	}
	$temp_obj = new WP_Members;
	$temp_obj->update_hidden_posts();
	delete_transient( '_wpmem_hidden_posts' );
}

function wpmem_upgrade_user_search_crud_table() {
	global $wpdb;
	// Drop old table if it exists.
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wpmembers_user_search_crud;" );
	// If the table does not exist, create the table to store the meta keys.
	$wpdb->query( "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wpmembers_user_search_crud (meta_key VARCHAR(255) NOT NULL);" );
}

function wpmem_add_profile_to_fields( $existing_settings ) {

	if ( ! function_exists( 'rktgk_wc_checkout_fields' ) ) {
		include_once( 'vendor/rocketgeek-utilities/loader.php' );
	}
	
	$fields = get_option( 'wpmembers_fields' );
	$skips = array( 'username', 'user_login', 'password', 'confirm_password', 'tos' );
	$woo_skips = array_merge( rktgk_wc_checkout_fields(), array( 'username', 'user_login', 'user_email', 'confirm_email', 'password', 'confirm_password' ) );
	$wpmem_fields_wcchkout = array();
	$wpmem_fields_wcaccount = array();
	
	foreach ( $fields as $key => $field ) {
		$meta_key = $field[2];
		$reg_val = ( "y" == $field[4] ) ? true : false;
		$fields[ $key ]['profile'] = ( ! in_array( $meta_key, $skips ) ) ? $reg_val : false;

		if ( isset( $existing_settings['woo']['add_checkout_fields'] ) && 1 == $existing_settings['woo']['add_checkout_fields'] ) {
			if ( 'file' !=  $field[3] && 'image' != $field[3] && ! in_array( $meta_key, $woo_skips ) && $reg_val ) {
				$wpmem_fields_wcchkout[] = $meta_key;
			}
		}
		if ( isset( $existing_settings['woo']['add_my_account_fields'] ) && 1 == $existing_settings['woo']['add_my_account_fields'] ) {
			if ( 'file' !=  $field[3] && 'image' != $field[3] && ! in_array( $meta_key, $woo_skips ) && $reg_val ) {
				$wpmem_fields_wcaccount[] = $meta_key;
			}
		}
	}
	
	update_option( 'wpmembers_fields', $fields, false );
	
	if ( ! empty( $wpmem_fields_wcchkout ) ) {
		update_option( 'wpmembers_wcchkout_fields', $wpmem_fields_wcchkout, false );
	}
	
	if ( ! empty( $wpmem_fields_wcaccount ) ) {
		update_option( 'wpmembers_wcacct_fields', $wpmem_fields_wcaccount, false );
	}
}


function wpmem_onboarding_init( $action ) {
	global $wpmem_onboarding;
    $settings = array(
        'product_action'  => $action,
    );
    $wpmem_onboarding = new WP_Members_Installer( $settings );
}

function wpmem_onboarding_new_install() {
	wpmem_onboarding_init( 'install' );
}

function wpmem_onboarding_pending_update() {
	wpmem_onboarding_init( 'update' );
}

function wpmem_onboarding_finalize() {
	global $wpmem, $wpmem_onboarding;
	if ( isset( $_POST['optin'] ) ) {
		if ( 'update_pending' == $wpmem->install_state ) {
			$wpmem_onboarding->deploy( 'wp-members', $wpmem->path . 'wp-members.php', 'update' );
		} else {
			$wpmem_onboarding->deploy( 'wp-members', $wpmem->path . 'wp-members.php', 'activate' );
		}
		update_option( 'wpmembers_optin', 1, false );
	} else {
		update_option( 'wpmembers_optin', 0, false );
	}

	$update_settings = get_option( 'wpmembers_settings' );
	if ( 'update_pending' == $wpmem->install_state || 'finalize' == wpmem_get( 'wpmem_onboarding_action' ) ) {
		$wpmem->install_state = $update_settings['install_state'] = 'install_complete_' . $wpmem->version . '_' . time();
	}
	update_option( 'wpmembers_settings', $update_settings, true );
}

function wpmem_plugin_deactivate() {
    global $wpmem, $wpmem_onboarding;
	$optin = get_option( 'wpmembers_optin' );
	if ( 1 == $optin ) {
		wpmem_onboarding_init( 'deactivate' );
		$wpmem_onboarding->deploy( 'wp-members', $wpmem->path . 'wp-members.php', 'deactivate' );
	}
}

function wpmem_update_user_dirs() {

	include_once 'api/api-utilities.php';

	$upload_vars = wpmem_upload_dir();

	$users_to_check = get_users( array( 'fields'=>'ID' ));

	if ( file_exists( $upload_vars['wpmem_user_files_dir'] ) ) {

		// Add indexes and htaccess
		wpmem_create_file( array(
			'path'     => $upload_vars['wpmem_base_dir'],
			'name'     => 'index.php',
			'contents' => "<?php // Silence is golden."
		) );
		
		wpmem_create_file( array(
			'path'     => $upload_vars['wpmem_user_files_dir'],
			'name'     => '.htaccess',
			'contents' => "Options -Indexes"
		) );
	}

	if ( file_exists( $upload_vars['wpmem_user_files_dir'] ) ) {

		// Loop through users to update user dirs.
		foreach ( $users_to_check as $user ) {
			$wpmem_user_dir = trailingslashit( $upload_vars['wpmem_user_files_dir'] ) . $user;
			if ( is_dir( $wpmem_user_dir ) ) {
				wpmem_create_file( array(
					'path'     => $wpmem_user_dir,
					'name'     => 'index.php',
					'contents' => "<?php // Silence is golden."
				) );
			}
		}
	}
}

function wpmem_update_autoload_options() {
	$wpmem_options = array(
		"wpmembers_settings" => true,
		"wpmem_hidden_posts" => true,
		"wpmem_memberships" => true,
		"wpmembers_optin" => false,
		"wpmembers_fields" => false,
		"wpmembers_dialogs" => false,
		"wpmembers_captcha" => false,
		"wpmembers_tos" => false,
		"wpmembers_export" => false,
		"wpmembers_dropins" => false,
		"wpmembers_wcchkout_fields" => false,
		"wpmembers_wcacct_fields" => false,
		"wpmembers_utfields" => false,
		"wpmembers_usfields" => false,
		"wpmembers_email_newreg" => false,
		"wpmembers_email_newmod" => false,
		"wpmembers_email_appmod" => false,
		"wpmembers_email_repass" => false,
		"wpmembers_email_footer" => false,
		"wpmembers_email_notify" => false,
		"wpmembers_email_wpfrom" => false,
		"wpmembers_email_wpname" => false,
		"wpmembers_email_html" => false,
		"wpmembers_email_getuser" => false,
		"wpmembers_email_validated" => false,
	);
	
	// Update wpmem options autoload values.
	wp_set_option_autoload_values( $wpmem_options );
	
	// Delete pre-3.x options if they exist.
	delete_option( "wpmembers_msurl"  );
	delete_option( "wpmembers_regurl" );
	delete_option( "wpmembers_logurl" );
	delete_option( "wpmembers_cssurl" );
	delete_option( "wpmembers_style"  );
	delete_option( "wpmembers_autoex" );
	delete_option( "wpmembers_attrib" );
}

class WP_Members_Installer {

	public $settings;
	public $product_action;
	public $opt_in_callback;
	public $opt_in_callback_args;

	public function __construct( $settings ) {
		$this->settings = $settings;

		foreach ( $settings as $key => $value ) {
			$this->{$key} = $value;
		}

		add_action( 'admin_notices', array( $this, 'onboarding_notice' ) );
	}

	public function deploy( $slug, $product_file, $action ) {
		global $wpmem;
		require_once $wpmem->path . 'includes/vendor/rocketgeek-plugin-manager/class-rocketgeek-deploy-plugin.php';
		//$rgut = new RocketGeek_Deploy_Plugin_v1( $slug, $product_file, $action, 'plugin' );
	}

	public function onboarding_notice() {
		global $wpmem;

		$show_release_notes = true;
		$release_notes_link = "https://rocketgeek.com/release-announcements/wp-members-3-5-2/";

		if ( 'new_install' == $wpmem->install_state ) {
			$notice_heading = __( 'Thank you for installing WP-Members, the original WordPress membership plugin.', 'wp-members' );
			$notice_button  = __( 'Complete plugin setup', 'wp-members' );
			$show_release_notes = false;
		}

		if ( 'update_pending' == $wpmem->install_state ) {
			$notice_heading = __( 'Thank you for updating WP-Members, the original WordPress membership plugin.', 'wp-members' );
			$notice_button  = __( 'Complete the update', 'wp-members' );
		}

		if ( 'finalize' == wpmem_get( 'wpmem_onboarding_action' ) ) {

		}

		include_once $wpmem->path . 'includes/admin/partials/onboarding_notice.php';
	}

	public function has_user_opted_in() {
		global $wpmem;
		if ( 1 == $wpmem->optin ) {
			return true;
		}

		return false;
	}
}
// End of file.