<?php
/**
 * The WP_Members Email Class.
 *
 * This class contains functions
 * for the plugin's email functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2025  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP_Members_Shortcodes
 * @author Chad Butler 
 * @copyright 2006-2025
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Email {
	
	/**
	 * Container email from address.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    string
	 */
	public $from;
	
	/**
	 * Container for email from name.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    string
	 */
	public $from_name;
	
	/**
	 * Container for settings.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    array
	 */
	public $settings;
	
	/**
	 * Setting for HTML email.
	 *
	 * @since  3.4.0
	 * @access public
	 * @var    string
	 */
	public $html = 0;
	
	/**
	 * Load custom from address.
	 *
	 * @since 3.3.0
	 */
	public function load_from() {
		$this->from      = get_option( 'wpmembers_email_wpfrom', '' );
		$this->from_name = get_option( 'wpmembers_email_wpname', '' );
	}
	
	/**
	 * Load plugin HTML email setting.
	 *
	 * @since 3.4.0
	 */
	private function load_format() {
		$this->html = get_option( 'wpmembers_email_html', 0 );
	}
	
	/**
	 * Builds emails for the user.
	 *
	 * @since 1.8.0
	 * @since 2.7.4 Added wpmem_email_headers and individual body/subject filters.
	 * @since 2.9.7 Major overhaul, added wpmem_email_filter filter.
	 * @since 3.1.0 Can filter in custom shortcodes with wpmem_email_shortcodes.
	 * @since 3.1.1 Added $custom argument for custom emails.
	 * @since 3.2.0 Changed wpmem_msurl key to wpmem_profile.
	 * @since 3.2.0 Changed toggle key to tag.
	 * @since 3.2.0 Moved to WP_Members_Email::to_user().
	 *
	 * @global object $wpmem                The WP_Members object.
	 * @global string $wpmem_mail_from      The email from address.
	 * @global string $wpmem_mail_from_name The email from name.
	 *
	 * @param  int    $user_ID              The User's ID.
	 * @param  string $password             Password from the registration process.
	 * @param  string $tag                  Tag indicating the email being sent (newreg|newmod|appmod|repass|getuser).
	 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to false).
	 * @param  array  $fields               Array of the registration data (defaults to null).
	 * @param  array  $custom               Array of custom email information (defaults to null).
	 */
	public function to_user( $user_id, $password, $tag, $wpmem_fields = false, $field_data = null, $custom = null ) {

		global $wpmem;
		
		// Load settings.
		$this->load_from();
		$this->load_format();

		// Handle backward compatibility for customizations that may call the email function directly.
		$wpmem_fields = ( false == $wpmem_fields ) ? wpmem_fields( 'all' ) : $wpmem_fields;

		// Handle backward compatibility for passing numeric tags.
		if ( is_numeric( $tag ) ) {
			if ( 5 != $tag ) {
				$tag = array_search( $tag, array( 'newreg'=>0, 'newmod'=>1, 'appmod'=>2, 'repass'=>3, 'getuser'=>4, 'validated'=>6 ) );
			}
		}

		//Determine email to be sent. Stored option is an array with keys 'body' and 'subj'.
		$tag_array = array( 'newreg', 'newmod', 'appmod', 'repass', 'getuser', 'validated' );
		if ( in_array( $tag, $tag_array ) ) {
			$this->settings = wpmem_get_email_settings( 'wpmembers_email_' . $tag );
			$this->settings['tag'] = $tag;
		} else {
			// This is a custom email.
			$this->settings['subj'] = $custom['subj'];
			$this->settings['body'] = $custom['body'];
			$this->settings['tag']  = ( isset( $custom['tag'] ) ) ? $custom['tag'] : '';
		}
		
		// wpautop() the content if we are doing HTML email.
		if ( 1 == $this->html ) {
			$this->settings['body'] = wpautop( $this->settings['body'] );
		}

		// Get the user ID.
		$user = new WP_User( $user_id );

		// Userdata for default shortcodes.
		$this->settings['user_id']       = $user_id;
		$this->settings['user_login']    = stripslashes( $user->user_login );
		$this->settings['user_email']    = stripslashes( $user->user_email );
		$this->settings['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
		$this->settings['exp_type']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
		$this->settings['exp_date']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
		$this->settings['wpmem_profile'] = esc_url( $wpmem->user_pages['profile'] );
		$this->settings['wpmem_reg']     = esc_url( $wpmem->user_pages['register'] );
		$this->settings['wpmem_login']   = esc_url( $wpmem->user_pages['login'] );
		$this->settings['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
		$this->settings['do_shortcodes'] = true;
		$this->settings['add_footer']    = true;
		$this->settings['footer']        = ( 1 == $this->html ) ? wpautop( wpmem_get_email_settings( 'wpmembers_email_footer' ) ) : wpmem_get_email_settings( 'wpmembers_email_footer' );
		$this->settings['disable']       = false;
		$this->settings['toggle']        = $this->settings['tag']; // Deprecated since 3.2.0, but remains in the array for legacy reasons.
		$this->settings['reset_link']    = esc_url_raw( add_query_arg( array( 'a' => 'pwdreset', 'key' => $password, 'id' => $user_id ), wpmem_profile_url() ) );
		$this->settings['line_break']    = ( 1 == $this->html ) ? "<br>" : "\r\n";

		// Apply filters (if set) for the sending email address.
		$default_header = ( $this->from && $this->from_name ) ? 'From: "' . $this->from_name . '" <' . $this->from . '>' : '';

		/**
		 * Filters the email headers.
		 *
		 * @since 2.7.4
		 * @since 3.2.0 Changed toggle to tag.
		 * @since 3.4.2 Added user ID.
		 *
		 * @param mixed  $default_header        The email headers.
		 * @param string $this->settings['tag'] Tag to determine what email is being generated (newreg|newmod|appmod|repass|admin).
		 * @param int    $user_id
		 */
		$this->settings['headers'] = apply_filters( 'wpmem_email_headers', $default_header, $this->settings['tag'], $this->settings['user_id'] );

		/**
		 * Filters attachments.
		 * 
		 * @since 3.4.1
		 * @since 3.4.2 Added user ID.
		 * 
		 * @param  mixed  $attachments           Any file attachments as a string or array. (default per wp_mail() documentation is empty array).
		 * @param  string $this->settings['tag'] Tag to determine what email is being generated (newreg|newmod|appmod|repass|admin).
		 * @param  int    $user_id
		 */
		$this->settings['attachments'] = apply_filters( 'wpmem_email_attachments', array(), $this->settings['tag'], $this->settings['user_id'] );

		/**
		 * Filter the email.
		 *
		 * This filter passes the email subject, body, user ID, and several other
		 * settings and parameters for use in the filter function. It also passes an
		 * array of the WP-Members fields, and an array of the posted registration
		 * data from the register function.
		 *
		 * @since 2.9.7
		 * @since 3.1.0 Added footer content to the array.
		 * @since 3.2.0 Changed wpmem_msurl key to wpmem_profile.
		 * @since 3.2.0 Change toggle to tag.
		 * @since 3.4.2 Added line_break optional param.
		 *
		 * @param array $this->settings {
		 *     An array containing email body, subject, user id, and additional settings.
		 *
		 *     @type string subj
		 *     @type string body
		 *     @type string tag
		 *     @type int    user_id
		 *     @type string user_login
		 *     @type string user_email
		 *     @type string blogname
		 *     @type string exp_type
		 *     @type string exp_date
		 *     @type string wpmem_profile
		 *     @type string reg_link
		 *     @type string do_shortcodes
		 *     @type bool   add_footer
		 *     @type string footer
		 *     @type bool   disable
		 *     @type mixed  headers
		 *     @type string toggle Deprecated since 3.2.0
		 *     @type string line_break
		 * }
		 * @param array $wpmem_fields An array of the WP-Members fields.
		 * @param array $field_data   An array of the posted registration data.
		 */
		$this->settings = apply_filters( 'wpmem_email_filter', $this->settings, $wpmem_fields, $field_data );

		// If emails are not disabled, continue the email process.
		if ( ! $this->settings['disable'] ) {
			
			/**
			 * Filters the email body based on tag.
			 *
			 * @since 2.7.4
			 * @deprecated 3.2.0 Use wpmem_email_filter instead.
			 *
			 * @param string $this->settings['body'] The body content of the new registration email.
			 * @param int    $user_id
			 */
			$this->settings['body'] = apply_filters_deprecated( 'wpmem_email_' . $this->settings['tag'], array( $this->settings['body'] ), '3.2.0', 'wpmem_email_filter' );

			// Get the email footer if needed.
			$foot = ( $this->settings['add_footer'] ) ? $this->settings['footer'] : '';

			// If doing shortcode replacements.
			if ( $this->settings['do_shortcodes'] ) {

				$shortcodes = array(
					'blogname'     => $this->settings['blogname'],
					'username'     => $this->settings['user_login'],
					'password'     => $password,
					'email'        => $this->settings['user_email'],
					'reglink'      => $this->settings['reg_link'],
					'members-area' => $this->settings['wpmem_profile'],
					'user-profile' => $this->settings['wpmem_profile'],
					'exp-type'     => $this->settings['exp_type'],
					'exp-data'     => $this->settings['exp_date'],
					'exp-date'     => $this->settings['exp_date'],
					'login'        => $this->settings['wpmem_login'],
					'register'     => $this->settings['wpmem_reg'],
					'reset_link'   => $this->settings['reset_link'],
				);

				// Add custom field shortcodes.
				foreach ( $wpmem_fields as $meta_key => $field ) {
					if ( ! array_key_exists( $meta_key, $shortcodes ) ) {
						$val = ( is_array( $field_data ) && $field['register'] ) ? $field_data[ $meta_key ] : get_user_meta( $user_id, $meta_key, true );
						$shortcodes[ $meta_key ] = $val;
					}
				}

				/**
				 * Filter available email shortcodes.
				 *
				 * @since 3.1.0
				 * @since 3.4.2 Added user ID.
				 *
				 * @param array  $shortcodes
				 * @param string $tag
				 * @param int    $user_id
				 */
				$shortcodes = apply_filters( 'wpmem_email_shortcodes', $shortcodes, $this->settings['tag'], $this->settings['user_id'] );

				$shortcd = array();
				$replace = array();
				foreach ( $shortcodes as $key => $val ) {
					// Shortcodes.
					$shortcd[] = '[' . $key . ']';
					// Replacement values.
					$replace[] = ( 'password' == $key ) ? $password : $val;
				}

				// Do replacements for subject, body, and footer shortcodes.
				$this->settings['subj'] = str_replace( $shortcd, $replace, $this->settings['subj'] );
				$this->settings['body'] = str_replace( $shortcd, $replace, $this->settings['body'] );
				$foot = ( $this->settings['add_footer'] ) ? str_replace( $shortcd, $replace, $foot ) : '';
			}

			// Append footer if needed.
			$this->settings['body'] = ( $this->settings['add_footer'] ) ? $this->settings['body'] . $this->settings['line_break'] . $foot : $this->settings['body'];

			// Send message.
			$this->send( 'user' );

		}
		return;
	}

	/**
	 * Builds the email for admin notification of new user registration.
	 *
	 * @since 2.3
	 * @since 3.2.0 Moved to WP_Members_Email::notify_admin().
	 *
	 * @global object $wpmem                The WP_Members object.
	 * @global string $wpmem_mail_from      The email from address.
	 * @global string $wpmem_mail_from_name The email from name.
	 *
	 * @param  int    $user_id              The User's ID.
	 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
	 * @param  array  $field_data           Array of the registration data (defaults to null).
	 */
	public function notify_admin( $user_id, $wpmem_fields = null, $field_data = null ) {

		global $wpmem;

		// Load from address.
		$this->load_from();

		// Handle backward compatibility for customizations that may call the email function directly.
		$wpmem_fields = wpmem_fields( 'admin_notify' );

		// WP default user fields.
		$wp_user_fields = array(
			'user_login',
			'user_nicename',
			'user_url',
			'user_registered',
			'display_name',
			'first_name',
			'last_name',
			'nickname',
			'description',
		);

		// Get the user data.
		$user = get_userdata( $user_id );

		// Get the email stored values.
		$this->settings  = wpmem_get_email_settings( 'wpmembers_email_notify' );

		// wpautop() the content if we are doing HTML email.
		if ( 1 == $this->html ) {
			$this->settings['body'] = wpautop( $this->settings['body'] );
		}

		// Userdata for default shortcodes.
		$this->settings['user_id']       = $user_id;
		$this->settings['user_login']    = stripslashes( $user->user_login );
		$this->settings['user_email']    = stripslashes( $user->user_email );
		$this->settings['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
		$this->settings['user_ip']       = ( is_array( $field_data ) ) ? $field_data['wpmem_reg_ip'] : get_user_meta( $user_id, 'wpmem_reg_ip', true );
		$this->settings['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
		$this->settings['act_link']      = esc_url( add_query_arg( 'user_id', $user_id, get_admin_url( '', 'user-edit.php' ) ) );
		$this->settings['exp_type']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
		$this->settings['exp_date']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
		$this->settings['do_shortcodes'] = true;
		$this->settings['add_footer']    = true;
		$this->settings['footer']        = ( 1 == $this->html ) ? wpautop( wpmem_get_email_settings( 'wpmembers_email_footer' ) ) : wpmem_get_email_settings( 'wpmembers_email_footer' );
		$this->settings['disable']       = false;
		$this->settings['line_break']    = ( 1 == $this->html ) ? "<br>" : "\r\n";

		// Builds an array of the user data fields.
		$field_arr = array();
		foreach ( $wpmem_fields as $meta_key => $field ) {
			if ( $field['register'] ) {
				if ( ! in_array( $meta_key, wpmem_get_excluded_meta( 'email' ) ) ) {
					if ( ( 'user_email' != $meta_key ) && ( 'password' != $meta_key ) ) {
						if ( 'user_url' == $meta_key ) {
							$val = esc_url( $user->user_url );
						} elseif ( in_array( $meta_key, $wp_user_fields ) ) {
							$val = esc_html( $user->{$meta_key} );
						} elseif ( wpmem_is_file_field( $meta_key ) ) {
							$val = wpmem_get_file_field_url( $meta_key, $user_id );
						} else {
							$val = ( is_array( $field_data ) ) ? esc_html( $field_data[ $meta_key ] ) : esc_html( get_user_meta( $user_id, $meta_key, true ) );
						}
						$field_arr[ $meta_key ] = $val;
					}
				}
			}
		}
		$this->settings['fields'] = $field_arr;

		// Apply filters (if set) for the sending email address.
		$default_header = ( $this->from && $this->from_name ) ? 'From: "' . $this->from_name . '" <' . $this->from . '>' : '';

		/** This filter is documented in class-wp-members-email.php */
		$this->settings['headers'] = apply_filters( 'wpmem_email_headers', $default_header, 'admin', $this->settings['user_id'] );

		/** This filter is documented in class-wp-members-email.php */
		$this->settings['attachments'] = apply_filters( 'wpmem_email_attachments', array(), 'admin', $this->settings['user_id'] );

		/**
		 * Filters the address the admin notification is sent to.
		 *
		 * @since 2.7.5
		 * @since 3.4.2 Added user ID.
		 *
		 * @param string The email address of the admin to send to.
		 */
		$this->settings['admin_email'] = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ), $this->settings['user_id'] );

		/**
		 * Filter the email.
		 *
		 * This is a new and more powerful filter than was previously available for
		 * emails. This new filter passes the email subject, body, user ID, and several
		 * other settings and parameters for use in the filter function. It also passes
		 * an array of the WP-Members fields, and an array of the posted registration
		 * data from the register function.
		 *
		 * @since 2.9.8
		 * @since 3.3.9 Added $user param.
		 * @since 3.4.2 Added optional line_break param.
		 * @since 3.5.0 $field_arr obsolete, use $fields instead.
		 *
		 * @param array $this->settings
		 *     An array containing email body, subject, user id, and additional settings.
		 *
		 *     @type string  $subj
		 *     @type string  $body
		 *     @type integer $user_id
		 *     @type string  $user_login
		 *     @type string  $user_email
		 *     @type string  $blogname
		 *     @type string  $user_ip
		 *     @type string  $reg_link
		 *     @type string  $act_link
		 *     @type string  $exp_type
		 *     @type string  $exp_date
		 *     @type boolean $do_shortcodes
		 *     @type boolean $add_footer
		 *     @type boolean $footer
		 *     @type boolean $disable
		 *     @type array   $field_arr @obsolete 3.5.0
		 *     @type array   $fields
		 *     @type string  $headers
		 *     @type string  $admin_email
		 *     @type string  $line_break
		 * }
		 * @param array    $wpmem_fields   An array of the WP-Members fields.
		 * @param array    $field_data     An array of the posted registration data.
		 * @param stdClass $user           WP user object for the specific user.
		 */
		$this->settings = apply_filters( 'wpmem_notify_filter', $this->settings, $wpmem_fields, $field_data, $user );

		// If emails are not disabled, continue the email process.
		if ( ! $this->settings['disable'] ) {

			// Split fields into field_str.
			$field_str = '';
			foreach ( $this->settings['fields'] as $key => $val ) {
				$field_str.= wpmem_get_field_label( $key ) . ': ' . $val . $this->settings['line_break']; 
			}

			// Get the email footer if needed.
			$foot = ( $this->settings['add_footer'] ) ? $this->settings['footer'] : '';

			// If doing shortcode replacements.
			if ( $this->settings['do_shortcodes'] ) {

				$shortcodes = array(
					'blogname'      => $this->settings['blogname'],
					'username'      => $this->settings['user_login'],
					'email'         => $this->settings['user_email'],
					'reglink'       => $this->settings['reg_link'],
					'exp-type'      => $this->settings['exp_type'],
					'exp-data'      => $this->settings['exp_date'],
					'exp-date'      => $this->settings['exp_date'],
					'user-ip'       => $this->settings['user_ip'],
					'activate-user' => $this->settings['act_link'],
					'fields'        => $field_str,
				);			

				// Add custom field shortcodes.
				foreach ( $wpmem_fields as $meta_key => $field ) {
					$shortcodes[ $meta_key ] = $this->settings['fields'][ $meta_key ];
				}
				// @todo Instead of foreach above: $shortcodes = array_merge( $shortcodes, $this->settings['fields']);

				/**
				 * Filter available email shortcodes.
				 *
				 * @since 3.1.0
				 * @since 3.4.2 Added user ID.
				 *
				 * @param array  $shortcodes
				 * @param string $toggle
				 * @param string $user_id
				 */
				$shortcodes = apply_filters( 'wpmem_email_shortcodes', $shortcodes, 'notify', $this->settings['user_id'] );

				$shortcd = array();
				$replace = array();
				foreach ( $shortcodes as $key => $val ) {
					// Shortcodes.
					$shortcd[] = '[' . $key . ']';
					// Replacement values.
					$replace[] = $val;
				}

				// Create the custom field shortcodes.
				foreach ( $wpmem_fields as $meta_key => $field ) {
					$shortcd[] = '[' . $meta_key . ']';
					$replace[] = ( is_array( $field_data ) && $field['register'] ) ? $field_data[ $meta_key ] : get_user_meta( $user_id, $meta_key, true );
				}

				// Get the subject, body, and footer shortcodes.
				$this->settings['subj'] = str_replace( $shortcd, $replace, $this->settings['subj'] );
				$this->settings['body'] = str_replace( $shortcd, $replace, $this->settings['body'] );
				$foot = ( $this->settings['add_footer'] ) ? str_replace( $shortcd, $replace, $foot ) : '';
			}

			// Append footer if needed.
			$this->settings['body'] = ( $this->settings['add_footer'] ) ? $this->settings['body'] . $this->settings['line_break'] . $foot : $this->settings['body'];

			/**
			 * Filters the admin notification email.
			 *
			 * @since 2.8.2
			 * @since 3.4.2 Added user ID.
			 *
			 * @param string $this->settings['body'] The admin notification email body.
			 */
			$this->settings['body'] = apply_filters( 'wpmem_email_notify', $this->settings['body'], $this->settings['user_id'] );

			// Send the message.
			$this->send( 'admin' );
		}
	}

	/**
	 * Filters the wp_mail from address (if set).
	 *
	 * @since 2.7
	 * @since 3.1.0 Converted to use email var in object.
	 * @since 3.2.0 Moved to WP_Members_Email::from().
	 *
	 * @param  string $email
	 * @return string $wpmem_mail_from|$email
	 */
	public function from( $email ) {
		return ( $this->from ) ? $this->from : $email;
	}

	/**
	 * Filters the wp_mail from name (if set).
	 *
	 * @since 2.7
	 * @since 3.1.0 Converted to use email var in object.
	 * @since 3.2.0 Moved to WP_Members_Email::from_name().
	 *
	 * @param  string $name
	 * @return string $wpmem_mail_from_name|$name
	 */
	public function from_name( $name ) {
		return ( $this->from_name ) ? stripslashes( $this->from_name ) : stripslashes( $name );
	}
	
	/**
	 * Returns HTML content type for email.
	 *
	 * @since 3.4.0
	 *
	 * @return string Always returns "text/html"
	 */
	public function content_type( $content_type = 'text/plain' ) {
		return ( 1 == $this->html ) ? 'text/html' : $content_type;
	}

	/**
	 * Sends email.
	 *
	 * @since 3.2.0
	 *
	 * @param  string  $to
	 * @return bool    $result
	 */
	private function send( $to ) {
		$args['to'] = ( 'user' == $to ) ? $this->settings['user_email'] : $this->settings['admin_email'];
		$args['subject']     = $this->settings['subj'];
		$args['message']     = $this->settings['body'];
		$args['headers']     = $this->settings['headers'];
		$args['attachments'] = $this->settings['attachments'];

		/**
		 * Filter email send arguments.
		 *
		 * @since 3.2.5
		 *
		 * @param array  $send_args
		 * @param string $to
		 * @param array  $this->settings
		 */
		$args = apply_filters( 'wpmem_email_send_args', $args, $to, $this->settings );
		
		// Apply filters.
		add_filter( 'wp_mail_from',         array( $this, 'from'      ) );
		add_filter( 'wp_mail_from_name',    array( $this, 'from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'content_type' ) );
		
		// Send message.
		$result = wp_mail( $args['to'], stripslashes( $args['subject'] ), stripslashes( $args['message'] ), $args['headers'], $args['attachments'] );
		
		// Remove customizations.
		remove_filter( 'wp_mail_from',         array( $this, 'from'      ) );
		remove_filter( 'wp_mail_from_name',    array( $this, 'from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'content_type' ) );
		
		// Return result (does not necessarily indicate message was sent).
		return $result;
	}

	/**
	 * Defaults for most plugin emails.
	 * 
	 * @since 3.5.0
	 * 
	 * @param  string  $tag
	 * @return array
	 */
	function get_default_email( $tag ) {

		switch ( $tag ) {

			case 'wpmembers_email_newreg':
				// Email for a new registration.
				$args['subj'] = 'Your registration info for [blogname]';
				$args['body'] = 'Thank you for registering for [blogname]!

Please confirm your email address by following the link below:
[confirm_link]

Once you have confirmed your email address, you will be able to log in using the credentials you created when you registered.
';
				break;

			case 'wpmembers_email_newmod':
	
				// Email for new registration, registration is moderated.
				$args['subj'] = 'Thank you for registering for [blogname]';
				$args['body'] = 'Thank you for registering for [blogname]. 
Your registration has been received and is pending approval.
You will receive login instructions upon approval of your account
';
				break;

			case 'wpmembers_email_appmod':
	
				// Email for registration is moderated, user is approved.
				$args['subj'] = 'Your registration for [blogname] has been approved';
				$args['body'] = 'Your registration for [blogname] has been approved.

Your registration information is below.
You may wish to retain a copy for your records.

username: [username]
password: [password]

You may log in and change your password here:
[user-profile]

You originally registered at:
[reglink]
';
				break;
	
			case 'wpmembers_email_repass':
	
				// Email for password reset.
				$args['subj'] = 'Your password reset for [blogname]';
				$args['body'] = 'A password reset was requested for [blogname].

Follow the link below to reset your password:
[reset_link]

If you did not request a password reset for [blogname], simply ignore this message and the reset key will expire.
';
				break;

			case 'wpmembers_email_notify':
	
				// Email for admin notification.
				$args['subj'] = 'New user registration for [blogname]';
				$args['body'] = 'The following user registered for [blogname]:
	
username: [username]
email: [email]

[fields]
This user registered here:
[reglink]

user IP: [user-ip]

activate user: [activate-user]
';
				break;

			case 'wpmembers_email_footer':
	
				// Email footer (no subject).
				$args = '----------------------------------
This is an automated message from [blogname]
Please do not reply to this address';

				break;
	
			case 'wpmembers_email_getuser':
		
				// Email for retrieve username.
				$args['subj'] = 'Username for [blogname]';
				$args['body'] = 'Your username for [blogname] is below.
	
username: [username]
';
				break;

			case 'wpmembers_email_validated':
				$args['subj'] = 'Thank you for validating your email.';
				$args['body'] = 'Thank you for validating your email address.  You may now log in and use the site.';
				break;
	
			default:
				$args = false;
				break;
		}
		
		return $args;
	}
}