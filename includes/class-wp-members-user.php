<?php
/**
 * The WP_Members_User Class.
 *
 * This is the WP_Members User object class. This class contains functions
 * for login, logout, registration and other user related methods.
 *
 * @package WP-Members
 * @subpackage WP_Members_User Object Class
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_User {
	
	/**
	 * Containers for reg form data.
	 *
	 * @since  3.1.7
	 * @access public
	 * @var    array
	 */
	public $post_data = array();
	public $prev_data = array();
	
	/**
	 * Container for user access information.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    array
	 */
	public $access = array();

	public $reg_type;

	
	/**
	 * The password reset object.
	 * 
	 * @since Unknown
	 * @access public
	 * @var object
	 */
	public $pwd_reset;
	
	/**
	 * Initilize the User object.
	 *
	 * @since 3.1.7
	 *
	 * @param object $settings The WP_Members Object
	 */
	public function __construct( $settings ) {
		
		add_action( 'wpmem_after_init', array( $this, 'load_user_products' ) );
		
		add_action( 'user_register', array( $this, 'set_reg_type'            ), 1 );
		add_action( 'user_register', array( $this, 'register_finalize'       ), 5 );
		add_action( 'user_register', array( $this, 'post_register_data'      ), 9 ); // Changed this to 9 so custom user meta is saved before the default (10) priority.
		add_action( 'user_register', array( $this, 'set_user_exp'            ), 25 );
		add_action( 'user_register', array( $this, 'register_email_to_user'  ), 25 );
		add_action( 'user_register', array( $this, 'register_email_to_admin' ), 25 );
		
		add_action( 'wpmem_register_redirect', array( $this, 'register_redirect' ), 20 ); // Adds a nonce to the redirect if there is a "redirect_to" attribute in the reg form.
		
		add_filter( 'registration_errors', array( $this, 'wp_register_validate' ), 10, 3 );  // native registration validation
	
		// Load anything the user as access to.
		if ( 1 == $settings->enable_products ) {
			add_action( 'user_register', array( $this, 'set_default_product' ), 6 );
		}

		// On file upload, check that the user folder has an index file.
		add_action( 'wpmem_file_uploaded', array( $this, 'check_folder_for_index' ), 10 , 3 );
	}
	
	/**
	 * Loads the current user's membership products on init.
	 *
	 * @since 3.4.0
	 */
	public function load_user_products() {
		if ( is_user_logged_in() ) {
			$this->access = wpmem_get_user_products( get_current_user_id() );
		}
	}
	
	/**
	 * Handle user login.
	 *
	 * Built from, but replaces, the original wpmem_login() function
	 * from core.php. wpmem_login() is currently maintained as a 
	 * wrapper and is the direct function called for login.
	 *
	 * @since 3.1.7
	 * @since 3.2.3 Removed wpmem_login_fields filter.
	 * @since 3.2.3 Replaced form collection with WP script to facilitate login with username OR email.
	 * @since 3.2.3 Changed to wp_safe_redirect().
	 * @since 3.3.9 Added wpmem_set_as_logged_in() to make sure user is set.
	 *
	 * @return string Returns "loginfailed" if failed login.
	 */
	public function login() {
		
		global $wpmem;

		$user = wp_signon( array(), is_ssl() );

		/**
		 * Adds a hook point to hijack the login process.
		 * 
		 * Useful for integration with problematic plugins like miniOrange.
		 * 
		 * @since 3.5.0
		 */
		$user = apply_filters( 'wpmem_after_wp_signon', $user );

		if ( is_wp_error( $user ) ) {
			$wpmem->error = $user;
			return "loginfailed";
		} else {
			
			// Make sure current user is set.
			// @todo Verify that removing this resovles 2 sessions issues per https://wordpress.org/support/topic/creating-multiple-same-sessions-on-login/
			// wpmem_set_as_logged_in( $user->ID );
			
			$redirect_to = wpmem_get( 'redirect_to', false );
			$redirect_to = ( $redirect_to ) ? esc_url_raw( trim( $redirect_to ) ) : esc_url_raw( wpmem_current_url() );
			/** This filter defined in wp-login.php */
			$redirect_to = apply_filters( 'login_redirect', $redirect_to, '', $user );
			/**
			 * Filter the redirect url.
			 *
			 * This is the plugin's original redirect filter. In 3.1.7, 
			 * WP's login_redirect filter hook was added to provide better
			 * integration support for other plugins and also for users
			 * who may already be using WP's filter(s). login_redirect
			 * comes first, then wpmem_login_redirect. So wpmem_login_redirect
			 * can be used to override a default in login_redirect.
			 *
			 * @since 2.7.7
			 * @since 2.9.2 Added $user_id
			 *
			 * @param string $redirect_to The url to direct to.
			 * @param int    $user->ID    The user's primary key ID.
			 */
			$redirect_to = apply_filters( 'wpmem_login_redirect', $redirect_to, $user->ID );
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}
	
	/**
	 * Handle user logout.
	 *
	 * Built from, but replaces, the original wpmem_logout() function
	 * from core.php. wpmem_logout() is currently maintained as a 
	 * wrapper and is the direct function called for logout.
	 *
	 * @since 3.1.7
	 * @since 3.2.0 Added logout_redirect filter
	 * @since 3.4.0 Added $user_id for wp_logout action (to match WP, which added this in 5.5.0).
	 *
	 * @param string $redirect_to URL to redirect the user to (default: false).
	 */
	public function logout( $redirect_to = false ) {
		
		// Get the user ID for when the action is fired.
		$user_id = get_current_user_id();
		
		// Default redirect URL.
		$redirect_to = ( $redirect_to ) ? $redirect_to : home_url();

		/** This filter is documented in /wp-login.php */
		$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $redirect_to, wp_get_current_user() );
		/**
		 * Filter where the user goes when logged out.
		 *
		 * @since 2.7.1
		 * @since 3.1.7 Moved to WP_Members_Users Class.
		 * @since 3.4.0 Added $user_id param.
		 *
		 * @param string The blog home page.
		 */
		$redirect_to = apply_filters( 'wpmem_logout_redirect', $redirect_to, $user_id );

		wp_destroy_current_session();
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );

		/** This action is defined in /wp-includes/pluggable.php. */
		do_action( 'wp_logout', $user_id );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	/**
	 * Sets the registration type.
	 *
	 * @since 3.3.0
	 */
	public function set_reg_type() {
		// Is this a WP-Members registration?
		$this->reg_type['is_wpmem']   = ( 'register' == wpmem_get( 'a' ) ) ? true : false;
		// Is this WP's native registration? Checks the native submit button.
		$this->reg_type['is_native']  = ( esc_html__( 'Register' ) == wpmem_get( 'wp-submit' ) ) ? true : false;
		// Is this a Users > Add New process? Checks the post action.
		$this->reg_type['is_add_new'] = ( 'createuser' == wpmem_get( 'action' ) ) ? true : false;
		// Is this a WooCommerce my account registration? Checks for WC fields.
		$this->reg_type['is_woo']     = ( wpmem_get( 'woocommerce-register-nonce' ) ) ? true : false;
		// Is this a WooCommerce checkout?
		$this->reg_type['is_woo_checkout'] = ( wpmem_get( 'woocommerce_checkout_place_order' ) ) ? true : false;
		// Is this a WooCommerce profile update?
		$this->reg_type['is_woo_update'] = ( wpmem_get( 'save-account-details-nonce' ) ) ? true : false;
	}

	/**
	 * Checks registration type.
	 * 
	 * @since 3.5.0
	 * 
	 * @param  string  $type
	 * @return boolean
	 */
	function is_reg_type( $type ) {
		return $this->reg_type[ 'is_' . $type ];
	}

	/**
	 * Validate user registration.
	 *
	 * @since 3.3.0
	 *
	 * @global int    $user_ID
	 * @global string $wpmem_themsg
	 * @global array  $userdata
	 *
	 * @param  string $tag
	 */
	public function register_validate( $tag ) {
		
		// Get the globals.
		global $user_ID, $wpmem, $wpmem_themsg, $userdata; 
		
		// Check the nonce.
		if ( empty( $_POST ) || ! wp_verify_nonce( $_REQUEST[ '_wpmem_' . $tag . '_nonce' ], 'wpmem_longform_nonce' ) ) {
			$wpmem_themsg = wpmem_get_text( 'reg_generic' );
			return;
		}

		// Make sure fields are loaded.
		wpmem_fields( $tag );

		// Is this a registration or a user profile update?
		if ( 'register' == $tag ) { 
			$this->post_data['username'] = sanitize_user( wpmem_get( 'username' ) );
		}

		// Add the user email to the $this->post_data array for _data hooks.
		if ( isset( $wpmem->fields['user_email'] ) ) {
			$this->post_data['user_email'] = sanitize_email( wpmem_get( 'user_email' ) );
		}
		
		// If this is an update, and tos is a field, and the user has the correct saved value, remove tos.
		if ( 'update' == $tag && isset( $wpmem->fields['tos'] ) ) {
			if ( get_user_meta( $user_ID, 'tos', true ) == $wpmem->fields['tos']['checked_value'] ) {
				unset( $wpmem->fields['tos'] );
			}
		}

		// Build the $this->post_data array from $_POST data.
		foreach ( $wpmem->fields as $meta_key => $field ) {
			if ( ( 'register' == $tag && true == $field['register'] ) || ( 'update' == $tag && true == $field['profile'] ) ) {
				if ( 'password' != $meta_key && 'confirm_password' != $meta_key && 'username' != $meta_key ) {
					if ( isset( $_POST[ $meta_key ] ) ) {
						switch ( $field['type'] ) {
						case 'checkbox':
							$this->post_data[ $meta_key ] = sanitize_text_field( $_POST[ $meta_key ] );
							break;
						case 'multiselect':
						case 'multicheckbox':
							$delimiter = ( isset( $field['delimiter'] ) ) ? $field['delimiter'] : '|';
							$this->post_data[ $meta_key ] = ( isset( $_POST[ $meta_key ] ) ) ? implode( $delimiter, wpmem_sanitize_array( $_POST[ $meta_key ] ) ) : '';
							break;
						case 'textarea':
							$this->post_data[ $meta_key ] = sanitize_textarea_field( $_POST[ $meta_key ] );
							break;
						case 'email':
							$this->post_data[ $meta_key ] = sanitize_email( $_POST[ $meta_key ] );
							break;
						case 'timestamp':
							$this->post_data[ $meta_key ] = strtotime( $_POST[ $meta_key ] );
							break;
						default:
							$this->post_data[ $meta_key ] = sanitize_text_field( $_POST[ $meta_key ] );
							break;
						}
					} else {
						$this->post_data[ $meta_key ] = '';
					}
				} else {
					// We do have password as part of the registration form.
					if ( isset( $_POST['password'] ) ) {
						$this->post_data['password'] = $_POST['password']; // wp_insert_user() hashes this, so sanitizing is unnessary (and undesirable).
					}
					if ( isset( $_POST['confirm_password'] ) ) {
						$this->post_data['confirm_password'] = $_POST['confirm_password'];
					}
				}
			}
		}

		/**
		 * Filter the submitted form fields prior to validation.
		 *
		 * @since 2.8.2
		 * @since 3.1.7 Added $tag
		 * @since 3.2.0 Moved to regiser_validate() method in user object class.
		 *
		 * @param array  $this->post_data An array of the posted form field data.
		 * @param string $tag
		 */
		$this->post_data = apply_filters( 'wpmem_pre_validate_form', $this->post_data, $tag );

		// Adds integration for custom error codes triggered by "register_post" or contained in "registration_errors"
		// @todo This will move towards integrating all WP-Members registration errors into the "registration_errors" filter
		//       and allow for more standardized custom validation.
		/* $errors = new WP_Error();
		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );
		$errors = apply_filters( 'registration_errors', $errors, $this->post_data['username'], $this->post_data['user_email'] );
		if ( count( $errors->get_error_messages() ) > 0 ) {
			$wpmem_themsg = $errors->get_error_message();
			return;
		} */

		if ( 'update' == $tag ) {
			$pass_arr = array( 'username', 'password', 'confirm_password', 'password_confirm' );
			foreach ( $pass_arr as $pass ) {
				unset( $wpmem->fields[ $pass ] );
			}
		}

		// Check for required fields, reverse the array for logical error message order.
		foreach ( array_reverse( $wpmem->fields ) as $meta_key => $field ) {
			// Validation if the field is required.
			if ( true == $field['required'] ) {
				if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
					// If this is a new registration.
					if ( 'register' == $tag ) {
						// If the required field is a file type.
						if ( empty( $_FILES[ $meta_key ]['name'] ) ) {
							$wpmem_themsg = sprintf( wpmem_get_text( 'reg_empty_field' ), esc_html__( $field['label'], 'wp-members' ) );
						}
					}
				} else {
					// If the required field is any other field type.
					if ( ( 'register' == $tag && true == $field['register'] ) || ( 'update' == $tag && true == $field['profile'] ) ) {
						if ( null == $this->post_data[ $meta_key ] ) {
							$wpmem_themsg = sprintf( wpmem_get_text( 'reg_empty_field' ), esc_html__( $field['label'], 'wp-members' ) );
						}
					}
				}
			}

			// Validate file field type.
			if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
				if ( '' == $field['file_types'] ) {
					$field['file_types'] = ( 'image' == $field['type'] ) ? 'gif|png|jpg|jpeg|bmp' : 'doc|docx|pdf|zip';
				}
				$allowed_file_types = explode( '|', $field['file_types'] );
				$msg_types  = implode( ', ', $allowed_file_types );
				if ( ! empty( $_FILES[ $meta_key ]['name'] ) ) {
					$extension = pathinfo( $_FILES[ $meta_key ]['name'], PATHINFO_EXTENSION );
					if ( ! in_array( $extension, $allowed_file_types ) ) {
						$wpmem_themsg = sprintf( wpmem_get_text( 'reg_file_type' ), esc_html__( $field['label'], 'wp-members' ), str_replace( '|', ',', $msg_types ) );
					}
				}
			}
		}

		if ( 'register' == $tag ) {
			if ( is_multisite() ) {
				// Multisite has different requirements.
				$result = wpmu_validate_user_signup( $this->post_data['username'], $this->post_data['user_email'] ); 
				$errors = $result['errors'];
				if ( $errors->errors ) {
					$wpmem_themsg = $errors->get_error_message(); 
					return $wpmem_themsg; 
					exit();
				}

			} else {
				// Validate username and email fields.
				$wpmem_themsg = ( email_exists( $this->post_data['user_email'] ) ) ? "email" : $wpmem_themsg;
				$wpmem_themsg = ( username_exists( $this->post_data['username'] ) ) ? "user" : $wpmem_themsg;
				$wpmem_themsg = ( ! is_email( $this->post_data['user_email']) ) ? wpmem_get_text( 'reg_valid_email' ) : $wpmem_themsg;
				$wpmem_themsg = ( ! validate_username( $this->post_data['username'] ) ) ? wpmem_get_text( 'reg_non_alphanumeric' ) : $wpmem_themsg;
				$wpmem_themsg = ( ! $this->post_data['username'] ) ? wpmem_get_text( 'reg_empty_username' ) : $wpmem_themsg;

				// If there is an error from username, email, or required field validation, stop registration and return the error.
				if ( $wpmem_themsg ) {
					return $wpmem_themsg;
					exit();
				}
			}

			// If form contains password and email confirmation, validate that they match.
			if ( array_key_exists( 'confirm_password', $this->post_data ) && $this->post_data['confirm_password'] != $this->post_data ['password'] ) { 
				$wpmem_themsg = wpmem_get_text( 'reg_password_match' );
			}
			if ( array_key_exists( 'confirm_email', $this->post_data ) && $this->post_data['confirm_email'] != $this->post_data ['user_email'] ) { 
				$wpmem_themsg = wpmem_get_text( 'reg_email_match' ); 
			}

			// Process CAPTCHA.
			if ( 0 != $wpmem->captcha ) {
				$check_captcha = WP_Members_Captcha::validate();
				if ( false === $check_captcha ) {
					return "empty"; // @todo Return and/or set error object. For now changed to return original value.
				}
			}

			// Check for user defined password.
			$this->post_data['password'] = wpmem_get( 'password', wp_generate_password() );

			// Add for _data hooks
			$this->post_data['user_registered'] = current_time( 'mysql', 1 );
			$this->post_data['user_role']       = get_option( 'default_role' );
			$this->post_data['wpmem_reg_ip']    = sanitize_text_field( wpmem_get_user_ip() );
			$this->post_data['wpmem_reg_url']   = esc_url_raw( wpmem_get( 'wpmem_reg_page', wpmem_get( 'redirect_to', false, 'request' ), 'request' ) );

			/*
			 * These native fields are not installed by default, but if they
			 * are added, use the $_POST value - otherwise, default to username.
			 * Value can be filtered with wpmem_register_data.
			 */
			$this->post_data['user_nicename']   = sanitize_title( wpmem_get( 'user_nicename', $this->post_data['username'] ) );
			$this->post_data['display_name']    = sanitize_text_field( wpmem_get( 'display_name', $this->post_data['username'] ) );
			$this->post_data['nickname']        = sanitize_text_field( wpmem_get( 'nickname', $this->post_data['username'] ) );
		}
	}

	/**
	 * Validates registration fields in the native WP registration.
	 *
	 * @since 2.8.3
	 * @since 3.3.0 Ported from wpmem_wp_reg_validate() in wp-registration.php.
	 *
	 * @global object $wpmem The WP-Members object class.
	 *
	 * @param  array  $errors               A WP_Error object containing any errors encountered during registration.
	 * @param  string $sanitized_user_login User's username after it has been sanitized.
	 * @param  string $user_email           User's email.
	 * @return array  $errors               A WP_Error object containing any errors encountered during registration.
	 */
	public function wp_register_validate( $errors, $sanitized_user_login, $user_email ) {

		global $wpmem;

		// Get any meta fields that should be excluded.
		$exclude = wpmem_get_excluded_meta( 'wp-register' );

		foreach ( wpmem_fields( 'register_wp' ) as $meta_key => $field ) {
			$is_error = false;
			if ( true == $field['required'] && true == $field['register'] && $meta_key != 'user_email' && ! in_array( $meta_key, $exclude ) ) {
				if ( ( $field['type'] == 'checkbox' || $field['type'] == 'multicheckbox' || $field['type'] == 'multiselect' || $field['type'] == 'radio' ) && ( ! isset( $_POST[ $meta_key ] ) ) ) {
					$is_error = true;
				} 
				if ( ( $field['type'] != 'checkbox' && $field['type'] != 'multicheckbox' && $field['type'] != 'multiselect' && $field['type'] != 'radio' ) && ( ! $_POST[ $meta_key ] ) ) {
					$is_error = true;
				}
				if ( $is_error ) {
					$errors->add( 'wpmem_error', sprintf( wpmem_get_text( 'reg_empty_field' ), esc_html__( $field['label'], 'wp-members' ) ) ); 
				}
			}
		}
		
		// Process CAPTCHA.
		if ( $wpmem->captcha > 0 ) {
			$check_captcha = WP_Members_Captcha::validate();
			if ( false === $check_captcha ) {
				$errors->add( 'wpmem_captcha_error', sprintf( wpmem_get_text( 'reg_captcha_err' ), esc_html__( $field['label'], 'wp-members' ) ) ); 
			}
		}

		return $errors;
	}
	
	/**
	 * User registration functions.
	 *
	 * @since 3.1.7
	 * @since 3.2.6 Added handler for membership field type.
	 * @since 3.3.0 Changed from register() to register_finalize().
	 *
	 * @global object $wpmem
	 * @param  int    $user_id
	 */
	public function register_finalize( $user_id ) {
		
		global $wpmem;

		// If this is WP-Members registration.
		if ( $this->reg_type['is_wpmem'] ) {
			// Put user ID into post_data array.
			$this->post_data['ID'] = $user_id;

			// Set remaining fields to wp_usermeta table.
			$new_user_fields_meta = array( 'user_url', 'first_name', 'last_name', 'description' );
			foreach ( $wpmem->fields as $meta_key => $field ) {
				// If the field is not excluded, update accordingly.
				if ( ! in_array( $meta_key, wpmem_get_excluded_meta( 'register' ) ) && ! in_array( $meta_key, $new_user_fields_meta ) ) {
					if ( $field['register'] && 'user_email' != $meta_key ) {
						// Assign memberships, if applicable.
						if ( 'membership' == $field['type'] && 1 == $wpmem->enable_products ) {
							wpmem_set_user_product( $this->post_data[ $meta_key ], $user_id );
						} else {
							update_user_meta( $user_id, $meta_key, $this->post_data[ $meta_key ] );
						}
					}
				}
			}

			// Store the registration url.
			update_user_meta( $user_id, 'wpmem_reg_url', $this->post_data['wpmem_reg_url'] );

			// Handle file uploads, if any.
			if ( ! empty( $_FILES ) ) {
				$this->upload_user_files( $user_id, $wpmem->fields );
			}
		}
	
		// If this is native WP (wp-login.php), Users > Add New, or WooCommerce registration.
		if ( $this->reg_type['is_native'] || $this->reg_type['is_add_new'] || $this->reg_type['is_woo'] ) {
			
			// Add new should process all fields.
			$which_fields = ( $this->reg_type['is_add_new'] ) ? 'all' : 'register_wp';
			
			// Get any excluded meta fields.
			$exclude = wpmem_get_excluded_meta( 'wp-register' );
			$fields  = wpmem_fields( $which_fields );
			if ( is_array( $fields ) && ! empty( $fields ) ) {
				foreach ( $fields as $meta_key => $field ) {
					$value = wpmem_get( $meta_key, false );
					if ( false !== $value && ! in_array( $meta_key, $exclude ) && 'file' != $field['type'] && 'image' != $field['type'] ) {
						if ( 'multiselect' == $field['type'] || 'multicheckbox' == $field['type'] ) {
							$value = implode( $field['delimiter'], $value );
						}
						$sanitized_value = sanitize_text_field( $value );
						update_user_meta( $user_id, $meta_key, $sanitized_value );
					}
				}
			}
		}
		
		// If this is Users > Add New.
		if ( is_admin() && $this->reg_type['is_add_new'] ) {
			// If moderated registration and activate is checked, set active flags.
			if ( 1 == $wpmem->mod_reg && isset( $_POST['activate_user'] ) ) {
				update_user_meta( $user_id, 'active', 1 );
				wpmem_set_user_status( $user_id, 0 );
			}
		}
		
		// Capture IP address of all users at registration.
		$user_ip = ( $this->reg_type['is_wpmem'] ) ? $this->post_data['wpmem_reg_ip'] : wpmem_get_user_ip(); // Sanitized at source now.
		update_user_meta( $user_id, 'wpmem_reg_ip', $user_ip );

	}
	
	/**
	 * Fires wpmem_post_register_data action.
	 *
	 * @since 3.3.2
	 *
	 * @param  int  $user_id
	 */
	public function post_register_data( $user_id ) {
		$this->post_data['ID'] = $user_id;
		/**
		 * Fires after user insertion but before email.
		 *
		 * @since 2.7.2
		 * @since 3.3.2 Hooked to user_register.
		 *
		 * @param array $this->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_register_data', $this->post_data );
	}
	
	/**
	 * Sends emails on registration.
	 *
	 * @since 3.3.0
	 *
	 * @param int $user_id
	 */
	public function register_email_to_user( $user_id ) {
		$send_notification = ( wpmem_is_reg_type( 'wpmem' ) ) ? true : false;
		/**
		 * Filter whether register notification is sent.
		 * 
		 * @since 3.5.0
		 * 
		 * @param  boolean  $send_notification
		 */
		$send_notification = apply_filters( 'wpmem_enable_user_notification', $send_notification, $user_id );
		if ( $send_notification ) {
			wpmem_email_to_user( array( 
				'user_id'      => $user_id, 
				'password'     => $this->post_data['password'],
				'tag'          => ( wpmem_is_enabled( 'mod_reg' ) ) ? 'newmod' : 'newreg', 
				'wpmem_fields' => wpmem_fields(), 
				'fields'       => $this->post_data 
			) );
		}
	}
	
	/**
	 * Sends admin notifiction on registration.
	 *
	 * @since 3.3.0
	 *
	 * @param int $user_id
	 */
	public function register_email_to_admin( $user_id ) {
		$allowed_reg_types = array( 'wpmem' ); // @todo
		$send_notification = ( wpmem_is_reg_type( 'wpmem' ) && wpmem_is_enabled( 'notify' ) ) ? true : false;
		/**
		 * Filter whether register notification is sent.
		 * 
		 * @since 3.5.0
		 * 
		 * @param  boolean  $send_notification
		 */
		$send_notification = apply_filters( 'wpmem_enable_admin_notification', $send_notification, $user_id, wpmem_fields(), $this->post_data );
		if ( $send_notification ) {
			wpmem_notify_admin( $user_id, wpmem_fields(), $this->post_data );
		}
	}
	
	/**
	 * Redirects user on registration.
	 *
	 * @since 3.1.7
	 */
	public function register_redirect() {
		$redirect_to = wpmem_get( 'redirect_to', false );
		if ( $redirect_to ) {
			$nonce_url = wp_nonce_url( $redirect_to, 'register_redirect', 'reg_nonce' );
			wp_safe_redirect( $nonce_url );
			exit();
		}
	}
	
	/**
	 * Password change or reset.
	 *
	 * @since 3.1.7
	 *
	 * @param  string $action
	 * @return string $result
	 */
	public function password_update( $action ) {
		$this->pwd_reset = new WP_Members_Pwd_Reset;
		if ( isset( $_POST['formsubmit'] ) ) {
			if ( 'link' == $action ) {
				$user = wpmem_get( 'user', false );
				$user = ( strpos( $user, '@' ) ) ? sanitize_email( $user ) : sanitize_user( $user );
				$args = array( 'user' => $user );
				return $this->password_link( $args );
			} else {
				$args = array(
					'pass1' => wpmem_get( 'pass1', false ),
					'pass2' => wpmem_get( 'pass2', false ),
				);
				return $this->password_change( $args );
			}
		}
		return;
	}
	
	/**
	 * Change a user's password()
	 *
	 * @since 3.1.7
	 *
	 * @return
	 */
	public function password_change( $args ) {
		global $user_ID;
		$is_error = false;
		// Check for both fields being empty.
		$is_error = ( ! $args['pass1'] && ! $args['pass2'] ) ? "pwdchangempty" : $is_error;
		// Make sure the fields match.
		$is_error = ( $args['pass1'] != $args['pass2'] ) ? "pwdchangerr" : $is_error;
		/**
		 * Filters the password change error.
		 *
		 * @since 3.1.5
		 * @since 3.1.7 Moved to user object.
		 *
		 * @param string $is_error
		 * @param int    $user_ID  The user's numeric ID.
		 * @param string $args['pass1']    The user's new plain text password.
		 */
		$is_error = apply_filters( 'wpmem_pwd_change_error', $is_error, $user_ID, $args['pass1'] );
		// User must be logged in OR must be resetting a forgotten password.
		$is_error = ( ! is_user_logged_in() && 'set_password_from_key' != wpmem_get( 'a', false, 'request' ) ) ? "loggedin" : $is_error;
		// Verify nonce.
		$is_error = ( ! wp_verify_nonce( $_REQUEST['_wpmem_pwdchange_nonce'], 'wpmem_shortform_nonce' ) ) ? "reg_generic" : $is_error;
		if ( $is_error ) {
			return $is_error;
		}
		/**
		 * Fires after password change.
		 *
		 * @since 2.9.0
		 * @since 3.0.5 Added $args['pass1'] to arguments passed.
		 * @since 3.1.7 Moved to user object.
		 *
		 * @param int    $user_ID The user's numeric ID.
		 * @param string $args['pass1']   The user's new plain text password.
		 */
		do_action( 'wpmem_pwd_change', $user_ID, $args['pass1'] );
		return "pwdchangesuccess";
	}

	/**
	 * Reset a user's password.
	 * 
	 * Replaces WP_Members_User::password_reset()
	 *
	 * @since Unknown
	 *
	 */
	public function password_link( $args ) {
		global $wpmem;
		/**
		 * Filter the password reset arguments.
		 *
		 * @since 3.4.2
		 *
		 * @param array The username or email.
		 */
		$arr = apply_filters( 'wpmem_pwdreset_args', $args );

		$errors = new WP_Error();

		if ( ! isset( $arr['user'] ) || '' == $arr['user'] ) { 
			// There was an empty field.
			$errors->add( 'empty', wpmem_get_text( 'pwd_reset_empty' ) );
			return "pwdreseterr";

		} else {

			if ( ! wp_verify_nonce( $_REQUEST['_wpmem_pwdreset_nonce'], 'wpmem_shortform_nonce' ) ) {
				$errors->add( 'nonce', wpmem_get_text( 'pwd_reset_nonce' ) );
				return "reg_generic";
			}

			$user_to_check = ( strpos( $arr['user'], '@' ) ) ? sanitize_email( $arr['user'] ) : sanitize_user( $arr['user'] );

			if ( username_exists( $user_to_check ) ) {
				$user = get_user_by( 'login', $user_to_check );
			} elseif ( email_exists( $user_to_check ) ) {
				$user = get_user_by( 'email', $user_to_check );
			} else {
				$user = false;
			}

			if ( ! is_wp_error( $user ) && false != $user ) {

				$has_error = false;

				// Check if user is approved.
				if ( ( wpmem_is_mod_reg() ) && ( ! wpmem_is_user_activated( $user->ID ) ) ) {
					$errors->add( 'acct_not_approved', wpmem_get_text( 'acct_not_approved' ) );
					$has_error = true;
				}

				// Check if user is validated.
				if ( ( wpmem_is_act_link() ) && ( ! wpmem_is_user_confirmed( $user->ID ) ) ) {
					$errors->add( 'acct_not_validated', wpmem_get_text( 'acct_not_validated' ) );
					$has_error = true;
				}

				// If either of these are an error, dump the user object.
				$user = ( $has_error ) ? false : $user;
			}

			if ( $user ) {
				wpmem_email_to_user( array( 'user_id'=>$user->ID, 'tag'=>'repass' ) );
				/** This action is documented in /includes/class-wp-members-user.php */
				do_action( 'wpmem_pwd_reset', $user->ID, '' );
				return "pwdresetsuccess";

			} else {
				// Username did not exist, or cannot reset password.
				if ( $errors->has_errors() ) {
					$wpmem->error = $errors;
				}
				return "pwdreseterr";
			}
		}
		return;
	}
	
	/**
	 * Handles resending a confirmation link email.
	 *
	 * @since 3.5.0
	 *
	 * @global object $wpmem
	 * @return string $regchk The regchk value.
	 */
	public function resend_confirm() {
		if ( isset( $_POST['formsubmit'] ) ) {
			
			if ( ! wp_verify_nonce( $_REQUEST['_wpmem_reconfirm_nonce'], 'wpmem_shortform_nonce' ) ) {
				return "reg_generic";
			}

			$check_user = wpmem_get( 'user', false );

			$sanitized_user_to_check = ( strpos( $check_user, '@' ) ) ? sanitize_email( $check_user ) : sanitize_user( $check_user );

			if ( username_exists( $sanitized_user_to_check ) ) {
				$user = get_user_by( 'login', $sanitized_user_to_check );
			} elseif ( email_exists( $sanitized_user_to_check ) ) {
				$user = get_user_by( 'email', $sanitized_user_to_check );
			} else {
				$user = false;
			}	

			if ( $user ) {
				// Send it in an email.
				wpmem_email_to_user( array( 
					'user_id' => intval( $user->ID ),
					'tag' => ( wpmem_is_enabled( 'mod_reg' ) ) ? 'newmod' : 'newreg'
				) );
				/**
				 * Fires after resending confirmation.
				 *
				 * @since 3.1.5
				 *
				 * @param int $user_ID The user's numeric ID.
				 */
				do_action( 'wpmem_reconfirm', intval( $user->ID ) );
				return 'reconfirmsuccess';
			} else {
				return 'reconfirmfailed';
			}
		}
		return;
	}

	/**
	 * Handles resending a confirmation email.
	 *
	 * @since 3.5.0
	 *
	 * @global object $wpmem
	 * @return string $regchk The regchk value.
	 */
	public function retrieve_username() {
		if ( isset( $_POST['formsubmit'] ) ) {
			
			if ( ! wp_verify_nonce( $_REQUEST['_wpmem_getusername_nonce'], 'wpmem_shortform_nonce' ) ) {
				return "reg_generic";
			}
			
			$sanitized_email = wpmem_get_sanitized( 'user_email', false, 'post', 'email' );
			$user  = ( false != $sanitized_email ) ? get_user_by( 'email', $sanitized_email ) : false;
			if ( $user ) {
				// Send it in an email.
				wpmem_email_to_user( array( 'user_id'=>intval( $user->ID ), 'tag'=>'getuser' ) );
				/**
				 * Fires after retrieving username.
				 *
				 * @since 3.0.8
				 *
				 * @param int $user_ID The user's numeric ID.
				 */
				do_action( 'wpmem_get_username', intval( $user->ID ) );
				return 'usernamesuccess';
			} else {
				return 'usernamefailed';
			}
		}
		return;
	}
	
	/**
	 * Handle user file uploads for registration and profile update.
	 *
	 * @since 3.1.8
	 * @since 3.2.6 Add file's post ID to $this->post_data.
	 * @since 3.4.7 Add wpmem_file_uploaded action hook.
	 *
	 * @param string $user_id
	 * @param array  $fields
	 */
	public function upload_user_files( $user_id, $fields ) {
		global $wpmem;
		foreach ( $fields as $meta_key => $field ) {
			if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) && isset( $_FILES[ $meta_key ] ) && is_array( $_FILES[ $meta_key ] ) ) {
				if ( ! empty( $_FILES[ $meta_key ]['name'] ) ) {
					// Upload the file and save it as an attachment.
					$file_post_id = $wpmem->forms->do_file_upload( $_FILES[ $meta_key ], $user_id );
					// Save the attachment ID as user meta.
					update_user_meta( $user_id, $meta_key, $file_post_id );
					// Add attachment ID to post data array.
					$this->post_data[ $meta_key ] = $file_post_id;
					/**
					 * User uploaded file.
					 * 
					 * @since 3.4.7
					 * 
					 * @param int    $user_id
					 * @param string $meta_key
					 * @param string $file_post_id
					 */
					do_action( 'wpmem_file_uploaded', $user_id, $meta_key, $file_post_id );
				}
			}
		}
	}
	
	/**
	 * Get user data for all fields in WP-Members.
	 *
	 * Retrieves user data for all WP-Members fields (and WP default fields)
	 * in an array keyed by WP-Members field meta keys.
	 *
	 * @since 3.2.0
	 * @since 3.2.6 Added option for "all" fields (default:false).
	 *
	 * @param  string $user_id optional (defaults to current user)
	 * @param  string $all     optional (default to false)
	 * @return array  $user_fields 
	 */
	public function user_data( $user_id = false, $all = false ) {
		$user_id = ( $user_id ) ? $user_id : get_current_user_id();
		if ( true == $all ) {
			$user_info = get_user_meta( $user_id ); 
			foreach( $user_info as $key => $value ) {
				$formatted = maybe_unserialize( $value[0] );
				$user_fields[ $key ] = $formatted;
			}
		} else {
			$fields = wpmem_fields();
			$user_data = get_userdata( $user_id );
			$excludes = array( 'first_name', 'last_name', 'description', 'nickname' );
			foreach ( $fields as $meta => $field ) {
				$meta = ( 'username' == $meta ) ? 'user_login' : $meta;
				if ( $field['native'] == 1 && ! in_array( $meta, $excludes ) ) {
					$user_fields[ $meta ] = $user_data->data->{$meta};
				} else {
					$user_fields[ $meta ] = get_user_meta( $user_id, $meta, true );
				}
			}
		}
		return $user_fields;
	}
	
	/**
	 * Sets the role for the specified user.
	 *
	 * @since 3.2.0
	 *
	 * @param integer $user_id
	 * @param string  $role
	 * @param string  $action (set|add|remove)
	 */
	public function update_user_role( $user_id, $role, $action = 'set' ) {
		$user = new WP_User( $user_id );
		switch ( $action ) {
			case 'add':
				$user->add_role( $role );
				break;
			case 'remove':
				$user->remove_role( $role );
				break;
			default:
				$user->set_role( $role );
				break;
		}
	}
	
	/**
	 * Sets a user's password.
	 *
	 * @since 3.2.3
	 *
	 * @param	int		$user_id
	 * @param	string	$password
	 */
	public function set_password( $user_id, $password ) {
		wp_set_password( $password, $user_id );
	}
	
	/**
	 * Sets user as logged on password change.
	 *
	 * (Hooked to wpmem_pwd_change)
	 *
	 * @since 3.2.0
	 *
	 * @param	int		$user_id
	 * @param	string	$password
	 */
	public function set_as_logged_in( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		/**
		 * Sets the WP auth cookie.
		 *
		 * May trigger the following WP filter/actions:
		 * - auth_cookie_expiration
		 * - secure_auth_cookie
		 * - secure_logged_in_cookie
		 * - set_auth_cookie
		 * - set_logged_in_cookie
		 * - send_auth_cookies
		 *
		 * @see https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
		 */
		wp_set_auth_cookie( $user_id, wpmem_get( 'rememberme', false, 'request' ) );
		/**
		 * Sets the user as logged in.
		 *
		 * May trigger the folloiwng WP filter/actions:
		 * - set_current_user
		 *
		 * @see https://developer.wordpress.org/reference/functions/wp_set_current_user/
		 */
		wp_set_current_user( $user_id, $user->user_login );
	}
	
	/**
	 * Validates user access to content.
	 *
	 * @since 3.2.0
	 * @todo Currently checks in this order: expiration, role, "other". If expiration product,
	 *       and the user is current, then access is granted. This doesn't consider if the 
	 *       user is current but does not have a required role (if BOTH an expiration and role
	 *       product). Maybe add role checking to the expiration block if both exist.
	 *
	 * @global object $wpmem
	 * @param  mixed  $membership Accepts a single membership slug/meta, or an array of multiple memberships.
	 * @param  int    $user_id (optional)
	 * @return bool   $access
	 */
	public function has_access( $membership, $user_id = false ) {
		global $wpmem;
		if ( ! is_user_logged_in() && ! $user_id ) {
			return false;
		}
		
		// Product must be an array.
		$membership_array = ( ! is_array( $membership ) ) ? explode( ",", $membership ) : $membership;

		$membership_array = $this->get_membership_stack( $membership_array );

		// Current user or requested user.
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		
		// Load user memberships array.
		$memberships = ( false == $user_id ) ? $this->access : wpmem_get_user_memberships( $user_id );

		// Start by assuming no access.
		$access  = false;

		// Start checking memberships. If the user has a valid membership, quit checking.
		foreach ( $membership_array as $prod ) {
			$expiration_product = false;
			$role_product = false;
			// Does the user have this membership?
			if ( isset( $memberships[ $prod ] ) ) {
				// Is this an expiration membership?
				if ( isset( $wpmem->membership->memberships[ $prod ]['expires'][0] ) && ! is_bool( $memberships[ $prod ] ) ) {
					$expiration_product = true;  
					if ( $this->is_current( $memberships[ $prod ] ) ) {
						$access = true;
						break;
					}
				}
				// Is this a role membership?
				if ( '' != wpmem_get_membership_role( $prod ) ) {
					$role_product = true;
					if ( $memberships[ $prod ] && wpmem_user_has_role( wpmem_get_membership_role( $prod ) ) ) {
						if ( $expiration_product && ! $this->is_current( $memberships[ $prod ] ) ) {
							$access = false;
							break;
						}
						$access = true;
						break;
					}
				}
				if ( ! $expiration_product && ! $role_product && $memberships[ $prod ] ) {
					$access = true;
					break;
				}
			}
		}
		
		/**
		 * Filter the access result.
		 *
		 * @since 3.2.0
		 * @since 3.2.3 Added $membership argument.
		 *
		 * @param  boolean $access
		 * @param  array   $membership
		 * @param  integer $user_id
		 */
		return apply_filters( 'wpmem_user_has_access', $access, $membership_array, $user_id );

	}
	
	/**
	 * Gets membership hierarchy (if any).
	 *
	 * Replaces original get_product_children() from 3.4.0 which was not as scalable.
	 *
	 * @since 3.4.1
	 *
	 * @global stdClass $wpmem
	 * @param  array    $membership_array
	 * $return array    $membership_array Product array with child products added.
	 */
	public function get_membership_stack( $membership_array ) {

		global $wpdb, $wpmem;

		$membership_ids = wpmem_get_memberships_ids();
		foreach ( $membership_array as $membership ) {
			// If the membership exists, check for child/parent relationships (if it doesn't exist and we didn't check here, we'd throw an undefined index error).
			if ( isset( $membership_ids[ $membership ] ) ) {
				// Do we need child access?
				$child_access = get_post_meta( $membership_ids[ $membership ], 'wpmem_product_child_access', true );
				if ( 1 == $child_access ) {
					$args = array(
						'post_type'   => $wpmem->membership->post_type,
						'post_parent' => $membership_ids[ $membership ], // Current post's ID
					);
					//Replaces use of get_children, which unfortunately causes an infinite loop.
					$sql = 'SELECT post_name FROM ' . $wpdb->prefix . 'posts WHERE post_type = "' . esc_sql( $args['post_type'] ) . '" AND post_parent = "' . esc_sql( $args['post_parent'] ) . '";';
					$children = $wpdb->get_results( $sql );
					if ( ! empty( $children ) ) {
						foreach ( $children as $child ) {
							$membership_array[] = $child->post_name;
						}
					}
				} 
				// Ancestor access is by default.
				$ancestors = get_post_ancestors( $membership_ids[ $membership ] );
				if ( ! empty( $ancestors ) ) {
					foreach ( $ancestors as $ancestor ) {
						$membership_array[] = get_post_field( 'post_name', $ancestor );
					}
				}
			}
		}		
		return $membership_array;
	}
	
	/**
	 * Loads anything the user has access to.
	 *
	 * @since 3.2.0
	 * @since 3.2.6 Updated to return empty array if no products exist for this user.
	 * @since 3.3.0 Updated to use individual meta for product access.
	 *
	 * @global object $wpmem
	 *
	 * @param  int      $user_id
	 * @param  stdClass $obj
	 * @return array    $memberships {
	 *     Memberships the user has as an array keyed by the membership slug.
	 *     Memberships the user does not have enabled are not in the array.
	 *     If a memberships is an expiration product, the expiration is the  
	 *     value as a timestamp (epoch time). Note that access can be checked
	 *     with wpmem_user_has_access() or wpmem_user_is_expired().
	 *
	 *     @type mixed 1|timestamp
	 * }
	 */
	function get_user_products( $user_id = false, $obj = false ) {
		global $wpmem;
		$membership_array = ( $obj ) ? $obj->membership->memberships : ( ( isset( $wpmem->membership->memberships ) ) ? $wpmem->membership->memberships : array() );
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		foreach ( $membership_array as $membership_meta => $membership ) {
			$user_product = get_user_meta( $user_id, '_wpmem_products_' . $membership_meta, true );
			if ( $user_product ) {
				$memberships[ $membership_meta ] = $user_product;
			}
			$user_product = '';
		}
		return ( isset( $memberships ) ) ? $memberships : array();
	}
	
	/**
	 * Sets a product as active for a user.
	 *
	 * If the product expires, it sets an expiration date
	 * based on the time period. Otherwise the value is
	 * set to "true" (which does not expire).
	 *
	 * @since 3.2.0
	 * @since 3.2.6 Added $date to set a specific expiration date.
	 * @since 3.3.0 Updated to new single meta, keeps legacy array for rollback.
	 * @since 3.3.1 Added no gap renewal option.
	 *
	 * @param string $membership
	 * @param int    $user_id
	 * @param string $set_date Formatted date should be MySQL timestamp, or simply YYYY-MM-DD.
	 */
	function set_user_product( $membership, $user_id = false, $set_date = false ) {
		
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		
		// New single meta format. @todo This remains when legacy array is removed.
		$prev_value = get_user_meta( $user_id, '_wpmem_products_' . $membership, true );

		// Convert date to add.
		$expiration_period = wpmem_get_expiration_period( $membership ); // Only needs to check if it's an expriation membership?
		
		$renew = ( $prev_value ) ? true : false;
	
		// If membership is an expiration product.
		if ( $expiration_period ) {
			$new_value = wpmem_generate_membership_expiration_date( $membership, $user_id, $set_date, $prev_value, $renew );
		} else {
			$new_value = true;
		}
		
		// Update product setting.
		update_user_meta( $user_id, '_wpmem_products_' . $membership, $new_value );
		
		// Update the legacy setting.
		$user_products = get_user_meta( $user_id, '_wpmem_products', true );
		$user_products = ( $user_products ) ? $user_products : array();
		$user_products[ $membership ] = ( true === $new_value ) ? true : date( 'Y-m-d H:i:s', $new_value );
		update_user_meta( $user_id, '_wpmem_products', $user_products );

		/**
		 * Fires when a user product has been set.
		 *
		 * @since 3.3.0
		 *
		 * @param  int    $user_id
		 * @param  string $membership
		 * @param  mixed  $new_value
		 * @param  string $prev_value
		 * @param  bool   $renew
		 */
		do_action( 'wpmem_user_product_set', $user_id, $membership, $new_value, $prev_value, $renew );
 
	}
	
	/**
	 * Removes a product from a user.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Updated for new single meta, keeps legacy array for rollback.
	 *
	 * @param string $membership
	 * @param int    $user_id
	 */
	function remove_user_product( $membership, $user_id = false ) {
		global $wpmem;
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		
		// @todo Legacy version.
		$user_products = get_user_meta( $user_id, '_wpmem_products', true );
		$user_products = ( $user_products ) ? $user_products : array();
		if ( $user_products ) {
			unset( $user_products[ $membership ] );
			update_user_meta( $user_id, '_wpmem_products', $user_products );
		}
		
		// @todo New version.
		return delete_user_meta( $user_id, '_wpmem_products_' . $membership );
	}
	
	/**
	 * Utility for expiration validation.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Validates date or epoch time.
	 *
	 * @param date $date
	 */
	function is_current( $date ) {
		$date = ( is_numeric( $date ) ) ? $date : strtotime( $date );
		return ( time() < $date ) ? true : false;
	}
	
	/**
	 * Check if a user is activated.
	 *
	 * @since 3.2.2
	 *
	 * @param  int   $user_id
	 * @return bool  $active
	 */
	function is_user_activated( $user_id = false ) {
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		$active  = get_user_meta( $user_id, 'active', true );
		$is_activated = ( 1 == $active ) ? true : false;
		/**
		 * Filter whether the user is active or not.
		 *
		 * @since 3.3.5
		 *
		 * @param bool $is_activated
		 * @param int  $user_id
		 */
		return apply_filters( 'wpmem_is_user_activated', $is_activated, $user_id ); 
	}

	/**
	 * Checks if a user is activated during user authentication.
	 *
	 * @since 2.7.1
	 * @since 3.2.0 Moved from core to user object.
	 *
	 * @param  object $user     The WordPress User object.
	 * @param  string $username The user's username (user_login).
	 * @param  string $password The user's password.
	 * @return object $user     The WordPress User object.
	 */ 
	function check_activated( $user, $username, $password ) {
		if ( ! is_wp_error( $user ) && ! is_null( $user ) && false == $this->is_user_activated( $user->ID ) ) {
			$msg = sprintf( wpmem_get_text( 'user_not_activated' ), '<strong>', '</strong>' );
			/**
			 * Filter the activation message.
			 * 
			 * @since 3.5.0
			 * 
			 * @param string
			 */
			$msg = apply_filters( 'wpmem_user_not_activated_msg', $msg );
			$user = new WP_Error( 'authentication_failed', $msg );
		}
		/**
		 * Filters the check_validated result.
		 * 
		 * @since 3.4.2
		 * 
		 * @param  mixed  $user
		 * @param  string $username
		 * @param  string $password
		 */
		return apply_filters( 'wpmem_check_activated', $user, $username, $password );
	}
	
	/**
	 * Prevents users not activated from resetting their password.
	 *
	 * @since 2.5.1
	 * @since 3.2.0 Moved to user object, renamed no_reset().
	 *
	 * @return bool Returns false if the user is not activated, otherwise true.
	 */
	function no_reset() {
		global $wpmem;
		$raw_val = wpmem_get( 'user_login', false );
		if ( $raw_val ) {
			if ( strpos( $raw_val, '@' ) ) {
				$user = get_user_by( 'email', sanitize_email( $raw_val ) );
			} else {
				$username = sanitize_user( $raw_val );
				$user     = get_user_by( 'login', $username );
			}
			if ( $wpmem->mod_reg == 1 ) { 
				if ( get_user_meta( $user->ID, 'active', true ) != 1 ) {
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * Set expiration for PayPal Subscriptions extension.
	 *
	 * @since 3.3.0
	 *
	 * @global object $wpmem
	 *
	 * @param int $user_id
	 */
	function set_user_exp( $user_id ) {
		global $wpmem;
		// Set user expiration, if used.
		if ( 1 == $wpmem->use_exp && 1 != $wpmem->mod_reg ) {
			if ( function_exists( 'wpmem_set_exp' ) ) {
				wpmem_set_exp( $user_id );
			}
		}
	}
	
	/**
	 * Sets default membership product access (if applicable).
	 *
	 * @since 3.3.0
	 *
	 * @global object $wpmem
	 *
	 * @param int $user_id
	 */
	function set_default_product( $user_id ) {
		global $wpmem;
		
		// Get default memberships.
		$default_products = $wpmem->membership->get_default_products();
		
		// Assign any default memberships to user.
		foreach ( $default_products as $membership ) {
			wpmem_set_user_product( $membership, $user_id );
		}
	}

	/**
	 * Checks user file upload folder for an index.php file.
	 * 
	 * @since 3.4.9.4
	 * 
	 * @param int    $user_id
	 * @param string $meta_key
	 * @param string $file_post_id
	 */
	public function check_folder_for_index( $user_id, $meta_key, $file_post_id ) {
		$upload_vars = wp_upload_dir( null, false );
		wpmem_create_file( array(
			'path'     => $upload_vars['path'],
			'name'     => 'index.php',
			'contents' => "<?php // Silence is golden."
		) );
	}
}