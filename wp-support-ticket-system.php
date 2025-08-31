<?php
/**
 * Plugin Name:       Woo Support Ticket System
 * Description:       A modern support ticket system for WordPress and WooCommerce.
 * Version:           1.8.2
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wsts
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define constants
define( 'WSTS_VERSION', '1.8.2' );
define( 'WSTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// =============================================================================
// Plugin Activation
// =============================================================================

/**
 * Handle plugin activation.
 */
function wsts_activate() {
    // Register CPT and Taxonomies to ensure they are available.
    wsts_register_ticket_cpt();
    wsts_register_taxonomies();

    // Add default terms if they don't exist.
    $priorities = ['Low' => '#0dcaf0', 'Medium' => '#ffc107', 'High' => '#dc3545'];
    foreach ($priorities as $priority => $color) {
        if (!term_exists($priority, 'ticket_priority')) {
            $term = wp_insert_term($priority, 'ticket_priority');
            if(!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'wsts_color', $color);
            }
        }
    }

    $statuses = ['Open' => '#0d6efd', 'Answered' => '#198754', 'Closed' => '#6c757d', 'Pending' => '#ff8c00'];
    foreach ($statuses as $status => $color) {
        if (!term_exists($status, 'ticket_status')) {
            $term = wp_insert_term($status, 'ticket_status');
             if(!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'wsts_color', $color);
            }
        }
    }

    // Flush rewrite rules to apply changes.
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wsts_activate' );


// =============================================================================
// Custom Post Type & Taxonomy Registration
// =============================================================================

/**
 * Register the 'support_ticket' custom post type.
 */
