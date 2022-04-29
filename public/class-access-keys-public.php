<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://vadyus.com
 * @since      1.0.0
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/public
 * @author     Vadyus <->
 */
class Access_Keys_Public {

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

	/*
	 * other variables
	 * */

    private $error_message = "Доступ заборонений!";
    private $redirect = false;
    private $www_auth = true;
    private $validated = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/access-keys-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/access-keys-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * The function of checking access keys
     */

    public function check_access() {

        $valid_passwords = array("administrator" => "edu-brd-prst21!");
        $valid_users = array_keys($valid_passwords);

        if(isset($_GET['_auth']) && !empty($_GET['_auth']) && isset($_GET['_bs']) && !empty($_GET['_bs'])) {
            $data['licence_key'] = $_GET['_auth'];
            $data['bios'] = base64_decode($_GET['_bs']);
            $this->validated = $this->check_licence($data);
            $this->redirect = true;
            $this->www_auth = false;
        } else {
            if(!isset($_COOKIE['km_auth'])) {
                if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                    $user = $_SERVER['PHP_AUTH_USER'];
                    $pass = $_SERVER['PHP_AUTH_PW'];
                    $this->validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);
                }
            } else {
                $this->validated = true;
            }
        }

        if (!$this->validated) {
            $this->access_denied();
        } else {
            setcookie("km_auth", uniqid('', true), time() + (86400 * 1), "/");
            if($this->redirect) {
                wp_redirect(site_url());
                die();
            }
        }
    }

    private function check_licence($data = array()): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "access_keys";
        $access_info = $wpdb->get_row( "SELECT * FROM $table_name WHERE bios = '" . $data['bios'] . "' ORDER BY id DESC");

        if($access_info !== NULL) { ////// bios found, check key, check demo

            $licence_info = $wpdb->get_row( "SELECT * FROM $table_name WHERE licence_key = '" . $data['licence_key'] . "' AND status = 1 ORDER BY id DESC");

            if($licence_info !== NULL) { // + works

                $date_activate = $licence_info->date_activate;
                $bios = $licence_info->bios;

                if($date_activate !== NULL && $bios !== NULL && $bios == $data['bios']) { // + works

                    return true; // allow access

                }

                if($date_activate === NULL && $bios === NULL) { // + works

                    $wpdb->query( "DELETE FROM $table_name WHERE bios = '" . $data['bios'] . "' AND licence_key <> '" . $data['licence_key'] . "'");

                    $wpdb->update( $table_name,
                        [ 'bios' => $data['bios'], 'date_activate' => current_time('mysql') ],
                        [ 'licence_key' => $data['licence_key'] ]
                    );
                    return true; // allow access

                }

            } else { // check demo, + works

                return $this->check_demo($access_info, $data);

            }

        } else { /////// bios not found, check key, activate demo

            $licence_info = $wpdb->get_row( "SELECT * FROM $table_name WHERE licence_key = '" . $data['licence_key'] . "' AND bios IS NULL AND date_activate IS NULL AND status = 1 ORDER BY id DESC");

            if($licence_info !== NULL) { // + works

                $wpdb->update( $table_name,
                    [ 'bios' => $data['bios'], 'date_activate' => current_time('mysql') ],
                    [ 'licence_key' => $data['licence_key'] ]
                );

            } else { // + works

                $wpdb->insert( $table_name,
                    [ 'licence_key' => "demo-" . uniqid(), 'bios' => $data['bios'],
                        'date_added' => current_time('mysql') ]
                );

            }

            return true; // allow access
        }
    }

    private function check_demo($access_info = array(), $data = array()): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "access_keys";

        if(!$access_info->status) {
            return false;
        }

        if($access_info->date_added !== NULL) {
            $date_added = new DateTime($access_info->date_added);
            $date_added = $date_added->modify('+30 day');
            $now = new DateTime();
            $demo = $now < $date_added;
            if($demo) {
                return true;
            } else {
                $this->error_message = "Закінчився термін демо-доступу! Зверніться, будь ласка, до нашого менеджера для отримання повного доступу!";
                return false;
            }
        } else {
            $wpdb->update( $table_name,
                [ 'date_added' => current_time('mysql') ],
                [ 'bios' => $data['bios'] ]
            );
            return true;
        }
    }

    private function access_denied() {
        if($this->www_auth) {
            header('WWW-Authenticate: Basic realm="Klassmaster"');
        }
        header('HTTP/1.0 401 Unauthorized');
        die ($this->error_message);
    }

}
