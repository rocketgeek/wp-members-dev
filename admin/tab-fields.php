<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the fields tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 */


/**
 * Builds the fields panel.
 *
 * @since 2.2.2
 *
 * @param  string $wpmem_fields deprecated in 2.8.0
 * @global string $add_field_err_msg The fields error message
 */
function wpmem_a_build_fields() {

	global $wpmem, $add_field_err_msg;
	$add_toggle = ( isset( $_GET['edit'] ) ) ? $_GET['edit'] : false;
	$wpmem_fields = wpmem_fields();
	?>
	<div class="metabox-holder">

		<div id="post-body">
			<div id="post-body-content">
			<?php if ( $add_toggle && ( isset( $_POST['wpmem_admin_a'] ) != 'edit_field' ) ) {
				wpmem_a_field_edit( 'edit', $wpmem_fields, $add_toggle );
			 } else {
				if ( ! $add_field_err_msg ) { 
					wpmem_a_field_table( $wpmem_fields );
				}
				wpmem_a_field_edit( 'add' );
			} ?>

				<div class="postbox">
					<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<strong><i>See the <a href="http://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/fields/" target="_blank">Users Guide on the field manager</a>.</i></strong>
					</div>
				</div>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->

	</div><!-- .metabox-holder -->
	<?php
}


/**
 * reorders the fields on DnD
 *
 * @since 2.5.1
 */
function wpmem_a_field_reorder() {

	// Start fresh.
	$new_order = $wpmem_old_fields = $wpmem_new_fields = $key = $row = '';

	$new_order = $_REQUEST['orderstring'];
	$new_order = explode( "&", $new_order );
	
	// Loop through $new_order to create new field array.
	$wpmem_old_fields = get_option( 'wpmembers_fields' );
	for ( $row = 0; $row < count( $new_order ); $row++ )  {
		if ( $row > 0 ) {
			$key = $new_order[ $row ];
			$key = substr( $key, 15 );
			if ( $key ) {
				for ( $x = 0; $x < count( $wpmem_old_fields ); $x++ ) {
					if ( $wpmem_old_fields[ $x ][2] == $key ) {
						$wpmem_new_fields[ $row - 1 ] = $wpmem_old_fields[ $x ];
					}
				}
			}
		}
	}

	update_option( 'wpmembers_fields', $wpmem_new_fields ); 

	die(); // This is required to return a proper result.

}


/**
 * Updates fields.
 *
 * @since 2.8
 *
 * @param  string $action The field update action (update_fields|add|edit)
 * @global string $add_field_err_msg The add field error message
 * @return string $did_update The fields update message
 *
 * @todo   apply some additional form validation to the add/update process
 */
