WORK IN PROGRESS | Simple Document Management System (SDMS)
A WordPress plugin to manage documents with multilingual support, custom categories, and secure file handling.

Table of Contents
Features
Installation
Configuration
Usage
Adding a New Document
Accessing Documents
Templates
Security Considerations
Contributing
License
Credits
Features
Custom Post Type: Adds a "Document" post type with hierarchical categories.
Multilingual Support: Upload and manage files in multiple languages per document.
Custom Permalinks: Generates URLs like /docs/category/subcategory/document/.
Secure File Downloads: Serves files through controlled URLs to prevent direct access.
Customizable Templates: Select different front-end templates for document display.
Custom File Type Icons: Upload custom icons for various file types.
Admin Settings Page: Configure languages, templates, and icons through an intuitive interface.
WordPress Standards: Built following WordPress coding best practices.
Installation
Download the plugin files and upload them to your WordPress installation under the wp-content/plugins/smds directory.
Activate the plugin through the WordPress admin dashboard:
Navigate to Plugins.
Click Activate next to "Simple Document Management System (SDMS)".
Flush Rewrite Rules:
Go to Settings > Permalinks.
Click Save Changes to refresh permalinks and enable custom URLs.
Configuration
Navigate to Settings > SDMS Settings in the WordPress admin dashboard.
Add Languages:
Select languages from the dropdown menu.
Click Add to include them.
Upload custom flags for each language if desired.
Customize File Type Icons:
Upload custom icons for different file types (PDF, Word, Excel, etc.).
Select a Template:
Choose a front-end template from the available options.
Save Changes to apply your settings.
Usage
Adding a New Document
Go to Documents > Add New.
Enter Title and Content for your document.
Assign Categories specific to the Document post type.
Upload Files for Each Language:
In the Language Files metabox, upload files for the languages you've added.
You can view or remove files before saving.
Select File Type Image:
Choose an image representing the file type, displayed on the front end.
Publish the document.
Accessing Documents
Front-End URL Structure:

Access documents via URLs like : https://yourdomain.com/docs/category/document/
Downloading Files:

Default Language : https://yourdomain.com/docs/category/document/download/
Specific Language : https://yourdomain.com/docs/category/document/download/{language_code}
Templates
Template Selection:
Choose templates from the settings page to change how documents are displayed.
Customization:
Templates are located in the templates directory.
Use the same CSS file for consistent styling across templates.
Creating Custom Templates:
You can create additional templates by adding PHP files in the templates directory.
Ensure your templates follow WordPress theme standards.
Security Considerations
File Validation:
Only allows specific file types to prevent malicious uploads.
File Size Limit:
Enforces maximum upload size to maintain performance.
User Permissions:
Only administrators can change plugin settings.
Editors can add or modify documents and custom fields.
Protected File URLs:
Direct access to file URLs is prevented; files are served through controlled endpoints.
Data Sanitization:
All user inputs are sanitized and validated.
Nonce Verification:
Uses nonces for security checks on form submissions.
Contributing
Contributions are welcome! Please submit issues and pull requests on the GitHub repository.

License
This plugin is licensed under the GNU General Public License v2.0 or later.

Credits
Developed by Dorian Renon
