jQuery(document).ready(function($) {
    /**
     * Handles the icon upload functionality on the settings page.
     * Allows administrators to upload custom icons for file types.
     */
    $('.sdms-upload-icon-button').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var fileType = button.data('file-type'); // Get the file type (e.g., 'pdf', 'word')

        // Create a new media frame
        var frame = wp.media({
            title: sdmsIconUploader.title, // Title of the media uploader window
            button: { text: sdmsIconUploader.button }, // Text for the 'Select' button
            library: { type: 'image' }, // Only allow images
            multiple: false // Only allow single selection
        });

        // When an image is selected, run a callback
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            // Update the hidden input with the new icon URL
            button.prevAll('input').val(attachment.url);
            // Update the displayed icon image
            button.prevAll('img').attr('src', attachment.url);
        });

        // Open the media uploader frame
        frame.open();
    });
});