function wpmem_update_fields( $action ) {

	// Get the current fields.
	$wpmem_fields    = get_option( 'wpmembers_fields' );
	$wpmem_ut_fields = get_option( 'wpmembers_utfields' );

	if ( $action == 'update_fields' ) {

		// Check nonce.
		check_admin_referer( 'wpmem-update-fields' );

		// @todo - need some additional form validation here
		
		// Update user table fields.
		$arr = ( isset( $_POST['ut_fields'] ) ) ? $_POST['ut_fields'] : '';
		update_option( 'wpmembers_utfields', $arr );

		// Rebuild the array, don't touch user_email - it's always mandatory.
		$nrow = 0;
		foreach ( $wpmem_fields as $field ) {
			
			$meta_key = $field[2];

			// Check to see if the field is checked for deletion, and if not, add it to the new array.
			$delete_field = "del_" . $meta_key;
			$delete_field = ( isset( $_POST[ $delete_field ] ) ) ? $_POST[ $delete_field ] : false; 
			if ( $delete_field != "delete" ) {

				for ( $i = 0; $i < 4; $i++ ) {
					$wpmem_newfields[ $nrow ][ $i ] = $field[ $i ];
				}

				$wpmem_newfields[ $nrow ][0] = $nrow + 1;

				$display_field = $meta_key . "_display"; 
				$require_field = $meta_key . "_required";
				$checked_field = $meta_key . "_checked";

				if ( $field[2] != 'user_email' ){
					$wpmem_newfields[ $nrow ][4] = ( isset( $_POST[ $display_field ] ) ) ? 'y' : '';
					$wpmem_newfields[ $nrow ][5] = ( isset( $_POST[ $require_field ] ) ) ? 'y' : '';
				} else {
					$wpmem_newfields[ $nrow ][4] = 'y';
					$wpmem_newfields[ $nrow ][5] = 'y';
				}
				
				$wpmem_newfields[ $nrow ][6] = $field[6];
				$wpmem_newfields[ $nrow ][7] = ( isset( $field[7] ) ) ? $field[7] : '';
				if ( $field[3] == 'checkbox' ) { 
					if ( isset( $_POST[ $checked_field ] ) && $_POST[ $checked_field ] == 'y' ) {
						$wpmem_newfields[ $nrow ][8] = 'y';
					} else {
						$wpmem_newfields[ $nrow ][8] = 'n';
					}
				}

				$nrow = $nrow + 1;
			}

		}
		
		update_option( 'wpmembers_fields', $wpmem_newfields );
		$did_update = __( 'WP-Members fields were updated', 'wp-members' );

	} elseif ( $action == 'add_field' || 'edit_field' ) {

		// Check nonce.
		check_admin_referer( 'wpmem-add-fields' );

		global $add_field_err_msg;

		$add_field_err_msg = false;

		// Error check that field label and option name are included and unique.
		$add_field_err_msg = ( $_POST['add_name']   == '' ) ? __( 'Field Label is required for adding a new field. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
		$add_field_err_msg = ( $_POST['add_option'] == '' ) ? __( 'Option Name is required for adding a new field. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;

		$add_field_err_msg = ( !preg_match("/^[A-Za-z0-9_]*$/", $_POST['add_option'] ) ) ? __( 'Option Name must contain only letters, numbers, and underscores', 'wp-members' ) : $add_field_err_msg;

		// Check for duplicate field names.
		$chk_fields = array();
		foreach ( $wpmem_fields as $field ) {
			$chk_fields[] = $field[2];
		}
		$add_field_err_msg = ( in_array( $_POST['add_option'], $chk_fields ) ) ? __( 'A field with that option name already exists', 'wp-members' ) : $add_field_err_msg;
		
		// Error check for reserved terms.
		$reserved_terms = wpmem_wp_reserved_terms();
		$submitted_meta = $_POST['add_option'];
		if ( in_array( strtolower( $submitted_meta ), $reserved_terms ) ) {
			$add_field_err_msg = sprintf( __( 'Sorry, "%s" is a <a href="https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms" target="_blank">reserved term</a>. Field was not added.', 'wp-members' ), $submitted_term );
		}

		// Error check option name for spaces and replace with underscores.
		$us_option = preg_replace( "/ /", '_', $submitted_meta );

		$arr = array();
		
		$type = $_POST['add_type'];

		$arr[0] = ( $action == 'add_field' ) ? ( count( $wpmem_fields ) ) + 2 : false;
		$arr[1] = stripslashes( $_POST['add_name'] );
		$arr[2] = $us_option;
		$arr[3] = $type;
		$arr[4] = ( isset( $_POST['add_display'] ) )  ? $_POST['add_display']  : 'n';
		$arr[5] = ( isset( $_POST['add_required'] ) ) ? $_POST['add_required'] : 'n';
		$arr[6] = ( $us_option == 'user_nicename' || $us_option == 'display_name' || $us_option == 'nickname' ) ? 'y' : 'n';

		if ( $type == 'checkbox' ) { 
			$add_field_err_msg = ( ! $_POST['add_checked_value'] ) ? __( 'Checked value is required for checkboxes. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
			$arr[7] = ( isset( $_POST['add_checked_value'] ) )   ? $_POST['add_checked_value']   : false;
			$arr[8] = ( isset( $_POST['add_checked_default'] ) ) ? $_POST['add_checked_default'] : 'n';
		}

		if (   $type == 'select' 
			|| $type == 'multiselect' 
			|| $type == 'radio'
			|| $type == 'multicheckbox' 
		) {
			// Get the values.
			$str = stripslashes( $_POST['add_dropdown_value'] );
			// Remove linebreaks.
			$str = trim( str_replace( array("\r", "\r\n", "\n"), '', $str ) );
			// Create array.
			if ( ! function_exists( 'str_getcsv' ) ) {
				$arr[7] = explode( ',', $str );
			} else {
				$arr[7] = str_getcsv( $str, ',', '"' );
			}
			// If multiselect or multicheckbox, set delimiter.
			if ( 'multiselect' == $type || 'multicheckbox' == $type ) {
				$arr[8] = ( isset( $_POST['add_delimiter_value'] ) ) ? $_POST['add_delimiter_value'] : '|';
			}
		}
		
		if ( $type == 'file' || $type == 'image' ) {
			$arr[7] = stripslashes( $_POST['add_file_value'] );
		}
		
		if ( $_POST['add_type'] == 'hidden' ) { 
			$add_field_err_msg = ( ! $_POST['add_hidden_value'] ) ? __( 'A value is required for hidden fields. Nothing was updated.', 'wp-members' ) : $add_field_err_msg;
			$arr[7] = ( isset( $_POST['add_hidden_value'] ) )   ? stripslashes( $_POST['add_hidden_value'] ) : '';
		}

		if ( $action == 'add_field' ) {
			if ( ! $add_field_err_msg ) {
				array_push( $wpmem_fields, $arr );
				update_option( 'wpmembers_fields', $wpmem_fields );
				$did_update = $_POST['add_name'] . ' ' . __( 'field was added', 'wp-members' );
			} else {
				$did_update = $add_field_err_msg;
			}
		} else {

			for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
				if ( $wpmem_fields[ $row ][2] == $_GET['edit'] ) {
					$arr[0] = $wpmem_fields[ $row ][0];
					//$x = ( $arr[3] == 'checkbox' ) ? 8 : ( ( $arr[3] == 'select' || $arr[3] == 'file' ) ? 7 : 6 );
					for ( $r = 0; $r < count( $arr ); $r++ ) {
						$wpmem_fields[ $row ][ $r ] = $arr[ $r ];
					}
				}
			}

			update_option( 'wpmembers_fields', $wpmem_fields );
			
			$did_update = $_POST['add_name'] . ' ' . __( 'field was updated', 'wp-members' );
			
		}
	}

	if ( WPMEM_DEBUG == true && isset( $arr ) ) { echo "<pre>"; print_r($arr); echo "</pre>"; }

	global $wpmem;
	$wpmem->load_fields();
	
	return $did_update;
}


/**
 * Function to write the field edit link.
 *
 * @since 2.8
 *
 * @param string $field_id The option name of the field to be edited
 */
function wpmem_fields_edit_link( $field_id ) {
	return '<a href="' . add_query_arg( array( 'page' => 'wpmem-settings', 'tab' => 'fields', 'edit' => $field_id ), get_admin_url() . 'options-general.php' ) . '">' . __( 'Edit' ) . '</a>';
}


/**
 * Function to dispay the add/edit field form.
 *
 * @since 2.8
 *
 * @param string      $mode The mode for the function (edit|add)
 * @param array|null  $wpmem_fields the array of fields
 * @param string|null $field the field being edited
 */
function wpmem_a_field_edit( $mode, $wpmem_fields = null, $meta_key = null ) {

	global $wpmem;

	if ( $mode == 'edit' ) {
		$fields = wpmem_fields();
		$field  = $fields[ $meta_key ];	
	}

	$form_action = ( $mode == 'edit' ) ? 'editfieldform' : 'addfieldform';

?>
	<div class="postbox">
		<h3 class="title"><?php ( $mode == 'edit' ) ? _e( 'Edit Field', 'wp-members' ) : _e( 'Add a Field', 'wp-members' ); ?></h3>
		<div class="inside">
			<form name="<?php echo $form_action; ?>" id="<?php echo $form_action; ?>" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
				<?php wp_nonce_field( 'wpmem-add-fields' ); ?>
				<ul>
					<li>
						<label><?php _e( 'Field Label', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
						<input type="text" name="add_name" value="<?php echo ( $mode == 'edit' ) ? $field['label'] : false; ?>" />
						<?php _e( 'The name of the field as it will be displayed to the user.', 'wp-members' ); ?>
					</li>
					<li>
						<label><?php _e( 'Meta Key', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
						<?php if ( $mode == 'edit' ) { 
							echo $meta_key; ?>
							<input type="hidden" name="add_option" value="<?php echo $meta_key; ?>" /> 
						<?php } else { ?>
							<input type="text" name="add_option" value="" />
							<?php _e( 'The database meta value for the field. It must be unique and contain no spaces (underscores are ok).', 'wp-members' ); ?>
						<?php } ?>
					</li>
					<li>
						<label><?php _e( 'Field Type', 'wp-members' ); ?></label>
						<?php if ( $mode == 'edit' ) {
							echo $field['type']; ?>
							<input type="hidden" name="add_type" value="<?php echo $field['type']; ?>" /> 							
						<?php } else { ?>
							<select name="add_type" id="wpmem_field_type_select">
                                <option value="text"><?php     _e( 'text',        'wp-members' ); ?></option>
                                <option value="email"><?php    _e( 'email',       'wp-members' ); ?></option>
                                <option value="textarea"><?php _e( 'textarea',    'wp-members' ); ?></option>
                                <option value="checkbox"><?php _e( 'checkbox',    'wp-members' ); ?></option>
                                <option value="multicheckbox"><?php _e( 'multiple checkbox', 'wp-members' ); ?></option>
                                <option value="select"><?php   _e( 'select (dropdown)',    'wp-members' ); ?></option>
                                <option value="multiselect"><?php   _e( 'multiple select', 'wp-members' ); ?></option>
                                <option value="radio"><?php    _e( 'radio group', 'wp-members' ); ?></option>
                                <option value="password"><?php _e( 'password',    'wp-members' ); ?></option>
                                <option value="image"><?php    _e( 'image',       'wp-members' ); ?></option>
                                <option value="file"><?php     _e( 'file',        'wp-members' ); ?></option>
                                <option value="url"><?php      _e( 'url',         'wp-members' ); ?></option>
                                <option value="hidden"><?php   _e( 'hidden',      'wp-members' ); ?></option>
							</select>
						<?php } ?>
					</li>
					<li>
						<label><?php _e( 'Display?', 'wp-members' ); ?></label>
						<input type="checkbox" name="add_display" value="y" <?php echo ( $mode == 'edit' ) ? checked( true, $field['register'] ) : false; ?> />
					</li>
					<li>
						<label><?php _e( 'Required?', 'wp-members' ); ?></label>
						<input type="checkbox" name="add_required" value="y" <?php echo ( $mode == 'edit' ) ? checked( true, $field['required'] ) : false; ?> />
					</li>
				<?php if ( $mode == 'add' || ( $mode == 'edit' && ( $field['type'] == 'file' || $field['type'] == 'image' ) ) ) { ?>
				<?php echo ( $mode == 'add' ) ? '<div id="wpmem_file_info">' : ''; ?>
					<li>
						<strong><?php _e( 'Additional information for field upload fields', 'wp-members' ); ?></strong>
					</li>
					<li>
						<label><?php _e( 'Accepted file types:', 'wp-members' ); ?></label>
						<input type="text" name="add_file_value" value="<?php echo ( $mode == 'edit' && ( $field['type'] == 'file' || $field['type'] == 'image' ) ) ? $field['file_types'] : false; ?>" />
					</li>
					<li>
						<label>&nbsp;</label>
						<span class="description"><?php _e( 'Accepted file types should be set like this: jpg|jpeg|png|gif', 'wp-members' ); ?></span>
					</li>
				<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
				<?php } ?>
				<?php if ( $mode == 'add' || ( $mode == 'edit' && $field['type'] == 'checkbox' ) ) { ?>
				<?php echo ( $mode == 'add' ) ? '<div id="wpmem_checkbox_info">' : ''; ?>
					<li>
						<label><?php _e( 'Checked by default?', 'wp-members' ); ?></label>
						<input type="checkbox" name="add_checked_default" value="y" <?php echo ( $mode == 'edit' && $field['type'] == 'checkbox' ) ? checked( true, $field['checked_default'] ) : false; ?> />
					</li>
					<li>
						<label><?php _e( 'Stored value if checked:', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
						<input type="text" name="add_checked_value" value="<?php echo ( $mode == 'edit' && $field['type'] == 'checkbox' ) ? $field['checked_value'] : false; ?>" class="small-text" />
					</li>
				<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
				<?php } 
				
				if ( isset( $field['type'] ) ) {
					$additional_settings = ( $field['type'] == 'select' || $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' || $field['type'] == 'radio' ) ? true : false;
					$delimiter_settings  = ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) ? true : false;
				}
				if ( $mode == 'add' || ( $mode == 'edit' && $additional_settings ) ) { ?>
				<?php echo ( $mode == 'add' ) ? '<div id="wpmem_dropdown_info">' : ''; ?>
                    <?php if ( $mode == 'add' || ( $mode == 'edit' && $delimiter_settings ) ) {
                    echo ( $mode == 'add' ) ? '<div id="wpmem_delimiter_info">' : ''; 
					if ( isset( $field['delimiter'] ) && ( "|" == $field['delimiter'] || "," == $field['delimiter'] ) ) {
						$delimiter = $field['delimiter'];
					} else {
						$delimiter = "|";
					}
					?>
                    <li>
						<label><?php _e( 'Stored values delimiter:', 'wp-members' ); ?></label>
						<select name = "add_delimiter_value">
                        	<option value="|" <?php selected( '|', $delimiter ); ?>>pipe "|"</option>
                            <option value="," <?php selected( ',', $delimiter ); ?>>comma ","</option>
                        </select>
                    </li>
                    <?php echo ( $mode == 'add' ) ? '</div>' : '';
                    } ?>
					<li>
						<label style="vertical-align:top"><?php _e( 'Values (Displayed|Stored):', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
						<textarea name="add_dropdown_value" rows="5" cols="40"><?php
// Accomodate editing the current dropdown values or create dropdown value example.
if ( $mode == 'edit' ) {
for ( $row = 0; $row < count( $field['values'] ); $row++ ) {
// If the row contains commas (i.e. 1,000-10,000), wrap in double quotes.
if ( strstr( $field['values'][ $row ], ',' ) ) {
echo '"' . $field['values'][ $row ]; echo ( $row == count( $field['values'] )- 1  ) ? '"' : "\",\n";
} else {
echo $field['values'][ $row ]; echo ( $row == count( $field['values'] )- 1  ) ? "" : ",\n";
} }
						} else {
							if (version_compare(PHP_VERSION, '5.3.0') >= 0) { ?>
<---- Select One ---->|,
Choice One|choice_one,
"1,000|one_thousand",
"1,000-10,000|1,000-10,000",
Last Row|last_row<?php } else { ?>
<---- Select One ---->|,
Choice One|choice_one,
Choice 2|choice_two,
Last Row|last_row<?php } } ?></textarea>
					</li>
					<li>
						<label>&nbsp;</label>
						<span class="description"><?php _e( 'Options should be Option Name|option_value,', 'wp-members' ); ?></span>
					</li>
					<li>
						<label>&nbsp;</label>
						<span class="description"><a href="http://rocketgeek.com/plugins/wp-members/users-guide/registration/choosing-fields/" target="_blank"><?php _e( 'Visit plugin site for more information', 'wp-members' ); ?></a></span>
					</li>
				<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
				<?php } ?>
				<?php if ( $mode == 'add' || ( $mode == 'edit' && $field['type'] == 'hidden' ) ) { ?>
				<?php echo ( $mode == 'add' ) ? '<div id="wpmem_hidden_info">' : ''; ?>
					<li>
						<label><?php _e( 'Value', 'wp-members' ); ?> <span class="req"><?php _e( '(required)', 'wp-members' ); ?></span></label>
						<input type="text" name="add_hidden_value" value="<?php echo ( $mode == 'edit' && $field['type'] == 'hidden' ) ? $field['value'] : ''; ?>" />
					</li>
				<?php echo ( $mode == 'add' ) ? '</div>' : ''; ?>
				<?php } ?>
				</ul><br />
				<?php if ( $mode == 'edit' ) { ?><input type="hidden" name="field_arr" value="<?php echo $meta_key; ?>" /><?php } ?>
				<input type="hidden" name="wpmem_admin_a" value="<?php echo ( $mode == 'edit' ) ? 'edit_field' : 'add_field'; ?>" />
				<?php $text = ( $mode == 'edit' ) ? __( 'Edit Field', 'wp-members' ) : __( 'Add Field', 'wp-members' ); ?>
				<?php submit_button( $text ); ?>
			</form>
		</div>
	</div>
<?php
}



/**
 * Function to display the table of fields in the field manager tab.
 * 
 * @since 2.8.0
 *
 * @global object $wpmem
 * @param  array  $wpmem_fields The array of fields.
 */
function wpmem_a_field_table( $wpmem_fields ) {

	global $wpmem; 
	
	$heading_array = array(
		__( 'Add/Delete',   'wp-members' ),
		__( 'Field Label',  'wp-members' ),
		__( 'Meta Key',     'wp-members' ),
		__( 'Field Type',   'wp-members' ),
		__( 'Display?',     'wp-members' ),
		__( 'Required?',    'wp-members' ),
		__( 'Checked?',     'wp-members' ),
		__( 'Edit'                       ),
		__( 'Users Screen', 'wp-members' ),
	); ?>
	<div class="postbox">
		<h3 class="title"><?php _e( 'Manage Fields', 'wp-members' ); ?></h3>
		<div class="inside">
			<p><?php _e( 'Determine which fields will display and which are required.  This includes all fields, both native WP fields and WP-Members custom fields.', 'wp-members' ); ?>
				<br /><strong><?php _e( '(Note: Email is always mandatory and cannot be changed.)', 'wp-members' ); ?></strong></p>
			<form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
			<?php wp_nonce_field( 'wpmem-update-fields' ); ?>
				<table class="widefat" id="wpmem-fields">
					<thead><tr class="head"><?php
					foreach ( $heading_array as $heading ) {
						echo '<th scope="col">' . $heading . '</th>';
					} ?>
					</tr></thead>
				<?php
				// Get the user table fields array.
				$wpmem_ut_fields = get_option( 'wpmembers_utfields' );
				// Order, label, optionname, input type, display, required, native.
				$class = '';
				// for ( $row = 0; $row < count($wpmem_fields); $row++ ) {
				$row = 0;
				foreach ( $wpmem_fields as $meta_key => $field ) {
					$class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
					<tr class="<?php echo $class; ?>" valign="top" id="<?php echo $meta_key;?>">
						<td width="10%"><?php 
						$can_delete = ( $meta_key == 'user_nicename' || $meta_key == 'display_name' || $meta_key == 'nickname' ) ? true : false;
							if ( ( $can_delete ) || ! $field['native'] ) {  ?><input type="checkbox" name="<?php echo "del_" . $meta_key; ?>" value="delete" /> <?php _e( 'Delete', 'wp-members' ); } ?></td>
						<td width="15%"><?php 
							_e( $field['label'], 'wp-members' );
							echo ( $field['required'] ) ? '<span class="req">*</span>' : ''; ?>
						</td>
						<td width="15%"><?php echo $meta_key; ?></td>
						<td width="10%"><?php echo $field['type']; ?></td>
					  <?php if ( $meta_key != 'user_email' ) { ?>
						<td width="10%"><?php echo wpmem_create_formfield( $meta_key . "_display",  'checkbox', true, $field['register'] ); ?></td>
						<td width="10%"><?php echo wpmem_create_formfield( $meta_key . "_required", 'checkbox', true, $field['required'] ); ?></td>
					  <?php } else { ?>
						<td colspan="2" width="20%"><small><i><?php _e( '(Email cannot be removed)', 'wp-members' ); ?></i></small></td>
					  <?php } ?>
						<td align="center" width="10%"><?php if ( $field['type'] == 'checkbox' ) { 
							echo wpmem_create_formfield( $meta_key . "_checked", 'checkbox', true, $field['checked_default'] ); } ?>
						</td>
						<td width="10%"><?php echo ( $field['native'] ) ? 'native' : wpmem_fields_edit_link( $meta_key ); ?></td>

						<td align="center" width="10%">
						<?php
						$wpmem_ut_fields_skip = array( 'user_email', 'confirm_email', 'password', 'confirm_password' );
						if ( ! in_array( $meta_key, $wpmem_ut_fields_skip ) ) { ?>
							<input type="checkbox" name="ut_fields[<?php echo $meta_key; ?>]" 
							value="<?php echo $field['label']; ?>" 
							<?php echo ( ( $wpmem_ut_fields ) && ( in_array( $field['label'], $wpmem_ut_fields ) ) ) ? 'checked' : false; ?> />
						<?php } ?>
						</td>
					</tr><?php
				}
	
				$static_rows = array(
					array( 'Registration Date', 'user_registered', '4' ),
					array( 'Registration IP', 'wpmem_reg_ip', '5' ),
					array( 'Active', 'active', '5' ),
					array( 'Subscription Type', 'exp_type', '5' ),
					array( 'Expires', 'expires', '5' ),
				);
					
				foreach ( $static_rows as $static_row ) {
					$do_row = true;
					$do_row = ( 'active' == $static_row[1] && 1 != $wpmem->mod_reg ) ? false : $do_row;
					$do_row = ( ( 'exp_type' == $static_row[1] || 'expires' == $static_row[1] ) && ( ! WPMEM_EXP_MODULE || 1 != $wpmem->use_exp ) ) ? false : $do_row;
					if ( $do_row ) {
						$ut_checked = ( ( $wpmem_ut_fields ) && ( in_array( $static_row[0], $wpmem_ut_fields ) ) ) ? $static_row[0] : false;
						echo '<tr class="nodrag nodrop">';
						echo '	<td>&nbsp;</td>';
						echo '	<td><i>' . __( $static_row[0], 'wp-members' ) . '</i></td>';
						echo '	<td><i>' . $static_row[1] . '</i></td>';
						echo '	<td colspan="' . $static_row[2] . '">&nbsp;</td>';
						echo ( '4' == $static_row[2] ) ? '	<td>' . __( 'native', 'wp-members' ) . '</td>' : '';
						echo '	<td align="center">';
						echo wpmem_create_formfield( 'ut_fields[' . $static_row[1] . ']', 'checkbox', $static_row[0], $ut_checked );
						echo '	</td>';
						echo '</tr>';
					}
				} ?>
				</table><br />
				<input type="hidden" name="wpmem_admin_a" value="update_fields" />
				<?php submit_button( __( 'Update Fields', 'wp-members' ) ); ?>
			</form>
		</div><!-- .inside -->
	</div>	
	<?php
}

// End of file.