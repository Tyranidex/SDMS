jQuery(document).ready(function($) {
    /**
     * Handles the icon upload and reset functionality on the settings page.
     */

    // Function to update language options (if needed)
    function updateLanguageOptions() {
        var addedCodes = [];
        $('#sdms_languages_table input[name^="sdms_languages"]').each(function() {
            var code = $(this).attr('name').match(/\[(.*?)\]/)[1];
            if ($.inArray(code, addedCodes) === -1) {
                addedCodes.push(code);
            }
        });
        $('#sdms_language_selector option').each(function() {
            var option = $(this);
            if ($.inArray(option.val(), addedCodes) !== -1) {
                option.remove();
            }
        });
    }

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
            alert(sdmsIconUploader.add_language_alert);
            return;
        }
        var selectedLanguage = availableLanguages.find(function(lang) {
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
                    '<button type="button" class="button sdms-remove-language" data-code="' + selectedCode + '">' + sdmsIconUploader.remove_label + '</button>' +
                    '<input type="hidden" name="sdms_languages[' + selectedCode + '][lang]" value="' + selectedLanguage.lang + '">' +
                    '<input type="hidden" name="sdms_languages[' + selectedCode + '][flag]" value="' + flag_url + '">' +
                '</td>' +
            '</tr>';

            // Append the new row to the table
            $('#sdms_languages_table tbody').append(newRow);

            // Update language selector options
            updateLanguageOptions();
        }
    });

    // Remove Language button click
    $(document).on('click', '.sdms-remove-language', function(){
        var code = $(this).data('code');
        $(this).closest('tr').remove();

        // Add the language back to the selector
        var language = availableLanguages.find(function(lang) {
            return lang.code === code;
        });
        if (language) {
            $('#sdms_language_selector').append('<option value="' + code + '">' + language.lang + '</option>');
        }
    });

    // Change Icon button click
    $(document).on('click', '.sdms-upload-icon-button', function(){
        var fileType = $(this).data('file-type');
        var button = $(this);

        var frame = wp.media({
            title: sdmsIconUploader.title,
            button: { text: sdmsIconUploader.button },
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
            var defaultUrl = sdmsIconUploader.default_icons[fileType];
            if (attachment.url !== defaultUrl) {
                // If Reset button doesn't exist, add it
                if (button.siblings('.sdms-reset-icon-button').length === 0) {
                    button.after('<button type="button" class="button sdms-reset-icon-button" data-file-type="' + fileType + '" data-default-url="' + defaultUrl + '">' + sdmsIconUploader.reset_label + '</button>');
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

    // Initialize language options on page load
    updateLanguageOptions();
});
