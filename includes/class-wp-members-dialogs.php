<?php
/**
 * The WP_Members_Dialogs Class.
 *
 * This class contains  functions for handling dialongs and messaging..
 *
 * @package WP-Members
 * @subpackage WP_Members_Dialogs Object Class
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Dialogs {
	
	function __construct() {
		if ( 1 == get_option( 'wpmem_legacy_dialogs' ) ) {
			add_filter( 'wpmem_msg_defaults', array( $this, 'map_deprecated_dialogs' ), 10, 3 );
		}
	}
	
	/**
	 * Returns a requested text string.
	 *
	 * This function manages all of the front-end facing text.
	 * All defaults can be filtered using wpmem_default_text_strings.
	 *
	 * @since 3.1.0
	 *
	 * @global object $wpmem
	 *
	 * @param  string $str
	 * @return string $text
	 */	
	function get_text( $str ) {
		global $wpmem;
		
		// Default Form Fields.
		$default_form_fields = array(
			'first_name'       => esc_html__( 'First Name', 'wp-members' ),
			'last_name'        => esc_html__( 'Last Name', 'wp-members' ),
			'addr1'            => esc_html__( 'Address 1', 'wp-members' ),
			'addr2'            => esc_html__( 'Address 2', 'wp-members' ),
			'city'             => esc_html__( 'City', 'wp-members' ),
			'thestate'         => esc_html__( 'State', 'wp-members' ),
			'zip'              => esc_html__( 'Zip', 'wp-members' ),
			'country'          => esc_html__( 'Country', 'wp-members' ),
			'phone1'           => esc_html__( 'Day Phone', 'wp-members' ),
			'user_email'       => esc_html__( 'Email', 'wp-members' ),
			'confirm_email'    => esc_html__( 'Confirm Email', 'wp-members' ),
			'user_url'         => esc_html__( 'Website', 'wp-members' ),
			'description'      => esc_html__( 'Biographical Info', 'wp-members' ),
			'password'         => esc_html__( 'Password', 'wp-members' ),
			'confirm_password' => esc_html__( 'Confirm Password', 'wp-members' ),
			'tos'              => esc_html__( 'TOS', 'wp-members' ),
			'tos_alt'          => esc_html__( 'Terms of Service', 'wp-members' ),
		);
		
		/*
		 * Strings to be added or removed in future versions, included so they will
		 * be in the translation template.
		 * @todo Check whether any of these should be removed.
		 */
		$benign_strings = array(
			__( 'No fields selected for deletion', 'wp-members' ),
			__( 'You are not logged in.', 'wp-members' ), // Technically removed 3.5
		);
	
		$defaults = array(
			
			// Login form.
			'login_heading'        => esc_html__( 'Existing Users Log In', 'wp-members' ),
			'login_username'       => esc_html__( 'Username or Email', 'wp-members' ),
			'login_password'       => esc_html__( 'Password', 'wp-members' ),
			'login_button'         => esc_html__( 'Log In', 'wp-members' ),
			'remember_me'          => esc_html__( 'Remember Me', 'wp-members' ),
			'forgot_link_before'   => esc_html__( 'Forgot password?', 'wp-members' ) . '&nbsp;',
			'forgot_link'          => esc_html__( 'Click here to reset', 'wp-members' ),
			'register_link_before' => esc_html__( 'New User?', 'wp-members' ) . '&nbsp;',
			'register_link'        => esc_html__( 'Click here to register', 'wp-members' ),
			'reconfirm_link_before' => esc_html__( 'Resend confirmation link?', 'wp-members' ) . '&nbsp;',
			'reconfirm_link'        => esc_html__( 'Click here', 'wp-members' ),
			
			// Password change form.
			'pwdchg_heading'       => esc_html__( 'Change Password', 'wp-members' ),
			'pwdchg_password1'     => esc_html__( 'New password', 'wp-members' ),
			'pwdchg_password2'     => esc_html__( 'Confirm new password', 'wp-members' ),
			'pwdchg_button'        => esc_html__( 'Update Password', 'wp-members' ),
			'pwdchg_update'        => esc_html__( 'Password successfully changed!', 'wp-members' ),
			
			// Password reset form.
			'pwdreset_heading'     => esc_html__( 'Reset Forgotten Password', 'wp-members' ),
			// @note As of version 3.5.0, old password reset is obsolete.
			//       The field label used is "login_username" (see above).
			//'pwdreset_username'    => esc_html__( 'Username', 'wp-members' ),
			//'pwdreset_email'       => esc_html__( 'Email', 'wp-members' ),
			'pwdreset_button'      => esc_html__( 'Reset Password' ),
			'username_link_before' => esc_html__( 'Forgot username?', 'wp-members' ) . '&nbsp;',
			'username_link'        => esc_html__( 'Click here', 'wp-members' ),
			
			// Retrieve username form.
			'username_heading'     => esc_html__( 'Retrieve username', 'wp-members' ),
			'username_email'       => esc_html__( 'Email Address', 'wp-members' ),
			'username_button'      => esc_html__( 'Retrieve username', 'wp-members' ),

			// Reconfirm form.
			'reconfirm_heading'    => esc_html__( 'Resend confirmation email', 'wp-members' ),
			'reconfirm_button'     => esc_html__( 'Resend confirmation', 'wp-members' ),
			'reconfirm_success'    => esc_html__( 'A new confirmation link has been sent. Be sure to check your spam folder.', 'wp-members' ),
			
			// Register form.
			'register_heading'     => esc_html__( 'New User Registration', 'wp-members' ),
			'register_username'    => esc_html__( 'Choose a Username', 'wp-members' ),
			'register_rscaptcha'   => esc_html__( 'Input the code:', 'wp-members' ),
			'register_tos'         => esc_html__( 'Please indicate that you agree to the %s Terms of Service %s', 'wp-members' ), // @note: if default changes, default check after wpmem_tos_link_txt must change.
			'register_clear'       => esc_html__( 'Reset Form', 'wp-members' ),
			'register_submit'      => esc_html__( 'Register', 'wp-members' ),
			'register_req_mark'    => '<span class="req">*</span>',
			'register_required'    => '<span class="req">*</span>' . esc_html__( 'Required field', 'wp-members' ),
			
			// User profile update form.
			'profile_heading'      => esc_html__( 'Edit Your Information', 'wp-members' ),
			'profile_username'     => esc_html__( 'Username', 'wp-members' ),
			'profile_submit'       => esc_html__( 'Update Profile', 'wp-members' ),
			'profile_upload'       => esc_html__( 'Update this file', 'wp-members' ),
			
			// Error messages and dialogs.
			'login_failed_heading' => esc_html__( 'Login Failed!', 'wp-members' ), // @deprecated 3.4.0
			'login_failed'         => esc_html__( 'You entered an invalid username or password.', 'wp-members' ),
			'login_failed_link'    => esc_html__( 'Click here to continue.', 'wp-members' ), // @deprecated 3.4.0
			'pwdchangempty'        => esc_html__( 'Password fields cannot be empty', 'wp-members' ),
			'usernamefailed'       => esc_html__( 'Sorry, that email address was not found.', 'wp-members' ),
			'usernamesuccess'      => esc_html__( 'An email was sent to %s with your username.', 'wp-members' ),
			'reg_empty_field'      => esc_html__( 'Sorry, %s is a required field.', 'wp-members' ),
			'reg_valid_email'      => esc_html__( 'You must enter a valid email address.', 'wp-members' ),
			'reg_non_alphanumeric' => esc_html__( 'The username cannot include non-alphanumeric characters.', 'wp-members' ),
			'reg_empty_username'   => esc_html__( 'Sorry, username is a required field', 'wp-members' ),
			'reg_username_taken'   => esc_html__( 'Sorry, that username is taken. Please try another.', 'wp-members' ),
			'reg_email_taken'      => esc_html__( 'Sorry, that email address already has an account. Please try another.', 'wp-members' ),
			'reg_password_match'   => esc_html__( 'Passwords did not match.', 'wp-members' ),
			'reg_email_match'      => esc_html__( 'Emails did not match.', 'wp-members' ),
			'reg_empty_captcha'    => esc_html__( 'You must complete the CAPTCHA form.', 'wp-members' ),
			'reg_invalid_captcha'  => esc_html__( 'CAPTCHA was not valid.', 'wp-members' ),
			'reg_generic'          => esc_html__( 'There was an error processing the form.', 'wp-members' ),
			'reg_captcha_err'      => esc_html__( 'There was an error with the CAPTCHA form.', 'wp-members' ),
			'reg_file_type'        => esc_html__( 'Sorry, you can only upload the following file types for the %s field: %s.', 'wp-members' ),
			'profile_update'       => esc_html__( 'Your information was updated!', 'wp-members' ),
			'login_not_confirmed'  => esc_attr__( '%sERROR%s: Account not confirmed. %sRequest a new confirmation link%s.', 'wp-members' ),
			
			// Links.
			'profile_edit'         => esc_html__( 'Edit My Information', 'wp-members' ),
			'profile_password'     => esc_html__( 'Change Password', 'wp-members' ),
			'register_status'      => esc_html__( 'You are logged in as %s', 'wp-members' ),
			'register_logout'      => esc_html__( 'Log out', 'wp-members' ),
			'register_continue'    => ( isset( $wpmem->user_pages['profile'] ) && '' != $wpmem->user_pages['profile'] ) ? esc_html__( 'Edit profile', 'wp-members' ) : esc_html__( 'Begin using the site.', 'wp-members' ),
			'login_welcome'        => esc_html__( 'You are logged in as %s', 'wp-members' ),
			'login_logout'         => esc_html__( 'Click to log out', 'wp-members' ),
			'status_welcome'       => esc_html__( 'You are logged in as %s', 'wp-members' ),
			'status_logout'        => esc_html__( 'click to log out', 'wp-members' ),
			'menu_logout'          => esc_html__( 'Log Out', 'wp-members' ),
			
			// Widget.
			'widget_status'         => esc_html__( 'You are logged in as %s', 'wp-members' ),
			'widget_logout'         => esc_html__( 'click here to log out', 'wp-members' ),
			'widget_login_failed_old' => __( 'Login Failed!<br />You entered an invalid username or password.', 'wp-members' ),
			'widget_login_failed'   => esc_html__( 'Invalid username or password.', 'wp-members' ), // @todo New string replacement. Replace widget_login_failed when translated.
			'widget_not_logged_in'  => '',
			'widget_login_username' => esc_html__( 'Username or Email', 'wp-members' ),
			'widget_login_password' => esc_html__( 'Password', 'wp-members' ),
			'widget_login_button'   => esc_html__( 'log in', 'wp-members' ),
			'widget_login_forgot'   => esc_html__( 'Forgot?', 'wp-members' ),
			'widget_login_register' => esc_html__( 'Register', 'wp-members' ),
			
			// Default Dialogs.
			'restricted_msg'       => esc_html__( "This content is restricted to site members.  If you are an existing user, please log in.  New users may register below.", 'wp-members' ),
			'success_old'          => __( "Congratulations! Your registration was successful.<br /><br />You may now log in using the password that was emailed to you.", 'wp-members' ),
			'success'              => esc_html__( "Congratulations! Your registration was successful. You may now log in using the password that was emailed to you.", 'wp-members' ),
			
			// @todo Under consideration for removal from the Dialogs tab.
			'user'                 => esc_html__( "Sorry, that username is taken, please try another.", 'wp-members' ),
			'email_old'            => __( "Sorry, that email address already has an account.<br />Please try another.", 'wp-members' ),
			'email'                => esc_html__( "Sorry, that email address already has an account. Please try another.", 'wp-members' ),
			'editsuccess'          => esc_html__( "Your information was updated!", 'wp-members' ),
			
			// @todo These are defaults and are under consideration for removal from the dialogs tab, possibly as we change the password reset to a link based process.
			'pwdchangerr'          => esc_html__( "Passwords did not match.", 'wp-members' ),
			'pwdchangesuccess'     => esc_html__( "Password successfully changed.", 'wp-members' ),
			'pwdreseterr'          => esc_html__( "Invalid username or email address.", 'wp-members' ),
			'pwdresetsuccess_alt'  => esc_html__( "Password successfully reset! An email containing a new password has been sent to the email address on file for your account.", 'wp-members' ),
			'pwdresetsuccess'      => esc_html__( "Reset request received. An email with instructions to complete the password reset has been sent.", 'wp-members' ),
			
			'acct_not_approved'    => esc_html__( "Your account request is still pending approval.", 'wp-members' ),
			'acct_not_validated'   => esc_html__( "You have not completed account validation. Check your inbox for the valdation email.", 'wp-members' ),
			
			'product_restricted_single'    => esc_html__( "This content requires the following membership: ", 'wp-members' ),
			'product_restricted_multiple'  => esc_html__( "This content requires one of the following memberships: ", 'wp-members' ),

			// @todo These are new in 3.5.0 (moved from other areas of the plugin)
			'rs_captcha_error' => esc_html__( 'Error with the captcha code configuration. Please notify the site administrator', 'wp-members' ),
			'rs_captcha_empty' => esc_html__( 'Captcha code was empty. You must complete the captcha code.', 'wp-members' ),
			'rs_captcha_wrong' => esc_html__( 'You have entered an incorrect code value. Please try again.', 'wp-members' ),
			'membership_field' => esc_html__( 'Choose membership', 'wp-members' ),
			'wp_form_required' => esc_html__( '(required)' ),
			'wp_form_activate' => esc_html__( 'Activate this user?', 'wp-members' ),
			'membership_sc_title'     => esc_html__( 'Memberships', 'wp-members' ),
			'membership_sc_no_expire' => esc_html__( 'Does not expire', 'wp-members' ),
			'customizer_generic_msg'  => esc_html__( "This is a generic message to display the form message dialog in the Customizer.", 'wp-members' ),
			'logout_sc_text' => esc_html__( 'Click here to log out.', 'wp-members' ),
			'user_not_activated' => esc_html__( '%sERROR%s: User has not been activated.', 'wp-members' ),
			'validate_email_text'    => esc_html__( 'Click to validate your account: ',       'wp-members' ), 
			'validate_success_msg'   => esc_html__( 'Thank you for validating your account.', 'wp-members' ), 
			'validate_invalid_msg'   => esc_html__( 'Validation key was expired or invalid',  'wp-members' ), 
			'validate_moderated_msg' => esc_html__( 'Your account is now pending approval',   'wp-members' ),
			'rest_content_rendered'  => esc_html__( "You must be logged in to view this content.", 'wp-members' ),
			'rest_excerpt_rendered'  => esc_html__( "You must be logged in to view this content.", 'wp-members' ),
			'rest_404'               => esc_html__( 'The page you are looking for does not exist', 'wp-members' ),
			'loginout_login_text'    => esc_html__( 'log in',  'wp-members' ),
			'loginout_logout_text'   => esc_html__( 'log out', 'wp-members' ),
			'woo_reg_required_field' => esc_html__( '%s is a required field.', 'wp-members' ),
			'tos_title' => esc_html__( 'Terms of Service', 'wp-members' ),
			'tos_close' => esc_html__('%sclose%s', 'wp-members'),
			'tos_print' => esc_html__('%sprint%s', 'wp-members'),

			// @todo These replace the default password reset dialogs.
			'pwd_reset_invalid_key'     => esc_html__( "Invalid key.", 'wp-members' ),
			'pwd_reset_invalid_user'    => esc_html__( "Invalid user.", 'wp-members' ),
			'pwd_reset_key_is_expired'  => esc_html__( "Sorry, the password reset key is expired.", 'wp-members' ),
			'pwd_reset_request_new_key' => esc_html__( "Request a new reset key.", 'wp-members' ),
			'pwd_reset_empty'           => esc_html__( 'User field cannot be empty', 'wp-members' ),
			'pwd_reset_nonce'           => esc_html__( 'There was an unspecified error', 'wp-members' ),

		); // End of $defaults array.
		
		/**
		 * Filter default terms.
		 *
		 * @since 3.1.0
		 * @deprecated 3.2.7 Use wpmem_default_text instead.
		 */
		$text = apply_filters_deprecated( 'wpmem_default_text_strings', array( '' ), '3.2.7', 'wpmem_default_text' );

		
		// Merge filtered $terms with $defaults.
		$text = wp_parse_args( $text, $defaults );
		
		/**
		 * Filter the default terms.
		 *
		 * Replaces 'wpmem_default_text_strings' so that multiple filters could
		 * be run. This allows for custom filters when also running the Text
		 * String Editor extension.
		 *
		 * @since 3.2.7
		 */
		$text = apply_filters( 'wpmem_default_text', $text );
		
		// Manage legacy keys (i.e. sb_ to widget_ ). 
		// @todo Legacy keys to be obsolete by 3.5.0
		$str = ( false !== strpos( $str, 'sb_' ) ) ? str_replace( 'sb_', 'widget_', $str ) : $str;
		foreach ( $text as $key => $value ) {
			if ( false !== strpos( $key, 'sb_' ) ) {
				$new_key = str_replace( 'sb_', 'widget_', $key );
				$text[ $new_key ] = $text[ $key ];
			}
		}

		if ( isset( $text[ $str ] ) ) {
			return $text[ $str ];
		} else {
			wpmem_write_log( 'Unknown dialog key "' . $key . '" in WP_Members_Dialogs::get_text()' );
			return '';
		}
	}
	
	/**
	 * Login Failed Dialog.
	 *
	 * Returns the login failed error message.
	 *
	 * @since 1.8
	 * @since 3.4.0 Removed "continue" (return) link (login form now displays by default under the error message).
	 *
	 * @todo 3.5.0 will fold this into the main message function and this functions associated filters will be obsolete.
	 *
	 * @global object $wpmem The WP_Members object.
	 * @return string $str   The generated html for the login failed message.
	 */
	function login_failed() {

		global $wpmem;

		// Defaults.
		$defaults = array(
			'div_before'     => '',
			'div_after'      => '', 
			'heading_before' => '',
			'heading'        => '', //wpmem_get_text( 'login_failed_heading' ),
			'heading_after'  => '',
			'p_before'       => '',
			'message'        => ( $wpmem->error ) ? $wpmem->error->get_error_message() : wpmem_get_text( 'login_failed' ), // @todo $this->error
			'p_after'        => '',
			//'link'           => '<a href="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' . wpmem_get_text( 'login_failed_link' ) . '</a>',
		);

		/**
		 * Filter the login failed dialog arguments.
		 *
		 * @since 2.9.0
		 * @since 3.3.3 Should pass defaults to filter.
		 * @deprecated 3.4.0
		 * 
		 * @todo Apply apply_filters_deprecated()
		 *
		 * @param array An array of arguments to merge with defaults.
		 */
		$args = apply_filters( 'wpmem_login_failed_args', $defaults );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		$str = $args['div_before']
			. $args['heading_before'] . $args['heading'] . $args['heading_after']
			. $args['p_before'] . $args['message'] . $args['p_after']
			//. $args['p_before'] . $args['link'] . $args['p_after']
			. $args['div_after'];

		/**
		 * Filter the login failed dialog.
		 *
		 * @since 2.7.3
		 *
		 * @param string $str The login failed dialog.
		 */
		$str = apply_filters( 'wpmem_login_failed', $str );

		return $str;
	}

	/**
	 * Gets the message to display.
	 * 
	 * @since 3.4.0
	 * 
	 * @todo This replaces some other functions and usage seems to be inconsistent.
	 *       Review and replace useage as needed.
	 * 
	 * @param  $tag     string
	 * @param  $custom  string
	 * @return $message string
	 */
	function get_message( $tag, $custom = false ) {

		// defaults
		$defaults = array(
			'div_before' => '<div class="wpmem_msg">',
			'div_after'  => '</div>', 
			'p_before'   => '', // @deprecated 3.4.0
			'p_after'    => '', // @deprecated 3.4.0
			'tags'       => array(
				'user',
				'email',
				'success',
				'editsuccess',
				'pwdchangerr',
				'pwdchangesuccess',
				'pwdreseterr',
				'pwdresetsuccess',
				'loginfailed',
			),
		);

		/**
		 * Filter the message arguments.
		 *
		 * @since 2.9.0
		 * @deprecated 3.3.0 Use wpmem_msg_defaults instead.
		 *
		 * @param array An array of arguments to merge with defaults.
		 */
		$args = apply_filters_deprecated( 'wpmem_msg_args', array(''), '3.3.0', 'wpmem_msg_defaults' );

		/** This filter is documented in /includes/class-wp-members-admin-api.php */
		$dialogs = apply_filters( 'wpmem_dialogs', get_option( 'wpmembers_dialogs' ) );

		// @todo Temporary(?) workaround for custom dialogs as an array (WP-Members Security).
		if ( array_key_exists( $tag, $dialogs ) ) {
			if ( is_array( $dialogs[ $tag ] ) ) {
				$msg = stripslashes( $dialogs[ $tag ]['value'] );
			} else {
				$msg = wpmem_get_text( $tag );
				$msg = ( $dialogs[ $tag ] == $msg ) ? $msg : wp_kses_post( __( stripslashes( $dialogs[ $tag ] ), 'wp-members' ) );
			}
		} elseif ( 'loginfailed' == $tag ) {
			$msg = $this->login_failed();
		} elseif ( $custom ) {
			$msg = $custom;
		} else {
			// It must be a custom message ("custom" in that it is not included in the dialogs array).
			$msg = $tag;
		}
	
		$defaults['msg'] = $msg;

		/**
		 * Filter the message array
		 *
		 * @since 2.9.2
		 * @since 3.1.1 added $dialogs parameter.
		 * @deprecated 3.3.0 Use wpmem_msg_defaults instead.
		 * @todo Obsolete in 3.5.0
		 *
		 * @param array  $defaults An array of the defaults.
		 * @param string $tag      The tag that we are on, if any.
		 * @param array  $dialogs
		 */
		$defaults = apply_filters_deprecated( 'wpmem_msg_dialog_arr', array( $defaults, $tag, $dialogs ), '3.3.0', 'wpmem_msg_defaults' );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		// Backwards compatibility for 'toggles'.
		if ( isset( $args['toggles'] ) ) {
			$args['tags'] = $args['toggles'];
		}

		/**
		 * Filter the message settings.
		 *
		 * @since 3.3.0
		 *
		 * @param array  $defaults An array of the defaults.
		 * @param string $tag      The tag that we are on, if any.
		 * @param array  $dialogs
		 */
		$args = apply_filters( 'wpmem_msg_defaults', $defaults, $tag, $dialogs );

		// @todo Temporary(?) workaround for custom dialogs as an array (WP-Members Security).
		$display_msg = ( is_array( $args['msg'] ) ) ? $args['msg']['value'] : $args['msg'];

		$str = $args['div_before'] . stripslashes( $display_msg ) . $args['div_after'];

		/**
		 * Filter the message.
		 *
		 * @since 2.7.4
		 * @since 3.1.0 Added tag.
		 *
		 * @param string $str The message.
		 * @param string $tag The tag of the message being displayed.
		 */
		return apply_filters( 'wpmem_msg_dialog', $str, $tag );
	}

	public function get_deprecated_dialogs() {
		return array ( 
			'user'             => 'reg_username_taken',
			'email'            => 'reg_email_taken',
			'editsuccess'      => 'profile_update',
			'pwdchangerr'      => 'pwdchangerr',
			'pwdchangesuccess' => 'pwdchangesuccess',
			'pwdreseterr'      => 'pwdreseterr',
			'pwdresetsuccess'  => 'pwdresetsuccess'
		);
	}

	public function map_deprecated_dialogs( $defaults, $tag, $dialogs ) {

		$deprecated_dialogs = $this->get_deprecated_dialogs();

		if ( array_key_exists( $tag, $deprecated_dialogs ) ) {
			$defaults['msg'] = wpmem_get_text( $deprecated_dialogs[ $tag ] );
		}

		return $defaults;
	}
}