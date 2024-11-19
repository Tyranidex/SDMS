# Simple Document Management System (SDMS)

**Version**: 1.0.1  
**Author**: Dorian Renon

---

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Adding Documents](#adding-documents)
  - [Accessing Documents](#accessing-documents)
- [Templates](#templates)
  - [Using Existing Templates](#using-existing-templates)
  - [Creating Custom Templates](#creating-custom-templates)
- [Customization](#customization)
  - [Flag Icons](#flag-icons)
  - [Translations](#translations)
- [Security Considerations](#security-considerations)
- [Developer Notes](#developer-notes)
- [To-Do](#to-do)
- [Support and Contributing](#support-and-contributing)
- [License](#license)
- [Credits](#credits)

---

## Description

**Simple Document Management System (SDMS)** is a WordPress plugin that allows you to manage documents with multilingual support, custom categories, and secure file handling. Users can upload documents in multiple languages, and visitors can download them or share them via email.

---

## Features

- **Multilingual Document Management**: Upload and manage documents in multiple languages.
- **Custom Post Type**: Adds a "Document" post type with hierarchical categories.
- **Custom Permalinks**: Generates user-friendly URLs like `/docs/category/subcategory/document/`.
- **Secure File Downloads**: Serves files through controlled URLs to prevent direct access.
- **Customizable Templates**: Use default templates or create your own for single posts, archives, and taxonomies.
- **AJAX-Powered Search**: Provide a seamless search experience on archive and taxonomy pages.
- **Email Sharing**: Visitors can share documents via email using a dynamic modal.
- **Custom File Type Icons**: Customize icons for different file types (PDF, Word, Excel, etc.).
- **Custom Flags**: Use default flag icons or upload custom ones.
- **Admin Settings Page**: Configure languages, templates, and icons through an intuitive interface.
- **WordPress Standards**: Built following WordPress coding best practices.

---

## Installation

1. **Upload Plugin Files**:
   - Download the plugin files.
   - Upload the `sdms` folder to the `/wp-content/plugins/` directory of your WordPress installation.

2. **Activate the Plugin**:
   - Log in to your WordPress admin dashboard.
   - Navigate to **Plugins**.
   - Find **Simple Document Management System (SDMS)** and click **Activate**.

3. **Flush Rewrite Rules**:
   - Go to **Settings > Permalinks**.
   - Click **Save Changes** to refresh permalinks and enable custom URLs.

---

## Configuration

1. **Access Settings**:
   - In the WordPress admin dashboard, navigate to **Settings > SDMS Settings**.

2. **Languages**:
   - **Add Languages**:
     - Select languages from the dropdown menu.
     - Click **Add** to include them.
   - **Custom Flags**:
     - Upload custom flag icons for each language if desired.

3. **Templates**:
   - **Select Templates**:
     - Choose templates for single posts, archives, and taxonomies from the available options.

4. **Icons**:
   - **Custom File Type Icons**:
     - Upload custom icons for different file types (e.g., PDF, Word, Excel).

5. **Save Changes**:
   - Click **Save Changes** to apply your settings.

---

## Usage

### Adding Documents

1. **Add New Document**:
   - Go to **Documents > Add New** in the admin dashboard.

2. **Enter Document Details**:
   - **Title**: Enter the document title.
   - **Content**: Add any content or description for the document.

3. **Assign Categories**:
   - In the **Categories** metabox, assign categories specific to the Document post type.

4. **Upload Files for Each Language**:
   - In the **Language Files** metabox, upload files for the languages you've added.
   - You can view or remove files before saving.

5. **Select File Type Image**:
   - In the metabox on the right, choose an image representing the file type, which will be displayed on the front end.

6. **Publish**:
   - Click **Publish** to make the document live.

### Accessing Documents

#### Front-End URL Structure

- **Documents** are accessible via URLs like:

  `https://yourdomain.com/docs/category/document/`

#### Downloading Files

- **Default Language**:

  `https://yourdomain.com/docs/category/document/download/`

- **Specific Language**:

  `https://yourdomain.com/docs/category/document/download/{language_code}`

  Replace `{language_code}` with the appropriate language code (e.g., `en`, `fr`).

---

## Templates

### Using Existing Templates

- **Default Templates**:
  - The plugin comes with default templates for single posts, archives, and taxonomies.
  - You can select these templates in the **SDMS Settings** page under **Templates**.

### Creating Custom Templates

You can create your own templates to customize how documents are displayed on your site.

1. **Create a Template File**:
   - In your theme or child theme directory, create a folder named `sdms-templates`.
   - Inside this folder, create a new PHP file for your template (e.g., `my-custom-template.php`).

2. **Add Template Header**:
   - At the top of your template file, include the following header:

     ```php
     <?php
     /*
     Template Name: My Custom Template
     */
     ?>
     ```

3. **Customize Template**:
   - Use WordPress functions and SDMS plugin data to build your template.
   - Refer to the existing templates in the plugin's `templates` directory for examples.

4. **Select Custom Template**:
   - Go to **SDMS Settings**.
   - Under **Templates**, your custom template will now appear in the dropdown menu.
   - Select your custom template and **Save Changes**.

---

## Customization

### Flag Icons

- **Default Flags**:
  - The plugin provides default flag icons for languages.

- **Custom Flags**:
  - Place custom flag icons in your theme's `sdms-flags` folder.
  - **Supported Formats**:
    - `.png` files named with the language code (e.g., `en.png`, `fr.png`).

### Translations

- **Language Files**:
  - Provide translations by adding `.po` and `.mo` files in the `languages` folder.

- **Generating Translation Files**:
  - Use tools like [Poedit](https://poedit.net/) to create translation files.

---

## Security Considerations

- **File Validation**:
  - Only allows specific file types to prevent malicious uploads.

- **File Size Limit**:
  - Enforces maximum upload size to maintain performance.

- **User Permissions**:
  - Only administrators can change plugin settings.
  - Editors can add or modify documents and custom fields.

- **Protected File URLs**:
  - Direct access to file URLs is prevented; files are served through controlled endpoints.

- **Data Sanitization**:
  - All user inputs are sanitized and validated.

- **Nonce Verification**:
  - Uses nonces for security checks on form submissions.

---

## Developer Notes

- **Helper Functions**:
  - Use helper functions like `sdms_get_flag_url()` and `sdms_get_download_url()` for code reusability.

- **Action Hooks**:
  - Utilize WordPress action hooks and filters provided by the plugin for further customization.

- **Caching File Paths**:
  - Cache the results of expensive operations like `get_attached_file()` to improve performance.

- **Use of Transients**:
  - Utilize WordPress transients for caching data that doesn't change frequently.

- **Code Reusability**:
  - Create helper functions for repetitive tasks, such as generating file download URLs.

---

## To-Do

- **Caching Enhancements**:
  - Implement caching for improved performance.

- **Search and Filtering**:
  - Implement search functionality to allow users to search documents by title, content, or categories.

- **Access Control**:
  - Add options to restrict access to certain documents based on user roles or permissions.

- **Notifications**:
  - Notify users or administrators when a new document is uploaded or updated.

- **Bulk Upload**:
  - Implement a bulk upload feature for adding multiple documents at once.

---

## Support and Contributing

- **Support**:
  - For support, please contact the author at [dorian.renon@gmail.com](mailto:dorian.renon@gmail.com) or visit the plugin's repository.

- **Contributing**:
  - Contributions are welcome! Please submit issues and pull requests on the GitHub repository.

---

## License

This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

---

## Credits

Developed by Dorian Renon with the help of ChatGPT.
