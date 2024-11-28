jQuery(document).ready(function($) {

    /**
     * Handles the modal display and form submission for sending documents via email.
     */

    // Open the modal when the button is clicked
    $('.sdms-send-document-button').on('click', function() {
        var postId = $(this).data('post-id');
        $('#sdms-post-id').val(postId);
        $('#sdms-send-document-modal').show();
    });

    // Close the modal when the close button is clicked
    $('.sdms-close-modal').on('click', function() {
        $('#sdms-send-document-modal').hide();
        $('#sdms-send-document-form')[0].reset();
        $('#sdms-send-document-message').empty();
    });

    // Close the modal when clicking outside the content
    $(window).on('click', function(event) {
        if ($(event.target).is('#sdms-send-document-modal')) {
            $('#sdms-send-document-modal').hide();
            $('#sdms-send-document-form')[0].reset();
            $('#sdms-send-document-message').empty();
        }
    });

    // Handle the form submission
    $('#sdms-send-document-form').on('submit', function(e) {
        e.preventDefault();

        var postId = $('#sdms-post-id').val();
        var senderName = $('#sdms-sender-name').val();
        var recipientEmail = $('#sdms-recipient-email').val();
        var nonce = sdmsAjaxModal.nonce;

        $.ajax({
            url: sdmsAjaxModal.ajax_url,
            type: 'post',
            data: {
                action: 'sdms_send_document',
                post_id: postId,
                sender_name: senderName,
                recipient_email: recipientEmail,
                nonce: nonce
            },
            success: function(response) {
                $('#sdms-send-document-message').html('<p>' + response.data.message + '</p>');
                if (response.success) {
                    $('#sdms-send-document-form')[0].reset();
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'An error occurred.';
                $('#sdms-send-document-message').html('<p>' + errorMessage + '</p>');
            }
        });
    });

});
