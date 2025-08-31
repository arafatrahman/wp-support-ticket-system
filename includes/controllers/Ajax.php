<?php
// includes/controllers/Ajax.php

class WSTS_Ajax_Controller {

    public function __construct() {
        add_action( 'wp_ajax_wsts_get_user_products', [$this, 'get_user_products'] );
        add_action( 'wp_ajax_wsts_create_new_ticket', [$this, 'create_new_ticket'] );
    }

    public function get_user_products() {
        check_ajax_referer( 'wsts_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( ['message' => 'Invalid request.'] );
        }

        $user = wp_get_current_user();
        $customer = new WC_Customer( $user->ID );
        $orders = wc_get_orders( array(
            'customer' => $customer->get_id(),
            'status' => array( 'wc-completed', 'wc-processing' ),
            'limit' => -1
        ) );

        $products = [];
        if ( $orders ) {
            foreach ( $orders as $order ) {
                foreach ( $order->get_items() as $item ) {
                    $product = $item->get_product();
                    if ( $product && !isset($products[$product->get_id()]) ) {
                        $products[$product->get_id()] = [
                            'id' => $product->get_id(),
                            'name' => $product->get_name(),
                        ];
                    }
                }
            }
        }

        wp_send_json_success( array_values($products) );
    }

    public function create_new_ticket() {
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

        $data = [
            'subject' => $subject,
            'product_id' => $product_id,
            'priority_id' => $priority_id,
            'user_id' => $user_id,
            'author_id' => $author_id
        ];

        $ticket_id = WSTS_Ticket_Model::create_ticket_draft($data);

        if ($ticket_id) {
            $redirect_url = add_query_arg('ticket_id', $ticket_id, $page_url);
            wp_send_json_success( ['message' => 'Ticket created... redirecting.', 'redirect_url' => $redirect_url] );
        } else {
            wp_send_json_error( ['message' => 'There was an error creating your ticket. Please try again.'] );
        }
    }
}