<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook into Contact Form 7 submission
add_action( 'wpcf7_mail_sent', 'cf7_storage_save_contact' );

function cf7_storage_save_contact( $contact_form ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf7_storage';

    $options = get_option( 'cf7_storage_settings' );
    $form_id = $contact_form->id();

    // Debugging: log form ID and options array
    error_log('CF7 Storage: Form ID - ' . $form_id);
    error_log('CF7 Storage: Options - ' . print_r($options, true));

    if ( isset( $options[ $form_id ] ) ) {
        $fields = $options[ $form_id ];

        // Retrieve form data
        $submission = WPCF7_Submission::get_instance();
        if ( $submission ) {
            $posted_data = $submission->get_posted_data();
            $form_title = $contact_form->title();
            $name = isset($fields['name']) && $fields['name'] !== 'none' ? sanitize_text_field( $posted_data[ $fields['name'] ] ) : '';
            $email = isset($fields['email']) && $fields['email'] !== 'none' ? sanitize_email( $posted_data[ $fields['email'] ] ) : '';
            $website = isset($fields['website']) && $fields['website'] !== 'none' ? sanitize_text_field( $posted_data[ $fields['website'] ] ) : '';
            $company = isset($fields['company']) && $fields['company'] !== 'none' ? sanitize_text_field( $posted_data[ $fields['company'] ] ) : '';
            $phone = isset($fields['phone']) && $fields['phone'] !== 'none' ? sanitize_text_field( $posted_data[ $fields['phone'] ] ) : '';
            $comments = isset($fields['comments']) && $fields['comments'] !== 'none' ? sanitize_textarea_field( $posted_data[ $fields['comments'] ] ) : '';

            // Debugging: log retrieved form data
            error_log('CF7 Storage: Name - ' . $name);
            error_log('CF7 Storage: Email - ' . $email);
            error_log('CF7 Storage: Website - ' . $website);
            error_log('CF7 Storage: Company - ' . $company);
            error_log('CF7 Storage: Phone - ' . $phone);
            error_log('CF7 Storage: Comments - ' . $comments);

            // Insert data into the database
            $wpdb->insert(
                $table_name,
                array(
                    'form_title' => $form_title,
                    'name' => $name,
                    'email' => $email,
                    'website' => $website,
                    'company' => $company,
                    'phone' => $phone,
                    'comments' => $comments,
                )
            );

            // Debugging: log any database errors
            error_log('CF7 Storage: Last error - ' . $wpdb->last_error);

            // Clear cache
            wp_cache_delete('cf7_storage_results');
        }
    }
}

// Function to fetch all Contact Form 7 forms and their fields
function cf7_storage_get_forms() {
    $forms = [];
    $args = array(
        'post_type' => 'wpcf7_contact_form',
        'posts_per_page' => -1,
    );

    $cf7_forms = get_posts( $args );
    foreach ( $cf7_forms as $form ) {
        $form_id = $form->ID;
        $form_title = $form->post_title;
        $form_content = $form->post_content;

        preg_match_all('/\[(\w+)\*?\s+([^\s\]]+)/', $form_content, $matches, PREG_SET_ORDER);

        $fields = [];
        foreach ( $matches as $match ) {
            if (strpos($match[2], 'class:') === false) {
                $fields[] = $match[2]; // form field name
            }
        }

        $forms[] = [
            'id' => $form_id,
            'title' => $form_title,
            'fields' => $fields,
        ];
    }
    return $forms;
}

// CSV export function
function cf7_storage_export_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf7_storage';

    $filename = 'cf7_contacts_' . gmdate( 'YmdHis' ) . '.csv';

    // Initialize the WP Filesystem
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    WP_Filesystem();
    global $wp_filesystem;

    // Create a temporary file in the WP filesystem
    $temp_file = wp_tempnam( $filename );
    if ( ! $temp_file ) {
        error_log('CF7 Storage: Failed to create temporary file');
        exit;
    }

    // Create CSV content
    $csv_content = [];
    $csv_content[] = implode( ',', array( 'ID', 'Form Title', 'Name', 'Email', 'Website', 'Company', 'Phone', 'Comments', 'Submitted At' ) );

    // Try to get cached results
    $cache_key = 'cf7_storage_results';
    $results = wp_cache_get( $cache_key );

    if ( $results === false ) {
        // Prepare and execute the query
        $query = "SELECT * FROM $table_name";
        $results = $wpdb->get_results( $query, ARRAY_A );

        // Cache the results
        wp_cache_set( $cache_key, $results, '', 3600 ); // Cache for 1 hour
    }

    if ( $results === false ) {
        error_log('CF7 Storage: Failed to retrieve data from database - ' . $wpdb->last_error);
        $wp_filesystem->delete( $temp_file );
        exit;
    }

    foreach ( $results as $row ) {
        $csv_content[] = implode( ',', array_map( 'esc_attr', $row ) );
    }

    // Write CSV content to the temporary file using WP_Filesystem
    if ( ! $wp_filesystem->put_contents( $temp_file, implode( "\n", $csv_content ), FS_CHMOD_FILE ) ) {
        error_log('CF7 Storage: Failed to write to temporary file');
        $wp_filesystem->delete( $temp_file );
        exit;
    }

    // Read the file and output it to the browser
    $file_data = $wp_filesystem->get_contents( $temp_file );
    if ( $file_data === false ) {
        error_log('CF7 Storage: Failed to read temporary file');
        $wp_filesystem->delete( $temp_file );
        exit;
    }

    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename=' . esc_attr( $filename ) );
    echo esc_html( $file_data );

    // Clean up the temporary file
    $wp_filesystem->delete( $temp_file );

    exit;
}
?>