function wsts_register_ticket_cpt() {
    $labels = [
        'name'               => _x( 'Support Tickets', 'post type general name', 'wsts' ),
        'singular_name'      => _x( 'Support Ticket', 'post type singular name', 'wsts' ),
        'menu_name'          => _x( 'Support Tickets', 'admin menu', 'wsts' ),
        'name_admin_bar'     => _x( 'Support Ticket', 'add new on admin bar', 'wsts' ),
        'add_new'            => _x( 'Add New', 'ticket', 'wsts' ),
        'add_new_item'       => __( 'Add New Ticket', 'wsts' ),
        'new_item'           => __( 'New Ticket', 'wsts' ),
        'edit_item'          => __( 'Edit Ticket', 'wsts' ),
        'view_item'          => __( 'View Ticket', 'wsts' ),
        'all_items'          => __( 'All Tickets', 'wsts' ),
        'search_items'       => __( 'Search Tickets', 'wsts' ),
        'parent_item_colon'  => __( 'Parent Tickets:', 'wsts' ),
        'not_found'          => __( 'No tickets found.', 'wsts' ),
        'not_found_in_trash' => __( 'No tickets found in Trash.', 'wsts' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'support-ticket'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-sos',
        'supports'           => ['title', 'editor', 'comments', 'author'],
        'show_in_rest'       => true, // Enable Gutenberg editor
    ];

    register_post_type( 'support_ticket', $args );
}
add_action( 'init', 'wsts_register_ticket_cpt' );

/**
 * Register custom taxonomies for Priority and Status.
 */
function wsts_register_taxonomies() {
    // Priority Taxonomy (Hierarchical)
    $priority_labels = [
        'name'              => _x( 'Priorities', 'taxonomy general name', 'wsts' ),
        'singular_name'     => _x( 'Priority', 'taxonomy singular name', 'wsts' ),
        'search_items'      => __( 'Search Priorities', 'wsts' ),
        'all_items'         => __( 'All Priorities', 'wsts' ),
        'parent_item'       => __( 'Parent Priority', 'wsts' ),
        'parent_item_colon' => __( 'Parent Priority:', 'wsts' ),
        'edit_item'         => __( 'Edit Priority', 'wsts' ),
        'update_item'       => __( 'Update Priority', 'wsts' ),
        'add_new_item'      => __( 'Add New Priority', 'wsts' ),
        'new_item_name'     => __( 'New Priority Name', 'wsts' ),
        'menu_name'         => __( 'Priorities', 'wsts' ),
    ];
    $priority_args = [
        'hierarchical'      => true,
        'labels'            => $priority_labels,
        'show_ui'           => true,
        'show_admin_column' => false, // We use a custom column
        'query_var'         => true,
        'rewrite'           => ['slug' => 'ticket-priority'],
        'show_in_rest'      => true,
    ];
    register_taxonomy( 'ticket_priority', ['support_ticket'], $priority_args );

    // Status Taxonomy (Hierarchical)
    $status_labels = [
        'name'              => _x( 'Statuses', 'taxonomy general name', 'wsts' ),
        'singular_name'     => _x( 'Status', 'taxonomy singular name', 'wsts' ),
        'search_items'      => __( 'Search Statuses', 'wsts' ),
        'all_items'         => __( 'All Statuses', 'wsts' ),
        'parent_item'       => __( 'Parent Status', 'wsts' ),
        'parent_item_colon' => __( 'Parent Status:', 'wsts' ),
        'edit_item'         => __( 'Edit Status', 'wsts' ),
        'update_item'       => __( 'Update Status', 'wsts' ),
        'add_new_item'      => __( 'Add New Status', 'wsts' ),
        'new_item_name'     => __( 'New Status Name', 'wsts' ),
        'menu_name'         => __( 'Statuses', 'wsts' ),
    ];
    $status_args = [
        'hierarchical'      => true,
        'labels'            => $status_labels,
        'show_ui'           => true,
        'show_admin_column' => false, // We use a custom column
        'query_var'         => true,
        'rewrite'           => ['slug' => 'ticket-status'],
        'show_in_rest'      => true,
    ];
    register_taxonomy( 'ticket_status', ['support_ticket'], $status_args );
}
add_action( 'init', 'wsts_register_taxonomies' );

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Generate HTML for a term badge with its custom color.
 *
 * @param WP_Term|null $term The term object.
 * @return string The HTML for the badge.
 */
function wsts_get_term_badge_html($term) {
    if (!$term || is_wp_error($term)) {
        return '';
    }
    $color = get_term_meta($term->term_id, 'wsts_color', true);
    if (!$color) {
        $color = '#6c757d'; // A default gray color if none is set
    }
    return sprintf(
        '<span class="badge" style="background-color:%s">%s</span>',
        esc_attr($color),
        esc_html($term->name)
    );
}

// =============================================================================
// Enqueue Scripts and Styles
// =============================================================================

/**
 * Enqueue scripts and styles for both admin and frontend.
 */
function wsts_enqueue_scripts($hook) {
    // Enqueue color picker for taxonomy pages in admin
    if ( is_admin() && ('term.php' === $hook || 'edit-tags.php' === $hook) ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    // Common styles
    wp_enqueue_style( 'wsts-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2' );
    wp_enqueue_style( 'wsts-datatables', 'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css', array(), '1.13.7' );
    wp_enqueue_style( 'wsts-style', WSTS_PLUGIN_URL . 'assets/css/style.css', array(), WSTS_VERSION );

    // Common scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'wsts-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true );
    wp_enqueue_script( 'wsts-datatables-js', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', array('jquery'), '1.13.7', true );
    wp_enqueue_script( 'wsts-datatables-bootstrap-js', 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js', array('jquery', 'wsts-datatables-js'), '1.13.7', true );
    wp_enqueue_script( 'wsts-script', WSTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), WSTS_VERSION, true );

    // Pass data to script.js
    wp_localize_script( 'wsts-script', 'wsts_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'wsts_nonce' )
    ));
}
add_action( 'wp_enqueue_scripts', 'wsts_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'wsts_enqueue_scripts' );


// =============================================================================
// Admin Area Customizations
// =============================================================================

/**
 * Add custom meta boxes for ticket details.
 */
function wsts_add_meta_boxes() {
    add_meta_box(
        'wsts_ticket_details',
        __( 'Ticket Details', 'wsts' ),
        'wsts_render_ticket_details_meta_box',
        'support_ticket',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wsts_add_meta_boxes' );

/**
 * Render the content of the ticket details meta box.
 */
function wsts_render_ticket_details_meta_box( $post ) {
    global $pagenow;
    wp_nonce_field( 'wsts_save_meta_box_data', 'wsts_meta_box_nonce' );

    $assigned_to = get_post_meta( $post->ID, '_wsts_assigned_to', true );
    $user_id = get_post_meta( $post->ID, '_wsts_user_id', true );
    
    $assignable_users = get_users(['role__in' => ['administrator', 'editor'], 'fields' => ['ID', 'display_name']]);

    // If creating a new ticket, show a dropdown of all users. Otherwise, show the user's name.
    if ( $pagenow === 'post-new.php' ) {
        $all_users = get_users(['fields' => ['ID', 'display_name']]);
        ?>
        <p>
            <label for="wsts_user_id"><strong><?php _e( 'User:', 'wsts' ); ?></strong></label>
            <select name="wsts_user_id" id="wsts_user_id" class="widefat" required>
                <option value=""><?php _e('-- Select User --', 'wsts'); ?></option>
                <?php foreach ($all_users as $user_item): ?>
                    <option value="<?php echo $user_item->ID; ?>"><?php echo esc_html($user_item->display_name); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    } else {
        $user = get_user_by('id', $user_id);
        ?>
        <p><strong><?php _e('User:', 'wsts'); ?></strong> <?php echo $user ? esc_html($user->display_name) : __('N/A', 'wsts'); ?></p>
        <?php
    }

    // Priority Dropdown
    $priorities = get_terms(['taxonomy' => 'ticket_priority', 'hide_empty' => false]);
    $current_priority = wp_get_post_terms($post->ID, 'ticket_priority', ['fields' => 'ids']);
    ?>
    <p>
        <label for="wsts_priority"><strong><?php _e( 'Priority:', 'wsts' ); ?></strong></label>
        <select name="wsts_priority" id="wsts_priority" class="widefat">
            <?php foreach ($priorities as $priority): ?>
                <option value="<?php echo $priority->term_id; ?>" <?php selected( in_array($priority->term_id, $current_priority) ); ?>><?php echo esc_html($priority->name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
    // Status Dropdown
    $statuses = get_terms(['taxonomy' => 'ticket_status', 'hide_empty' => false]);
    $current_status = wp_get_post_terms($post->ID, 'ticket_status', ['fields' => 'ids']);
    ?>
    <p>
        <label for="wsts_status"><strong><?php _e( 'Status:', 'wsts' ); ?></strong></label>
        <select name="wsts_status" id="wsts_status" class="widefat">
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo $status->term_id; ?>" <?php selected( in_array($status->term_id, $current_status) ); ?>><?php echo esc_html($status->name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="wsts_assigned_to"><strong><?php _e( 'Assigned To:', 'wsts' ); ?></strong></label>
        <select name="wsts_assigned_to" id="wsts_assigned_to" class="widefat">
            <option value="0"><?php _e('-- Unassigned --', 'wsts'); ?></option>
            <?php foreach ($assignable_users as $admin_user): ?>
                <option value="<?php echo $admin_user->ID; ?>" <?php selected($assigned_to, $admin_user->ID); ?>><?php echo esc_html($admin_user->display_name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * Save custom meta box data and taxonomy terms.
 */
function wsts_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['wsts_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wsts_meta_box_nonce'], 'wsts_save_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save the user ID when an admin creates a ticket
    if ( isset( $_POST['wsts_user_id'] ) ) {
        update_post_meta( $post_id, '_wsts_user_id', intval( $_POST['wsts_user_id'] ) );
    }
    if ( isset( $_POST['wsts_priority'] ) ) {
        wp_set_post_terms( $post_id, [intval($_POST['wsts_priority'])], 'ticket_priority', false );
    }
    if ( isset( $_POST['wsts_status'] ) ) {
        wp_set_post_terms( $post_id, [intval($_POST['wsts_status'])], 'ticket_status', false );
    }
    if ( isset( $_POST['wsts_assigned_to'] ) ) {
        update_post_meta( $post_id, '_wsts_assigned_to', intval( $_POST['wsts_assigned_to'] ) );
    }
}
add_action( 'save_post_support_ticket', 'wsts_save_meta_box_data' );


/**
 * Customize the columns in the ticket list table.
 */
function wsts_set_custom_edit_ticket_columns($columns) {
    unset($columns['author']);
    unset($columns['date']);
    unset($columns['comments']);
    $columns['user'] = __( 'User', 'wsts' );
    $columns['assigned_to'] = __( 'Assigned To', 'wsts' );
    $columns['priority'] = __( 'Priority', 'wsts' );
    $columns['status'] = __( 'Status', 'wsts' );
    $columns['last_updated'] = __( 'Last Updated', 'wsts' );
    return $columns;
}
add_filter( 'manage_support_ticket_posts_columns', 'wsts_set_custom_edit_ticket_columns' );

/**
 * Populate the custom columns with data.
 */
function wsts_custom_ticket_column( $column, $post_id ) {
    switch ( $column ) {
        case 'user':
            $user_id = get_post_meta($post_id, '_wsts_user_id', true);
            $user = get_user_by('id', $user_id);
            echo $user ? esc_html($user->display_name) : __('N/A', 'wsts');
            break;
        case 'assigned_to':
            $assigned_id = get_post_meta($post_id, '_wsts_assigned_to', true);
            if($assigned_id) {
                $user = get_user_by('id', $assigned_id);
                echo $user ? esc_html($user->display_name) : __('Unknown', 'wsts');
            } else {
                echo '<em>' . __('Unassigned', 'wsts') . '</em>';
            }
            break;
        case 'priority':
            $terms = get_the_terms($post_id, 'ticket_priority');
            if ($terms && !is_wp_error($terms)) {
                echo wsts_get_term_badge_html(array_shift($terms));
            }
            break;
        case 'status':
            $terms = get_the_terms($post_id, 'ticket_status');
            if ($terms && !is_wp_error($terms)) {
                echo wsts_get_term_badge_html(array_shift($terms));
            }
            break;
        case 'last_updated':
            echo get_the_modified_date( 'F j, Y, g:i a', $post_id );
            break;
    }
}
add_action( 'manage_support_ticket_posts_custom_column', 'wsts_custom_ticket_column', 10, 2 );

/**
 * Add custom views (e.g., "Open", "Closed") to the ticket list table.
 */
function wsts_add_ticket_views( $views ) {
    $statuses = get_terms([
        'taxonomy' => 'ticket_status',
        'hide_empty' => false,
    ]);

    if ( ! empty( $statuses ) && ! is_wp_error( $statuses ) ) {
        foreach ( $statuses as $status ) {
            $query = new WP_Query([
                'post_type' => 'support_ticket',
                'post_status' => 'any',
                'tax_query' => [
                    [
                        'taxonomy' => 'ticket_status',
                        'field'    => 'slug',
                        'terms'    => $status->slug,
                    ],
                ],
            ]);
            $count = $query->found_posts;

            if ( $count === 0 ) {
                continue;
            }

            $url = add_query_arg(
                [
                    'post_type'     => 'support_ticket',
                    'ticket_status' => $status->slug,
                ],
                'edit.php'
            );

            $is_current = isset( $_GET['ticket_status'] ) && $_GET['ticket_status'] === $status->slug;
            $class = $is_current ? 'current' : '';

            $views[ $status->slug ] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                esc_url( $url ),
                $class,
                esc_html( $status->name ),
                $count
            );
        }
    }

    return $views;
}
add_filter( 'views_edit-support_ticket', 'wsts_add_ticket_views' );


// =============================================================================
// Taxonomy Color Picker Fields
// =============================================================================

/**
 * Add color picker field to the "Add New" screen for taxonomies.
 */
function wsts_add_taxonomy_color_field() {
    ?>
    <div class="form-field">
        <label for="term-color"><?php _e( 'Color', 'wsts' ); ?></label>
        <input type="text" name="term_color" id="term-color" class="wsts-color-picker" value="#ffffff">
        <p class="description"><?php _e( 'Choose a color for this term.', 'wsts' ); ?></p>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.wsts-color-picker').wpColorPicker();
        });
    </script>
    <?php
}
add_action( 'ticket_priority_add_form_fields', 'wsts_add_taxonomy_color_field' );
add_action( 'ticket_status_add_form_fields', 'wsts_add_taxonomy_color_field' );

/**
 * Add color picker field to the "Edit" screen for taxonomies.
 */
function wsts_edit_taxonomy_color_field( $term ) {
    $color = get_term_meta( $term->term_id, 'wsts_color', true );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="term-color"><?php _e( 'Color', 'wsts' ); ?></label></th>
        <td>
            <input type="text" name="term_color" id="term-color" class="wsts-color-picker" value="<?php echo esc_attr( $color ); ?>">
            <p class="description"><?php _e( 'Choose a color for this term.', 'wsts' ); ?></p>
        </td>
    </tr>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.wsts-color-picker').wpColorPicker();
        });
    </script>
    <?php
}
add_action( 'ticket_priority_edit_form_fields', 'wsts_edit_taxonomy_color_field' );
add_action( 'ticket_status_edit_form_fields', 'wsts_edit_taxonomy_color_field' );

/**
 * Save the color field value for taxonomies.
 */
function wsts_save_taxonomy_color( $term_id ) {
    if ( isset( $_POST['term_color'] ) ) {
        update_term_meta( $term_id, 'wsts_color', sanitize_hex_color( $_POST['term_color'] ) );
    }
}
add_action( 'created_ticket_priority', 'wsts_save_taxonomy_color' );
add_action( 'edited_ticket_priority', 'wsts_save_taxonomy_color' );
add_action( 'created_ticket_status', 'wsts_save_taxonomy_color' );
add_action( 'edited_ticket_status', 'wsts_save_taxonomy_color' );


// =============================================================================
// Frontend Shortcode
// =============================================================================

/**
 * Register the main shortcode for the support system.
 */
function wsts_support_system_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged in to view your support tickets.', 'wsts' ) . '</p>';
    }

    ob_start();
    
    if(isset($_GET['ticket_id'])) {
        wsts_render_frontend_single_ticket(intval($_GET['ticket_id']));
    } else {
        wsts_render_frontend_ticket_list();
    }

    return ob_get_clean();
}
add_shortcode( 'support_ticket_system', 'wsts_support_system_shortcode' );

