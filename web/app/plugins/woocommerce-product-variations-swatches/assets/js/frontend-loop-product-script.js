jQuery(document).ready(function ($) {
    'use strict';
    //Impeka theme masonry
    jQuery(document).on('viwpvs_init_swatches', function () {
        jQuery(window).trigger('resize');
    });
    /**
     * Init functions after page loaded
     */
    viwpvs_frontend_loop_init();
    /**
     * Init functions after Jet smart filter loaded
     */
    jQuery(document).on('jet-filter-content-rendered', function () {
        init_swatches_after_ajax()
    });
    /**
     * Init functions after FacetWP loaded
     */
    jQuery(document).on('facetwp-loaded', function () {
        init_swatches_after_ajax()
    });
    /**
     * Init functions after Riode theme's ajax filter
     */
    if (window.hasOwnProperty('Riode') && window.Riode !== undefined) {
        window.Riode.$window.on('update_lazyload', function () {
            init_swatches_after_ajax()
        })
    }
    jQuery(document).on('facetwp-loaded', function () {
        init_swatches_after_ajax()
    });
    jQuery(document).on('avalon23-end-redraw-page', function () {
        init_swatches_after_ajax()
    });
    /**
     * Init functions after Ajax Load More(by Darren Cooney) loaded
     */
    window.almComplete = function (alm) {
        init_swatches_after_ajax()
    };
    /**
     * Init functions after Ajax complete
     */
    jQuery(document).on('ajaxComplete', function (event, jqxhr, settings) {
        viwpvs_frontend_loop_init();
        return false;
    });

    function init_swatches_after_ajax() {
        jQuery('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function (k, v) {
            jQuery(v).removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check vi_wpvs_variation_form vi_wpvs_variation_form_init').wpvs_get_variation().viwpvs_woo_product_variation_swatches();
        });
    }

    //Compatible
    viwpvs_fixed();
});

function viwpvs_frontend_loop_init() {
    jQuery('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function () {
        let swatches = viwpvs_set_swatches_position(jQuery(this));
        swatches.removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check').wpvs_get_variation();
        swatches.addClass('vi_wpvs_variation_form vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();
    });
}

jQuery(window).on('load', function () {
    'use strict';
    viwpvs_frontend_loop_init();
});

function viwpvs_set_swatches_position(swatches) {
    swatches = jQuery(swatches);
    if (jQuery.inArray(viwpvs_frontend_loop_product_params.theme, ['woostify']) !== -1) {
        let product, div_append;
        if (swatches.closest('.product').length) {
            product = swatches.closest('.product');
            product.addClass('vi_wpvs_loop_variation_woostify');
            div_append = product.find('.product-loop-meta .animated-meta');
            if (viwpvs_frontend_loop_product_params.theme_swatches_pos) {
                let clone = swatches.clone();
                swatches.remove();
                div_append.append('<span class="vi_wpvs_loop_variation_price vi_wpvs_loop_variation_hidden"></span>');
                div_append.find('.vi_wpvs_loop_variation_price').remove();
                if (viwpvs_frontend_loop_product_params.theme_swatches_pos === 'after_price') {
                    clone.insertAfter(div_append.find('.price'));
                    jQuery('<span class="vi_wpvs_loop_variation_price vi_wpvs_loop_variation_hidden"></span>').insertBefore(div_append.find('.vi_wpvs_loop_variation_form'));
                } else {
                    clone.insertBefore(div_append.find('.price'));
                    jQuery('<span class="vi_wpvs_loop_variation_price vi_wpvs_loop_variation_hidden"></span>').insertAfter(div_append.find('.vi_wpvs_loop_variation_form'));
                }
                swatches = clone;
            }
        }
    }
    return swatches;
}

