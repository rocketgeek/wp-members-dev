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


		/**
		 * @alias mem-from-csv
		 */
		public function mem_from_csv( $args, $assoc_args ) {

			// Set specific criteria.
			$membership_key = ( isset( $assoc_args['key'] ) ) ? $assoc_args['key'] : "import_membership";
			$expiration_key = ( isset( $assoc_args['exp'] ) ) ? $assoc_args['exp'] : "import_expires";

			$file_name = $args[0];
			$file_path = ( isset( $assoc_args['path'] ) ) ? ABSPATH . $assoc_args['path'] : ABSPATH . 'wp-content/uploads/';

			$file = fopen( trailingslashit( $file_path ) . $file_name, 'r' );
			while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
				//$line is an array of the csv elements
				$imports[] = $line;
			}
			fclose( $file ); 

			$meta_keys = array_shift( $imports );

			$x = 0;
			foreach ( $imports as $import ) {
				foreach ( $import as $key => $field ) {
					$keyed_values[ $meta_keys[ $key ] ] = $field;
				}

				// Do we have a field we can get the user by?
				if ( isset( $keyed_values['ID'] ) ) {
					$user_id = $keyed_values['ID'];
				} elseif ( isset( $keyed_values['user_login'] ) ) {
					$user = get_user_by( 'login', $keyed_values['user_login'] );
					$user_id = $user->ID;
				} elseif ( isset( $keyed_values['user_email'] ) ) {
					$user = get_user_by( 'email', $keyed_values['user_login'] );
					$user_id = $user->ID;
				}

				// Set specific criteria.
				$membership = ( isset( $keyed_values[ $membership_key ] ) ) ? $keyed_values[ $membership_key ] : false;
				$expiration = ( isset( $keyed_values[ $expiration_key ] ) ) ? $keyed_values[ $expiration_key ] : false;

				// Set expiration date - either "false" or MySQL timestamp.
				$date = ( $expiration ) ? date( "Y-m-d H:i:s", strtotime( $expiration ) ) : false;

				// Set user product access.
				if ( $membership ) {
					wpmem_set_user_product( $membership, $user_id, $date );
				}

				if ( isset( $assoc_args['verbose'] ) ) {
					/* translators: %s is the placeholder for the name of the membership, do not remove it. */
					WP_CLI::line( sprintf( __( 'Set %s membership for user %s', 'wp-members' ), $membership, $user_id ) );
					$list[] = array(
						'user ID' => $user_id,
						'membership' => $membership,
						'expires' => $date,
					);
				}
				$x++;
			}
			if ( isset( $assoc_args['verbose'] ) ) {
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'user ID', 'membership', 'expires' ) );
				$formatter->display_items( $list );
			}

			/* translators: %s is the placeholder for the number of memberships updated, do not remove it. */
			WP_CLI::success( sprintf( __( 'Imported memberships for %s users', 'wp-members' ), $x ) );

		}

	}

	WP_CLI::add_command( 'mem import', 'WP_Members_Import_CLI' );
}