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

( function () {

    'use strict';

    document.addEventListener( 'DOMContentLoaded', () => {

        /** If there is no Liker on this page then exit. */
        if ( document.getElementsByClassName( 'mdp-liker-box' ).length === 0 ) { return; }

        /** Init for each Liker box */
        document.querySelectorAll( '.mdp-liker-box' ).forEach($wrapper => new mdpLikerRating( $wrapper ) );

    } );

    /**
     * Liker WordPress Plugin Main Class
     */
    class mdpLikerRating {

        constructor ( $wrapper ) {

            /** Data from WP: ajax url and timestamp of last reset.
             * @param window.mdpLiker
             */
            this.wpData = window.mdpLiker;

            this.host = window.location.hostname;
            this.id = $wrapper.id.replace( 'mdp-liker-', '' ); // Page/Post id
            this.likerButtons = $wrapper.querySelectorAll('.mdp-liker-buttons button' );

            /** Flag to identify users voting for the first time. */
            this.fNew = true;
            this.canceled = false;
            this.voted = false;

            /** Current values of the user's like. */
            this.val_1 = null;
            this.val_2 = null;
            this.val_3 = null;

            /** Previous values of the user's like. */
            this.old_val_1 = null;
            this.old_val_2 = null;
            this.old_val_3 = null;

            this.previousValue = []; // We use it for calculation
            this.sessionPostID = this.id;

            this.likerHover = false;
            this.likerTouch = false;
            this.previousActive = document.activeElement;

            const { displayBefore, results } = this.wpData;

            /** If there are no likerData, this is the first visit. */
            this.createLocalStorage();

            /** Read likerData from Local Storage */
            this.likerData = JSON.parse( window.localStorage.getItem( 'mdpLikerData' ) )[ this.host ];

            /** Remove any likerData if it was created before the last reset */
            this.flushExpiredLocalStorage( $wrapper );

            /** Store nad display old votes by guid */
            this.findOldVotes( $wrapper );

            /** Add Event listeners */
            this.addListeners();

            /** Async call for escaping HTML cache of the results */
            if ( displayBefore === 'on' || !this.fNew ) {
                this.getResults();
            }

            /** Display results if user already vote */
            if( "show" === results ){
                this.displayResults( ! this.fNew );
            }

        }

        /**
         * Create new local storage
         */
        createLocalStorage() {

            const localStorage = JSON.parse( window.localStorage.getItem( 'mdpLikerData' ) );
            let likerLocal = {};

            if ( localStorage === null ) {

                /** Create first and new liker data */
                likerLocal[ this.host ] = {

                    "guid": this.getGUID(),
                    "timestamp": Math.floor( Date.now() / 1000 ),

                };

                /** Create and save Liker Data in Local Storage */
                window.localStorage.setItem( 'mdpLikerData', JSON.stringify( likerLocal ) );

            } else if ( typeof localStorage.guid !== 'undefined' && typeof localStorage.timestamp !== 'undefined' ) {

                /** Migrate from old liker data object */
                likerLocal[ this.host ] = JSON.parse( window.localStorage.getItem( 'mdpLikerData' ) );

                /** Create and save Liker Data in Local Storage */
                window.localStorage.setItem( 'mdpLikerData', JSON.stringify( likerLocal ) );

            }

        }

        /**
         * Remove all likerData if they are created before last reset
         */
        flushExpiredLocalStorage() {

            const { resetTimestamp } = this.wpData;

            if ( resetTimestamp === undefined ) { return; } //Exit if no liker variables from WP

            // noinspection JSUnresolvedVariable
            if ( this.likerData.timestamp < resetTimestamp ) {

                let likerLocal = JSON.parse( window.localStorage.getItem( 'mdpLikerData' ) );

                /** Recreate likerData, like for first visit. */
                likerLocal[ this.host ] = {

                    "guid": this.getGUID(),
                    "timestamp": Math.floor( Date.now() / 1000 ),

                };

                /** Remember likerData in Local Storage. */
                window.localStorage.setItem( 'mdpLikerData', JSON.stringify( likerLocal ) );

            }

        }

        /**
         * Update local storage after voting
         */
        updateLocalStorage() {

            if ( this.val_1 + this.val_2 + this.val_3 > 0 ) {

                this.likerData[ 'val_1_' + this.id ] = this.val_1;
                this.likerData[ 'val_2_' + this.id ] = this.val_2;
                this.likerData[ 'val_3_' + this.id ] = this.val_3;


            } else {

                delete this.likerData[ 'val_1_' + this.id ];
                delete this.likerData[ 'val_2_' + this.id ];
                delete this.likerData[ 'val_3_' + this.id ];

            }

            this.likerData.timestamp = Math.floor( Date.now() / 1000 );

            let localStorage = JSON.parse( window.localStorage.getItem( 'mdpLikerData' ) );
            localStorage[ this.host ] = this.likerData;

            window.localStorage.setItem( 'mdpLikerData', JSON.stringify( localStorage ) );

        }

        /**
         * Pseudo GUID. We use unique user id to identify previously voted users.
         * @returns {String}
         **/
        getGUID() {

            // noinspection SpellCheckingInspection
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {

                const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);

            } );

        }

        /**
         * Find ald votes and store results
         * @param $wrapper
         */
        findOldVotes( $wrapper ) {

            if ( $wrapper.getAttribute( 'data-memory' ) !== 'on' ) { return; } // Exit if display before voting is disabled
            if ( this.likerData[ "val_1_" + this.id ] === undefined ) { return; } // Exit if no one votes stored

            this.fNew = false; // Already liked in this post/page.

            /** Get previous like value. */
            this.previousValue[1] = this.likerData[ "val_1_" + this.id ];
            this.previousValue[2] = this.likerData[ "val_2_" + this.id ];
            this.previousValue[3] = this.likerData[ "val_3_" + this.id ];

            /** Find buttons to make it active */
            let makeActiveButtons = {};
            this.previousValue.forEach( ( value, index) => {

                if ( value > 0 ) {

                    makeActiveButtons = document.querySelectorAll(`#mdp-liker-${ this.id } #mdp-liker-btn-${ index }` );

                }

            } );

            /** Make buttons active */
            makeActiveButtons.forEach( $button => $button.classList.add( 'mdp-active' ) );

        }

        /**
         * Add eventListeners to buttons
         */
        addListeners() {

            for ( let i = 0; i < this.likerButtons.length; i++ ) {

                // Listen liker buttons click
                this.likerButtons[i].removeEventListener('click', ( e ) => { this.doLike( e ) } );
                this.likerButtons[i].addEventListener('click', ( e ) => { this.doLike( e ) } );

                // Mouse move events
                this.likerButtons[i].addEventListener( 'mouseenter', ( e ) => {
                    this.likerHover = e.target.id;
                }, { passive: true } );
                this.likerButtons[i].addEventListener( 'mouseleave', () => {
                    this.likerHover = false;
                }, { passive: true } );

                // Touch events
                this.likerButtons[ i ].addEventListener( "touchstart", ( e ) => {
                    this.likerTouch = e.target.id;
                }, { passive: true } );
                this.likerButtons[ i ].addEventListener( "touchend", () => {
                    this.likerTouch = false;
                }, { passive: true } );

            }

            /** Store focused element */
            window.addEventListener( 'keypress', () => {
                this.previousActive = document.activeElement;
            }, false );

        }

        /**
         * Click on the button
         *
         * @param {object} e
         * @return
         **/
        doLike( e) {

            /** Rating cheat prevention */
            if ( this.chitChecker( e ) ) { return; }

            e.preventDefault();
            this.pageDisable();
            this.canceled = false;

            /** Store old like values to calculate it on change. */
            if ( this.old_val_1 === 0 ) {

                this.old_val_1 = this.likerData[ "old_val_1_" + this.id ];
                this.old_val_2 = this.likerData[ "old_val_2_" + this.id ];
                this.old_val_3 = this.likerData[ "old_val_3_" + this.id ];

            } else {

                this.old_val_1 = this.val_1;
                this.old_val_2 = this.val_2;
                this.old_val_3 = this.val_3;

            }

            /** Detect clicked button id */
            const voteId = this.catchVote( e.target );

            /** Vote cancel */
            this.cancelLike();

            /** Prepare request data */
            const data = {
                'liker_id': this.id,
                'val_1': this.val_1,
                'val_2': this.val_2,
                'val_3': this.val_3,
                'voteId': voteId,
                'guid': this.likerData.guid,
                'new_like': this.fNew
            };

            /** Proceed request and get response */
            this.request( data );

        }


        /**
         * Returns true algorithm cheating try, or false is everything is OK
         * @param e
         * @returns {boolean}
         */
        chitChecker( e ) {

            const checkMouse = e.target.id === this.likerHover;
            const checkTouch = e.target.id === this.likerTouch;
            const checkFocus = e.target.id === this.previousActive.id;

            return ! checkMouse && ! checkFocus && !checkTouch;

        }

        /**
         * Making page blur if multiple clicks
         */
        pageDisable() {

            if ( document.querySelectorAll( '.mdp-liker-msg' ).length > 2 ) {

                // Add blur
                document.body.style.filter = `blur( ${ document.querySelectorAll( '.mdp-liker-msg' ).length - 2 }px )`;

                // Remove blur
                document.body.addEventListener( 'click', function ( e ) {

                    if ( ! e.target.id.includes( 'mdp-liker-btn-' ) ) {

                        document.body.style.filter = 'unset';

                    }

                } )

            }

        }

        /**
         * Check id of clicked button and set voting variables
         * @param $button
         * @return {number}
         */
        catchVote( $button ) {

            $button = $button.closest( 'button' ); // Fix clicking on text in button, but not on button

            this.val_1 = 0;
            this.val_2 = 0;
            this.val_3 = 0;
            let vote = 0;

            if ( $button.id === 'mdp-liker-btn-1' ) {

                this.val_1 = 1;
                vote = 1;

            } else if ( $button.id === 'mdp-liker-btn-2' ) {

                this.val_2 = 1;
                vote = 2;

            } else if ( $button.id === 'mdp-liker-btn-3' ) {

                this.val_3 = 1;
                vote = 3;

            }

            return vote;

        }

        /**
         * Cancel vote if button clicked again
         */
        cancelLike() {

            // Re-voting in current session
            if ( this.old_val_1 !== null && this.val_1 !== null ) {

                if ( this.old_val_1 === 1 && this.val_1 === 1 ) {

                    this.old_val_1 = 0;
                    this.val_1 = 0;
                    this.canceled = true;

                }

                if ( this.old_val_2 === 1 && this.val_2 === 1 ) {

                    this.old_val_2 = 0;
                    this.val_2 = 0;
                    this.canceled = true;

                }

                if ( this.old_val_3 === 1 && this.val_3 === 1 ) {

                    this.old_val_3 = 0;
                    this.val_3 = 0;
                    this.canceled = true;

                }

            }

            // Revoting after page refresh
            if ( this.old_val_1 === null && this.val_1 !== null ) {

                if ( this.likerData[ `val_1_${ this.id }` ] === 1 && this.val_1 === 1 ) {

                    this.old_val_1 = 0;
                    this.val_1 = 0;
                    this.canceled = true;

                }

                if ( this.likerData[ `val_2_${ this.id }` ] === 1 && this.val_2 === 1 ) {

                    this.old_val_2 = 0;
                    this.val_2 = 0;
                    this.canceled = true;

                }

                if ( this.likerData[ `val_3_${ this.id }` ] === 1 && this.val_3 === 1 ) {

                    this.old_val_3 = 0;
                    this.val_3 = 0;
                    this.canceled = true;

                }

            }

        }


        /**
         * Get likes data by id and refresh likes number on the buttons
         */
        getResults() {

            const { url, nonceGetLike } = this.wpData;

            /** AJAX call */
            const xHttp = new XMLHttpRequest();
            const action = 'get_like';

            xHttp.open( 'POST', `${ url }?nonce=${ nonceGetLike }&action=${ action }`, true);
            xHttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded');

            xHttp.onload = () => {

                // Request failed
                if ( ! xHttp.status ) {

                    console.error( 'Get likes failed! See below:' );
                    console.error( data );

                } else if ( xHttp.status !== 200 ) { // Request error

                    console.error( 'Get likes error! Returned status of ' + xHttp.status );

                } else if ( xHttp.status === 200 ) { // Request success

                    const { status, liker } = JSON.parse( xHttp.response );

                    if ( status ) {

                        this.updateResults( liker );

                    }

                }

            };

            xHttp.send( encodeURI( `liker_id=${ this.id }` ) );

        }

        /**
         * Display results in buttons.
         *
         * @param liker.positive
         * @param liker.neutral
         * @param liker.negative
         *
         * @return {void}
         **/
        updateResults( liker ) {

            /** Nothing to update if no likes data */
            if ( liker[ 'positive' ] === 'undefined' ) { return; }

            let buttons = [];
            let currentValue = [];

            /** Store current val */
            currentValue[1] = liker.positive;
            currentValue[2] = liker.neutral;
            currentValue[3] = liker.negative;

            /** Update votes values */
            for ( let i = 1; i <= currentValue.length; i++ ) {

                if ( document.querySelector(`#mdp-liker-${ this.id } .mdp-liker-buttons span.val-${ i }` ) === null ) { continue; }

                /** Add new val */
                buttons[i] = document.querySelector(`#mdp-liker-${ this.id } .mdp-liker-buttons span.val-${ i }` );
                buttons[i].innerHTML = currentValue[ i ];

            }

            /** Display results if user already vote */
            if( "show" === this.wpData.results ) {
                this.displayResults( this.voted || ! this.fNew );
            }

            /** Update previous vote */
            this.previousValue = currentValue;

        }

        /**
         * Display results for already voted users.
         *
         * @param condition
         */
        displayResults( condition ) {

            /** Show result only if condition is true */
            if ( ! condition ) { return; }

            /** Display results */
            for (let i = 0; i < this.likerButtons.length; i++) {
                this.likerButtons[i].querySelector( 'span' ).classList.add( 'mdp-liker-result' );
            }

        }

        /**
         * Create AJAX call
         *
         * @param {object} data
         * @return
         **/
        request( data) {

            const { liker_id, val_1, val_2, val_3, voteId, guid, new_like } = data;
            const { url, nonceProcessLike } = this.wpData;

            let sessionDataLiker = window.sessionStorage.getItem( 'likerSession' );
            let session = sessionDataLiker === null ? 0 : 1;

            this.sessionPostID = liker_id;

            if ( sessionDataLiker !== null && parseInt(sessionDataLiker ) !== parseInt(liker_id) ) {
                this.sessionPostID = liker_id;
                session = 0;
            }

            /** Create session */
            window.sessionStorage.setItem( 'likerSession', this.sessionPostID );

            /** Disable buttons */
            this.buttonsDisable( true );

            /** AJAX call */
            const xHttp = new XMLHttpRequest();
            const action = 'process_like';

            xHttp.open( 'POST', `${ url }?nonce=${ nonceProcessLike }&action=${ action }`, true);
            xHttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded');

            xHttp.onload = () => {

                // Request failed
                if ( ! xHttp.status ) {

                    console.error( 'Request failed! See below:' );
                    console.error( data );

                }

                // Request error
                else if ( xHttp.status !== 200 ) {

                    console.error( 'Request error! Returned status of ' + xHttp.status );

                }

                // Request success
                else if ( xHttp.status === 200 ) {

                    const { liker, callback } = JSON.parse( xHttp.response );
                    this.voted = true;

                    if ( callback.status ) {

                        // Update voting
                        this.updateResults( liker );
                        this.buttonsDisable( false );
                        this.activateButton( document.querySelector( `#mdp-liker-${ this.id } #mdp-liker-btn-${ voteId }` ) );
                        this.updateLocalStorage();

                    } else {

                        // Limit exceed
                        this.showMessage( callback.message );
                        this.hideMessage();
                        this.buttonsDisable( false );

                    }

                    this.fNew = false;

                }

            };

            xHttp.send( encodeURI( `liker_id=${ liker_id }&val_1=${val_1}&val_2=${val_2}&val_3=${val_3}&guid=${guid}&new_like=${new_like}&session=${session}` ) );

        }

        /**
         * Disable/Enable Liker buttons
         * @param isDisable
         */
        buttonsDisable( isDisable ) {

            /** Disable buttons */
            for ( let i = 0; i < this.likerButtons.length; i++ ) {

                this.likerButtons[i].disabled = isDisable;

            }

        }

        /**
         * Activate current button
         * @param $currentButton
         */
        activateButton( $currentButton ) {

            this.deActivateButtons();

            /** Activate current button by mdp-active class. */
            if ( ! this.canceled ) {

                $currentButton.classList.add( 'mdp-active' );

            }

        }

        /**
         * Make all button inactive
         */
        deActivateButtons() {

            for ( let i = 0; i < this.likerButtons.length; i++ ) {

                this.likerButtons[i].classList.remove( 'mdp-active' );

            }

        }

        /**
         * Show message
         * @param message
         */
        showMessage( message ) {

            for ( let $parent of document.querySelectorAll( `#mdp-liker-${ this.id } .mdp-liker-buttons` ) ) { //TODO: REPLACE ID FROM THIS.CLASS

                const $msg = document.createElement( 'p' );
                $msg.classList.add( 'mdp-liker-msg' );
                $msg.innerHTML = message;

                $parent.appendChild( $msg );

            }

        }

        /**
         * Hide and remove message
         */
        hideMessage() {

            for ( let $msg of document.querySelectorAll( `#mdp-liker-${ this.id } .mdp-liker-msg` ) ) { //TODO: REPLACE ID FROM THIS.CLASS

                $msg.classList.add( 'mdp-liker-fade' );

                setTimeout( () => {
                    $msg.remove();
                }, 3001 );

            }

        }

    }

} () );