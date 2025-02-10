<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_DB_Tools {

        /**
         * Creates a database view.
         * 
         * ## OPTIONS
		 *
         * --name=<view_name>
         * : Name of the view being created.
         * 
         * --table=<db_table>
         * : The table to view, without the database prefix.
		 *
		 * --fields=<table_fields>
		 * : A comma seperated list of table's fields.
		 *
		 * [--meta=<meta_fields>]
		 * : A comma seperated list of wp_usermeta fields.
		 *
         * [--where=<where_field>]
         * : A where comparison field.
         * 
         * [--compare=<comparison_operator>] 
         * : A where comparison (default:=).
         * 
         * [--where_value=<where_value>] 
         * : Required if there is a where comparison.
         * 
         * [--order_by=<meta_key>] 
         * : Order the view by a field.
         * 
         * [--order=<desc>] 
         * : Order by desc|asc
         * 
         * [--dates=<meta_fields>] 
         * : Field that should be date formatted.
         * 
         * [--date_display=<display_format>] 
         * : What format for dates. Default: %Y-%m-%d
         * 
         * @alias create-view
         */
        public function create_view( $args, $assoc_args ) {

            global $wpdb;

            $table     = $wpdb->prefix . esc_sql( $assoc_args['table'] );
			$view_name = esc_sql( $assoc_args['name'] );
            $columns   = explode( ',', esc_sql( $assoc_args['fields'] ) );
            $meta_keys = ( isset( $assoc_args['meta'] ) ) ? explode( ',', esc_sql( $assoc_args['meta'] ) ) : false;
            $meta_id   = $this->get_meta_id( esc_attr( $assoc_args['table'] ) ); // The id field for meta (i.e. user_id, post_id, etc) is table name, no prefix.
            $order_by  = ( isset( $assoc_args['order_by'] ) ) ? esc_sql( $assoc_args['order_by'] ) : false;
            $order     = ( isset( $assoc_args['order'] ) ) ? esc_sql( $assoc_args['order'] ) : 'asc';
            $dates     = ( isset( $assoc_args['dates'] ) ) ? explode( ',', esc_sql( $assoc_args['dates'] ) ) : false;
            $date_display = ( isset( $assoc_args['date_display'] ) ) ? esc_sql( $assoc_args['date_display'] ) : "%Y-%m-%d";

            if ( isset( $assoc_args['where'] ) ) {
                $is_where  = true;
                $where     = esc_sql( $assoc_args['where'] ); 
            } else {
                $is_where = false;
            }
            

            // Check if view already exists.
            if ( false != $this->view_exists( $view_name ) ) {
                WP_CLI::error( __( 'View already exists. Try another name or edit existing view.', 'wp-members' ) );
            }

            // If there's "where", is it a field that is part of the query?
            if ( $is_where ) {
                $in_columns = ( in_array( $where, $columns ) ) ? true : false;
                $unknown_where = false;
                if ( $meta_keys ) {
                    $in_meta = ( in_array( $where, $meta_keys ) ) ? true : false;
                    if ( ! $in_columns && ! $in_meta ) {
                        $unknown_where = true;
                    }
                } else {
                    if ( ! $in_columns ) {
                        $unknown_where = true;
                    }
                }
            }

            $sql = 'CREATE OR REPLACE VIEW ' . $view_name . ' AS SELECT t.id,';

            // Build wp_users fields.  Include error check.
            $contains_bad_field = false;
			$table_columns = $this->columns( $table );
            foreach ( $columns as $column ) {
				if ( ! in_array( $column, $table_columns ) ) {
					$contains_bad_field = true;
				}
                $sql_fields[] = 't.' . $column;

                // Check if this is the "where" field.
                if ( $is_where && $column == $where ) {
                    $where_field = 't.' . $column;
                }

                // Check if this is the "order by" field.
                if ( $order_by && $column == $order_by ) {
                    $order_by = 't.' . $column;
                }
            }

            if ( $is_where && $unknown_where ) {
                $where_field = 't.' . $column;
            }
			
			// Check that fields are actually valid wp_user fields.
            if ( $contains_bad_field ) {
                WP_CLI::error( sprintf( __( 'Fields for %s fields contains a field that is not a valid %s field.', 'wp-members' ), $table, $table ) );
            }
			
			// Trim comma from last value.
			$sql = $sql . implode( ',', $sql_fields );
			
			// If we have meta fields, we have a join.
            if ( $meta_keys ) {
			    $sql .= ',';

                // Get existing user meta fields for error checking.
                $table_meta_fields = $this->get_meta_keys( $table );

                // Build wp_usermeta fields.
                $is_join = false;
                $m = 1;
                foreach ( $meta_keys as $meta_field ) {

                    // If meta key is invalid, print error.
                    if ( ! in_array( $meta_field, $table_meta_fields ) ) {
                        WP_CLI::error( sprintf( __( '%s is not a valid meta key in %s.', 'wp-members' ), esc_attr( $meta_field ), $this->get_meta_table( $table ) ) );
                    }

                    /*
                    * Do we have a "where" clause?
                    * 
                    * If not, it's more efficient to use internal selects for each meta in the view.
                    * If so, we'll have to do a JOIN for the metas.
                    */
                    if ( $is_where ) {

                        if ( in_array( $meta_field, $dates ) ) {
                            $sql_meta[] = 'FROM_UNIXTIME( m' . $m . '.meta_value, "' . $date_format . '")  AS `' . esc_sql( $meta_field ) . '`';
                        } else {
                            $sql_meta[] = 'm' . $m . '.meta_value AS `' . esc_sql( $meta_field ) . '`';
                        }
                        $m++;
                    } else {
                        $sql_meta[] = '(select meta_value from ' . $this->get_meta_table( $table ) . ' where ' . $meta_id . ' = t.id and meta_key = "' . esc_sql( $meta_field ) . '" limit 1) as `' . esc_sql( $meta_field ) . '`';
                    }
                }
                
                // Trim comma from last value.
                $sql = $sql . implode( ',', $sql_meta );

            }

            $sql .= ' FROM ' . $table . ' t';

            // If we have a where clause, do JOINs and add WHERE.
            if ( $is_where ) {

				// If there are meta fields.
				if ( $meta_keys ) {
					// JOIN wp_usermeta m1 ON (m1.' . $meta_id . ' = t.ID AND m1.meta_key = 'my_meta_key_name')
					$m = 1;
					foreach ( $meta_keys as $meta_field ) {
						$sql .= ' JOIN ' . $this->get_meta_table( $table ) . ' m' . $m . ' ON (m' . $m . '.' . $meta_id . ' = t.ID AND m' . $m . '.meta_key = "' . esc_sql( $meta_field ) . '") ';
                        
                        // Check if this is the "where" field.
                        if ( $is_where && $meta_field == $where ) {
                            $where_field = 'm' . $m . '.meta_value';
                        }

                        // Check if this is the "order by" field.
                        if ( $order_by && $meta_field == $order_by ) {
                            $order_by = 'm' . $m . '.meta_value';
                        }
                        
                        $m++;
                    }
				}
				
                // Add where clause
                $sql .= ' WHERE ' . esc_sql( $where_field );

                // Is there a comparison defined? If not, default to "="
                $sql .= ( isset( $assoc_args['compare'] ) ) ? ' ' . esc_sql( $assoc_args['compare'] ) . ' ' : '=';

                // Add the value. Error if not included.
                if ( ! isset( $assoc_args['where_value'] ) ) {
                    WP_CLI::error( __( 'A "where_value" value is required when including a comparison', 'wp-members' ) );
                } else {
                    $sql .= ' "' . esc_sql( $assoc_args['where_value'] ) . '" ';
                }
            }

            if ( $order_by ) {
                $sql .= ' ORDER BY ' . $order_by; 
            }

            $sql .= ";";

            // Add the view.
            $result = $wpdb->query( $sql );

            if ( $result ) {
                WP_CLI::success( __( 'New view added: ', 'wp-members' ) . esc_attr( $view_name ) );
            } else {
                WP_CLI::error( __( 'An unknown error occurred. View was not added.', 'wp-members' ) );
            }
        }

        /**
         * Drops specified db views.
         * 
         * <view_name>
         * : Name of the view to drop.
         * 
         * @alias drop-view
         */
        public function drop_view( $args ) {
            global $wpdb;
            $errors = array();
            foreach ( $args as $arg ) {
                $sql = "DROP VIEW IF EXISTS " . esc_sql( $arg );
                $result = $wpdb->query( $sql );
                if ( $result ) {
                    WP_CLI::success( __( 'Dropped user view: ', 'wp-members' ) . esc_attr( $arg ) );
                } else {
                    $errors[] = $arg;
                }
            }
            if ( ! empty( $errors ) ) {
                WP_CLI::error( sprintf( __( 'An unknown error occurred. The following views were not dropped: %s', 'wp-members' ), implode( ', ', $errors ) ) );
            }
        }

        /**
         * Lists all views in the database.
         */
        public function views() {
            global $wpdb;

            $results = $this->get_views();

            if ( $results ) {
                foreach( $results as $result ) {
                    $list[] = array( 'Views'=>$result->{'Tables_in_'.$wpdb->dbname} );
                }

                WP_CLI::line( sprintf( __( 'List of views contained in %s', 'wp-members' ), $wpdb->dbname ) );
                $formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Views' ) );
                $formatter->display_items( $list );
            } else {
                WP_CLI::line( sprintf( __( 'There are no views in %s', 'wp-members' ), $wpdb->dbname ) );
            }
        }

        /**
         * Gets all unique meta keys from a table.
         *
         * <table_name>
         * : Name of the table to view meta keys.
         * 
         * @alias meta-keys
         */
        public function meta_keys( $args ) {
            $results = $this->get_meta_keys( $args[0] );
            if ( $results ) {
                foreach( $results as $result ) {
                    $list[] = array( 'Field'=>$result );
                }
                WP_CLI::line( sprintf( __( 'List of meta keys contained in %s %s', 'wp-members' ), $wpdb->dbname, $wpdb->prefix . esc_attr( $args[0] ) ) );
                $formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Field' ) );
                $formatter->display_items( $list );
            }
        }

        private function columns( $table ) {
            global $wpdb;
            $sql = 'SELECT column_name FROM information_schema.columns
                WHERE table_schema = "' . $wpdb->dbname . '"
                AND table_name = "' . esc_sql( $table ) . '";';
            
            $results = $wpdb->get_results( $sql );
            foreach ( $results as $result ) {
                $columns[] = $result->column_name;
            }

            return $columns;
        }

        private function get_meta_keys( $table ) {
            global $wpdb;
            $sql = 'SELECT DISTINCT meta_key FROM ' . $this->get_meta_table( $table ) . ';';
            $results = $wpdb->get_results( $sql );

            $results = $wpdb->get_results( $sql );
            foreach ( $results as $result ) {
                $keys[] = $result->meta_key;
            }

            return $keys;
        }

        private function get_meta_table( $table ) {
            global $wpdb;
            // Remove stem.
            $raw_table_name = str_replace( $wpdb->prefix, '', $table );
            $wp_meta_tables = apply_filters( 'wpmem_cli_wp_meta_tables', array( 
                'comments' => 'commentmeta',
                'posts' => 'postmeta',
                'terms' => 'termmeta',
                'users' => 'usermeta'
            ) );
            return $wpdb->prefix . $wp_meta_tables[ $raw_table_name ];
        }

        private function get_meta_id( $table ) {
            // Remove stem.
            $wp_meta_tables = apply_filters( 'wpmem_cli_wp_meta_ids', array( 
                'comments' => 'comment_id',
                'posts' => 'post_id',
                'terms' => 'term_id',
                'users' => 'user_id'
            ) );
            return $wp_meta_tables[ $table ];
        }

        private function get_views() {
            global $wpdb;
            return $wpdb->get_results( 'SHOW FULL TABLES IN ' . $wpdb->dbname . ' WHERE table_type="VIEW";' );
        }

        private function view_exists( $chk_view ) {
            global $wpdb;
            $views = $this->get_views();
            // If there are no views, it does not exist.
            if ( ! $views ) {
                echo 'does not exist!';
                return false;
            }
            // Put views in array to check.
            foreach ( $views as $view ) {
                $views_array[] = $view->{'Tables_in_' . $wpdb->dbname};
            }
            return ( in_array( $chk_view, $views_array ) ) ? true : false;
        }

        /**
         * Checks the options table autoload size.
         * 
         * ## OPTIONS
		 *
         * [--order=<order>]
         * : Order to display result (ASC|DESC, default:DESC).
         * 
         * [--limit=<limit>]
         * : Limit the results returned (default:10).
		 *
		 * [--like=<like>]
		 * : Return options with MySQL query "LIKE".
         * 
         * [--all]
		 * : Returns all results.
         * 
         * @alias autoload-size
         */
        public function autoload_size( $args, $assoc_args ) {

            global $wpdb;
            
            $order = ( isset( $assoc_args['order'] ) ) ? $assoc_args['order'] : "DESC";
            $limit = ( isset( $assoc_args['limit'] ) ) ? "LIMIT " . intval( $assoc_args['limit'] ) : "LIMIT 10";

            if ( isset( $assoc_args['all'] ) ) {
                $limit = '';
            }

            $and = ( isset( $assoc_args['like'] ) ) ? ' AND option_name LIKE "%' . esc_sql( $assoc_args['like'] ) . '%"' : "";

            $query = 'SELECT ROUND(SUM(LENGTH(option_value))/1024) as autoload_size FROM ' . $wpdb->prefix . 'options WHERE autoload="yes" OR autoload="on";';
            $results = $wpdb->get_results( $query );

            WP_CLI::line( __( 'Autoload options size (in KiB):', 'wp-members' ) . ' ' . $results[0]->autoload_size );

            $query = 'SELECT option_name, length(option_value) 
                AS option_value_length FROM ' . $wpdb->prefix . 'options  
                WHERE autoload="yes" OR autoload="on" ' . $and . ' ORDER BY option_value_length ' . esc_sql( $order ) . ' ' . $limit . ';';
            
            $results = $wpdb->get_results( $query );

            if ( $results ) {
                foreach( $results as $result ) {
                    $list[] = array(
                        'Name'=>$result->option_name,
                        'Value'=>$result->option_value_length,
                    );
                }

                WP_CLI::line( __( 'List of autoload options', 'wp-members' ) );
                $formatter = new \WP_CLI\Formatter( $assoc_args, array( 'Name', 'Value' ) );
                $formatter->display_items( $list );
            } else {
                WP_CLI::line( __( 'There were no results', 'wp-members' ) );
            }
        }
    }
}

WP_CLI::add_command( 'mem db', 'WP_Members_CLI_DB_Tools' );