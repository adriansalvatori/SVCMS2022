/**
 * Inline Confirmation plugin for jQuery
 *
 * One of the less obtrusive ways of implementing a confirmation dialogue. Requires jQuery 1.7+.
 *
 * v1.0.0
 *
 * Copyright (c) 2021 XplodedThemes
 */

/**
 * Usage:
 *
 * // using default options
 * $("a.delete").inlineConfirm();
 *
 * // using some custom options
 * $("a.delete").inlineConfirm({
 *   message: "Are you sure ?"
 *   confirm: "Confirm",
 *   cancel: "Cancel",
 *   separator: " | ",
 *   reverse: true,
 *   bindsOnEvent: "hover",
 *   confirmCallback: function(action) {
 *     action.parent().show();
 *   }
 * });
 *
 * Configuration options:
 *
 * confirm              string    the Text for the confirm action (default: "Confirm")
 * cancel               string    the Text for the cancel action (default: "Cancel")
 * separator            string    the HTML for the separator between the confirm and the cancel actions (default: " ")
 * reverse              boolean   revert the confirm and the cancel actions (default: false)
 * hideOriginalAction   boolean   whether or not to hide the original action, useful for display the dialogue as a modal if set to false (default: true)
 * showOriginalAction   boolean   whether or not to show the original action after confirmation
 * preventDefaultEvent  boolean   whether or not to prevent default event after confirmation. Set to true for ajax actions
 * bindsOnEvent         string    the JavaScript event handler for binding the confirmation action (default: "click")
 * expiresIn            integer   seconds before the confirmation dialogue closes automatically, 0 to disable this feature (default: 0)
 * excludeClass         string    if button has this class, nothing will happen when clicked.
 * confirmCallback      function  the callback function to execute after the confirm action, accepts the original action object as an argument
 * cancelCallback       function  the callback function to execute after the cancel action, accepts the original action object as an argument
 */

(function($){

    $.fn.inlineConfirm = function(options) {
        var defaults = {
            message: "Are you sure ?",
            confirm: "<a href='#'>Confirm</a>",
            cancel: "<a href='#'>Cancel</a>",
            separator: " | ",
            reverse: false,
            hideOriginalAction: true,
            showOriginalAction: false,
            preventDefaultEvent: false,
            bindsOnEvent: "click",
            expiresIn: 0,
            excludeClass: 'processing',
            confirmCallback: function() { return true; },
            cancelCallback: function() { return true; }
        };

        var original_action;
        var timeout_id;
        var all_actions     = $(this);
        var options         = $.extend(defaults, options);
        var block_class     = "inline-confirmation-block";
        var message_class     = "inline-confirmation-message";
        var confirm_class   = "inline-confirmation-confirm";
        var cancel_class    = "inline-confirmation-cancel";
        var action_class    = "inline-confirmation-action";

        if(options.message && options.message !== '') {
            options.message = "<span class='" + action_class + " " + message_class + "'>" + options.message + "</span> ";
        }

        options.confirm = "<span class='" + action_class + " " + confirm_class + "'><a href='#'>" + options.confirm + "</a></span>";
        options.cancel  = "<span class='" + action_class + " " + cancel_class + "'><a href='#'>" + options.cancel + "</a></span>";

        var action_set = options.reverse === false
            ? options.confirm + options.separator + options.cancel
            : options.cancel + options.separator + options.confirm;

        if(options.message && options.message !== '') {
            action_set = options.message + action_set;
        }

        $(this).on(options.bindsOnEvent, function(e) {

            e.preventDefault();

            original_action = $(this);

            if(original_action.hasClass(options.excludeClass)) {
                return;
            }

            all_actions.show();
            $("span." + block_class).hide();

            if (options.hideOriginalAction === true) {
                $(this).trigger("update").hide();
            }

            var active_action_set = $("span." + block_class, $(this).parent());

            if (active_action_set.length > 0) {
                active_action_set.show();
            } else {
                $(this).after("<span class='" + block_class + "'>" + action_set + "</span>");
            }

            if (options.expiresIn > 0) {
                timeout_id = setTimeout(function () {
                    $("span." + block_class, original_action.parent()).hide();
                    original_action.show();
                }, options.expiresIn * 1000);
            }

        });

        $(this).parent().on("click", "span." + action_class, function(e) {
            clearTimeout(timeout_id);
            $(this).parent().hide();

            e.preventDefault();
            
            var args = new Array();

            args[0]  = e;
            args[1]  = original_action;

            if ($(this).hasClass(confirm_class)) {

                if(options.showOriginalAction === true) {
                    original_action.show();
                }

                options.confirmCallback.apply(this, args);

                if(options.preventDefaultEvent === false && original_action.attr('href') !== '' && original_action.attr('href') != '#') {
                    location.href= original_action.attr('href');
                }

            } else {

                e.preventDefault();
                original_action.show();
                options.cancelCallback.apply(this, args);
            }

        });
    };

})(jQuery);
