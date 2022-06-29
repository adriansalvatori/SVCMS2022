jQuery(document).ready(function () {
    'use strict';
    jQuery(document).on('click','.vi-wpvs-customize-woo-widget-wrap:not(.vi-wpvs-customize-woo-widget-wrap-init) .vi-wpvs-customize-woo-widget-btn-design', function (e) {
        e.preventDefault();
        e.stopPropagation();
        viwpvs_woo_widget_settings(jQuery(this).closest('.vi-wpvs-customize-woo-widget-wrap'));
        jQuery(this).trigger('click');
        return false;
    });
    jQuery(document).on('click','.vi-wpvs-customize-woo-widget-wrap:not(.vi-wpvs-customize-woo-widget-wrap-init) ', function () {
        viwpvs_woo_widget_settings(jQuery(this));
        return false;
    });
    jQuery(document).on('widget-updated', function ( event, $widget ) {
        viwpvs_woo_widget_settings(jQuery('#widgets-right .vi-wpvs-customize-woo-widget-wrap:not(.vi-wpvs-customize-woo-widget-wrap-init)'));
        return false;
    });
});
function viwpvs_woo_widget_settings(swatches_settings) {
    swatches_settings = jQuery(swatches_settings);
    swatches_settings.addClass('vi-wpvs-customize-woo-widget-wrap-init');
    swatches_settings.find('.vi-wpvs-customize-woo-widget-display_type').on('change',function () {
        if (jQuery(this).val() === 'list'){
            swatches_settings.find('.vi-wpvs-customize-woo-widget-content-wrap').removeClass('vi-wpvs-hidden');
        }else {
            swatches_settings.find('.vi-wpvs-customize-woo-widget-content-wrap').addClass('vi-wpvs-hidden');
        }
    });
    swatches_settings.find('.vi-wpvs-customize-woo-widget-design-select').on('change',function () {
        if (jQuery(this).val()){
            swatches_settings.find('.vi-wpvs-customize-woo-widget-design-'+jQuery(this).data('design')).removeClass('vi-wpvs-hidden');
        }else {
            swatches_settings.find('.vi-wpvs-customize-woo-widget-design-'+jQuery(this).data('design')).addClass('vi-wpvs-hidden');
        }
    });
    swatches_settings.find('.vi-wpvs-customize-woo-widget-heading1').on('click',function () {
        if (jQuery(this).hasClass('active')){
            jQuery(this).removeClass('active');
            jQuery(this).closest('.vi-wpvs-customize-woo-widget-design-content').find('.vi-wpvs-customize-woo-widget-heading, table').addClass('vi-wpvs-hidden');
        }else {
            jQuery(this).addClass('active');
            jQuery(this).closest('.vi-wpvs-customize-woo-widget-design-content').find('.vi-wpvs-customize-woo-widget-heading, table').removeClass('vi-wpvs-hidden');
        }
    });
    swatches_settings.find('.vi-wpvs-customize-woo-widget-btn-design').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        swatches_settings.find('.vi-wpvs-customize-woo-widget-design-wrap').removeClass('vi-wpvs-hidden');
    });
    swatches_settings.find('.vi-wpvs-customize-woo-widget-close').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        swatches_settings.find('.vi-wpvs-customize-woo-widget-design-wrap').addClass('vi-wpvs-hidden');
    });
    swatches_settings.find('input[type = "number"]').unbind().on('change', function () {
        let min = parseFloat(jQuery(this).attr('min')) || 0,
            max = parseFloat(jQuery(this).attr('max')),
            val = parseFloat(jQuery(this).val()) || 0;
        if (min > val) {
            jQuery(this).val(min);
        } else {
            jQuery(this).val(val);
        }
        if (max && max < val) {
            jQuery(this).val(max);
        }
    });
    swatches_settings.find('.vi-wpvs-color').each(function () {
        jQuery(this).css({backgroundColor: jQuery(this).val()});
        jQuery(this).unbind().minicolors({
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
    });
}
function viwpvs_woo_widget_settings1() {
    jQuery('#widgets-right .vi-wpvs-customize-woo-widget-wrap:not(.vi-wpvs-customize-woo-widget-wrap-init),.customize-control .vi-wpvs-customize-woo-widget-wrap:not(.vi-wpvs-customize-woo-widget-wrap-init)').each(function (k, swatches_settings) {
        swatches_settings = jQuery(swatches_settings);
        swatches_settings.addClass('vi-wpvs-customize-woo-widget-wrap-init');
        swatches_settings.find('.vi-wpvs-customize-woo-widget-display_type').on('change',function () {
            if (jQuery(this).val() === 'list'){
                swatches_settings.find('.vi-wpvs-customize-woo-widget-content-wrap').removeClass('vi-wpvs-hidden');
            }else {
                swatches_settings.find('.vi-wpvs-customize-woo-widget-content-wrap').addClass('vi-wpvs-hidden');
            }
        });
        swatches_settings.find('.vi-wpvs-customize-woo-widget-design-select').on('change',function () {
            if (jQuery(this).val()){
                swatches_settings.find('.vi-wpvs-customize-woo-widget-design-'+jQuery(this).data('design')).removeClass('vi-wpvs-hidden');
            }else {
                swatches_settings.find('.vi-wpvs-customize-woo-widget-design-'+jQuery(this).data('design')).addClass('vi-wpvs-hidden');
            }
        });
        swatches_settings.find('.vi-wpvs-customize-woo-widget-heading1').on('click',function () {
            if (jQuery(this).hasClass('active')){
                jQuery(this).removeClass('active');
                jQuery(this).closest('.vi-wpvs-customize-woo-widget-design-content').find('.vi-wpvs-customize-woo-widget-heading, table').addClass('vi-wpvs-hidden');
            }else {
                jQuery(this).addClass('active');
                jQuery(this).closest('.vi-wpvs-customize-woo-widget-design-content').find('.vi-wpvs-customize-woo-widget-heading, table').removeClass('vi-wpvs-hidden');
            }
        });
        swatches_settings.find('.vi-wpvs-customize-woo-widget-btn-design').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            swatches_settings.find('.vi-wpvs-customize-woo-widget-design-wrap').removeClass('vi-wpvs-hidden');
        });
        swatches_settings.find('.vi-wpvs-customize-woo-widget-close').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            swatches_settings.find('.vi-wpvs-customize-woo-widget-design-wrap').addClass('vi-wpvs-hidden');
        });
        swatches_settings.find('input[type = "number"]').unbind().on('change', function () {
            let min = parseFloat(jQuery(this).attr('min')) || 0,
                max = parseFloat(jQuery(this).attr('max')),
                val = parseFloat(jQuery(this).val()) || 0;
            if (min > val) {
                jQuery(this).val(min);
            } else {
                jQuery(this).val(val);
            }
            if (max && max < val) {
                jQuery(this).val(max);
            }
        });
        swatches_settings.find('.vi-wpvs-color').each(function () {
            jQuery(this).css({backgroundColor: jQuery(this).val()});
            jQuery(this).unbind().minicolors({
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
        });
    });
}