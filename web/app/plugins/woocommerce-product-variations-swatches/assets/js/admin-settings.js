jQuery(document).ready(function ($) {
    'use strict';
    jQuery('.vi-ui.vi-ui-main.tabular.menu .item').vi_tab({
        history: true,
        historyType: 'hash'
    });
    $('.vi-wpvs-input-shortcode-field').on('click', function () {
        let $shortcode = $(this), $wrapper = $shortcode.parent();
        $shortcode.select();
        document.execCommand('copy');
        $wrapper.attr('data-tooltip', vi_wpvs_admin_settings.i18n_shortcode_copied);
        setTimeout(function () {
            $wrapper.attr('data-tooltip', vi_wpvs_admin_settings.i18n_copy_shortcode);
        }, 3000)
    });
    /*Setup tab*/
    let changeProfile, tabs,
        tabEvent = false,
        initialTab = 'swatches_profile',
        navSelector = '.vi-ui.vi-ui-main.menu',
        panelSelector = '.vi-ui.vi-ui-main.tab',
        panelFilter = function () {
            jQuery(panelSelector + ' a').filter(function () {
                return jQuery(navSelector + ' a[title=' + jQuery(this).attr('title') + ']').size() != 0;
            });
        };
    // Initializes plugin features
    jQuery.address.strict(false).wrap(true);

    if (jQuery.address.value() == '') {
        jQuery.address.history(false).value(initialTab).history(true);
    }
    // Address handler
    jQuery.address.init(function (event) {

        // Adds the ID in a lazy manner to prevent scrolling
        jQuery(panelSelector).attr('id', initialTab);

        panelFilter();

        // Tabs setup
        tabs = jQuery('.vi-ui.vi-ui-main.menu')
            .vi_tab({
                history: true,
                historyType: 'hash'
            });

        // Enables the plugin for all the tabs
        jQuery(navSelector + ' a').on('click', function (event) {
            tabEvent = true;
            if (jQuery(this).data('tab') === 'custom_attrs' && changeProfile && jQuery('.vi-wpvs-accordion-wrap').length) {
                let swatchesProfile = {};
                jQuery('.vi-wpvs-accordion-wrap').each(function () {
                    swatchesProfile[jQuery(this).find('.vi-wpvs-ids').val()] = jQuery(this).find('.vi-wpvs-names').val();
                });
                jQuery('.vi-wpvs-table-swatches-profile').each(function () {
                    let div_container = jQuery(this),
                        profile = jQuery(this).find('select').val(),
                        selectProfile, html = '';
                    html += ' <select name="custom_attribute_profiles[]" class="vi-ui fluid dropdown vi-wpvs-custom_attribute_profiles">';
                    for (let profileId in swatchesProfile) {
                        selectProfile = profile === profileId ? 'select ="selected"' : '';
                        html += '<option value="' + profileId + '" ' + selectProfile + ' >' + swatchesProfile[profileId] + '</option>';
                    }
                    html += '</select>';
                    div_container.html(html);
                    div_container.find('.vi-ui.dropdown').unbind().dropdown();
                });
                changeProfile = false;
            }
            tabEvent = false;
            return true;
        });

    });

    //Auto update
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = jQuery.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });

    jQuery('.vi-wpvs-save').on('click', function () {
        jQuery(this).addClass('loading');
        let nameArr = jQuery('input[name="names[]"]');
        let z, v;
        for (z = 0; z < nameArr.length; z++) {
            if (!jQuery('input[name="names[]"]').eq(z).val()) {
                alert('Name cannot be empty!');
                if (!jQuery('.vi-wpvs-accordion').eq(z).hasClass('vi-wpvs-active-accordion')) {
                    jQuery('.vi-wpvs-accordion').eq(z).addClass('vi-wpvs-active-accordion');
                }
                jQuery('.vi-wpvs-save').removeClass('loading');
                return false;
            }
        }

        for (z = 0; z < nameArr.length - 1; z++) {
            for (v = z + 1; v < nameArr.length; v++) {
                if (jQuery('input[name="names[]"]').eq(z).val() === jQuery('input[name="names[]"]').eq(v).val()) {
                    alert("Names are unique!");
                    if (!jQuery('.vi-wpvs-accordion').eq(v).hasClass('vi-wpvs-active-accordion')) {
                        jQuery('.vi-wpvs-accordion').eq(v).addClass('vi-wpvs-active-accordion');
                    }
                    jQuery('.vi-wpvs-save').removeClass('loading');
                    return false;
                }
            }
        }

        jQuery(this).attr('type', 'submit');
    });
    jQuery(document).on('click', '.vi-wpvs-import', function () {
        jQuery('.vi-wpvs-import-wrap-wrap').removeClass('vi-wpvs-hidden');
    });
    jQuery(document).on('click', '.vi-wpvs-reset', function () {
        if (confirm('All settings will be deleted. Are you sure you want to reset yours settings?')) {
            jQuery(this).attr('type', 'submit');
        }
    });
    handleInit();

    function handleInit() {
        jQuery('.vi-ui.accordion').villatheme_accordion('refresh');
        jQuery('.vi-ui.dropdown').unbind().dropdown();
        cloneItem();
        RemoveItem();
        handleValueChange();
        handleCheckBox();
        handleColorPicker();
    }

    // change name
    function handleValueChange() {
        jQuery('.vi-wpvs-names').unbind().on('keyup', function () {
            jQuery(this).parent().parent().parent().find('.vi-wpvs-accordion-name').html(jQuery(this).val());
        });
        jQuery('input[type = "number"]').unbind().on('change', function () {
            let min = parseFloat(jQuery(this).attr('min')) || 0,
                max = parseFloat(jQuery(this).attr('max')),
                val = parseFloat(jQuery(this).val()) || 0;
            if (jQuery(this).attr('min') !== undefined) {
                if (min > val) {
                    jQuery(this).val(min);
                } else {
                    jQuery(this).val(val);
                }
            }
            if (jQuery(this).attr('max') !== undefined) {
                if (max && max < val) {
                    jQuery(this).val(max);
                }
            }
        });
        jQuery(".vi-wpvs-category-search").select2({
            closeOnSelect: false,
            ajax: {
                url: "admin-ajax.php?action=viwpvs_search_cate",
                dataType: 'json',
                type: "GET",
                quietMillis: 50,
                delay: 250,
                data: function (params) {
                    return {
                        keyword: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 2
        });
    }

    /**
     *
     */
    // jQuery(document).on('change', '[name="custom_attribute_type[]"]', function () {
    //     let $select = jQuery(this), $row = $select.closest('.vi-wpvs-rule-custom-attrs-container');
    //     let custom_attribute_type = $select.val();
    //     let $change_product_image = $row.find('.vi-wpvs-custom-attr-change-product-image-enable');
    //     if (custom_attribute_type === 'image' || custom_attribute_type === 'variation_img') {
    //         $change_product_image.removeClass('disabled');
    //     } else {
    //         $change_product_image.addClass('disabled');
    //     }
    // });

    function handleCheckBox() {
        jQuery('.vi-ui.checkbox').unbind().checkbox();

        jQuery('input[type="checkbox"]').unbind().on('change', function () {
            let $checkbox = jQuery(this), $row = $checkbox.closest('.vi-wpvs-rule-custom-attrs-container');
            // let custom_attribute_type = $row.find('[name="custom_attribute_type[]"]').val();
            // if (custom_attribute_type === 'image' || custom_attribute_type === 'variation_img') {
            //     jQuery('.vi-wpvs-custom-attr-change-product-image-enable').removeClass('disabled');
            // } else {
            //     jQuery('.vi-wpvs-custom-attr-change-product-image-enable').addClass('disabled');
            // }
            if ($checkbox.prop('checked')) {
                $checkbox.parent().find('input[type="hidden"]').val('1');
                if ($checkbox.hasClass('vi-wpvs-product-list-add-to-cart-checkbox')) {
                    jQuery('.vi-wpvs-product-list-atc-enable').removeClass('vi-wpvs-hidden');
                    jQuery('.vi-wpvs-custom-attr-loop-enable').addClass('disabled');
                }
                if ($checkbox.hasClass('vi-wpvs-product-list-more-link-enable-checkbox')) {
                    jQuery('.vi-wpvs-product-list-more-link-enable').removeClass('vi-wpvs-hidden');
                }
                if ($checkbox.hasClass('vi-wpvs-product_list_slider-checkbox')) {
                    jQuery('.vi-wpvs-product_list_slider-enable').removeClass('vi-wpvs-hidden');
                }
                if ($checkbox.hasClass('vi-wpvs-single_attr_title-checkbox')) {
                    jQuery('.vi-wpvs-single_attr_title-enable').removeClass('vi-wpvs-hidden');
                }
            } else {
                $checkbox.parent().find('input[type="hidden"]').val('');
                if ($checkbox.hasClass('vi-wpvs-product-list-add-to-cart-checkbox')) {
                    jQuery('.vi-wpvs-product-list-atc-enable').addClass('vi-wpvs-hidden');
                    jQuery('.vi-wpvs-custom-attr-loop-enable').removeClass('disabled');
                }
                if ($checkbox.hasClass('vi-wpvs-product-list-more-link-enable-checkbox')) {
                    jQuery('.vi-wpvs-product-list-more-link-enable').addClass('vi-wpvs-hidden');
                }
                if ($checkbox.hasClass('vi-wpvs-product_list_slider-checkbox')) {
                    jQuery('.vi-wpvs-product_list_slider-enable').addClass('vi-wpvs-hidden');
                }
                if ($checkbox.hasClass('vi-wpvs-single_attr_title-checkbox')) {
                    jQuery('.vi-wpvs-single_attr_title-enable').addClass('vi-wpvs-hidden');
                }
            }
        });
    }

    function handleColorPicker() {
        jQuery('.vi-wpvs-color').each(function () {
            jQuery(this).css({backgroundColor: jQuery(this).val()});
        });
        jQuery('.vi-wpvs-color').unbind().minicolors({
            change: function (value, opacity) {
                jQuery(this).parent().find('.vi-wpvs-color').css({backgroundColor: value});
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
    }

    function RemoveItem() {
        jQuery('.vi-wpvs-accordion-remove').unbind().on('click', function (e) {
            if (jQuery('.vi-wpvs-accordion-remove').length === 1) {
                alert('You can not remove the last item.');
                return false;
            }
            if (confirm("Would you want to remove this?")) {
                let div_rmove = jQuery(this).parent().parent().parent();
                jQuery('.vi-wpvs-table-swatches-profile option[value= "' + div_rmove.find('.vi-wpvs-ids').val() + '"]').remove();
                div_rmove.remove();
                changeProfile = true;
            }
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-rule-custom-attrs-remove').unbind().on('click', function (e) {
            if (jQuery('.vi-wpvs-rule-custom-attrs-remove ').length === 1) {
                alert('You can not remove the last item.');
                return false;
            }
            if (confirm("Would you want to remove this?")) {
                jQuery(this).parent().parent().remove();
            }
            e.stopPropagation();
        });
    }

    function cloneItem() {
        jQuery('.vi-wpvs-accordion-clone').unbind().on('click', function (e) {
            e.stopPropagation();
            let new_id = jQuery('.vi-wpvs-accordion-wrap').length;
            var current = jQuery(this).parent().parent().parent();
            var newRow = current.clone();
            var $now = Date.now();
            newRow.attr('data-accordion_id', new_id);
            newRow.find('.vi-wpvs-ids').val($now);
            for (let i = 0; i < newRow.find('.vi-ui.dropdown').length; i++) {
                let selected = current.find('.vi-ui.dropdown').eq(i).dropdown('get value');
                newRow.find('.vi-ui.dropdown').eq(i).dropdown('set selected', selected);
            }
            newRow.find('.iris-picker').remove();
            newRow.insertAfter(current);
            changeProfile = true;
            handleInit();
            e.stopPropagation();
        });
        jQuery('.vi-wpvs-rule-custom-attrs-clone').unbind().on('click', function (e) {
            e.stopPropagation();
            var current = jQuery(this).parent().parent();
            var newRow = current.clone();
            var $now = Date.now();
            newRow.find('.vi-wpvs-custom_attribute_id').val($now);
            newRow.find('.vi-wpvs-category-search').attr('name', 'custom_attribute_category[' + $now + '][]');
            for (let j = 0; j < newRow.find('.vi-ui.dropdown').length; j++) {
                let selected = current.find('.vi-ui.dropdown').eq(j).dropdown('get value');
                newRow.find('.vi-ui.dropdown').eq(j).dropdown('set selected', selected);
            }
            newRow.find('.select2').remove();
            newRow.insertAfter(current);
            handleInit();
            e.stopPropagation();
        });
    }
});

function viwpvs_set_value_number(div) {
    jQuery(div).find('input[type = "number"]').unbind().on('blur', function () {
        let new_val, min = parseFloat(jQuery(this).attr('min')) || 0,
            max = parseFloat(jQuery(this).attr('max')),
            val = parseFloat(jQuery(this).val()) || 0;
        new_val = val;
        if (min > val) {
            new_val = min;
        }
        if (max && max < val) {
            new_val = max;
        }
        jQuery(this).val(new_val).trigger('change');
    });
}