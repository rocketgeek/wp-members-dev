<?php
/**
 * Handles email validation link for new user registrations.
 * 
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Validation_Link {
	
	/**
	 * Meta containers
	 *
	 * @since 3.3.5
	 */
	public $validation_confirm = '_wpmem_user_confirmed';
	
	/**
	 * Options.
	 *
	 * @since 3.3.5
	 */
	public $send_welcome = true;
	public $show_success = true;
	public $send_notify  = true;
	public $validated = false;
	public $email_text;
	public $invalid_message;
	public $success_message;
	public $moderated_message;
	
	/**
	 * Initialize validation link feature.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {
		
		$defaults = array(
			'email_text'        => wpmem_get_text( 'validate_email_text' ), // 'Click to validate your account: '
			'success_message'   => wpmem_get_text( 'validate_success_msg' ), // 'Thank you for validating your account.'
			'invalid_message'   => wpmem_get_text( 'validate_invalid_msg' ), // 'Validation key was expired or invalid'
			'moderated_message' => wpmem_get_text( 'validate_moderated_msg' ), // 'Your account is now pending approval'
		);
		
		/**
		 * Filter default dialogs.
		 *
		 * @since 3.3.8
		 *
		 * @param array $defaults
		 */
		$defaults = apply_filters( 'wpmem_validation_link_default_dialogs', $defaults );
		
		foreach ( $defaults as $key => $value ) {
			$this->{$key} = $value;
		}
		
		add_action( 'the_content',        array( $this, 'maybe_process_validation' ) );
		add_action( 'template_redirect',  array( $this, 'validate_key'       ) );
		add_filter( 'authenticate',       array( $this, 'check_validated'    ), 99, 3 );
		add_filter( 'wpmem_email_filter', array( $this, 'add_key_to_email'   ), 10, 3 );
		add_filter( 'the_content',        array( $this, 'validation_success' ), 100 );
		
		add_action( 'wpmem_account_validation_success', array( $this, 'set_as_logged_in' ), 9 );
		add_action( 'wpmem_account_validation_success', array( $this, 'send_welcome' ) );
		add_action( 'wpmem_account_validation_success', array( $this, 'notify_admin' ) );
	}
	
	/**
	 * Include the validation key in the new user registration email as a validation link.
	 *
	 * @since 3.3.5
	 *
	 * @param  array    $arr
	 * @param  array    $wpmem_fields
	 * @param  array    $field_data
	 * @return array
	 */
	public function add_key_to_email( $arr, $wpmem_fields, $field_data ) {

		// Only do this for new registrations.
		$email_type = ( wpmem_is_enabled( 'mod_reg' ) ) ? 'newmod' : 'newreg';
		if ( $arr['toggle'] == $email_type ) {

			$user = get_user_by( 'ID', intval( $arr['user_id'] ) );

			/**
			 * Gets the user based on the password key.
			 *
			 * WP filters/actions triggered:
			 * - retrieve_password
			 * - allow_password_reset
			 * - retrieve_password_key
			 *
			 * @see: https://developer.wordpress.org/reference/functions/get_password_reset_key/
			 * @param WP_User User to retrieve password reset key for.
			 * @return string|WP_Error Password reset key on success. WP_Error on error.
			 */
			$key = $this->set_validation_key( $user );

			// Generate confirm link.
			/**
			 * Filter the return url
			 *
			 * @since 3.3.5
			 * @since 3.3.9 Added $user object
			 *
			 * @param string The link URL (trailing slash recommended).
			 * @param object $user
			 */
			$url = apply_filters( 'wpmem_validation_link_return_url', trailingslashit( wpmem_profile_url() ), $user );
			$query_args = array(
				'a'     => 'confirm',
				'key'   => $key,
				'login' => $user->user_login,
			);
			
			// urlencode, primarily for user_login with a space.
			$query_args = array_map( 'rawurlencode', $query_args );
			
			$link = add_query_arg( $query_args, trailingslashit( $url ) );
			
			/**
			 * Filter the confirmation link.
			 *
			 * @since 3.3.9
			 *
			 * @param  string  $link
			 * @param  string  $url
			 * @param  array   $query_args
			 */
			$link = apply_filters( 'wpmem_validation_link', $link, $url, $query_args );

			$sanitized_link = esc_url_raw( $link );
		
			// Does email body have the [confirm_link] shortcode?
			if ( strpos( $arr['body'], '[confirm_link]' ) ) {
				$arr['body'] = str_replace( '[confirm_link]', $sanitized_link, $arr['body'] );
			} else {
				// Add text and link to the email body.
				$arr['body'] = $arr['body'] . "\r\n"
					. $this->email_text . ' ' . $sanitized_link;
			}
		}

		return $arr;
	}

	/**
	 * Checks for validation key in the URL and starts validation process.
	 * 
	 * @since 3.5.7
	 * 
	 * @param  string $content The content to potentially replace with validation confirmation form.
	 * @return string The original content or the validation confirmation form if validation is being processed.
	 */
	public function maybe_process_validation( $content ) {
		if ( 'confirm' == wpmem_get( 'a', false, 'get' ) ) {
			$defaults = array(
				'div_id'       => 'wpmem_email_confirm',
				'div_class'    => 'wpmem-email-confirm',
				'form_id'      => 'wpmem_email_confirm_form',
				'form_class'   => 'wpmem-email-confirm-form',
				'button_id'    => 'wpmem_email_confirm_button',
				'button_class' => 'button',
				'post_url'     => wpmem_profile_url(),
				'key'          => wpmem_get_sanitized( 'key', false, 'get', 'key' ),
				'login'        => wpmem_get_sanitized( 'login', false, 'get', 'text' ),
				'button_txt'   => wpmem_get_text( 'validate_confirm_btn' ),
				'confirm_txt'  => '<p>' . esc_html__( 'Please click the button below to confirm your email address and complete your registration:', 'wp-members' ) . '</p>',
				'redirect_to'  => wpmem_get( 'redirect_to', "", 'get' ),
			);
			
			/**
			 * Filter the arguments for the validation confirmation form.
			 * 
			 * @since 3.5.7
			 * 
			 * @param array $defaults Values for the variables used in the form. {
			 *    @type string $div_id       The ID attribute for the form container div.
			 *    @type string $div_class    The class attribute for the form container div.
			 *    @type string $form_id      The ID attribute for the form element.
			 *    @type string $form_class   The class attribute for the form element.
			 *    @type string $button_id    The ID attribute for the form submit button.
			 *    @type string $button_class The class attribute for the form submit button.
			 *    @type string $post_url     The URL the confirmation form submits to.
			 *    @type string $key          The validation key included as a hidden field in the form.
			 *    @type string $login        The user login included as a hidden field in the form.
			 *    @type string $button_txt   The text to display in the confirmation button.
			 *    @type string $confirm_txt  The text to display above the confirmation button.
			 * }
			 */
			$args = apply_filters( 'wpmem_validation_confirmation_form_args', $defaults );
			
			// Merge defaults with any args passed via the filter, just in case there is a missing value.
			$args = wp_parse_args( $args, $defaults );
			
			// Build the confirmation form.
			$form = '<div id="' . esc_attr( $args['div_id'] ) . '" class="' . esc_attr( $args['div_class'] ) . '">
				' . $args['confirm_txt'] . '
				<form id="' . esc_attr( $args['form_id'] ) . '" class="' . esc_attr( $args['form_class'] ) . '" method="post" action="' . esc_url( $args['post_url'] ) . '">
					<input type="hidden" name="a" value="confirm_validate" />
					<input type="hidden" name="key" value="' . esc_attr( $args['key'] ) . '" />
					<input type="hidden" name="login" value="' . esc_attr( $args['login'] ) . '" />
					<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect_to'] ) . '" />
					<button type="submit" id="' . esc_attr( $args['button_id'] ) . '" class="' . esc_attr( $args['button_class'] ) . '">' . esc_html( $args['button_txt'] ) . '</button>
				</form>
			</div>';
			/**
			 * Filter the validation confirmation content.
			 * 
			 * @since 3.5.7
			 * 
			 * @param string $form The HTML content to display on the validation confirmation page.
			 * @param array  $args Values for the variables used in the form. {
			 *    @type string $div_id       The ID attribute for the form container div.
			 *    @type string $div_class    The class attribute for the form container div.
			 *    @type string $form_id      The ID attribute for the form element.
			 *    @type string $form_class   The class attribute for the form element.
			 *    @type string $button_id    The ID attribute for the form submit button.
			 *    @type string $button_class The class attribute for the form submit button.
			 *    @type string $post_url     The URL the confirmation form submits to.
			 *    @type string $key          The validation key included as a hidden field in the form.
			 *    @type string $login        The user login included as a hidden field in the form.
			 *    @type string $button_txt   The text to display in the confirmation button.
			 *    @type string $confirm_txt  The text to display above the confirmation button.
			 * }
			 */
			return apply_filters( 'wpmem_validation_confirmation_form', $form, $args );
		}
		return $content;
	}

	/**
	 * Check for a validation key and if one exists, validate and log in user.
	 *
	 * @since 3.3.5
	 */
	public function validate_key() {
		
		// Check for validation key.
		$key   = ( 'confirm_validate' == wpmem_get( 'a', false ) ) ? wpmem_get( 'key',   false ) : false;
		$login = ( 'confirm_validate' == wpmem_get( 'a', false ) ) ? wpmem_get( 'login', false ) : false;

		// Do not attempt validation if key or login is missing.
		if ( false !== $key ) {

			// Set an error container.
			$errors = new WP_Error();

			/**
			 * Validate the key.
			 *
			 * WP_Error will be invalid_key or expired_key. Process triggers password_reset_expiration filter
			 * filtering DAY_IN_SECONDS default. Filter password_reset_key_expired is also triggered filtering
			 * the return value (which can be used to override the expired/invalid check based on user_id).
			 *
			 * WP filter/actions triggered:
			 * - password_reset_expiration
			 * - password_reset_key_expired
			 *
			 * @see https://developer.wordpress.org/reference/functions/check_password_reset_key/
			 * @param string Hash to validate sending user's password.
			 * @param string The user login.
			 * @return WP_User|WP_Error WP_User object on success, WP_Error object for invalid or expired keys (invalid_key|expired_key).
			 */
			$user = check_password_reset_key( $key, $login );

			if ( ! is_wp_error( $user ) ) {

				$this->validated = true;

				// Delete validation_key meta and set active.
				$this->clear_activation_key( $user->ID );
				$this->set_as_confirmed( $user->ID );

				/**
				 * Fires when a user has successfully validated their account.
				 *
				 * @since 3.3.5
				 *
				 * @param int $user_id
				 */
				do_action( 'wpmem_account_validation_success', $user->ID );

			} else {
				$this->validated = false;

				/**
				 * Fires when a user account validation failed.
				 *
				 * @since 3.3.5
				 *
				 * @param int $user_id
				 */
				do_action( 'wpmem_account_validation_failed', $user->ID );
			}
		}
		return;
	}

	/**
	 * Display messaging.
	 *
	 * Shows success if key validates, expired if it does not.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $content
	 * @return string  $content
	 */
	public function validation_success( $content ) {

		if ( $this->show_success && 'confirm_validate' == wpmem_get( 'a', false ) && isset( $this->validated ) ) {

			if ( true === $this->validated ) {
				$msg = $this->success_message;
				
				if ( wpmem_is_mod_reg() ) {
					$user = get_user_by( 'login', sanitize_user( wpmem_get( 'login', false ) ) );
					if ( ! wpmem_is_user_activated( $user->ID ) ) {
						$msg = $msg . ' ' . $this->moderated_message;
					}
				}
			} elseif ( false === $this->validated ) {
				$msg = $this->invalid_message;
			} else {
				$msg = '';
			}
			
			$content = wpmem_get_display_message( 'custom', $msg ) . $content;
		}

		return $content;
	}

	/**
	 * Checks if a user is activated during user authentication.
	 *
	 * This prevents access via login if the user has not confirmed their email.
	 *
	 * @since 3.3.5 Moved from core to user object.
	 *
	 * @param  object $user     The WordPress User object.
	 * @param  string $username The user's username (user_login).
	 * @param  string $password The user's password.
	 * @return object $user     The WordPress User object.
	 */ 
	function check_validated( $user, $username, $password ) {
		if ( ! is_wp_error( $user ) && ! is_null( $user ) && false == wpmem_is_user_confirmed( $user->ID ) ) {
			$error_message = sprintf( wpmem_get_text( 'login_not_confirmed' ), '<strong>', '</strong>', '<a href="' . esc_url_raw( wpmem_reconfirm_url() ) . '">', '</a>' );
			$user = new WP_Error( 'authentication_failed', $error_message );
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
		return apply_filters( 'wpmem_check_validated', $user, $username, $password );
	}
	
	/**
	 * Sets user as logged in upon validation (if moderated reg is not enabled).
	 *
	 * @since 3.5.0
	 *
	 * @param int $user_id
	 */
	public function set_as_logged_in( $user_id ) {
		// If registration is not moderated, set the user as logged in.
		if ( ! wpmem_is_enabled( 'mod_reg' ) ) {
			wpmem_set_as_logged_in( $user_id );
		}
	}

	/**
	 * Sends the welcome email to the user upon validation of their email.
	 *
	 * @since 3.3.5
	 * @since 3.3.8 Sends email specific to email validation (previously was moderated approved email).
	 *
	 * @param int $user_id
	 */
	public function send_welcome( $user_id ) {
		if ( $this->send_welcome ) {
			$email_to_send = ( wpmem_get_email_settings( 'wpmembers_email_validated' ) ) ? 'validated' : 'modreg';
			wpmem_email_to_user( array( 'user_id'=>$user_id, 'tag'=>$email_to_send ) );
		}
	}
	
	/**
	 * Sends notification email to the admin upon validation of the user's email.
	 *
	 * @since 3.3.5
	 *
	 * @param int $user_id
	 */
	public function notify_admin( $user_id ) {
		if ( $this->send_notify ) {
			wpmem_notify_admin( $user_id );
		}	
	}
	
	/**
	 * Clears user_activation_key.
	 *
	 * @since 3.3.8
	 *
	 * @param int $user_id
	 */
	public function clear_activation_key( $user_id ) {
		global $wpdb;
		$result = $wpdb->update( $wpdb->users, array( 'user_activation_key' => '', ), array( 'ID' => intval( $user_id ) ) );
		//clean_user_cache( $user_id );
	}

	/**
	 * Sets a user activation key.
	 *
	 * @since 3.3.8
	 *
	 * @param mixed $user user ID (int)|WP_User (object).
	 */
	public function set_validation_key( $user ) {
		$user = ( is_object( $user ) ) ? $user : get_user_by( 'ID', intval( $user ) );
		return get_password_reset_key( $user );
	}
	
	/**
	 * Sets user as having validated their email.
	 *
	 * @since 3.3.8
	 *
	 * @param int $user_id
	 */
	public function set_as_confirmed( $user_id ) {
		update_user_meta( $user_id, $this->validation_confirm, time() );
		/**
		 * Fires when user is set as confirmed (either manually or by user).
		 *
		 * @since 3.3.9
		 *
		 * @param int $user_id
		 * @param string time()
		 */
		do_action( 'wpmem_user_set_as_confirmed', $user_id, time() );
	}
	
	/**
	 * Sets user as NOT having validated their email.
	 *
	 * @since 3.3.8
	 *
	 * @param int $user_id
	 */
	public function set_as_unconfirmed( $user_id ) {
		delete_user_meta( $user_id, $this->validation_confirm );
		$validation_key = $this->set_validation_key( $user_id );
		/**
		 * Fires when user is set as confirmed (either manually or by user).
		 *
		 * @since 3.3.9
		 *
		 * @param int $user_id
		 * @param string time()
		 * @param string $key
		 */
		do_action( 'wpmem_user_set_as_unconfirmed', $user_id, time(), $validation_key );
	}
}