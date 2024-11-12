jQuery(document).ready(function($){
    /**
     * Handles the media upload functionality for language files in the admin area.
     * Allows users to upload and manage files for each language in the document edit screen.
     */
    var mediaUploader;

    // Upload button click event
    $('.sdms-upload-button').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var language = button.data('language'); // Get the language code (e.g., 'en', 'fr')
        var cell = button.closest('td'); // Get the parent table cell

        // Create the media frame
        mediaUploader = wp.media({
            title: sdmsUploader.title, // Title of the media uploader window
            button: {
                text: sdmsUploader.button // Text for the 'Select' button
            },
            multiple: false // Only allow single file selection
        });

        // When a file is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            // Update the hidden input with the attachment ID
            cell.find('input[name="sdms_file_' + language + '"]').val(attachment.id);

            // Update the File row to display the 'View File' link
            var fileRow = cell.closest('tbody').find('tr').eq(0); // First row (File status row)
            var fileCell = fileRow.find('td').eq(cell.index());
            fileCell.html('<a href="' + attachment.url + '" target="_blank">' + sdmsUploader.viewFile + '</a>');

            // Update the UI: Hide the upload button and show the remove button
            button.hide();
            cell.find('.sdms-remove-file-button').show();
        });

        // Open the media uploader frame
        mediaUploader.open();
    });

    // Remove button click event
    $(document).on('click', '.sdms-remove-file-button', function(e) {
        e.preventDefault();

        var button = $(this);
        var language = button.data('language'); // Get the language code
        var cell = button.closest('td'); // Get the parent table cell

        // Clear the hidden input
        cell.find('input[name="sdms_file_' + language + '"]').val('');

        // Update the File row to display 'No file uploaded.'
        var fileRow = cell.closest('tbody').find('tr').eq(0); // First row (File status row)
        var fileCell = fileRow.find('td').eq(cell.index());
        fileCell.html(sdmsUploader.noFile);

        // Update the UI: Hide the remove button and show the upload button
        button.hide();
        cell.find('.sdms-upload-button').show();
    });
});