/**
 * Render the list of tickets on the frontend.
 */
function wsts_render_frontend_ticket_list() {
    $user_id = get_current_user_id();
    $args = [
        'post_type' => 'support_ticket',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_wsts_user_id',
                'value' => $user_id,
                'compare' => '='
            ]
        ]
    ];
    $tickets = new WP_Query($args);
    ?>
    <div class="wsts-frontend-container container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php _e('My Support Tickets', 'wsts'); ?></h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal"><?php _e('Create New Ticket', 'wsts'); ?></button>
        </div>

        <table id="userTicketsTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th><?php _e('Subject', 'wsts'); ?></th>
                    <th><?php _e('Priority', 'wsts'); ?></th>
                    <th><?php _e('Status', 'wsts'); ?></th>
                    <th><?php _e('Last Updated', 'wsts'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $tickets->have_posts() ) : ?>
                    <?php while ( $tickets->have_posts() ) : $tickets->the_post(); ?>
                        <tr>
                            <td><a href="?ticket_id=<?php the_ID(); ?>"><?php the_title(); ?></a></td>
                            <td>
                                <?php 
                                $priority_terms = get_the_terms(get_the_ID(), 'ticket_priority');
                                if ($priority_terms && !is_wp_error($priority_terms)) {
                                    echo wsts_get_term_badge_html(array_shift($priority_terms));
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                $status_terms = get_the_terms(get_the_ID(), 'ticket_status');
                                if ($status_terms && !is_wp_error($status_terms)) {
                                    echo wsts_get_term_badge_html(array_shift($status_terms));
                                }
                                ?>
                            </td>
                            <td><?php echo get_the_modified_date(); ?></td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4"><?php _e('You have not created any tickets yet.', 'wsts'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1" aria-labelledby="newTicketModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newTicketModalLabel"><?php _e('Create a New Support Ticket', 'wsts'); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="wsts-new-ticket-form">
                <div id="wsts-form-notice" class="alert" style="display:none;"></div>
                <div class="mb-3">
                    <label for="ticket-subject" class="form-label"><?php _e('Subject', 'wsts'); ?></label>
                    <input type="text" class="form-control" id="ticket-subject" name="subject" required>
                </div>
                <div class="mb-3">
                    <label for="ticket-type" class="form-label"><?php _e('Regarding', 'wsts'); ?></label>
                    <select class="form-select" id="ticket-type" name="type">
                        <option value="service"><?php _e('General Service', 'wsts'); ?></option>
                        <?php if(class_exists('WooCommerce')): ?>
                        <option value="product"><?php _e('A Purchased Product', 'wsts'); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3" id="wsts-product-select-wrapper" style="display:none;">
                    <label for="ticket-product" class="form-label"><?php _e('Select Product', 'wsts'); ?></label>
                    <select class="form-select" id="ticket-product" name="product_id">
                        <option value=""><?php _e('Loading products...', 'wsts'); ?></option>
                    </select>
                </div>
                 <div class="mb-3">
                    <label for="ticket-priority" class="form-label"><?php _e('Priority', 'wsts'); ?></label>
                    <select class="form-select" id="ticket-priority" name="priority">
                        <?php
                        $priorities = get_terms(['taxonomy' => 'ticket_priority', 'hide_empty' => false]);
                        foreach($priorities as $priority) {
                            echo '<option value="'.$priority->term_id.'">'.esc_html($priority->name).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="wsts-submit-ticket"><?php _e('Submit Ticket', 'wsts'); ?></button>
          </div>
        </div>
      </div>
    </div>
    <?php
}

/**
 * Render the single ticket view on the frontend.
 */
function wsts_render_frontend_single_ticket($ticket_id) {
    $user_id = get_current_user_id();
    $ticket_user_id = get_post_meta($ticket_id, '_wsts_user_id', true);

    if (get_post_type($ticket_id) !== 'support_ticket' || $user_id != $ticket_user_id) {
        echo '<div class="alert alert-danger">' . __('Ticket not found or you do not have permission to view it.', 'wsts') . '</div>';
        return;
    }
    
    // Handle comment submission (replies)
    if (isset($_POST['submit']) && isset($_POST['comment_post_ID'])) {
        $answered_status = get_term_by('slug', 'answered', 'ticket_status');
        if($answered_status) {
            wp_set_post_terms($ticket_id, [$answered_status->term_id], 'ticket_status');
        }
        wp_update_post(['ID' => $ticket_id, 'post_modified' => current_time('mysql')]); // To update modified date
    }

    $ticket = get_post($ticket_id);
    $status_terms = get_the_terms($ticket_id, 'ticket_status');
    $status = ($status_terms && !is_wp_error($status_terms)) ? array_shift($status_terms) : null;
    $comments = get_comments(['post_id' => $ticket_id, 'orderby' => 'comment_date', 'order' => 'ASC']);
    ?>
    <div class="wsts-frontend-container container">
        <a href="<?php echo strtok(get_permalink(), '?'); ?>" class="btn btn-secondary mb-3"><?php _e('&laquo; Back to My Tickets', 'wsts'); ?></a>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo esc_html($ticket->post_title); ?></h4>
                <?php if($status){
                    echo wsts_get_term_badge_html($status);
                } ?>
            </div>
            <div class="card-body">
                <!-- Initial Ticket Message -->
                <?php if(empty($comments) && $ticket->post_content === ' '): ?>
                     <div class="alert alert-info"><?php _e('Please add your message below to submit your ticket.', 'wsts'); ?></div>
                <?php else: 
                    $ticket_creator = get_user_by('id', $ticket_user_id);
                ?>
                <div class="wsts-reply-item user-reply">
                    <div class="reply-header">
                        <strong><?php echo esc_html($ticket_creator->display_name); ?></strong>
                        <small class="text-muted"><?php echo human_time_diff(get_the_time('U', $ticket), current_time('timestamp')); ?> ago</small>
                    </div>
                    <div class="reply-body">
                        <?php echo wpautop($ticket->post_content); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Replies (Comments) -->
                <?php foreach ( $comments as $comment ) : 
                    $is_admin = user_can($comment->user_id, 'manage_options');
                ?>
                <div class="wsts-reply-item <?php echo $is_admin ? 'admin-reply' : 'user-reply'; ?>">
                    <div class="reply-header">
                        <strong><?php echo $comment->comment_author; ?></strong>
                        <small class="text-muted"><?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')); ?> ago</small>
                    </div>
                    <div class="reply-body">
                        <?php echo wpautop( $comment->comment_content ); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if(!$status || $status->slug !== 'closed'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><?php _e('Add a Reply', 'wsts'); ?></h5>
            </div>
            <div class="card-body">
                <?php 
                if (empty($comments) && $ticket->post_content === ' ') {
                    // Use wp_editor for the first message
                    wp_editor('', 'comment', ['textarea_name' => 'comment']);
                    echo '<input name="submit" type="submit" id="submit" class="btn btn-primary mt-3" value="Submit Ticket">';
                    echo '<input type="hidden" name="comment_post_ID" value="'.$ticket_id.'" id="comment_post_ID">';
                    echo '<input type="hidden" name="comment_parent" id="comment_parent" value="0">';
                } else {
                    comment_form([], $ticket_id); 
                }
                ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info mt-4"><?php _e('This ticket is closed. You cannot add new replies.', 'wsts'); ?></div>
        <?php endif; ?>
    </div>
    <?php
}


// =============================================================================
// AJAX Handlers
// =============================================================================

/**
 * AJAX handler for fetching user's purchased products.
 */
function wsts_get_user_products() {
    check_ajax_referer( 'wsts_nonce', 'nonce' );

    if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
        wp_send_json_error( ['message' => 'Invalid request.'] );
    }

    $user = wp_get_current_user();
    $customer = new WC_Customer( $user->ID );
    $orders = wc_get_orders( array(
        'customer' => $customer->get_id(),
        'status'   => array( 'wc-completed', 'wc-processing' ),
        'limit'    => -1
    ) );

    $products = [];
    if ( $orders ) {
        foreach ( $orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $product = $item->get_product();
                if ( $product && !isset($products[$product->get_id()]) ) {
                    $products[$product->get_id()] = [
                        'id'   => $product->get_id(),
                        'name' => $product->get_name(),
                    ];
                }
            }
        }
    }

    wp_send_json_success( array_values($products) );
}
add_action( 'wp_ajax_wsts_get_user_products', 'wsts_get_user_products' );


