<?php
// includes/views/frontend/single-ticket.php

?>
<div class="wsts-frontend-container container">
    <a href="<?php echo strtok(get_permalink(), '?'); ?>" class="btn btn-secondary mb-3"><?php _e('&laquo; Back to My Tickets', 'wsts'); ?></a>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?php echo esc_html($ticket->post_title); ?></h4>
            <?php if($status){
                echo WSTS_Taxonomy_Model::get_term_badge_html($status);
            } ?>
        </div>
        <div class="card-body">
            <!-- Initial Ticket Message -->
            <?php if(empty($comments) && $ticket->post_content === ' '): ?>
                <div class="alert alert-info"><?php _e('Please add your message below to submit your ticket.', 'wsts'); ?></div>
            <?php else: ?>
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