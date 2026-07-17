<?php
/**
 * WP-Members Deprecated Functions
 *
 * These functions have been deprecated and are now obsolete.
 * Use alternative functions as these will be removed in a 
 * future release.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2026  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package   WP-Members
 * @author    Chad Butler 
 * @copyright 2006-2026
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8.0
 * @since 3.1.0 Converted to wrapper for create_form_field() in utlities object.
 * @deprecated 3.2.0 Use wpmem_form_field() instead. Note: some extensions still use this.
 *
 * @global object $wpmem    The WP_Members object class.
 * @param  string $name     The name of the field.
 * @param  string $type     The field type.
 * @param  string $value    The default value for the field.
 * @param  string $valtochk Optional for comparing the default value of the field.
 * @param  string $class    Optional for setting a specific CSS class for the field.
 * @return string $str      The field returned as a string.
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' ) {
	$args = array(
		'name'     => $name,
		'type'     => $type,
		'value'    => $value,
		'compare'  => $valtochk,
		'class'    => $class,
	);
	return wpmem_form_field( $args );
}
endif;

if ( ! function_exists( 'wpmem_inc_regemail' ) ):
/**
 * Builds emails for the user.
 *
 * @since 1.8.0
 * @since 2.7.4 Added wpmem_email_headers and individual body/subject filters.
 * @since 2.9.7 Major overhaul, added wpmem_email_filter filter.
 * @since 3.1.0 Can filter in custom shortcodes with wpmem_email_shortcodes.
 * @since 3.1.1 Added $custom argument for custom emails.
 * @deprecated 3.2.0 Use wpmem_email_to_user() instead.
 *
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param  int    $user_ID              The User's ID.
 * @param  string $password             Password from the registration process.
 * @param  string $toggle               Toggle indicating the email being sent (newreg|newmod|appmod|repass|getuser).
 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param  array  $fields               Array of the registration data (defaults to null).
 * @param  array  $custom               Array of custom email information (defaults to null).
 */
function wpmem_inc_regemail( $user_id, $password, $toggle, $wpmem_fields = null, $field_data = null, $custom = null ) {
	global $wpmem;
	wpmem_write_log( "wpmem_inc_regemail() is deprecated since WP-Members 3.2.0. Use wpmem_email_to_user() instead" );
	wpmem_email_to_user( $user_id, $password, $toggle, $wpmem_fields, $field_data, $custom );
	return;
}
endif;

if ( ! function_exists( 'wpmem_inc_changepassword' ) ):
/**
 * Change Password Dialog.
 *
 * Loads the form for changing password.
 *
 * @since 2.0.0
 * @since 3.2.0 Now an alias for $wpmem->forms->do_changepassword_form()
 * @deprecated 3.3.0 Use wpmem_change_password_form() instead.
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html for the change password form.
 */
function wpmem_inc_changepassword() {
	wpmem_write_log( "wpmem_inc_changepassword() is deprecated as of WP-Members 3.3.0. Use wpmem_inc_changepassword() instead" );
	return wpmem_changepassword_form();
}
endif;

if ( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function.
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 * @since 2.7.2 Added pre/post process actions.
 * @since 2.8.2 Added validation and data filters.
 * @since 2.9.3 Added validation for multisite.
 * @since 3.0.0 Moved from wp-members-register.php to /inc/register.php.
 * @deprecated 3.3.0 Use wpmem_user_register() instead.
 *
 * @global int    $user_ID
 * @global object $wpmem
 * @global string $wpmem_themsg
 * @global array  $userdata
 *
 * @param  string $tag           Identifies 'register' or 'update'.
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $tag ) {
	return wpmem_user_register( $tag );
} // End registration function.
endif;

if ( ! function_exists( 'wpmem_get_captcha_err' ) ):
/**
 * Generate reCAPTCHA error messages.
 *
 * @since 2.4
 * @deprecated 3.3.0 No replacement exists.
 *
 * @param  string $wpmem_captcha_err The response from the reCAPTCHA API.
 * @return string $wpmem_captcha_err The appropriate error message.
 */
function wpmem_get_captcha_err( $wpmem_captcha_err ) {

	switch ( $wpmem_captcha_err ) {

	case "invalid-site-public-key":
		$wpmem_captcha_err = esc_html__( 'We were unable to validate the public key.', 'wp-members' );
		break;

	case "invalid-site-public-key":
		$wpmem_captcha_err = esc_html__( 'We were unable to validate the private key.', 'wp-members' );
		break;

	case "invalid-request-cookie":
		$wpmem_captcha_err = esc_html__( 'The challenge parameter of the verify script was incorrect.', 'wp-members' );
		break;

	case "incorrect-captcha-sol":
		$wpmem_captcha_err = esc_html__( 'The CAPTCHA solution was incorrect.', 'wp-members' );
		break;

	case "verify-params-incorrect":
		$wpmem_captcha_err = esc_html__( 'The parameters to verify were incorrect', 'wp-members' );
		break;

	case "invalid-referrer":
		$wpmem_captcha_err = esc_html__( 'reCAPTCHA API keys are tied to a specific domain name for security reasons.', 'wp-members' );
		break;

	case "recaptcha-not-reachable":
		$wpmem_captcha_err = esc_html__( 'The reCAPTCHA server was not reached.  Please try to resubmit.', 'wp-members' );
		break;

	case 'really-simple':
		$wpmem_captcha_err = esc_html__( 'You have entered an incorrect code value. Please try again.', 'wp-members' );
		break;
	}

	return $wpmem_captcha_err;
}
endif;

if ( current_user_can( 'list_users' ) ) {
	add_action( 'wpmem_admin_after_profile',  'wpmem_deprecated_show_expiration', 8 );
}
/**
 * Adds user expiration to the user profile.
 *
 * @since 3.1.1
 * @since 3.2.0 Moved to WP_Members_User_Profile object
 * @deprecated 3.6.0 No replacement exists.
 *
 * @global object $wpmem
 * @param  int    $user_id
 */
function wpmem_deprecated_show_expiration( $user_id ) {

	global $wpmem;
	/*
	* If using subscription model, show expiration.
	* If registration is moderated, this doesn't show 
	* if user is not active yet.
	*/
	if ( wpmem_is_exp_enabled() && $wpmem->use_exp == 1 ) {
		if ( ( $wpmem->mod_reg == 1 &&  get_user_meta( $user_id, 'active', true ) == 1 ) || ( $wpmem->mod_reg != 1 ) ) {
			if ( function_exists( 'wpmem_a_extend_user' ) ) {
				wpmem_a_extend_user( $user_id );
			}
		}
	} 
} 