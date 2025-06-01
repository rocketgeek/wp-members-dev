<?php
/**
 * A WP_CLI set of subcommands to list and update WP-Members plugin settings.
 *
 * @since 3.3.5
 */
class WP_Members_CLI_Settings {
	
	/**
	 * Initialize any required elements.
	 *
	 * @since 3.3.5
	 *
	 * @global object $wpmem
	 */
	public function __construct() {
		// WP-Members admin needs to be loaded manually.
		global $wpmem;
		if ( ! isset( $wpmem->admin ) ) {
			$wpmem->load_admin();
		}
	}
	
	/**
	 * List the WP-Members content settings.
	 *
	 * @since 3.3.5
	 */
	public function content() {
		$this->list_settings( array( 'content' ), array() );	
	}
	
	/**
	 * List the WP-Members option settings.
	 *
	 * @since 3.3.5
	 */
	public function options() {
		$this->list_settings( array( 'options' ), array() );
	}
	
	/**
	 * Lists WP-Members settings.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $args
	 * @param  array  $assoc_args
	 */
	private function list_settings( $args, $assoc_args ) {
		
		global $wpmem;
		
		if ( 'content' == $args[0] ) {
			$settings = $this->settings( 'content' );
		} else {
			$settings = $this->settings( 'options' );
		}
		if ( 'content' == $args[0] ) {

			// @todo Add custom post types, and look for admin where all possible post types are assembled.
			$post_types = array_merge( array( 'post', 'page' ), $wpmem->admin->post_types() );

			foreach( $post_types as $post_type ) {
				foreach ( $settings as $setting => $description ) {
					if ( 'autoex' != $setting ) {
						$list[] = array(
							'Setting' => $setting . ' ' . $post_type,
							'Description' => $description . ' ' . $post_type,
							'Value' =>  $wpmem->{$setting}[ $post_type ],
							'Option' => ( 0 == $wpmem->{$setting}[ $post_type ] ) ? 'Disabled' : 'Enabled',
						);
					} else {
						$list[] = array(
							'Setting' => $setting . ' ' . $post_type,
							'Description' => $description . ' ' . $post_type,
							'Value' =>  $wpmem->{$setting}[ $post_type ]['enabled'],
							'Option' => ( 0 == $wpmem->{$setting}[ $post_type ]['enabled'] ) ? 'Disabled' : 'Enabled',
						);
						$list[] = array( 
							'Setting' => '', 
							'Description' => $post_type . ' excerpt word length:', 
							'Value' => $wpmem->{$setting}[ $post_type ]['length'],
							'Option' => '', 
						);
					}
				}

				$list[] = array( 'Setting' => '', 'Description' => '', 'Value' => '', 'Option' => '' );
			}
			
		} else {
			foreach ( $settings as $setting => $description ) {
				if ( 'captcha' == $setting ) {
					$option = WP_Members_Captcha::type( $wpmem->{$setting} );
				} else {
					$option = ( 0 == $wpmem->{$setting} ) ? 'Disabled' : 'Enabled';
				}
				$list[] = array(
					'Setting'  => $setting,
					'Description' => $description,
					'Value' => $wpmem->{$setting},
					'Option' => $option,
				);

			}
		}
	
		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Description', 'Setting', 'Value', 'Option' ) );
		$formatter->display_items( $list ); 
	}

	/**
	 * List custom post types for WP-Members management.
	 *
	 * @since 3.3.5
	 * 
	 * @subcommand post-types
	 */
	public function post_types() {
		global $wpmem;
		$post_types = $wpmem->admin->post_types();
		if ( empty( $post_types ) ) {
			WP_CLI::line( 'No custom post types are available for WP-Members settings' );
		} else {
			foreach ( $post_types as $post_type ) {
				$enabled = ( array_key_exists( $post_type, $wpmem->post_types ) ) ? "Enabled" : "Disabled";
				$list[] = array(
					'Post Type' => $post_type,
					'Value' => $enabled,
				);
			}
			WP_CLI::line( 'Custom post type settings for WP-Members:' );
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Post Type', 'Value' ) );
			$formatter->display_items( $list ); 
		}
	}
	
