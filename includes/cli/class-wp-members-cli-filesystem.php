<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_Filesystem_Upgrade {

		function __construct() {
			// Need the admin api for some CLI commands.
			global $wpmem;
			require_once $wpmem->path . 'includes/admin/api.php';
			require_once $wpmem->path . 'includes/class-wp-members-filesystem.php';
			$wpmem->filesystem = new WP_Members_Update_Filesystem_Class;
		}

		/**
		 * List files that need to be moved.
		 * 
		 * ## OPTIONS
		 * 
		 * [--page=<page>]
		 * : Current page of results to display. Default is 1.
		 * 
		 * [--per_page=<per_page>]
		 * : Number of results to display per page. Default is 20.
		 * 
		 * ## EXAMPLES
		 * 
		 *    wp mem fs-upgrade list --page=1 --per_page=10
		 */
		public function list( $args, $assoc_args ) {
			global $wpmem;
			$files_to_move = $wpmem->filesystem->get_file_list();
			if ( empty( $files_to_move ) ) {
				WP_CLI::line( WP_CLI::colorize( '%gThere are no files to move.%n' ) );
			} else {

				$current_page = WP_CLI\Utils\get_flag_value( $assoc_args, 'page', 1 );
				$per_page     = WP_CLI\Utils\get_flag_value( $assoc_args, 'per_page', 20 );

				$query_args = array(
					'number' => intval( $per_page ),
					'offset' => intval( $current_page )
				);

				$total_items = count( $files_to_move );
				$total_pages = ceil( $total_items/$per_page );

				$offset = ($current_page-1) * $per_page;

				// paginate with array_slice.
				$paged_files_to_move = array_slice( $files_to_move, $offset, $per_page );

				// Indicate paginated results.
				WP_CLI::line( 'Page ' . $current_page . ' of ' . $total_pages . ' (' . $total_items . ' records, ' . $per_page . ' per page)' );

				foreach( $paged_files_to_move as $file ) {
					$user = get_userdata( $file->post_author );
					$list[] = array( 
						'user id' => $file->post_author,
						'user_email' => $user->user_email,
						'file id' => $file->ID,
						'path (in wp-content/uploads)' => wpmem_get_sub_str( '/wpmembers/user_files', get_attached_file( $file->ID ) )
					);
				}
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'user id', 'user_email', 'file id', 'path (in wp-content/uploads)' ) );
				$formatter->display_items( $list );
	
			}
		}

		/**
		 * Move files to new directory structure.
		 * 
		 * ## EXAMPLES
		 * 
		 *    wp mem fs-upgrade move
		 */
		public function move( $args, $assoc_args ) {
			
			WP_CLI::line( 'This will move all files in the WP-Members uploads directory to a new directory structure.' );
			WP_CLI::line( 'Files are not deleted but the attachment data is updated in the database.' );
			WP_CLI::line( 'Make sure you have backed up your database.' );
			WP_CLI::confirm( 'Are you sure you want to continue?' );
			
			global $wpmem;
			$wpmem->filesystem->update_filesystem();
			if ( ! empty( $wpmem->filesystem->get_errors() ) ) {
				WP_CLI::error( 'There were errors' );
				foreach ( $wpmem->filesystem->get_errors() as $user_id => $error ) {
					$user = get_userdata( $user_id );
					$list[] = array(
						'user_id' => $user_id,
						'user_email' => $user->user_email,
						'error' => $error
					);
				}
				WP_CLI::line( 'Attempts to move uploads for the following user IDs returned an error:' );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'user_id', 'user_email', 'error' ) );
				$formatter->display_items( $list );
			} else {
				WP_CLI::line( WP_CLI::colorize( '%gNo errors during move.%n' ) );
				WP_CLI::line( 'Run <wp mem fs-upgrade list> to confirm all files were moved.' );
				WP_CLI::line( 'Run <wp mem fs-upgrade delete> to remove the old files and directories.' );
			}
		}

		/**
		 * Delete old uploads directory.
		 * 
		 * ## EXAMPLES
		 * 
		 *    wp mem fs-upgrade delete
		 */
		public function delete( $args, $assoc_args ) {
			global $wpmem;
			WP_CLI::confirm( 'This will delete all files in uploads/wpmembers/user_files/ and cannot be undone. Are you sure?' );
			$rmdir = $wpmem->filesystem->delete_directory( trailingslashit( $wpmem->filesystem->basedir ) . 'wpmembers/user_files/' );
			if ( $rmdir ) {
				WP_CLI::success( 'Deletion of old directory successful.' );
			} else {
				WP_CLI::error( 'Deletion processing returned an error. You may need to check directory permissions and/or delete the folder manually' );
			}
		}
	}
}
WP_CLI::add_command( 'mem fs-upgrade', 'WP_Members_CLI_Filesystem_Upgrade' );