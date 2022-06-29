class OrderBumpsAdmin {
    constructor() {
        jQuery( document.body ).on( 'change', '#cfw_ob_display_for', ( event ) => {
            const element = jQuery( event.currentTarget );
            const specificProductsTargetElements = jQuery( '#cfw_ob_products, #cfw_ob_any_product, #cfw_ob_upsell' ).parents( 'tr' );
            const specificCategoriesTargetElements = jQuery( '#cfw_ob_categories' ).parents( 'tr' );

            if ( element.val() === 'specific_products' ) {
                specificProductsTargetElements.show();
            } else {
                specificProductsTargetElements.hide();
            }

            if ( element.val() !== 'specific_categories' ) {
                specificCategoriesTargetElements.hide();
            } else {
                specificCategoriesTargetElements.show();
            }
        } );

        jQuery( '#cfw_ob_display_for' ).trigger( 'change' );

        jQuery( document.body ).on( 'change', '#cfw_ob_upsell', () => {
            const upsellIncompatibleSettings = jQuery( '#cfw_ob_offer_quantity, [name="cfw_ob_item_removal_behavior"]' ).parents( 'tr' );

            if ( jQuery( '#cfw_ob_upsell' ).is( ':checked' ) ) {
                upsellIncompatibleSettings.hide();
            } else {
                upsellIncompatibleSettings.show();
            }
        } );

        jQuery( '#cfw_ob_upsell' ).trigger( 'change' );
    }
}

export default OrderBumpsAdmin;
