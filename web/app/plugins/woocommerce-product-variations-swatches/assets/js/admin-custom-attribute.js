jQuery(document).ready(function ($) {
    'use strict';
    $('.product_attributes.vi-wpvs-attribute-wrap-wrap').find('.woocommerce_attribute:not(.vi-wpvs-attribute-wrap)').remove();
    $('.product_attributes:not(.vi-wpvs-attribute-wrap-wrap)').remove();
    $('#product_attributes > .toolbar-top').append('<button class="button primary vi_wpvs_global_setting_url"><a href="' + viwpvs_admin_custom_attribute.global_setting_url + '" target="_blank">' + viwpvs_admin_custom_attribute.global_setting_title + '</a></button>');

    $('.vi-wpvs-taxonomy-add-new-term').select2({
        closeOnSelect: false
    });
    var viwpvs_custom_attribute_t = viwpvs_custom_attribute;
    viwpvs_custom_attribute_t.init();
    $(document).ajaxComplete(function (event, jqxhr, settings) {
        let data = settings.data;
        if (data && (data.search('woocommerce_add_attribute') !== -1 || data.search('woocommerce_load_variations') !== -1 || data.search('woocommerce_save_attributes') !== -1)) {
            viwpvs_custom_attribute_t.init();
            $('.vi-wpvs-taxonomy-add-new-term').select2({
                closeOnSelect: false
            });
        }
    });
    // Add a new attribute (via ajax).
    $('.product_attributes').on('click', '.vi-wpvs-attribute-taxonomy-create', function () {

        $('.product_attributes').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        let $button = $(this);
        let $wrapper = $button.closest('.woocommerce_attribute');
        let attribute = $wrapper.data('taxonomy');
        let new_attribute_name = window.prompt(woocommerce_admin_meta_boxes.new_attribute_prompt);

        if (new_attribute_name) {
            let data = {
                action: 'woocommerce_add_new_attribute',
                taxonomy: attribute,
                term: new_attribute_name,
                security: woocommerce_admin_meta_boxes.add_attribute_nonce
            };
            let attribute_wrap = $button.closest('.vi-wpvs-attribute-value-wrap-wrap');
            $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
                if (response.error) {
                    // Error.
                    window.alert(response.error);
                    $('.product_attributes').unblock();
                } else if (response.slug) {
                    // Success.
                    let data_send = {
                        action: 'vi_wvps_get_html_global_attrs_item',
                        product_id: $('.vi-wpvs-attribute-wrap-wrap').data('product_id'),
                        attribute_name: attribute_wrap.data('attribute_name'),
                        i: attribute_wrap.data('index'),
                    };
                    data_send.term_id = response.term_id;
                    $.ajax({
                        url: viwpvs_admin_custom_attribute.ajax_url,
                        type: 'post',
                        data: data_send,
                        beforeSend: function () {
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                attribute_wrap.find('.vi-wpvs-attribute-taxonomy-action').before(response.content);
                                viwpvs_custom_attribute.init();
                            } else {
                                window.alert(response.content);
                            }
                        },
                        complete: function () {
                            $('.product_attributes').unblock();
                        }
                    });
                }
            });
        } else {
            $('.product_attributes').unblock();
        }

        return false;
    });
});

