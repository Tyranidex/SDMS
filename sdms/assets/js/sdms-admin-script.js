jQuery(document).ready(function($) {

    /**
     * Handles the media uploader for icon selection and language management in the admin area.
     */

    // Tab navigation
    $('.nav-tab').on('click', function(e){
        e.preventDefault();
        var target = $(this).attr('href');

        // Remove active class from all tabs and add to the clicked tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Hide all tab contents and show the targeted one
        $('.tab-content').hide();
        $(target).show();
    });

    // Add Language button click
    $('#sdms_add_language').on('click', function(){
        var selectedCode = $('#sdms_language_selector').val();
        if (!selectedCode) {
            alert(sdmsAdmin.add_language_alert);
            return;
        }
        var selectedLanguage = sdmsAdmin.available_languages.find(function(lang) {
            return lang.code === selectedCode;
        });

        if (selectedLanguage) {
            // Generate flag URL
            var flag_url = selectedLanguage.flag;

            // Create new table row
            var newRow = '<tr>' +
                '<td>' +
                    '<img src="' + flag_url + '" alt="' + selectedLanguage.lang + '" class="sdms-flag-image">' +
                '</td>' +
                '<td>' +
                    selectedLanguage.lang +
                '</td>' +
                '<td>' +
                    selectedCode +
                '</td>' +
                '<td>' +
                    '<button type="button" class="button sdms-remove-language" data-code="' + selectedCode + '">' + sdmsAdmin.remove_label + '</button>' +
                    '<input type="hidden" name="sdms_languages[' + selectedCode + '][lang]" value="' + selectedLanguage.lang + '">' +
                    '<input type="hidden" name="sdms_languages[' + selectedCode + '][flag]" value="' + flag_url + '">' +
                '</td>' +
            '</tr>';

            // Append the new row to the table
            $('#sdms_languages_table tbody').append(newRow);

            // Remove the language from the selector
            $('#sdms_language_selector option[value="' + selectedCode + '"]').remove();
        }
    });

    // Remove Language button click
    $(document).on('click', '.sdms-remove-language', function(){
        var code = $(this).data('code');
        $(this).closest('tr').remove();

        // Add the language back to the selector
        var language = sdmsAdmin.available_languages.find(function(lang) {
            return lang.code === code;
        });
        if (language) {
            $('#sdms_language_selector').append('<option value="' + code + '">' + language.lang + '</option>');
        }
    });

    // Upload Icon button click
    $(document).on('click', '.sdms-upload-icon-button', function(){
        var fileType = $(this).data('file-type');
        var button = $(this);

        var frame = wp.media({
            title: sdmsAdmin.title,
            button: { text: sdmsAdmin.button },
            library: { type: 'image' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            // Update the hidden input with the new icon URL
            button.prev('input[type="hidden"]').val(attachment.url);
            // Update the displayed icon image
            button.closest('.sdms-file-type-icon').find('img.sdms-file-icon-image').attr('src', attachment.url);

            // Check if the new icon is custom
            var defaultUrl = sdmsAdmin.default_icons[fileType];
            if (attachment.url !== defaultUrl) {
                // If Reset button doesn't exist, add it
                if (button.siblings('.sdms-reset-icon-button').length === 0) {
                    button.after('<button type="button" class="button sdms-reset-icon-button" data-file-type="' + fileType + '" data-default-url="' + defaultUrl + '">' + sdmsAdmin.reset_label + '</button>');
                }
            } else {
                // If icon is default, remove the Reset button if it exists
                button.siblings('.sdms-reset-icon-button').remove();
            }
        });

        frame.open();
    });

    // Reset Icon button click
    $(document).on('click', '.sdms-reset-icon-button', function(){
        var fileType    = $(this).data('file-type');
        var defaultUrl  = $(this).data('default-url');
        var button      = $(this);

        // Update the displayed icon image to default
        button.closest('.sdms-file-type-icon').find('img.sdms-file-icon-image').attr('src', defaultUrl);

        // Update the hidden input with the default icon URL
        button.closest('.sdms-file-type-icon').find('input[type="hidden"]').val(defaultUrl);

        // Remove the Reset button
        button.remove();
    });

    /**
     * Handles the media upload functionality for language files in the admin area.
     * Allows users to upload and manage files for each language in the document edit screen.
     */
    var mediaUploader;

    // Upload button click event
    $('.sdms-upload-button').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var language = button.data('language'); // Get the language code
        var cell = button.closest('td'); // Get the parent table cell

        // Create the media frame
        mediaUploader = wp.media({
            title: sdmsAdmin.title, // Title of the media uploader window
            button: {
                text: sdmsAdmin.button // Text for the 'Select' button
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
            fileCell.html('<a href="' + attachment.url + '" target="_blank">' + sdmsAdmin.viewFile + '</a>');

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
        fileCell.html(sdmsAdmin.noFile);

        // Update the UI: Hide the remove button and show the upload button
        button.hide();
        cell.find('.sdms-upload-button').show();
    });

});
