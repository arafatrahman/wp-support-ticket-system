<?php
// includes/views/frontend/ticket-list.php

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
                                echo WSTS_Taxonomy_Model::get_term_badge_html(array_shift($priority_terms));
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_terms = get_the_terms(get_the_ID(), 'ticket_status');
                            if ($status_terms && !is_wp_error($status_terms)) {
                                echo WSTS_Taxonomy_Model::get_term_badge_html(array_shift($status_terms));
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