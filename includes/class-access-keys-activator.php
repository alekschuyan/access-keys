<?php

/**
 * Fired during plugin activation
 *
 * @link       https://vadyus.com
 * @since      1.0.0
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Access_Keys
 * @subpackage Access_Keys/includes
 * @author     Vadyus <->
 */
class Access_Keys_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate() {
        self::create_db_table();
	}

    public static function create_db_table () {
        global $wpdb;
        $plugin_name_db_version = '1.0.0';
        global $plugin_name_db_version;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . "access_keys";
        if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

            $sql = "CREATE TABLE " . $table_name . " (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  licence_key VARCHAR(255) NULL,
	  bios VARCHAR(255) NULL,
	  comment text NULL,
	  status tinyint(1) DEFAULT '1' NOT NULL,
	  date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  date_activate DATETIME NULL,
	  PRIMARY KEY id (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option( 'plugin_name_db_version', $plugin_name_db_version );

        }
    }

}
