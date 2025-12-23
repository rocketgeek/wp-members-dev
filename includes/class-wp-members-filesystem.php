<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Update_Filesystem_Class {
	
	public $uploads = array();
	public $basedir;
	public $baseurl;
	protected $errors = array();
	protected $move_complete = false;

	public function __construct() {
		$this->uploads = wp_upload_dir();
		$this->basedir = $this->uploads['basedir'];
		$this->baseurl = $this->uploads['baseurl'];
	}

	public function is_move_complete() {
		return $this->move_complete;
	}

	public function set_move_complete( $val ) {
		$this->move_complete = $val;
	}

	public function get_errors() {
		return $this->errors;
	}

	public function get_file_list() {
		global $wpdb;
		$search_str = '%wpmembers/user_files/%';
		/**
		 * Filter the file list string.
		 * 
		 * @since 3.5.5
		 */
		$search_str = apply_filters( 'wpmem_get_file_list_search_str', $search_str );
		//return $wpdb->get_results( 'SELECT ID, post_author, post_title, guid FROM ' . $wpdb->posts . ' WHERE post_type = "attachment" AND guid LIKE "' . $search_str . '";' );
		return $wpdb->get_results( 'SELECT
				u1.ID,
				u1.post_author,
				u1.post_title,
				u1.guid,
				m1.meta_value AS wp_attached_file
			FROM ' . $wpdb->posts . ' u1
			JOIN ' . $wpdb->postmeta . ' m1 ON (m1.post_id = u1.id AND m1.meta_key = "_wp_attached_file")
			WHERE m1.meta_value like "%wpmembers/user_files/%";'
		);
	}

	public function update_filesystem() {

		// Check user capability to prevent being used by a non-permissioned user.
		/**
		 * Filter the required capability.
		 * 
		 * @since 3.5.5
		 */
		$has_req_caps = apply_filters( 'wpmem_update_filesystem_caps', 'delete_site' );

		if ( ! $has_req_caps ) {
			return false;
		}

		$results = $this->get_file_list();
	
		// If there are results, they need to move.
		if ( $results ) {

			$new_dir_location = trailingslashit( '/wpmembers/' . wpmem_get_file_dir_hash() );
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

			// Set up progress bar for WP-CLI.
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$count = count( $results );
				$progress = \WP_CLI\Utils\make_progress_bar( 'Moving uploaded user files.', $count );
			}

			foreach ( $results as $result ) {

				$user_id = $result->post_author;
				$guid = $result->guid;
				
				// User ID dir
				$user_dir_hash = wpmem_get_user_dir_hash( $user_id );

				// File name
				$filename = wpmem_hash_file_name( basename( get_attached_file( $result->ID ) ) );

				// Move location.
				$new_file_path = $this->basedir . $new_dir_location . trailingslashit( $user_dir_hash ) . $filename;

				$do_move = $this->move_attachment_file( $result->ID, $new_file_path );

				if ( is_wp_error( $do_move ) ) {
					$this->errors[ $user_id ] = $do_move->get_error_message();
				}

				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					$progress->tick();
				}
			}

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$progress->finish();
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
	public function move_attachment_file( $attachment_id, $new_file_path ) {

		// Check user capability to prevent being used by a non-permissioned user.
		/**
		 * Filter the required capability.
		 * 
		 * @since 3.5.5
		 */
		$has_req_caps = apply_filters( 'wpmem_update_filesystem_caps', 'delete_site' );

		if ( ! $has_req_caps ) {
			return false;
		}

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

	/**
	 * Deletes directories using rmdir even if they are not empty.
	 * 
	 * @see https://wpbitz.com/code-snippets/delete-directory-using-rmdir-in-php-even-if-the-directory-is-not-empty/
	 * 
	 * @param  string  $dir
	 * @return boolean
	 */
	public function delete_directory( $dir ) {

		// Check user capability to prevent being used by a non-permissioned user.
		/**
		 * Filter the required capability.
		 * 
		 * @since 3.5.5
		 */
		$has_req_caps = apply_filters( 'wpmem_update_filesystem_caps', 'delete_site' );

		if ( ! $has_req_caps ) {
			return false;
		}

		// Check if $dir is a directory, return false if it's not.
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		// Get all files and folders in the directory.
		$items = scandir( $dir );

		// Loop through items in the directory.
		foreach ( $items as $item ) {
			// Skip special entries "." (current directory) and ".." (parent directory)
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

		// Remove the main directory.
		return rmdir( $dir );
	}
}