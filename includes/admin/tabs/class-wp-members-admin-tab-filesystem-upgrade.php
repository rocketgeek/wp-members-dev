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
		return array_merge( $tabs, array( 'filesystem-upgrade' => 'Filesystem' ) );
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

		global $wpmem_filesystem_upgrade;
		$wpmem_filesystem_upgrade = New WP_Members_Update_Filesystem_Class();

		// Check how many files to move.
		$files_to_move = $wpmem_filesystem_upgrade->get_file_list();
		$num_to_move = count( $files_to_move );

		if ( isset( $_POST['update-filesystem-confirm'] ) && 'move' == $_POST['update-filesystem-confirm'] ) {
			$wpmem_filesystem_upgrade->update_filesystem();
			$wpmem_filesystem_upgrade->moves_complete = true;
		} ?>

		<div class="wrap">
			<form name="update-filesystem-form" id="update-filesystem-form" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>"> 
			<?php wp_nonce_field( 'wpmem-upgrade-filesystem' ); ?>
			<h2>Upgrade the WP-Members filesystem</h2>
			<?php

		if ( ! empty( $wpmem_filesystem_upgrade->errors ) ) { ?>
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
			<?php foreach ( $wpmem_filesystem_upgrade->errors as $user_id => $error ) {
				echo '<tr><td>' . $user_id . '</td><td>' . $error . '</td></tr>';
			} ?>
			</table>
		<?php } elseif ( $wpmem_filesystem_upgrade->moves_complete ) { ?>
			<p>Filesystem was updated.</p>
			<p>Once you have confirmed your moves and you are satisfied that things are correct, 
				you will need to delete the original directory and files.<br />
				<a href="<?php echo esc_url( admin_url() . '/options-general.php?page=wpmem-settings&tab=filesystem-upgrade' ); ?>">Continue to the initial screen
				and select the delete option</a>.
			</p>
		<?php } elseif ( isset( $_POST['update-filesystem-confirm'] ) && 'delete' == $_POST['update-filesystem-confirm'] ) {
			// Clean up (delete) old dir.
			$rmdir = $wpmem_filesystem_upgrade->delete_directory( trailingslashit( $wpmem_filesystem_upgrade->basedir ) . 'wpmembers/user_files' );
			if ( $rmdir ) {
				echo '<p>Deletion was successful.</p>';
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
					1. Move the current files to a new structure.
					2. Delete the old files and directory.
				</p>
				<p>
					Please make sure you have backed up your database as well 
					as your filesystem in case you need to roll back.
				</p>
				
			</div>
			<h3>Step 1: Move the filesystem</h3>
			<input type="radio" id="move" name="update-filesystem-confirm" value="move" /> Move the current filesystem.<br>
			<p>
				There are <?php echo $num_to_move; ?> files to move.<br/>
				Make sure you have backed up your database and filesystem.
			</p>
			<h3>Step 2: Delete old filesystem</h3>
			<input type="radio" id="delete" name="update-filesystem-confirm" value="delete" /> Delete the old filesystem.<br>
			<p>
				Make sure you have run step 1 above first.<br/>
				Make sure you have backed up your database and filesystem.
			</p>
			<input type="hidden" name="wpmem_admin_a" value="update_filesystem" />
			<?php submit_button();
		} ?>
			</form>
		</div><!-- #post-box -->
		<?php
	}
}

class WP_Members_Update_Filesystem_Class {
	
	public $errors = array();
	public $uploads = array();
	public $basedir;
	public $baseurl;
	public $moves_complete = false;

	function __construct() {
		$this->uploads = wp_upload_dir();
		$this->basedir = $this->uploads['basedir'];
		$this->baseurl = $this->uploads['baseurl'];
	}

	function get_file_list() {
		global $wpdb;
		return $wpdb->get_results( 'SELECT ID, post_author, post_title, guid FROM ' . $wpdb->posts . ' WHERE post_type = "attachment" AND guid LIKE "%wpmembers/user_files/%";' );
	}
	
