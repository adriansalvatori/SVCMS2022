/**
 * Customizer Communicator
 */
( function ( api, $) {
    "use strict";

    api.bind("ready", function() {

        var requireVarUpdate = [
            {
                key: "ajax_add_to_cart",
                var: "ajaxAddToCart",
                bool: true
            },

            {
                key: "single_ajax_add_to_cart",
                var: "ajaxSinglePageAddToCart",
                bool: true
            },
            {
                key: "single_refresh_fragments",
                var: "singleRefreshFragments",
                bool: true
            },
            {
                key: "single_added_scroll_to_notice",
                var: "singleScrollToNotice",
                bool: true
            },

            {
                key: "override_spinner",
                var: "overrideSpinner",
                bool: true
            },
            {
                key: "spinner_icon",
                var: "spinnerIcon"
            },
            {
                key: "checkmark_icon",
                var: "checkmarkIcon"
            },

            {
                key: "redirection_enabled",
                var: "redirectEnabled",
                bool: true,
                save: true
            },
            {
                key: "redirection_to",
                var: "redirectTo",
                save: true
            },
            {
                key: "redirection_to_custom",
                var: "redirectionToCustom",
                save: true
            }
        ];

        requireVarUpdate.forEach(function (setting) {

            api.value(XT_ATC.customizerConfigId + "[" + setting.key + "]").bind(function (value) {

                XT_ATC[setting.var] = setting.bool ? (parseInt(value) ? true : false) : value;

                var targetWindow = api.previewer.targetWindow();

                if(targetWindow) {
                    targetWindow.XT_ATC[setting.var] = XT_ATC[setting.var];
                }

                if(setting.save) {
                    api.previewer.save();
                }
            });
        });
    });

} )( wp.customize, jQuery);
