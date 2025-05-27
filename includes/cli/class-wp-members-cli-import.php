<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Manage WP-Members via WP-CLI
	 */
	class WP_Members_Import_CLI {

		public function __construct() {
			// WP-Members admin needs to be loaded manually.
			global $wpmem;
			if ( ! isset( $wpmem->admin ) ) {
				$wpmem->load_admin();
			}
		}

		/**
		 * Displays default path.
		 * 
		 * ## OPTIONS
		 * 
		 * [--dir=<dir_path>] 
		 * : Additional path value to add to ABSPATH.
		 * 
		 * @subcommand check-path
		 * @alias get-abspath
		 */
		public function check_path( $args, $assoc_args ) {
			$file_path = ABSPATH . 'wp-content/uploads/';
			
			if ( isset( $assoc_args['dir'] ) ) {
				$file_path = trailingslashit( trailingslashit( $file_path ) . $assoc_args['dir'] ); //2025/05/
			}

			WP_CLI::line( __( 'File path:', 'wp-members' ) . ' ' . $file_path );
			return;
		}

		/**
		 * Import memberships
		 * 
		 * ## OPTIONS
		 *
		 * --file=<file_name> 
		 * : The filename of the import csv.
		 * 
		 * [--dir=<dir_path>] 
		 * : Additional path value to add to ABSPATH.
		 * 
		 * [--verbose] 
		 * : Displays verbose results.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 */
		public function memberships( $args, $assoc_args ) {

			// Set specific criteria.
			$membership_key = ( isset( $assoc_args['key'] ) ) ? $assoc_args['key'] : "import_membership";
			$expiration_key = ( isset( $assoc_args['exp'] ) ) ? $assoc_args['exp'] : "import_expires";

			$file_name = $assoc_args['file'];
			$file_path = ( isset( $assoc_args['path'] ) ) ? ABSPATH . $assoc_args['path'] : ABSPATH . 'wp-content/uploads/';

			$file_path = ( isset( $assoc_args['dir'] ) ) ? trailingslashit( trailingslashit( $file_path ) . $assoc_args['dir'] ) : $file_path;

			// Check if file exists.
			if ( ! file_exists( trailingslashit( $file_path ) . $file_name ) ) {
				WP_CLI::line( __( 'File name:', 'wp-members' ) . ' ' . $file_name );
				WP_CLI::line( __( 'File path:', 'wp-members' ) . ' ' . $file_path );
				WP_CLI::error( __( 'Parameters given did not return a valid file. Check your filename and path values.', 'wp-members' ) );
			}

			$file = fopen( trailingslashit( $file_path ) . $file_name, 'r' );

			while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				//$line is an array of the csv elements
				$imports[] = $line;
			}
			fclose( $file ); 

			$meta_keys = array_shift( $imports );

			$x = 0;
			$e = 0;
			foreach ( $imports as $import ) {
				$keyed_values = array();
				foreach ( $import as $key => $field ) {
					$keyed_values[ $meta_keys[ $key ] ] = $field;
				}

				// Do we have a field we can get the user by?
				$user_id = false;

				// Do we have a field we can get the user by?
				if ( isset( $keyed_values['ID'] ) ) {
					$user_id = $keyed_values['ID'];
				} elseif ( isset( $keyed_values['user_login'] ) ) {
					$user = get_user_by( 'login', $keyed_values['user_login'] );
					$user_id = $user->ID;
				} elseif ( isset( $keyed_values['user_email'] ) ) {
					$user = get_user_by( 'email', $keyed_values['user_email'] );
					$user_id = $user->ID;
				}

				// Set specific criteria.
				$membership = ( isset( $keyed_values[ $membership_key ] ) ) ? $keyed_values[ $membership_key ] : false;
				$expiration = ( isset( $keyed_values[ $expiration_key ] ) ) ? $keyed_values[ $expiration_key ] : false;

				// Set expiration date - either "false" or MySQL timestamp.
				$date = ( $expiration ) ? date( "Y-m-d H:i:s", strtotime( $expiration ) ) : false;

				if ( ! $user_id ) {
					if ( ! $membership ) {
						$membership = __( 'unknown', 'wp-members' );
					}
					//if ( isset( $assoc_args['verbose'] ) ) {	
						/* translators: %s is the placeholder for the name of the membership, do not remove it. */
					//	WP_CLI::line( sprintf( __( 'Error adding %s membership for unknown user', 'wp-members' ), $membership ) );
					//}
					$e++;
				} else {

					// Set user product access.
					if ( $membership ) {
						wpmem_set_user_membership( $membership, $user_id, $date );
					}

					//if ( isset( $assoc_args['verbose'] ) ) {
						/* translators: %s is the placeholder for the name of the membership, do not remove it. */
					//	WP_CLI::line( sprintf( __( 'Set %s membership for user %s', 'wp-members' ), $membership, $user_id ) );
					//}
					$x++;
				}

				if ( isset( $assoc_args['verbose'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $keyed_values['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $keyed_values['user_login'] ) ) {
						$list_items['user_login'] = $keyed_values['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $keyed_values['user_email'] ) ) {
						$list_items['user_email'] = $keyed_values['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list_items['membership'] = $membership;
					if ( ! in_array( 'membership', $columns ) ) {
							$columns[] = 'membership';
						}
					if ( isset( $keyed_values[ $expiration_key ] ) ) {
						$list_items[ $expiration_key ] = $date;
						if ( ! in_array( $expiration_key, $columns ) ) {
							$columns[] = $expiration_key;
						}
					}

					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				/* translators: %s is the placeholder for the number of memberships updated, do not remove it. */
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( __( 'Imported memberships for %s users with %s errors', 'wp-members' ), $x, $e ) );
				/* translators: %s is the placeholder for the filename, do not remove it. */
				WP_CLI::confirm( sprintf( __( 'You have selected to delete the import file %s on completion. This cannot be undone.  Do you with to continue?', 'wp-members' ), $file_name ) );
				unlink( trailingslashit( $file_path ) . $file_name );
			} else {

				/* translators: %s is the placeholder for the number of memberships updated, do not remove it. */
				WP_CLI::success( sprintf( __( 'Imported memberships for %s users with %s errors', 'wp-members' ), $x, $e ) );
			}

		}

	}

	WP_CLI::add_command( 'mem import', 'WP_Members_Import_CLI' );
}