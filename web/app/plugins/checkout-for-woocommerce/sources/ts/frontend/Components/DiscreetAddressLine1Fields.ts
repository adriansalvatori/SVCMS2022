import cfwBuildAddress1 from '../../functions/cfwBuildAddress1';

class DiscreetAddressLine1Fields {
    setupListeners(): void {
        this.addListener( 'shipping_' );
        this.addListener( 'billing_' );

        this.setAddress1( 'shipping_' );
        this.setAddress1( 'billing_' );
    }

    addListener( prefix: string ): void {
        jQuery( document.body ).on( 'change', `#${prefix}country, #${prefix}house_number, #${prefix}street_name`, () => {
            this.setAddress1( prefix );
        } );
    }

    setAddress1( prefix: string ): void {
        const country     = ( document.getElementById( `${prefix}country`      ) as HTMLInputElement )?.value;
        const houseNumber = ( document.getElementById( `${prefix}house_number` ) as HTMLInputElement )?.value;
        const streetName  = ( document.getElementById( `${prefix}street_name`  ) as HTMLInputElement )?.value;

        if ( [ country, houseNumber, streetName ].includes( undefined ) ) {
            return;
        }

        const newValue = cfwBuildAddress1( country, houseNumber, streetName );

        jQuery( `#${prefix}address_1` ).val( newValue ).trigger( 'change' );
    }
}

export default DiscreetAddressLine1Fields;
