<?php

if ( ! function_exists( 'rktgk_get_row' ) ) :
    /**
     * A utility function to return a single row from the databse.
     * 
     * Primarily, a wrapper for wpdb::get_row() that handles the integration of
     * wpdb::prepare() by default.  All that is necessary is to pass the table
     * name, the column name, and the query value ("where").
     * 
     * @todo Can only do strings right now, needs to test $where for type and 
     *       set the query % accordingly.
     * 
     * @param  string  $table
     * @param  string  $column
     * @param  string  $where
     * @param  string  $type   The placeholder type (float|int|integer|str|string) (default:string)
     * @return object|boolean Returns the row results as an object if found, otherwise returns false.   
     */
    function rktgk_get_row( $table, $column, $where, $type = "string" ) {
        global $wpdb;
        switch ( $type ) {
            case 'float':
                $placeholder = "%f";
                break;
            case 'int':
            case 'integer':
                $placeholder = "%d";
                break;
            case 'str':
            case 'string':
            default:
                $placeholder = "%s";
                break;
        }
        $result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `' . esc_sql( $table ) . '` WHERE `' . esc_sql( $column ) . '` = ' . $placeholder . ';', $where ) );
        return ( is_object( $result ) ) ? $result : false;
    }
endif;