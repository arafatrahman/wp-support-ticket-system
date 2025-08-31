<?php
// includes/controllers/Frontend.php

class WSTS_Frontend_Controller {

    public function handle_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You must be logged in to view your support tickets.', 'wsts' ) . '</p>';
        }

        ob_start();

        if(isset($_GET['ticket_id'])) {
            $this->render_single_ticket(intval($_GET['ticket_id']));
        } else {
            $this->render_ticket_list();
        }

        return ob_get_clean();
    }

    private function render_ticket_list() {
        $user_id = get_current_user_id();
        $tickets = WSTS_Ticket_Model::get_user_tickets($user_id);
        $priorities = get_terms(['taxonomy' => 'ticket_priority', 'hide_empty' => false]);

        include WSTS_PLUGIN_DIR . 'includes/views/frontend/ticket-list.php';
    }

    private function render_single_ticket($ticket_id) {
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
        $ticket_creator = get_user_by('id', $ticket_user_id);

        include WSTS_PLUGIN_DIR . 'includes/views/frontend/single-ticket.php';
    }
}