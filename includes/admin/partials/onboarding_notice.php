<?php
global $current_screen, $wpmem;

$which = ( 'update_pending' == $wpmem->install_state ) ? 'update_pending_notice_args' : 'new_install_notice_args'; 

$utms = array( 
	'utm_source'   => get_site_url(),
	'utm_medium'   => 'wp-members-core-plugin',
	'utm_campaign' => 'plugin-install',
);
	
$action_complete = ( 'update_pending' == $wpmem->install_state ) ? esc_html__( 'WP-Members update complete', 'wp-members' ) : esc_html__( 'WP-Members installation complete', 'wp-members' );

if ( 'finalize' == wpmem_get( 'wpmem_onboarding_action' ) ) {
?>
<div class="notice notice-info is-dismissible">
	<h3><?php echo $action_complete; ?></h3>
	<?php if ( 'update_pending' != $wpmem->install_state ) { ?>
		<p>WP-Members installs some basic defaults to get you started. Be sure to review <a href="<?php echo admin_url(); ?>options-general.php?page=wpmem-settings">the plugin's default setup here</a>.
		There are links to related documentation in the plugin settings.  There are also some helpful links below.</p>     
	<?php } ?>
	<ul>
		<li>&raquo; <a href="<?php echo $release_notes_link . "?" . http_build_query( $utms ); ?>" target="_blank"><?php printf( esc_html__( 'WP-Members version %s release notes', 'wp-members' ), $wpmem->version ); ?></a></li>
		<li>&raquo; <a href="https://rocketgeek.com/plugins/wp-members/docs/?<?php echo http_build_query( $utms ); ?>" target="_blank"><?php _e( 'WP-Members documentation', 'wp-members' ); ?></a></li>
	</ul>  
	<h3><?php _e( 'Want more features? Or need help?', 'wp-members' ); ?></h3>
	<p>There are <a href="https://rocketgeek.com/store/?<?php echo http_build_query( $utms ); ?>" target="_blank">premium plugin add-ons</a> available as well as a <a href="https://rocketgeek.com/plugins/wp-members/support-options/?<?php echo http_build_query( $utms ); ?>" target="_blank">discounted bundle</a>.<br />
	If you need assistance, consider a <a href="https://rocketgeek.com/plugins/wp-members/support-options/?<?php echo http_build_query( $utms ); ?>" target="_blank">premium support subscription</a>.</p>
	<p>
		<a href="<?php echo admin_url() . 'options-general.php?page=wpmem-settings'; ?>"><?php _e( 'Go to WP-Members settings', 'wp-members' ); ?></a> | 
		<a href="<?php echo admin_url() . 'plugins.php'; ?>"><?php _e( 'Go to WordPress plugins page', 'wp-members' ); ?></a> |
		<a href="<?php echo admin_url() . 'update-core.php'; ?>"><?php _e( 'Go to WordPress updates page', 'wp-members' ); ?></a>
	</p>
</div>

<?php } else { ?>

<div class="notice notice-info">
<form action="" method="post">
	<p style="font-weight:bold;"><?php echo esc_html( $notice_heading ); ?></p>

<?php if ( $show_release_notes ) { ?>
	<p class="description"><a href="<?php echo esc_url_raw( $release_notes_link ); ?>" target="_blank"><?php esc_html_e( 'Read the release notes', 'wp-members' ); ?></a></p>
<?php } ?>
<?php if ( false == $this->has_user_opted_in() ) { ?>
	<h3><?php esc_html_e( 'Never miss an important update!', 'wp-members' ); ?></h3>
	<p><input type="checkbox" name="optin" value="1" checked />
		<?php esc_html_e( 'Opt-in to our security and feature updates notifications and non-sensitive diagnostic tracking.', 'wp-members' );?>
	</p>
	<p class="description">
		<?php esc_html_e( 'This is only so we know how the plugin is being used so we can make it better and more secure.', 'wp-members' ); ?><br />
		<?php esc_html_e( 'We do not track any personal information, and no data is ever shared with third parties!', 'wp-members' ); ?>
	</p>
	<?php } ?>
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $notice_button ); ?> &raquo;"></p>
	<input type="hidden" name="wpmem_onboarding_action" value="finalize" />
</form>
</div>

<?php } ?>