/**
 * AJAX handler for creating a new ticket DRAFT.
 */
function wsts_create_new_ticket() {
    check_ajax_referer( 'wsts_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( ['message' => 'You must be logged in.'] );
    }

    $subject = sanitize_text_field( $_POST['subject'] );
    $product_id = isset($_POST['product_id']) ? intval( $_POST['product_id'] ) : 0;
    $priority_id = intval( $_POST['priority'] );
    $page_url = esc_url_raw( $_POST['page_url'] );

    if ( empty( $subject ) || empty($priority_id) ) {
        wp_send_json_error( ['message' => 'Please fill in all required fields.'] );
    }
    
    $user_id = get_current_user_id();

    // Find an admin to be the 'author' of the post to bypass capability issues.
    $admins = get_users( ['role' => 'administrator', 'number' => 1] );
    $author_id = ( !empty($admins) ) ? $admins[0]->ID : 1; // Default to user 1 if no admin found

    $ticket_data = [
        'post_title'   => $subject,
        'post_content' => ' ', // Content will be added as the first comment
        'post_status'  => 'pending', // Create as pending
        'post_author'  => $author_id,
        'post_type'    => 'support_ticket',
    ];

    $ticket_id = wp_insert_post( $ticket_data );

    if ( !is_wp_error($ticket_id) ) {
        update_post_meta($ticket_id, '_wsts_user_id', $user_id);
        update_post_meta($ticket_id, '_wsts_product_id', $product_id);
        
        wp_set_post_terms($ticket_id, [$priority_id], 'ticket_priority');
        $pending_status = get_term_by('slug', 'pending', 'ticket_status');
        if($pending_status) {
            wp_set_post_terms($ticket_id, [$pending_status->term_id], 'ticket_status');
        }
        
        $redirect_url = add_query_arg('ticket_id', $ticket_id, $page_url);
        wp_send_json_success( ['message' => 'Ticket created... redirecting.', 'redirect_url' => $redirect_url] );
    } else {
        wp_send_json_error( ['message' => 'There was an error creating your ticket. Please try again.'] );
    }
}
add_action( 'wp_ajax_wsts_create_new_ticket', 'wsts_create_new_ticket' );

