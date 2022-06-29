declare let WebFont: any;

class FontSelector {
    constructor( selector: string ) {
        const element = jQuery( selector );

        if ( !element.length ) {
            return;
        }

        element.on( 'change',  ( event ) => {
            const selected = element.find( 'option:selected' ).text();

            jQuery( event.currentTarget ).css( 'font-family', selected );
            jQuery( document.body ).trigger( 'cfw_admin_field_changed' );
        } );

        element.one( 'select2:open', () => {
            const select2Id = element.prop( 'id' );
            const fontResults = jQuery( `#select2-${select2Id}-results` );
            let timeout;

            fontResults.on( 'scroll', ( event ) => {
                clearTimeout( timeout );
                timeout = setTimeout( () => {
                    fontResults.find( 'li' ).not( '.font-loaded' ).not( ':first-child' ).each( ( i, liElement ) => {
                        const li = jQuery( liElement );
                        const fontName = li.text();
                        const topOfResults = fontResults.offset().top;
                        const bottomOfResults = topOfResults + fontResults.outerHeight();
                        const topOfItem = li.offset().top;
                        const bottomOfItem = topOfItem + li.outerHeight();

                        if ( bottomOfResults > topOfItem && topOfResults < bottomOfItem ) {
                            WebFont.load( {
                                google: {
                                    families: [ fontName ],
                                    text: fontName,
                                },
                                fontactive( familyName ) {
                                    li.css( 'font-family', familyName );
                                    li.addClass( 'font-loaded' );
                                },
                            } );
                        }
                    } );
                }, 100 );
            } ).trigger( 'scroll' );
        } );
    }
}

export default FontSelector;
