<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_Memberships {

		function __construct() {
			// Need the admin api for some CLI commands.
			global $wpmem;
			require_once $wpmem->path . 'includes/admin/api.php';
		}

		/**
		 * CLI command to list memberships on the site.
		 * @since 3.5.3
		 */
		public function list( $args, $assoc_args ) {
            $memberships = wpmem_get_memberships();
			if ( empty( $memberships ) ) {
				WP_CLI::line( 'There are no memberships created for this site' );
			} else {
				foreach ( $memberships as $membership ) {
					 $list[] = array(
						 'name' => $membership['title'],
						 'meta (slug)' => $membership['name'],
					 );
				}

				WP_CLI::line( 'WP-Members memberships:' );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'name', 'meta (slug)' ) );
				$formatter->display_items( $list );
			}
		}

		/**
		 * Get membership counts.
		 *
		 * ## OPTIONS
		 *
		 * [<meta>]
		 * : The meta key (slug) of the membership to get data for (gets all memberships by default)
		 *
		 * [--type=<active|expired>]
		 * : Get only active or expired membership count.
		 * 
		 * [--all] 
		 * : Gets all membership count.
		 *
		 * @since 3.5.3
		 * @subcommand list-count
		 */
		public function list_count( $args, $assoc_args ) {

			if ( empty( $args ) && ! isset( $assoc_args['all'] ) && ! isset( $assoc_args['type'] ) ) {
				$count_type = "all";
			} else {
				if ( isset( $assoc_args['type'] ) ) {
					$count_type = $assoc_args['type'];
				} else {
					$count_type = "all";
				}
			}

			switch ( $count_type ) {

				case "all":

					$memberships = wpmem_get_memberships();
					if ( empty( $memberships ) ) {
						WP_CLI::error( 'No memberships exist for this site' );
					}

					// Is this "all" as in "all memberships" or "all" as in "all counts of an individual membership"?
					if ( isset( $args[0] ) ) {

						$membership = $memberships[ $args[0] ];

						$active_count  = wpmem_get_membership_count( $membership['name'], 'active'  );
						$expired_count = wpmem_get_membership_count( $membership['name'], 'expired' );
						$total_count   = wpmem_get_membership_count( $membership['name'] );

						WP_CLI::line( sprintf( 'Counts for "%s" membership:', $membership['title'] ) );
						WP_CLI::line( 'Active: ' . $active_count );
						WP_CLI::line( 'Expired: ' . $expired_count );
						WP_CLI::line( 'Total: ' . $total_count );

					} else {

						foreach ( $memberships as $membership ) {
							
							$active_count  = wpmem_get_membership_count( $membership['name'], 'active'  );
							$expired_count = wpmem_get_membership_count( $membership['name'], 'expired' );
							$total_count   = wpmem_get_membership_count( $membership['name'] );

							$list[] = array(
								'Membership' => $membership['title'],
								'Active' => $active_count,
								'Expired' => $expired_count,
								'Total' => $total_count
							);
						}
		
						WP_CLI::line( 'WP-Members membership counts:' );
						$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Membership', 'Active', 'Expired', 'Total' ) );
						$formatter->display_items( $list );
					}
					break;

				case "active":
				case "expired":

					$memberships = wpmem_get_memberships();

					if ( empty( $memberships ) ) {
						WP_CLI::error( 'No memberships exist for this site' );
					}

					$membership = $args[0];
					$type = ( "active" == $count_type ) ? "active" : "expired";
					$label = ( "active" == $count_type ) ? "Active" : "Expired";
					$count = wpmem_get_membership_count( $memberships[ $membership ]['name'], $type );
					WP_CLI::line( $label . ' "' . $memberships[ $membership ]['name'] . '" memberships: ' . $count );
					break;

			}
		}

		/**
		 * Adds a membership to a user.
		 * 
		 * ## OPTIONS
		 * 
		 * <membership_meta_key> 
		 * : The meta key (slug) of the membership to add to the user.
		 * 
		 * [--id=<user>]
		 * : The ID of the user to add the membership to.
		 * 
		 * [--login=<user>]
		 * : The login of the user to add the membership to.
		 * 
		 * [--email=<user>]
		 * : The email of the user to add the membership to.
		 * 
		 * [--date=<YYYY-MM-DD>] 
		 * : The expiration date (optional, if excluded, the default time period will be set).
		 * 
		 * @since 3.5.3
		 * @since 3.5.4 Added expiration date.
		 */
		public function add( $args, $assoc_args ) {
			$date = ( isset( $assoc_args['date'] ) ) ? $assoc_args['date'] : false;
			$user = wpmem_cli_get_user( $assoc_args );
			wpmem_set_user_membership( $args[0], $user->ID, $date );
			WP_CLI::success( sprintf( '%s membership added to %s', $args[0], $user->user_email ) );
		}

		/**
		 * Updates a membership for a user.
		 * 
		 * ## OPTIONS
		 * 
		 * <membership_meta_key> 
		 * : The meta key (slug) of the membership to update.
		 * 
		 * [--date=<YYYY-MM-DD>]
		 * : The expiration date (optional, if excluded, the default time period will be set).
		 * 
		 * [--id=<user>]
		 * : The ID of the user to update.
		 * 
		 * [--login=<user>]
		 * : The login of the user to udpate.
		 * 
		 * [--email=<user>]
		 * : The email of the user to update.
		 * 
		 * @since 3.5.3
		 */
		public function update( $args, $assoc_args ) {
			$user = wpmem_cli_get_user( $assoc_args );
			wpmem_set_user_membership( $args[0], $user->ID, $assoc_args['date'] );
			WP_CLI::success( sprintf( '%s membership update for %s', $args[0], $user->user_email ) );			
		}

		/**
		 * Removes a membership to a user.
		 * 
		 * ## OPTIONS
		 * 
		 * <membership_meta_key> 
		 * : The meta key (slug) of the membership to remove from the user.
		 * 
		 * [--id=<user>]
		 * : The ID of the user to remove the membership from.
		 * 
		 * [--login=<user>]
		 * : The login of the user to remove the membership from.
		 * 
		 * [--email=<user>]
		 * : The email of the user to remove the membership from.
		 * 
		 * @since 3.5.3
		 */
		public function remove( $args, $assoc_args ) {
			$user = wpmem_cli_get_user( $assoc_args );
			wpmem_remove_user_membership( $args[0], $user );
			WP_CLI::success( sprintf( '%s membership removed from %s', $args[0], $user->user_email ) );
		}

		/**
		 * Create a new membership.
		 * 
		 * ## OPTIONS
		 * 
		 * --title=<title>
		 * : The membership title (readable), use quotes if title has whitespace ("The Title").
		 * 
		 * --name=<meta>
		 * : The membership meta key.
		 * 
		 * [--status=<status>] 
		 * : Published status.
		 * ---
		 * default: publish
		 * options:
		 *    - publish
		 *    - draft
		 * ---
		 * 
		 * [--author=<user_id>] 
		 * : User ID of the author (defaults to site admin user ID).
		 * 
		 */
		public function create( $args, $assoc_args ) {

			/**
			 * 	wpmem_create_membership( $args ): {
			*     @type string $title      User readable name of membership.
			*     @type string $name       Sanitized title of the membership to be used as the meta key.
			*     @type string $status     Published status: publish|draft (default: publish)
			*     @type int    $author     User ID of membership author, Optional, defaults to site admin.
			*     @type array  $meta_input
			*         Meta fields for membership CPT (not all are required).
			* 
			*         @type string $name         The sanitized title of the membership.
			*         @type string $default
			*         @type string $role         Roles if a role is required.
			*         @type string $expires      Expiration period if used (num|per).
			*         @type int    $no_gap       If renewal is "no gap" renewal.
			*         @type string $fixed_period (start|end|grace_num|grace_per)
			*         @type int    $set_default_{$key}
			*         @type string $message      Custom message for restriction.
			*         @type int    $child_access If membership hierarchy is used.
			*     }
			 */

			$top_level_args = [ 'title', 'name', 'status', 'author' ];
			// @todo Will need some validation here so we don't blow things up.
			foreach ( $assoc_args as $key => $arg ) {
				if ( in_array( $key, $top_level_args ) ) {
					$mem_args[ $key ] = $arg;
				}
			}

			// @todo Assemble meta input
			
			$result = wpmem_create_membership( $mem_args );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( 'There was an error creating the membership.' );
			}
		}
	}
}
WP_CLI::add_command( 'mem membership', 'WP_Members_CLI_Memberships' );