<!-- templates/send-document-modal.php -->

<div id="sdms-send-document-modal" class="sdms-modal" style="display:none;">
    <div class="sdms-modal-content">
        <span class="sdms-close-modal">&times;</span>
        <h2><?php _e( 'Envoyer le document', 'sdms' ); ?></h2>
        <form id="sdms-send-document-form">
            <input type="hidden" name="post_id" id="sdms-post-id" value="<?php echo esc_attr( get_the_ID() ); ?>">

            <label for="sdms-sender-name"><?php _e( 'Votre nom:', 'sdms' ); ?></label>
            <input type="text" name="sender_name" id="sdms-sender-name" required>

            <label for="sdms-recipient-email"><?php _e( 'Email du destinataire:', 'sdms' ); ?></label>
            <input type="email" name="recipient_email" id="sdms-recipient-email" required>

            <input type="submit" value="<?php _e( 'Envoyer', 'sdms' ); ?>">
        </form>
        <div id="sdms-send-document-message"></div>
    </div>
</div>
