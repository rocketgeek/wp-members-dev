<?php
/**
 * WP-Members Admin functions
 *
 * Static functions to manage the shortcodes tab.
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

class WP_Members_Admin_Tab_Shortcodes {

	/**
	 * Creates the tab.
	 *
	 * @since 3.2.0
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The tab html, otherwise false.
	 */
	static function do_tab( $tab ) {
		if ( $tab == 'shortcodes' || ! $tab ) {
			// Render the tab.
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Builds the dialogs panel.
	 *
	 * @since 2.2.2
	 * @since 3.3.0 Ported from wpmem_a_build_dialogs().
	 *
	 * @global object $wpmem
	 */
	static function build_settings() { 
		global $wpmem; ?>
		<div class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">
				<?php wpmem_a_meta_box(); ?>
				<div class="postbox">
					<h3><span><?php esc_html_e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<strong><i>See the <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/shortcodes/" target="_blank">Users Guide on dialogs</a>.</i></strong>
					</div>
				</div>
			</div> <!-- .inner-sidebar -->

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<div class="inside">
							<form name="updateshortcodesform" id="updateshortcodesform" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>"> 
							<?php 
                            wp_nonce_field( 'wpmem-update-shortcodes' );
                            $wpmem_enable_field_sc = $wpmem->shortcodes->enable_field; 
                            $wpmem_enable_field_sc = ( $wpmem_enable_field_sc ) ? $wpmem_enable_field_sc : 0;
                            $sc_notice_start = '<a href="https://rocketgeek.com/plugins/wp-members/docs/shortcodes/field-shortcodes/#security" target="_blank">';
                            ?>
                            <h3>WP-Members <?php esc_html_e( 'Shortcodes', 'wp-members' ); ?></h3>
                            <p><?php esc_html_e('&#91;wpmem_field&#93; shortcode settings:', 'wp-members'); ?></p>
                            <table>
                                <tr>
                                    <td><input type="radio" name="wpmem_enable_field_sc" id="wpmem_enable_field_sc_1" value="0" <?php checked( $wpmem_enable_field_sc, 0 ); ?> /></td>
                                    <td><label><?php esc_html_e('Fully disabled', 'wp-members'); ?></label>
                                    <span class="description"><?php esc_html_e( 'Fully disables the &#91;wpmem_field&#93; shortcode.', 'wp-members' ); ?></span></td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="wpmem_enable_field_sc" id="wpmem_enable_field_sc_2" value="1" <?php checked( $wpmem_enable_field_sc, 1 ); ?> /></td>
                                    <td><label><?php esc_html_e('Partially enabled', 'wp-members'); ?></label>
                                    <span class="description"><?php esc_html_e( 'Enables the shortcode to display only if the user has "list_users" capability.', 'wp-members' ); ?></span></td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="wpmem_enable_field_sc" id="wpmem_enable_field_sc_3" value="2" <?php checked( $wpmem_enable_field_sc, 2 ); ?> /></td>
                                    <td><label><?php esc_html_e('Fully enabled', 'wp-members'); ?></label>
                                    <span class="description"><?php printf(esc_html__('%sSee docs for security implications%s.','wp-members'),$sc_notice_start,'</a>'); ?></span></td>
                                </tr>
                                <?php // @todo Future expansion ?>
                                <!--<tr>
                                    <td colspan=2>
                                        <label for="wpmem_fields_sc_allowed_meta"><?php // esc_html_e( 'Meta keys allowed for &#91;wpmem_field&#93; shortcode', 'wp-members' ); ?></label>
                                        <textarea name="wpmem_fields_sc_allowed_meta" rows="3" cols="50" id="" class="large-text code"><?php  // echo esc_textarea( $some_var ); ?></textarea>
                                    </td>
                                </tr>-->
                            </table>
                            <input type="hidden" name="wpmem_admin_a" value="update_shortcodes" />
                            <?php submit_button(); ?>
							</form>
						</div><!-- .inside -->
					</div><!-- #post-box -->
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div> <!-- .metabox-holder -->
		<?php
	}


	/**
	 * Updates the dialog settings.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported from wpmem_update_dialogs().
	 *
	 * @global object $wpmem
	 * @return string The dialogs updated message.
	 */
	static function update() {

		global $wpmem;

		// Check nonce.
		check_admin_referer( 'wpmem-update-shortcodes' );

		$wpmem_settings = get_option( 'wpmembers_settings' );

		if ( isset( $_POST['wpmem_enable_field_sc'] ) ) {
			$wpmem_settings['shortcodes']['enable_field'] = intval( $_POST['wpmem_enable_field_sc'] );
		} else {
			$wpmem_settings['shortcodes']['enable_field'] = 0;
		}

		update_option( 'wpmembers_settings', $wpmem_settings, true );

		$wpmem->shortcodes->enable_field = $wpmem_settings['shortcodes']['enable_field'];

		return esc_html__( 'WP-Members shortcode settings were updated', 'wp-members' );
	}

} // End of file.