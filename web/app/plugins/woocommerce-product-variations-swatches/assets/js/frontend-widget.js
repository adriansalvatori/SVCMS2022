jQuery(document).ready(function ($) {
    'use strict';
    jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
        jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
    });
    jQuery(document).ajaxComplete(function (event, jqxhr, settings) {
        jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
            jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
        });
        return false;
    });
    /**
     * Init functions after Riode theme's ajax filter
     */
    if (window.hasOwnProperty('Riode') && window.Riode !== undefined) {
        window.Riode.$window.on('update_lazyload', function () {
            jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
                jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
            });
        })
    }
    $('body').on('click', '.vi-wpvs-show-more', function () {
        let $show_more = $(this), $widget = $show_more.closest('.widget');
        if ($widget.length === 0) {
            $widget = $show_more.closest('.elementor-widget-container');
        }
        $show_more.remove();
        $widget.find('.vi-wpvs-hidden.vi-wpvs-wc-layered-nav-term').removeClass('vi-wpvs-hidden');
    })
});
jQuery(window).on('load', function () {
    'use strict';
    jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
        jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
    });
});
let viwpvs_woo_widget = function ($swatches) {
    $swatches = jQuery($swatches);
    let $wrap = $swatches.closest('.vi-wpvs-wc-layered-nav-term');
    // $wrap.addClass('vi-wpvs-wc-layered-nav-term');
    // $wrap.parent().addClass('vi-wpvs-woocommerce-widget-layered-nav-list');
    if ($wrap.hasClass('chosen')) {
        $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
    }
    $wrap.on('mouseenter', function () {
        jQuery(this).addClass('vi-wpvs-wc-layered-nav-term-hover');
        if (!$swatches.find('.vi-wpvs-option-wrap').hasClass('vi-wpvs-option-wrap-selected')) {
            $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-hover');
        }
    }).on('mouseleave', function () {
        jQuery(this).removeClass('vi-wpvs-wc-layered-nav-term-hover');
        if (!$swatches.find('.vi-wpvs-option-wrap').hasClass('vi-wpvs-option-wrap-selected')) {
            $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-hover').addClass('vi-wpvs-option-wrap-default');
        }
    });
    $swatches.find('.vi-wpvs-option.vi-wpvs-option-color').each(function (color_item_k, color_item) {
        let colors = jQuery(color_item).data('option_color');
        jQuery(color_item).css({background: colors});
    });
};
jQuery.fn.viwpvs_woo_widget_swatches = function () {
    new viwpvs_woo_widget(this);
    return this;
};