<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add admin menu
add_action( 'admin_menu', 'cf7_storage_admin_menu' );

function cf7_storage_admin_menu() {
    add_menu_page(
        'Contacts',
        'Contact List',
        'manage_options',
        'cf7-storage',
        'cf7_storage_admin_page',
        'dashicons-list-view',
        26
    );
    add_submenu_page(
        'cf7-storage',
        'CF7 Storage Settings',
        'Settings',
        'manage_options',
        'cf7-storage-settings',
        'cf7_storage_settings_page'
    );
}

// Admin page content
function cf7_storage_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf7_storage';

    if ( isset( $_POST['export_csv'] ) ) {
        // Check nonce
        if ( ! isset( $_POST['cf7_storage_export_nonce'] ) || ! wp_verify_nonce( $_POST['cf7_storage_export_nonce'], 'cf7_storage_export' ) ) {
            wp_die( 'Nonce verification failed' );
        }

        cf7_storage_export_csv();
    }

    // Try to get cached results
    $cache_key = 'cf7_storage_results';
    $results = wp_cache_get( $cache_key );

    if ( $results === false ) {
        // Prepare and execute the query
        $query = "SELECT * FROM $table_name";
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cf7_storage" ), ARRAY_A );

        // Cache the results
        wp_cache_set( $cache_key, $results, '', 3600 ); // Cache for 1 hour
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Contact Form Storage', 'cf7-storage' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'cf7_storage_export', 'cf7_storage_export_nonce' ); ?>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column"><?php esc_html_e( 'ID', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Form Title', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Name', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Email', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Website', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Company', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Phone', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Comments', 'cf7-storage' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Submitted At', 'cf7-storage' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $results as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['id'] ); ?></td>
                            <td><?php echo esc_html( $row['form_title'] ); ?></td>
                            <td><?php echo esc_html( $row['name'] ); ?></td>
                            <td><?php echo esc_html( $row['email'] ); ?></td>
                            <td><?php echo esc_html( $row['website'] ); ?></td>
                            <td><?php echo esc_html( $row['company'] ); ?></td>
                            <td><?php echo esc_html( $row['phone'] ); ?></td>
                            <td><?php echo esc_html( $row['comments'] ); ?></td>
                            <td><?php echo esc_html( $row['submitted_at'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <input type="submit" name="export_csv" class="button button-primary" value="<?php esc_attr_e( 'Export to CSV', 'cf7-storage' ); ?>">
            </p>
        </form>
    </div>
    <?php
}

// Settings page content
function cf7_storage_settings_page() {
    $forms = cf7_storage_get_forms();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'CF7 Storage Settings', 'cf7-storage' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <?php foreach ( $forms as $form ) : ?>
                <a href="#tab-<?php echo esc_attr( $form['id'] ); ?>" class="nav-tab" data-tab="tab-<?php echo esc_attr( $form['id'] ); ?>"><?php echo esc_html( $form['title'] ); ?></a>
            <?php endforeach; ?>
        </h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'cf7_storage_settings_group' );
            foreach ( $forms as $form ) {
                ?>
                <div id="tab-<?php echo esc_attr( $form['id'] ); ?>" class="cf7-storage-tab-content">
                    <?php
                    do_settings_sections( 'cf7-storage-settings-' . $form['id'] );
                    ?>
                </div>
                <?php
            }
            submit_button();
            ?>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var tabs = document.querySelectorAll('.nav-tab');
        var tabContents = document.querySelectorAll('.cf7-storage-tab-content');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
                tab.classList.add('nav-tab-active');

                tabContents.forEach(function(content) { content.style.display = 'none'; });
                document.querySelector('#' + tab.dataset.tab).style.display = 'block';
            });
        });

        document.querySelector('.nav-tab').click();
    });
    </script>
    <style>
    .cf7-storage-tab-content { display: none; }
    </style>
    <?php
}

// Register settings
add_action( 'admin_init', 'cf7_storage_register_settings' );

function cf7_storage_register_settings() {
    $forms = cf7_storage_get_forms();

    register_setting( 'cf7_storage_settings_group', 'cf7_storage_settings', 'cf7_storage_sanitize_settings' );

    foreach ( $forms as $form ) {
        add_settings_section(
            'cf7_storage_settings_section_' . $form['id'],
            esc_html( $form['title'] ),
            null,
            'cf7-storage-settings-' . $form['id']
        );

        add_settings_field(
            'cf7_storage_name_field_' . $form['id'],
            esc_html__( 'Name Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_name_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'name',
                'fields' => $form['fields'],
            )
        );

        add_settings_field(
            'cf7_storage_email_field_' . $form['id'],
            esc_html__( 'Email Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_email_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'email',
                'fields' => $form['fields'],
            )
        );

        add_settings_field(
            'cf7_storage_website_field_' . $form['id'],
            esc_html__( 'Website Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_website_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'website',
                'fields' => $form['fields'],
            )
        );

        add_settings_field(
            'cf7_storage_company_field_' . $form['id'],
            esc_html__( 'Company Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_company_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'company',
                'fields' => $form['fields'],
            )
        );

        add_settings_field(
            'cf7_storage_phone_field_' . $form['id'],
            esc_html__( 'Phone Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_phone_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'phone',
                'fields' => $form['fields'],
            )
        );

        add_settings_field(
            'cf7_storage_comments_field_' . $form['id'],
            esc_html__( 'Comments Field', 'cf7-storage' ),
            'cf7_storage_field_callback',
            'cf7-storage-settings-' . $form['id'],
            'cf7_storage_settings_section_' . $form['id'],
            array(
                'label_for' => 'cf7_storage_comments_field_' . $form['id'],
                'type' => 'dropdown',
                'form_id' => $form['id'],
                'name' => 'comments',
                'fields' => $form['fields'],
            )
        );
    }
}

function cf7_storage_field_callback( $args ) {
    $options = get_option( 'cf7_storage_settings' );
    $form_id = $args['form_id'];
    $name = $args['name'];
    $fields = $args['fields'];
    $value = isset( $options[ $form_id ][ $name ] ) ? $options[ $form_id ][ $name ] : '';

    if ( $args['type'] === 'dropdown' ) {
        echo '<select id="' . esc_attr( $args['label_for'] ) . '" name="cf7_storage_settings[' . esc_attr( $form_id ) . '][' . esc_attr( $name ) . ']">';
        echo '<option value="none" ' . selected( $value, 'none', false ) . '>None</option>';
        foreach ( $fields as $field ) {
            echo '<option value="' . esc_attr( $field ) . '" ' . selected( $value, $field, false ) . '>' . esc_html( $field ) . '</option>';
        }
        echo '</select>';
    }
}

function cf7_storage_sanitize_settings( $input ) {
    $sanitized_input = array();
    foreach ( $input as $form_id => $fields ) {
        foreach ( $fields as $key => $value ) {
            $sanitized_input[$form_id][$key] = sanitize_text_field( $value );
        }
    }
    return $sanitized_input;
}
?>
