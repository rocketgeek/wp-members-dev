<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Manage WP-Members via WP-CLI
	 */
	class WP_Members_CLI {
		
		/**
		 * Gets that status or block value of a post or post setting.
		 * 
		 * ## OPTIONS
		 *
		 * <block_val|status|hidden>
		 * : What to get? 
		 *
		 * --id=<post_id|post_slug>
		 * : Post ID
		 */
		public function get( $args, $assoc_args ) {

			if ( is_numeric( $assoc_args['id'] ) ) {
				$post_id = $assoc_args['id'];
			} else {
				$post = get_page_by_path( $assoc_args['id'], OBJECT );
				if ( $post ) {
					$post_id = $post->ID;
				} else {
					WP_CLI::error( $assoc_args['id'] . ' is not a valid post' );
				}
			}
			
			switch ( $args[0] ) {
				
				case "block_val":
					$block_setting = wpmem_get_block_setting( $post_id );
					switch( $block_setting ) {
						case '1':
							$block_text = 'restricted';
							break;
						case '2':
							$block_text = 'hidden';
							break;
						case '0':
						case false:
							$block_text = 'unrestricted';
							break;
					}
					WP_CLI::line( 'post restriction setting:' . ' ' . $block_text );
					break;
					
				case "status":
					if ( false == get_post_status( $post_id ) ) {
						WP_CLI::error( sprintf( 'No post id %d exists. Try wp post list', $post_id ) );
					}
					if ( true == wpmem_is_hidden( $post_id ) ) {
						$line = sprintf( 'post %s is hidden', $post_id );
					} else {
						$line = ( wpmem_is_blocked( $post_id ) ) ? sprintf( 'post %s is restricted', $post_id ) : sprintf( 'post %s is not restricted', $post_id );
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
		 * --id=<post_id>
		 * : Post ID to set property for.
		 *
		 * --status=<unblock|unrestrict|hide|block|restrict>
		 * : The status to set.
		 * ---
		 * options:
		 *    - restrict
		 *    - block
		 *    - unrestrict
		 *    - unblock
		 *    - hide
		 *
		 * @since 3.3.5
		 */
		public function set( $args, $assoc_args ) {
			
			$post_id = $assoc_args['post_id'];
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
			WP_CLI::line( sprintf( 'Set post id %s as %s', $post_id, $line ) );
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