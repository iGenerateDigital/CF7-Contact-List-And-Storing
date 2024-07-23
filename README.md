# CF7 Contact List

**Version:** 1.0  
**Author:** iGenerate Digital  
**Author URI:** [iGenerate Digital](https://www.igeneratedigital.com)
**Description:** Stores Contact Form 7 submissions and allows for managing and exporting contacts.

## Description

Contact List is a WordPress plugin that stores submissions from Contact Form 7 forms. It allows you to manage and export contacts to a CSV file. The plugin includes a settings page where you can map form fields to database columns for each Contact Form 7 form.

## Installation

1. **Download the Plugin:**
   - Download the latest version of the plugin from the repository or release page.

2. **Upload to WordPress:**
   - Go to the WordPress Admin Dashboard.
   - Navigate to `Plugins` > `Add New`.
   - Click on the `Upload Plugin` button.
   - Choose the downloaded ZIP file and click `Install Now`.

3. **Activate the Plugin:**
   - After installation, click `Activate` to activate the plugin.

4. **Verify Database Table:**
   - The plugin will automatically create a database table named `cf7_storage` to store the submissions.

## Usage

### Setting Up Field Mappings

1. **Go to Settings:**
   - Navigate to `CF7 Storage` > `Settings` in the WordPress Admin Dashboard.

2. **Map Fields:**
   - You will see a tabbed interface with each Contact Form 7 form.
   - Select the tab for the form you want to configure.
   - Use the dropdown menus to map the form fields to the corresponding database columns (Name, Email, Website, Company, Phone, Comments).
   - You can select `None` if a form does not collect a particular field.

3. **Save Settings:**
   - Click the `Save Changes` button to save the mappings.

### Viewing and Exporting Submissions

1. **View Submissions:**
   - Navigate to `CF7 Storage` in the WordPress Admin Dashboard.
   - You will see a list of all stored submissions with their details.

2. **Export to CSV:**
   - To export the submissions to a CSV file, click the `Export to CSV` button.

## Developer Notes

### Database Table

The plugin creates a database table named `cf7_storage` with the following structure:

```sql
CREATE TABLE cf7_storage (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    form_title varchar(255) NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    website varchar(255) NOT NULL,
    company varchar(255) NOT NULL,
    phone varchar(20) NOT NULL,
    comments text NOT NULL,
    submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (id)
);

### Hooks
- `wpcf7_mail_sent`: This hook is used to save form submissions to the database.

### Functions
- `cf7_storage_save_contact`: Handles saving form submissions to the database.
- `cf7_storage_get_forms`: Fetches all Contact Form 7 forms and their fields.
- `cf7_storage_export_csv`: Handles exporting stored contacts to a CSV file.

### Troubleshooting

**Submissions Not Storing:**
- Ensure the database table `yov_cf7_storage` exists and has the correct structure.
- Check the `wp-content/debug.log` file for any errors.
- Verify field mappings in the settings page.

**CSV Export Issues:**
- Ensure there are submissions stored in the database.
- Check for any errors during the export process.

### License
This plugin is licensed under the GPLv2 or later.

### Contact
For support or questions, please contact support@igeneratedigital.com.au.
