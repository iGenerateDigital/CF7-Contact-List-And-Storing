<?php
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook into Contact Form 7 submission
add_action( 'wpcf7_mail_sent', 'cf7_storage_save_contact' );

function cf7_storage_save_contact( $contact_form ) {
    global $wpdb;
    $table_name = 'cf7_storage';

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

    $filename = 'cf7_contacts_' . date( 'YmdHis' ) . '.csv';
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename=' . $filename );

    $output = fopen( 'php://output', 'w' );
    fputcsv( $output, array( 'ID', 'Form Title', 'Name', 'Email', 'Website', 'Company', 'Phone', 'Comments', 'Submitted At' ) );

    $results = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
    foreach ( $results as $row ) {
        fputcsv( $output, $row );
    }

    fclose( $output );
    exit;
}
?>
