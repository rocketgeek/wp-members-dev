<?php
/**
 * WP-Members Admin functions
 *
 * Static functions to manage the plugin options tab.
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

class WP_Members_Admin_Tab_Options {

	/**
	 * Creates the tab.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Ported from wpmem_a_options_tab().
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The about tab, otherwise false.
	 */
	static function do_tab( $tab ) {
		if ( $tab == 'options' || ! $tab ) {
			// Render the about tab.
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Builds the settings panel.
	 *
	 * @since 2.2.2
	 * @since 3.3.0 Ported from wpmem_a_build_options().
	 *
	 * @global object $wpmem The WP_Members Object.
	 */
	static function build_settings() {

		global $wpmem;

		/** This filter is documented in wp-members/includes/class-wp-members-email.php */
		$admin_email = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ) );
		$chg_email   = sprintf( esc_html__( '%sChange%s or %sFilter%s this address', 'wp-members' ), '<a href="' . site_url( 'wp-admin/options-general.php', 'admin' ) . '">', '</a>', '<a href="https://rocketgeek.com/plugins/wp-members/docs/filter-hooks/wpmem_notify_addr/">', '</a>' );
		$help_link   = sprintf( esc_html__( 'See the %sUsers Guide on plugin options%s.', 'wp-members' ), '<a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/" target="_blank">', '</a>' );	

		// Build an array of post types
		$post_types = $wpmem->admin->post_types();
		$post_arr = array(
			'post' => esc_html__( 'Posts' ),
			'page' => esc_html__( 'Pages' ),
		);
		if ( $post_types ) {
			foreach ( $post_types  as $post_type ) { 
				$cpt_obj = get_post_type_object( $post_type );
				$post_arr[ $cpt_obj->name ] = $cpt_obj->labels->name;
			}
		} ?>

		<div class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">
				<?php wpmem_a_meta_box(); ?>
				<?php wpmem_a_rating_box(); ?>
				<div class="postbox">
					<h3><span><?php esc_html_e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<p><strong><i><?php echo $help_link; ?></i></strong></p>
						<p><button id="opener">Get Settings Information</button></p>
					</div>
				</div>
				<?php wpmem_a_rss_box(); ?>
			</div> <!-- .inner-sidebar -->

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<h3><span><?php esc_html_e( 'Manage Options', 'wp-members' ); ?></span></h3>
						<div class="inside">
							<form name="updatesettings" id="updatesettings" method="post" action="<?php echo wpmem_admin_form_post_url(); ?>">
							<?php wp_nonce_field( 'wpmem-update-settings' ); ?>
								<h3><?php esc_html_e( 'Content', 'wp-members' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/#content" target="_blank" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								<ul>
								<?php

								// Content Blocking option group.
								$i = 0;
								$len = count( $post_arr );
								foreach ( $post_arr as $key => $val ) {  
									if ( $key == 'post' || $key == 'page' || ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ) {
									?>
									<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
										<label><?php echo ( $i == 0 ) ? esc_html__( 'Content Restriction', 'wp-members' ) : '&nbsp;'; ?></label>
										 <?php
										$block  = ( isset( $wpmem->block[ $key ] ) ) ? $wpmem->block[ $key ] : '';
										$values = array(
											esc_html__( 'Do not restrict', 'wp-members' ) . '|0',
											esc_html__( 'Restrict', 'wp-members' ) . '|1',
											// @todo Future development. esc_html__( 'Hide', 'wp-members' ) . '|2',
										);
										echo wpmem_form_field( 'wpmem_block_' . $key, 'select', $values, $block ); ?>
										<span><?php echo $val; ?></span><?php // @todo - this needs to be translatable. ?>
									</li>
									<?php $i++;
									}
								}

								// Show Excerpts, Login Form, and Registration Form option groups.

								$option_group_array = array( 
									'show_excerpt' => esc_html__( 'Show Excerpts', 'wp-members' ), 
									'show_login'   => esc_html__( 'Show Login Form', 'wp-members' ), 
									'show_reg'     => esc_html__( 'Show Registration Form', 'wp-members' ),
									'autoex'       => esc_html__( 'Auto Excerpt:', 'wp-members' ),
								);

								foreach ( $option_group_array as $item_key => $item_val ) {
									$i = 0;
									$len = count( $post_arr );
									foreach ( $post_arr as $key => $val ) {
										if ( $key == 'post' || $key == 'page' || ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ) {
										?>
										<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
											<label><?php echo ( $i == 0 ) ? $item_val : '&nbsp;'; ?></label>
										<?php if ( 'autoex' == $item_key ) { 
											if ( isset( $wpmem->{$item_key}[ $key ] ) && $wpmem->{$item_key}[ $key ]['enabled'] == 1 ) {
												$setting = 1; 
												$ex_len  = $wpmem->{$item_key}[ $key ]['length'];
												$ex_text = ( isset( $wpmem->{$item_key}[ $key ]['text'] ) ) ? $wpmem->{$item_key}[ $key ]['text'] : '';
											} else {
												$setting = 0;
												$ex_len  = '';
												$ex_text = ''; 
											}
											echo wpmem_form_field( 'wpmem_' . $item_key . '_' . $key, 'checkbox', '1', $setting ); ?> <span><?php echo $val; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
											<span><?php esc_html_e( 'Number of words in excerpt:', 'wp-members' ); ?> </span><input name="wpmem_autoex_<?php echo $key; ?>_len" type="text" size="5" value="<?php echo $ex_len; ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
											<span><?php esc_html_e( 'Custom read more link (optional):', 'wp-members' ); ?> </span><input name="wpmem_autoex_<?php echo $key; ?>_text" type="text" size="5" value="<?php echo $ex_text; ?>" />
										<?php } else {
											$setting = ( isset( $wpmem->{$item_key}[ $key ] ) ) ? $wpmem->{$item_key}[ $key ] : 0; 
											echo wpmem_form_field( 'wpmem_' . $item_key . '_' . $key, 'checkbox', '1', $setting ); ?> <span><?php echo $val; ?></span>
										<?php } ?>
										</li>
										<?php $i++;
										}
									}
								} ?>
								</ul>
								<?php
								if ( WPMEM_EXP_MODULE == true ) {
									$rows = array( 
										array(esc_html__('Enable PayPal','wp-members'),'wpmem_settings_time_exp',esc_html__('Requires payment through PayPal following registration','wp-members'),'use_exp'),
										array(esc_html__('Trial period','wp-members'),'wpmem_settings_trial',esc_html__('Allows for a trial period before PayPal payment is required','wp-members'),'use_trial'),
									); ?>
								<h3><?php esc_html_e( 'Subscription Settings', 'wp-members' ); ?></h3>	
								<ul><?php
								foreach ( $rows as $row ) { ?>
								  <li>
									<label><?php echo $row[0]; ?></label>
									<?php echo wpmem_form_field( $row[1], 'checkbox', '1', $wpmem->{$row[3]} ); ?>&nbsp;&nbsp;
									<?php if ( $row[2] ) { ?><span class="description"><?php echo $row[2]; ?></span><?php } ?>
								  </li>
								<?php } 
								}?></ul>
							<!-- Not yet.
								<h3><?php esc_html_e( 'New Feature Settings', 'wp-members' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/new-feature-settings/" target="_blank" title="info" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								<?php
								$rows = array(
									array( 'Remove legacy dialogs', 'wpmem_legacy_dialogs' ),
								);
								$checkbox_value = get_option( 'wpmem_legacy_dialogs' );
								?><ul><?php
								foreach ( $rows as $key => $row ) { ?>
								  <li>
									<label><?php echo $row[0]; ?></label>
									<?php echo wpmem_form_field( $row[1], 'checkbox', '1', $checkbox_value ); ?>
								  </li>
								<?php } ?>
								</ul>
							-->	
								<?php if ( wpmem_is_woo_active() ) { ?>
								<h3><?php esc_html_e( 'WooCommerce Settings', 'wp-members' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/woocommerce-settings/" target="_blank" title="info" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								<?php
									$woo_options[] = array(esc_html__('WooCommerce Checkout', 'wp-members' ),'wpmem_settings_add_woo_checkout_fields',esc_html__('Add WP-Members fields to WooCommerce registration during checkout','wp-members'),'add_checkout_fields');
									$woo_options[] = array(esc_html__('WooCommerce My Account', 'wp-members' ),'wpmem_settings_add_woo_my_account_fields',esc_html__('Add WP-Members fields to WooCommerce My Account registration','wp-members'),'add_my_account_fields');
									$woo_options[] = array(esc_html__('WooCommerce Update', 'wp-members' ),'wpmem_settings_add_woo_update_fields',esc_html__('Add WP-Members fields to WooCommerce My Account user profile update','wp-members'),'add_update_fields');
									$woo_options[] = array(esc_html__('Restrict Product Purchase', 'wp-members' ),'wpmem_settings_add_restrict_woo_products',esc_html__('If a WooCommerce product is restricted, it will not be purchasable','wp-members'),'product_restrict');
								?>
								<ul><?php
								foreach ( $woo_options as $key => $row ) { ?>
								  <li>
									<label><?php echo $row[0]; ?></label>
									<?php 
										$checkbox_value = ( isset( $wpmem->woo->{$row[3]} ) ) ? $wpmem->woo->{$row[3]} : 0;
										echo wpmem_form_field( $row[1], 'checkbox', '1', $checkbox_value );
									?>&nbsp;&nbsp;
									<span class="description"><?php echo $row[2]; ?></span>
								  </li>
								<?php } ?>
								</ul>
								<?php } ?>

								<h3><?php esc_html_e( 'Other Settings', 'wp-members' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/#other" target="_blank" title="info" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								<ul>
								<?php 
								/** This filter is defined in includes/class-wp-members.php */
								$dropin_dir = apply_filters( 'wpmem_dropin_dir', $wpmem->dropin_dir );
								$mem_link_start = '<a href="https://rocketgeek.com/plugins/wp-members/docs/membership-products/" target="_blank">';
								$mem_link_end   = '</a>';
								$conf_link_start = '<a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/#confirm" target="_blank">';
								$conf_link_end   = '</a>';
								$rows = array(
									array(esc_html__('Enable memberships', 'wp-members'),'wpmem_settings_products',sprintf(esc_html__('Enables creation of different %s membership products %s','wp-members'),$mem_link_start,$mem_link_end),'enable_products'),
									array(esc_html__('Clone menus','wp-members'),'wpmem_settings_menus',esc_html__('Enables logged in menus','wp-members'),'clone_menus'),
									array(esc_html__('Notify admin','wp-members'),'wpmem_settings_notify',sprintf(esc_html__('Notify %s for each new registration? %s','wp-members'),$admin_email,$chg_email),'notify'),
									array(esc_html__('Moderate registration','wp-members'),'wpmem_settings_moderate',esc_html__('Holds new registrations for admin approval','wp-members'),'mod_reg'),
									array(esc_html__('Confirmation Link', 'wp-members'),'wpmem_settings_act_link',sprintf(esc_html__('Send email confirmation link on new registration. %s(Requires additional configuration)%s','wp-members'),$conf_link_start,$conf_link_end),'act_link'),
									array(esc_html__('Ignore warning messages','wp-members'),'wpmem_settings_ignore_warnings',esc_html__('Ignores WP-Members warning messages in the admin panel','wp-members'),'warnings'),
									//array(esc_html__('Enable dropins', 'wp-members'),'wpmem_settings_enable_dropins',sprintf(esc_html__('Enables dropins in %s', 'wp-members'), $dropin_dir),'dropins'),
									array(esc_html__('Notifications & Diagnostics', 'wp-members' ),'wpmem_settings_optin',esc_html__('Opt in to security and updates notifications and non-sensitive diagnostics tracking', 'wp-members'),'optin'),
								);
								foreach ( $rows as $row ) { 
									if ( $row[0] == esc_html__('Clone menus','wp-members') && 1 != $wpmem->clone_menus ) {
										continue;
									}?>
								  <li>
									<label><?php echo $row[0]; ?></label>
									<?php echo wpmem_form_field( $row[1], 'checkbox', '1', $wpmem->{$row[3]} ); ?>&nbsp;&nbsp;
									<?php if ( $row[2] ) { ?><span class="description"><?php echo $row[2]; ?></span><?php } ?>
								  </li>
								<?php } ?>
								  <li>
									<label><?php esc_html_e( 'Attribution', 'wp-members' ); ?></label>
									<?php echo wpmem_form_field( 'attribution', 'checkbox', '1', $wpmem->attrib ); ?>&nbsp;&nbsp;
									<span class="description"><?php esc_html_e( 'Attribution is appreciated!  Display "powered by" link on register form?', 'wp-members' ); ?></span>
								  </li>
								  <li>
									<label><?php esc_html_e( 'Enable CAPTCHA for Registration', 'wp-members' ); ?></label>
									<?php $captcha = array( esc_html__( 'None', 'wp-members' ) . '|0' );
									if ( 1 == $wpmem->captcha ) {
										$wpmem->captcha = 3; // reCAPTCHA v1 is fully obsolete. Change it to v2.
									}
									$captcha[] = esc_html__( 'reCAPTCHA v2', 'wp-members' ) . '|3';
									$captcha[] = esc_html__( 'reCAPTCHA v3', 'wp-members' ) . '|4';
									$captcha[] = esc_html__( 'Really Simple CAPTCHA', 'wp-members' ) . '|2';
									$captcha[] = esc_html__( 'hCaptcha', 'wp-members' ) . '|5';
									echo wpmem_form_field( 'wpmem_settings_captcha', 'select', $captcha, $wpmem->captcha ); ?>
								  </li>
								<h3><?php esc_html_e( 'Pages' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/#pages" target="_blank" title="info" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								  <?php
									$user_pages = array(
										'log' => array(
											'url' => $wpmem->user_pages['login'],
											'label' => esc_html__( 'Login Page:', 'wp-members' ),
											'description' => esc_html__( 'Specify a login page (optional)', 'wp-members' ),
										),
										'reg' => array(
											'url' => $wpmem->user_pages['register'],
											'label' => esc_html__( 'Register Page:', 'wp-members' ),
											'description' => esc_html__( 'For creating a register link in the login form', 'wp-members' ),
										),
										'ms' => array(
											'url' => $wpmem->user_pages['profile'],
											'label' => esc_html__( 'User Profile Page:', 'wp-members' ),
											'description' => esc_html__( 'For creating a forgot password link in the login form', 'wp-members' ),
										),
									);
								foreach ( $user_pages as $key => $setting ) { ?>
									<li>
										<label><?php echo $setting['label'] ?></label>
										<select name="wpmem_settings_<?php echo $key; ?>page" id="wpmem_<?php echo $key; ?>page_select">
										<?php WP_Members_Admin_Tab_Options::page_list( $setting['url'] ); ?>
										</select>&nbsp;<span class="description"><?php echo $setting['description']; ?></span><br />
										<div id="wpmem_<?php echo $key; ?>page_custom">
											<label>&nbsp;</label>
											<input class="regular-text code" type="text" name="wpmem_settings_<?php echo $key; ?>url" value="<?php echo $setting['url']; ?>" placeholder="https://" size="50" />
										</div>
									</li><?php
								} ?>
								<h3><?php esc_html_e( 'Stylesheet' ); ?> <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/options/#styles" target="_blank" title="info" data-tooltip="<?php esc_html_e( 'Click the icon for documentation', 'wp-members' ); ?>"><span class="dashicons dashicons-info"></span></a></h3>
								  <?php
								  if ( $wpmem->cssurl != trailingslashit( $wpmem->url ) . 'assets/css/forms/generic-no-float.min.css'
								    && $wpmem->cssurl != trailingslashit( $wpmem->url ) . 'assets/css/forms/generic-no-float.css' ) {
									$wpmem_cssurl = $wpmem->cssurl;
								  } else {
									$wpmem_cssurl = '';
								  }
								  ?>
								  <div id="wpmem_stylesheet_custom">
									  <p><?php _e( 'The plugin has a standard stylesheet. To use it, leave the following option empty. To use a custom stylesheet, enter its URL below.', 'wp-members' ); ?></p>
									  <li>
										<label><?php esc_html_e( 'Custom Stylesheet:', 'wp-members' ); ?></label>
										<input class="regular-text code" type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" placeholder="https://" size="50" />
									  </li>
								  </div>
									<input type="hidden" name="wpmem_admin_a" value="update_settings">
									<?php submit_button( esc_html__( 'Update Settings', 'wp-members' ) ); ?>
								</ul>
							</form>
							<p>If you like <strong>WP-Members</strong> please give it a <a href="https://wordpress.org/support/plugin/wp-members/reviews?rate=5#new-post">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Thanks!!</p>
						</div><!-- .inside -->
					</div>
					<?php if ( $post_types ) { ?>
					<div class="postbox">
						<h3><span><?php esc_html_e( 'Custom Post Types', 'wp-members' ); ?></span></h3>
						<div class="inside">
							<form name="updatecpts" id="updatecpts" method="post" action="<?php echo wpmem_admin_form_post_url(); ?>">
							<?php wp_nonce_field( 'wpmem-update-cpts' ); ?>
								<table class="form-table">
									<tr>
										<th scope="row"><?php esc_html_e( 'Add to WP-Members Settings', 'wp-members' ); ?></th>
										<td><fieldset><?php
										foreach ( $post_arr as $key => $val ) {
											if ( 'post' != $key && 'page' != $key && 'wpmem_product' != $key ) {
												$checked = ( isset( $wpmem->post_types ) && array_key_exists( $key, $wpmem->post_types ) ) ? ' checked' : '';
												echo '<label for="' . $key . '"><input type="checkbox" name="wpmembers_handle_cpts[]" value="' . $key . '"' . $checked . ' />' . $val . '</label><br />';
											}
										}
										?></fieldset>
										</td>
									</tr>
									<tr>
										<input type="hidden" name="wpmem_admin_a" value="update_cpts" />
										<td colspan="2"><?php submit_button( esc_html__( 'Update Settings', 'wp-members' ) ); ?></td>
									</tr>
									<tr>
										<td colspan="2"><?php esc_html_e( 'Please keep in mind that Custom Post Types are "custom" and therefore, not all of them will function exactly the same way. WP-Members will certainly work with any post type that operate like a post or a page; but you will need to review any custom post type added to determine that it functions the way you expect.', 'wp-members' ); ?></td>
									</tr>
								</table>
							</form>
						</div>
					</div>
					<?php } ?>
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div><!-- .metabox-holder -->
	<div id="dialog-message" title="<?php esc_html_e( 'WP-Members Settings', 'wp-members' ); ?>">
	<h3><span><?php esc_html_e( 'WP-Members Settings', 'wp-members' ); ?></span></h3>
	<p><?php esc_html_e( 'The following is your WP-Members settings information if needed for support.', 'wp-members' ); ?></p>
	<pre>
	<textarea cols=80 rows=10 align=left wrap=soft style="width:80%;" id="supportinfo" wrap="soft"><?php
	global $wp_version, $wpdb, $wpmem;
	echo "WP Version: " . $wp_version . "\r\n";
	echo "PHP Version: " . phpversion() . "\r\n";
	echo "MySQL Version: " . $wpdb->db_version() . "\r\n";
	wpmem_fields();
	print_r( $wpmem );
	
	/**
	 * Action to add before other plugin info.
	 *
	 * @since 3.4.0
	 */
	do_action( 'wpmem_settings_for_support' );

	echo '***************** Plugin Info *******************' . "\r\n";
	$all_plugins    = get_plugins();
	$active_plugins = get_option( 'active_plugins' );
	$active_display = ''; $inactive_display = '';
	foreach ( $all_plugins as $key => $value ) {
		if ( in_array( $key, $active_plugins ) ) {
			$active_display.= $key . " | " . $value['Name'] . " | Version: " . $value['Version'] . "\r\n";
		} else {
			$inactive_display.= $key . " | " . $value['Name'] . " | Version: " . $value['Version'] . "\r\n";
		}
	}
	echo "*************** Active Plugins **************** \r\n";
	echo $active_display;
	echo "*************** Inactive Plugins **************** \r\n";
	echo $inactive_display;
	?></textarea>
	</pre>
	<button id="select_all" class="ui-button-text"><?php esc_html_e( 'Click to Copy', 'wp-members' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Updates the plugin options.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported from wpmem_update_options() and wpmem_update_cpts().
	 *
	 * @global object $wpmem  The WP_Members object.
	 * @param  string $action
	 * @return string         The options updated message.
	 */
	static function update( $action ) {
		
		if ( 'update_cpts' == $action ) {
			
			// Check nonce.
			check_admin_referer( 'wpmem-update-cpts' );

			// Get the main settings array as it stands.
			$wpmem_newsettings = get_option( 'wpmembers_settings' );

			// Assemble CPT settings.
			$cpts = array();

			$post_arr = array();
			$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
			if ( $post_types ) {
				foreach ( $post_types as $post_type ) {
					$cpt_obj = get_post_type_object( $post_type );
					if ( $cpt_obj->labels->name != 'wpmem_product' ) {
						$post_arr[ $cpt_obj->name ] = $cpt_obj->labels->name;
					}
				}
			}

			$post_vals = ( isset( $_POST['wpmembers_handle_cpts'] ) ) ? $_POST['wpmembers_handle_cpts'] : false;
			if ( $post_vals ) {
				foreach ( $post_vals as $val ) {
					$cpts[ $val ] = sanitize_text_field( $post_arr[ $val ] );
				}
			} else {
				$cpts = array();
			}
			$wpmem_newsettings['post_types'] = $cpts;

			// Update settings, remove or add CPTs.
			$chk_settings = array( 'block', 'show_excerpt', 'show_login', 'show_reg', 'autoex' );
			foreach ( $chk_settings as $chk ) {
				// Handle removing unmanaged CPTs.
				foreach ( $wpmem_newsettings[ $chk ] as $key => $val ) {
					if ( 'post' != $key && 'page' != $key ) {
						// If the $key is not in managed CPTs, remove it.
						if ( ! array_key_exists( $key, $cpts ) ) {
							unset( $wpmem_newsettings[ $chk ][ $key ] );
						}
					}
				}
				// Handle adding managed CPTs.
				foreach ( $cpts as $key => $val ) {
					if ( ! array_key_exists( $key, $wpmem_newsettings[ $chk ] ) ) {
						if ( 'autoex' == $chk ) {
							// Auto excerpt is an array.
							$wpmem_newsettings[ $chk ][ $key ] = array(
								'enabled' => 0,
								'length'  => '',
							);
						} else {
							// All other settings are 0|1.
							$wpmem_newsettings[ $chk ][ $key ] = 0;
						}
					}
				}
			}

			WP_Members_Admin_Tab_Options::save_settings( $wpmem_newsettings );

			return esc_html__( 'Custom Post Type settings were updated', 'wp-members' );
			
		} else {
			
			global $wpmem;

			// Check nonce.
			check_admin_referer( 'wpmem-update-settings' );

			$wpmem_settings_msurl  = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_msurl'] ) : '';
			$wpmem_settings_mspage = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? '' : wpmem_sanitize_field( $_POST['wpmem_settings_mspage'], 'int' );
			if ( $wpmem_settings_msurl != '' && $wpmem_settings_msurl != 'use_custom' && ! $wpmem_settings_mspage ) {
				$msurl = trim( $wpmem_settings_msurl );
			} else {
				$msurl = $wpmem_settings_mspage;
			}

			$wpmem_settings_regurl  = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_regurl'] ) : '';
			$wpmem_settings_regpage = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? '' : wpmem_sanitize_field( $_POST['wpmem_settings_regpage'], 'int' );
			if ( $wpmem_settings_regurl != '' && $wpmem_settings_regurl != 'use_custom' && ! $wpmem_settings_regpage ) {
				$regurl = trim( $wpmem_settings_regurl );
			} else {
				$regurl = $wpmem_settings_regpage;
			}

			$wpmem_settings_logurl  = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? esc_url( $_POST['wpmem_settings_logurl'] ) : '';
			$wpmem_settings_logpage = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? '' : wpmem_sanitize_field( $_POST['wpmem_settings_logpage'], 'int' );
			if ( '' != $wpmem_settings_logurl && 'use_custom' != $wpmem_settings_logurl && ! $wpmem_settings_logpage ) {
				$logurl = trim( $wpmem_settings_logurl );
			} else {
				$logurl = $wpmem_settings_logpage;
			}

			$wpmem_settings_cssurl_sanitized = esc_url_raw( $_POST['wpmem_settings_cssurl'] );

			$wpmem_newsettings = array(
				'version' => $wpmem->version,
				'db_version'      => $wpmem->db_version,
				'act_link'        => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_act_link',        0 ), 'int' ),
				'enable_products' => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_products',        0 ), 'int' ),
				'clone_menus'     => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_menus',           0 ), 'int' ),
				'notify'          => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_notify',          0 ), 'int' ),
				'mod_reg'         => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_moderate',        0 ), 'int' ),
				'captcha'         => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_captcha',         0 ), 'int' ),
				'use_exp'         => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_time_exp',        0 ), 'int' ),
				'use_trial'       => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_trial',           0 ), 'int' ),
				'warnings'        => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_ignore_warnings', 0 ), 'int' ),
				'dropins'         => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_enable_dropins',  0 ), 'int' ),
				'user_pages'      => array(
					'profile'  => ( $msurl  ) ? $msurl  : '',
					'register' => ( $regurl ) ? $regurl : '',
					'login'    => ( $logurl ) ? $logurl : '',
				),
				'woo' => array(
					'add_checkout_fields'   => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_add_woo_checkout_fields',   0 ), 'int' ),
					'add_my_account_fields' => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_add_woo_my_account_fields', 0 ), 'int' ),
					'add_update_fields'     => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_add_woo_update_fields',     0 ), 'int' ),
					'product_restrict'      => wpmem_sanitize_field( wpmem_get( 'wpmem_settings_add_restrict_woo_products', 0 ), 'int' ),
				),
				'cssurl'       => ( '' != $wpmem_settings_cssurl_sanitized ) ? trim( $wpmem_settings_cssurl_sanitized ) : '',
				'attrib'       => wpmem_sanitize_field( wpmem_get( 'attribution', 0 ), 'int' ),
			);

			// Make sure shortcode setting is not overwritten. @todo Is this the best way to handle this?
			$wpmem_newsettings['shortcodes']['enable_field'] = ( isset( $wpmem->shortcodes->enable_field ) ) ? $wpmem->shortcodes->enable_field : 0;

			// Build an array of post types
			$post_arr = array( 'post', 'page' );
			if ( isset( $wpmem->post_types ) ) {
				$wpmem_newsettings['post_types'] = $wpmem->post_types;
				foreach ( $wpmem_newsettings['post_types'] as $key => $val ) { 
					$post_arr[] = $key;
				}
			}
			
			// If activation link is being enabled, make sure current admin is marked as activated.
			if ( 1 == $wpmem_newsettings['act_link'] && 0 == $wpmem->act_link ) {
				update_user_meta( get_current_user_id(), '_wpmem_user_confirmed', time() );
			}

			// Leave form tag settings alone.
			if ( isset( $wpmem->form_tags ) ) {
				$wpmem_newsettings['form_tags'] = $wpmem->form_tags;
			}

			// Get settings for blocking, excerpts, show login, and show registration for posts, pages, and custom post types.
			$option_group_array = array( 'block', 'show_excerpt', 'show_login', 'show_reg', 'autoex' );
			foreach ( $option_group_array as $option_group_item ) {
				$arr = array();
				foreach ( $post_arr as $post_type ) {
					$post_var = 'wpmem_' . $option_group_item . '_' . $post_type;
					if ( $option_group_item == 'autoex' ) {
						// Auto excerpt is an array.
						$arr[ $post_type ]['enabled'] = ( isset( $_POST[ $post_var ]           ) ) ? wpmem_sanitize_field( $_POST[ $post_var ], 'int' ) : 0;
						$arr[ $post_type ]['length']  = ( isset( $_POST[ $post_var . '_len'  ] ) ) ? ( ( $_POST[ $post_var . '_len' ] == '' ) ? 0 : wpmem_sanitize_field( $_POST[ $post_var . '_len' ], 'int' ) ) : '';
						$arr[ $post_type ]['text']    = ( isset( $_POST[ $post_var . '_text' ] ) ) ? sanitize_text_field( $_POST[ $post_var . '_text' ] ) : '';
					} else {
						// All other settings are 0|1.
						$arr[ $post_type ] = ( isset( $_POST[ $post_var ] ) ) ? wpmem_sanitize_field( $_POST[ $post_var ], 'int' ) : 0;
					}
				}
				$wpmem_newsettings[ $option_group_item ] = $arr;
			}

			/*
			 * If we are setting registration to be moderated, 
			 * check to see if the current admin has been 
			 * activated so they don't accidentally lock themselves
			 * out later.
			 */
			if ( isset( $_POST['wpmem_settings_moderate'] ) == 1 ) {
				update_user_meta( get_current_user_id(), 'active', 1 );
			}
			
			if ( isset( $_POST['wpmem_settings_act_link'] ) == 1 ) {
				update_user_meta( get_current_user_id(), '_wpmem_activation_confirm', time() );
			}

			if ( 1 == wpmem_get_sanitized( 'wpmem_legacy_dialogs', 0, 'post', 'int' ) ) {
				update_option( 'wpmem_legacy_dialogs', 1, false );
			} else {
				update_option( 'wpmem_legacy_dialogs', 0, false );
			}

			WP_Members_Admin_Tab_Options::save_settings( $wpmem_newsettings );

			return esc_html__( 'WP-Members settings were updated', 'wp-members' );
		}
	}

	/**
	 * Puts new settings into the current object.
	 *
	 * @since 3.0.9
	 * @since 3.3.0 Ported from wpmem_admin_new_settings().
	 *
	 * @global $wpmem
	 * @param $new
	 * @return $settings
	 */
	static function save_settings( $new ) {

		// Update saved settings.
		update_option( 'wpmembers_settings', $new, true );

		// Update the current WP_Members object with the new settings.
		global $wpmem;
		foreach ( $new as $key => $val ) {
			if ( 'user_pages' == $key ) {
				foreach ( $val as $subkey => $subval ) {
					$val[ $subkey ] = ( is_numeric( $subval ) ) ? get_page_link( $subval ) : $subval;
				}
				$wpmem->{$key} = $val;
			} elseif ( wpmem_is_woo_active() && 'woo' == $key ) {
				// Set Woo options.
				foreach ( $val as $subkey => $subval ) {
					$wpmem->woo->{$subkey} = $subval;
				}
			} else {
				$wpmem->{$key} = $val;
			}
		}

		if ( isset( $_POST['wpmem_settings_optin'] ) && 0 == $wpmem->optin ) {
			update_option( 'wpmembers_optin', 1, false );
			$wpmem->optin = 1;
		} elseif ( ! isset( $_POST['wpmem_settings_optin'] ) && 1 == $wpmem->optin ) {
			update_option( 'wpmembers_optin', 0, false );
			$wpmem->optin = 0;
		}
	}

	/**
	 * Create the stylesheet dropdown selection.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported from wpmem_admin_style_list().
	 * @deprecated 3.5.0 No longer needed as the selector is no longer used.
	 *
	 * @param $style string The stored stylesheet setting.
	 */
	static function style_list( $style ) {

		$list = array(
			'No Float'                   => 'generic-no-float',
			'Rigid'                      => 'generic-rigid',
			'Twenty Sixteen - no float'  => 'wp-members-2016-no-float',
			'Twenty Fifteen'             => 'wp-members-2015',
			'Twenty Fifteen - no float'  => 'wp-members-2015-no-float',
			'Twenty Fourteen'            => 'wp-members-2014',
			'Twenty Fourteen - no float' => 'wp-members-2014-no-float',
			//'Twenty Thirteen'            => WPMEM_DIR . 'css/wp-members-2013.css',
			//'Twenty Twelve'              => WPMEM_DIR . 'css/wp-members-2012.css',
			//'Twenty Eleven'              => WPMEM_DIR . 'css/wp-members-2011.css',
			//'Twenty Ten'                 => WPMEM_DIR . 'css/wp-members.css',
			//'Kubrick'                    => WPMEM_DIR . 'css/wp-members-kubrick.css',
		);

		/**
		 * Filters the list of stylesheets in the plugin options dropdown.
		 *
		 * @since 2.8.0
		 * @deprecated 3.3.0 There is no way to manage custom values in the dropdown with the new setting rules.
		 *
		 * @param array $list An array of stylesheets that can be applied to the plugin's forms.
		 */
		//$list = apply_filters( 'wpmem_admin_style_list', $list );

		$selected = false;
		foreach ( $list as $name => $location ) {
			$selected = ( $location == $style ) ? true : $selected;
			echo '<option value="' . $location . '" ' . selected( $location, $style ) . '>' . $name . "</option>\n";
		}
		$selected = ( ! $selected ) ? ' selected' : '';
		echo '<option value="use_custom"' . $selected . '>' . esc_html__( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>';

		return;
	}

	/**
	 * Create a dropdown selection of pages.
	 *
	 * @since 2.8.1
	 * @since 3.3.0 Ported from wpmem_admin_page_list().
	 *
	 * @todo  Consider wp_dropdown_pages. Can be retrieved as HTML (echo=false) and str_replaced to add custom values.
	 *
	 * @param string $val
	 */
	static function page_list( $val, $show_custom_url = true ) {

		$selected = ( '' == $val ) ? 'select a page' : false;
		$pages    = get_pages();

		echo '<option value=""'; echo ( 'select a page' == $selected ) ? ' selected' : ''; echo '>'; echo esc_attr( esc_html__( 'Select a page', 'wp-members' ) ); echo '</option>';

		foreach ( $pages as $page ) {
			$selected = ( get_page_link( $page->ID ) == $val ) ? true : $selected; //echo "VAL: " . $val . ' PAGE LINK: ' . get_page_link( $page->ID );
			$option   = '<option value="' . $page->ID . '"' . selected( get_page_link( $page->ID ), $val, 'select' ) . '>';
			$option  .= $page->post_title;
			$option  .= '</option>';
			echo $option;
		}
		if ( $show_custom_url ) {
			$selected = ( ! $selected ) ? ' selected' : '';
			echo '<option value="use_custom"' . $selected . '>' . esc_html__( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>'; 
		}
	}

} // End of file.