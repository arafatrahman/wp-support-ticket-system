<?php
// includes/views/admin/meta-box.php

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