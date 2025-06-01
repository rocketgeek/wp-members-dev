<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Manage WP-Members via WP-CLI
	 */
	class WP_Members_CLI {
		
		/**
		 * Object getter.
		 * 
		 * ## OPTIONS
		 *
		 * <what>
		 * : What to get? <block_val|status|hidden|>
		 *
		 * [--id=<post_id>]
		 * : Post ID
		 */
		public function get( $args, $assoc_args ) {
			
			$post_id = $assoc_args['id'];
			
			switch ( $args[0] ) {
				
				case "block_val":
					WP_CLI::line( 'post block setting:' . ' ' . wpmem_get_block_setting( $post_id ) );
					break;
					
				case "status":
					if ( false === get_post_status ( $post_id ) ) {
						/* translators: %s is the placeholder for the post ID, do not remove it. */
						WP_CLI::error( sprintf( 'No post id %d exists. Try wp post list', $post_id ) );
					}
					if ( true === wpmem_is_hidden( $post_id ) ) {
						/* translators: %s is the placeholder for the post ID, do not remove it. */
						$line = sprintf( 'post %s is hidden', $post_id );
					} else {
						/* translators: %s is the placeholder for the post ID, do not remove it. */
						$line = ( wpmem_is_blocked( $post_id ) ) ? sprintf( 'post %s is blocked', $post_id ) : sprintf( 'post %s is not blocked', $post_id );
					}
					WP_CLI::line( $line );
					break;
					
				case "hidden":
					$hidden_posts = wpmem_get_hidden_posts();

					if ( empty( $hidden_posts ) ) {
						WP_CLI::line( 'There are no hidden posts' );
					} else {
						foreach ( $hidden_posts as $post_id ) {
							 $list[] = array(
								 'id' => $post_id,
								 'title' => get_the_title( $post_id ),
								 'url' => get_permalink( $post_id ),
							 );
						}

						WP_CLI::line( 'WP-Members hidden posts:' );
						$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'id', 'title', 'url' ) );
						$formatter->display_items( $list );
					}
					break;
			}	
		}

		/**
		 * Sets post properites.
		 *
		 * ## OPTIONS
		 *
		 * <what>
		 * : What to set (status).
		 *
		 * [--id=<post_id>]
		 * : Post ID to set property for.
		 *
		 * [--status=<unblock|unrestrict|hide|block|restrict>]
		 * : The status to set.
		 *
		 * @since 3.3.5
		 */
		public function set( $args, $assoc_args ) {
			
			$post_id = $assoc_args['post_id'];
			
			switch( $args[0] ) {
				
				case 'status':
					switch( $assoc_args['status'] ) {
						case 'unblock':
						case 'unrestrict':
							$val = 0; $line = 'unrestricted';
							break;
						case 'hide':
							$val = 2; $line = 'hidden';
							break;
						case 'block':
						case 'restrict':
						default;
							$val = 1; $line = 'restricted';
							break;
					}
					update_post_meta( $post_id, '_wpmem_block', $val );
					/* translators: %s is a placeholder, do not remove it. */
					WP_CLI::line( sprintf( 'Set post id %s as %s', $post_id, $line ) );
					break;
			}
		}
		
		/**
		 * Refreshes the hidden post array.
		 *
		 * @since 3.3.5
		 * 
		 * @alias refresh-hidden
		 */
		public function refresh_hidden() {
			wpmem_update_hidden_posts();
			WP_CLI::success( 'Hidden posts refreshed' );
		}
	}

    WP_CLI::add_command( 'mem', 'WP_Members_CLI' );

	// Load all subcommands
	require_once 'class-wp-members-cli-import.php';
	require_once 'class-wp-members-cli-memberships.php';
	require_once 'class-wp-members-cli-settings.php';
	require_once 'class-wp-members-cli-user.php';
}