	/**
	 * Manage post type.
	 *
	 * ## OPTIONS
	 *
	 * [--enable=<post_type>]
	 * : enable the specified post type.
	 *
	 * [--disable=<post_type>]
	 * : disable the specified post type.
	 *
	 * @since 3.3.5
	 * 
	 * @subcommand post-type
	 */
	public function post_type( $args, $assoc_args ) {
		global $wpmem;
		if ( isset( $assoc_args['enable'] ) || isset( $assoc_args['disable'] ) ) {
			$post_types = $wpmem->admin->post_types();
			if ( ( isset( $assoc_args['enable'] ) && ! array_key_exists( $assoc_args['enable'], $post_types ) ) || ( isset( $assoc_args['disable'] ) && ! array_key_exists( $assoc_args['disable'], $post_types ) ) ) {
				WP_CLI::error( 'Not an available post type. Try [wp mem settings post_types]' );
			}
			// Handle disable.
			if ( isset( $assoc_args['disable'] ) ) {
				unset( $wpmem->post_types[ $assoc_args['disable'] ] );
				wpmem_update_option( 'wpmembers_settings', 'post_types', $wpmem->post_types, true );
				WP_CLI::success( sprintf( 'Disabled %s post type.', $assoc_args['disable'] ) );
			}
			if ( isset( $assoc_args['enable'] ) ) {
				$cpt_obj = get_post_type_object( $assoc_args['enable'] );	
				$new_post_types = array_merge($wpmem->post_types, array( $cpt_obj->name => $cpt_obj->labels->name ) );
				wpmem_update_option( 'wpmembers_settings', 'post_types', $new_post_types, true );
				WP_CLI::success( sprintf( 'Enabled %s post type.', $assoc_args['enable'] ) );
			}
		} else {
			WP_CLI::error( 'Must specify an option: --enable=<post_type> or --disable=<post_type>' );
		}
	}
	
	/**
	 * Enable a WP-Members setting.
	 *
	 * ## OPTIONS
	 *
	 * <option>
	 * : The WP-Members option setting to enable.
	 * ---
	 * options:
	 *    - notify
	 *    - mod_reg
	 *    - act_link
	 *    - optin
	 *    - captcha
	 *    - warnings
	 *    - dropins
	 *    - enable_products
	 *    - clone_menus
	 * ---
	 * 
	 * [<captcha_type>] 
	 * : If enabling captcha, include the captcha type
	 * ---
	 * default: recaptcha_v3
	 * options:
	 *    - recaptcha_v3
	 *    - recaptcha_v2
	 *    - rs_captcha
	 *    - hcaptcha
	 * 
	 * ## EXAMPLES
	 *
	 *     wp mem settings enable mod_reg
	 * 
	 * @todo Add options to enable content (post type) settings.
	 */
	public function enable( $args, $assoc_args ) {
		$settings = $this->settings( 'options' );

		if ( 'captcha' == $args[0] ) {

			if ( ! isset( $args[1] ) ) {
				WP_CLI::error( 'You must specify captcha type: rs_captcha|recaptcha_v2|recaptcha_v3|hcaptcha' );
			}

			switch( $args[1] ) {
				case 'rs_captcha':
					$which = 2;
					break;
				case 'hcaptcha':
					$which = 5;
					break;
				case 'recaptcha_v2':
					$which = 3;
					break;
				case 'recaptcha_v3':
				default:
					$which = 4;
					break;
			}
			wpmem_update_option( 'wpmembers_settings', $args[0], $which, true );
			WP_CLI::success(  sprintf( '%s %s enabled', $settings[ $args[0] ], $args[1] ) );
		
		} else {
			wpmem_update_option( 'wpmembers_settings', $args[0], 1, true );
			WP_CLI::success( sprintf( '%s enabled', $settings[ $args[0] ] ) );
		}
	}
	
