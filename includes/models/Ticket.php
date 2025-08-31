<?php
// includes/models/Ticket.php

class WSTS_Ticket_Model {

    public static function register_cpt() {
        $labels = [
            'name' => _x( 'Support Tickets', 'post type general name', 'wsts' ),
            'singular_name' => _x( 'Support Ticket', 'post type singular name', 'wsts' ),
            'menu_name' => _x( 'Support Tickets', 'admin menu', 'wsts' ),
            'name_admin_bar' => _x( 'Support Ticket', 'add new on admin bar', 'wsts' ),
            'add_new' => _x( 'Add New', 'ticket', 'wsts' ),
            'add_new_item' => __( 'Add New Ticket', 'wsts' ),
            'new_item' => __( 'New Ticket', 'wsts' ),
            'edit_item' => __( 'Edit Ticket', 'wsts' ),
            'view_item' => __( 'View Ticket', 'wsts' ),
            'all_items' => __( 'All Tickets', 'wsts' ),
            'search_items' => __( 'Search Tickets', 'wsts' ),
            'parent_item_colon' => __( 'Parent Tickets:', 'wsts' ),
            'not_found' => __( 'No tickets found.', 'wsts' ),
            'not_found_in_trash' => __( 'No tickets found in Trash.', 'wsts' ),
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'support-ticket'],
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-sos',
            'supports' => ['title', 'editor', 'comments', 'author'],
            'show_in_rest' => true, // Enable Gutenberg editor
        ];

        register_post_type( 'support_ticket', $args );
    }

    public static function get_user_tickets($user_id) {
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
        return new WP_Query($args);
    }

    public static function create_ticket_draft($data) {
        $ticket_data = [
            'post_title' => $data['subject'],
            'post_content' => ' ', // Content will be added as the first comment
            'post_status' => 'pending', // Create as pending
            'post_author' => $data['author_id'],
            'post_type' => 'support_ticket',
        ];

        $ticket_id = wp_insert_post( $ticket_data );

        if ( !is_wp_error($ticket_id) ) {
            update_post_meta($ticket_id, '_wsts_user_id', $data['user_id']);
            update_post_meta($ticket_id, '_wsts_product_id', $data['product_id']);
            wp_set_post_terms($ticket_id, [$data['priority_id']], 'ticket_priority');
            $pending_status = get_term_by('slug', 'pending', 'ticket_status');
            if($pending_status) {
                wp_set_post_terms($ticket_id, [$pending_status->term_id], 'ticket_status');
            }
            return $ticket_id;
        }
        return false;
    }
}