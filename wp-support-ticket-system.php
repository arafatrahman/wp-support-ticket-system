<?php
/**
 * Plugin Name:       WP Support Tickets System
 * Description:       A modern support ticket system for WordPress and WooCommerce using Custom Post Types.
 * Version:           1.0.0
 * Author:            Arafat Rahman
 * Author URI:        https://webbird.co.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wsts
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define constants
define( 'WSTS_VERSION', '1.7.0' );
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

    $statuses = ['Open' => '#0d6efd', 'Answered' => '#198754', 'Closed' => '#6c757d'];
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
 * Enqueue admin scripts and styles.
 */
function wsts_enqueue_admin_scripts($hook) {
    // Enqueue color picker for taxonomy pages
    if ( 'term.php' === $hook || 'edit-tags.php' === $hook ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    // Enqueue custom admin styles
    wp_enqueue_style( 'wsts-admin-style', WSTS_PLUGIN_URL . 'assets/css/style.css', array(), WSTS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'wsts_enqueue_admin_scripts' );


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
