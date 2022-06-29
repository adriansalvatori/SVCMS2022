jQuery( function( $ ) {

    if ( typeof wc_checkout_params === 'undefined' || typeof aw_birthdays_params === 'undefined' ) {
        return false;
    }

    if ( wc_checkout_params.option_guest_checkout === 'yes' && aw_birthdays_params.is_logged_in !== '1' ) {
        $( document.body ).on( 'init_checkout', init_birthday_field() );
    }

    function init_birthday_field() {
        toggle_birthday_field_visibility();
        $( 'input#createaccount' ).on( 'change', function(){
            toggle_birthday_field_visibility()
        } );
    }

    function toggle_birthday_field_visibility() {
        var $birthday_section = $('.automatewoo-birthday-section');

        if ( $( 'input#createaccount' ).is( ':checked' ) ) {
            $birthday_section.slideDown();
        } else {
            $birthday_section.hide();
        }
    }

});