let viwpvs_custom_attribute = {
    init: function () {
        this.editAttribute();
        this.editItem();
        this.ColorPicker();
        this.UploadImage();
        this.duplicateItem();
        this.removeItem();
    },
    editAttribute: function () {
        jQuery('.vi-wpvs-attribute-info-custom-open').unbind().on('click', function () {
            jQuery(this).find('.vi-wpvs-attribute-value-action-icon').toggleClass('vi-wpvs-hidden');
            jQuery(this).closest('.vi-wpvs-attribute-content-wrap').find('.vi-wpvs-attribute-info-custom-wrap').toggleClass('vi-wpvs-hidden');
        });
        jQuery('.vi-wpvs-attribute-value-title-wrap').unbind().on('click', function () {
            if (!jQuery(this).hasClass('vi-wpvs-attribute-value-title-toggle')) {
                return false;
            }
            jQuery(this).find('.vi-wpvs-attribute-value-action-icon').toggleClass('vi-wpvs-hidden');
            jQuery(this).closest('.vi-wpvs-attribute-value-wrap').find('.vi-wpvs-attribute-value-content-wrap').toggleClass('vi-wpvs-attribute-value-content-open').toggleClass('vi-wpvs-attribute-value-content-close');
        });
        jQuery('.vi-wpvs-attribute-taxonomy-select-all').unbind().on('click', function () {
            let attribute_wrap = jQuery(this).closest('.vi-wpvs-attribute-value-wrap-wrap');
            let total_term = parseInt(jQuery(this).data('total_term') || 0),
                available_wrap = attribute_wrap.find('.vi-wpvs-attribute-value-wrap');
            if (total_term === 0) {
                return false;
            }
            if (available_wrap.length < total_term) {
                let term_ids = [];
                available_wrap.find('.vi_wpvs_attribute_values').map(function () {
                    term_ids.push(jQuery(this).val());
                });
                let data_send = {
                    action: 'vi_wvps_get_html_global_attrs_items',
                    attribute_name: attribute_wrap.data('attribute_name'),
                    i: attribute_wrap.data('index'),
                    vi_attribute_type: attribute_wrap.parent().find('.vi-wpvs-attribute-type select').val() || '',
                    available: term_ids
                };
                jQuery.ajax({
                    url: viwpvs_admin_custom_attribute.ajax_url,
                    type: 'post',
                    data: data_send,
                    beforeSend: function () {
                        jQuery('.product_attributes').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                    },
                    success: function (response) {
                        if (response && response.status === 'success') {
                            available_wrap.each(function (key, value) {
                                jQuery(value).removeClass('vi-wpvs-hidden');
                                jQuery(value).find('input, select').each(function () {
                                    jQuery(this).attr('name', jQuery(this).data('name'));
                                });
                            });
                            attribute_wrap.find('.vi-wpvs-attribute-taxonomy-action').before(response.content);
                            viwpvs_custom_attribute.init();
                        }
                    },
                    complete: function () {
                        jQuery('.product_attributes').unblock();
                    }
                });
            } else {
                available_wrap.each(function (key, value) {
                    jQuery(value).removeClass('vi-wpvs-hidden');
                    jQuery(value).find('input, select').each(function () {
                        jQuery(this).attr('name', jQuery(this).data('name'));
                    });
                });
            }
            jQuery(this).parent().find('.vi-wpvs-attribute-taxonomy-add-new').addClass('disabled');
        });
        jQuery('.vi-wpvs-attribute-taxonomy-select-none').unbind().on('click', function () {
            let attribute_value_wrap = jQuery(this).closest('.vi-wpvs-attribute-value-wrap-wrap').find('.vi-wpvs-attribute-value-wrap');
            if (attribute_value_wrap.length === 0) {
                return false;
            }
            jQuery(this).parent().find('.vi-wpvs-attribute-taxonomy-add-new').removeClass('disabled');
            attribute_value_wrap.each(function (key, value) {
                jQuery(value).addClass('vi-wpvs-hidden');
                jQuery(value).find('input, select').each(function () {
                    jQuery(this).attr('name', '');
                });
            });
        });
        jQuery('.vi-wpvs-attribute-taxonomy-add-new').unbind().on('click', function () {
            jQuery(this).addClass('vi-wpvs-action-editing');
            jQuery(this).closest('.vi-wpvs-attribute-value-wrap-wrap').find('.vi-wpvs-attribute-taxonomy-add-new-term-wrap').removeClass('vi-wpvs-hidden');
        });

        jQuery('.vi-wpvs-attribute-type select').unbind().on('change', function () {
            let val = jQuery(this).val(),
                $container = jQuery(this).closest('.vi-wpvs-attribute-content-wrap'),
                $color_wrap = $container.find('.vi-wpvs-attribute-value-content-color-wrap');
            if (val === 'color' || val === '0') {
                $color_wrap.removeClass('vi-wpvs-hidden');
            } else {
                $color_wrap.addClass('vi-wpvs-hidden');
            }
            // div_container.find('.vi-wpvs-attribute-value-content-wrap > div, .vi-wpvs-attribute-value-action-icon').addClass('vi-wpvs-hidden');
            // if (val === 'image') {
            //     div_container.find('.vi-wpvs-attribute-value-title-wrap').addClass('vi-wpvs-attribute-value-title-toggle');
            //     div_container.find('.vi-wpvs-attribute-value-content-wrap .vi-wpvs-attribute-value-content-' + val + '-wrap,.vi-wpvs-attribute-value-action-icon-down ').removeClass('vi-wpvs-hidden');
            // } else {
            //     let show = false;
            //     if ($container.find('.vi-wpvs-change-product-image-select').val() !== '' && val !== 'variation_img') {
            //         div_container.find('.vi-wpvs-attribute-value-title-wrap').addClass('vi-wpvs-attribute-value-title-toggle');
            //         div_container.find('.vi-wpvs-attribute-value-content-wrap .vi-wpvs-attribute-value-content-image-wrap,.vi-wpvs-attribute-value-action-icon-down ').removeClass('vi-wpvs-hidden');
            //         show = true;
            //     }
            //     if (val === 'color') {
            //         div_container.find('.vi-wpvs-attribute-value-title-wrap').addClass('vi-wpvs-attribute-value-title-toggle');
            //         div_container.find('.vi-wpvs-attribute-value-content-wrap .vi-wpvs-attribute-value-content-' + val + '-wrap,.vi-wpvs-attribute-value-action-icon-down ').removeClass('vi-wpvs-hidden');
            //         show = true;
            //     }
            //     if (!show) {
            //         div_container.find('.vi-wpvs-attribute-value-title-wrap').removeClass('vi-wpvs-attribute-value-title-toggle');
            //         div_container.find('.vi-wpvs-attribute-value-content-wrap').removeClass('vi-wpvs-attribute-value-content-open').addClass('vi-wpvs-attribute-value-content-close');
            //     }
            // }
        }).trigger('change');
    },
    editItem: function () {
        jQuery('.vi-wpvs-attribute-value-name').unbind().on('click', function (e) {
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-attribute-edit-button-cancel').unbind().on('click', function () {
            jQuery('.vi-wpvs-action-editing').removeClass('vi-wpvs-action-editing');
            jQuery(this).closest('.vi-wpvs-attribute-edit-wrap-wrap').addClass('vi-wpvs-hidden');
        });
        jQuery('.vi-wpvs-attribute-edit-button-ok').unbind().on('click', function () {
            let attribute_wrap = jQuery('.vi-wpvs-action-editing').closest('.vi-wpvs-attribute-value-wrap-wrap'),
                new_terms = jQuery(this).closest('.vi-wpvs-attribute-taxonomy-add-new-term-wrap').find('.vi-wpvs-taxonomy-add-new-term').val();
            let data_send = {
                action: 'vi_wvps_get_html_global_attrs_item',
                product_id: jQuery('.vi-wpvs-attribute-wrap-wrap').data('product_id'),
                attribute_name: attribute_wrap.data('attribute_name'),
                i: attribute_wrap.data('index'),
            };
            if (new_terms && new_terms.length > 0) {
                new_terms.forEach(function (v) {
                    let attribute_value_wrap = attribute_wrap.find('.vi-wpvs-attribute-taxonomy-value-wrap-' + v);
                    if (attribute_value_wrap.length) {
                        attribute_value_wrap.removeClass('vi-wpvs-hidden');
                        attribute_value_wrap.find('input, select').each(function () {
                            jQuery(this).attr('name', jQuery(this).data('name'));
                        });
                    } else {
                        data_send.term_id = v;
                        jQuery.ajax({
                            url: viwpvs_admin_custom_attribute.ajax_url,
                            type: 'post',
                            data: data_send,
                            beforeSend: function () {
                                jQuery('.product_attributes').block({
                                    message: null,
                                    overlayCSS: {
                                        background: '#fff',
                                        opacity: 0.6
                                    }
                                });
                            },
                            success: function (response) {
                                if (response && response.status === 'success') {
                                    attribute_wrap.find('.vi-wpvs-attribute-taxonomy-action').before(response.content);
                                    viwpvs_custom_attribute.init();
                                }
                            },
                            complete: function () {
                                jQuery('.product_attributes').unblock();
                            }
                        })
                    }
                });
            }
            jQuery(this).closest('.vi-wpvs-attribute-taxonomy-add-new-term-wrap').find('.vi-wpvs-taxonomy-add-new-term').val('').trigger('change');
            jQuery('.vi-wpvs-action-editing').removeClass('vi-wpvs-action-editing');
            jQuery(this).closest('.vi-wpvs-attribute-taxonomy-add-new-term-wrap').addClass('vi-wpvs-hidden');
        });
    },
    removeItem: function () {
        jQuery('.vi-wpvs-attribute-wrap-wrap .vi-wpvs-attribute-value-action-remove').unbind().on('click', function (e) {
            e.stopPropagation();
            let attribute_wrap = jQuery(this).closest('.vi-wpvs-attribute-value-wrap');
            if (attribute_wrap.hasClass('vi-wpvs-attribute-taxonomy-value-wrap')) {
                if (confirm(viwpvs_admin_custom_attribute.remove_item)) {
                    attribute_wrap.addClass('vi-wpvs-hidden');
                    attribute_wrap.find('input, select').each(function () {
                        jQuery(this).attr('name', '');
                    });
                    attribute_wrap.parent().find('.vi-wpvs-attribute-taxonomy-add-new').removeClass('disabled');
                }
            } else {
                if (attribute_wrap.parent().find('.vi-wpvs-attribute-value-wrap').length === 1) {
                    alert(viwpvs_admin_custom_attribute.remove_last_item);
                    return false;
                }
                if (confirm(viwpvs_admin_custom_attribute.remove_item)) {
                    attribute_wrap.remove();
                }
            }
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-attribute-colors-action-remove').unbind().on('click', function (e) {
            if (jQuery(this).closest('.vi-wpvs-attribute-value-content-color-table').find('tr').length === 2) {
                alert(viwpvs_admin_custom_attribute.remove_last_item);
                return false;
            }
            if (confirm(viwpvs_admin_custom_attribute.remove_item)) {
                jQuery(this).parent().parent().remove();
            }
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-attribute-row-remove').unbind().on('click', function (e) {
            e.preventDefault();
            if (confirm(viwpvs_admin_custom_attribute.remove_attribute)) {
                let wrap = jQuery(this).closest('.vi-wpvs-attribute-wrap');
                if (wrap.is('.taxonomy')) {
                    wrap.remove();
                    jQuery('select.attribute_taxonomy').find('option[value="' + wrap.data('taxonomy') + '"]').removeAttr('disabled');
                } else {
                    wrap.find('select, input[type=text]').val('');
                    wrap.hide();
                    jQuery('.product_attributes .woocommerce_attribute').each(function (index, el) {
                        jQuery('.attribute_position', el).val(parseInt(jQuery(el).index('.product_attributes .woocommerce_attribute'), 10));
                    });
                }
            }
            e.stopPropagation();
        });
    },
    duplicateItem: function () {
        jQuery('.vi-wpvs-attribute-colors-action-clone').unbind().on('click', function (e) {
            e.stopPropagation();
            var current = jQuery(this).parent().parent();
            var newRow = current.clone();
            newRow.find('.iris-picker').remove();
            newRow.insertAfter(current);
            viwpvs_custom_attribute.init();
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-attribute-value-action-clone').unbind().on('click', function (e) {
            e.stopPropagation();
            let i = jQuery('.vi-wpvs-attribute-value-wrap').length, j;
            var current = jQuery(this).closest('.vi-wpvs-attribute-value-wrap');
            var newRow = current.clone();
            j = current.data('attribute_number');
            newRow.find('.iris-picker').remove();
            newRow.find('.vi_attribute_colors').each(function () {
                jQuery(this).attr('name', 'vi_attribute_colors[' + j + '][' + i + '][]');
            });
            newRow.insertAfter(current);
            viwpvs_custom_attribute.init();
            e.stopPropagation();
        });
    },
    UploadImage: function () {
        var viwpvs_img_uploader;
        jQuery('.vi-attribute-image-remove').unbind().on('click', function (e) {
            let wrap = jQuery(this).closest('.vi-wpvs-attribute-value-content-image-wrap');
            let src_placeholder = wrap.find('.vi-attribute-image-preview img').data('src_placeholder');
            wrap.find('.vi_attribute_image').val('');
            wrap.find('.vi-attribute-image-preview img').attr('src', src_placeholder);
            jQuery(this).addClass('vi-wpvs-hidden');
        });
        jQuery('.vi-attribute-image-add-new').unbind().on('click', function (e) {
            e.preventDefault();
            jQuery('.vi_attribute_image-editing').removeClass('vi_attribute_image-editing');
            jQuery(this).closest('.vi-wpvs-attribute-value-content-image-wrap').addClass('vi_attribute_image-editing');
            //If the uploader object has already been created, reopen the dialog
            if (viwpvs_img_uploader) {
                viwpvs_img_uploader.open();
                return false;
            }
            //Extend the wp.media object
            viwpvs_img_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: true
            });

            //When a file is selected, grab the URL and set it as the text field's value
            viwpvs_img_uploader.on('select', function () {
                let attachment = viwpvs_img_uploader.state().get('selection').first().toJSON();
                jQuery('.vi_attribute_image-editing').find('.vi_attribute_image').val(attachment.id);
                jQuery('.vi_attribute_image-editing').find('.vi-attribute-image-preview img').attr('src', attachment.url);
                jQuery('.vi_attribute_image-editing').find('.vi-attribute-image-remove').removeClass('vi-wpvs-hidden');
                jQuery('.vi_attribute_image-editing').removeClass('vi_attribute_image-editing');
            });

            //Open the uploader dialog
            viwpvs_img_uploader.open();
        });
    },
    ColorPicker: function () {
        jQuery('.vi-wpvs-color').each(function () {
            jQuery(this).css({backgroundColor: jQuery(this).val()});
        });
        jQuery('.vi-wpvs-color.vi_attribute_colors').unbind().minicolors({
            change: function (value, opacity) {
                jQuery(this).parent().find('.vi-wpvs-color.vi_attribute_colors').css({backgroundColor: value});
            },
            animationSpeed: 50,
            animationEasing: 'swing',
            changeDelay: 0,
            control: 'wheel',
            defaultValue: '',
            format: 'rgb',
            hide: null,
            hideSpeed: 100,
            inline: false,
            keywords: '',
            letterCase: 'lowercase',
            opacity: true,
            position: 'bottom left',
            show: null,
            showSpeed: 100,
            theme: 'default',
            swatches: []
        });
    },
    wpvs_term_color_preview: function () {
    }
};