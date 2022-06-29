/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

( function ( $ ) {

    "use strict";

    $( document ).ready( function () {

        /** Show/Hide Design options depends of Button(s) style */
        const $buttonType = $( '#mdp_liker_design_settings_style' );
        function ShowButtonsStyles() {

            if ( $buttonType.val() !== 'style-unset' ) {
                $( '.mdp-tab-name-design tr:not(:nth-child(1))' ).show( 500 );
            } else {
                $( '.mdp-tab-name-design tr:not(:nth-child(1))' ).hide( 0 );
            }

        }
        $buttonType.on( 'change', ShowButtonsStyles );
        ShowButtonsStyles();

        /** Show/Hide switcher voting limit */
        const $limitByIp = $( '#mdp_liker_backend_settings_limit_by_ip' );
        function limitByIP() {

            if ( $limitByIp.prop( 'checked' ) ) {
                $( '#mdp_liker_backend_settings_voting_limit' ).closest( 'tr' ).show( 300 );
                $( '#mdp_liker_backend_settings_limit_msg' ).closest( 'tr' ).show( 300 );
            } else {
                $( '#mdp_liker_backend_settings_voting_limit' ).closest( 'tr' ).hide( 300 );
                $( '#mdp_liker_backend_settings_limit_msg' ).closest( 'tr' ).hide( 300 );
            }

        }
        $limitByIp.on( 'change', limitByIP );
        limitByIP();

        /** Show/Hide switcher results before voting */
        const $resultsFront = $( '#mdp_liker_backend_settings_results' );
        function ShowResultsFront() {

            if ( $resultsFront.val() === 'show' ) {
                $( '#mdp_liker_backend_settings_display' ).closest( 'tr' ).show( 300 );
            } else {
                $( '#mdp_liker_backend_settings_display' ).closest( 'tr' ).hide( 300 );
            }

        }
        $resultsFront.on( 'change', ShowResultsFront );
        ShowResultsFront();

        /** Show/hide Advanced Schema Markup field */
        let jsonLDSwitcher = $( '#mdp_liker_schema_settings_advanced_markup' );
        function ShowJsonLDField() {

            if ( jsonLDSwitcher.prop( 'checked' ) === true ) {
                jsonLDSwitcher.closest( 'tr' ).next().show( 300 );
            } else {
                jsonLDSwitcher.closest( 'tr' ).next().hide( 300 );
            }
        }
        jsonLDSwitcher.on( 'click', ShowJsonLDField );
        ShowJsonLDField();

        /** Show/hide Advanced Schema Markup field */
        let markupSwitcher = $( '#mdp_liker_schema_settings_google_search_results' );
        function ShowAdvancedMarkupField() {

            if ( markupSwitcher.prop( 'checked' ) === true ) {

                markupSwitcher.closest( 'tr' ).next().show( 300 );
                ShowJsonLDField();

            } else {

                markupSwitcher.closest( 'tr' ).next().hide( 300 ).next().hide( 300 );

            }

        }
        markupSwitcher.on( 'click', ShowAdvancedMarkupField );
        ShowAdvancedMarkupField();

        /** Liker type Change */
        let $type = $( '#mdp_liker_general_settings_type' );
        $type.on( 'change', function() {

            const $capt1 = $( '#mdp_liker_general_settings_caption_1' ).parent().parent();
            const $capt2 = $( '#mdp_liker_general_settings_caption_2' ).parent().parent();
            const $capt3 = $( '#mdp_liker_general_settings_caption_3' ).parent().parent();

            if ( $( this ).val() === 'three-buttons' ) {
                $capt1.show( 200 );
                $capt2.show( 200 );
                $capt3.show( 200 );
            } else if ( $( this ).val() === 'two-buttons' ) {
                $capt1.show( 200 );
                $capt2.hide( 100 );
                $capt3.show( 200 );
            } else if ( $( this ).val() === 'one-button' ) {
                $capt1.show( 200 );
                $capt2.hide( 100 );
                $capt3.hide( 100 );
            }
        } );
        $type.change(); // Refresh caption inputs.

        /** Shortcode switcher settings */
        const tab = 'mdp_liker_shortcode_settings';

        /** Show/hide setting by switcher **/
        function displayBySwitcher( switcherId, settingId ) {
            $( switcherId ).prop( 'checked' ) ?
                $( settingId ).closest( 'tr' ).show( 300 ) :
                $( settingId ).closest( 'tr' ).hide( 300 );
        }

        /** Show/Hide for multiple options */
        function displayBySwitcher2( switcherId, switcherId2, settingId ) {
            $( switcherId ).prop( 'checked' ) && $( switcherId2 ).prop( 'checked' ) ?
                $( settingId ).closest( 'tr' ).show( 300 ) :
                $( settingId ).closest( 'tr' ).hide( 300 );
        }

        // Image
        $( `#${ tab }_top_image` ).on( 'click', function () {

            if ( $( `#${ tab }_top_image` ).prop( 'checked' ) ) {

                $( `#${ tab }_top_image_size` ).closest( 'tr' ).show( 300 );
                $( `#${ tab }_top_equal` ).closest( 'tr' ).show( 300 );
                if ( $( `#${ tab }_top_equal` ).prop( 'checked' ) ) { $( `#${ tab }_top_height` ).closest( 'tr' ).show( 300 ); }

            } else {

                $( `#${ tab }_top_image_size` ).closest( 'tr' ).hide( 300 );
                $( `#${ tab }_top_equal` ).closest( 'tr' ).hide( 300 );
                $( `#${ tab }_top_height` ).closest( 'tr' ).hide( 300 );
            }

        } );

        // Equal height
        $( `#${ tab }_top_equal` ).on( 'click', function () {

            if ( $( `#${ tab }_top_equal` ).prop( 'checked' ) ) {

                $( `#${ tab }_top_height` ).closest( 'tr' ).show( 300 );

            } else {

                $( `#${ tab }_top_height` ).closest( 'tr' ).hide( 300 );
            }

        } );

        // Title
        $( `#${ tab }_top_title` ).on( 'click', function () {


            $( `#${ tab }_top_title` ).prop( 'checked' ) ?
                $( `#${ tab }_top_title_tag` ).closest( 'tr' ).show( 300 ) :
                $( `#${ tab }_top_title_tag` ).closest( 'tr' ).hide( 300 );

        } );

        // Rating
        $( `#${ tab }_top_rating` ).on( 'click', function () {

            if ( $( `#${ tab }_top_rating` ).prop( 'checked' ) ) {
                $( `#${ tab }_top_size` ).closest( 'tr' ).show( 300 )
                $( `#${ tab }_dashicons` ).closest( 'tr' ).show( 300 )
            } else {
                $( `#${ tab }_top_size` ).closest( 'tr' ).hide( 300 );
                $( `#${ tab }_dashicons` ).closest( 'tr' ).hide( 300 );
            }

        } );

        displayBySwitcher( `#${ tab }_top_image`, `#${ tab }_top_image_size` );
        displayBySwitcher( `#${ tab }_top_image`, `#${ tab }_top_equal` );
        displayBySwitcher( `#${ tab }_top_image`, `#${ tab }_top_height` );
        displayBySwitcher( `#${ tab }_top_title`, `#${ tab }_top_title_tag` );
        displayBySwitcher( `#${ tab }_top_rating`, `#${ tab }_top_size` );

        displayBySwitcher2( `#${ tab }_top_image`, `#${ tab }_top_equal`, `#${ tab }_top_height` );

    } );

    /**
     * Reset Liker results.
     **/
    $( '.mdp-reset' ).on( 'click', function( e ) {
        e.preventDefault();

        new duDialog( 'Reset Liker', 'Do you really want to reset your liker data?', duDialog.OK_CANCEL, // jshint ignore:line
            {
                okText: 'Okay',
                callbacks: {
                    okClick: function() {

                        /** Disable button and show process. */
                        $( '#reset' ).attr( 'disabled', true ).addClass( 'mdp-spin' ).find( '.material-icons' ).text('refresh');

                        /** Prepare data for AJAX request. */
                        let data = {
                            action: 'reset_liker',
                            nonce: window.mdpLikerReset.nonce,
                            doReset: 1
                        };

                        /** Make POST AJAX request. */
                        $.post( window.mdpLikerReset.ajaxURL, data, function( response ) {

                            /** Show Error message if returned false. */
                            if ( ! response ) {
                                console.log( response );
                                new duDialog( 'Error', 'Looks like an error has occurred. Please try again later.' );
                            } else {
                                new duDialog( 'Success', 'Data successfully cleared.' );
                            }

                        }, 'json' ).fail(function( response ) {

                            /** Show Error message if returned some data. */
                            console.log( response );
                            new duDialog( 'Reset Liker: Error', 'Looks like an error has occurred. Please try again later.' );

                        } ).always(function() {
                            /** Enable button again. */
                            $( '#reset' ).attr( 'disabled', false ).removeClass( 'mdp-spin' ).find( '.material-icons' ).text('close');
                        } );

                        this.hide();  // hides the dialog
                    }
                }
            });
    });

} ( jQuery ) );
