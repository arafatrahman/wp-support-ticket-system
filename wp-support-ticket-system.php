<?php
/**
 * Plugin Name: WP Support Ticket System 
 * Description: A modern support ticket system for WordPress and WooCommerce.
 * Version: 1.8.2
 * Author: Web Bird
 * Author URI: https://webbird.co.uk
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wsts
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define constants
define( 'WSTS_VERSION', '1.8.2' );
define( 'WSTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load dependencies (models, controllers, etc.)
require_once WSTS_PLUGIN_DIR . 'includes/models/Taxonomy.php';
require_once WSTS_PLUGIN_DIR . 'includes/models/Ticket.php';
require_once WSTS_PLUGIN_DIR . 'includes/controllers/Admin.php';
require_once WSTS_PLUGIN_DIR . 'includes/controllers/Frontend.php';
require_once WSTS_PLUGIN_DIR . 'includes/controllers/Ajax.php';
require_once WSTS_PLUGIN_DIR . 'includes/controllers/Notification.php';

// Bootstrap the plugin
class WSTS_Plugin {

    private $admin_controller;
    private $frontend_controller;
    private $ajax_controller;
    private $notification_controller;

    public function __construct() {
        $this->init_controllers();
        $this->register_hooks();
    }

    private function init_controllers() {
        $this->admin_controller = new WSTS_Admin_Controller();
        $this->frontend_controller = new WSTS_Frontend_Controller();
        $this->ajax_controller = new WSTS_Ajax_Controller();
        $this->notification_controller = new WSTS_Notification_Controller();
    }

    private function register_hooks() {
        register_activation_hook( __FILE__, [$this, 'activate'] );
        add_action( 'init', [$this, 'register_cpt_and_taxonomies'] );
        add_action( 'wp_enqueue_scripts', [$this->admin_controller, 'enqueue_scripts'] ); // Shared for admin and frontend
        add_action( 'admin_enqueue_scripts', [$this->admin_controller, 'enqueue_scripts'] );
        add_shortcode( 'support_ticket_system', [$this->frontend_controller, 'handle_shortcode'] );
    }

    public function activate() {
        // Register CPT and Taxonomies to ensure they are available.
        $this->register_cpt_and_taxonomies();

        // Add default terms
        WSTS_Taxonomy_Model::add_default_priorities();
        WSTS_Taxonomy_Model::add_default_statuses();

        // Flush rewrite rules to apply changes.
        flush_rewrite_rules();
    }

    public function register_cpt_and_taxonomies() {
        WSTS_Ticket_Model::register_cpt();
        WSTS_Taxonomy_Model::register_taxonomies();
    }
}

new WSTS_Plugin();