/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************!*\
  !*** ./src/settings.js ***!
  \*************************/
jQuery(document).ready(function ($) {
    'use strict';

    $('.vi-wbe-save-settings').on('click', function () {
        $(this).addClass('loading');
    });

    $('.villatheme-get-key-button').one('click', function (e) {
        let v_button = $(this);
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
            let obj = $.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id === item_id && (obj['data'][i].domain === main_domain || obj['data'][i].domain === '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain === main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    $('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                $('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                $(v_button).closest('form').submit();
            } else {
                $('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9hc3NldHMvLi9zcmMvc2V0dGluZ3MuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSxLQUFLOztBQUVMO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwrQkFBK0Isd0JBQXdCO0FBQ3ZEO0FBQ0E7QUFDQTtBQUNBO0FBQ0EseUJBQXlCO0FBQ3pCO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGFBQWE7QUFDYjtBQUNBO0FBQ0EsU0FBUztBQUNULEtBQUs7QUFDTCxDQUFDLEUiLCJmaWxlIjoic2V0dGluZ3MuanMiLCJzb3VyY2VzQ29udGVudCI6WyJqUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uICgkKSB7XHJcbiAgICAndXNlIHN0cmljdCc7XHJcblxyXG4gICAgJCgnLnZpLXdiZS1zYXZlLXNldHRpbmdzJykub24oJ2NsaWNrJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICQodGhpcykuYWRkQ2xhc3MoJ2xvYWRpbmcnKTtcclxuICAgIH0pO1xyXG5cclxuICAgICQoJy52aWxsYXRoZW1lLWdldC1rZXktYnV0dG9uJykub25lKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgbGV0IHZfYnV0dG9uID0gJCh0aGlzKTtcclxuICAgICAgICB2X2J1dHRvbi5hZGRDbGFzcygnbG9hZGluZycpO1xyXG4gICAgICAgIGxldCBkYXRhID0gdl9idXR0b24uZGF0YSgpO1xyXG4gICAgICAgIGxldCBpdGVtX2lkID0gZGF0YS5pZDtcclxuICAgICAgICBsZXQgYXBwX3VybCA9IGRhdGEuaHJlZjtcclxuICAgICAgICBsZXQgbWFpbl9kb21haW4gPSB3aW5kb3cubG9jYXRpb24uaG9zdG5hbWU7XHJcbiAgICAgICAgbWFpbl9kb21haW4gPSBtYWluX2RvbWFpbi50b0xvd2VyQ2FzZSgpO1xyXG4gICAgICAgIGxldCBwb3B1cF9mcmFtZTtcclxuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgICAgbGV0IGRvd25sb2FkX3VybCA9IHZfYnV0dG9uLmF0dHIoJ2RhdGEtZG93bmxvYWQnKTtcclxuICAgICAgICBwb3B1cF9mcmFtZSA9IHdpbmRvdy5vcGVuKGFwcF91cmwsIFwibXlXaW5kb3dcIiwgXCJ3aWR0aD0zODAsaGVpZ2h0PTYwMFwiKTtcclxuICAgICAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignbWVzc2FnZScsIGZ1bmN0aW9uIChldmVudCkge1xyXG4gICAgICAgICAgICAvKkNhbGxiYWNrIHdoZW4gZGF0YSBzZW5kIGZyb20gY2hpbGQgcG9wdXAqL1xyXG4gICAgICAgICAgICBsZXQgb2JqID0gJC5wYXJzZUpTT04oZXZlbnQuZGF0YSk7XHJcbiAgICAgICAgICAgIGxldCB1cGRhdGVfa2V5ID0gJyc7XHJcbiAgICAgICAgICAgIGxldCBtZXNzYWdlID0gb2JqLm1lc3NhZ2U7XHJcbiAgICAgICAgICAgIGxldCBzdXBwb3J0X3VudGlsID0gJyc7XHJcbiAgICAgICAgICAgIGxldCBjaGVja19rZXkgPSAnJztcclxuICAgICAgICAgICAgaWYgKG9ialsnZGF0YSddLmxlbmd0aCA+IDApIHtcclxuICAgICAgICAgICAgICAgIGZvciAobGV0IGkgPSAwOyBpIDwgb2JqWydkYXRhJ10ubGVuZ3RoOyBpKyspIHtcclxuICAgICAgICAgICAgICAgICAgICBpZiAob2JqWydkYXRhJ11baV0uaWQgPT09IGl0ZW1faWQgJiYgKG9ialsnZGF0YSddW2ldLmRvbWFpbiA9PT0gbWFpbl9kb21haW4gfHwgb2JqWydkYXRhJ11baV0uZG9tYWluID09PSAnJyB8fCBvYmpbJ2RhdGEnXVtpXS5kb21haW4gPT0gbnVsbCkpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHVwZGF0ZV9rZXkgPT0gJycpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHVwZGF0ZV9rZXkgPSBvYmpbJ2RhdGEnXVtpXS5kb3dubG9hZF9rZXk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdXBwb3J0X3VudGlsID0gb2JqWydkYXRhJ11baV0uc3VwcG9ydF91bnRpbDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIGlmIChzdXBwb3J0X3VudGlsIDwgb2JqWydkYXRhJ11baV0uc3VwcG9ydF91bnRpbCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdXBkYXRlX2tleSA9IG9ialsnZGF0YSddW2ldLmRvd25sb2FkX2tleTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1cHBvcnRfdW50aWwgPSBvYmpbJ2RhdGEnXVtpXS5zdXBwb3J0X3VudGlsO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChvYmpbJ2RhdGEnXVtpXS5kb21haW4gPT09IG1haW5fZG9tYWluKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB1cGRhdGVfa2V5ID0gb2JqWydkYXRhJ11baV0uZG93bmxvYWRfa2V5O1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICBpZiAodXBkYXRlX2tleSkge1xyXG4gICAgICAgICAgICAgICAgICAgIGNoZWNrX2tleSA9IDE7XHJcbiAgICAgICAgICAgICAgICAgICAgJCgnLnZpbGxhdGhlbWUtYXV0b3VwZGF0ZS1rZXktZmllbGQnKS52YWwodXBkYXRlX2tleSk7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgdl9idXR0b24ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKTtcclxuICAgICAgICAgICAgaWYgKGNoZWNrX2tleSkge1xyXG4gICAgICAgICAgICAgICAgJCgnPHA+PHN0cm9uZz4nICsgbWVzc2FnZSArICc8L3N0cm9uZz48L3A+JykuaW5zZXJ0QWZ0ZXIoXCIudmlsbGF0aGVtZS1hdXRvdXBkYXRlLWtleS1maWVsZFwiKTtcclxuICAgICAgICAgICAgICAgICQodl9idXR0b24pLmNsb3Nlc3QoJ2Zvcm0nKS5zdWJtaXQoKTtcclxuICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICQoJzxwPjxzdHJvbmc+IFlvdXIga2V5IGlzIG5vdCBmb3VuZC4gUGxlYXNlIGNvbnRhY3Qgc3VwcG9ydEB2aWxsYXRoZW1lLmNvbSA8L3N0cm9uZz48L3A+JykuaW5zZXJ0QWZ0ZXIoXCIudmlsbGF0aGVtZS1hdXRvdXBkYXRlLWtleS1maWVsZFwiKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSk7XHJcbn0pOyJdLCJzb3VyY2VSb290IjoiIn0=