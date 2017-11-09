<?php
/**
 * The WP_Members Products Admin Class.
 *
 * @package WP-Members
 * @subpackage WP_Members_Products_Admin
 * @since 3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Products_Admin {

	/**
	 * Class constructor.
	 *
	 * @since 3.2.0
	 */
	function __construct() {
		add_filter( 'manage_wpmem_mem_plan_posts_columns',       array( $this, 'columns_heading' ) );
		add_action( 'manage_wpmem_mem_plan_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		add_action( 'add_meta_boxes',                            array( $this, 'meta_boxes' ) );
		add_action( 'save_post',                                 array( $this, 'save_details' ) );
		add_action( 'wpmem_admin_after_block_meta',              array( $this, 'add_product_to_post' ), 10, 2 );
		add_action( 'wpmem_admin_block_meta_save',               array( $this, 'save_product_to_post' ), 10, 3 );
	}

	/**
	 * Column headings for list table.
	 *
	 * @since 3.2.0
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	function columns_heading( $columns ) {
		unset( $columns['date'] );
		$columns['slug']         = __( 'Slug', 'wp-members' );
		$columns['role']         = __( 'Role', 'wp-members' );
		$columns['expires']      = __( 'Expires', 'wp-members' );
		$columns['last_updated'] = __( 'Last updated', 'wp-members' );
		return $columns;
	}

	/**
	 * Column content for list table.
	 *
	 * @since 3.2.0
	 *
	 * @param  string $column_name
	 * @param  int    $post_id
	 */
	function columns_content( $column, $post_id ) {
		$post = get_post( $post_id );
		switch ( $column ) {
			case 'slug':
				echo $post->post_name;
				break;
			case 'role':
				$role = $this->get_meta( 'membership_product_role' );
				echo ( $role ) ? $role : __( 'No role required', 'wp-members' );
				break;
			case 'expires':
				$number = $this->get_meta( 'membership_product_number_of_periods' );
				$period = $this->get_meta( 'membership_product_time_period' );
				echo ( $number ) ? $number . ' ' . $period : __( 'Does not expire', 'wp-members' );
				break;
			case 'last_updated':
				echo date_i18n( get_option( 'date_format' ), strtotime( $post->post_modified ) );
				break;
		}
	}

	/**
	 * Gets value of requested post meta.
	 *
	 * @since 3.2.0
	 *
	 * @param  string $value
	 * @return string
	 */
	function get_meta( $value ) {
		global $post;
		$field = get_post_meta( $post->ID, $value, true );
		if ( ! empty( $field ) ) {
			return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
		} else {
			return false;
		}
	}

	/**
	 * Handles meta boxes for CPT editor.
	 *
	 * @since 3.2.0
	 */
	function meta_boxes() {
		remove_meta_box( 'slugdiv', 'wpmem_mem_plan', 'normal' );
		add_meta_box(
			'membership_product',
			__( 'Membership Product Details', 'wp-members' ),
			array( $this, 'details_html' ),
			'wpmem_mem_plan',
			'normal',
			'high'
		);
	}

	/**
	 * Outputs HTML for CPT editor.
	 *
	 * @since 3.2.0
	 *
	 * @param  object $post
	 */
	function details_html( $post ) { ?>
		<div class="inside">
			<?php wp_nonce_field( '_membership_product_nonce', 'membership_product_nonce' ); ?>
			<p class="form-field">
				<label for="membership_product_name_slug"><?php _e( 'Name (slug)', 'membership_product' ); ?></label>
				<input type="text" name="membership_product_name_slug" id="membership_product_name_slug" value="<?php echo esc_attr( $post->post_name ); ?>">
			</p>
			<p class="form-field">
				<input type="checkbox" name="membership_product_role_required" id="membership_product_role_required" value="role-required" <?php echo ( $this->get_meta( 'membership_product_role_required' ) === 'role-required' ) ? 'checked' : ''; ?>>
				<label for="membership_product_role_required"><?php _e( 'Role Required?', 'membership_product' ); ?></label>	</p>
			<p class="form-field">
				<label for="membership_product_role"><?php _e( 'Role', 'membership_product' ); ?></label>
				<select name="membership_product_role" id="membership_product_role">
					<option value=""><?php _e( 'No Role', 'wp-members' ); ?></option>
					<?php wp_dropdown_roles( $this->get_meta( 'membership_product_role' ) ); ?>
				</select>
			</p>
			<p class="form-field">
				<input type="checkbox" name="membership_product_expires" id="membership_product_expires" value="expires" <?php echo ( $this->get_meta( 'membership_product_expires' ) === 'expires' ) ? 'checked' : ''; ?>>
				<label for="membership_product_expires"><?php _e( 'Expires', 'membership_product' ); ?></label></p>
			<p class="form-field">
				<label for="membership_product_time_period"><?php _e( 'Time period', 'membership_product' ); ?></label>
				<input type="text" name="membership_product_time_period" id="membership_product_time_period" value="<?php echo $this->get_meta( 'membership_product_time_period' ); ?>">
			</p>
			<p class="form-field">
				<label for="membership_product_number_of_periods"><?php _e( 'Number of periods', 'membership_product' ); ?></label>
				<input type="text" name="membership_product_number_of_periods" id="membership_product_number_of_periods" value="<?php echo $this->get_meta( 'membership_product_number_of_periods' ); ?>">
			</p>
		</div><?php
	}

	/**
	 * Saves meta fields for CPT
	 *
	 * @since 3.2.0
	 *
	 * @param  int $post_id
	 */
	function save_details( $post_id ) {
		
		$settings = array(
			'membership_product_role_required',
			'membership_product_role',
			'membership_product_expires',
			'membership_product_time_period',
			'membership_product_number_of_periods',
			'membership_product_name_slug',
		);
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['membership_product_nonce'] ) || ! wp_verify_nonce( $_POST['membership_product_nonce'], '_membership_product_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		foreach ( $settings as $setting ) {
			$field = wpmem_get( $setting, null );
			update_post_meta( $post_id, $setting, esc_attr( $field ) );
		}
	}

	/**
	 * Add dropdown to post and page meta box for marking access by product..
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 * @param  object $post
	 * @param  string $block
	 */
	function add_product_to_post( $post, $block ) {
		global $wpmem;
		$product = get_post_meta( $post->ID, $wpmem->membership->post_meta, true );
		$product = ( $product ) ? $product : array();
		$values = array();
		foreach ( $wpmem->membership->products as $key => $value ) {
			$values[] = $value . '|' . $key;
		}
		echo wpmem_form_label( array( 
			'meta_key'=>'',
			'label'=>__( 'Limit access to:', 'wp-members' ),
			'type'=> 'multiselect'
		) );
		echo "<br />";
		echo wpmem_form_field( array( 
			'name' => $wpmem->membership->post_meta, 
			'type' => 'multiselect',
			'value' => $values,
			'compare' => $product,
		) );
	}

	/**
	 * Save custom post meta for access by product.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 * @param  object $post
	 * @param  string $block
	 * @param  string $unblock
	 */
	function save_product_to_post( $post, $block, $unblock ) {
		global $wpmem;
		$products = wpmem_get( $wpmem->membership->post_meta );
		if ( $products ) {
			update_post_meta( $post->ID, $wpmem->membership->post_meta, $products );
		} else {
			delete_post_meta( $post->ID, $wpmem->membership->post_meta );
		}
	}
	
}