/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************!*\
  !*** ./src/admin.js ***!
  \**********************/
jQuery(document).ready(function ($) {
    'use strict';
    let $product_screen = $('.edit-php.post-type-product'),
        $title_action = $product_screen.find('.page-title-action:first'),
        $blankslate = $product_screen.find('.woocommerce-BlankState');

    if (0 === $blankslate.length) {
        if (viWbeParams.url) {
            $title_action.after(`<a href="${viWbeParams.url}" class="page-title-action" style="display: inline-block">
                                    <i class="dashicons dashicons-media-spreadsheet" style="height: auto; font-size: 18px; line-height: 1rem"> </i>
                                    ${viWbeParams.text}
                                </a>`);
        }
    }

});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9hc3NldHMvLi9zcmMvYWRtaW4uanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQSw0Q0FBNEMsZ0JBQWdCO0FBQzVELHlHQUF5RyxpQkFBaUI7QUFDMUgsc0NBQXNDO0FBQ3RDO0FBQ0E7QUFDQTs7QUFFQSxDQUFDLEUiLCJmaWxlIjoiYWRtaW4uanMiLCJzb3VyY2VzQ29udGVudCI6WyJqUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uICgkKSB7XHJcbiAgICAndXNlIHN0cmljdCc7XHJcbiAgICBsZXQgJHByb2R1Y3Rfc2NyZWVuID0gJCgnLmVkaXQtcGhwLnBvc3QtdHlwZS1wcm9kdWN0JyksXHJcbiAgICAgICAgJHRpdGxlX2FjdGlvbiA9ICRwcm9kdWN0X3NjcmVlbi5maW5kKCcucGFnZS10aXRsZS1hY3Rpb246Zmlyc3QnKSxcclxuICAgICAgICAkYmxhbmtzbGF0ZSA9ICRwcm9kdWN0X3NjcmVlbi5maW5kKCcud29vY29tbWVyY2UtQmxhbmtTdGF0ZScpO1xyXG5cclxuICAgIGlmICgwID09PSAkYmxhbmtzbGF0ZS5sZW5ndGgpIHtcclxuICAgICAgICBpZiAodmlXYmVQYXJhbXMudXJsKSB7XHJcbiAgICAgICAgICAgICR0aXRsZV9hY3Rpb24uYWZ0ZXIoYDxhIGhyZWY9XCIke3ZpV2JlUGFyYW1zLnVybH1cIiBjbGFzcz1cInBhZ2UtdGl0bGUtYWN0aW9uXCIgc3R5bGU9XCJkaXNwbGF5OiBpbmxpbmUtYmxvY2tcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGkgY2xhc3M9XCJkYXNoaWNvbnMgZGFzaGljb25zLW1lZGlhLXNwcmVhZHNoZWV0XCIgc3R5bGU9XCJoZWlnaHQ6IGF1dG87IGZvbnQtc2l6ZTogMThweDsgbGluZS1oZWlnaHQ6IDFyZW1cIj4gPC9pPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAke3ZpV2JlUGFyYW1zLnRleHR9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9hPmApO1xyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcbn0pOyJdLCJzb3VyY2VSb290IjoiIn0=