// =============================================================================
// Email Notifications
// =============================================================================
/**
 * Send notification when a ticket is published.
 */
function wsts_notify_on_publish( $new_status, $old_status, $post ) {
    if ( $post->post_type !== 'support_ticket' || $new_status !== 'publish' || $old_status === 'publish' ) {
        return;
    }

    $ticket_user_id = get_post_meta($post->ID, '_wsts_user_id', true);
    $ticket_user = get_user_by('id', $ticket_user_id);

    // Set status to Open
    $open_status = get_term_by('slug', 'open', 'ticket_status');
    if ($open_status) {
        wp_set_post_terms($post->ID, [$open_status->term_id], 'ticket_status');
    }

    // Notify User
    $subject_user = sprintf( 'Your Support Ticket #%d is now open', $post->ID );
    $message_user = "Hello {$ticket_user->display_name},\n\n";
    $message_user .= "Your support ticket '{$post->post_title}' has been approved and is now open. Our team will get back to you shortly.\n\n";
    wp_mail( $ticket_user->user_email, $subject_user, $message_user );
}
add_action( 'transition_post_status', 'wsts_notify_on_publish', 10, 3 );


/**
 * Send email notification when a new reply (comment) is added.
 */
function wsts_notify_on_new_reply( $comment_id, $comment_approved, $commentdata ) {
    if ( 1 !== $comment_approved ) {
        return;
    }

    $post_id = $commentdata['comment_post_ID'];
    if ( get_post_type($post_id) !== 'support_ticket' ) {
        return;
    }

    $commenter = get_user_by('id', $commentdata['user_id']);
    $ticket = get_post($post_id);
    $ticket_user_id = get_post_meta($post_id, '_wsts_user_id', true);
    $ticket_user = get_user_by('id', $ticket_user_id);
    $ticket_url_admin = admin_url('post.php?post=' . $post_id . '&action=edit');
    $page_url = get_permalink( get_page_by_path( 'support' ) ); // Assumes your page slug is 'support'
    $ticket_url_user = add_query_arg('ticket_id', $post_id, $page_url);

    // Check if this is the first comment
    $comments_count = get_comments_number($post_id);

    // If an admin/agent replied, notify the user.
    if ( user_can($commenter, 'manage_options') ) {
        $subject = sprintf('A reply has been added to your ticket #%d', $post_id);
        $message = "Hello {$ticket_user->display_name},\n\n";
        $message .= "A new reply has been added to your support ticket: '{$ticket->post_title}'.\n\n";
        $message .= "Reply:\n" . strip_tags($commentdata['comment_content']) . "\n\n";
        $message .= "You can view the full conversation here: {$ticket_url_user}\n";
        wp_mail($ticket_user->user_email, $subject, $message);
    } 
    // If the user replied
    else {
        // If it's the first reply, it's a new ticket submission.
        if ($comments_count == 1) {
            // Notify Admin
            $admin_email = get_option('admin_email');
            $subject_admin = sprintf( 'New Support Ticket Submitted: #%d - %s', $post_id, $ticket->post_title );
            $message_admin = "A new support ticket has been submitted by {$ticket_user->display_name} and is awaiting review.\n\n";
            $message_admin .= "Subject: {$ticket->post_title}\n\n";
            $message_admin .= "Message:\n" . strip_tags($commentdata['comment_content']) . "\n\n";
            $message_admin .= "Review and publish the ticket here: {$ticket_url_admin}\n";
            wp_mail( $admin_email, $subject_admin, $message_admin );

        } 
        // Otherwise, it's a reply to an existing ticket.
        else {
            $assigned_to_id = get_post_meta($post_id, '_wsts_assigned_to', true);
            $notify_email = get_option('admin_email');

            if ($assigned_to_id) {
                $agent = get_user_by('id', $assigned_to_id);
                if ($agent) {
                    $notify_email = $agent->user_email;
                }
            }
            
            $subject = sprintf('A new reply has been added to ticket #%d', $post_id);
            $message = "Hello,\n\n";
            $message .= "A new reply has been added by {$ticket_user->display_name} to ticket #{$post_id}: '{$ticket->post_title}'.\n\n";
            $message .= "Reply:\n" . strip_tags($commentdata['comment_content']) . "\n\n";
            $message .= "You can view the full conversation here: {$ticket_url_admin}\n";
            wp_mail($notify_email, $subject, $message);
        }
    }
}
add_action( 'comment_post', 'wsts_notify_on_new_reply', 10, 3 );
