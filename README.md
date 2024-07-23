=== Contact List ===
Contributors: iGenerate Digital
Donate link: https://igeneratedigital.com.au
Tags: contact form, contact form 7, address book, csv export, form submissions
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Short Description ==
Contact List stores Contact Form 7 submissions and allows for managing and exporting contacts.

== Description ==
Contact List is a WordPress plugin that stores submissions from Contact Form 7 forms. It allows you to manage and export contacts to a CSV file. The plugin includes a settings page where you can map form fields to database columns for each Contact Form 7 form.

== Installation ==
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

== Usage ==
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

== Frequently Asked Questions ==
= Why are my form submissions not being stored? =
- Ensure the database table `cf7_storage` exists and has the correct structure.
- Check the `wp-content/debug.log` file for any errors.
- Verify field mappings in the settings page.

= Why is the CSV export not working? =
- Ensure there are submissions stored in the database.
- Check for any errors during the export process.

== Screenshots ==
1. Screenshot showing the settings page.
2. Screenshot showing the submissions list.
3. Screenshot showing the CSV export button.

== Changelog ==
= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.0 =
* Initial release.

== License ==
This plugin is licensed under the GPLv2 or later.

== Contact ==
For support or questions, please contact support@igeneratedigital.com.au.
