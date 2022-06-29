declare let ajaxurl: any;

class SettingsExporterButton {
    constructor( selector: string ) {
        const exportButton = jQuery( selector );

        exportButton.on( 'click', ( e ) => {
            e.preventDefault();

            const nonce = jQuery( '#export_settings_button' ).data( 'nonce' );

            jQuery.ajax( {
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'cfw_generate_settings',
                    nonce,
                },
                success( response ) {
                    if ( response ) {
                        const data = `data:text/json;charset=utf-8,${encodeURIComponent( response )}`;
                        const element = document.createElement( 'a' );

                        element.setAttribute( 'href', data );
                        element.setAttribute( 'download', 'cfw-settings.json' );

                        document.body.appendChild( element );

                        element.click();
                        element.remove();
                    }
                },
            } );
        } );
    }
}

export default SettingsExporterButton;
