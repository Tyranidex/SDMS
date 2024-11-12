#### WORK IN PROGRESS 
# Simple Document Management System (SDMS)
A WordPress plugin to manage documents with multilingual support, custom categories, and secure file handling.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Adding a New Document](#adding-a-new-document)
  - [Accessing Documents](#accessing-documents)
- [Templates](#templates)
  - [Adding Custom Templates](#adding-custom-templates)
- [Security Considerations](#security-considerations)
- [To do](#to-do)
- [Contributing](#contributing)
- [License](#license)
- [Credits](#credits)

## Features
- **Custom Post Type** : Adds a "Document" post type with hierarchical categories.
- **Multilingual Support** : Upload and manage files in multiple languages per document.
- **Custom Permalinks** : Generates URLs like /docs/category/subcategory/document/.
- **Secure File Downloads** : Serves files through controlled URLs to prevent direct access.
- **Customizable Templates** : Select different front-end templates for document display.
- **Custom File Type Icons** : Upload custom icons for various file types.
- **Admin Settings Page** : Configure languages, templates, and icons through an intuitive interface.
- **WordPress Standards** : Built following WordPress coding best practices.

## Installation
- Download the plugin files and upload them to your WordPress installation under the **wp-content/plugins/smds** directory.
- **Activate** the plugin through the **WordPress admin dashboard**:
- Navigate to **Plugins**.
- Click **Activate** next to "Simple Document Management System (SDMS)".
- Flush Rewrite Rules:
  - Go to **Settings** > **Permalinks**.
  - Click **Save Changes** to refresh permalinks and enable custom URLs.

## Configuration
- Navigate to **Settings > SDMS Settings** in the WordPress admin dashboard.
- Add Languages:
  - Select languages from the dropdown menu.
  - Click Add to include them.
- Upload custom flags for each language if desired.
- Customize File Type Icons:
  - Upload custom icons for different file types (PDF, Word, Excel, etc.).
- Select a Template:
  - Choose a front-end template from the available options.
- **Save Changes** to apply your settings.

## Usage

#### Adding a New Document
- Go to **Documents > Add New**.
- Enter **Title** and **Content** for your document.
- Assign Categories specific to the Document post type.
- Upload Files for Each Language:
  - In the Language Files metabox, upload files for the languages you've added.
  - You can view or remove files before saving.
- Select File Type Image:
  - Choose an image representing the file type, displayed on the front end.
- Publish the document.

#### Accessing Documents
- Front-End URL Structure:
  - Access documents via URLs like : https://yourdomain.com/docs/category/document/
- Downloading Files:
  - Default Language : https://yourdomain.com/docs/category/document/download/
  - Specific Language : https://yourdomain.com/docs/category/document/download/{language_code}

## Templates
- Template Selection:
  - Choose templates from the settings page to change how documents are displayed.

### Adding custom templates
You can add your own templates to customize how documents are displayed on your site.
- Create a Template File:
  - In your theme or child theme directory, create a folder named **smds-templates**.
  - Inside this folder, create a new PHP file for your template (e.g., my-custom-template.php).
- Add Template Header:
  - At the top of your template file, include the following header:

```
<?php
/*
Template Name: My Custom Template
*/
?>
```

Use WordPress functions and SMDS plugin data to build your template.
Refer to the existing templates in the plugin's templates directory for examples.

## Security Considerations
- **File Validation** : Only allows specific file types to prevent malicious uploads.
- **File Size Limit** : Enforces maximum upload size to maintain performance.
- **User Permissions** : Only administrators can change plugin settings.
- Editors can add or modify documents and custom fields.
- **Protected File URLs** : Direct access to file URLs is prevented; files are served through controlled endpoints.
- **Data Sanitization** : All user inputs are sanitized and validated.
- **Nonce Verification** : Uses nonces for security checks on form submissions.

## To Do
- **Caching File Paths** : Cache the results of expensive operations like get_attached_file() to improve performance.
- **Code Reusability** : Create helper functions for repetitive tasks, such as generating file download URLs.
- **Use of Transients** : Utilize WordPress transients for caching data that doesn't change frequently.
- **Search and Filtering** : Implement search functionality to allow users to search documents by title, content, or categories.
- **Access Control** : Add options to restrict access to certain documents based on user roles or permissions.
- **Notifications** : Notify users or administrators when a new document is uploaded or updated.
- **Bulk Upload** : Implement a bulk upload feature for adding multiple documents at once.

## Contributing
Contributions are welcome! Please submit issues and pull requests on the GitHub repository.

## License
This plugin is licensed under the GNU General Public License v3.0.

## Credits
Developed by Dorian Renon with the help of my buddy ChatGPT o1.
