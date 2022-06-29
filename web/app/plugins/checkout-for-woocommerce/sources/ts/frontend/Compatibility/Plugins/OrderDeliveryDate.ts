import Compatibility from '../Compatibility';

class OrderDeliveryDate extends Compatibility {
    constructor() {
        super( 'OrderDeliveryDate' );
    }

    load(): void {
        jQuery( document.body ).one( 'updated_checkout', () => {
            jQuery( 'input[name="shipping_method[0]"]:checked' ).trigger( 'change' );
        } );
    }
}

export default OrderDeliveryDate;
