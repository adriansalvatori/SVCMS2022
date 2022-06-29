(function( $, window ) {
    'use strict';

    const parent = window.parent;

    const vars = parent.XT_WOOFC;

    parent.$ = parent.jQuery;

    const checkout = {

        skipCartRefresh: false,
        skipCheckoutRefresh: false,
        orderComplete: false,
        placingOrder: false,

        html() {
            return $('html');
        },
        form() {
            return $('form.checkout');
        },
        formVisible() {
            return checkout.form().is(':visible');
        },
        submitBtn() {
            return $('#place_order');
        },
        isActive() {
            return checkout.html().is(':visible') && parent.xt_woofc_checkout_active();
        },
        init() {

            if((vars && location.href.search(vars.checkout_frame_query) === -1)) {
                parent.location.reload();
            }

            if(parent === window) {
                return;
            }

            checkout.initEvents();
            checkout.updateCartFooterVisibility();

            $(window).on('load', () => {
                checkout.syncCartShippingMethod();
                checkout.resize();
            });

            if(parent.xirkiPostMessage) {
                parent.xirkiPostMessage.windows.register(window);
            }
        },

        initEvents() {

            $(document.body).on('click', '.wc-backward', (evt) => {

                evt.preventDefault();

                checkout.hide();
            });

            $(document.body).on('click', 'a:not([href~="#"])', ( evt ) => {

                let target = $(evt.currentTarget);

                if(
                    target.hasClass('woocommerce-remove-coupon') ||
                    target.hasClass('woocommerce-privacy-policy-link') ||
                    target.hasClass('woocommerce-terms-and-conditions-link') ||
                    target.hasClass('wc-backward')
                ) {
                    return;
                }

                evt.preventDefault();
                evt.stopPropagation();

                let href = target.attr('href');

                checkout.redirect( href );
            });

            $(document.body).on('click', '.xt_woofc-shipping-edit', (evt) => {

                evt.preventDefault();

                let scrollToElement;

                if($('#ship-to-different-address-checkbox').is(':checked')) {
                    scrollToElement = $('.woocommerce-shipping-fields');
                }else{
                    scrollToElement = $('.woocommerce-billing-fields');
                }

                if(scrollToElement.length) {

                    checkout.scrollTo(scrollToElement.offset().top - 20);
                }
            });

            $(document.body).on('click', 'a[href~="#"]', () => {
                checkout.resize();
            });

            $( document.body ).ajaxStop(() => {
                checkout.resize();
                checkout.hideLoading();
            });

            $( document.body ).on( 'submit', 'form', () => {
                checkout.showLoading();
            });

            $( document.body ).on( 'init_checkout update_checkout', () => {
                checkout.showLoading();
            });

            $( document.body ).on( 'checkout_error updated_checkout', () => {

                checkout.hideLoading();

                // Whenever checkout updated, update the cart
                if(!checkout.skipCartRefresh) {
                    checkout.skipCheckoutRefresh = true;
                    checkout.refreshCart();
                }else{
                    checkout.skipCartRefresh = false;
                }
            });

            // On checkout error, scroll to first invalid field or top error
            $( document.body ).on( 'checkout_error', () => {

                checkout.placingOrder = false;
                checkout.hideLoading();

                setTimeout(() => {

                    let $invalid = $('.woocommerce-invalid').first();

                    if($invalid.length === 0) {
                        $invalid = $('.woocommerce-error').first();
                    }

                    if($invalid.length) {

                        checkout.scrollTo($invalid.offset().top);
                    }

                }, 400);

                checkout.triggerParentEvent('xt_woofc_checkout_error');
            });

            // Resize checkout on any change
            $( document.body ).on( 'click resize change init_checkout update_checkout checkout_error updated_checkout', () => {
                checkout.updateCartFooterVisibility();
                checkout.resize();
            });

            // On payment method select, always scroll to bottom
            $(document.body).on('change', '#payment input', () => {

                checkout.updateCartFooterVisibility();

                if(!checkout.skipCartRefresh) {
                    checkout.scrollToBottom();
                }
            });

            // On checkout success, show redirect message and redirect to order thank you page
            checkout.form().on('checkout_place_order_success', (evt, result) => {

                evt.preventDefault();
                evt.stopPropagation();
                evt.stopImmediatePropagation();

                $('.xt_woofc-checkout-wrap').hide().remove();

                parent.xt_woofc_hide_footer();
                checkout.resize();

                checkout.completed(result.redirect);

                return false;
            });

            window.addEventListener("message", (event) => {

                if(!event || typeof(event.data) !== 'string') {
                    return;
                }

                // Get the sent data
                const data = JSON.parse(event.data);

                switch(data.event) {

                    case 'xt_woofc_show_checkout':
                    case 'wc_fragments_refreshed':
                    case 'wc_fragments_loaded':
                    case 'xt_atc_added_to_cart':
                        checkout.updateCheckout();
                        break;

                    case 'xt_woofc_emptied':
                        setTimeout(() => {
                            checkout.unload();
                        }, 100);
                        break;

                    case 'xt_woofc_checkout_place_order':
                        checkout.placeOrder();
                        break;
                }

            }, false);
        },

        // Whenever cart updated, update the checkout form
        updateCheckout() {

            checkout.updateCartFooterVisibility();

            if(!checkout.skipCheckoutRefresh) {

                checkout.skipCartRefresh = true;
                $(document.body).trigger('update_checkout');

                checkout.syncCartShippingMethod();

            }else{
                checkout.skipCheckoutRefresh = false;
            }
        },

        placeOrder() {

            if(checkout.placingOrder) {
                return;
            }

            checkout.placingOrder = true;
            checkout.showLoading();
            checkout.form().submit();
        },

        hide() {

            parent.xt_woofc_hide_checkout();
        },

        unload() {

            if(checkout.placingOrder) {
                return;
            }

            parent.xt_woofc_unload_checkout();
        },

        resize() {

            if(checkout.resizeInterval) {
                clearInterval(checkout.resizeInterval);
            }

            let total = 0;
            checkout.resizeInterval = setInterval( () => {

                if(total >= 20) {
                    clearInterval(checkout.resizeInterval);
                    return;
                }

                parent.xt_woofc_resize_checkout( $( 'html' ).height() );
                total++;

            }, 80 );
        },

        isLoading() {
            parent.xt_woofc_is_loading();
        },

        showLoading() {
            if(checkout.orderComplete || !checkout.isActive() || !checkout.form().length) {
                return;
            }
            parent.xt_woofc_show_loading();
        },

        hideLoading() {
            if(checkout.orderComplete) {
                return;
            }
            parent.xt_woofc_hide_loading();
        },

        refreshCart() {

            parent.xt_woofc_refresh_cart();
        },

        scrollTo(top) {

            parent.xt_woofc_scroll_to(top);
        },

        scrollToBottom() {

            parent.xt_woofc_scroll_to_bottom();
        },

        syncCartShippingMethod() {

            // Sync shipping method from cart

            const p_shipping_methods = parent.$('#shipping_method');
            const shipping_methods = $('#shipping_method');

            let shipping_method;

            if(p_shipping_methods.is('select')) {
                shipping_method = p_shipping_methods.val();
                shipping_methods.val(shipping_method);
            }else{
                shipping_method = p_shipping_methods.find('input.shipping_method').filter(':checked').attr('id');
                shipping_methods.find('#'+shipping_method).trigger('click');
            }

        },

        redirect(redirect){

            parent.xt_woofc_checkout_redirect( redirect );
        },

        completed(orderUrl) {

            let url = new URL(orderUrl);
            url.searchParams.delete(vars.checkout_frame_query);
            orderUrl = url.href;

            checkout.orderComplete = true;
            checkout.placingOrder = false;

            const redirect = (vars.checkout_complete_action === 'redirect');

            parent.xt_woofc_checkout_completed( orderUrl, redirect );

        },

        isSubmitBtnVisible() {

            return checkout.submitBtn().is(':visible') && checkout.form().length && checkout.formVisible();
        },

        updateCartFooterVisibility() {

            setTimeout(() => {

                if(!checkout.isActive()) {
                    return;
                }

                if(checkout.isSubmitBtnVisible()) {
                    parent.xt_woofc_show_footer();
                }else{
                    parent.xt_woofc_hide_footer();
                }

            }, 1);
        },

        triggerParentEvent(...args) {

            parent.$('body').trigger(...args);
        }
    };

    $(function() {
        checkout.init();
    });

})( jQuery, window);

