<?php
// includes/views/frontend/ticket-list.php

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure user is logged in
if ( ! is_user_logged_in() ) {
    echo '<p>' . esc_html__( 'You must be logged in to view your support tickets.', 'wsts' ) . '</p>';
    return;
}

// Enqueue styles and scripts
wp_enqueue_style( 'wsts-ticket-list', WSTS_PLUGIN_URL . 'assets/css/wsts-ticket-list.css', [], '1.0.5' );
wp_enqueue_script( 'wsts-ticket-list', WSTS_PLUGIN_URL . 'assets/js/wsts-ticket-list.js', ['jquery'], '1.0.5', true );
wp_enqueue_editor(); // Enqueue TinyMCE and dependencies

// Localize script with all data
$user_id = get_current_user_id();
$is_admin = current_user_can( 'manage_options' );
$tickets_query = $is_admin ? new WP_Query([
    'post_type' => 'support_ticket',
    'posts_per_page' => -1,
    'post_status' => 'any',
]) : WSTS_Ticket_Model::get_user_tickets( $user_id );

$priorities = get_terms( ['taxonomy' => 'ticket_priority', 'hide_empty' => false] );
$statuses = get_terms( ['taxonomy' => 'ticket_status', 'hide_empty' => false] );
$ticket_data = [];
if ( $tickets_query->have_posts() ) {
    while ( $tickets_query->have_posts() ) {
        $tickets_query->the_post();
        $ticket_id = get_the_ID();
        $status_terms = get_the_terms( $ticket_id, 'ticket_status' );
        $status = ( $status_terms && ! is_wp_error( $status_terms ) ) ? array_shift( $status_terms ) : null;
        $priority_terms = get_the_terms( $ticket_id, 'ticket_priority' );
        $priority = ( $priority_terms && ! is_wp_error( $priority_terms ) ) ? array_shift( $priority_terms ) : null;
        $product_id = get_post_meta( $ticket_id, '_wsts_product_id', true );
        $product_name = $product_id ? get_the_title( $product_id ) : 'General Service';
        $ticket_owner_id = get_post_meta( $ticket_id, '_wsts_user_id', true );
        $ticket_data[] = [
            'id' => $ticket_id,
            'subject' => get_the_title(),
            'requester' => get_userdata( $ticket_owner_id )->user_email,
            'status' => $status ? $status->slug : 'pending',
            'status_name' => $status ? $status->name : 'Pending',
            'priority' => $priority ? $priority->slug : 'low',
            'priority_name' => $priority ? $priority->name : 'Low',
            'created' => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago',
            'department' => $product_id ? 'product' : 'general',
            'description' => get_the_content(),
            'comments' => get_comments( ['post_id' => $ticket_id] ),
            'owner_id' => $ticket_owner_id,
        ];
    }
    wp_reset_postdata();
}

wp_localize_script( 'wsts-ticket-list', 'wsts_ajax', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'wsts_nonce' ),
    'is_admin' => $is_admin,
    'tickets' => $ticket_data,


] );

// Calculate stats (for admins only)
$base_args = [
    'post_type' => 'support_ticket',
    'posts_per_page' => -1,
    'post_status' => 'any',
];
$total_query = new WP_Query( $base_args );
$total = $is_admin ? $total_query->found_posts : count( array_filter( $ticket_data, fn($t) => $t['owner_id'] == $user_id ) );
$open_args = array_merge( $base_args, [
    'tax_query' => [['taxonomy' => 'ticket_status', 'field' => 'slug', 'terms' => 'open']],
]);
$open_query = new WP_Query( $open_args );
$open = $is_admin ? $open_query->found_posts : count( array_filter( $ticket_data, fn($t) => $t['owner_id'] == $user_id && $t['status'] == 'open' ) );
$pending_args = array_merge( $base_args, [
    'tax_query' => [['taxonomy' => 'ticket_status', 'field' => 'slug', 'terms' => 'pending']],
]);
$pending_query = new WP_Query( $pending_args );
$pending = $is_admin ? $pending_query->found_posts : count( array_filter( $ticket_data, fn($t) => $t['owner_id'] == $user_id && $t['status'] == 'pending' ) );
$closed_args = array_merge( $base_args, [
    'tax_query' => [['taxonomy' => 'ticket_status', 'field' => 'slug', 'terms' => 'closed']],
    'date_query' => [
        [
            'after' => date( 'Y-m-d' ) . ' 00:00:00',
            'before' => date( 'Y-m-d' ) . ' 23:59:59',
            'inclusive' => true,
            'column' => 'post_modified',
        ],
    ],
]);
$closed_today_query = new WP_Query( $closed_args );
$closed_today = $is_admin ? $closed_today_query->found_posts : count( array_filter( $ticket_data, fn($t) => $t['owner_id'] == $user_id && $t['status'] == 'closed' ) );

