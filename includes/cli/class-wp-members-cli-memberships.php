<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_Memberships {

		/**
		 * CLI command to list memberships on the site.
		 * @since 3.5.3
		 */
		public function list( $args, $assoc_args ) {
            $memberships = wpmem_get_memberships();
			if ( empty( $memberships ) ) {
				WP_CLI::line( __( 'There are no memberships created for this site', 'wp-members' ) );
			} else {
				foreach ( $memberships as $membership ) {
					 $list[] = array(
						 'name' => $membership['title'],
						 'slug' => $membership['name'],
					 );
				}

				WP_CLI::line( __( 'WP-Members memberships:', 'wp-members' ) );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'name', 'slug' ) );
				$formatter->display_items( $list );
			}
		}

		/**
		 * Get membership counts
		 * 
		 * @alias list-count
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
						WP_CLI::error( __( 'No memberships exist for this site', 'wp-members' ) );
					}

					// Is this "all" as in "all memberships" or "all" as in "all counts of an individual membership"?
					if ( isset( $args[0] ) ) {

						$membership = $memberships[ $args[0] ];

						$active_count  = wpmem_get_membership_count( $membership['name'], 'active'  );
						$expired_count = wpmem_get_membership_count( $membership['name'], 'expired' );
						$total_count   = wpmem_get_membership_count( $membership['name'] );

						/* translators: %s is the placeholder for the name of the membership, do not remove it. */
						WP_CLI::line( sprintf( __( 'Counts for "%s" membership:', 'wp-members' ), $membership['title'] ) );
						WP_CLI::line( __( 'Active: ', 'wp-members' ) . $active_count );
						WP_CLI::line( __( 'Expired: ', 'wp-members' ) . $expired_count );
						WP_CLI::line( __( 'Total: ', 'wp-members' ) . $total_count );

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
		
						WP_CLI::line( __( 'WP-Members membership counts:', 'wp-members' ) );
						$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Membership', 'Active', 'Expired', 'Total' ) );
						$formatter->display_items( $list );
					}
					break;

				case "active":
				case "expired":

					$memberships = wpmem_get_memberships();

					if ( empty( $memberships ) ) {
						WP_CLI::error( __( 'No memberships exist for this site', 'wp-members' ) );
					}

					$membership = $args[0];
					$type = ( "active" == $count_type ) ? "active" : "expired";
					$label = ( "active" == $count_type ) ? "Active" : "Expired";
					$count = wpmem_get_membership_count( $memberships[ $membership ]['name'], $type );
					WP_CLI::line( $label . ' "' . $memberships[ $membership ]['name'] . '" memberships: ' . $count );
					break;

			}
		}

		public function list_active( $args, $assoc_args ) {
			
			
		}

		public function add( $args, $assoc_args ) {
			$membership = $args[0];
			$user_id = $this->get_user_id( $assoc_args );

			wpmem_set_user_membership( $membership, $user_id );

			WP_CLI::line( sprintf( __( '%s membership removed from %s', 'wp-members' ), $membership, $user_id ) );
		}

		public function remove( $args, $assoc_args ) {
			$membership = $args[0];
			$user_id = $this->get_user_id( $assoc_args );

			wpmem_remove_user_membership( $membership, $user_id );

			WP_CLI::line( sprintf( __( '%s membership removed from %s', 'wp-members' ), $membership, $user_id ) );
		}

		private function get_user_id( $assoc_args ) {
			if ( isset( $assoc_args['id'] ) ) {
				$user_id = $assoc_args['id'];
			} elseif ( isset( $assoc_args['login'] ) ) {
				$user = get_user_by( 'login', $assoc_args['login'] );
				$user_id = $user->ID;
			} elseif ( isset( $assoc_args['email'] ) ) {
				$user = get_user_by( 'email', $assoc_args['email'] );
				$user_id = $user->ID;
			} else {
				WP_CLI::error( __( 'No valid user data from inputs', 'wp-members' ) );
			}
			return $user_id;
		}
	}
}
WP_CLI::add_command( 'mem membership', 'WP_Members_CLI_Memberships' );