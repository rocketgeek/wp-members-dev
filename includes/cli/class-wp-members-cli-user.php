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
		 * --id=<user_id>
		 * : The WP ID of the user to activate.
		 *
		 * [--notify=<boolean>]
		 * : Whether to send notifcation to user (true if omitted).
		 *
		 * @since 3.3.5
		 */
		public function activate( $args, $assoc_args ) {

			if ( wpmem_is_enabled( 'mod_reg' ) ) {
				$validation = $this->validate_user_id( $assoc_args['id'] );
			} else {
				WP_CLI::error( __( 'Moderated registration is not enabled in WP-Members options.', 'wp-members' ) );
			}

			if ( true == $validation ) {

				$notify  = ( isset( $assoc_args['notify'] ) && 'false' == $assoc_args['notify'] ) ? false : true;

				// Is the user already activated?
				if ( false == wpmem_is_user_activated( $assoc_args['id'] ) ) {

					wpmem_activate_user( $assoc_args['id'], $notify );
					WP_CLI::success( __( 'User activated.', 'wp-members' ) );
					if ( $notify ) {
						WP_CLI::success( __( 'Email notification sent to user.', 'wp-members' ) );
					}
				} else {
					WP_CLI::error( __( 'User is already activated.', 'wp-members' ) );
				}
			} else {
				WP_CLI::error( $validation );
			}
		}

		/**
		 * CLI command to deactivate users.
		 *
		 * ## OPTIONS
		 *
		 * --id=<user_id>
		 * : The WP ID of the user to deactivate.
		 *
		 * @since 3.3.5
		 */
		public function deactivate( $args, $assoc_args ) {

			if ( wpmem_is_enabled( 'mod_reg' ) ) {
				$validation = $this->validate_user_id( $assoc_args['id'] );
			} else {
				WP_CLI::error( __( 'Moderated registration is not enabled in WP-Members options.', 'wp-members' ) );
			}
			
			if ( true == $validation ) {
				wpmem_deactivate_user( $assoc_args['id'] );
				WP_CLI::success( __( 'User deactivated.', 'wp-members' ) );
			} else {
				WP_CLI::error( $validation );
			}		
		}

		/**
		 * Validates user info for activation.
		 *
		 * @since 3.3.5
		 */
		private function validate_user_id( $user_id ) {
			
			$user_id = ( isset( $user_id ) ) ? $user_id : false;

			if ( $user_id ) {
				// Is the user ID and actual user?
				if ( wpmem_is_user( $user_id ) ) {
					return true;
				} else {
					WP_CLI::error( __( 'Invalid user ID. Please specify a valid user. Try `wp user list`.', 'wp-members' ) );
				}
			} else {
				WP_CLI::error( __( 'No user id specified. Must specify user id as --id=123', 'wp-members' ) );
			}
		}

		/**
		 * Lists users by activation state.
		 *
		 * ## OPTIONS
		 *
		 * <pending|activated|deactivated>
		 * : status of the user
		 *
		 * @subcommand list
		 *
		 * @since 3.3.5
		 */
		public function list_users( $args, $assoc_args ) {

			// Accepted list args.
			$accepted = array( 'pending', 'activated', 'deactivated' );

			if ( ! in_array( $args[0], $accepted ) ) {
				/* translators: do not translate "pending|activated|deactivated", these are the command values */
				WP_CLI::error( __( 'Must include a user status from the following: pending|activated|deactivated', 'wp-members' ) );
			}

			switch ( $args[0] ) {
				case 'pending':
					$users = wpmem_get_pending_users();
					$status = 'pending';
					break;
				case 'activated':
					$users = wpmem_get_activated_users();
					$status = 'activated';
					break;
				case 'deactivated':
					$users = wpmem_get_deactivated_users();
					$status = 'deactivated';
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
				/* translators: %s is the placeholder for the user status value, do not remove it. */
				WP_CLI::line( sprintf( __( 'Currently there are no %s users.', 'wp-members' ), $status ) );
			}
		}

		/**
		 * Gets a list of pending users.
		 *
		 * @subcommand get-pending
		 * @since 3.3.5
		 */
		public function get_pending() {
			$this->list_users( array( 'pending' ), array() );
		}

		/**
		 * Gets a list of activated users.
		 *
		 * @subcommand get-activated
		 * @since 3.3.5
		 */
		public function get_activated() {
			$this->list_users( array( 'activated' ), array() );
		}

		/**
		 * Gets a list of deactivated users.
		 *
		 * @subcommand get-deactivated
		 * @since 3.3.5
		 */
		public function get_deactivated() {
			$this->list_users( array( 'deactivated' ), array() );
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
				WP_CLI::error( __( 'User does not exist. Try wp user list', 'wp-members' ) );
			}
			$all  = ( isset( $assoc_args['all'] ) ) ? true : false;
			$this->display_user_detail( $user, $all );
		}
		
		/**
		 * Manually set a user as confirmed.
		 *
		 * ## OPTIONS
		 *
		 * --id=<user_id>
		 * : The WP ID of the user to confirm.
		 *
		 * @since 3.3.5
		 */
		public function confirm( $args, $assoc_args ) {
			$validation = $this->validate_user_id( $assoc_args['id'] );
			if ( true == $validation ) {
				wpmem_set_user_as_confirmed( $assoc_args['id'] );
				WP_CLI::success( __( 'User confirmed', 'wp-members' ) );
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
				WP_CLI::line( __( 'Displaying all user roles for the following user:', 'wp-members' ) );
				WP_CLI::line( __( 'Username', 'wp-members' ) . ': ' . $user->user_login );
				WP_CLI::line( __( 'Email', 'wp-members' ) . ': ' . $user->user_email );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'roles' ) );
				$formatter->display_items( $list );
			} else {
				/* translators: %s is the placeholder for user role, do not remove it. */
				WP_CLI::line( sprintf( __( 'User role: %s', 'wp-members' ), $role ) );
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
				WP_CLI::error( __( 'User does not exist. Try wp user list', 'wp-members' ) );
			}

			$membership = $assoc_args['key'];

			$date = ( isset( $assoc_args['date'] ) ) ? $assoc_args['date'] : false;

			wpmem_set_user_membership( $membership, $user->ID, $date );

			/* translators: %s is the placeholder for membership and user id, do not remove it. */
			WP_CLI::line( sprintf( __( 'Set %s membership for user %s', 'wp-members' ), $membership, $user->user_login ) );
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
			/* translators: %s is the placeholder for the user email name, do not remove it. */
			WP_CLI::line( sprintf( __( 'User: %s', 'wp-members' ), $user->user_email) );

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