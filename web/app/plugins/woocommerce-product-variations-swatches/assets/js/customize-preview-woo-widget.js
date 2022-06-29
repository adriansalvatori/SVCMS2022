(function () {
    'use strict';
    jQuery(document).ready(function () {
        jQuery(document).on('ajaxComplete', function (event, jqxhr, settings) {
            jQuery('.vi-wpvs-customize-woo-widget-css').each(function () {
               let widget_id = jQuery(this).data('widget_id') ;
               if (jQuery('#vi-wpvs-customize-'+widget_id).length){
                   jQuery('#vi-wpvs-customize-'+widget_id).html(jQuery(this).html());
               }else {
                   jQuery('#vi-wpvs-frontend-widget-inline-css').after('<style id="vi-wpvs-customize-'+widget_id+'" type="text/css">'+jQuery(this).html()+'</style>');
               }
                jQuery(this).remove();
            });
        });
    });
})();