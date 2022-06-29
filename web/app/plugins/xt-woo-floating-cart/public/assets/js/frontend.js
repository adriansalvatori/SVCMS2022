(function( $, window ) {
	'use strict';

	window.XT_WOOFC = window.XT_WOOFC || {};

	$.fn.mutated = function(cb, e) {
		e = e || { subtree:true, childList:true, characterData:true };
		$(this).each(function() {
			function callback(changes) { cb.call(node, changes, this); }
			let node = this;
			(new MutationObserver(callback)).observe(node, e);
		});
	};

	//jQuery Cache
	const $$ = (() => {
		let cache = {};
		return ((selector) => {
			if(selector === 'flush') {
				cache = {};
				return true;
			}
			return cache[selector] || ( cache[selector] = $(selector) );
		});
	})();

	const goBackSvg = '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24" xml:space="preserve" style="display: inline-block;transform: rotate(180deg);margin-right: 8px;height: 40px;vertical-align: top;width: 20px;"><line fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="3" y1="12" x2="21" y2="12"></line><polyline fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" points="15,6 21,12 15,18 "></polyline></svg>';

	const lang = (key) => {
		return XT_WOOFC.lang[key] ? XT_WOOFC.lang[key] : null;
	};

	const option = (key) => {
		return XT_WOOFC[key] ? XT_WOOFC[key] : null;
	};

	const optionIs = (key, value) => {
		return (option(key) === value);
	};

	const optionEnabled = (key) => {
		return (option(key) === "1");
	};

	const cart = {

		el: {
			singleAddToCartBtn: 'form .single_add_to_cart_button, .letiations .single_add_to_cart_button',
			wooPageNotices: '.woocommerce-notices-wrapper',
			wooNotices: '.xt_woofc-wc-notices',
			notice: '.xt_woofc-notice',
			container: '.xt_woofc',
			inner: '.xt_woofc-inner',
			wrapper: '.xt_woofc-wrapper',
			header: '.xt_woofc-header',
			title: '.xt_woofc-title',
			body: '.xt_woofc-body',
			bodyHeader: '.xt_woofc-body-header',
			bodyFooter: '.xt_woofc-body-footer',
			listWrap: '.xt_woofc-list-wrap',
			list: 'ul.xt_woofc-list',
			trigger: '.xt_woofc-trigger',
			counter: '.xt_woofc-count',
			checkoutButton: '.xt_woofc-checkout',
			shippingBarPerc: '.xt_woofc-shipping-bar-perc',
		},
		cartNoticeTimeoutId: null,
		undoTimeoutId: null,
		lastRemovedKey: null,
		suggestedProductsSlider: null,
		winWidth: null,
		cartWidth: null,
		isLoading: false,
		isActive: false,
		isEmpty: true,
		isTransitioning: false,
		isReady: false,
		couponsEnabled: false,
		couponsListEnabled: false,
		modalMode: false,
		ajaxInit: false,
		animationType: null,
		triggerEvent: null,
		hoverdelay: null,
		viewMode: 'desktop',
		triggers: [],

		init() {

			// wc_cart_fragments_params is required to continue, ensure the object exists
			if ( typeof wc_cart_fragments_params === 'undefined') {
				return false;
			}

			cart.initVars();
			cart.updateDynamicVars();
			cart.setTriggers();
			cart.removeUnwantedAjaxRequests();
			cart.initEvents();
			cart.resize();
			cart.setTriggerDefaultText();
			cart.refreshCartCountSize();
			cart.removeUnwantedElements();
			cart.refreshCartVisibility();
			cart.initMutationObserver();

			/* <fs_premium_only> */
			if(optionEnabled('premium') && optionEnabled('cart_autoheight_enabled')) {
				cart.initScrollObserver();
			}
			/* </fs_premium_only> */

			if(cart.ajaxInit) {

				cart.refreshCart(() => {

					cart.cartReady();
				});

			}else{

				cart.cartReady();
			}
		},

		initVars() {

			cart.modalMode = $$(cart.el.container).hasClass('xt_woofc-modal');
			cart.ajaxInit = $$(cart.el.container).attr('data-ajax-init') === '1' || optionEnabled('is_customize_preview');
			cart.triggers = [cart.el.trigger];
		},

		updateDynamicVars() {

			cart.couponsEnabled = optionEnabled('enable_coupon_form');
			cart.couponsListEnabled = optionEnabled('enable_coupon_list');
			cart.animationType = $$(cart.el.container).attr('data-animation');
			cart.triggerEvent = $$(cart.el.container).attr('data-trigger-event');
			cart.hoverdelay = $$(cart.el.container).attr('data-hoverdelay') ? $$(cart.el.container).attr('data-hoverdelay') : 0;
		},

		flushCache() {

			$$('flush');

			cart.updateDynamicVars();
		},

		setTriggers() {

			let triggers = option('trigger_selectors');

			if(triggers && triggers.length) {
				triggers.forEach((item) => {

					if(item.selector !== '') {
						cart.triggers.push(item.selector);
					}
				});
			}
		},

		removeUnwantedAjaxRequests() {

			// Remove unwanted ajax request (cart form submit) coming from native cart script.
			if(optionEnabled('enable_totals')) {

				$.ajaxPrefilter((options, originalOptions, jqXHR) => {
					if (originalOptions.url === '#woocommerce-cart-form') {
						jqXHR.abort();
					}
				});
			}

		},

		isSetDemoAction() {

			let params = new URLSearchParams(window.location.search);
			return params.get('action') === 'xt_set_demo';
		},

		initEvents() {

			if (cart.isSetDemoAction() && sessionStorage.getItem('wc_cart_created') !== null) {
				sessionStorage.removeItem('wc_cart_created');
				location.reload();
				return;
			}

			$( window ).on( "beforeunload", () => {

				if (cart.isSetDemoAction()) {
					sessionStorage.removeItem('wc_cart_created');
				}
			});

			// Needed for Cart Menu Item & Shortcode on all pages
			$(window).on('resize', () => {

				window.requestAnimationFrame(cart.resize);
			});

			if(!$$(cart.el.container).length) {
				return;
			}

			// Make sure to burst the cache and refresh the cart after a browser back button event
			$(window).on('pageshow', () => {

				if(!cart.isReady) {
					cart.refreshCart(() => {

						cart.cartReady();
					});
				}

			});

			$(document.body).on('xt_atc_adding_to_cart', () => {

				$$(cart.el.container).removeClass('xt_woofc-empty');
				cart.maybeShowNotice();
			});

			$(document.body).on('vclick', cart.el.notice, (evt) => {

				if($(evt.currentTarget).find('a').length === 0) {
					cart.hideNotice();
				}
			});

			// Update Cart List Obj
			$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', () => {

				cart.onFragmentsRefreshed();
			});

			//open/close cart

			cart.triggers.forEach((trigger, i) => {

				const selector = i === 0 ? 'vclick' : 'click';

				$(document.body).on(selector, trigger, (evt) => {
					if($(cart.el.container).is(':visible')) {
						evt.preventDefault();
						evt.stopPropagation();
						cart.toggleCart();
					}
				});

				if($(trigger).hasClass('xt_woofc-trigger') && cart.triggerEvent === 'mouseenter' && !XT.isTouchDevice()) {

					let mouseEnterTimer;
					$(trigger).on('mouseenter', (evt) => {

						mouseEnterTimer = setTimeout(() => {

							if(!cart.isActive) {
								evt.preventDefault();
								cart.toggleCart();
							}

						}, cart.hoverdelay);

					}).on('mouseleave', () => {

						clearTimeout(mouseEnterTimer);
					});

				}
			});

			//close cart when clicking on the .xt_woofc::before (bg layer)
			$$(cart.el.container).on('vclick', (evt) => {
				if( evt.target === evt.currentTarget ) {
					cart.toggleCart(false);
				}
			});

			//delete an item from the cart
			$$(cart.el.body).on('vclick', '.xt_woofc-delete-item', (evt) => {
				evt.preventDefault();

				// Simulate native cart remove to keep analytics / tracking plugins working as they should
				let $quantityInput = $(evt.currentTarget).closest('li').find('.xt_woofc-quantity-row input');
				let quantity = $quantityInput.length ? $quantityInput.val() : 0;

				let $clone = $(evt.currentTarget).clone().addClass('remove').removeClass('xt_woofc-delete-item').hide();
				$(evt.currentTarget).parent().append($clone);

				let $wrap = $clone.wrap('<span class="mini_cart_item"></span>').parent();
				$wrap.hide().append('<span class="quantity">'+quantity+'></span>');

				$clone.trigger('click');

				let key = $(evt.target).parents('.xt_woofc-product').data('key');
				cart.removeProduct(key);
			});

			//update item quantity

			$( document ).on('keyup', '.xt_woofc-quantity-row input', (evt) => {

				let $target = $(evt.currentTarget);
				cart.updateQuantityInputWidth($target);
			});

			$( document ).on('change', '.xt_woofc-quantity-row input', (evt) => {

				evt.preventDefault();

				let $target = $(evt.currentTarget);

				let $parent = $target.parent();
				let min = parseFloat( $target.attr( 'min' ) );
				let max	= parseFloat( $target.attr( 'max' ) );

				if ( min && min > 0 && parseFloat( $target.val() ) < min ) {

					$target.val( min );
					cart.setNotice(lang('min_qty_required'), 'error', $parent);
					return;

				}else if ( max && max > 0 && parseFloat( $target.val() ) > max ) {

					$target.val( max );
					cart.setNotice(lang('max_stock_reached'), 'error', $parent);
					return;

				}

				let product = $target.closest('.xt_woofc-product');
				let qty = $target.val();
				let key = product.data('key');

				cart.updateQuantityInputWidth($target);
				cart.updateProduct(key, qty);
			});

			let quantityChangeTimeout;
			$( document ).on( 'vclick', '.xt_woofc-quantity-col-minus, .xt_woofc-quantity-col-plus', (evt) => {

				evt.preventDefault();

				if(quantityChangeTimeout) {
					clearTimeout(quantityChangeTimeout);
				}

				let $target = $(evt.currentTarget);

				// Get values

				let $parent 	= $target.closest( '.xt_woofc-quantity-row' ),
					$qty_input	= $parent.find( 'input' ),
					currentVal	= parseFloat( $qty_input.val() ),
					max			= parseFloat( $qty_input.attr( 'max' ) ),
					min			= parseFloat( $qty_input.attr( 'min' ) ),
					step		= $qty_input.attr( 'step' ),
					newQty		= currentVal;

				// Format values
				if ( ! currentVal || isNaN(currentVal)) {
					currentVal = 0;
				}
				if (isNaN(max)) {
					max = 0;
				}
				if (isNaN(min)) {
					min = 0;
				}
				if ( step === 'any' || step === '' || step === undefined || isNaN(step) ) {
					step = 1;
				}

				// Change the value
				if ( $target.is( '.xt_woofc-quantity-col-plus' ) ) {

					if ( max && ( max === currentVal || currentVal > max ) ) {
						cart.setNotice(lang('max_stock_reached'), 'error', $parent);
						return;
					} else {
						newQty = ( currentVal + parseFloat( step ) );
					}

				} else {

					if ( min && ( min === currentVal || currentVal < min ) ) {
						cart.setNotice(lang('min_qty_required'), 'error', $parent);
						return;
					} else if ( currentVal > 0 ) {
						newQty = ( currentVal - parseFloat( step ) );
					}

				}

				// Trigger change event

				let product = $qty_input.closest('.xt_woofc-product');
				let key = product.data('key');

				if(currentVal !== newQty) {

					$qty_input.val(newQty);

					// throttle update
					quantityChangeTimeout = setTimeout(() => {

						// Update product quantity
						cart.updateProduct(key, newQty);

					}, 500);
				}
			});


			//reinsert item deleted from the cart
			$(document.body).on('vclick', '.xt_woofc-undo', (evt) => {

				if(cart.undoTimeoutId) {
					clearInterval(cart.undoTimeoutId);
				}
				evt.preventDefault();

				cart.hideNotice(true);
				cart.showLoading(true);

				let timeout = 0;
				let key = cart.lastRemovedKey;

				let product = $$(cart.el.list).find('.xt_woofc-deleted');
				let lastProduct = product.last();

				const onAnimationEnd = (el) => {

					el.removeClass('xt_woofc-deleted xt_woofc-undo-deleted').removeAttr('style');
					cart.refreshCartVisibility();
				};

				const onLastAnimationEnd = () => {

					cart.autoHeight();

					cart.undoProductRemove(key, () => {
						$( document.body ).trigger( 'xt_woofc_undo_product_remove', [ key ] );
					});
				};

				$$(cart.el.container).removeClass('xt_woofc-empty');

				cart.animationEnd(lastProduct, true, onLastAnimationEnd);

				product.each(function() {

					let $this = $(this);

					cart.animationEnd($this, true, onAnimationEnd);

					setTimeout(() => {

						$this.addClass('xt_woofc-undo-deleted');

					}, timeout);

					timeout = timeout + 270;

				});

				$$(cart.el.list).find('.xt_woofc-deleting-last').removeClass('xt_woofc-deleting-last');

			});

			$(document).on('wc_update_cart', () => {

				cart.refreshCart();
			});

			$(document.body).on('xt_woofc_after_hide_footer', () => {
				cart.checkoutButtonIdle();
			});

			$(document.body).on('xtfw_customizer_xt_woofc_changed', (e, setting_id, setting_value) => {

				if(XT_WOOFC.hasOwnProperty(setting_id)) {
					XT_WOOFC[setting_id] = setting_value;
				}

				cart.onFragmentsRefreshed();
			});

			$(document.body).on('xtfw_customizer_saved', () => {
				cart.refreshCart();
			});

			$(document).on('vclick', cart.el.checkoutButton, (evt) => {

				if(cart.isLoading) {
					evt.preventDefault();
					evt.stopPropagation();
					return;
				}

				/* <fs_premium_only> */
				if(optionEnabled('premium') && optionEnabled('cart_checkout_form')) {

					evt.preventDefault();
					evt.stopPropagation();

					if(!cart_checkout.isFrameActive()) {

						if(cart.hasErrors()) {
							cart.scrollToTop();
							cart.shakeElement(cart.getFirstError());
						}else {
							cart.checkoutButtonProcessing();
							cart.updateCartButtonLabel('wait');
							cart_checkout.show();
						}

					}else{
						cart.showLoading();
						$(document.body).trigger('xt_woofc_checkout_place_order');
					}

					return;
				}
				/* </fs_premium_only> */

				cart.checkoutButtonProcessing();
				cart.updateCartButtonLabel('wait');

			});

			/* <fs_premium_only> */
			if(optionEnabled('premium')) {

				$(document.body).on('xt_atc_added_to_cart', (evt, data, trigger) => {

					cart.onRequestDone(data, 'add', () => {
						cart.onAddedToCart(trigger);
					});
				});

				// clear cart confirm when clicking on the header clear icon
				$$(cart.el.header).on('vclick', '.xt_woofc-header-clear', (evt) => {
					if (evt.target === evt.currentTarget) {
						cart.clearCartConfirm();
					}
				});

				// clear cart when clicking on the header clear confirm link
				$$(cart.el.header).on('vclick', '.xt_woofc-header-clear-confirm', (evt) => {
					if (evt.target === evt.currentTarget) {
						evt.preventDefault();
						cart.clearCart();
					}
				});

				// cancel clear cart when clicking on the header clear cancel link
				$$(cart.el.header).on('vclick', '.xt_woofc-header-clear-cancel', (evt) => {
					if (evt.target === evt.currentTarget) {
						evt.preventDefault();
						cart.hideNotice();
					}
				});

				// close cart when clicking on the header close icon
				$$(cart.el.header).on('vclick', '.xt_woofc-undo-clear', (evt) => {
					if (evt.target === evt.currentTarget) {
						evt.preventDefault();
						cart.clearCartRestore();
					}
				});

				// close cart when clicking on the header close icon
				$$(cart.el.header).on('vclick', '.xt_woofc-header-close', (evt) => {
					if (evt.target === evt.currentTarget) {
						cart.toggleCart(false);
					}
				});

				$( document.body ).on('updated_cart_totals', () => {

					const calcForm = $('form.woocommerce-shipping-calculator');

					if(calcForm.length) {
						calcForm.slideUp();
					}
				});

				if(optionEnabled('enable_totals')) {

					$(document).on('select2:open', '.xt_woofc-body .woocommerce-shipping-calculator #calc_shipping_country', (evt) => {

						let $form = $(evt.target).closest('form');

						$form.find('input:text, textarea').val('');
						$form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
					});

					$(document).on('select2:open', '.xt_woofc-body .select2-hidden-accessible', ()  => {

						$$(cart.el.body).css('overflow', 'hidden');
					});

					$(document).on('select2:select', '.xt_woofc-body .select2-hidden-accessible', (evt) => {

						$$(cart.el.body).css('overflow', '');
						$$(cart.el.body).off('scroll.select2-' + evt.target.id);
					});

					$(document).on('select2:close', '.xt_woofc-body .select2-hidden-accessible', (evt) => {

						$$(cart.el.body).css('overflow', '');
						$$(cart.el.body).off('scroll.select2-' + evt.target.id);
					});
				}

			}
			/* </fs_premium_only> */

		},

		showLoading(hideContent = false) {

			if(cart.isLoading) {
				return;
			}

			cart.isLoading = true;
			$$('html').removeClass('xt_woofc-stoploading').addClass('xt_woofc-loading');

			if(hideContent) {
				$$('html').addClass('xt_woofc-loading-hide-content');
			}
		},

		hideLoading() {

			if(!cart.isLoading) {
				return;
			}

			let loadingTimout = $$(cart.el.container).attr('data-loadingtimeout') ? parseInt($$(cart.el.container).attr('data-loadingtimeout')) : 0;

			setTimeout(() => {

				$$('html').removeClass('xt_woofc-loading-hide-content');
				$$('html').addClass('xt_woofc-stoploading');

				setTimeout(() => {
					$$('html').removeClass('xt_woofc-stoploading xt_woofc-loading');
					cart.isLoading = false;
				}, 310);

			}, cart.isActive ? loadingTimout + 100 : 0);
		},

		enableBodyScroll($el) {

			bodyScrollLock.enableBodyScroll($el.get(0));
		},

		disableBodyScroll($el) {

			bodyScrollLock.disableBodyScroll($el.get(0));
		},

		digitsCount(n) {

			let count = 0;
			if (n >= 1) ++count;

			while (n / 10 >= 1) {
				n /= 10;
				++count;
			}
			return count;
		},

		updateQuantityInputWidth(input) {

			let qty = $(input).val();
			let digits = cart.digitsCount(qty);
			let width = 25 * (digits / 2) + 'px';
			if(digits < 2) {
				width = 25;
			}
			$( input ).css('width', width);
		},

		autoHeight() {

			/* <fs_premium_only> */
			if(cart_checkout.isFrameLoading()) {
				return
			}

			if(optionEnabled('premium') && optionEnabled('cart_autoheight_enabled')) {

				if (cart.isActive && !cart.isTransitioning) {

					let listHeight = 0;

					$$(cart.el.list).children().each(function () {
						if (!$(this).hasClass('xt_woofc-deleted')) {
							listHeight += $(this).outerHeight();
						}
					});

					$$(cart.el.list).css({'min-height': listHeight + 'px'});

					let autoHeight = 0;

					autoHeight += $$(cart.el.header).outerHeight(true);
					autoHeight += $$(cart.el.bodyHeader).outerHeight(true);
					$$(cart.el.listWrap).children().each(function () {
						autoHeight += $(this).outerHeight(true);
					});
					autoHeight += $$(cart.el.bodyFooter).outerHeight(true);
					autoHeight += $$(cart.el.checkoutButton).outerHeight(true);
					autoHeight += cart_payments.getButtonsHeight();
					autoHeight += cart_checkout.getFrameHeight();

					xt_gsap.to($$(cart.el.inner), {'height': autoHeight + 'px', duration: 0.3});

				} else {

					$$(cart.el.list).css('min-height', '');
				}

				return;
			}
			/* </fs_premium_only> */

			$$(cart.el.inner).css('height', '');

		},

		/* <fs_premium_only> */
		onAddedToCart(trigger) {

			let single = trigger ? trigger.hasClass('single_add_to_cart_button') : false;
			let single_letiation = trigger ? trigger.closest('.letiations').length > 0 : false;

			if(optionEnabled('premium')) {

				if ($$(cart.el.container).attr('data-flytocart') === '1' && !cart.isActive) {

					cart.animateAddToCart(trigger, single);

				} else if (!single_letiation) {

					cart.animateCartShake();
				}
			}
		},
		/* </fs_premium_only> */

		resize() {

			let layouts = option('layouts');

			cart.winWidth = $(window).width();

			if(cart.winWidth <= layouts.S) {

				cart.viewMode = 'mobile';

				$('body').removeClass('xt_woofc-is-desktop xt_woofc-is-tablet').addClass('xt_woofc-is-mobile');

			}else if(cart.winWidth <= layouts.M) {

				cart.viewMode = 'tablet';

				$('body').removeClass('xt_woofc-is-desktop xt_woofc-is-mobile').addClass('xt_woofc-is-tablet');

			}else{

				cart.viewMode = 'desktop';

				$('body').removeClass('xt_woofc-is-mobile xt_woofc-is-tablet').addClass('xt_woofc-is-desktop');

			}

			if($$(cart.el.container).length) {

				cart.cartWidth = $$(cart.el.inner).width();

				if (cart.cartWidth <= layouts.XS) {

					$$(cart.el.container).addClass('xt_woofc-narrow-cart xt-framework-notice-narrow');

				} else {

					$$(cart.el.container).removeClass('xt_woofc-narrow-cart xt-framework-notice-narrow');
				}
			}

			$(document.body).trigger('xt_woofc_resize', [cart.viewMode]);

			/* <fs_premium_only> */
			if(optionEnabled('premium')) {

				if(optionEnabled('sp_slider_enabled')) {
					cart.refreshSuggestedProductsSlider();
				}

			}
			/* </fs_premium_only> */

			setTimeout(() => {
				cart.refreshCartVisibility();
			}, 10)

		},

		initMutationObserver() {

			if(cart.isReady) {
				return false;
			}

			$('body').mutated((changes) => {

				if(cart.isReady) {
					return false;
				}

				changes.some((change) => {

					return Array.prototype.slice.call(change.addedNodes).some((item) => {

						if($(item).hasClass('add_to_cart_button') || $(item).hasClass('single_add_to_cart_button')) {

							cart.flushCache();
							cart.setTriggerDefaultText();

							return true;
						}

					});
				});
			});
		},

		initScrollObserver() {

			let resize_observer = new ResizeObserver(() => {
				cart.autoHeight();
			});

			$$(cart.el.body).children().each((index, child) => {
				resize_observer.observe(child);
			});
		},

		setTriggerDefaultText() {

			if($$(cart.el.singleAddToCartBtn).length > 0) {

				$$(cart.el.singleAddToCartBtn).each(function() {

					$(this).data('defaultText', $(this).html().trim());

					if($(this).data('defaultText') !== '') {
						$(this).html(lang('wait'));
					}

					$(this).data('loading', true).addClass('loading');

				});
			}
		},

		resetTriggerDefaultText() {

			$$(cart.el.singleAddToCartBtn).each(function() {

				$(this).removeData('loading').removeClass('loading');

				if($(this).data('defaultText') !== '') {
					$(this).html($(this).data('defaultText'));
				}

			});
		},

		transitionEnd(el, once, callback) {

			let events = 'webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend';

			if(once) {

				el.one(events, (evt) => {

					$(this).off(events);

					evt.stopPropagation();
					callback($(this));
				});

			}else{

				el.on(events, function(evt) {

					evt.stopPropagation();
					callback($(this));
				});
			}
		},

		transitionEndClear(el) {

			el.off('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend');
		},

		animationEnd(el, once, callback) {

			let events = 'webkitAnimationEnd oanimationend oAnimationEnd msAnimationEnd animationend';

			if(once) {

				el.one(events, function(evt) {

					$(this).off(events);

					evt.stopPropagation();
					callback($(this));
				});

			}else{

				el.on(events, function(evt) {

					evt.stopPropagation();
					callback($(this));
				});
			}
		},

		clearCartConfirm() {

			cart.setNotice(lang('clear_confirm'), 'error');
		},

		clearCart() {

			cart.hideNotice(true);

			cart.request('clear');
		},

		clearCartRestore() {

			cart.hideNotice(true);

			cart.request('clear_restore');
		},

		toggleCart(flag = null) {

			if(cart.isTransitioning || cart.isLoading) {
				return false;
			}

			cart.isTransitioning = true;

			let action;

			if(flag) {
				action = flag ? 'open' : 'close';
			}else{
				action = cart.isActive ? 'close' : 'open';
			}

			cart.transitionEnd($$(cart.el.wrapper), true, () => {

				cart.isTransitioning = false;

				if (cart.isActive) {

					$$(cart.el.container).addClass('xt_woofc-cart-opened');
					$$(cart.el.container).removeClass('xt_woofc-cart-closed');

					// needed for custom payment buttons
					$(document.body).trigger('wc_fragments_loaded');

					cart.hideNotice();
					cart.hideLoading();

					$(document.body).trigger('xt_woofc_on_opened');

				} else {

					if(cart.modalMode) {
						$$(cart.el.wrapper).css('transition', 'none');
					}
					$$(cart.el.container).removeClass('xt_woofc-cart-opened');
					$$(cart.el.container).addClass('xt_woofc-cart-closed');

					if(cart.modalMode) {
						setTimeout(() => {
							$$(cart.el.wrapper).css('transition', '');
						}, 100)
					}

					$(document.body).trigger('xt_woofc_on_closed');

				}

				cart.refreshCartVisibility();
				cart.autoHeight();

			});

			if( action === 'close' && cart.isActive) {

				cart.isActive = false;

				$$(cart.el.container).removeClass('xt_woofc-cart-open');
				$$(cart.el.container).addClass('xt_woofc-cart-close');

				$(document.body).trigger('xt_woofc_on_closing');

				if(optionEnabled('body_lock_scroll')) {
					cart.enableBodyScroll($$(cart.el.body));
				}

				cart.resetUndo();
				cart.hideNotice();

				setTimeout(() => {
					$$(cart.el.body).scrollTop(0);
					//check if cart empty to hide it
					cart.refreshCartVisibility();

					/* <fs_premium_only> */
					if(optionEnabled('premium') && optionEnabled('sp_slider_enabled')) {
						cart.destroySuggestedProductsSlider();
					}
					/* </fs_premium_only> */

				}, 500);

			} else if( action === 'open' && !cart.isActive) {

				$(document.body).trigger('xt_woofc_on_opening');

				cart.isActive = true;

				$$(cart.el.container).removeClass('xt_woofc-cart-close');
				$$(cart.el.container).addClass('xt_woofc-cart-open');

				if(optionEnabled('body_lock_scroll')) {
					cart.disableBodyScroll($$(cart.el.body));
				}

				/* <fs_premium_only> */
				if(optionEnabled('premium') && optionEnabled('sp_slider_enabled')) {

					cart.initSuggestedProductsSlider();
				}
				/* </fs_premium_only> */

				cart.autoHeight();
				cart.hideNotice();

			}

		},

		getCartPosition(viewMode) {

			let position_key = viewMode !== 'desktop' ? 'data-'+viewMode+'-position' : 'data-position';

			return $$(cart.el.container).attr(position_key);
		},

		/* <fs_premium_only> */
		animateAddToCart(trigger, single) {

			let productsContainer = $('body');
			let position = cart.getCartPosition(cart.viewMode);

			let findImageFunction = single ? cart.findSingleImage : cart.findLoopImage;

			findImageFunction(trigger, (item) => {

				if(!item || item.length === 0) {

					return;
				}

				let itemPosition = item.offset();
				let triggerPosition = $$(cart.el.trigger).offset();

				if(itemPosition.top === 0 && itemPosition.left === 0) {

					let products = trigger.closest('.products');
					let product = trigger.closest('.product');
					let single_main_product = single && products.length === 0;

					if(single_main_product && product.length) {
						itemPosition = product.offset();
					}else{
						itemPosition = trigger.offset();
						itemPosition.top = itemPosition.top - item.height();

						if(single_main_product) {
							itemPosition.left = itemPosition.left - item.width();
						}
					}
				}

				let defaultState = {
					opacity: 1,
					top: itemPosition.top,
					left: itemPosition.left,
					width: item.width(),
					height: item.height(),
					transform: 'scale(1)'
				};

				let top_dir = 0;
				let left_dir = 0;

				if(position === 'bottom-right') {

					top_dir = -1;
					left_dir = -1;

				}else if(position === 'bottom-left') {

					top_dir = -1;
					left_dir = 1;

				}else if(position === 'top-right') {

					top_dir = 1;
					left_dir = -1;

				}else if(position === 'top-left') {

					top_dir = 1;
					left_dir = 1;
				}

				let animationState = {
					top: triggerPosition.top + ($$(cart.el.trigger).height() / 2) - (defaultState.height / 2) + (trigger.height() * top_dir),
					left: triggerPosition.left + ($$(cart.el.trigger).width() / 2) - (defaultState.width / 2) + (trigger.width() * left_dir),
					opacity: 0.9,
					transform: 'scale(0.3)'
				};

				let inCartState = {
					top: triggerPosition.top + ($$(cart.el.trigger).height() / 2) - (defaultState.height / 2),
					left: triggerPosition.left + ($$(cart.el.trigger).width() / 2) - (defaultState.width / 2),
					opacity: 0,
					transform: 'scale(0)'
				};

				let duplicatedItem = item.clone();
				duplicatedItem.find('.add_to_cart_button').remove();
				duplicatedItem.css(defaultState);
				duplicatedItem.addClass('xt_woofc-fly-to-cart');

				duplicatedItem.appendTo(productsContainer);

				let flyAnimationDuration = $$(cart.el.container).attr('data-flyduration') ? $$(cart.el.container).attr('data-flyduration') : 650;
				flyAnimationDuration = (parseInt(flyAnimationDuration) / 1000);

				xt_gsap.to(duplicatedItem, flyAnimationDuration, { css: animationState, ease: Power3.easeOut, onComplete:() => {

					cart.animateCartShake();

					xt_gsap.to(duplicatedItem, (flyAnimationDuration * 0.8), { css: inCartState, ease: Power3.easeOut, onComplete: () => {

						$(duplicatedItem).remove();
					}});
				}});
			});
		},

		animateCartShake() {

			if($$(cart.el.container).attr('data-opencart-onadd') === '1') {

				cart.toggleCart(true);

			}else{

				let shakeClass = $$(cart.el.container).attr('data-shaketrigger');

				if(shakeClass !== '') {
					cart.shakeElement($$(cart.el.inner), shakeClass)
				}
			}
		},
		/* </fs_premium_only> */

		findLoopImage(trigger, callback) {

			if(trigger.data('product_image_src')) {

				let imageData = {
					src: trigger.data('product_image_src'),
					width: trigger.data('product_image_width'),
					height: trigger.data('product_image_height')
				};

				cart.createFlyToCartImage(imageData, (img) => {

					callback(img);
				});

			}else{

				callback(null);
			}
		},

		findSingleImage(trigger, callback) {

			let imageData, fromElem;
			const form = trigger.closest('form');
			const product = trigger.closest('.product');
			const is_letiable = form.hasClass('letiations_form');

			if(is_letiable) {

				let letiation_id = parseInt(form.find('input[name=letiation_id]').val());
				let letiations = form.data('product_letiations');
				let letiation = letiations ? letiations.find((item) => {
					return item.letiation_id === letiation_id;
				}) : null;

				if(letiation && letiation.image && letiation.image.src !== '') {

					imageData = {
						src: letiation.image.src,
						width: letiation.image.src_w,
						height: letiation.image.src_h
					};
				}
			}

			if(!imageData && product.length) {

				fromElem = product.find('.xtfw-wc-product-image img');

				if(fromElem.length) {

					imageData = {
						src: fromElem.attr('src'),
						width: fromElem.attr('width'),
						height: fromElem.attr('height')
					};
				}
			}

			if(!imageData) {

				fromElem = form.find('.xt_woofc-product-image');

				if (fromElem.length && fromElem.data('product_image_src')) {

					imageData = {
						src: fromElem.data('product_image_src'),
						width: fromElem.data('product_image_width'),
						height: fromElem.data('product_image_height')
					};
				}
			}

			if(imageData) {

				cart.createFlyToCartImage(imageData, (img) => {

					callback(img);
				});

			}else{

				callback(null);
			}
		},

		createFlyToCartImage(imageData, callback) {

			let item = $('<img>');
			item.attr('src', imageData.src);
			item.attr('width', imageData.width);
			item.attr('height', imageData.height);

			item.css({
				width: imageData.width + 'px',
				height: imageData.height + 'px'
			});

			item.on('load', function() {
				callback($(this));
			});

			item.on('error', function() {
				callback(null);
			});

		},

		request(type, args, callback) {

			$(document.body).trigger('xt_woofc_request_start', [args]);

			cart.removeAllBodyNotices();
			cart.showLoading();

			if(type !== 'remove' && type !== 'restore' && type !== 'clear' && type !== 'clear_restore') {
				cart.lastRemovedKey = null;
				cart.hideNotice();
			}

			if(type === 'refresh' || type === 'totals') {

				cart.refreshFragments(type, callback);
				return false;
			}

			args = $.extend(args, {type: type});

			$.XT_Ajax_Queue({

				url: cart.get_url('xt_woofc_'+type),
				data: args,
				type: 'post'

			}).done((data) => {

				cart.flushCache();

				$(document.body).trigger('xt_woofc_request_done');

				if(type === 'restore' || type === 'clear_restore') {
					cart.refreshCart(callback);
				}else {
					cart.onRequestDone(data, type, callback);
				}
			});
		},

		refreshFragments(type, callback) {

			$.XT_Ajax_Queue({
				url: cart.get_url('get_refreshed_fragments'),
				data: {
					type: type
				},
				type: 'post'

			}).done((data) => {

				cart.onRequestDone(data, type, callback);
			});
		},

		onRequestDone(data, type, callback = null) {

			$.each( data.fragments, ( key, value ) => {

				$(key).replaceWith(value);
			});

			if (type !== 'add') {

				$(document.body).trigger('wc_fragments_refreshed');

			} else {

				cart.onFragmentsRefreshed();
			}

			cart.hideLoading();

			if(callback) {
				callback(data);
			}
		},

		onFragmentsRefreshed() {

			cart.flushCache();
			cart.removeUnwantedElements();
			cart.refreshCartCountSize();
			cart.maybeShowNotice();
			cart.refreshCartVisibility();

			if(cart.hasErrors()) {
				$( document.body ).trigger( 'xt_woofc_has_errors' );
			}

			setTimeout(() => {
				cart.autoHeight();
			}, 100);

			/* <fs_premium_only> */
			if(optionEnabled('premium') ) {

				if (optionEnabled('sp_slider_enabled')) {

					cart.initSuggestedProductsSlider();
				}

				if (optionEnabled('cart_shipping_bar_enabled') && $$(cart.el.shippingBarPerc).length) {

					let width = $$(cart.el.shippingBarPerc).data('width');
					setTimeout(() => {
						$$(cart.el.shippingBarPerc).find('span').css('width', width);
					}, 100)
				}
			}
			/* </fs_premium_only> */

		},

		updateProduct(key, qty, callback = null) {

			if(qty > 0) {

				cart.request('update', {

					cart_item_key: key,
					cart_item_qty: qty

				}, (data) => {

					$( document.body ).trigger( 'xt_woofc_product_update', [ key, qty ] );

					if(callback) {
						callback(data);
					}

				});

			}else{
				cart.removeProduct(key, callback);
			}
		},

		removeProduct(key, callback = null) {

			cart.showLoading(true);
			cart.lastRemovedKey = key;

			cart.request('remove', {

				cart_item_key: key

			}, () => {

				cart.resetUndo();

				let timeout = 0;
				let product = $$(cart.el.list).find('li[data-key="'+key+'"]');
				let isBundle = product.hasClass('xt_woofc-bundle');
				let isComposite = product.hasClass('xt_woofc-composite');
				let toRemove = [];
				let $prev;
				let $next;

				toRemove.push(product);

				if(isBundle || isComposite) {

					let selector = '';
					let group_id = product.data('key');

					if(isBundle) {
						selector += '.xt_woofc-bundled-item[data-group="'+group_id+'"]';
					}else{
						selector += '.xt_woofc-composite-item[data-group="'+group_id+'"]';
					}

					let groupedProducts = $($$(cart.el.list).find(selector).get().reverse());

					groupedProducts.each(function() {
						toRemove.push($(this));
					});
				}

				toRemove.reverse().forEach(($item) => {

					setTimeout(() => {

						$prev = $item.prev();
						if($prev.length && $item.is(':last-of-type')) {
							$prev.addClass('xt_woofc-deleting-last');
						}

						$next = $item.next();
						if($next.length) {
							$next.css('--xt-woofc-list-prev-item-height', $item.outerHeight(true) + 'px');
						}

						$item.addClass('xt_woofc-deleted');

					}, timeout);

					timeout = timeout + 270;

				});

				setTimeout(() => {

					cart.refreshCartVisibility();
					cart.autoHeight();

					$( document.body ).trigger( 'xt_woofc_product_removed', [ key ] );

					//wait 8sec before completely remove the item
					cart.undoTimeoutId = setTimeout(() => {

						$( document.body ).trigger( 'xt_woofc_product_dom_removed', [ key ] );

						cart.resetUndo();
						cart.hideNotice();
						$$(cart.el.list).find('.xt_woofc-deleting-last').removeClass('xt_woofc-deleting-last');

						if(callback) {
							callback();
						}

					}, 8000);

				}, timeout);
			});

		},

		hideCartTitle() {

			if(cart.noticeCollidingWithTitle()) {
				$$(cart.el.title).css({'opacity': 0.3, 'transform': 'translateX(-150%)'});
			}
		},

		showCartTitle() {
			$$(cart.el.title).css({'opacity': 1, 'transform': 'translateX(0)'});
		},

		noticeCollidingWithTitle() {

			let maxWidth = $(cart.el.header).width() - $(cart.el.title).outerWidth(true);
			$('.xt_woofc-header-action').each(function() {

				maxWidth -= $(this).outerWidth(true);
			});

			return $(cart.el.notice).outerWidth(true) >= maxWidth;
		},

		shakeElement(elemToShake, type = null) {
			if(elemToShake && elemToShake.length) {
				const selector = 'xt_woofc-shake' + (type ? '-'+type : '');
				cart.animationEnd(elemToShake, true, () => {
					elemToShake.removeClass(selector);
				});
				elemToShake.addClass(selector);
			}
		},

		hideNotice(hideCouponToggle = false) {

			$(document.body).trigger('xt_woofc_before_hide_notice', [hideCouponToggle]);

			if(cart.el.noticeTimeoutId) {
				clearTimeout(cart.el.noticeTimeoutId);
			}

			$$(cart.el.notice).removeClass('xt_woofc-visible');

			cart.showCartTitle();
			cart.transitionEnd($$(cart.el.notice), true, () => {
				$$(cart.el.notice).empty();
				$(document.body).trigger('xt_woofc_after_hide_notice', [hideCouponToggle]);
			});
		},

		showNotice(elemToShake = null) {

			cart.transitionEndClear($$(cart.el.notice));

			$(document.body).trigger('xt_woofc_before_show_notice', [elemToShake]);

			let timeout = elemToShake ? 100 : 0;

			$$(cart.el.notice).removeClass('xt_woofc-visible');

			if(elemToShake) {

				cart.shakeElement(elemToShake);
			}

			setTimeout(() => {

				$$(cart.el.notice).addClass('xt_woofc-visible');
				cart.hideCartTitle();

				if(cart.noticeHasError()) {

					cart.shakeElement($$(cart.el.notice));

					if(elemToShake) {

						cart.shakeElement(elemToShake);
					}
				}

				if(cart.el.noticeTimeoutId) {
					clearTimeout(cart.el.noticeTimeoutId);
				}

				if($$(cart.el.notice).find('a').length === 0) {
					cart.el.noticeTimeoutId = setTimeout(() => {
						cart.hideNotice();
					}, 6000);
				}

				$(document.body).trigger('xt_woofc_after_show_notice', [elemToShake]);

			}, timeout);
		},

		maybeShowNotice() {

			if($$(cart.el.notice).length && $$(cart.el.notice).html().trim() !== '') {
				cart.showNotice();
			}
		},

		noticeHasError() {

			return $$(cart.el.notice).data('type') === 'error';
		},

		setNotice(notice, type = 'success', elemToShake = null) {

			if(!cart.isActive) {
				return;
			}

			$$(cart.el.notice).removeClass (function (index, className) {
				return (className.match (/(^|\s)xt_woofc-notice-\S+/g) || []).join(' ');
			});

			$$(cart.el.notice).data('type', type).addClass('xt_woofc-notice-'+type).html(notice);

			cart.showNotice(elemToShake);
		},

		resetUndo() {

			if(cart.undoTimeoutId) {
				clearInterval(cart.undoTimeoutId);
			}

			$$(cart.el.list).find('.xt_woofc-deleted').remove();
		},

		undoProductRemove(key, callback) {

			cart.request('restore', {

				cart_item_key: key,

			}, callback);
		},

		hasErrors() {

			return $$(cart.el.container).find('.woocommerce-error').length > 0;
		},

		getFirstError() {

			return $$(cart.el.container).find('.woocommerce-error').first();
		},

		refreshCart(callback = null) {

			cart.request('refresh', {}, () => {

				if(callback) {
					callback();
				}
			});
		},

		refreshCartVisibility() {

			if( $$(cart.el.list).find('li:not(.xt_woofc-deleted):not(.xt_woofc-no-product)').length === 0) {
				$$(cart.el.container).addClass('xt_woofc-empty');
				cart.isEmpty = true;
				$(document.body).trigger('xt_woofc_emptied');
			}else{
				$$(cart.el.container).removeClass('xt_woofc-empty');
				cart.isEmpty = false;
			}

			$(document.body).trigger('xt_woofc_refresh_visibility');

		},

		refreshCartCountSize() {

			let quantity = Number($$(cart.el.counter).find('li').eq(0).text());

			if(quantity > 999) {

				$$(cart.el.counter).removeClass('xt_woofc-count-big');
				$$(cart.el.counter).addClass('xt_woofc-count-bigger');

			}else if(quantity > 99) {

				$$(cart.el.counter).removeClass('xt_woofc-count-bigger');
				$$(cart.el.counter).addClass('xt_woofc-count-big');

			}else{

				$$(cart.el.counter).removeClass('xt_woofc-count-big');
				$$(cart.el.counter).removeClass('xt_woofc-count-bigger');
			}

			$(document.body).trigger('xt_woofc_refresh_counter_size', [quantity]);

		},

		removeUnwantedElements() {

			if($$(cart.el.body).find('.woocommerce-cart-form').length > 1) {
				$$(cart.el.body).find('.woocommerce-cart-form').each(function(i) {
					if(i > 0) {
						$(this).remove();
					}
				});
				$$(cart.el.body).find('.woocommerce-cart-form').empty();
			}

			if($$(cart.el.body).find('.woocommerce-notices-wrapper').length) {
				$$(cart.el.body).find('.woocommerce-notices-wrapper').remove();
			}

			if($$(cart.el.body).find('.woocommerce-form-coupon,.woocommerce-form-coupon-toggle').length) {
				$$(cart.el.body).find('.woocommerce-form-coupon,.woocommerce-form-coupon-toggle').remove();
			}

			if(optionEnabled('enable_totals') && $$(cart.el.body).find('.angelleye-proceed-to-checkout-button-separator').length) {

				setTimeout(() => {
					$$(cart.el.body).find('.angelleye-proceed-to-checkout-button-separator').insertAfter($$(cart.el.body).find('.angelleye_smart_button_bottom'));
				},100);
			}
		},

		scrollTo(top, instant = false) {

			if(instant) {
				$$(cart.el.body).scrollTop(top);
				return;
			}

			$$(cart.el.body).animate({scrollTop: top}, 400);

			setTimeout(() => {
				$$(cart.el.body).animate({scrollTop: top}, 400);
			}, 100);
		},

		scrollToTop(instant = false) {

			cart.scrollTo(0, instant);
		},

		scrollToBottom(instant = false) {

			cart.scrollTo($$(cart.el.body).get(0).scrollHeight, instant);
		},

		removeAllBodyNotices() {

			let $notices = $$(cart.el.bodyFooter).find('.woocommerce-error, .woocommerce-message');
			if($notices.length) {
				$notices.each(function() {
					$(this).slideUp("fast", function() {
						$(this).remove();
					});
				});
			}
		},

		checkoutButtonProcessing() {

			$$(cart.el.checkoutButton).addClass('xt_woofc-processing');
		},

		checkoutButtonIdle() {

			$$(cart.el.checkoutButton).removeClass('xt_woofc-processing');
		},

		resetCheckoutButtonLabel(label = null) {

			label = label ? label : 'checkout';

			/* <fs_premium_only> */
			if(optionEnabled('premium') && optionEnabled('cart_checkout_form') && cart_checkout.isFrameActive()) {
				label = 'place_order';
			}
			/* </fs_premium_only> */

			cart.checkoutButtonIdle();
			cart.updateCartButtonLabel(label);
		},

		cartReady() {

			cart.resetTriggerDefaultText();
			cart.resetCheckoutButtonLabel();

			$$(cart.el.container).addClass('xt_woofc-cart-closed');
			$('body').addClass('xt_woofc-ready');

			cart.resize();
			$(document.body).trigger('xt_woofc_ready');

			cart.isReady = true;
		},

		get_url( endpoint ) {
			return option('wc_ajax_url').toString().replace(
				'%%endpoint%%',
				endpoint
			);
		},

		updateCartTitle(title_key) {

			let svg = '';

			if(title_key === 'checkout') {
				svg += '<a href="#" class="xt_woofc-close-checkout" title="'+lang('back_to_cart')+'">'+goBackSvg+'</a>';
			}else if(title_key === 'coupons') {
				svg = '<a href="#" class="xt_woofc-close-coupon-form" title="' + lang('back_to_cart') + '">' + goBackSvg + '</a>'
			}

			$$(cart.el.title).hide().html(svg+lang(title_key)).fadeIn("fast");
		},

		updateCartButtonLabel(label_key) {

			$$(cart.el.checkoutButton).find('.xt_woofc-footer-label').text(lang(label_key));
		},

		/* <fs_premium_only> */
		initSuggestedProductsSlider(){

			cart.destroySuggestedProductsSlider();

			let sliderArrow = option('sp_slider_arrow');

			cart.suggestedProductsSlider = $('.xt_woofc-sp-products').lightSlider({
				item: 1,
				enableDrag: XT.isTouchDevice(),
				adaptiveHeight: true,
				controls: optionEnabled('sp_slider_enabled'),
				prevHtml: '<span class="xt_woofc-sp-arrow-icon '+sliderArrow+'"></span>',
				nextHtml: '<span class="xt_woofc-sp-arrow-icon '+sliderArrow+'"></span>',
				onSliderLoad: () => {

					setTimeout(() => {
						cart.resize();
						setTimeout(() => {
							$('.xt_woofc-sp').css('opacity', 1);
						}, 300);
					}, 200);
				}
			});

		},

		destroySuggestedProductsSlider() {

			if(cart.suggestedProductsSlider && typeof(cart.suggestedProductsSlider.destroy) !== 'undefined') {
				$('.xt_woofc-sp').css('opacity', 0);
				cart.suggestedProductsSlider.destroy();
			}

		},

		refreshSuggestedProductsSlider() {

			if(cart.suggestedProductsSlider && typeof(cart.suggestedProductsSlider.refresh) !== 'undefined') {
				cart.suggestedProductsSlider.refresh();
			}

		},

		hideFooter() {
			if(!cart.isActive) {
				return;
			}
			$(document.body).trigger('xt_woofc_before_hide_footer');
			xt_gsap.to($('.xt_woofc-footer'), {y: '100%', duration: 0.3, onComplete: () => {
				$(document.body).trigger('xt_woofc_after_hide_footer');
			}});
		},

		showFooter() {

			$(document.body).trigger('xt_woofc_before_show_footer');
			xt_gsap.to($('.xt_woofc-footer'), {y: '0', duration: 0.3, onComplete: () => {
				$(document.body).trigger('xt_woofc_after_show_footer');
			}});
		}
		/* </fs_premium_only> */

	};

	/* <fs_premium_only> */

	const cart_coupons = {
		el: {
			toggle: '.xt_woofc-coupon',
			removeBtn: '.xt_woofc-remove-coupon',
			applyBtn: '.xt_woofc-coupon-apply',
			form: '.xt_woofc-coupon-form',
		},
		init() {

			cart_coupons.initEvents();

			if (cart.couponsEnabled) {

				cart_coupons.closeCouponForm(true);
				$$(cart_coupons.el.form).on('submit', cart_coupons.submitForm);
			}
		},
		initEvents() {

			$(document.body).on('vclick', cart_coupons.el.removeBtn, cart_coupons.removeCouponEvent);

			if (cart.couponsEnabled) {

				$(document.body).on('xt_woofc_before_show_notice', cart_coupons.hideToggle);
				$(document.body).on('xt_woofc_before_hide_notice', (evt, hideCouponToggle) => {

					if(!hideCouponToggle) {
						cart_coupons.showToggle();
					}
				});

				$(document.body).on('xt_woofc_on_closing', cart_coupons.closeCouponForm);
				$(document.body).on('xt_woofc_request_start', cart_coupons.closeCouponForm);
				$(document.body).on('xt_woofc_product_dom_removed', cart_coupons.closeCouponForm);
				$(document.body).on('xt_woofc_request_done', cart_coupons.closeCouponForm);
				$(document.body).on('xt_woofc_before_show_checkout', cart_coupons.closeCouponForm);
				$(document.body).on('xt_woofc_hide_checkout', cart_coupons.closeCouponForm);

				$(document.body).on('vclick', cart_coupons.el.toggle + ', .xt_woofc-close-coupon-form', cart_coupons.showCouponForm);
				$(document.body).on('vclick', cart_coupons.el.applyBtn, cart_coupons.applyCoupon);
			}
		},

		hideToggle() {
			$$(cart_coupons.el.toggle).removeClass('xt_woofc-visible');
		},
		showToggle() {
			$$(cart_coupons.el.toggle).addClass('xt_woofc-visible');
		},
		showCouponForm(evt) {

			evt.preventDefault();

			cart.scrollToTop();

			if ($$(cart_coupons.el.form).is(':visible')) {

				cart_coupons.closeCouponForm();

			} else {

				if (cart.couponsListEnabled) {
					cart.updateCartTitle('coupons');
					cart_coupons.hideToggle();
				}

				$$(cart_coupons.el.form).slideDown(350, function () {
					$$(cart_coupons.el.form).find(':input:eq(0)').focus();

					if (cart.couponsListEnabled) {

						$$(cart.el.wrapper).addClass('xt_woofc-coupons-visible');
						cart.disableBodyScroll($$(cart_coupons.el.form));
					}
				});
			}
		},
		closeCouponForm(fast = false) {

			if ($$(cart_coupons.el.form).is(':visible')) {

				if (fast) {
					$$(cart_coupons.el.form).hide();
				} else {
					$$(cart_coupons.el.form).slideUp();
				}

				cart.hideNotice();

				let couponError = $$(cart_coupons.el.form).find('.xt_woofc-coupon-error');
				if (couponError.length) {
					couponError.empty();
				}

				if (cart.couponsListEnabled) {

					cart.enableBodyScroll($$(cart_coupons.el.form));

					$$(cart.el.wrapper).removeClass('xt_woofc-coupons-visible');

					if(optionEnabled('cart_checkout_form') && cart_checkout.isFrameActive()) {
						cart.updateCartTitle('checkout');
					}else{
						cart.updateCartTitle('title');
					}
				}
			}
		},
		submitForm(evt) {

			evt.preventDefault();

			const $form = $(evt.currentTarget);

			if ($form.is('.processing')) {
				return false;
			}

			$form.addClass('processing');

			cart.showLoading();

			let data = {
				coupon_code: $form.find('input[name="coupon_code"]').val()
			};

			$.XT_Ajax_Queue({
				url: cart.get_url('xt_woofc_apply_coupon'),
				data: data,
				type: 'post'

			}).done((response) => {

				$form.removeClass('processing');

				setTimeout(() => {

					cart.onRequestDone(response, 'apply_coupon');
					cart.hideLoading();

					if (!cart.noticeHasError()) {

						$(document.body).trigger('coupon_applied');
					}

				}, 5);

			});

			return false;
		},
		applyCoupon(evt) {

			evt.preventDefault();

			let coupon = $(evt.currentTarget).data('coupon');

			$$(cart_coupons.el.form).find('input[name="coupon_code"]').val(coupon);
			$(cart_coupons.el.form).trigger('submit');
		},
		removeCouponEvent(evt) {

			evt.preventDefault();

			let coupon = $(evt.currentTarget).data('coupon');
			let container = $(evt.currentTarget).closest('.woocommerce-checkout-review-order');

			cart_coupons.removeCoupon(coupon, container);
		},
		removeCoupon(coupon, container) {

			if (container.is('.processing')) {
				return false;
			}

			container.addClass('processing');

			cart.showLoading();

			let data = {
				coupon: coupon
			};

			$.XT_Ajax_Queue({
				url: cart.get_url('xt_woofc_remove_coupon'),
				data: data,
				type: 'post'

			}).done( (response) => {

				container.removeClass('processing');

				cart.onRequestDone(response, 'remove_coupon');
				$(document.body).trigger('coupon_removed');

				// Remove coupon code from coupon field
				$('form.xt_woofc-coupon-form').find('input[name="coupon_code"]').val('');

				cart.hideLoading();

				if ($$(cart_coupons.el.form).is(':visible') && cart.couponsListEnabled) {
					return;
				}

				cart.scrollToBottom();
			});

		}
	};

	/**
	 * Object to handle AJAX calls for cart shipping changes.
	 */
	const cart_shipping = {

		/**
		 * Initialize event handlers and UI state.
		 */
		init() {


			$(document).off('vclick', '.xt_woofc-shipping-edit');
			$(document).on('vclick', '.xt_woofc .xt_woofc-shipping-edit', cart_shipping.toggle_shipping);

			$(document).off('vclick', 'select.shipping_method, :input[name^=shipping_method]');
			$(document).on('change', '.xt_woofc select.shipping_method, .xt_woofc :input[name^=shipping_method]', cart_shipping.shipping_method_selected);

			$(document).off('submit', 'form.woocommerce-shipping-calculator');
			$(document).on('submit', '.xt_woofc form.woocommerce-shipping-calculator', cart_shipping.shipping_calculator_submit);

			$$(cart.el.body).find('.shipping-calculator-form').hide();
		},

		/**
		 * Toggle Shipping Calculator panel
		 */
		toggle_shipping() {

			$$(cart.el.body).find('.shipping-calculator-form').slideToggle('medium', function () {
				cart.scrollToBottom();
			});

			$(document.body).trigger('country_to_state_changed'); // Trigger select2 to load.
		},

		/**
		 * Handles when a shipping method is selected.
		 */
		shipping_method_selected() {

			let shipping_methods = {};

			$$(cart.el.body).find('select.shipping_method, :input[name^=shipping_method][type=radio]:checked, :input[name^=shipping_method][type=hidden]').each(function () {
				shipping_methods[$(this).data('index')] = $(this).val();
			});

			cart.showLoading();

			let data = {
				shipping_method: shipping_methods
			};

			$.ajax({
				type: 'post',
				url: cart.get_url('xt_woofc_update_shipping_method'),
				data: data,
				dataType: 'json'
			}).done((response) => {
				cart_shipping.update_cart_totals_div(response);
				cart.onRequestDone(response, 'update_shipping_method');
			}).always(() => {
				cart.hideLoading();
				$(document.body).trigger('updated_shipping_method');
			});
		},

		/**
		 * Handles a shipping calculator form submit.
		 *
		 * @param {Object} evt The JQuery event.
		 */
		shipping_calculator_submit(evt) {
			evt.preventDefault();

			let $form = $(evt.currentTarget);

			cart.showLoading();

			// Provide the submit button value because wc-form-handler expects it.
			$('<input />').attr('type', 'hidden')
				.attr('name', 'calc_shipping')
				.attr('value', 'x')
				.appendTo($form);

			// Make call to actual form post URL.
			$.ajax({
				type: $form.attr('method'),
				url: $form.attr('action'),
				data: $form.serialize(),
				dataType: 'html'
			}).done((response) => {

				cart_shipping.toggle_shipping();

				if (cart_shipping.update_wc_div(response, false)) {
					cart.scrollToBottom();
				}

				$(document.body).trigger('xt_woofc_cart_shipping_updated');

			}).always(() => {
				cart.hideLoading();
			});
		},

		/**
		 * Update the .woocommerce div with a string of html.
		 *
		 * @param {String} html_str The HTML string with which to replace the div.
		 * @param {boolean} preserve_notices Should notices be kept? False by default.
		 */
		update_wc_div(html_str, preserve_notices) {

			let $html = $.parseHTML(html_str);
			let $errors = $('.woocommerce-error', $html);
			let $notices = $('.woocommerce-info', $html);

			// Remove errors
			if (!preserve_notices) {
				$('.woocommerce-error').remove();
			}

			// Display errors
			if ($errors.length > 0 && $$(cart.el.wooNotices).length) {

				$$(cart.el.wooNotices).prepend($errors);
				cart.scrollToTop();

				return false;

			}else if($notices.length) {

				cart.setNotice($notices.first().text());
			}

			cart.showLoading();

			$(document.body).trigger('updated_wc_div');

			setTimeout(() => {
				cart.hideLoading();
			}, 800);

			return true;
		},

		/**
		 * Update the .cart_totals div with a string of html.
		 *
		 * @param {String} html_str The HTML string with which to replace the div.
		 */
		update_cart_totals_div(html_str) {
			$('.cart_totals').replaceWith(html_str);
			$(document.body).trigger('updated_cart_totals');
		}

	};

	const cart_checkout = {

		el: {
			frame: '.xt_woofc-checkout-frame',
			submittedMsg: '#xt_woofc-checkout-thankyou',

		},
		loaded: false,

		init() {

			$(document.body).on('xt_woofc_on_closed', () => {

				cart_checkout.hide();

				if($('html').hasClass('xt_woofc-checkout-complete')) {
					$('html').removeClass( 'xt_woofc-checkout-complete' );
					$$(cart_checkout.el.submittedMsg).removeClass('xt_woofc-loading').hide();
				}
			});

			$(document.body).on('xt_woofc_before_show_checkout', () => {

				cart.scrollToTop(true);
				cart.hideNotice();

			});

			$(document.body).on('xt_woofc_hide_checkout', () => {

				cart.showFooter();
				cart.scrollToTop(true);
				cart.resize();
			});

			$(document.body).on('xt_woofc_before_show_footer', () => {
				let height = $(':root').css('--xt-woofc-checkout-btn-height');
				xt_gsap.to('#xt_woofc', {'--xt-woofc-checkout-btn-height': height, duration: 0});
			});

			$(document.body).on('xt_woofc_after_hide_footer', () => {
				if(cart_checkout.isFrameActive()) {
					xt_gsap.to('#xt_woofc', {'--xt-woofc-checkout-btn-height': 0, duration: 0.3});
				}
			});

			$(document.body).on('xt_woofc_checkout_place_order', () => {
				cart.checkoutButtonProcessing();
				cart.updateCartButtonLabel('placing_order');
			});

			$(document.body).on('xt_woofc_checkout_error', () => {
				cart.resetCheckoutButtonLabel();
			});

			$(document.body).on('xt_woofc_has_errors xt_woofc_cart_shipping_updated', (evt) => {
				evt.preventDefault();
				cart_checkout.unload();
			});

			$(document.body).on('vclick', '.xt_woofc-close-checkout', (evt) => {
				evt.preventDefault();
				cart_checkout.hide();
			});

			// Communication between Parent & Checkout Frame
			$(document.body).on('xt_woofc_show_checkout', cart_checkout.messageFrame);
			$(document.body).on('wc_fragments_refreshed', cart_checkout.messageFrame);
			$(document.body).on('wc_fragments_loaded', cart_checkout.messageFrame);
			$(document.body).on('xt_atc_added_to_cart', cart_checkout.messageFrame);
			$(document.body).on('xt_woofc_emptied', cart_checkout.messageFrame);
			$(document.body).on('xt_woofc_checkout_place_order', cart_checkout.messageFrame);
		},

		messageFrame(evt) {

			if(cart_checkout.isFrameLoaded()) {

				const message = JSON.stringify({
					event: evt.type
				});

				document.querySelector(cart_checkout.el.frame).contentWindow.postMessage(message, option('home_url'));
			}
		},

		load() {

			if ( cart.isEmpty ) {
				return;
			}

			cart.showLoading(true);

			$('html').addClass( 'xt_woofc-checkout-loading' );

			let checkoutFrameWrap = $('<div>');

			let checkoutFrame = $( '<iframe name="xt_woofc_checkout_frame">' );
			checkoutFrame.addClass( 'xt_woofc-checkout-frame' );
			checkoutFrame.attr( 'scrolling', 'no' );

			let url = new URL( option('checkout_frame_url') );
			url.searchParams.set( 'xt-woofc-checkout', 'true' );

			if(window.top.hasOwnProperty('wp') && window.top.wp.hasOwnProperty('customize')) {

				url.searchParams.set('customize_changeset_uuid', window.top.wp.customize.settings.changeset.uuid);
				url.searchParams.set('customize_messenger_channel', window.top.wp.customize.previewer.channel());
				url.searchParams.set('theme', wp.customize.settings.theme.stylesheet);
				url.searchParams.set('customize_autosaved', 'on');
			}

			checkoutFrame.attr('src', url);

			checkoutFrame.get(0).onload = () => {
				$('html').removeClass( 'xt_woofc-checkout-loading' );
				cart.hideLoading();
				cart_checkout.show();
			};

			checkoutFrameWrap.html(checkoutFrame);

			$$(cart.el.body).append(checkoutFrameWrap);
		},

		unload() {

			if(cart_checkout.isFrameAttached()) {

				$(cart_checkout.el.frame).parent().remove();
				cart_checkout.loaded = false;
				cart_checkout.hide();
			}
		},

		show() {

			if ( cart.isEmpty ) {
				return;
			}

			$(document.body).trigger('xt_woofc_before_show_checkout');

			if(!cart_checkout.isFrameAttached()) {
				return cart_checkout.load();
			}else{
				cart_checkout.loaded = true;
				setTimeout(() => {
					$(document.body).trigger('xt_woofc_show_checkout');
				}, 5)
			}

			$(cart_checkout.el.frame).fadeIn();


			cart.updateCartTitle('checkout');
			cart.updateCartButtonLabel('place_order');
			cart.checkoutButtonIdle();

			$('html').addClass('xt_woofc-checkout-active');
		},

		hide() {

			$(document.body).trigger('xt_woofc_before_hide_checkout');

			$(cart_checkout.el.frame).slideUp(300);

			$('html').removeClass( 'xt_woofc-checkout-active' );

			cart.updateCartTitle('title');
			cart.resetCheckoutButtonLabel();

			$(document.body).trigger('xt_woofc_hide_checkout');

		},

		resize( iframeHeight ) {

			if ( iframeHeight ) {
				let checkoutFrame = $(cart_checkout.el.frame).get(0);
				checkoutFrame.style.height = ( iframeHeight ) + 'px';

				cart.autoHeight();
			}
		},

		getFrameHeight() {

			let height = 0;
			if(optionEnabled('cart_checkout_form') && cart_checkout.isFrameActive()) {
				height += $(cart_checkout.el.frame).outerHeight(true);
			}

			return height;
		},

		isFrameAttached() {

			return $(cart_checkout.el.frame).length;
		},

		isFrameLoaded() {

			return cart_checkout.isFrameAttached() && cart_checkout.loaded;
		},

		isFrameLoading() {

			return $('html').hasClass('xt_woofc-checkout-loading');
		},

		isFrameActive() {

			return cart_checkout.isFrameAttached() && cart_checkout.isFrameLoaded() && $('html').hasClass('xt_woofc-checkout-active');
		},

		completed( orderUrl, redirect = false ){

			$('html').addClass('xt_woofc-checkout-complete');

			if($$(cart_checkout.el.submittedMsg).find('a').length) {
				$$(cart_checkout.el.submittedMsg).find('a').attr('href', orderUrl);
			}

			$$(cart_checkout.el.submittedMsg).addClass('xt_woofc-loading').fadeIn();

			cart.updateCartTitle('order_received_title');

			if ( orderUrl && redirect ) {
				setTimeout(() => {
					cart_checkout.redirect(orderUrl);
				}, 3000 );
			}else{
				cart.refreshCart();
			}
		},

		redirect( redirectUrl ) {
			window.location.href = redirectUrl;
		},

	};

	const cart_payments = {

		el: {
			buttons: '.xt_woofc-payment-btns',
		},
		observer: null,
		
		init() {

			$(document.body).on('xt_woofc_before_show_checkout xt_woofc_on_closed', () =>{
				cart_payments.clearButtonsObserver(true)
			});

			$(document.body).on('xt_woofc_hide_checkout xt_woofc_on_opened', () =>{
				cart_payments.initButtonsObserver();
			});

		},

		initButtonsObserver() {

			if(cart.isEmpty || !cart.isActive || cart_checkout.isFrameActive()) {
				return;
			}

			cart_payments.clearButtonsObserver();

			let height = 0;
			let previous_height = 0;
			let same_height_counter = 0;
			let timeout = 10;

			cart_payments.observer = setInterval(() => {

				if(same_height_counter > 10) {
					cart_payments.clearButtonsObserver();
					cart_payments.showButtons(height);

				}else {

					height = cart_payments.getButtonsHeight();

					if (height > 0 && height === previous_height) {
						same_height_counter++;
					} else {
						previous_height = height;
					}

					timeout = 20;
				}

			}, timeout);
		},

		clearButtonsObserver(hideButtons = false) {

			$('#ppc-button-minicart').not($$(cart.el.container).find('#ppc-button-minicart')).each(function() {
				$(this).remove();
			});

			if(cart_payments.observer) {
				clearInterval(cart_payments.observer);
			}

			if(hideButtons) {
				cart_payments.hideButtons();
			}
		},

		getButtonsHeight() {

			let buttonsHeight = 0;

			if(optionEnabled('custom_payments') && !cart.isEmpty && $(cart_payments.el.buttons).length && $(cart_payments.el.buttons).find('iframe').length && cart.isActive && !cart.isTransitioning) {

				let padding = (parseInt($(':root').css('--xt-woofc-payment-btns-padding')) * 2);

				$(cart_payments.el.buttons).find('.xt_woofc-payment-btn').each(function () {
					let $iframe = $(this).find('iframe');
					if ($iframe.length) {
						buttonsHeight += $iframe.height();
					}
				});

				buttonsHeight = buttonsHeight + padding;
			}

			return buttonsHeight;
		},

		hideButtons() {

			xt_gsap.to($$(cart.el.wrapper), {paddingBottom: 0, duration: 0.3});
			setTimeout(() => {
				$(cart_payments.el.buttons).css('opacity', '0');
			}, 100)
		},

		showButtons(height) {

			$(cart_payments.el.buttons).css('opacity', '1');
			xt_gsap.to($$(cart.el.wrapper), {paddingBottom: (height + 2), duration: 0.3});
		},

	};

	const cart_menu = {

		el: {
			item: '.xt_woofc-menu',
			link: '.xt_woofc-menu-link',
		},

		init() {

			if(optionIs('cart_menu_click_action', 'toggle')) {

				$(document.body).on('vclick', cart_menu.el.link, cart_menu.onClick);
			}

			$(document.body).on('xt_woofc_refresh_visibility', cart_menu.onCartRefreshVisibility);
			$(document.body).on('xt_woofc_refresh_counter_size', cart_menu.onCartRefreshCounterSize);
		},

		onClick(evt) {

			if($(cart.el.container).is(':visible')) {
				evt.preventDefault();
				cart.toggleCart();
			}
		},

		onCartRefreshVisibility() {

			if ($$(cart_menu.el.item).length) {

				if (cart.isEmpty) {
					$$(cart_menu.el.item).addClass('xt_woofc-menu-empty');
				} else {
					$$(cart_menu.el.item).removeClass('xt_woofc-menu-empty');
				}
			}
		},

		onCartRefreshCounterSize(evt, quantity) {

			if(quantity > 999) {

				$$(cart_menu.el.link).removeClass('xt_woofc-count-big');
				$$(cart_menu.el.link).addClass('xt_woofc-count-bigger');

			}else if(quantity > 99) {

				$$(cart_menu.el.link).removeClass('xt_woofc-count-bigger');
				$$(cart_menu.el.link).addClass('xt_woofc-count-big');

			}else{

				$$(cart_menu.el.link).removeClass('xt_woofc-count-big');
				$$(cart_menu.el.link).removeClass('xt_woofc-count-bigger');

			}
		}
	};

	const cart_shortcode = {

		el: {
			item: '.xt_woofc-shortcode',
			link: '.xt_woofc-shortcode-link',
		},

		init() {

			if(optionIs('cart_shortcode_click_action', 'toggle')) {

				$(document.body).on('vclick', cart_shortcode.el.link, cart_shortcode.onClick);
			}
		},

		onClick(evt) {

			evt.preventDefault();
			cart.toggleCart();
		}

	};

	/* </fs_premium_only> */

	$(function() {

		cart.init();

		/* <fs_premium_only> */
		if(optionEnabled('premium')) {

			cart_shipping.init();
			cart_coupons.init();

			if(optionEnabled('custom_payments')) {
				cart_payments.init();
			}

			if(optionEnabled('cart_checkout_form')) {
				cart_checkout.init();
			}

			if($$(cart.el.container).length) {

				if (optionEnabled('cart_menu_enabled') && $$(cart_menu.el.item).length) {
					cart_menu.init();
				}

				if (optionEnabled('cart_shortcode_enabled') && $$(cart_shortcode.el.item).length) {
					cart_shortcode.init();
				}
			}
		}
		/* </fs_premium_only> */

	});

	window.xt_woofc_is_loading = () => cart.isLoading;
	window.xt_woofc_is_cart_open = () => cart.isActive;
	window.xt_woofc_is_cart_empty = () => cart.isEmpty;
	window.xt_woofc_show_loading = (...args) => cart.showLoading(...args);
	window.xt_woofc_hide_loading = () => cart.hideLoading();
	window.xt_woofc_refresh_cart = (...args) => cart.refreshCart(...args);
	window.xt_woofc_toggle_cart = (...args) => cart.toggleCart(...args);
	window.xt_woofc_open_cart = () => cart.toggleCart(true);
	window.xt_woofc_close_cart = () => cart.toggleCart(false);
	window.xt_woofc_refresh_visibility = () => cart.refreshCartVisibility();
	window.xt_woofc_scroll_to = (...args) => cart.scrollTo(...args);
	window.xt_woofc_scroll_to_top = (...args) => cart.scrollToTop(...args);
	window.xt_woofc_scroll_to_bottom = (...args) => cart.scrollToBottom(...args);

	/* <fs_premium_only> */
	if(optionEnabled('premium')) {
		window.xt_woofc_unload_checkout = () => cart_checkout.unload();
		window.xt_woofc_resize_checkout = (...args) => cart_checkout.resize(...args);
		window.xt_woofc_hide_checkout = () => cart_checkout.hide();
		window.xt_woofc_show_checkout = () => cart_checkout.show();
		window.xt_woofc_checkout_active = () => cart_checkout.isFrameActive();
		window.xt_woofc_checkout_completed = (...args) => cart_checkout.completed(...args);
		window.xt_woofc_checkout_redirect = (...args) => cart_checkout.redirect(...args);
		window.xt_woofc_show_footer = () => cart.showFooter();
		window.xt_woofc_hide_footer = () => cart.hideFooter();
	}
	/* </fs_premium_only> */

})( jQuery, window);