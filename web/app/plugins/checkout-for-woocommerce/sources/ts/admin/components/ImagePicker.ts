declare let wp: any;

class ImagePicker {
    private fileFrame: any;

    constructor( buttonSelector: string ) {
        if ( typeof wp.media === 'undefined' ) {
            return;
        }

        const wpMediaPostId = wp.media.model.settings.post.id; // Store the old id

        jQuery( document.body ).on( 'click', buttonSelector, ( event ) => {
            const parentContainer = jQuery( event.currentTarget ).parents( '.cfw-admin-upload-control-parent' );
            const fieldElement = parentContainer.find( 'input[type=hidden]' );
            const setToPostId = fieldElement.val();

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( this.fileFrame ) {
                // Set the post ID to what we want
                this.fileFrame.uploader.uploader.param( 'post_id', setToPostId );
                // Open frame
                this.fileFrame.open();

                return;
            }
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = setToPostId;

            // Create the media frame.
            this.fileFrame = wp.media.frames.file_frame = wp.media( {
                title: 'Select a image to upload',
                button: {
                    text: 'Use this image',
                },
                multiple: false,
            } );

            // When an image is selected, run a callback.
            this.fileFrame.on( 'select', () => {
                // We set multiple to false so only get one image from the uploader
                const attachment = this.fileFrame.state().get( 'selection' ).first().toJSON();

                // Do something with attachment.id and/or attachment.url here
                parentContainer.find( '.cfw-admin-image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' ).show();
                fieldElement.val( attachment.id );

                // Restore the main post ID
                wp.media.model.settings.post.id = wpMediaPostId;

                jQuery( document.body ).trigger( 'cfw_admin_field_changed' );
            } );

            // Finally, open the modal
            this.fileFrame.open();
        } );

        // Restore the main ID when the add media button is pressed
        jQuery( 'a.add_media' ).on( 'click', () => {
            wp.media.model.settings.post.id = wpMediaPostId;
        } );

        // Clear Image Button
        jQuery( document.body ).on( 'click', '.delete-custom-img', ( event ) => {
            event.preventDefault();
            const parentContainer = jQuery( event.currentTarget ).parents( '.cfw-admin-upload-control-parent' );
            const fieldElement = parentContainer.find( 'input[type=hidden]' );

            fieldElement.val( '' );
            parentContainer.find( '.cfw-admin-image-preview' ).hide();

            jQuery( document.body ).trigger( 'cfw_admin_field_changed' );
        } );
    }
}

export default ImagePicker;
