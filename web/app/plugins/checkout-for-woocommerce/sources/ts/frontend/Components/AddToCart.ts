import AddToCartAction from '../Actions/AddToCartAction';
import DataService     from '../Services/DataService';

class AddToCart {
    constructor() {
        if ( DataService.getSetting( 'enable_ajax_add_to_cart' ) ) {
            jQuery( document.body ).on( 'submit', 'form.cart', this.addToCartFormSubmit );
        }
    }

    addToCartFormSubmit( e ): void {
        const form = jQuery( e.currentTarget );

        if ( form.closest( '.product' ).hasClass( 'product-type-external' ) ) {
            return;
        }

        e.preventDefault();

        const button = form.find( 'button[type="submit"]' );
        const productData = form.serializeArray();
        let hasProductId = false;

        // Check for woocommerce custom quantity code
        // https://docs.woocommerce.com/document/override-loop-template-and-show-quantities-next-to-add-to-cart-buttons/
        jQuery.each( productData, ( key, formItem ) => {
            if ( formItem.name === 'productID' || formItem.name === 'add-to-cart' ) {
                if ( formItem.value ) {
                    hasProductId = true;
                    return false;
                }
            }

            return true;
        } );

        let productID: string | boolean = false;

        // If no product id found , look for the form action URL
        if ( !hasProductId && form.attr( 'action' ) ) {
            const isUrl = form.attr( 'action' ).match( /add-to-cart=([0-9]+)/ );
            productID = isUrl ? isUrl[ 1 ] : false;
        }

        // if button as name add-to-cart get it and add to form
        if ( button.attr( 'name' ) && button.attr( 'name' ) === 'add-to-cart' && button.attr( 'value' ) ) {
            productID = button.attr( 'value' );
        }

        if ( productID ) {
            productData.push( { name: 'add-to-cart', value: productID } );
        }

        button.addClass( 'loading' );

        // Trigger event.
        jQuery( document.body ).trigger( 'adding_to_cart', [ button, productData ] );

        new AddToCartAction( button ).load( jQuery.param( productData ) );
    }
}

export default AddToCart;
