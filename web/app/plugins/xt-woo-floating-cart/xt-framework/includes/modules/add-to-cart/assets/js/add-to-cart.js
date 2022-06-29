(function( $ ) {
    'use strict';

    window.XT_ATC = window.XT_ATC || {};

    $(function() {

        var supportStorage = ( 'sessionStorage' in window && window.sessionStorage !== null );
        var startButtonLoaderTimeout;
        var stopButtonLoaderTimeout;
        var singleAddedParams;

        // Shop Vars
        XT_ATC.ajaxAddToCart = !!XT_ATC.ajaxAddToCart;
        XT_ATC.ajaxSinglePageAddToCart = !!XT_ATC.ajaxSinglePageAddToCart;
        XT_ATC.isProductPage = !!XT_ATC.isProductPage;

        // Single Product Page Vars
        XT_ATC.singleRefreshFragments = !!XT_ATC.singleRefreshFragments;
        XT_ATC.singleScrollToNotice = !!XT_ATC.singleScrollToNotice;
        XT_ATC.singleScrollToNoticeTimeout = parseInt(XT_ATC.singleScrollToNoticeTimeout);

        // Spinner Override Vars
        XT_ATC.overrideSpinner = !!XT_ATC.overrideSpinner;

        // Spinner Redirection Vars
        XT_ATC.redirectionEnabled = !!XT_ATC.redirectionEnabled;

        function init() {

            fixCartForms();
            observeCartForms();

            $(document.body).on('should_send_ajax_request.adding_to_cart', shouldAddToCart);
            $(document.body).on('adding_to_cart', onAddingToCart);
            $(document.body).on('added_to_cart', onAddedToCart);
            $(document.body).on('click.xt_atc', '.single_add_to_cart_button', onSingleAddToCart);
            $(document.body).on('wc_fragments_refreshed', onSingleFragmentsRefreshed);

            moveEventToTheTop();
        }

        function fixCartForms() {

            $('form .add_to_cart_button').each(function () {
                if($(this).closest('.product-type-grouped').length === 0) {
                    $(this).removeClass('add_to_cart_button').addClass('single_add_to_cart_button');
                }
            });

            $('form.cart').each(function() {

                var trigger = $(this).find('[type="submit"]').not('.single_add_to_cart_button');
                if(trigger.length){
                    trigger.addClass('single_add_to_cart_button');
                }
            });
        }

        function observeCartForms() {

            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        // element added to DOM
                        var isCartForm = [].some.call(mutation.addedNodes, function(el) {
                            return $(el).find('form.cart').length;
                        });
                        if (isCartForm) {
                            fixCartForms();
                        }
                    }
                });
            });

            observer.observe(document.body, { attributes:true, childList:true, characterData:true });
        }

        function moveEventToTheTop() {

            var interval = setInterval(function() {

                if(!isEventBindedFirst(document.body, 'click', 'xt_atc')) {
                    eventBindFirst(document.body, 'click', 'xt_atc');
                }else{
                    clearInterval(interval);
                }

            }, 1000);
        }

        function isEventBindedFirst(element, type, namespace) {

            var events = $._data(element).events[type];
            if(events && events.length > 0) {
                return (events[0].namespace === namespace);
            }
            return false;
        }

        function eventBindFirst(element, type, namespace) {

            var events = $._data(element).events[type];

            if(events && events.length > 0) {
                events.find(function(event, index) {
                    if(namespace === event.namespace) {
                        events.unshift(events.splice(index, 1)[0]);
                    }
                });
            }
        }

        function shouldAddToCart(evt, trigger) {

            return !isButtonLoading(trigger);
        }

        function onAddingToCart(evt, trigger) {

            if(trigger) {
                startButtonLoader(trigger);
            }

            $(document.body).trigger('xt_atc_adding_to_cart', [trigger]);
        }

        function onAddedToCart(evt, fragments, cart_hash, trigger) {

            if(trigger) {
                stopButtonLoader(trigger);
            }

            if (!cartHasErrors(fragments)) {
                $(document.body).trigger('xt_atc_added_to_cart', [{fragments: fragments}, trigger, cart_hash]);
            }
        }

        function onSingleAddToCart(evt) {

            var trigger = $(evt.currentTarget);
            var form = trigger.closest('form');

            if (!skipSingleAddToCart(trigger)) {

                evt.preventDefault();
                //evt.stopPropagation(); // had to be removed to make sure analytics plugins work
                evt.stopImmediatePropagation(); // Might still cause issues

                if(!isButtonLoading(trigger) && !isButtonDisabled(trigger) && validSingleAddToCart(form, trigger)) {
                    singleAddToCart(form, trigger);
                }
            }
        }

        function cartHasErrors(fragments) {

            if (fragments && fragments.hasOwnProperty('.woocommerce-notices-wrapper') && $(fragments['.woocommerce-notices-wrapper']).length) {

                return $(fragments['.woocommerce-notices-wrapper']).find('.woocommerce-error').length > 0;
            }
            return false;
        }

        function validSingleAddToCart(form, trigger) {

            // validate required options from multiple plugins
            var errors = 0;

            // Check if has quantity
            var $qty_input = form.find('.quantity input.qty:visible');

            if ($qty_input.length) {

                $qty_input.each(function() {

                    $(this).removeClass('xt_atc-error');

                    var value = $(this).val() !== '' ? parseInt($(this).val()) : 0;
                    var has_min = $(this).get(0).hasAttribute("min");
                    var min = has_min ? parseInt($(this).attr('min')) : 1;

                    if (value < min || value <= 0) {

                        $(this).addClass('xt_atc-error');
                        errors++;
                    }
                });
            }

            // https://woocommerce.com/products/product-add-ons/
            var $elements = form.find('.wc-pao-required-addon, .required-product-addon');

            // https://codecanyon.net/item/woocommerce-extra-product-options/7908619
            $elements = $.merge(
                $elements,
                form.find('.tm-has-required + div.tm-extra-product-options-container').not('.tc-hidden div.tm-extra-product-options-container')
            );

            // https://wordpress.org/plugins/woocommerce-product-addon/
            $elements = $.merge(
                $elements,
                form.find('.ppom-field-wrapper .show_required').closest('.form-group')
            );

            // https://woocommerce.com/products/gravity-forms-add-ons/
            $elements = $.merge(
                $elements,
                form.find('.gfield_contains_required')
            );

            $elements.each(function () {

                var $row = $(this);

                if ($row.is(':visible')) {
                    var $input = $row.find(':input');

                    if ($input.attr('type') === 'checkbox' || $input.attr('type') === 'radio') {
                        $row.removeClass('xt_atc-error');
                        if (!$input.is(':checked')) {
                            errors++;
                            $row.addClass('xt_atc-error');
                        }
                    } else {
                        $row.removeClass('xt_atc-error');
                        if ($input.val() === '') {
                            errors++;
                            $row.addClass('xt_atc-error');
                        }
                    }
                } else {
                    $row.removeClass('xt_atc-error');
                }
            });

            if (errors > 0) {
                var $firstError = form.find('.xt_atc-error').first();
                var $scrollElm = maybeInQuickView(trigger) ? form : $('html,body');

                if ($firstError.length) {
                    $scrollElm.animate({scrollTop: $firstError.offset().top - 100}, 500);
                }
            }

            return (errors === 0);
        }

        function skipSingleAddToCart(trigger) {

            if(
                trigger.closest('.wc-product-table').length ||
                trigger.closest('.product').hasClass('product-type-external') ||
                hasClassRegEx(trigger, [
                    /buy-now/,
                    /buy_now/,
                    /buynow/
                ])
            ) {
                return true;
            }

            if(!XT_ATC.ajaxSinglePageAddToCart && isSingleProductPage(trigger)) {
                return true;
            }

            return false;
        }

        function hasClassRegEx(el, regex) {

            var classes = $(el).attr('class');

            if(!classes || !regex){ return false; }

            classes = classes.split(' ');

            for(var i=0; i < classes.length; i++) {

                if(typeof(regex) === 'object' && regex.hasOwnProperty('length')) {

                    for(var r=0; r < regex.length; r++) {

                        if (classes[i].match(regex[r]) !== null) {
                            return true;
                        }
                    }

                }else {

                    if (classes[i].match(regex) !== null) {
                        return true;
                    }
                }
            }

            return false;
        }

        function isButtonLoading(trigger) {

            return !!trigger.data('loading');
        }

        function isButtonDisabled(trigger) {

            return trigger.hasClass('disabled');
        }

        function startButtonLoader(trigger) {

            trigger.data('loading', true);
            trigger.removeClass('added loading');

            if (XT_ATC.overrideSpinner) {

                if (startButtonLoaderTimeout) {
                    clearTimeout(startButtonLoaderTimeout);
                }

                var trigger_html = trigger.html();

                var computedStyles = window.getComputedStyle(trigger.get(0));

                var trigger_bgcolor = computedStyles.backgroundColor;
                var trigger_color = computedStyles.color;
                var trigger_width = computedStyles.width;
                var trigger_height = computedStyles.height;

                trigger.data('html', trigger_html);
                trigger.addClass('xt_atc-loading');

                trigger.css({
                    backgroundColor: trigger_bgcolor,
                    color: trigger_color,
                    width: trigger_width,
                    height: trigger_height
                });

                var $spinnerWrap = trigger.find('.xt_atc-button-spinner-wrap');
                var $spinner;

                if ($spinnerWrap.length === 0) {
                    $spinnerWrap = $('<span class="xt_atc-button-spinner-wrap"></span>');
                    $spinner = $('<span class="xt_atc-button-spinner ' + XT_ATC.spinnerIcon + '"></span>');
                    $spinnerWrap.html($spinner);
                    trigger.html($spinnerWrap);
                } else {
                    $spinner = $spinnerWrap.find('.xt_atc-button-spinner');
                    $spinner.removeClass(XT_ATC.checkmarkIcon).addClass(XT_ATC.spinnerIcon);
                }

                startButtonLoaderTimeout = setTimeout(function () {
                    $spinnerWrap.addClass('xt_atc-button-spinner-ready');
                }, 5);

            }else{

                trigger.addClass('loading');
            }
        }

        function stopButtonLoader(trigger) {

            if (XT_ATC.overrideSpinner) {

                if (stopButtonLoaderTimeout) {
                    clearTimeout(stopButtonLoaderTimeout);
                }

                var $spinnerWrap = trigger.find('.xt_atc-button-spinner-wrap');

                // remove spinner
                if ($spinnerWrap.length) {

                    $spinnerWrap.removeClass('xt_atc-button-spinner-ready');

                    var $spinner = $spinnerWrap.find('.xt_atc-button-spinner');

                    var resetStyles = {
                        backgroundColor: '',
                        color: '',
                        width: '',
                        height: ''
                    };

                    stopButtonLoaderTimeout = setTimeout(function () {

                        // add checkmark
                        $spinner.removeClass(XT_ATC.spinnerIcon).addClass(XT_ATC.checkmarkIcon);
                        $spinnerWrap.addClass('xt_atc-button-spinner-ready');

                        setTimeout(function () {

                            trigger.html(trigger.data('html'));

                            trigger.removeClass('xt_atc-loading').addClass('added');
                            trigger.removeData('loading');

                            trigger.css(resetStyles);

                        }, 2000);

                    }, 300);
                }

            } else {

                trigger.removeClass('loading').addClass('added');
                trigger.removeData('loading');
            }
        }

        function getFormData(form, trigger) {

            var formData = new FormData(form.get(0));

            // Get data from all buttons that contains a name and a value
            form.find('button').each(function() {

                var key = $(this).attr('name');
                var value = $(this).val();

                if(key && value && !formData.has(key)) {

                    formData.append(key, value);
                }
            });

            // Fetch changes that are directly added by calling trigger.data( key, value )
            $.each( trigger.data(), function( key, value ) {

                if(!formData.has(key)) {
                    formData.append(key, value);
                }
            });

            // Fetch data attributes in trigger. Give preference to data-attributes because they can be directly modified by javascript
            // while `.data` are jquery specific memory stores.
            $.each( trigger[0].dataset, function( key, value ) {

                if(!formData.has(key)) {
                    formData.append(key, value);
                }
            });

            //If no add-to-cart / product_id found, look for the form action URL
            if( !formData.has('add-to-cart') && !formData.has('product_id')){
                var is_url = form.attr('action').match(/add-to-cart=([0-9]+)/);
                var productID = is_url ? is_url[1] : false;

                if( productID ){
                    formData.append('add-to-cart', productID);
                }
            }

            //If no add-to-cart found but product_id is found, use it.
            if( !formData.has('add-to-cart') && formData.has('product_id')){

                formData.append('add-to-cart', formData.get('product_id'));
            }

            return formData;
        }

        function singleAddToCart(form, trigger) {

            trigger.removeClass('added');

            var data = getFormData(form, trigger);

            // Allow 3rd parties to validate and quit early.
            if ( false === $( document.body ).triggerHandler( 'should_send_ajax_request.adding_to_cart', [ trigger ] ) ) {
                $( document.body ).trigger( 'ajax_request_not_sent.adding_to_cart', [ false, false, trigger ] );
                return true;
            }

            // Trigger event.
            $(document.body).trigger( 'adding_to_cart', [ trigger, data ] );

            // Fix issue in customizer preview. Restore original form data
            $.ajaxPrefilter(function( options, originalOptions ) {
                if(options.url.search('xt_atc_single') !== -1) {
                    options.data = originalOptions.data;
                }
            });

            $.XT_Ajax_Queue({
                url: XT_ATC.ajaxUrl.toString().replace('%%endpoint%%', 'xt_atc_single'),
                enctype: 'multipart/form-data',
                type: 'post',
                data: data,
                processData: false,  // Important!
                contentType: false,
                cache: false,
                timeout: 600000
            }).done(function(data) {

                // Redirect to cart option
                if (XT_ATC.redirectionEnabled) {
                    window.location = XT_ATC.redirectionTo;
                    return;
                }

                if (!isSingleProductPage(trigger) && !maybeInQuickView(trigger) && !wc_add_to_cart_params.is_cart && trigger.parent().find('.added_to_cart').length === 0) {
                    trigger.after('<a href="' + wc_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' + wc_add_to_cart_params.i18n_view_cart + '">' + wc_add_to_cart_params.i18n_view_cart + '</a>');
                }

                if (!isSingleProductPage(trigger) && data.fragments && data.fragments.hasOwnProperty('.woocommerce-notices-wrapper')) {
                    delete data.fragments['.woocommerce-notices-wrapper'];
                }

                if(XT_ATC.singleRefreshFragments) {

                    singleAddedParams = {
                        'fragments': data.fragments,
                        'cart_hash': data.cart_hash,
                        'trigger': trigger
                    };

                    $(document.body).trigger('wc_fragment_refresh');

                }else{

                    triggerSingleAddedToCart(data.fragments, data.cart_hash, trigger);
                }



            }).fail(function() {

                stopButtonLoader(trigger);
            });
        }

        function triggerSingleAddedToCart(fragments, cart_hash, trigger) {

            singleAddedParams = null;

            // Trigger event so themes can refresh other areas.
            $(document.body).trigger('added_to_cart', [fragments, cart_hash, trigger]);

            if(supportStorage) {
                sessionStorage.removeItem('wc_cart_created');
            }

            maybeScrollToNotice(trigger);
        }

        function onSingleFragmentsRefreshed() {

            if(singleAddedParams) {
                triggerSingleAddedToCart(singleAddedParams.fragments, singleAddedParams.cart_hash, singleAddedParams.trigger);
            }
        }

        function maybeInQuickView(trigger) {

            var maybeQuickViewContainer = trigger.closest('.single-product');
            return maybeQuickViewContainer.length > 0 && (maybeQuickViewContainer.prop('tagName') !== 'BODY');
        }

        function maybeInProductList(trigger) {

            var maybeProductListContainer = trigger.closest('.products');
            return maybeProductListContainer.length > 0;
        }

        function isSingleProductPage(trigger) {

            return (maybeInQuickView(trigger) || maybeInProductList(trigger)) ? false : XT_ATC.isProductPage;
        }

        function maybeScrollToNotice(trigger) {

            var wooNotices = $('.woocommerce-notices-wrapper')

            if(isSingleProductPage(trigger) && wooNotices.length && XT_ATC.singleScrollToNotice) {

                // If cart has errors, scroll to error
                setTimeout(function () {

                    var scrollTop = $(window).scrollTop();
                    var offset = wooNotices.offset().top - 100;

                    if(scrollTop >= offset) {
                        $('html,body').animate({scrollTop: offset}, 500);
                    }

                }, (XT_ATC.singleScrollToNoticeTimeout + 500));
            }
        }

        init();
    });

})( jQuery, window );