	/**
	 * Disables a WP-Members setting.
	 *
	 * ## OPTIONS
	 *
	 * <option>
	 * : The WP-Members option setting to disable.
	 * ---
	 * options:
	 *    - notify
	 *    - mod_reg
	 *    - act_link
	 *    - optin
	 *    - captcha
	 *    - warnings
	 *    - dropins
	 *    - enable_products
	 *    - clone_menus
	 * ---
	 * 
	 * ## EXAMPLES
	 *
	 *     wp mem settings disable mod_reg
	 * 
	 * @todo Test for captcha
	 * @todo Add options to disable content (post type) settings.
	 */
	public function disable( $args ) {
		$settings = $this->settings( 'options' );
		wpmem_update_option( 'wpmembers_settings', $args[0], 0, true );
		WP_CLI::success( sprintf( '%s disabled', $settings[ $args[0] ] ) );
	}
	
	/**
	 * Set, clear, or list the WP-Members page settings.
	 *
	 * ## OPTIONS
	 *
	 * <option>
	 * : List user pages, clear user pages, or set a page ID for a user page.
	 * ---
	 * default: list
	 * options
	 *    - list
	 *    - clear
	 *    - set
	 * ---
	 *
	 * [--all]
	 * : used with <clear> option, clears all pages.
	 *
	 * [--login[=<ID>]]
	 * : Leave empty (--login) to clear, or set a page ID for the login page.
	 *
	 * [--register[=<ID>]]
	 * : Leave empty (--register) to clear, or set a page ID for the registration page.
	 *
	 * [--profile[=<ID>]]
	 * : Leave empty (--profile) to clear, or set a page ID for the profile page.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mem settings pages clear --all
	 *     wp mem settings pages clear --register
	 *     wp mem settings pages set --login=123
	 *     wp mem settings pages list
	 */
	public function pages( $args, $assoc_args ) {

		if ( 'clear' == $args[0] ) {
			if ( empty( $assoc_args ) ) {
				WP_CLI::error( 'You must specify --all or --login|register|profile', true );
			}
			if ( isset( $assoc_args['all'] ) ) {
				unset( $assoc_args['all'] );
				$assoc_args = array( 'login'=>'', 'register'=>'', 'profile'=>'' );
			}
			foreach ( $assoc_args as $page => $value ) {
				if ( isset( $assoc_args[ $page ] ) ) {
					wpmem_update_option( 'wpmembers_settings', 'user_pages/' . $page, '', true );
					WP_CLI::success( sprintf( '%s page cleared', ucfirst( $page ) ) );
				}	
			}
			return;
		}
		if ( 'set' == $args[0] ) {
			if ( empty( $assoc_args ) ) {
				WP_CLI::error( 'You must specify which page(s) to set: --login=<ID>, --register=<ID>, --profile=<ID>', true );
			}
			foreach ( $assoc_args as $page => $value ) {
				if ( isset( $assoc_args[ $page ] ) ) {
					wpmem_update_option( 'wpmembers_settings', 'user_pages/' . $page, $assoc_args[ $page ], true );
					WP_CLI::success( sprintf( '%s page set to ID %s', ucfirst( $page ), $assoc_args[ $page ] ) );
				}
			}
			return;
		}
		if ( 'list' == $args[0] ) {

			$raw_settings = get_option( 'wpmembers_settings' );
			$user_pages = wpmem_user_pages();

			foreach ( $user_pages as $key => $page ) {
				$list[] = array(
					'Page' => ucfirst( $key ),
					'ID' => $raw_settings['user_pages'][ $key ],
					'URL' => $page,
				);
			}
			
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Page', 'ID', 'URL' ) );
			$formatter->display_items( $list );
		}
	}

	private function settings( $which ) {
		switch ( $which ) {
			case 'content':
				return array( 
					'block' => 'Content Restriction', 
					'show_excerpt' => 'Show Excerpts', 
					'show_login' => 'Show Login Form', 
					'show_reg' => 'Show Registration Form', 
					'autoex' => 'Auto Excerpt' );
				break;
			case 'options':
				return array( 
					'enable_products' => 'Enable membership products', 
					'notify' => 'Notify admin', 
					'mod_reg' => 'Moderate registration', 
					'act_link' => 'Confirmation link',
					'optin' => 'Opt in to security and updates information',
					'warnings' => 'Ignore warning messages', 
					'dropins' => 'Enable dropins', 
					'captcha' => 'Enable registration CAPTCHA', 					
					'clone_menus' => 'Clone menus (deprecated)',
				);
				break;
		}
	}
}

WP_CLI::add_command( 'mem settings', 'WP_Members_CLI_Settings' );