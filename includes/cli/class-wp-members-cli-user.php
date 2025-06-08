<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_User {
		function __construct() {
			// Need the admin api for some CLI commands.
			global $wpmem;
			require_once $wpmem->path . 'includes/admin/api.php';
		}

		/**
		 * CLI command to activate users.
		 *
		 * ## OPTIONS
		 *
		 * [--id=<user_id>]
		 * : Activate user by user ID.
		 * 
		 * [--login=<user_login>] 
		 * : Activate user by login (username).
		 * 
		 * [--email=<user_email>] 
		 * : Activate user by email.
		 *
		 * [--notify=<boolean>]
		 * : Whether to send notifcation to user (true if omitted).
		 * 
		 * [--all] 
		 * : Activates all pending users.
		 * 
		 * [--deactivated] 
		 * : Activates all deactivated users (instead of pending) when using --all.
		 *
		 * @since 3.3.5
		 */
		public function activate( $args, $assoc_args ) {

			if ( ! wpmem_is_enabled( 'mod_reg' ) ) {
				WP_CLI::error( 'Moderated registration is not enabled in WP-Members options.' );
			}

			if ( ! isset( $assoc_args['all'] ) ) {
				if ( ! isset( $assoc_args['id'] ) && ! isset( $assoc_args['login'] ) && ! isset( $assoc_args['email'] ) ) {
					WP_CLI::error( 'Missing required parameter. Specify user ID with --id=<user_id> or --all to activate all users.' );
				}
			}

			// Sending notification? (sends notification if omitted).
			$notify  = ( isset( $assoc_args['notify'] ) && 'false' == $assoc_args['notify'] ) ? false : true;

			// Are we doing all or individual?
			if ( isset( $assoc_args['all'] ) ) {
				if ( isset( $assoc_args['deactivated'] ) ) {
					WP_CLI::confirm( 'This will set all deactivated users as activated. Are you sure?' );
				} else {
					WP_CLI::confirm( 'This will set all pending users as activated. Are you sure?' );
				}
				$users = ( isset( $assoc_args['deactivated'] ) ) ? wpmem_get_deactivated_users() : wpmem_get_pending_users();
				foreach ( $users as $user ) {
					wpmem_activate_user( $user, $notify );
				}
				WP_CLI::success( 'Activated ' . count( $users ) . ' users.' );
				return;
			}

			// If doing an individual user (wpmem_cli_get_user() throws its own error if invalid).
			$user = wpmem_cli_get_user( $assoc_args );

			// Is the user already activated?
			if ( false == wpmem_is_user_activated($user->ID ) ) {
				// If valid user and not activated, activate.
				wpmem_activate_user( $user->ID, $notify );
				WP_CLI::success( 'User activated.' );
				if ( $notify ) {
					WP_CLI::success( 'Email notification sent to user.' );
				}
			} else {
				WP_CLI::error( 'User is already activated.' );
			}
		}

		/**
		 * CLI command to deactivate users.
		 *
		 * ## OPTIONS
		 *
		 * [--id=<user_id>]
		 * : Deactivate user by user ID.
		 * 
		 * [--login=<user_login>] 
		 * : Dectivate user by login (username).
		 * 
		 * [--email=<user_email>] 
		 * : Dectivate user by email.
		 * 
		 * [--all] 
		 * : Deactivates all activated users (automatically excludes admin role).
		 * 
		 * [--admin]
		 * : Include admin role accounts when deactivating all users.
		 *
		 * @since 3.3.5
		 */
		public function deactivate( $args, $assoc_args ) {

			if ( ! wpmem_is_enabled( 'mod_reg' ) ) {
				WP_CLI::error( 'Moderated registration is not enabled in WP-Members options.' );
			}

			if ( ! isset( $assoc_args['all'] ) ) {
				if ( ! isset( $assoc_args['id'] ) && ! isset( $assoc_args['login'] ) && ! isset( $assoc_args['email'] ) ) {
					WP_CLI::error( 'Missing required parameter. Specify user ID with --id=<user_id> or --all to deactivate all users.' );
				}
			}

			// Are we doing all or individual?
			if ( isset( $assoc_args['all'] ) ) {
				if ( isset( $assoc_args['admin'] ) ) {
					WP_CLI::confirm( 'This will set all activated users (including administrators) as deactivated. Are you sure?' );
				} else {
					WP_CLI::confirm( 'This will set all activated users as deactivated. Are you sure?' );
				}
				
				$users = wpmem_get_activated_users();
				$x = 0;
				foreach ( $users as $user ) {
					$user_roles = wpmem_get_user_role( $user, true ); // To make sure we don't deactivate admins unintentionally.
					if ( isset( $assoc_args['admin'] ) ) {
						wpmem_deactivate_user( $user );
						$x++;
					} else {
						if ( ! in_array( 'administrator', $user_roles ) ) {
							wpmem_deactivate_user( $user );
							$x++;
						}
					}
				}
				WP_CLI::success( 'Dectivated ' . $x . ' users.' );
				return;
			} 

			// If doing an individual user (wpmem_cli_get_user() throws its own error if invalid).
			$user = wpmem_cli_get_user( $assoc_args );
			wpmem_deactivate_user( $user->ID );
			WP_CLI::success( 'User deactivated.' );	
		}

		/**
		 * Lists users by activation state.
		 *
		 * ## OPTIONS
		 *
		 * <pending|activated|deactivated|confirmed|unconfirmed>
		 * : status of the user
		 *
		 * @subcommand list
		 *
		 * @since 3.3.5
		 */
		public function list_users( $args, $assoc_args ) {

			// Accepted list args.
			$accepted = array( 'pending', 'activated', 'deactivated', 'confirmed', 'unconfirmed' );

			$status = $args[0];
			switch ( $status ) {
				case 'pending':
					$users = wpmem_get_pending_users();
					break;
				case 'activated':
					$users = wpmem_get_activated_users();
					break;
				case 'deactivated':
					$users = wpmem_get_deactivated_users();
					break;
				case 'confirmed':
					$users = wpmem_get_confirmed_users();
					break;
				case 'unconfirmed':
					$users = wpmem_get_users_by_meta( '_wpmem_user_confirmed', false );
					break;
			}

			if ( ! empty( $users ) ) {
				foreach ( $users as $user_id ) {
					$user = get_userdata( $user_id );
					$list[] = array(
						'ID'       => $user->ID,
						'username' => $user->user_login,
						'email'    => $user->user_email,
						'status'   => $status,
					);
				}

				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'ID', 'username', 'email', 'status' ) );
				$formatter->display_items( $list );
			} else {
				WP_CLI::line( sprintf( 'Currently there are no %s users.', $status ) );
			}
		}

		/**
		 * Gets detail of requested user.
		 *
		 * ## OPTIONS
		 *
		 * [--id]
		 * : Get user by ID.
		 *
		 * [--login]
		 * : Get user by user login.
		 * 
		 * [--email]
		 * : Get user by user email.
		 * 
		 * [--all]
		 * : Gets all user meta.
		 *
		 * @subcommand get-user-by
		 * @since 3.3.5
		 * 
		 * @todo Needs debugging.
		 */
		public function detail( $args, $assoc_args ) {
			// is user by id, email, or login
			$user = wpmem_cli_get_user( $assoc_args );
			if ( ! $user ) {
				WP_CLI::error( 'User does not exist. Try wp user list' );
			}
			$all  = ( isset( $assoc_args['all'] ) ) ? true : false;
			$this->display_user_detail( $user, $all );
		}
		
		/**
		 * Manually set a user as confirmed.
		 *
		 * ## OPTIONS
		 *
		 * [--id=<user_id>]
		 * : Confirm user by user ID.
		 * 
		 * [--login=<user_login>] 
		 * : Confirm user by login (username).
		 * 
		 * [--email=<user_email>] 
		 * : Confirm user by email.
		 * 
		 * [--all] 
		 * : Marks all users as confirmed.
		 *
		 * @since 3.3.5
		 */
		public function confirm( $args, $assoc_args ) {
			if ( ! wpmem_is_enabled( 'act_link' ) ) {
				WP_CLI::error( 'User confirmation is not enabled in WP-Members options.' );
			}

			if ( ! isset( $assoc_args['all'] ) ) {
				if ( ! isset( $assoc_args['id'] ) && ! isset( $assoc_args['login'] ) && ! isset( $assoc_args['email'] ) ) {
					WP_CLI::error( 'Missing required parameter. Specify user ID with --id=<user_id> or --all to confirm all users.' );
				}
			}

			// Are we doing all or individual?
			if ( isset( $assoc_args['all'] ) ) {
				WP_CLI::confirm( 'This will set all unconfirmed users as confirmed. Are you sure?' );
				// Get all unconfirmed users.
				$users = wpmem_get_users_by_meta( '_wpmem_user_confirmed', false );
				if ( $users ) {
					foreach ( $users as $user ) {
						wpmem_set_user_as_confirmed( $user );
					}
					WP_CLI::success( 'Marked ' . count( $users ) . ' users as confirmed.' );
					return;
				} else {
					WP_CLI::error( 'There are no unconfirmed users' );
				}
			} else {
				// If doing an individual user (wpmem_cli_get_user() throws its own error if invalid).
				$user = wpmem_cli_get_user( $assoc_args );
				if ( $user ) {
					wpmem_set_user_as_confirmed( $user->ID );
					WP_CLI::success( 'User confirmed' );
				}
			}
		}

		/**
		 * Manually set a user as unconfirmed.
		 *
		 * ## OPTIONS
		 *
		 * [--id=<user_id>]
		 * : Unonfirm user by user ID.
		 * 
		 * [--login=<user_login>] 
		 * : Unonfirm user by login (username).
		 * 
		 * [--email=<user_email>] 
		 * : Unonfirm user by email.
		 * 
		 * [--all] 
		 * : Marks all users as unconfirmed.
		 *
		 * @since 3.3.5
		 */
		public function unconfirm( $args, $assoc_args ) {
			if ( ! wpmem_is_enabled( 'act_link' ) ) {
				WP_CLI::error( 'User confirmation is not enabled in WP-Members options.' );
			}

			if ( ! isset( $assoc_args['all'] ) ) {
				if ( ! isset( $assoc_args['id'] ) && ! isset( $assoc_args['login'] ) && ! isset( $assoc_args['email'] ) ) {
					WP_CLI::error( 'Missing required parameter. Specify user ID with --id=<user_id> or --all to confirm all users.' );
				}
			}

			// Are we doing all or individual?
			if ( isset( $assoc_args['all'] ) ) {
				WP_CLI::confirm( 'This will set all confirmed users as unconfirmed. Are you sure?' );
				$users = wpmem_get_confirmed_users();
				if ( $users ) {
					$x=0;
					foreach ( $users as $user ) {
						$user_roles = wpmem_get_user_role( $user, true ); // To make sure we don't deactivate admins unintentionally.
						if ( ! in_array( 'administrator', $user_roles ) ) {
							wpmem_set_user_as_unconfirmed( $user );
							$x++;
						}
					}
					WP_CLI::success( 'Marked ' . $x . ' users as unconfirmed.' );
					return;
				} else {
					WP_CLI::error( 'There are no confirmed users' );
				}
			} else {
				// If doing an individual user (wpmem_cli_get_user() throws its own error if invalid).
				$user = wpmem_cli_get_user( $assoc_args );
				if ( $user ) {
					wpmem_set_user_as_unconfirmed( $user->ID );
					WP_CLI::success( 'User unconfirmed' );
				}
			}
		}
		
		/**
		 * Gets the role of a user.
		 * 
		 * [--id=<user_id>]
		 * : The WP ID of the user.
		 * 
		 * [--login=<user_login>]
		 * : The WP username of the user.
		 * 
		 * [--email=<user_email>] 
		 * : The user email.
		 * 
		 * [--all] 
		 * : 
		 * 
		 * @subcommand get-role
		 */
		public function get_role( $args, $assoc_args ) {

			$user = wpmem_cli_get_user( $assoc_args );

			$all = ( isset( $assoc_args['all'] ) ) ? true : false;
			$role = wpmem_get_user_role( $user->ID,  $all );
			if ( is_array( $role ) ) {
				foreach ( $role as $r ) {
					$list[] = array(
						'roles' => $r,
					);
				}
				WP_CLI::line( 'Displaying all user roles for the following user:' );
				WP_CLI::line( 'Username' . ': ' . $user->user_login );
				WP_CLI::line( 'Email' . ': ' . $user->user_email );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'roles' ) );
				$formatter->display_items( $list );
			} else {
				WP_CLI::line( sprintf( 'User role: %s', $role ) );
			}
		}

		/**
		 * @subcommand set-membership
		 */
		public function set_membership( $args, $assoc_args ) {

			// is user by id, email, or login
			$user_by = ( isset( $assoc_args['user_by'] ) ) ? $assoc_args['user_by'] : 'login';
			$user = get_user_by( $user_by, $args[0] );
			if ( empty( $user ) || ! $user ) {
				WP_CLI::error( 'User does not exist. Try wp user list' );
			}

			$membership = $assoc_args['key'];

			$date = ( isset( $assoc_args['date'] ) ) ? $assoc_args['date'] : false;

			wpmem_set_user_membership( $membership, $user->ID, $date );

			WP_CLI::line( sprintf( 'Set %s membership for user %s', $membership, $user->user_login ) );
		}

		/**
		 * Handles user detail display.
		 *
		 * @since 3.3.5
		 *
		 * @param  object  $user
		 * @param          $all
		 */
		private function display_user_detail( $user, $all ) {

			WP_CLI::line( sprintf( 'User: %s', $user->user_email) );

			$values = wpmem_user_data( $user->ID, $all );
			foreach ( $values as $key => $meta ) {
				 $list[] = array(
					 'meta' => $key,
					 'value' => $meta,
				 );
			}

			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'meta', 'value' ) );

			$formatter->display_items( $list );
		}
	}
}
WP_CLI::add_command( 'mem user', 'WP_Members_CLI_User' );