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

			WP_CLI::line( 'File path:' . ' ' . $file_path );
			return;
		}

		/**
		 * Import memberships from a CSV file.
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
		 * [--dry-run] 
		 * : Preview what memberships will be set.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 * 
		 * [--cols=<columns>] 
		 * : A comma separated string of column names.
		 * 
		 * @since Unknown
		 */
		public function memberships( $args, $assoc_args ) {

			// Get memberships the site has (before we do anything).
			$site_memberships = wpmem_get_memberships();

			// Set specific criteria.
			$membership_key = ( isset( $assoc_args['key'] ) ) ? $assoc_args['key'] : "import_membership";
			$expiration_key = ( isset( $assoc_args['exp'] ) ) ? $assoc_args['exp'] : "import_expires";

			// Get the file contents.
			$csv = $this->get_csv_from_file( $assoc_args );

			$x = 0;
			$e = 0;
			foreach ( $csv as $row ) {

				$row = wpmem_sanitize_array( $row );

				// Do we have a field we can get the user by?
				$user_id = $this->get_user_id_from_row( $row );

				// Set specific criteria.
				$membership = ( isset( $row[ $membership_key ] ) ) ? $row[ $membership_key ] : false;
				$expiration = ( isset( $row[ $expiration_key ] ) ) ? $row[ $expiration_key ] : false;

				// Set expiration date - either "false" or MySQL timestamp.
				$date = ( $expiration ) ? date( "Y-m-d H:i:s", strtotime( $expiration ) ) : false;

				if ( ! $user_id ) {
					if ( ! $membership ) {
						$membership = 'unknown';
					}
					$e++; // Add to error count.
				} else {

					// Does the membership exist?
					if ( ! array_key_exists( $membership, $site_memberships ) ) {
						$membership = 'unknown' . ': ' . $membership;
						$e++; // Add to error count.
					} else {

						// Set user product access.
						if ( $membership && ! isset( $assoc_args['dry-run'] ) ) {
							wpmem_set_user_membership( $membership, $user_id, $date );
						}
						$x++; // Add to success count.
					}
				}

				if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $row['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $row['user_login'] ) ) {
						$list_items['user_login'] = $row['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $row['user_email'] ) ) {
						$list_items['user_email'] = $row['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list_items['membership'] = $membership;
					if ( ! in_array( 'membership', $columns ) ) {
							$columns[] = 'membership';
						}
					if ( isset( $row[ $expiration_key ] ) ) {
						$list_items[ $expiration_key ] = $date;
						if ( ! in_array( $expiration_key, $columns ) ) {
							$columns[] = $expiration_key;
						}
					}

					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( 'Imported memberships for %s users with %s errors.', $x, $e ) );
				$this->cleanup( $assoc_args );
			} else {
				if ( isset( $assoc_args['dry-run'] ) ) {
					WP_CLI::line( sprintf( 'Import memberships for %s users with %s errors.', $x, $e ) );
				} else {
					WP_CLI::success( sprintf( 'Imported memberships for %s users with %s errors.', $x, $e ) );
				}
			}
		}

		/**
		 * Activates all users in an imported CSV file.
		 * 
		 * ## OPTIONS
		 * 
		 * --file=<file_name> 
		 * : The filename of the import csv.
		 * 
		 * [--dir=<dir_path>] 
		 * : Additional path value to add to ABSPATH.
		 * 
		 * [--notify] 
		 * : Send email notification to user. Defaults to false.
		 * 
		 * [--pwd] 
		 * : Sets a password for the user (used with notify). Defaults to false.
		 * 
		 * [--verbose] 
		 * : Displays verbose results.
		 * 
		 * [--dry-run] 
		 * : Preview what users will be activated.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 * 
		 * [--cols=<columns>] 
		 * : A comma separated string of column names.
		 */
		public function activate( $args, $assoc_args ) {
			$csv = $this->get_csv_from_file( $assoc_args );

			$notify = ( isset( $assoc_args['notify'] ) ) ? true : false;
			$setpwd = ( isset( $assoc_args['pwd']    ) ) ? true : false;

			if ( $notify ) {
				WP_CLI::confirm( 'You have selected to send email notification to activated users. Is this correct?' );
			}

			if ( $setpwd ) {
				if ( $setpwd && ! $notify ) {
					WP_CLI::confirm( 'You have selected to set user passwords without sending notification to the user.  This is not recommended.  Do you wish to continue?' );
				} else {
					WP_CLI::confirm( 'You have selected to set passwords when activating the user. Is this correct?' );
				}
			}

			$x = 0;
			$e = 0;
			foreach ( $csv as $row ) {
				$row = wpmem_sanitize_array( $row );
				$user_id = $this->get_user_id_from_row( $row );
				if ( $user_id ) {
					if ( ! isset( $assoc_args['dry-run'] ) ) {
						wpmem_activate_user( $user_id, $notify, $setpwd );
					}
					$x++;
				} else {
					$e++;
				}

				if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $row['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $row['user_login'] ) ) {
						$list_items['user_login'] = $row['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $row['user_email'] ) ) {
						$list_items['user_email'] = $row['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( 'Set %s users as activated with %s errors.', $x, $e ) );
				$this->cleanup( $assoc_args );
			} else {
				if ( isset( $assoc_args['dry-run'] ) ) {
					WP_CLI::line( sprintf( 'Set %s users as activated with %s errors.', $x, $e ) );
				} else {
					WP_CLI::success( sprintf( 'Set %s users as activated with %s errors.', $x, $e ) );
				}
			}
		}

		/**
		 * Deactivates all users in an imported CSV file.
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
		 * [--dry-run] 
		 * : Preview what users will be deactivated.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 * 
		 * [--cols=<columns>] 
		 * : A comma separated string of column names.
		 * 
		 * @since 3.5.4
		 */
		public function deactivate( $args, $assoc_args ) {
			$csv = $this->get_csv_from_file( $assoc_args );

			$x = 0;
			$e = 0;
			foreach ( $csv as $row ) {
				$row = wpmem_sanitize_array( $row );
				$user_id = $this->get_user_id_from_row( $row );
				if ( $user_id ) {
					if ( ! isset( $assoc_args['dry-run'] ) ) {
						wpmem_deactivate_user( $user_id );
					}
					$x++;
				} else {
					$e++;
				}

				if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $row['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $row['user_login'] ) ) {
						$list_items['user_login'] = $row['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $row['user_email'] ) ) {
						$list_items['user_email'] = $row['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( 'Set %s users as deactivated with %s errors.', $x, $e ) );
				$this->cleanup( $assoc_args );
			} else {
				if ( isset( $assoc_args['dry-run'] ) ) {
					WP_CLI::line( sprintf( 'Set %s users as deactivated with %s errors.', $x, $e ) );
				} else {
					WP_CLI::success( sprintf( 'Set %s users as deactivated with %s errors.', $x, $e ) );
				}
			}
		}

		/**
		 * Confirms all users in an imported CSV file.
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
		 * [--dry-run] 
		 * : Preview what users will be confirmed.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 * 
		 * [--cols=<columns>] 
		 * : A comma separated string of column names.
		 * 
		 * @since 3.5.4
		 */
		public function confirm( $args, $assoc_args ) {
			$csv = $this->get_csv_from_file( $assoc_args );

			$x = 0;
			$e = 0;
			foreach ( $csv as $row ) {
				$row = wpmem_sanitize_array( $row );
				$user_id = $this->get_user_id_from_row( $row );
				if ( $user_id ) {
					if ( ! isset( $assoc_args['dry-run'] ) ) {
						wpmem_set_user_as_confirmed( $user_id );
					}
					$x++;
				} else {
					$e++;
				}

				if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $row['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $row['user_login'] ) ) {
						$list_items['user_login'] = $row['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $row['user_email'] ) ) {
						$list_items['user_email'] = $row['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( 'Set %s users as confirmed with %s errors.', $x, $e ) );
				$this->cleanup( $assoc_args );
			} else {
				if ( isset( $assoc_args['dry-run'] ) ) {
					WP_CLI::line( sprintf( 'Set %s users as confirmed with %s errors.', $x, $e ) );
				} else {
					WP_CLI::success( sprintf( 'Set %s users as confirmed with %s errors.', $x, $e ) );
				}
			}
		}

		/**
		 * Unonfirms all users in an imported CSV file.
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
		 * [--dry-run] 
		 * : Preview what users will be unconfirmed.
		 * 
		 * [--cleanup] 
		 * : Deletes the import file when import is completed.
		 * 
		 * [--cols=<columns>] 
		 * : A comma separated string of column names.
		 * 
		 * @since 3.5.4
		 */
		public function unconfirm( $args, $assoc_args ) {
			$csv = $this->get_csv_from_file( $assoc_args );

			$x = 0;
			$e = 0;
			foreach ( $csv as $row ) {
				$row = wpmem_sanitize_array( $row );
				$user_id = $this->get_user_id_from_row( $row );
				if ( $user_id ) {
					if ( ! isset( $assoc_args['dry-run'] ) ) {
						wpmem_set_user_as_unconfirmed( $user_id );
					}
					$x++;
				} else {
					$e++;
				}

				if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
					// Set columns for output.
					$columns = array();
					if ( isset( $row['ID'] ) ) {
						$list_items['user ID'] = $user_id;
						if ( ! in_array( 'user ID', $columns ) ) {
							$columns[] = 'user ID';
						}
					}
					if ( isset( $row['user_login'] ) ) {
						$list_items['user_login'] = $row['user_login'];
						if ( ! in_array( 'user_login', $columns ) ) {
							$columns[] = 'user_login';
						}
					}
					if ( isset( $row['user_email'] ) ) {
						$list_items['user_email'] = $row['user_email'];
						if ( ! in_array( 'user_email', $columns ) ) {
							$columns[] = 'user_email';
						}
					}
					$list[] = $list_items;
				}
			}

			if ( isset( $assoc_args['verbose'] ) || isset( $assoc_args['dry-run'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $columns );
				$formatter->display_items( $list );
			}

			if ( isset( $assoc_args['cleanup'] ) ) {
				WP_CLI::line( WP_CLI::colorize( "%GSuccess:%n " ) . sprintf( 'Set %s users as unconfirmed with %s errors.', $x, $e ) );
				$this->cleanup( $assoc_args );
			} else {
				if ( isset( $assoc_args['dry-run'] ) ) {
					WP_CLI::line( sprintf( 'Set %s users as unconfirmed with %s errors.', $x, $e ) );
				} else {
					WP_CLI::success( sprintf( 'Set %s users as unconfirmed with %s errors.', $x, $e ) );
				}
			}
		}

		/**
		 * Gets the user ID, checks for columns in order of
		 * ID, user_login, and user_email. 
		 * @since 3.5.4
		 */
		private function get_user_id_from_row( $row ) {
			$user_id = false;
			// Do we have a field we can get the user by?
			if ( isset( $row['ID'] ) ) {
				$user_id = $row['ID'];
			} elseif ( isset( $row['user_login'] ) ) {
				$user = get_user_by( 'login', $row['user_login'] );
				$user_id = $user->ID;
				$user_id = ( $user ) ? $user->ID : false;
			} elseif ( isset( $row['user_email'] ) ) {
				$user = get_user_by( 'email', sanitize_email( $row['user_email'] ) );
				$user_id = ( $user ) ? $user->ID : false;
			}
			return $user_id;
		}

		/**
		 * Gets the file path info.
		 * @since 3.5.4
		 */
		private function get_file_info( $assoc_args ) {
			$file_info['name'] = $assoc_args['file'];
			$file_path = ( isset( $assoc_args['path'] ) ) ? ABSPATH . $assoc_args['path'] : ABSPATH . 'wp-content/uploads/';

			$file_info['path'] = ( isset( $assoc_args['dir'] ) ) ? trailingslashit( trailingslashit( $file_path ) . $assoc_args['dir'] ) : $file_path;

			$file_info['file'] = trailingslashit( $file_info['path'] ) . $file_info['name'];
			return $file_info;
		}

		/**
		 * Converts the CSV to a keyed array.
		 * @since 3.5.4
		 */
		private function get_csv_from_file( $assoc_args ) {
			
			$file_info = $this->get_file_info( $assoc_args );

			// Check if file exists.
			if ( ! file_exists( $file_info['file'] ) ) {
				WP_CLI::line( 'File name:' . ' ' . $file_info['name'] );
				WP_CLI::line( 'File path:' . ' ' . $file_info['path'] );
				WP_CLI::error( 'Parameters given did not return a valid file. Check your filename and path values.' );
			}

			$cols = ( isset( $assoc_args['cols'] ) ) ? explode( ",", $assoc_args['cols'] ) : false;
			return wpmem_csv_to_array( $file_info['file'], $cols );
		}

		/**
		 * Deletes an import file when complete.
		 * @since 3.5.4
		 */
		private function cleanup( $assoc_args ) {
			// Don't delete if it's a dry run.
			if ( ! isset( $assoc_args['dry-run'] ) ) {
				$file_info = $this->get_file_info( $assoc_args );
				WP_CLI::confirm( sprintf( 'You have selected to delete %s on completion. This cannot be undone. Do you with to continue?', $file_info['name'] ) );
				$result = unlink( $file_info['file'] );
				if ( $result ) {
					WP_CLI::success( sprintf( '%s was deleted.', $file_info['name'] ) );
				} else {
					WP_CLI::success( sprintf( 'Unable to delete %s.', $file_info['name'] ) );
				}
			}
		}
	}

	WP_CLI::add_command( 'mem import', 'WP_Members_Import_CLI' );
}