	function update_filesystem() {

		$results = $this->get_file_list();
	
		// If there are results, they need to move.
		if ( $results ) {

			// How long of a hash?
			$hash_len = 24;
			
			$dir_hash = get_option( 'wpmem_file_dir_hash' );
			if ( ! $dir_hash ) {
				$dir_hash = wp_generate_password( $hash_len, false, false );
				update_option( 'wpmem_file_dir_hash', $dir_hash );
			}

			$new_dir_location = trailingslashit( '/wpmembers/user_files_' . $dir_hash );
			// If new_dir_location does not exist, create it.
			if ( ! is_dir( $this->basedir . $new_dir_location ) ) {
				mkdir( $this->basedir . $new_dir_location, 0755, true );
				// Add indexes and htaccess
				wpmem_create_file( array(
					'path'     => $this->basedir . $new_dir_location,
					'name'     => 'index.php',
					'contents' => "<?php // Silence is golden."
				) );
				wpmem_create_file( array(
					'path'     => $this->basedir . $new_dir_location,
					'name'     => '.htaccess',
					'contents' => "Options -Indexes"
				) );
			}

			foreach ( $results as $result ) {

				$user_id = $result->post_author;
				$guid = $result->guid;
				
				// User ID dir
				$user_dir_hash = get_user_meta( $user_id, 'wpmem_user_dir_hash', true );
				if ( ! $user_dir_hash ) {		
					$uid_len = strlen( $user_id );
					$user_dir_hash = $user_id . wp_generate_password( ( $hash_len-$uid_len ), false, false );
					update_user_meta( $user_id, 'wpmem_user_dir_hash', $user_dir_hash );
				}

				// File name
				$ext = pathinfo( $guid, PATHINFO_EXTENSION );
				$key = sha1( random_bytes(24) );
				$key = substr( $key, 0, $hash_len );

				$new_dir = $new_dir_location . trailingslashit( $user_dir_hash ) . $key;

				// Move location.
				$new_file_path = $this->basedir . $new_dir . "." . $ext;

				$do_move = $this->move_attachment_file( $result->ID, $new_file_path );

				if ( is_wp_error( $do_move ) ) {
					$this->errors[ $user_id ] = $do_move->get_error_message();
				}
			}
		}
	}

	// this one from grok.
	/**
	 * Move/rename a media attachment file and update WordPress metadata.
	 *
	 * @param int    $attachment_id The ID of the attachment post.
	 * @param string $new_file_path Absolute path to the new location (including filename).
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	function move_attachment_file( $attachment_id, $new_file_path ) {
		// Verify it's a valid attachment
		if ( get_post_type( $attachment_id ) !== 'attachment' ) {
			return new WP_Error( 'invalid_attachment', 'Invalid attachment ID.' );
		}

		// Get current absolute file path
		$old_file_path = get_attached_file( $attachment_id );
		if ( ! file_exists( $old_file_path ) ) {
			$error = new WP_Error( 'file_missing', 'Original file not found.' );
			if ( is_wp_error( $error ) ) {
				// Try if it's http
				$old_file_path = str_replace( trailingslashit( $this->baseurl ), '', $old_file_path );
				if ( ! file_exists( $old_file_path ) ) {
					// Still an error.
					return $error;
				}
			}
		}

		// Ensure destination directory exists
		$new_dir = dirname( $new_file_path );
		if ( ! file_exists( $new_dir ) ) {
			if ( ! wp_mkdir_p( $new_dir ) ) {
				return new WP_Error( 'dir_create_failed', 'Could not create destination directory.' );
			}
		}

		// Move the original file
		if ( ! rename( $old_file_path, $new_file_path ) ) {
			return new WP_Error( 'move_failed', 'Failed to move the main file.' );
		}

		// Update the main attached file path (relative to uploads dir)
		$new_relative_path = str_replace( trailingslashit( $this->basedir ), '', $new_file_path );
		update_attached_file( $attachment_id, $new_relative_path );

		// For images: regenerate metadata (updates paths for all thumbnail sizes)
		// This also moves the thumbnail files if needed (rename handles it via metadata update)
		if ( wp_attachment_is_image( $attachment_id ) ) {
			$new_metadata = wp_generate_attachment_metadata( $attachment_id, $new_file_path );
			wp_update_attachment_metadata( $attachment_id, $new_metadata );
		}

		// Optional: Update the GUID (rarely needed, but can help in some cases)
		wp_update_post( array( 'ID' => $attachment_id, 'guid' => trailingslashit( $this->basedir ) . $new_relative_path ) );

		wpmem_create_file( array(
			'path'     => $new_dir,
			'name'     => 'index.php',
			'contents' => "<?php // Silence is golden."
		) );

		return true;
	}

	// From https://www.scientecheasy.com/2025/09/delete-directory-in-php.html/
	function delete_directory( $dir ) {
		// Check if the given path is actually a directory.
		if ( ! is_dir( $dir ) ) {
			return false; // If it's not a directory, it will stop execution.
		}

		// Get all files and folders inside the directory.
		$items = scandir( $dir );
		// Apply loop through each item in the directory.
		foreach ( $items as $item ) {
			// Skip the special entries "." (current directory) and ".." (parent directory)
			if ( $item == '.' || $item == '..' ) {
				continue;
			}
			// Build the full path of the current item.
			$path = $dir . DIRECTORY_SEPARATOR . $item;

			// If the item is a directory, call this function recursively.
			if ( is_dir( $path ) ) {
				$this->delete_directory( $path );
			} else { // If the item is a file, delete it.
				unlink( $path );
			}
		}
		// After all files and subdirectories are deleted, remove the main directory.
		return rmdir( $dir );
	}
}
// End of file.