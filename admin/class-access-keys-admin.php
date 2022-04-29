<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://vadyus.com
 * @since      1.0.0
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/admin
 * @author     Vadyus <->
 */
class Access_Keys_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Access_Keys_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Access_Keys_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/access-keys-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Access_Keys_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Access_Keys_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/access-keys-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */

    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
        */
        add_options_page( 'Генерация и настройка ключей доступа Klassmaster', 'Ключи доступа', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     */

    public function add_action_links( $links ) {

        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    /**
     * Render the settings page for this plugin.
     */

    public function display_plugin_setup_page() {

        include_once( 'partials/access-keys-admin-display.php' );

    }

    /**
     * Validate options
     */
    public function validate($input) {

        if(isset($input['comment']) && !empty($input['comment']) && isset($input['status']) && !empty($input['status'])) {
            $this->update_keys($input);
        }

        if(isset($input['delete']) && !empty($input['delete'])) {
            $this->delete_keys($input);
        }

        $valid = array();
        $valid['licence_key_count'] = (isset($input['licence_key_count']) && !empty($input['licence_key_count'])) ? $input['licence_key_count'] : '';

        if($valid['licence_key_count']) {
            $this->generate_keys($valid['licence_key_count']);
        }

        return $valid;
    }

    /**
     * Generate access keys
     */
    public function generate_keys($licence_key_count) {
        global $wpdb;
        $table_name = $wpdb->prefix . "access_keys";
        for($i = 0; $i < $licence_key_count; $i++) {
            $new_key = uniqid('km_', true);
            $wpdb->insert( $table_name, array( 'licence_key' => $new_key, 'date_added' => current_time('mysql')) );
        }

    }

    /**
     * Update access keys
     */
    public function update_keys($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . "access_keys";

        foreach ($data['comment'] as $id => $comment) {
            $comment = esc_sql($comment);
            $status = $data['status'][$id];
            $wpdb->update( $table_name,
                [ 'comment' => "$comment", 'status' => $status ],
                [ 'id' => $id ]
            );
        }
    }

    /**
     * Delete access keys
     */
    public function delete_keys($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . "access_keys";

        foreach ($data['delete'] as $id => $delete) {
            if($delete) {
                $wpdb->delete( $table_name, [ 'id' => $id ] );
            }
        }
    }

    /**
     * Update all options
     */
    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

}