// Pagination setup
$per_page = 5;
$page = isset( $_GET['tpage'] ) ? max( 1, intval( $_GET['tpage'] ) ) : 1;
$total_pages = ceil( count( $ticket_data ) / $per_page );
$ticket_data_paginated = array_slice( $ticket_data, ( $page - 1 ) * $per_page, $per_page );
?>

<div class="wsts_container">
    <div class="wsts_header-content">
        <div class="wsts_logo"><?php esc_html_e( 'SupportHub', 'wsts' ); ?></div>
        <div class="wsts_search-bar">
            <input type="text" placeholder="<?php esc_attr_e( 'Search tickets...', 'wsts' ); ?>">
        </div>
        <div class="wsts_user-actions">
            <button id="wsts_header-new-ticket"><?php esc_html_e( 'New Ticket', 'wsts' ); ?></button>
        </div>
    </div>

    <div class="wsts_main-content">
        <div class="wsts_sidebar">
            <h2><?php esc_html_e( 'Filters', 'wsts' ); ?></h2>
            <div class="wsts_filters">
                <div>
                    <label for="wsts_status"><?php esc_html_e( 'Status', 'wsts' ); ?></label>
                    <select id="wsts_status">
                        <option value="all"><?php esc_html_e( 'All Statuses', 'wsts' ); ?></option>
                        <?php foreach ( $statuses as $status ) : ?>
                            <option value="<?php echo esc_attr( $status->slug ); ?>"><?php echo esc_html( $status->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="wsts_priority"><?php esc_html_e( 'Priority', 'wsts' ); ?></label>
                    <select id="wsts_priority">
                        <option value="all"><?php esc_html_e( 'All Priorities', 'wsts' ); ?></option>
                        <?php foreach ( $priorities as $priority ) : ?>
                            <option value="<?php echo esc_attr( $priority->slug ); ?>"><?php echo esc_html( $priority->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="wsts_date"><?php esc_html_e( 'Date', 'wsts' ); ?></label>
                    <input type="date" id="wsts_date">
                </div>
                <div>
                    <label for="wsts_search"><?php esc_html_e( 'Keyword', 'wsts' ); ?></label>
                    <input type="text" id="wsts_search" placeholder="<?php esc_attr_e( 'Enter keyword...', 'wsts' ); ?>">
                </div>
                <button><?php esc_html_e( 'Apply Filters', 'wsts' ); ?></button>
            </div>
        </div>

        <div class="wsts_content">
            <div class="wsts_stats-cards">
                <div class="wsts_stat-card">
                    <h3><?php esc_html_e( 'Open Tickets', 'wsts' ); ?></h3>
                    <div class="wsts_number"><?php echo esc_html( $open ); ?></div>
                </div>
                <div class="wsts_stat-card">
                    <h3><?php esc_html_e( 'Pending Tickets', 'wsts' ); ?></h3>
                    <div class="wsts_number"><?php echo esc_html( $pending ); ?></div>
                </div>
                <div class="wsts_stat-card">
                    <h3><?php esc_html_e( 'Closed Today', 'wsts' ); ?></h3>
                    <div class="wsts_number"><?php echo esc_html( $closed_today ); ?></div>
                </div>
                <div class="wsts_stat-card">
                    <h3><?php esc_html_e( 'Total Tickets', 'wsts' ); ?></h3>
                    <div class="wsts_number"><?php echo esc_html( $total ); ?></div>
                </div>
            </div>

            <div class="wsts_ticket-list">
                <div class="wsts_ticket-list-header">
                    <h2><?php esc_html_e( 'Recent Tickets', 'wsts' ); ?></h2>
                    <button class="wsts_new-ticket-btn" id="wsts_new-ticket-btn">
                        <i class="fas fa-plus"></i> <?php esc_html_e( 'New Ticket', 'wsts' ); ?>
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Subject', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Requester', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Priority', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Created', 'wsts' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'wsts' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wsts_tickets-table-body">
                        <?php if ( ! empty( $ticket_data_paginated ) ) : ?>
                            <?php foreach ( $ticket_data_paginated as $ticket ) : ?>
                                <tr data-id="<?php echo esc_attr( $ticket['id'] ); ?>">
                                    <td>#<?php echo esc_html( $ticket['id'] ); ?></td>
                                    <td><?php echo esc_html( $ticket['subject'] ); ?></td>
                                    <td><?php echo esc_html( $ticket['requester'] ); ?></td>
                                    <td><span class="wsts_status wsts_status-<?php echo esc_attr( $ticket['status'] ); ?>"><?php echo esc_html( $ticket['status_name'] ); ?></span></td>
                                    <td class="wsts_priority-<?php echo esc_attr( $ticket['priority'] ); ?>"><?php echo esc_html( $ticket['priority_name'] ); ?></td>
                                    <td><?php echo esc_html( $ticket['created'] ); ?></td>
                                    <td><button class="wsts_action-btn" data-id="<?php echo esc_attr( $ticket['id'] ); ?>"><i class="fas fa-eye"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="7"><?php esc_html_e( 'You have not created any tickets yet.', 'wsts' ); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="wsts_pagination">
                    <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                        <button class="<?php echo $i === $page ? 'wsts_active' : ''; ?>" onclick="window.location.href='?tpage=<?php echo $i; ?>'"><?php echo $i; ?></button>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Ticket Modal -->
<div class="wsts_modal" id="wsts_new-ticket-modal">
    <div class="wsts_modal-content">
        <div class="wsts_modal-header">
            <h2><?php esc_html_e( 'Create New Ticket', 'wsts' ); ?></h2>
            <button class="wsts_close-btn">&times;</button>
        </div>
        <div class="wsts_modal-body">
            <form id="wsts_new-ticket-form">
                <div id="wsts_form-notice" style="display:none; color: red; margin-bottom: 20px;"></div>
                <div class="wsts_form-group">
                    <label for="wsts_subject"><?php esc_html_e( 'Subject', 'wsts' ); ?></label>
                    <input type="text" id="wsts_subject" required>
                </div>
                <div class="wsts_form-group">
                    <label for="wsts_ticket-type"><?php esc_html_e( 'Regarding', 'wsts' ); ?></label>
                    <select id="wsts_ticket-type" required>
                    <option value="general"><?php esc_html_e( 'General Inquiry', 'wsts' ); ?></option>
                    <option value="support"><?php esc_html_e( 'Technical Support', 'wsts' ); ?></option>
                    <option value="billing"><?php esc_html_e( 'Billing & Payments', 'wsts' ); ?></option>
                    <option value="partnership"><?php esc_html_e( 'Partnership Request', 'wsts' ); ?></option>
                    <option value="feedback"><?php esc_html_e( 'Feedback / Suggestions', 'wsts' ); ?></option>
                    <option value="other"><?php esc_html_e( 'Other', 'wsts' ); ?></option>
                        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                            <option value="product"><?php esc_html_e( 'A Purchased Product', 'wsts' ); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="wsts_form-group" id="wsts_product-select-wrapper" style="display:none;">
                    <label for="wsts_ticket-product"><?php esc_html_e( 'Select Product', 'wsts' ); ?></label>
                    <select id="wsts_ticket-product">
                        <option value=""><?php esc_html_e( 'Loading products...', 'wsts' ); ?></option>
                    </select>
                </div>
                <div class="wsts_form-group">
                    <label for="wsts_priority"><?php esc_html_e( 'Priority', 'wsts' ); ?></label>
                    <select id="wsts_priority" required>
                        <option value=""><?php esc_html_e( 'Select Priority', 'wsts' ); ?></option>
                        <?php foreach ( $priorities as $priority ) : ?>
                            <option value="<?php echo esc_attr( $priority->term_id ); ?>"><?php echo esc_html( $priority->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="wsts_form-group">
                    <label for="wsts_description"><?php esc_html_e( 'Description', 'wsts' ); ?></label>
                    <?php
                    $editor_settings = [
                        'textarea_name' => 'wsts_description',
                        'editor_id' => 'wsts_description',
                        'media_buttons' => true,
                        'textarea_rows' => 15,
                        'teeny' => false,
                        'quicktags' => true,
                        'tinymce' => [
                            'toolbar1' => 'formatselect bold italic underline strikethrough | bullist numlist outdent indent | blockquote | alignleft aligncenter alignright | link unlink | wp_more | spellchecker',
                            'toolbar2' => 'styleselect forecolor backcolor | hr removeformat | subscript superscript | code charmap | pastetext pasteword | undo redo | wp_help',
                            'plugins' => 'charmap colorpicker hr image lists media paste textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview', // Removed 'table'
                            'menubar' => false, // Disable the entire menubar
                            'statusbar' => true,
                            'mode' => 'tmce', // Force Visual mode
                            'setup' => 'function(editor) {
                                editor.on("init", function() {
                                    // Force Visual mode and hide Text tab
                                    editor.mode.set("tmce"); // Ensure Visual mode is active
                                    // Wait for the editor to be fully rendered
                                    setTimeout(function() {
                                        var wpEditorWrap = document.querySelector("#wp-wsts_description-wrap");
                                        if (wpEditorWrap) {
                                            var switchEditors = wpEditorWrap.querySelector(".wp-editor-tabs");
                                            if (switchEditors) {
                                                var textTab = switchEditors.querySelector(".switch-html");
                                                if (textTab) {
                                                    textTab.style.display = "none"; // Hide Text tab
                                                }
                                                var visualTab = switchEditors.querySelector(".switch-tmce");
                                                if (visualTab) {
                                                    visualTab.style.display = "none"; // Optionally hide Visual tab to avoid confusion
                                                }
                                            }
                                        }
                                    }, 100); // Small delay to ensure DOM is ready
                                });
                                editor.on("BeforeSetMode", function(e) {
                                    if (e.mode === "html") {
                                        e.preventDefault(); // Prevent switching to Text mode
                                        editor.mode.set("tmce"); // Force back to Visual mode
                                    }
                                });
                            }',
                        ],
                    ];
                    wp_editor( '', 'wsts_description', $editor_settings );
                    ?>
                </div>


            </form>
        </div>
        <div class="wsts_modal-footer">
            <button class="wsts_btn-secondary" id="wsts_cancel-new-ticket"><?php esc_html_e( 'Cancel', 'wsts' ); ?></button>
            <button class="wsts_btn-primary" id="wsts_submit-new-ticket"><?php esc_html_e( 'Create Ticket', 'wsts' ); ?></button>
        </div>
    </div>
</div>

<!-- View/Edit Ticket Modal -->
<div class="wsts_modal" id="wsts_ticket-detail-modal">
    <div class="wsts_modal-content">
        <div class="wsts_modal-header">
            <h2 id="wsts_ticket-modal-title"><?php esc_html_e( 'Ticket Details', 'wsts' ); ?></h2>
            <button class="wsts_close-btn">&times;</button>
        </div>
        <div class="wsts_modal-body">
            <div class="wsts_ticket-details">
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Ticket ID:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-id"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Subject:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-subject"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Requester:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-requester"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Department:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-department"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Priority:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-priority"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Status:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-status"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Created:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-created"></div>
                </div>
                <div class="wsts_detail-row">
                    <div class="wsts_detail-label"><?php esc_html_e( 'Description:', 'wsts' ); ?></div>
                    <div class="wsts_detail-value" id="wsts_detail-description"></div>
                </div>
            </div>
            <div class="wsts_comments-section">
                <h3><?php esc_html_e( 'Comments', 'wsts' ); ?></h3>
                <div id="wsts_comments-container"></div>
                <div class="wsts_add-comment">
                    <div class="wsts_form-group">
                        <label for="wsts_new-comment"><?php esc_html_e( 'Add Comment', 'wsts' ); ?></label>
                        <?php
                        $comment_editor_settings = [
                            'textarea_name' => 'wsts_new-comment',
                            'editor_id' => 'wsts_new-comment',
                            'media_buttons' => false,
                            'textarea_rows' => 5,
                            'teeny' => true,
                            'quicktags' => false,
                            'tinymce' => [
                                'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink',
                                'toolbar2' => '',
                                'plugins' => 'lists,link', // Removed 'table'
                            ],
                        ];
                        wp_editor( '', 'wsts_new-comment', $comment_editor_settings );
                        ?>
                    </div>
                    <button class="wsts_btn-primary" id="wsts_add-comment-btn"><?php esc_html_e( 'Add Comment', 'wsts' ); ?></button>
                </div>
            </div>
        </div>
        <div class="wsts_modal-footer">
            <button class="wsts_btn-secondary" id="wsts_close-ticket-details"><?php esc_html_e( 'Close', 'wsts' ); ?></button>
            <button class="wsts_btn-primary" id="wsts_edit-ticket-btn"><?php esc_html_e( 'Edit Ticket', 'wsts' ); ?></button>
            <?php if ( current_user_can( 'manage_options' ) ) : ?>
                <button class="wsts_btn-primary" id="wsts_approve-ticket-btn" style="display: none;"><?php esc_html_e( 'Approve Ticket', 'wsts' ); ?></button>
            <?php endif; ?>
        </div>
    </div>
</div>