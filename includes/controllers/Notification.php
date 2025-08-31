<?php
// includes/controllers/Notification.php

class WSTS_Notification_Controller {

    public function __construct() {
        add_action( 'transition_post_status', [$this, 'notify_on_publish'], 10, 3 );
        add_action( 'comment_post', [$this, 'notify_on_new_reply'], 10, 3 );
    }

    public function notify_on_publish( $new_status, $old_status, $post ) {
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

    public function notify_on_new_reply( $comment_id, $comment_approved, $commentdata ) {
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
}