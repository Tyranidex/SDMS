jQuery(document).ready(function($) {

    // Ouvrir la modale lorsque le bouton est cliqué
    $('.sdms-send-document-button').on('click', function() {
        var postId = $(this).data('post-id');
        $('#sdms-post-id').val(postId);
        $('#sdms-send-document-modal').show();
    });

    // Fermer la modale lorsque le bouton de fermeture est cliqué
    $('.sdms-close-modal').on('click', function() {
        $('#sdms-send-document-modal').hide();
        $('#sdms-send-document-form')[0].reset();
        $('#sdms-send-document-message').empty();
    });

    // Fermer la modale en cliquant en dehors du contenu
    $(window).on('click', function(event) {
        if ($(event.target).is('#sdms-send-document-modal')) {
            $('#sdms-send-document-modal').hide();
            $('#sdms-send-document-form')[0].reset();
            $('#sdms-send-document-message').empty();
        }
    });

    // Gérer la soumission du formulaire
    $('#sdms-send-document-form').on('submit', function(e) {
        e.preventDefault();

        var postId = $('#sdms-post-id').val();
        var senderName = $('#sdms-sender-name').val();
        var recipientEmail = $('#sdms-recipient-email').val();
        var nonce = sdmsAjax.nonce;

        $.ajax({
            url: sdmsAjax.ajax_url,
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
                $('#sdms-send-document-message').html('<p>' + xhr.responseJSON.data.message + '</p>');
            }
        });
    });

});