function viwpvs_fixed() {
    //infiniteScroll
    jQuery(document).on('append.infiniteScroll', function (event, response, path, items) {
        jQuery(items).find('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function (k, v) {
            jQuery(v).removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check vi_wpvs_variation_form vi_wpvs_variation_form_init').wpvs_get_variation().viwpvs_woo_product_variation_swatches();
        });
    });
    //infinite theme
    if (typeof $us !== 'undefined') {
        $us.WGrid.prototype.afterAppendItems = function (items) {
            jQuery(items).find('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function (k, v) {
                jQuery(v).removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check vi_wpvs_variation_form vi_wpvs_variation_form_init').wpvs_get_variation().viwpvs_woo_product_variation_swatches();
            });
        };
    }
}

var viwpvs_get_variations = function ($swatches) {
    let self = this;
    self.is_atc = viwpvs_frontend_loop_product_params.is_atc;
    self.wc_ajax_url = viwpvs_frontend_loop_product_params.wc_ajax_url;
    self.swatches = $swatches;
    self.product_id = parseInt($swatches.data('product_id'));
    self.variation = $swatches.data('product_variations');
    self.is_find_variation = $swatches.data('vpvs_find_variation');
    self.is_ajax = !self.variation;
    self.xhr = false;
    self.img_product = jQuery(self.get_img_product($swatches));
    self.img_src = self.img_product.attr('data-src') || self.img_product.attr('src') || self.img_product.attr('content') || self.img_product.attr('srcset') || '';
    let product = $swatches.closest('.product'), attribute = {};
    if (!product.length) {//Authentic
        product = $swatches.closest('.item-product');
    }
    if (!product.length) {
        product = $swatches.closest('.product-item');
    }
    if (!product.length) {
        product = $swatches.closest('.gdlr-core-item-list');
    }
    if (!product.length) {
        product = $swatches.closest('.jet-woo-products__item');
    }
    if (!product.length) {
        product = $swatches.closest('.product-warp-item');
    }
    if (!product.length) {
        product = $swatches.closest('.product__box');
    }
    if (!product.length) {
        product = $swatches.closest('.woo-entry-inner');
    }
    self.product = product;
    product.on('click', '.viwcuf_product_change_qty', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let qty_input = jQuery(this).closest('.vi_wpvs_loop_action_qty').find('.viwcuf_product_qty');
        let val = parseFloat(qty_input.val()),
            min = parseFloat(qty_input.attr('min')),
            max = parseFloat(qty_input.attr('max')),
            step = parseFloat(qty_input.attr('step') || 1);
        if (jQuery(this).hasClass('viwcuf_product_plus')) {
            val += step;
        } else {
            val -= step;
        }
        if (min > 0 && min > val) {
            val = min;
        }
        if (max > 0 && val > max) {
            val = max;
        }
        qty_input.val(val).trigger('change');
    });
    product.on('click', '.vi_wpvs_loop_atc_button', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        if (jQuery(this).hasClass('vi_wpvs_loop_variation_no_pointer')) {
            return false;
        }
        product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
        let $thisbutton = jQuery(this), data = $swatches.serialize();
        if (product.find('.viwcuf_product_qty').length > 0) {
            let qty = parseInt(product.find('.viwcuf_product_qty').val() || 0),
                min_qty = parseInt(product.find('.viwcuf_product_qty').attr('min') || 1),
                max_qty = parseInt(product.find('.viwcuf_product_qty').attr('max') || 0);
            if (qty === 0) {
                return false;
            }
            if (qty < min_qty) {
                product.find('.viwcuf_product_qty_tooltip').removeClass('vi_wpvs_loop_variation_hidden').html(viwpvs_frontend_loop_product_params.less_min_qty + ' ' + min_qty + '.');
                setTimeout(function () {
                    product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
                }, 2000);
                return false;
            }
            if (max_qty > 0 && qty > max_qty) {
                product.find('.viwcuf_product_qty_tooltip').removeClass('vi_wpvs_loop_variation_hidden').html(viwpvs_frontend_loop_product_params.greater_max_qty + ' ' + max_qty + '.');
                setTimeout(function () {
                    product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
                }, 2000);
                return false;
            }
            data += '&quantity=' + qty;
        }
        jQuery.ajax({
            type: 'post',
            url: self.wc_ajax_url.toString().replace('%%endpoint%%', 'wpvs_add_to_cart'),
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass('added').addClass('loading');
            },
            complete: function (response) {
                $thisbutton.removeClass('loading').addClass('added');
            },
            success: function (response) {
                if (response.error) {
                    window.location = product.find('a:not(.added_to_cart)').first().attr('href');
                    return false;
                }
                // Redirect to cart option
                if (viwpvs_frontend_loop_product_params.cart_redirect_after_add === 'yes') {
                    window.location = viwpvs_frontend_loop_product_params.cart_url;
                    return false;
                }
                jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                if (!viwpvs_frontend_loop_product_params.woocommerce_enable_ajax_add_to_cart) {
                    jQuery(document.body).trigger("wc_fragment_refresh");
                }
                jQuery(document.body).trigger("update_checkout");
                jQuery(document.body).trigger("viwpvs_added_to_cart", [response, $thisbutton]);
                jQuery('#nm-menu-cart-btn').click();
            },
        });
    });
    $swatches.on('viwpvs_check_variations', {viwpvs_get_variations: self}, self.find_variation);
    $swatches.on('change', 'select', {viwpvs_get_variations: self}, self.onChange);
    $swatches.find('.vi-wpvs-variation-wrap-wrap-hasdefault').each(function (k, v) {
        v = jQuery(v);
        if (!v.find('select').val()) {
            v.find('select').val(v.data('selected'));
        }
    });
    $swatches.find('select').each(function () {
        let val = jQuery(this).val();
        attribute[jQuery(this).data('attribute_name')] = val;
    });
    self.current_attr = attribute;
    if (self.is_find_variation) {
        setTimeout(function () {
            $swatches.trigger('viwpvs_check_variations');
        }, 100);
    }
};
viwpvs_get_variations.prototype.onChange = function (event) {
    let self = event.data.viwpvs_get_variations;
    let swatches = self.swatches;
    self.img_product = self.get_img_product(swatches, true);
    swatches.find('input[name="variation_id"], input.variation_id').val('').trigger('change');
    self.current_attr[jQuery(this).data('attribute_name')] = jQuery(this).val() || '';
    if (self.is_ajax) {
        swatches.trigger('viwpvs_check_variations');
        if (!self.is_find_variation) {
            swatches.trigger('viwpvs_variation_select_change');
        }
    } else {
        swatches.trigger('woocommerce_variation_select_change');
        swatches.trigger('viwpvs_check_variations');
    }
    swatches.trigger('woocommerce_variation_has_changed');
};
viwpvs_get_variations.prototype.find_variation = function (event) {
    let self = event.data.viwpvs_get_variations, variation = null, is_stop = false;
    let attrs = self.current_attr,
        product = self.product,
        variations = self.variation,
        img_product = self.img_product,
        img_src = self.img_src;
    if (!self.is_find_variation) {
        is_stop = true;
    } else {
        jQuery.each(attrs, function (k, v) {
            if (!v) {
                is_stop = true;
                return false;
            }
        });
    }
    if (is_stop) {
        self.update_attributes(attrs, variations, product, self);
        self.show_variation(self, variation, product, img_product, img_src);
        return false;
    }
    if (self.is_ajax) {
        if (self.xhr) {
            self.xhr.abort();
        }
        if (variations) {
            jQuery.each(variations, function (key, value) {
                if (self.check_is_equal(attrs, value.attributes)) {
                    variation = value;
                    return false;
                }
            });
            if (variation) {
                self.show_variation(self, variation, product, img_product, img_src);
            } else {
                if (variations.length < parseInt(self.swatches.data('variation_count') || 0)) {
                    self.call_ajax(attrs, variations, product, self.swatches, self, img_product, img_src);
                } else {
                    self.show_variation(self, variation, product, img_product, img_src);
                }
            }
        } else {
            variations = [];
            self.call_ajax(attrs, variations, product, self.swatches, self, img_product, img_src);
        }
    } else {
        jQuery.each(variations, function (key, value) {
            if (self.check_is_equal(attrs, value.attributes)) {
                variation = value;
                return false;
            }
        });
        self.update_attributes(attrs, variations, product, self);
        self.show_variation(self, variation, product, img_product, img_src);
    }
};
viwpvs_get_variations.prototype.update_attributes = function (attrs, variations, product, self) {
    if (self.is_ajax || !self.is_find_variation) {
        return false;
    }
    product.find('select').each(function (k, v) {
        let current_select = jQuery(v);
        let current_name = current_select.data('attribute_name') || current_select.attr('name'),
            show_option_none = current_select.data('show_option_none'),
            current_val = current_select.val() || '',
            current_val_valid = true,
            new_select = jQuery('<select/>'),
            attached_options_count,
            option_gt_filter = ':gt(0)';

        // Reference options set at first.
        if (!current_select.data('attribute_html')) {
            let refSelect = current_select.clone();
            refSelect.find('option').removeAttr('disabled attached selected');
            // Legacy data attribute.
            current_select.data('attribute_options', refSelect.find('option' + option_gt_filter).get());
            current_select.data('attribute_html', refSelect.html());
        }

        new_select.html(current_select.data('attribute_html'));

        // The attribute of this select field should not be taken into account when calculating its matching variations:
        // The constraints of this attribute are shaped by the values of the other attributes.
        let checkAttributes = jQuery.extend(true, {}, attrs);
        checkAttributes[current_name] = '';
        let match_variations = [];
        for (let i = 0; i < variations.length; i++) {
            let match = variations[i];
            if (self.check_is_equal(checkAttributes, match.attributes)) {
                match_variations.push(match);
            }
        }
        // Loop through variations.
        for (let num in match_variations) {
            if (typeof (match_variations[num]) === 'undefined') {
                continue;
            }
            let variationAttributes = match_variations[num].attributes;

            for (let attr_name in variationAttributes) {
                if (!variationAttributes.hasOwnProperty(attr_name)) {
                    continue;
                }
                let attr_val = variationAttributes[attr_name],
                    variation_active = '';

                if (attr_name === current_name) {
                    if (match_variations[num].variation_is_active) {
                        variation_active = 'enabled';
                    }
                    if (attr_val) {
                        // Decode entities.
                        attr_val = jQuery('<div/>').html(attr_val).text();
                        // Attach to matching options by value. This is done to compare
                        // TEXT values rather than any HTML entities.
                        let $option_elements = new_select.find('option');
                        if ($option_elements.length) {
                            for (let i = 0, len = $option_elements.length; i < len; i++) {
                                let $option_element = jQuery($option_elements[i]);
                                let option_value = $option_element.val();

                                if (attr_val === option_value) {
                                    $option_element.addClass('attached ' + variation_active);
                                    break;
                                }
                            }
                        }
                    } else {
                        // Attach all apart from placeholder.
                        new_select.find('option:gt(0)').addClass('attached ' + variation_active);
                    }
                }

            }

        }
        // Count available options.
        attached_options_count = new_select.find('option.attached').length;
        // Check if current selection is in attached options.
        if (current_val) {
            current_val_valid = false;

            if (0 !== attached_options_count) {
                new_select.find('option.attached.enabled').each(function () {
                    var option_value = jQuery(this).val();

                    if (current_val === option_value) {
                        current_val_valid = true;
                        return false; // break.
                    }
                });
            }
        }
        // Detach the placeholder if:
        // - Valid options exist.
        // - The current selection is non-empty.
        // - The current selection is valid.
        // - Placeholders are not set to be permanently visible.
        if (attached_options_count > 0 && current_val && current_val_valid && ('no' === show_option_none)) {
            new_select.find('option:first').remove();
            option_gt_filter = '';
        }

        // Detach unattached.
        new_select.find('option' + option_gt_filter + ':not(.attached)').remove();

        // Finally, copy to DOM and set value.
        current_select.html(new_select.html());
        current_select.find('option' + option_gt_filter + ':not(.enabled)').prop('disabled', true);

        // Choose selected value.
        if (current_val) {
            // If the previously selected value is no longer available, fall back to the placeholder (it's going to be there).
            if (current_val_valid) {
                current_select.val(current_val);
            } else {
                current_select.val('').change();
            }
        } else {
            current_select.val(''); // No change event to prevent infinite loop.
        }
    });

    // Custom event for when variations have been updated.
    self.swatches.trigger('woocommerce_update_variation_values');
};
viwpvs_get_variations.prototype.call_ajax = function (attrs, variations, product, swatches, self, img_product, img_src) {
    attrs.product_id = self.product_id;
    self.xhr = jQuery.ajax({
        url: self.wc_ajax_url.toString().replace('%%endpoint%%', 'get_variation'),
        type: 'POST',
        data: attrs,
        beforeSend: function () {
            swatches.find('.vi_wpvs_loop_variation_form_loading').removeClass('vi_wpvs_loop_variation_form_loading_hidden').addClass('vi_wpvs_loop_variation_form_loading_visible');
        },
        success: function (result) {
            self.show_variation(self, result, product, img_product, img_src);
            if (result) {
                variations[variations.length || 0] = result;
            }
            delete attrs.product_id;
        },
        complete: function () {
            swatches.find('.vi_wpvs_loop_variation_form_loading').removeClass('vi_wpvs_loop_variation_form_loading_visible').addClass('vi_wpvs_loop_variation_form_loading_hidden');
        }
    });
};
viwpvs_get_variations.prototype.show_variation = function (self, variation, product, img_product, img_src) {
    if (variation) {
        let purchasable = false;
        if (img_product && jQuery(img_product).length && product.find('.vi_wpvs_loop_atc_button').length > 0) {
            setTimeout(function () {
                if (jQuery(img_product).parent().is('picture')) {
                    jQuery(img_product).parent().find('source').each(function (k, v) {
                        jQuery(v).attr({'src': variation.image.thumb_src, 'srcset': variation.image.thumb_src});
                    })
                }

                img_product.attr({'src': variation.image.thumb_src, 'srcset': variation.image.thumb_src});
                if (img_product.is('.show-on-hover')) {
                    jQuery(img_product).parent().find('img').not(img_product).each(function (k, v) {
                        jQuery(v).attr({'src': variation.image.thumb_src, 'srcset': variation.image.thumb_src});
                    })
                }
                let $has_hover_image = img_product.closest('.has-hover-image');
                if ($has_hover_image.length > 0) {
                    $has_hover_image.find('.hover-gallery-image').attr({
                        'src': variation.image.thumb_src,
                        'srcset': variation.image.thumb_src
                    })
                }
            }, 10)
        }
        if (variation.price_html) {
            product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').addClass('vi_wpvs_loop_variation_hidden');
            product.find('.vi_wpvs_loop_variation_price').removeClass('vi_wpvs_loop_variation_hidden').html(variation.price_html);
        }
        if (self.is_atc) {
            purchasable = true;
            if (!variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible) {
                purchasable = false;
            }
            if (purchasable) {
                self.set_add_to_cart(variation.variation_id, self.swatches);
                product.find('.add-to-cart-grid, .add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden vi_wpvs_loop_variation_no_pointer');
                product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_action .viwcuf_product_qty').attr({
                    min: variation.min_qty,
                    max: variation.max_qty
                });
            } else {
                self.set_add_to_cart('', self.swatches);
                product.find('.add-to-cart-grid, .add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
            }
        }
        self.swatches.trigger('viwpvs_show_variation', [variation, purchasable]);
    } else {
        // if (img_product && jQuery(img_product).length) {
        //     if (jQuery(img_product).parent().is('picture')) {
        //         jQuery(img_product).parent().find('source').each(function (k, v) {
        //             jQuery(v).attr({'srcset': img_src});
        //         });
        //     }
        //     img_product.attr({'src': img_src, 'srcset': img_src});
        // }
        product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').removeClass('vi_wpvs_loop_variation_hidden');
        product.find('.vi_wpvs_loop_variation_price').addClass('vi_wpvs_loop_variation_hidden');
        if (self.is_atc) {
            self.set_add_to_cart('', self.swatches);
            product.find('.add-to-cart-grid, .add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
            product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
            product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
        }
        jQuery(self.swatches).trigger('viwpvs_hide_variation');
    }
};
viwpvs_get_variations.prototype.set_add_to_cart = function (variation_id, swatches) {
    swatches.find('.variation_id').val(variation_id);
};
viwpvs_get_variations.prototype.check_is_equal = function (attrs1, attrs2) {
    let aProps = Object.getOwnPropertyNames(attrs1),
        bProps = Object.getOwnPropertyNames(attrs2);
    if (aProps.length !== bProps.length) {
        return false;
    }
    for (let i = 0; i < aProps.length; i++) {
        let attr_name = aProps[i];
        let val1 = attrs1[attr_name];
        let val2 = attrs2[attr_name];
        if (val1 !== undefined && val2 !== undefined && val1 !== null && val2 !== null && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
            return false;
        }
    }
    return true;
};
viwpvs_get_variations.prototype.get_img_product = function ($swatches, hover = false) {
    let product, img_product;
    if (viwpvs_frontend_loop_product_params.theme === 'uncode') {//Uncode
        product = $swatches.closest('.tmb-woocommerce');
    } else {
        product = $swatches.closest('.product');
    }
    if (!product.length) {//Authentic
        product = $swatches.closest('.item-product');
    }
    if (!product.length) {//Milano
        product = $swatches.closest('.product-item');
    }
    if (!product.length) {//Infinite
        product = $swatches.closest('.gdlr-core-item-list');
    }
    if (!product.length) {//Zella
        product = $swatches.closest('.product-warp-item');
    }
    if (!product.length) {//JetWoo Widgets For Elementor
        product = $swatches.closest('.jet-woo-products__item');
    }
    if (!product.length) {//Nutchup theme
        product = $swatches.closest('.product__box');
    }
    if ($swatches.closest('.woo-entry-inner').length) {//OceanWP slick slide
        product = $swatches.closest('.woo-entry-inner');
    }
    if (hover && window.outerWidth > 549 && product.find('img.show-on-hover').length) {//flatsome theme
        img_product = product.find('img.show-on-hover').first();
    } else if (product.find('.slick-current.slick-active').length) {//OceanWP slick slide
        img_product = product.find('.slick-current.slick-active').first();
    } else if (product.find('.attachment-shop_catalog').length) {//Zella ,Skudmart 1.0.6
        img_product = product.find('.attachment-shop_catalog').first();
    } else if (product.find('.gdlr-core-product-thumbnail').length) {//Infinite
        img_product = product.find('.gdlr-core-product-thumbnail').first();
    } else if (product.find('.woo-entry-image-main.lazyloaded').length) {//ocean
        img_product = product.find('.woo-entry-image-main.lazyloaded').first();
    } else if (product.find('.woo-entry-image-main').length) {//ocean
        img_product = product.find('.woo-entry-image-main').first();
    } else if (product.find('.wp-post-image.vi-load').length) { //demo
        img_product = product.find('.wp-post-image.vi-load ').first();
    } else if (product.find('.attachment-woocommerce_thumbnail').length) {
        img_product = product.find('.attachment-woocommerce_thumbnail').first();
    } else if (product.find('.primary-thumb img').length) {
        img_product = product.find('.primary-thumb img').first();
    } else if (product.find('.wp-post-image').length) {
        img_product = product.find('.wp-post-image').first();
    }
    if (!img_product) {
        if (product && product.find('img')) {
            let $preview_thumb = product.find('.preview-thumb img');
            if ($preview_thumb.length > 0) {
                img_product = $preview_thumb;
            } else {
                img_product = product.find('img').first();
            }
        } else {
            img_product = false;
        }
    }
    if (img_product && img_product.find('img').length > 0) {
        img_product = img_product.find('img').first();
    }

    return img_product;
};
jQuery.fn.wpvs_get_variation = function () {
    new viwpvs_get_variations(this);
    return this;
};