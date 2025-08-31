<?php
// includes/controllers/Admin.php

class WSTS_Admin_Controller {

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'add_meta_boxes'] );
        add_action( 'save_post_support_ticket', [$this, 'save_meta_box_data'] );
        add_filter( 'manage_support_ticket_posts_columns', [$this, 'set_custom_edit_ticket_columns'] );
        add_action( 'manage_support_ticket_posts_custom_column', [$this, 'custom_ticket_column'], 10, 2 );
        add_filter( 'views_edit-support_ticket', [$this, 'add_ticket_views'] );

        // Taxonomy color fields
        add_action( 'ticket_priority_add_form_fields', [$this, 'add_taxonomy_color_field'] );
        add_action( 'ticket_status_add_form_fields', [$this, 'add_taxonomy_color_field'] );
        add_action( 'ticket_priority_edit_form_fields', [$this, 'edit_taxonomy_color_field'] );
        add_action( 'ticket_status_edit_form_fields', [$this, 'edit_taxonomy_color_field'] );
        add_action( 'created_ticket_priority', [$this, 'save_taxonomy_color'] );
        add_action( 'edited_ticket_priority', [$this, 'save_taxonomy_color'] );
        add_action( 'created_ticket_status', [$this, 'save_taxonomy_color'] );
        add_action( 'edited_ticket_status', [$this, 'save_taxonomy_color'] );
    }

    public function enqueue_scripts($hook = null) {
        // Enqueue color picker for taxonomy pages in admin
        if ( is_admin() && ('term.php' === $hook || 'edit-tags.php' === $hook) ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }

        // Common styles
        wp_enqueue_style( 'wsts-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2' );
        wp_enqueue_style( 'wsts-datatables', 'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css', array(), '1.13.7' );
        wp_enqueue_style( 'wsts-style', WSTS_PLUGIN_URL . 'assets/css/style.css', array(), WSTS_VERSION );

        // Common scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'wsts-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true );
        wp_enqueue_script( 'wsts-datatables-js', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', array('jquery'), '1.13.7', true );
        wp_enqueue_script( 'wsts-datatables-bootstrap-js', 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js', array('jquery', 'wsts-datatables-js'), '1.13.7', true );
        wp_enqueue_script( 'wsts-script', WSTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), WSTS_VERSION, true );

        // Pass data to script.js
        wp_localize_script( 'wsts-script', 'wsts_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wsts_nonce' )
        ));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'wsts_ticket_details',
            __( 'Ticket Details', 'wsts' ),
            [$this, 'render_ticket_details_meta_box'],
            'support_ticket',
            'side',
            'high'
        );
    }

    public function render_ticket_details_meta_box( $post ) {
        global $pagenow;
        wp_nonce_field( 'wsts_save_meta_box_data', 'wsts_meta_box_nonce' );

        $assigned_to = get_post_meta( $post->ID, '_wsts_assigned_to', true );
        $user_id = get_post_meta( $post->ID, '_wsts_user_id', true );
        $assignable_users = get_users(['role__in' => ['administrator', 'editor'], 'fields' => ['ID', 'display_name']]);

        // Load view
        include WSTS_PLUGIN_DIR . 'includes/views/admin/meta-box.php';
    }

    public function save_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['wsts_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wsts_meta_box_nonce'], 'wsts_save_meta_box_data' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save data
        if ( isset( $_POST['wsts_user_id'] ) ) {
            update_post_meta( $post_id, '_wsts_user_id', intval( $_POST['wsts_user_id'] ) );
        }
        if ( isset( $_POST['wsts_priority'] ) ) {
            wp_set_post_terms( $post_id, [intval($_POST['wsts_priority'])], 'ticket_priority', false );
        }
        if ( isset( $_POST['wsts_status'] ) ) {
            wp_set_post_terms( $post_id, [intval($_POST['wsts_status'])], 'ticket_status', false );
        }
        if ( isset( $_POST['wsts_assigned_to'] ) ) {
            update_post_meta( $post_id, '_wsts_assigned_to', intval( $_POST['wsts_assigned_to'] ) );
        }
    }

    public function set_custom_edit_ticket_columns($columns) {
        unset($columns['author']);
        unset($columns['date']);
        unset($columns['comments']);
        $columns['user'] = __( 'User', 'wsts' );
        $columns['assigned_to'] = __( 'Assigned To', 'wsts' );
        $columns['priority'] = __( 'Priority', 'wsts' );
        $columns['status'] = __( 'Status', 'wsts' );
        $columns['last_updated'] = __( 'Last Updated', 'wsts' );
        return $columns;
    }

    public function custom_ticket_column( $column, $post_id ) {
        switch ( $column ) {
            case 'user':
                $user_id = get_post_meta($post_id, '_wsts_user_id', true);
                $user = get_user_by('id', $user_id);
                echo $user ? esc_html($user->display_name) : __('N/A', 'wsts');
                break;
            case 'assigned_to':
                $assigned_id = get_post_meta($post_id, '_wsts_assigned_to', true);
                if($assigned_id) {
                    $user = get_user_by('id', $assigned_id);
                    echo $user ? esc_html($user->display_name) : __('Unknown', 'wsts');
                } else {
                    echo '<em>' . __('Unassigned', 'wsts') . '</em>';
                }
                break;
            case 'priority':
                $terms = get_the_terms($post_id, 'ticket_priority');
                if ($terms && !is_wp_error($terms)) {
                    echo WSTS_Taxonomy_Model::get_term_badge_html(array_shift($terms));
                }
                break;
            case 'status':
                $terms = get_the_terms($post_id, 'ticket_status');
                if ($terms && !is_wp_error($terms)) {
                    echo WSTS_Taxonomy_Model::get_term_badge_html(array_shift($terms));
                }
                break;
            case 'last_updated':
                echo get_the_modified_date( 'F j, Y, g:i a', $post_id );
                break;
        }
    }

    public function add_ticket_views( $views ) {
        $statuses = get_terms([
            'taxonomy' => 'ticket_status',
            'hide_empty' => false,
        ]);

        if ( ! empty( $statuses ) && ! is_wp_error( $statuses ) ) {
            foreach ( $statuses as $status ) {
                $query = new WP_Query([
                    'post_type' => 'support_ticket',
                    'post_status' => 'any',
                    'tax_query' => [
                        [
                            'taxonomy' => 'ticket_status',
                            'field' => 'slug',
                            'terms' => $status->slug,
                        ],
                    ],
                ]);
                $count = $query->found_posts;

                if ( $count === 0 ) {
                    continue;
                }

                $url = add_query_arg(
                    [
                        'post_type' => 'support_ticket',
                        'ticket_status' => $status->slug,
                    ],
                    'edit.php'
                );

                $is_current = isset( $_GET['ticket_status'] ) && $_GET['ticket_status'] === $status->slug;
                $class = $is_current ? 'current' : '';

                $views[ $status->slug ] = sprintf(
                    '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                    esc_url( $url ),
                    $class,
                    esc_html( $status->name ),
                    $count
                );
            }
        }

        return $views;
    }

    public function add_taxonomy_color_field() {
        ?>
        <div class="form-field">
            <label for="term-color"><?php _e( 'Color', 'wsts' ); ?></label>
            <input type="text" name="term_color" id="term-color" class="wsts-color-picker" value="#ffffff">
            <p class="description"><?php _e( 'Choose a color for this term.', 'wsts' ); ?></p>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.wsts-color-picker').wpColorPicker();
        });
        </script>
        <?php
    }

    public function edit_taxonomy_color_field( $term ) {
        $color = get_term_meta( $term->term_id, 'wsts_color', true );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term-color"><?php _e( 'Color', 'wsts' ); ?></label></th>
            <td>
                <input type="text" name="term_color" id="term-color" class="wsts-color-picker" value="<?php echo esc_attr( $color ); ?>">
                <p class="description"><?php _e( 'Choose a color for this term.', 'wsts' ); ?></p>
            </td>
        </tr>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.wsts-color-picker').wpColorPicker();
        });
        </script>
        <?php
    }

    public function save_taxonomy_color( $term_id ) {
        if ( isset( $_POST['term_color'] ) ) {
            update_term_meta( $term_id, 'wsts_color', sanitize_hex_color( $_POST['term_color'] ) );
        }
    }
}