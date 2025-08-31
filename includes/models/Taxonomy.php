<?php
// includes/models/Taxonomy.php

class WSTS_Taxonomy_Model {

    public static function register_taxonomies() {
        // Priority Taxonomy (Hierarchical)
        $priority_labels = [
            'name' => _x( 'Priorities', 'taxonomy general name', 'wsts' ),
            'singular_name' => _x( 'Priority', 'taxonomy singular name', 'wsts' ),
            'search_items' => __( 'Search Priorities', 'wsts' ),
            'all_items' => __( 'All Priorities', 'wsts' ),
            'parent_item' => __( 'Parent Priority', 'wsts' ),
            'parent_item_colon' => __( 'Parent Priority:', 'wsts' ),
            'edit_item' => __( 'Edit Priority', 'wsts' ),
            'update_item' => __( 'Update Priority', 'wsts' ),
            'add_new_item' => __( 'Add New Priority', 'wsts' ),
            'new_item_name' => __( 'New Priority Name', 'wsts' ),
            'menu_name' => __( 'Priorities', 'wsts' ),
        ];
        $priority_args = [
            'hierarchical' => true,
            'labels' => $priority_labels,
            'show_ui' => true,
            'show_admin_column' => false, // We use a custom column
            'query_var' => true,
            'rewrite' => ['slug' => 'ticket-priority'],
            'show_in_rest' => true,
        ];
        register_taxonomy( 'ticket_priority', ['support_ticket'], $priority_args );

        // Status Taxonomy (Hierarchical)
        $status_labels = [
            'name' => _x( 'Statuses', 'taxonomy general name', 'wsts' ),
            'singular_name' => _x( 'Status', 'taxonomy singular name', 'wsts' ),
            'search_items' => __( 'Search Statuses', 'wsts' ),
            'all_items' => __( 'All Statuses', 'wsts' ),
            'parent_item' => __( 'Parent Status', 'wsts' ),
            'parent_item_colon' => __( 'Parent Status:', 'wsts' ),
            'edit_item' => __( 'Edit Status', 'wsts' ),
            'update_item' => __( 'Update Status', 'wsts' ),
            'add_new_item' => __( 'Add New Status', 'wsts' ),
            'new_item_name' => __( 'New Status Name', 'wsts' ),
            'menu_name' => __( 'Statuses', 'wsts' ),
        ];
        $status_args = [
            'hierarchical' => true,
            'labels' => $status_labels,
            'show_ui' => true,
            'show_admin_column' => false, // We use a custom column
            'query_var' => true,
            'rewrite' => ['slug' => 'ticket-status'],
            'show_in_rest' => true,
        ];
        register_taxonomy( 'ticket_status', ['support_ticket'], $status_args );
    }

    public static function add_default_priorities() {
        $priorities = ['Low' => '#0dcaf0', 'Medium' => '#ffc107', 'High' => '#dc3545'];
        foreach ($priorities as $priority => $color) {
            if (!term_exists($priority, 'ticket_priority')) {
                $term = wp_insert_term($priority, 'ticket_priority');
                if(!is_wp_error($term)) {
                    update_term_meta($term['term_id'], 'wsts_color', $color);
                }
            }
        }
    }

    public static function add_default_statuses() {
        $statuses = ['Open' => '#0d6efd', 'Answered' => '#198754', 'Closed' => '#6c757d', 'Pending' => '#ff8c00'];
        foreach ($statuses as $status => $color) {
            if (!term_exists($status, 'ticket_status')) {
                $term = wp_insert_term($status, 'ticket_status');
                if(!is_wp_error($term)) {
                    update_term_meta($term['term_id'], 'wsts_color', $color);
                }
            }
        }
    }

    public static function get_term_badge_html($term) {
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
}