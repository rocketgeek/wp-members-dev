<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the captcha tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2026  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2026
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_Tab_Captcha {

	/**
	 * Creates the captcha tab.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported to do_tab().
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The captcha options tab, otherwise false.
	 */
	public static function do_tab( $tab ) {
		if ( $tab == 'captcha' ) {
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Adds the captcha tab.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported wpmem_add_captcha_tab() to add_tab().
	 *
	 * @param  array $tabs The array of tabs for the admin panel.
	 * @return array       The updated array of tabs for the admin panel.
	 */
	public static function add_tab( $tabs ) {
		return array_merge( $tabs, array( 'captcha' => 'Captcha' ) );
	}

	/**
	 * Builds the captcha options.
	 *
	 * @since 2.4.0
	 * @since 3.3.0 Ported wpmem_a_build_captcha_options() to build_settings().
	 */
	public static function build_settings() {

		// Globals.
		global $wpmem, $wpmem_updated_captcha_type;
		// Settings.
		$wpmem_captcha = get_option( 'wpmembers_captcha' );		
		?>
		<div class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">
				<?php wpmem_a_meta_box(); ?>
				<div class="postbox">
					<h3><span><?php esc_html_e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<strong><i><?php 
							/* translators: %s replaced with a link to the Users Guide on CAPTCHA settings. */
							printf( esc_html__( 'See the %sUsers Guide on CAPTCHA%s.', 'wp-members' ), '<a href="https://rocketgeek.com/plugins/wp-members/docs/registration/using-captcha/" target="_blank">', '</a>' );
						?></i></strong>
					</div>
				</div>
			</div> <!-- .inner-sidebar -->

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">

						<h3><?php esc_html_e( 'Manage CAPTCHA Options', 'wp-members' ); ?></h3>
						<div class="inside">
							<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo esc_url_raw( wpmem_admin_form_post_url() ); ?>">
							<?php wp_nonce_field( 'wpmem-update-settings' ); ?>
								<table class="form-table">
									<tr valign="top">
										<th><?php esc_html_e( 'CAPTCHA Type', 'wp-members' ); ?></th>
										<td><?php
											if ( 1 == $wpmem->captcha ) {
												$wpmem->captcha = 3; // reCAPTCHA v1 is fully obsolete. Change it to v2.
											}
											$captcha[] = esc_html__( 'reCAPTCHA v2', 'wp-members' ) . '|3';
											$captcha[] = esc_html__( 'reCAPTCHA v3', 'wp-members' ) . '|4';
											$captcha[] = esc_html__( 'Really Simple CAPTCHA', 'wp-members' ) . '|2';
											$captcha[] = esc_html__( 'hCaptcha', 'wp-members' ) . '|5';
											wpmem_form_field_echo( 'wpmem_settings_captcha', 'select', $captcha, $wpmem->captcha ); ?>
										</td>
									</tr>
									<?php if ( isset( $wpmem_updated_captcha_type ) ) { ?>
									<tr>
										<td colspan="2">
											<p><?php esc_html_e( 'CAPTCHA type was changed. Please verify and update the settings below for the new CAPTCHA type.', 'wp-members' ); ?></p>
										</td>
									</tr>
									<?php } ?>
								<?php // if reCAPTCHA is enabled...
								if ( 3 == $wpmem->captcha || 4 == $wpmem->captcha ) {
									$show_update_button = true; 
									$private_key = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['private'] : '';
									$public_key  = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['public']  : '';
									?>
									<tr valign="top">
										<th scope="row"><?php esc_html_e( 'reCAPTCHA Keys', 'wp-members' ); ?></th>
										<td>
											<p><?php if ( 
												   ! isset( $wpmem_captcha['recaptcha']['private'] )
												|| ! isset( $wpmem_captcha['recaptcha']['public'] )
												|| '' == $wpmem_captcha['recaptcha']['private'] 
												|| '' == $wpmem_captcha['recaptcha']['public'] ) {
												/* translators: %1$s & %2$s are replaced with a link to the reCAPTCHA sign up page. */
												printf( esc_html__( 'reCAPTCHA requires an API key, consisting of a "site" and a "secret" key. You can sign up for a %1$s free reCAPTCHA key%2$s', 'wp-members' ), "<a href=\"https://www.google.com/recaptcha/admin#whyrecaptcha\" target=\"_blank\">", '</a>' );
											} ?></p>
											<p><label><?php esc_html_e( 'Site Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_publickey" size="60" value="<?php echo esc_attr( $public_key ); ?>" /></p>
											<p><label><?php esc_html_e( 'Secret Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_privatekey" size="60" value="<?php echo esc_attr( $private_key ); ?>" /></p>
										 </td>
									</tr>
								<?php 
								} elseif ( 5 == $wpmem->captcha ) {
									$show_update_button = true; 
									$private_key = ( isset( $wpmem_captcha['hcaptcha'] ) ) ? $wpmem_captcha['hcaptcha']['secret']   : '';
									$public_key  = ( isset( $wpmem_captcha['hcaptcha'] ) ) ? $wpmem_captcha['hcaptcha']['api_key']  : '';
									?>
									<tr valign="top">
										<th scope="row"><?php esc_html_e( 'hCaptcha Keys', 'wp-members' ); ?></th>
										<td>
											<p><?php if ( '' == $private_key || '' == $public_key ) {
												/* translators: %1$s & %2$s are replaced with a link to the hCaptcha sign up page. */
												printf( esc_html__( 'hCaptcha requires an API key. You can sign up for %1$s an hCaptcha API key here %2$s', 'wp-members' ), "<a href=\"https://hcaptcha.com/\" target=\"_blank\">", '</a>' );
											} ?></p>
											<p><label><?php esc_html_e( 'API Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_publickey" size="60" value="<?php echo esc_attr( $public_key ); ?>" /></p>
											<p><label><?php esc_html_e( 'Secret Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_privatekey" size="60" value="<?php echo esc_attr( $private_key ); ?>" /></p>
										 </td>
									</tr>
								<?php
								// If Really Simple CAPTCHA is enabled.
								} elseif ( $wpmem->captcha == 2 ) {

									// Setup defaults.
									$defaults = array( 
										'characters'   => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
										'num_char'     => '4',
										'dim_w'        => '72',
										'dim_h'        => '30',
										'font_color'   => '0,0,0',
										'bg_color'     => '255,255,255',
										'font_size'    => '12',
										'kerning'      => '14',
										'img_type'     => 'png',
									);

									$args = ( isset( $wpmem_captcha['really_simple'] ) && is_array( $wpmem_captcha['really_simple'] ) ) ? $wpmem_captcha['really_simple'] : array();

									$args = wp_parse_args( $args, $defaults );

									// Explode colors.
									$font_color = explode( ',', $args['font_color'] );
									$bg_color   = explode( ',', $args['bg_color']   );

									$show_update_button = true;
									if ( is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) ) { ?>
										<tr>
											<th scope="row"><?php esc_html_e( 'Characters for image', 'wp-members' ); ?></th>
											<td><input name="characters" type="text" size="34" value="<?php echo esc_attr( $args['characters'] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Number of characters', 'wp-members' ); ?></th>
											<td><input name="num_char" type="text" size="2" value="<?php echo esc_attr( $args['num_char'] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Image dimensions', 'wp-members' ); ?></th>
											<td><?php esc_html_e( 'Width' ); ?> <input name="dim_w" type="text" size="2" value="<?php echo esc_attr( $args['dim_w'] ); ?>" /> <?php esc_html_e( 'Height' ); ?> <input name="dim_h" type="text" size="2" value="<?php echo esc_attr( $args['dim_h'] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Font color of characters', 'wp-members' ); ?></th>
											<td>R:<input name="font_color_r" type="text" size="2" value="<?php echo esc_attr( $font_color[0] ); ?>" /> G:<input name="font_color_g" type="text" size="2" value="<?php echo esc_attr( $font_color[1] ); ?>" /> B:<input name="font_color_b" type="text" size="2" value="<?php echo esc_attr( $font_color[2] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Background color of image', 'wp-members' ); ?></th>
											<td>R:<input name="bg_color_r" type="text" size="2" value="<?php echo esc_attr( $bg_color[0] ); ?>" /> G:<input name="bg_color_g" type="text" size="2" value="<?php echo esc_attr( $bg_color[1] ); ?>" /> B:<input name="bg_color_b" type="text" size="2" value="<?php echo esc_attr( $bg_color[2] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Font size', 'wp-members' ); ?></th>
											<td><input name="font_size" type="text" value="<?php echo esc_attr( $args['font_size'] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Width between characters', 'wp-members' ); ?></th>
											<td><input name="kerning" type="text" value="<?php echo esc_attr( $args['kerning'] ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Image type', 'wp-members' ); ?></th>
											<td><select name="img_type">
												<option<?php echo esc_attr( ( $args['img_type'] == 'png' ) ? ' selected' : '' ); ?>>png</option>
												<option<?php echo esc_attr( ( $args['img_type'] == 'gif' ) ? ' selected' : '' ); ?>>gif</option>
												<option<?php echo esc_attr( ( $args['img_type'] == 'jpg' ) ? ' selected' : '' ); ?>>jpg</option>
												</select>
											</td>
										</tr><?php

									} else {

										$show_update_button = true; // @todo Fixes whether the update button shows or not.
																	//       Could remove that logic altogether? ?>
										<tr>
											<td colspan="2">
												<p><?php esc_html_e( 'To use Really Simple CAPTCHA, you must have the Really Simple CAPTCHA plugin installed and activated.', 'wp-members' ); ?></p>
												<?php /* translators: %1$s & %2$s are replaced with a link to the Really Simple CAPTCHA plugin. */ ?>
												<p><?php printf( esc_html__( 'You can download Really Simple CAPTCHA from the %1$swordpress.org plugin repository%2$s.', 'wp-members' ), '<a href="http://wordpress.org/plugins/really-simple-captcha/">', '</a>' ); ?></p>
											</td>
										</tr><?php
									}
								} // End if RSC is selected.
								if ( $show_update_button ) { 

									switch ( $wpmem->captcha ) {
										case 1: 
											$captcha_type = 'recaptcha';
											break;
										case 2:
											$captcha_type = 'really_simple';
											break;
										case 3:
										case 4:
											$captcha_type = 'recaptcha2';
											break;
										case 5:
											$captcha_type = 'hcaptcha';
											break;
									} ?>
									<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td>
											<input type="hidden" name="wpmem_recaptcha_type" value="<?php echo esc_attr( $captcha_type ); ?>" />
											<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
											<?php submit_button( esc_html__( 'Update CAPTCHA Settings', 'wp-members' ) ); ?>
										</td>
									</tr>
								<?php } ?>
								</table>
							</form>
						</div><!-- .inside -->
					</div>
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div><!-- .metabox-holder -->
		<?php
	}

	/**
	 * Updates the captcha options.
	 *
	 * @since 2.8
	 * @since 3.3.0 Ported wpmem_update_captcha() to update().
	 *
	 * @return string The captcha option update message.
	 */
	public static function update() {
		
		global $wpmem;

		$settings     = get_option( 'wpmembers_captcha' );
		$update_type  = sanitize_text_field( wpmem_get( 'wpmem_recaptcha_type', '' ) );
		$which        = intval( wpmem_get( 'wpmem_settings_captcha', 0 ) );

		// If there are no current settings.
		if ( ! $settings ) {
			$settings = array();
		}
		
		if ( 0 != $which && $wpmem->captcha != $which ) {
			// Changing captcha type.
			global $wpmem_updated_captcha_type;
			$wpmem_updated_captcha_type = true;
			$wpmem->captcha = $which;
			wpmem_update_option( 'wpmembers_settings', 'captcha', $which, true );
		}

		if ( $update_type == 'recaptcha' || $update_type == 'recaptcha2' ) {
			$settings['recaptcha'] = array(
				'public'  => sanitize_text_field( wpmem_get( 'wpmem_captcha_publickey', '' ) ),
				'private' => sanitize_text_field( wpmem_get( 'wpmem_captcha_privatekey', '' ) ),
			);
			if ( 'recaptcha' == wpmem_get( 'wpmem_captcha_theme', false ) ) {
				$settings['recaptcha']['theme'] = sanitize_text_field( wpmem_get( 'wpmem_captcha_theme', '' ) );
			}
		}
		
		if ( 'hcaptcha' == $update_type ) {
			$settings['hcaptcha']['api_key'] = sanitize_text_field( wpmem_get( 'wpmem_captcha_publickey', '' ) );
			$settings['hcaptcha']['secret']  = sanitize_text_field( wpmem_get( 'wpmem_captcha_privatekey', '' ) );
		}

		if ( $update_type == 'really_simple' ) {
			$font_color = sanitize_text_field( wpmem_get( 'font_color_r', '' ) ) . ',' . sanitize_text_field( wpmem_get( 'font_color_g', '' ) ) . ',' . sanitize_text_field( wpmem_get( 'font_color_b', '' ) );
			$bg_color   = sanitize_text_field( wpmem_get( 'bg_color_r', '' ) )   . ',' . sanitize_text_field( wpmem_get( 'bg_color_g', '' ) )   . ',' . sanitize_text_field( wpmem_get( 'bg_color_b', '' ) );
			$settings['really_simple'] = array(
					'characters'   => sanitize_text_field( wpmem_get( 'characters', '' ) ),
					'num_char'     => sanitize_text_field( wpmem_get( 'num_char', '' ) ),
					'dim_w'        => sanitize_text_field( wpmem_get( 'dim_w', '' ) ),
					'dim_h'        => sanitize_text_field( wpmem_get( 'dim_h', '' ) ),
					'font_color'   => $font_color,
					'bg_color'     => $bg_color,
					'font_size'    => sanitize_text_field( wpmem_get( 'font_size', '' ) ),
					'kerning'      => sanitize_text_field( wpmem_get( 'kerning', '' ) ),
					'img_type'     => sanitize_text_field( wpmem_get( 'img_type', '' ) ),
			);
		}

		update_option( 'wpmembers_captcha', $settings, false );
		return esc_html__( 'CAPTCHA was updated for WP-Members', 'wp-members' );
	}
}