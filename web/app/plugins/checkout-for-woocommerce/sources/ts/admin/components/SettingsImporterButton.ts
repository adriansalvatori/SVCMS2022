class SettingsImporterButton {
    constructor( selector: string ) {
        const importButton = jQuery( selector );

        importButton.on( 'click', ( e ) => {
            // eslint-disable-next-line no-alert
            if ( !window.confirm( 'Are you sure you want replace your current settings?' ) ) {
                e.preventDefault();
            }
        } );
    }
}

export default SettingsImporterButton;
