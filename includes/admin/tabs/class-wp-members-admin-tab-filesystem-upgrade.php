<?php
/**
 * WP-Members Admin functions
 *
 * Functions to upgrade the filesystem.
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

class WP_Members_Admin_Filesystem_Upgrade {

	/**
	 * Creates the tab.
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The tab html, otherwise false.
	 */
	static function do_tab( $tab ) {
		if ( $tab == 'filesystem-upgrade' || ! $tab ) {
			// Render the tab.
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Adds the tab.
	 *
	 * @param  array $tabs The array of tabs for the admin panel.
	 * @return array       The updated array of tabs for the admin panel.
	 */
	public static function add_tab( $tabs ) {
		return array_merge( $tabs, array( 'filesystem-upgrade' => esc_html__( 'Filesystem', 'wp-members' ) ) );
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

		global $wpmem;
		require_once $wpmem->path . 'includes/class-wp-members-filesystem.php';
		$wpmem->filesystem = New WP_Members_Update_Filesystem_Class();

		// Check how many files to move.
		$files_to_move = $wpmem->filesystem->get_file_list();
		$num_to_move = count( $files_to_move );

		if ( isset( $_POST['update-filesystem-confirm'] ) && 'move' == $_POST['update-filesystem-confirm'] ) {
			$wpmem->filesystem->update_filesystem();
			$wpmem->filesystem->set_move_complete( true );
			update_option( 'wpmem_upgrade_filesystem_move_complete', 1, false );
		} ?>

		<div class="wrap">
			<form name="update-filesystem-form" id="update-filesystem-form" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>"> 
			<?php wp_nonce_field( 'wpmem-upgrade-filesystem' ); ?>
			<h2><?php esc_html_e( 'Upgrade the WP-Members filesystem', 'wp-members' ); ?></h2>
			<?php

		if ( ! empty( $wpmem->filesystem->get_errors() ) ) { ?>
			<p>
				File moves were attempted.  The table below displays user ID folders that were not moved.
				This may be all existing files or only some.  You should verify by checking 
				the filesystem directly.
			</p>
			<p>
				Once you have confirmed your moves and you are satisfied that things are correct, 
				you will need to delete the original directory and files.<br />
				<a href="<?php echo trailingslashit( admin_url() ) . '/options-general.php?page=wpmem-settings&tab=filesystem-upgrade'; ?>">Continue to the initial screen
				and select the delete option</a>.
			</p>
			<table class="widefat fixed" cellspacing="0">
				<tr>
					<th id="user_id" style="width:68px">User ID</th>
					<th id="error">Error</th>
				</tr>
			<?php foreach ( $wpmem->filesystem->get_errors() as $user_id => $error ) {
				echo '<tr><td>' . $user_id . '</td><td>' . $error . '</td></tr>';
			} ?>
			</table>
		<?php } elseif ( $wpmem->filesystem->is_move_complete() ) { ?>
			<p>Filesystem was updated.</p>
			<?php
				// Get an updated count.
				$files_to_move = $wpmem->filesystem->get_file_list(); 
				$num_to_move = count( $files_to_move );
				if ( $num_to_move > 0 ) {
				echo '<p>There are ' . $num_to_move . ' files that were not moved. You may run step 1 again to
				attempt to move these, or you may need to move them manually.';
			} ?>
			<p>If you wish to proceed to step 2 to delete the original directories and files you may do so below.</p>
			<h3>Step 2: Delete old filesystem</h3>
			<input type="radio" id="delete" name="update-filesystem-confirm" value="delete" /> Delete the old filesystem.<br>
			<?php submit_button(); ?>
		<?php } elseif ( isset( $_POST['update-filesystem-confirm'] ) && 'delete' == $_POST['update-filesystem-confirm'] ) {
			// Clean up (delete) old dir.
			$rmdir = $wpmem->filesystem->delete_directory( trailingslashit( $wpmem->filesystem->basedir ) . 'wpmembers/user_files' );
			if ( $rmdir ) {
				echo '<p>Deletion was successful.</p>';
				delete_option( 'wpmem_upgrade_filesystem_move_complete' );
			} else {
				echo '<p>Deletion was not successful. You may need to check directory permissions and/or delete the folder manually</p>';
			} ?>
			<p><a href="<?php echo esc_url( admin_url() . '/options-general.php?page=wpmem-settings&tab=options' ); ?>">Return to main plugin page</a></p>
		<?php } else { ?>
			<div style="width:500px;">
				<p>
					Your current configuration indicates that you have uploaded files within the WP-Members 
					filesystem in a deprecated configuration.  You have the option to move these files. However, 
					this cannot be reversed or undone.    
				</p>
				<p>
					The upgrade process is two steps:
					<ol>
					<li>Move the current files to a new structure.</li>
					<li>Delete the old files and directory.</li>
					</ol>
				</p>
				<p>
					This process will move uploaded user files and delete the 
					previous directories.  It cannot be undone. <strong>Please 
					make sure you have backed up the database and the filesystem
					before running these actions</strong>.
				</p>
				
			</div>
			<h3>Step 1: Move the filesystem</h3>
			<input type="radio" id="move" name="update-filesystem-confirm" value="move" /> Move the current filesystem.<br>
			<p>
				There are <?php echo $num_to_move; ?> files to move.<br/>
			</p>
			<?php
			$step_1_done = get_option( 'wpmem_upgrade_filesystem_move_complete' );
			if ( $step_1_done ) { ?>
			<h3>Step 2: Delete old filesystem</h3>
			<input type="radio" id="delete" name="update-filesystem-confirm" value="delete" /> Delete the old filesystem.<br>
			<p>
				Make sure you have run step 1 above first.<br/>
				Make sure step 1 indicates there are no files to move or 
				you are satisfied that all files have been move to new directories.
			</p>
			<?php } ?>
			<input type="hidden" name="wpmem_admin_a" value="update_filesystem" />
			<?php submit_button();
		} ?>
			</form>
		</div><!-- #post-box -->
		<?php
	}
}
// End of file.