// CHANGES TO THIS FILE ARE MARKED IN ALL CAPS

declare let wc_address_i18n_params: any;

class AddressInternationalizationService {
    constructor() {
        jQuery( ( $ ) => {
            // wc_address_i18n_params is required to continue, ensure the object exists
            if ( typeof wc_address_i18n_params === 'undefined' ) {
                return false;
            }

            const locale_json = wc_address_i18n_params.locale.replace( /&quot;/g, '"' ); const
                locale = JSON.parse( locale_json );

            function field_is_required( field, is_required ) {
                if ( is_required ) {
                    field.find( 'label .optional' ).remove();
                    field.addClass( 'validate-required' );

                    if ( field.find( 'label .required' ).length === 0 ) {
                        field.find( 'label' ).append(
                            `&nbsp;<abbr class="required" title="${
                                wc_address_i18n_params.i18n_required_text
                            }">*</abbr>`,
                        );
                    }
                } else {
                    field.find( 'label .required' ).remove();
                    field.removeClass( 'validate-required woocommerce-invalid woocommerce-invalid-required-field' );

                    if ( field.find( 'label .optional' ).length === 0 ) {
                        field.find( 'label' ).append( `&nbsp;<span class="optional">(${wc_address_i18n_params.i18n_optional_text})</span>` );
                    }
                }

                // THIS IS A CUSTOMIZATION
                field.attr( 'data-parsley-required', is_required ? 'true' : 'false' );
            }

            // Handle locale
            $( document.body )
                .on( 'country_to_state_changing', ( event, country, wrapper ) => {
                    const thisform = wrapper; let
                        thislocale;

                    if ( typeof locale[ country ] !== 'undefined' ) {
                        thislocale = locale[ country ];
                    } else {
                        thislocale = locale.default;
                    }

                    const $postcodefield = thisform.find( '#billing_postcode_field, #shipping_postcode_field' );
                    const $cityfield     = thisform.find( '#billing_city_field, #shipping_city_field' );
                    const $statefield    = thisform.find( '#billing_state_field, #shipping_state_field' );

                    if ( !$postcodefield.attr( 'data-o_class' ) ) {
                        $postcodefield.attr( 'data-o_class', $postcodefield.attr( 'class' ) );
                        $cityfield.attr( 'data-o_class', $cityfield.attr( 'class' ) );
                        $statefield.attr( 'data-o_class', $statefield.attr( 'class' ) );
                    }

                    const locale_fields = JSON.parse( wc_address_i18n_params.locale_fields );

                    $.each( locale_fields, ( key, value ) => {
                        const field       = thisform.find( value );
                        const fieldLocale = $.extend( true, {}, locale.default[ key ], thislocale[ key ] );

                        // Labels.
                        if ( typeof fieldLocale.label !== 'undefined' ) {
                            field.find( 'label' ).html( fieldLocale.label );
                        }

                        // Placeholders.
                        if ( typeof fieldLocale.placeholder !== 'undefined' ) {
                            field.find( ':input' ).attr( 'placeholder', fieldLocale.placeholder );
                            field.find( ':input' ).attr( 'data-placeholder', fieldLocale.placeholder );
                            field.find( '.select2-selection__placeholder' ).text( fieldLocale.placeholder );
                        }

                        // Use the i18n label as a placeholder if there is no label element and no i18n placeholder.
                        if (
                            typeof fieldLocale.placeholder === 'undefined'
                            && typeof fieldLocale.label !== 'undefined'
                            && !field.find( 'label' ).length
                        ) {
                            field.find( ':input' ).attr( 'placeholder', fieldLocale.label );
                            field.find( ':input' ).attr( 'data-placeholder', fieldLocale.label );
                            field.find( '.select2-selection__placeholder' ).text( fieldLocale.label );
                        }

                        // Required.
                        if ( typeof fieldLocale.required !== 'undefined' ) {
                            field_is_required( field, fieldLocale.required );
                        } else {
                            field_is_required( field, false );
                        }

                        // Priority.
                        if ( typeof fieldLocale.priority !== 'undefined' ) {
                            field.data( 'priority', fieldLocale.priority );
                        }

                        // Hidden fields.
                        if ( key !== 'state' ) {
                            if ( typeof fieldLocale.hidden !== 'undefined' && fieldLocale.hidden === true ) {
                                field.hide().find( ':input' ).val( '' );
                            } else if ( field.is( ':hidden' ) && !field.hasClass( 'cfw-hidden' ) ) {
                                // CUSTOMIZATION:
                                // Make sure the field is already hidden and that it isn't hidden by us before showing it
                                // This ensures our 'Add Address Line 2/Company' links works
                                field.show();
                            }
                        }

                        // Class changes.
                        if ( Array.isArray( fieldLocale.class ) ) {
                            field.removeClass( 'form-row-first form-row-last form-row-wide' );
                            field.addClass( fieldLocale.class.join( ' ' ) );
                        }
                    } );

                    // NO SORT FOR YOU
                    // SORTING IS EVIL OK

                    // const fieldsets = $(
                    //     '.woocommerce-billing-fields__field-wrapper,'
                    //     + '.woocommerce-shipping-fields__field-wrapper,'
                    //     + '.woocommerce-address-fields__field-wrapper,'
                    //     + '.woocommerce-additional-fields__field-wrapper .woocommerce-account-fields',
                    // );

                    // fieldsets.each( ( index, fieldset ) => {
                    //     const rows    = $( fieldset ).find( '.form-row' );
                    //     const wrapper = rows.first().parent();
                    //
                    //     // Before sorting, ensure all fields have a priority for bW compatibility.
                    //     let last_priority = 0;
                    //
                    //     rows.each( function () {
                    //         if ( !$( this ).data( 'priority' ) ) {
                    //             $( this ).data( 'priority', last_priority + 1 );
                    //         }
                    //         last_priority = $( this ).data( 'priority' );
                    //     } );
                    //
                    //     // Sort the fields.
                    //     rows.sort( ( a, b ) => {
                    //         const asort = parseInt( $( a ).data( 'priority' ), 10 );
                    //         const bsort = parseInt( $( b ).data( 'priority' ), 10 );
                    //
                    //         if ( asort > bsort ) {
                    //             return 1;
                    //         }
                    //         if ( asort < bsort ) {
                    //             return -1;
                    //         }
                    //         return 0;
                    //     } );
                    //
                    //     rows.detach().appendTo( wrapper );
                    // } );
                } )
                .trigger( 'wc_address_i18n_ready' );
        } );
    }
}

export default AddressInternationalizationService;
