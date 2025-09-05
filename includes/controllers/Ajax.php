<?php
// includes/controllers/Ajax.php

if ( ! class_exists( 'WSTS_Ajax_Controller' ) ) {
    class WSTS_Ajax_Controller {
        public function __construct() {
            add_action( 'wp_ajax_wsts_get_user_products', [$this, 'get_user_products'] );
            add_action( 'wp_ajax_wsts_create_new_ticket', [$this, 'create_new_ticket'] );
            add_action( 'wp_ajax_wsts_get_single_ticket_html', [$this, 'get_single_ticket_html'] );
            add_action( 'wp_ajax_wsts_add_comment', [$this, 'add_comment'] );
            add_action( 'wp_ajax_wsts_approve_ticket', [$this, 'approve_ticket'] );
        }

        public function get_user_products() {
            check_ajax_referer( 'wsts_nonce', 'nonce' );

            if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
                wp_send_json_error( ['message' => 'Invalid request.'] );
            }

            $user = wp_get_current_user();
            $customer = new WC_Customer( $user->ID );
            $orders = wc_get_orders( [
                'customer' => $customer->get_id(),
                'status' => ['wc-completed', 'wc-processing'],
                'limit' => -1
            ] );

            $products = [];
            if ( $orders ) {
                foreach ( $orders as $order ) {
                    foreach ( $order->get_items() as $item ) {
                        $product = $item->get_product();
                        if ( $product && !isset( $products[$product->get_id()] ) ) {
                            $products[$product->get_id()] = [
                                'id' => $product->get_id(),
                                'name' => $product->get_name(),
                            ];
                        }
                    }
                }
            }

            wp_send_json_success( array_values( $products ) );
        }

        public function create_new_ticket() {
            check_ajax_referer( 'wsts_nonce', 'nonce' );

            if ( ! is_user_logged_in() ) {
                wp_send_json_error( ['message' => 'You must be logged in.'] );
            }

            $subject = sanitize_text_field( $_POST['subject'] );
            $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
            $priority_id = isset( $_POST['wsts_priority'] ) ? intval( $_POST['wsts_priority'] ) : 0;
            $description = wp_kses_post( $_POST['description'] );

            if ( empty( $subject ) || empty( $description ) ) {
                wp_send_json_error( ['message' => 'Please fill in all required fields.'] );
            }

            $user_id = get_current_user_id();
            $admins = get_users( ['role' => 'administrator', 'number' => 1] );
            $author_id = ( ! empty( $admins ) ) ? $admins[0]->ID : 1;

            $data = [
                'subject' => $subject,
                'content' => $description,
                'product_id' => $product_id,
                'priority_id' => $priority_id,
                'user_id' => $user_id,
                'author_id' => $author_id
            ];

            $ticket_id = WSTS_Ticket_Model::create_ticket_draft( $data );

            if ( ! is_wp_error( $ticket_id ) ) {
                wp_send_json_success( ['ticket_id' => $ticket_id, 'message' => 'Ticket created... opening.'] );
            } else {
                wp_send_json_error( ['message' => 'Error creating ticket.'] );
            }
        }

        public function get_single_ticket_html() {
           check_ajax_referer('wsts_nonce', 'nonce');
            $ticket_id = intval($_POST['ticket_id']);
            if (!$ticket_id || !get_post($ticket_id) || get_post_type($ticket_id) !== 'support_ticket') {
                wp_send_json_error(['message' => 'Invalid ticket ID']);
                return;
            }
            // Fetch ticket data and return
            wp_send_json_success(['data' => 'Ticket data here']);
        }

        public function add_comment() {
            check_ajax_referer( 'wsts_nonce', 'nonce' );

            if ( ! is_user_logged_in() ) {
                wp_send_json_error( ['message' => 'You must be logged in.'] );
            }

            $ticket_id = intval( $_POST['comment_post_ID'] );
            $user_id = get_current_user_id();
            $ticket_user_id = get_post_meta( $ticket_id, '_wsts_user_id', true );

            if ( get_post_type( $ticket_id ) !== 'support_ticket' || ( $user_id != $ticket_user_id && ! user_can( $user_id, 'manage_options' ) ) ) {
                wp_send_json_error( ['message' => 'Invalid permission.'] );
            }

            $comment_content = wp_kses_post( $_POST['comment'] );
            $comment_parent = intval( $_POST['comment_parent'] );

            $user = get_user_by( 'id', $user_id );

            $comment_data = [
                'comment_post_ID' => $ticket_id,
                'comment_author' => $user->display_name,
                'comment_author_email' => $user->user_email,
                'comment_content' => $comment_content,
                'comment_parent' => $comment_parent,
                'user_id' => $user_id,
                'comment_approved' => 1,
            ];

            $comment_id = wp_insert_comment( $comment_data );

            if ( ! is_wp_error( $comment_id ) ) {
                $answered_status = get_term_by( 'slug', 'answered', 'ticket_status' );
                if ( $answered_status ) {
                    wp_set_post_terms( $ticket_id, [ $answered_status->term_id ], 'ticket_status' );
                }
                wp_update_post( [ 'ID' => $ticket_id, 'post_modified' => current_time( 'mysql' ) ] );
                wp_send_json_success();
            } else {
                wp_send_json_error( ['message' => 'Error adding comment.'] );
            }
        }

        public function approve_ticket() {
            check_ajax_referer( 'wsts_nonce', 'nonce' );

            if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( ['message' => 'You do not have permission to approve tickets.'] );
            }

            $ticket_id = intval( $_POST['ticket_id'] );
            if ( get_post_type( $ticket_id ) !== 'support_ticket' ) {
                wp_send_json_error( ['message' => 'Invalid ticket ID.'] );
            }

            $open_status = get_term_by( 'slug', 'open', 'ticket_status' );
            if ( ! $open_status ) {
                wp_send_json_error( ['message' => 'Open status not found.'] );
            }

            $result = wp_set_post_terms( $ticket_id, [ $open_status->term_id ], 'ticket_status' );
            if ( ! is_wp_error( $result ) ) {
                wp_update_post( [ 'ID' => $ticket_id, 'post_modified' => current_time( 'mysql' ) ] );
                wp_send_json_success( ['message' => 'Ticket approved successfully.'] );
            } else {
                wp_send_json_error( ['message' => 'Error approving ticket.'] );
            }
        }
    }
}