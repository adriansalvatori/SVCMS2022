jQuery(document).ready(function () {
    'use strict';
    jQuery('.vi-ui.accordion').villatheme_accordion('refresh');
    jQuery('.vi-ui.dropdown').unbind().dropdown();
    jQuery('.vi-ui.checkbox').unbind().checkbox();
    jQuery('input[type="checkbox"]').unbind().on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery(this).parent().find('input[type="hidden"]').val('1');
        }else {
            jQuery(this).parent().find('input[type="hidden"]').val('');
        }
    });
    jQuery('input[type = "number"]').unbind().on('change', function () {
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
    jQuery('.vi-wpvs-color').each(function () {
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