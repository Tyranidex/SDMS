<?php
/**
 * Send Document Modal Template
 *
 * This template is included in the footer to display the modal for sending documents via email.
 */
?>
<div id="sdms-send-document-modal" class="sdms-modal" style="display:none;">
    <div class="sdms-modal-content">
        <span class="sdms-close-modal">&times;</span>
        <h2><?php _e( 'Send Document', 'sdms' ); ?></h2>
        <form id="sdms-send-document-form">
            <input type="hidden" name="post_id" id="sdms-post-id" value="">

            <label for="sdms-sender-name"><?php _e( 'Your Name:', 'sdms' ); ?></label>
            <input type="text" name="sender_name" id="sdms-sender-name" required>

            <label for="sdms-recipient-email"><?php _e( 'Recipient Email:', 'sdms' ); ?></label>
            <input type="email" name="recipient_email" id="sdms-recipient-email" required>

            <input type="submit" value="<?php _e( 'Send', 'sdms' ); ?>">
        </form>
        <div id="sdms-send-document-message"></div>
    </div>
</div>
