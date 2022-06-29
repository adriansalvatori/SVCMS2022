jQuery(window).on('elementor/frontend/init', () => {
    'use strict';
    viwpvs_frontend_init();
});
jQuery(document).ready(function ($) {
    'use strict';
    jQuery(document).on('click', '.vi-wpvs-variation-style', function (e) {
        jQuery('.vi-wpvs-variation-wrap-option-available').remove();
        jQuery('.vi-wpvs-variation-wrap-option.vi-wpvs-variation-wrap-option-show').removeClass('vi-wpvs-variation-wrap-option-show');
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    jQuery(document).on('click', 'body', function (e) {
        jQuery('.vi-wpvs-variation-wrap-option-available').remove();
        jQuery('.vi-wpvs-variation-wrap-option.vi-wpvs-variation-wrap-option-show').removeClass('vi-wpvs-variation-wrap-option-show');
    });
    jQuery(document).on('click', '.vi-wpvs-variation-wrap-option-available .vi-wpvs-option-wrap', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        if (jQuery(this).hasClass('vi-wpvs-option-wrap-disable')) {
            return false;
        }
        let current_index = jQuery('.vi-wpvs-variation-wrap-option-available .vi-wpvs-option-wrap').index(jQuery(this));
        jQuery('.vi-wpvs-variation-wrap-option.vi-wpvs-variation-wrap-option-show').removeClass('vi-wpvs-variation-wrap-option-show').find('.vi-wpvs-option-wrap').eq(current_index).trigger('click');
        jQuery('.vi-wpvs-variation-wrap-option-available').remove();
    });
    jQuery(document).on('mouseenter', '.vi-wpvs-variation-wrap-option-available .vi-wpvs-option-wrap', function (e) {
        if (!jQuery(this).hasClass('vi-wpvs-option-wrap-selected') && !jQuery(this).hasClass('vi-wpvs-option-wrap-disable') && !jQuery(this).hasClass('vi-wpvs-product-link')) {
            jQuery(this).removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-hover');
        }
    });
    jQuery(document).on('mouseleave', '.vi-wpvs-variation-wrap-option-available .vi-wpvs-option-wrap', function (e) {
        if (!jQuery(this).hasClass('vi-wpvs-option-wrap-selected') && !jQuery(this).hasClass('vi-wpvs-option-wrap-disable')) {
            jQuery(this).removeClass('vi-wpvs-option-wrap-hover').addClass('vi-wpvs-option-wrap-default');
        }
    });
    jQuery(document.body).on('vi_wpvs_variation_form', function () {
        jQuery('.vi_wpvs_variation_form:not(.vi_wpvs_variation_form_init)').each(function () {
            jQuery(this).addClass('vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();
        });
    });
    viwpvs_frontend_init();
    jQuery(document).on('woodmart-quick-view-displayed', function (event, jqxhr, settings) {
        viwpvs_frontend_init();
    });
    jQuery(document).on('ajaxComplete', function (event, jqxhr, settings) {
        viwpvs_frontend_init();
        return false;
    });
});

function viwpvs_frontend_init() {
    jQuery('.vi_wpvs_variation_form:not(.vi_wpvs_variation_form_init)').each(function () {
        jQuery(this).addClass('vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();
    });
    jQuery('.variations_form:not(.vi_wpvs_variation_form),.variations_form:not(.vi_wpvs_variation_form_init)').each(function () {
        jQuery(this).addClass('vi_wpvs_variation_form vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();
    });
}

jQuery(window).on('load', function () {
    'use strict';
    viwpvs_frontend_init();
});
let viwpvs_frontend = function ($form) {
    this.form = $form;
    this.variationData = $form.data('product_variations');
    this.swatch_image_src = '';
    this.swatch_image_data = '';
    this.init();
};

viwpvs_frontend.prototype.init = function () {
    let viwpvs_frontend = this,
        form = this.form,
        variations = this.variationData;
    if (variations && (!form.hasClass('vi_wpvs_loop_variation_form') || (form.hasClass('vi_wpvs_loop_variation_form') && form.data('vpvs_find_variation'))) && form.find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected').length === form.find('.vi-wpvs-select-attribute select option:selected').length) {
        form.addClass('vi_wpvs_variation_form_has_selected');
        form.on('hide_variation viwpvs_hide_variation', function (e) {
            if (form.hasClass('vi_wpvs_variation_form_has_selected') && form.find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected').length === form.find('.vi-wpvs-select-attribute select option:selected').length) {
                form.removeClass('vi_wpvs_variation_form_has_selected');
                viwpvs_frontend.hide_variation();
            }
        });
    }

    /*Change attribute select after not found exists variation*/
    if (!form.find('.vi-wpvs-variation-wrap-wrap').data('hide_outofstock')) {
        form.on('hide_variation viwpvs_hide_variation', function (e) {
            form.find('.vi-wpvs-variation-wrap-wrap').each(function (k, v) {
                let select_val = jQuery(v).find('select').val(),
                    select_swatches = jQuery(v).find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected').data('attribute_value');
                if (select_swatches && !select_val && jQuery(v).find('select option[value="' + select_swatches + '"]').length) {
                    jQuery(v).find('select').val(select_swatches).trigger('change');
                }
            });
        });
    }
    form.on('woocommerce_update_variation_values', function () {
        viwpvs_frontend.select_variation_item();
    });
    /*Change product image according to attribute image*/
    form.on('check_variations', function (event) {
        setTimeout(function () {
            if (event.data !== undefined) {
                if (event.data.variationForm !== undefined) {
                    let getChosenAttributes = event.data.variationForm.getChosenAttributes();
                    if (event.data.variationForm.useAjax) {
                        if (getChosenAttributes.count > 0) {
                            if (getChosenAttributes.chosenCount > 0) {
                                for (let i in getChosenAttributes.data) {
                                    if (getChosenAttributes.data.hasOwnProperty(i)) {
                                        if (getChosenAttributes.data[i]) {
                                            let $v_wrap = form.find(`.vi-wpvs-variation-wrap-wrap[data-wpvs_attribute_name="${i}"]`);
                                            change_image_by_attribute($v_wrap.find('.vi-wpvs-option-wrap-selected'));
                                            viwpvs_show_selected_item($v_wrap)
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }, 10)
    });
    form.on('viwpvs_variation_select_change', function (event) {
        // console.log(2)
        setTimeout(function () {
            let $selected = form.find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected');
            if ($selected.length > 0) {
                $selected.each(function (k, item) {

                    change_image_by_attribute(jQuery(item));
                })
            }
        }, 10)
    });
    /*Do not change to attribute images if a variation has image*/
    let change_image_timeout;
    form.on('found_variation', function (event, variation) {
        let variation_has_image = true;
        if (variation && variation.hasOwnProperty('viwpvs_no_image') && variation['viwpvs_no_image']) {
            variation_has_image = false;
        }
        if (variation_has_image && change_image_timeout) {
            clearTimeout(change_image_timeout);
        }
    });
    form.on('woocommerce_variation_select_change', function () {
        let $selected = form.find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected');
        if ($selected.length > 0) {
            $selected.each(function (k, item) {

                change_image_by_attribute(jQuery(item));
            })
        }
    });

    function change_image_by_attribute($current) {
        if ($current.data('change_product_image') === 'variation_image' || $current.data('change_product_image') === 'attribute_image') {
            if (form.is('.vi_wpvs_loop_variation_form')) {
                if ($current.data('loop_source')) {
                    let swatch_image_src = $current.data('loop_source'), srcset = swatch_image_src;
                    if ($current.data('loop_data') && $current.data('loop_data').hasOwnProperty('srcset') && $current.data('loop_data')['srcset']) {
                        srcset = $current.data('loop_data')['srcset'];
                    }
                    let $image = viwpvs_get_variations.prototype.get_img_product(form, true);
                    if ($image) {
                        if (jQuery($image).parent().is('picture')) {
                            jQuery($image).parent().find('source').each(function (k, v) {
                                jQuery(v).attr({'srcset': srcset});
                            })
                        }
                        setTimeout(function () {
                            $image.attr({
                                'src': swatch_image_src,
                                'srcset': srcset
                            });
                        }, 1);
                        return false;
                    }
                }
            } else if (typeof form.wc_variations_image_update === 'function' && $current.data('loop_data')) {
                if (change_image_timeout) {
                    clearTimeout(change_image_timeout);
                }
                change_image_timeout = setTimeout(function () {
                    form.wc_variations_image_update({image: $current.data('loop_data')});
                    if ('undefined' !== typeof woodmartThemeModule) {
                        woodmartThemeModule.$document.trigger('wdShowVariationNotQuickView');
                    }
                    form.trigger('viwpvs_main_image_changed_by_attribute', [$current]);
                }, 10);
                return false;
            }
        }
    }

    viwpvs_frontend.design_variation_item();
    // viwpvs_frontend.select_variation_item();
    if (form.find('.vi-wpvs-variation-wrap-select-wrap').length) {
        form.find('.vi-wpvs-variation-wrap-select-wrap').each(function (k, item) {
            let $item = jQuery(item), $item_wrap = $item.closest('.vi-wpvs-variation-wrap');
            $item.parent().parent().parent().css({width: '100%'});
            let select_wrap, select_button;
            select_wrap = $item.find('.vi-wpvs-variation-wrap-option');
            if (!select_wrap.attr('data-offset_height')) {
                select_wrap.attr('data-offset_height', select_wrap.outerHeight()).removeClass('vi-wpvs-select-hidden').addClass('vi-wpvs-hidden');
            }

            select_button = $item.find('.vi-wpvs-variation-button-select');
            if (select_wrap.find('.vi-wpvs-option-wrap-selected').length) {
                select_button.find('span').html(select_wrap.find('.vi-wpvs-option-wrap-selected .vi-wpvs-option-select').html());
            }
            select_button.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (select_wrap.hasClass('vi-wpvs-variation-wrap-option-show')) {
                    jQuery('.vi-wpvs-variation-wrap-option-available').remove();
                    select_wrap.removeClass('vi-wpvs-variation-wrap-option-show')
                } else {
                    let select_wrap_height, scroll_top, window_height, view_able_offset;
                    select_wrap_height = parseFloat(select_wrap.attr('data-offset_height'));
                    scroll_top = jQuery(window).scrollTop();
                    window_height = jQuery(window).outerHeight();
                    view_able_offset = jQuery(this).offset().top - scroll_top;
                    jQuery('.vi-wpvs-variation-wrap-option.vi-wpvs-variation-wrap-option-show').removeClass('vi-wpvs-variation-wrap-option-show');
                    select_wrap.addClass('vi-wpvs-variation-wrap-option-show');
                    jQuery('.vi-wpvs-variation-wrap-option-available').remove();
                    let new_select = $item_wrap.clone();
                    new_select.find('.vi-wpvs-variation-button-select').remove();
                    new_select.find('.vi-wpvs-variation-wrap-option').removeClass('vi-wpvs-hidden vi-wpvs-variation-wrap-option-show');
                    new_select.addClass('vi-wpvs-variation-wrap-option-available').css({
                        width: jQuery(this).outerWidth(),
                        left: jQuery(this).offset().left
                    });
                    if (scroll_top > view_able_offset || scroll_top < select_wrap_height || window_height > (view_able_offset + select_wrap_height + 40)) {
                        new_select.toggleClass('vi-wpvs-variation-wrap-select-bottom');
                        new_select.css({top: (jQuery(this).offset().top + jQuery(this).outerHeight())});
                    } else {
                        new_select.toggleClass('vi-wpvs-variation-wrap-select-top');
                        new_select.css({top: (jQuery(this).offset().top - select_wrap.outerHeight())});
                    }
                    jQuery('body').append(new_select);
                }
            });
        });
    }
    viwpvs_frontend.design_variation_slider(form);
    form.find('.vi-wpvs-option-wrap').each(function (k, item) {
        let attr_div, attr_select, attr_value, val, swatch_type;
        attr_div = jQuery(item).closest('.vi-wpvs-variation-wrap-wrap');
        attr_select = attr_div.find('select');
        swatch_type = attr_div.data('swatch_type');
        attr_select.find('option').removeClass('vi-wpvs-option-disabled');
        jQuery(item).on('mouseenter', function () {
            if (!jQuery(this).hasClass('vi-wpvs-option-wrap-selected') && !jQuery(this).hasClass('vi-wpvs-option-wrap-disable') && !jQuery(this).hasClass('vi-wpvs-product-link')) {
                jQuery(this).removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-hover');
            }
        }).on('mouseleave', function () {
            if (!jQuery(this).hasClass('vi-wpvs-option-wrap-selected') && !jQuery(this).hasClass('vi-wpvs-option-wrap-disable')) {
                jQuery(this).removeClass('vi-wpvs-option-wrap-hover').addClass('vi-wpvs-option-wrap-default');
            }
        }).on('click', function (e) {
            viwpvs_frontend.swatch_image_data = '';
            viwpvs_frontend.swatch_image_src = '';
            let $current = jQuery(this);
            e.stopPropagation();
            if ($current.is('.vi-wpvs-product-link')) {
                location.href = $current.data('product_link');
                return false;
            }
            if ($current.hasClass('vi-wpvs-option-wrap-disable')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            if (!$current.parent().hasClass('vi-wpvs-variation-wrap-radio')) {
                e.preventDefault();
                e.stopPropagation();
            }
            jQuery('.vi-wpvs-variation-wrap-option').addClass('vi-wpvs-hidden');
            form.find('.reset_variations').removeClass('vi-wpvs-hidden');
            attr_div.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-selected vi-wpvs-option-wrap-hover').addClass('vi-wpvs-option-wrap-default');
            if (attr_div.find('.vi-wpvs-variation-wrap').hasClass('vi-wpvs-variation-wrap-select')) {
                attr_div.find('.vi-wpvs-variation-button-select >span ').html($current.find('.vi-wpvs-option-select').html());
            }
            if ($current.find('.vi-wpvs-option-radio').length > 0) {
                attr_div.find('.vi-wpvs-option-radio').prop('checked', false);
                $current.find('.vi-wpvs-option-radio').prop('checked', true);
                $current.removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
            }
            attr_value = viwpvs_to_string(attr_select.val());
            val = viwpvs_to_string($current.data('attribute_value'));
            if (val !== attr_value) {
                $current.removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
                attr_select.val(val).trigger('change');
            } else if (!$current.parent().hasClass('vi-wpvs-variation-wrap-radio')) {
                if (form.hasClass('vi_wpvs_loop_variation_form')) {
                    if (form.data('wpvs_double_click')) {
                        attr_select.val('').trigger('change');
                    } else {
                        $current.removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
                    }
                } else {
                    if (attr_div.data('wpvs_double_click')) {
                        attr_select.val('').trigger('change');
                    } else {
                        $current.removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
                    }
                }
            }
            e.stopPropagation();
        });
    });
    form.find('select:not(.vi-wpvs-select-attribute):not(.vi-wpvs-variation-style-select)').on('change', function () {
        setTimeout(function () {
            viwpvs_frontend.select_variation_item();
        }, 500);
    });
    form.on('click', '.reset_variations', function () {
        viwpvs_frontend.select_variation_item();
        viwpvs_frontend.hide_variation();
    });
    jQuery(document).trigger('viwpvs_init_swatches', [form]);
};

viwpvs_frontend.prototype.design_variation_item = function () {
    let form = this.form;
    form.find('.vi-wpvs-variation-wrap-wrap').each(function () {
        let $wrap = jQuery(this), variation_wrap = $wrap.parent().parent();
        $wrap.parent().addClass('vi-wpvs-variation-style-content');
        $wrap.find(`div.vi-wpvs-select-attribute select[data-attribute_name="${$wrap.data('wpvs_attribute_name')}"]`).addClass('vi-wpvs-select-attribute');
        variation_wrap.addClass($wrap.data('display_type'));
        if (!$wrap.data('wpvs_attr_title')) {
            variation_wrap.find('.label').addClass('vi-wpvs-hidden');
        }
    });
    form.find('.vi-wpvs-option.vi-wpvs-option-color').each(function (color_item_k, color_item) {
        let colors = jQuery(color_item).data('option_color');
        jQuery(color_item).css({background: colors});
    });
    form.find('.vi-wpvs-variation-wrap-wrap').removeClass('vi-wpvs-hidden');
};
viwpvs_frontend.prototype.design_variation_slider = function (swatches) {
    let slider_wrap = swatches.find('.vi-wpvs-variation-wrap-slider').closest('.vi-wpvs-variation-wrap-wrap');
    if (slider_wrap.length === 0) {
        return false;
    }
    slider_wrap.each(function (k, v) {
        let max_item, wrap_width = jQuery(swatches).innerWidth(),
            disable_type = jQuery(v).find('.vi-wpvs-variation-wrap').data('out_of_stock'),
            item_width = jQuery(v).find('.vi-wpvs-option-wrap').first().innerWidth(),
            item_count = jQuery(v).find('.vi-wpvs-option-wrap').length,
            item_start = jQuery(v).find('.vi-wpvs-option-wrap.vi-wpvs-option-wrap-selected').index();
        item_start = item_start > 0 ? item_start : 0;
        if (jQuery(v).closest('.vi-wpvs-variation-style').hasClass('vi-wpvs-variation-style-horizontal') && jQuery(v).closest('.vi-wpvs-variation-style').hasClass('vi_wpvs_loop_variation_attr_name_enable')) {
            wrap_width = wrap_width * 80 / 100;
        }
        max_item = Math.floor(wrap_width / (item_width + 10));
        if (item_count > max_item) {
            if (disable_type.search('blur') === -1) {
                jQuery(v).flexslider({
                    namespace: 'vi-wpvs-slider-',
                    selector: '.vi-wpvs-variation-wrap-slider .vi-wpvs-option-wrap',
                    animation: 'slide',
                    animationLoop: false,
                    startAt: item_start,
                    itemWidth: item_width,
                    itemMargin: 5,
                    controlNav: false,
                    maxItems: max_item,
                    move: 1,
                    slideshow: false,
                    reverse: false,
                    touch: true,
                    start: function (slider) {
                        slider.last = item_count - this.maxItems;
                        slider.limit = (this.itemWidth + 10) * slider.last + 10;
                        slider.animatingTo = slider.currentSlide > slider.last ? 0 : slider.animatingTo;
                    },
                    before: function (slider) {
                        item_count = jQuery(v).find('.vi-wpvs-option-wrap:not(.vi-wpvs-option-wrap-disable)').length;
                        if (item_count > this.maxItems) {
                            slider.last = item_count - this.maxItems;
                            slider.limit = (this.itemWidth + 10) * slider.last + 10;
                            slider.animatingTo = slider.currentSlide > slider.last ? 0 : slider.animatingTo;
                        } else {
                            slider.animatingTo = 0;
                        }
                    }
                });
            } else {
                jQuery(v).flexslider({
                    namespace: 'vi-wpvs-slider-',
                    selector: '.vi-wpvs-variation-wrap-slider .vi-wpvs-option-wrap',
                    animation: 'slide',
                    animationLoop: false,
                    startAt: item_start,
                    itemWidth: item_width,
                    itemMargin: 5,
                    controlNav: false,
                    maxItems: max_item,
                    move: 1,
                    slideshow: false,
                    reverse: false,
                    touch: true,
                    start: function (slider) {
                        slider.last = item_count - this.maxItems;
                        slider.limit = (this.itemWidth + 10) * slider.last + 10;
                        slider.animatingTo = slider.currentSlide > slider.last ? 0 : slider.animatingTo;
                    },
                });
            }
        }
    });
};
viwpvs_frontend.prototype.select_variation_item = function () {
    let form = this.form;
    let product_variations = this.variationData;
    form.find('.vi-wpvs-label-selected').addClass('vi-wpvs-hidden');
    form.find('.vi-wpvs-option-wrap-out-of-stock').removeClass('vi-wpvs-option-wrap-out-of-stock');
    form.find('.vi-wpvs-option-wrap-backorders').removeClass('vi-wpvs-option-wrap-backorders');
    form.find('.vi-wpvs-variation-wrap-wrap').each(function (k, v) {
        let $wrap = jQuery(this);
        if ($wrap.data('blur_out_backorders')) {
            let $current_select = $wrap.data('swatch_type') === 'viwpvs_default' ? $wrap.find(`select[name="${$wrap.data('wpvs_attribute_name')}"]`) : $wrap.find('select.vi-wpvs-select-attribute');
            let attribute_name = viwpvs_to_string($current_select.data('attribute_name'));
            let attribute_value = $current_select.val();
            if (!$wrap.hasClass('vi-wpvs-option-wrap-backorders-attribute-checked')) {
                let $container = $wrap.find(`.vi-wpvs-variation-wrap`);
                $container.find('.vi-wpvs-option-wrap').each(function () {
                    let $option = jQuery(this), attr_value = viwpvs_to_string($option.data('attribute_value'));
                    if (attr_value) {
                        let v_count = 0,
                            v_out = 0;
                        for (let product_variation_k in product_variations) {
                            if (product_variations.hasOwnProperty(product_variation_k)) {
                                let product_variation = product_variations[product_variation_k];
                                if (product_variation['attributes'][attribute_name] === '') {
                                    v_count++;
                                    if (product_variation.hasOwnProperty('viwpvs_on_backorders')) {
                                        v_out++;
                                    }
                                } else {
                                    if (product_variation['attributes'] !== null && product_variation['attributes'] !== undefined) {
                                        if (viwpvs_to_string(product_variation['attributes'][attribute_name]) === attr_value) {
                                            v_count++;
                                            if (product_variation.hasOwnProperty('viwpvs_on_backorders')) {
                                                v_out++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if (v_count === v_out) {
                            $option.addClass('vi-wpvs-option-wrap-backorders-attribute');
                        }
                    }
                });
                $wrap.addClass('vi-wpvs-option-wrap-backorders-attribute-checked');
            }
            if (attribute_value) {
                for (let product_variation_k in product_variations) {
                    if (product_variations.hasOwnProperty(product_variation_k)) {
                        let product_variation = product_variations[product_variation_k];
                        if (product_variation['attributes'][attribute_name] === attribute_value && product_variation.hasOwnProperty('viwpvs_on_backorders')) {
                            for (let attr_name in product_variation['attributes']) {
                                let attr_value = product_variation['attributes'][attr_name];
                                if (attr_name !== attribute_name) {
                                    let $container = form.find(`.vi-wpvs-variation-wrap[data-attribute="${attr_name}"]`);
                                    $container.find('.vi-wpvs-option-wrap').each(function () {
                                        let $current_option = jQuery(this);
                                        if (!$current_option.hasClass('vi-wpvs-option-wrap-backorders-attribute') && !$current_option.hasClass('vi-wpvs-option-wrap-backorders') && viwpvs_to_string($current_option.data('attribute_value')) === attr_value) {
                                            let maybe_outofstock = true;
                                            for (let product_variation_k in product_variations) {
                                                if (product_variations.hasOwnProperty(product_variation_k)) {
                                                    let product_variation = product_variations[product_variation_k];

                                                    if (product_variation['attributes'][attribute_name] === attribute_value && product_variation['attributes'][attr_name] === viwpvs_to_string($current_option.data('attribute_value')) && !product_variation.hasOwnProperty('viwpvs_on_backorders')) {
                                                        maybe_outofstock = false;
                                                        break;
                                                    }
                                                }
                                            }
                                            if (maybe_outofstock) {
                                                $current_option.addClass('vi-wpvs-option-wrap-backorders');
                                            }
                                            return false;
                                        }
                                    });
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($wrap.data('hide_outofstock')) {
            let attrs_value = jQuery(v).find('select option:not(.vi-wpvs-option-disabled)').map(function () {
                return jQuery(this).val();
            });
            jQuery(v).find('.vi-wpvs-option-wrap:not(.vi-wpvs-product-link)').each(function (option_item_k, option_item) {
                let val = viwpvs_to_string(jQuery(option_item).data('attribute_value'));
                if (jQuery.inArray(val, attrs_value) > -1) {
                    jQuery(option_item).removeClass('vi-wpvs-option-wrap-disable');
                } else {
                    jQuery(option_item).removeClass('vi-wpvs-option-wrap-selected').addClass('vi-wpvs-option-wrap-default vi-wpvs-option-wrap-disable');
                }
            });
        } else {
            let attrs_value = jQuery(v).find('select option:not(.vi-wpvs-option-disabled)').map(function () {
                return jQuery(this).val();
            });
            jQuery(v).find('.vi-wpvs-option-wrap:not(.vi-wpvs-product-link)').each(function (option_item_k, option_item) {
                let val = viwpvs_to_string(jQuery(option_item).data('attribute_value'));
                if (jQuery.inArray(val, attrs_value) > -1) {
                    jQuery(option_item).removeClass('vi-wpvs-hidden');
                } else {
                    jQuery(option_item).removeClass('vi-wpvs-option-wrap-selected').addClass('vi-wpvs-option-wrap-default vi-wpvs-hidden');
                }
            });
            if (product_variations) {
                let $current_select = $wrap.data('swatch_type') === 'viwpvs_default' ? $wrap.find(`select[name="${$wrap.data('wpvs_attribute_name')}"]`) : $wrap.find('select.vi-wpvs-select-attribute');
                let attribute_name = viwpvs_to_string($current_select.data('attribute_name'));
                let attribute_value = $current_select.val();
                if (!$wrap.hasClass('vi-wpvs-option-wrap-out-of-stock-attribute-checked')) {
                    let $container = $wrap.find(`.vi-wpvs-variation-wrap`);
                    $container.find('.vi-wpvs-option-wrap').each(function () {
                        let $option = jQuery(this), attr_value = viwpvs_to_string($option.data('attribute_value'));
                        if (attr_value) {
                            let v_count = 0,
                                v_out = 0;
                            for (let product_variation_k in product_variations) {
                                if (product_variations.hasOwnProperty(product_variation_k)) {
                                    let product_variation = product_variations[product_variation_k];
                                    if (product_variation['attributes'] !== null && product_variation['attributes'] !== undefined) {
                                        if (product_variation['attributes'][attribute_name] === '') {
                                            v_count++;
                                            if ((product_variation.hasOwnProperty('is_purchasable') && !product_variation.is_purchasable) || (product_variation.hasOwnProperty('is_in_stock') && !product_variation.is_in_stock) || product_variation.hasOwnProperty('viwpvs_not_available')) {
                                                v_out++;
                                            }
                                        } else if (viwpvs_to_string(product_variation['attributes'][attribute_name]) === attr_value) {
                                            v_count++;
                                            if (product_variation.hasOwnProperty('viwpvs_not_available')) {
                                                v_out++;
                                            }
                                        }
                                    }
                                }
                            }

                            if (v_count === v_out) {
                                $option.addClass('vi-wpvs-option-wrap-out-of-stock-attribute');
                            }
                        }
                    });
                    $wrap.addClass('vi-wpvs-option-wrap-out-of-stock-attribute-checked');
                }
                if (attribute_value) {
                    for (let product_variation_k in product_variations) {
                        if (product_variations.hasOwnProperty(product_variation_k)) {
                            let product_variation = product_variations[product_variation_k];
                            if (product_variation['attributes'][attribute_name] === attribute_value && product_variation.hasOwnProperty('viwpvs_not_available')) {
                                for (let attr_name in product_variation['attributes']) {
                                    let attr_value = product_variation['attributes'][attr_name];
                                    if (attr_name !== attribute_name) {
                                        let $container = form.find(`.vi-wpvs-variation-wrap[data-attribute="${attr_name}"]`);
                                        $container.find('.vi-wpvs-option-wrap:not(.vi-wpvs-hidden)').each(function () {
                                            let $current_option = jQuery(this);
                                            if (!$current_option.hasClass('vi-wpvs-option-wrap-out-of-stock-attribute') && !$current_option.hasClass('vi-wpvs-option-wrap-out-of-stock')) {
                                                if (viwpvs_to_string($current_option.data('attribute_value')) === attr_value) {
                                                    let maybe_outofstock = true;
                                                    for (let product_variation_k in product_variations) {
                                                        if (product_variations.hasOwnProperty(product_variation_k)) {
                                                            let product_variation = product_variations[product_variation_k];
                                                            // console.log(`["${attribute_name}"=>"${attr_value}","${attr_name}"=>"${$current_option.data('attribute_value')}"]`);
                                                            if (product_variation['attributes'][attribute_name] === attribute_value && product_variation['attributes'][attr_name] === viwpvs_to_string($current_option.data('attribute_value')) && !product_variation.hasOwnProperty('viwpvs_not_available')) {
                                                                maybe_outofstock = false;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if (maybe_outofstock) {
                                                        $current_option.addClass('vi-wpvs-option-wrap-out-of-stock');
                                                    }
                                                    return false;
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        viwpvs_show_selected_item(jQuery(v));
    });
};
viwpvs_frontend.prototype.hide_variation = function () {
    let form = this.form;
    form.find('.reset_variations').addClass('vi-wpvs-hidden');
    form.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-selected vi-wpvs-option-wrap-out-of-stock').addClass('vi-wpvs-option-wrap-default');
    form.find('.vi-wpvs-option-radio').prop('checked', false);
    form.find('.vi-wpvs-variation-wrap-option').addClass('vi-wpvs-hidden');
    form.find('.vi-wpvs-variation-button-select >span ').html(form.find('.vi-wpvs-option-select:first-child').html());
    form.find('.vi-wpvs-variation-style .vi-wpvs-label-selected').addClass('vi-wpvs-hidden');
};
jQuery.fn.viwpvs_woo_product_variation_swatches = function () {
    new viwpvs_frontend(this);
    return this;
};

function viwpvs_to_string(str) {
    if (str === undefined || str === null) {
        str = '';
    } else {
        str = str.toString();
    }
    return str;
}

function viwpvs_show_selected_item($v_wrap) {
    if ($v_wrap.length > 0 && $v_wrap.data('show_selected_item') && $v_wrap.find('.vi-wpvs-option-wrap-selected').length) {
        let $label = $v_wrap.parent().parent().find('.vi-wpvs-label-selected');
        if ($label.length) {
            $label.html($v_wrap.find('.vi-wpvs-option-wrap-selected').data('attribute_label')).removeClass('vi-wpvs-hidden');
        } else {
            let $append_wrap = $v_wrap.parent().parent().find('.label');
            if (!$append_wrap.length) {
                $append_wrap = $v_wrap.parent().parent().find('label');
            }
            if ($append_wrap.length) {
                $append_wrap.css({
                    display: 'inline-flex',
                    flexWrap: 'wrap',
                    alignItems: 'center'
                })
                    .append('<span class="vi-wpvs-label-selected">' + $v_wrap.find('.vi-wpvs-option-wrap-selected').data('attribute_label') + '</span>');
            }
        }
    }
}
