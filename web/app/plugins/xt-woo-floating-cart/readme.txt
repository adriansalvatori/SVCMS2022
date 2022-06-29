=== XT Floating Cart for WooCommerce ===

Plugin Name: XT Floating Cart for WooCommerce
Contributors: XplodedThemes
Author: XplodedThemes
Author URI: https://www.xplodedthemes.com
Tags: woocommerce cart, side cart, fly to cart, fly cart, woocommerce side cart, mini cart, floating cart, cart, ajax cart, ajax add to cart, related products, upsell, cross-sell
Requires at least: 4.6
Tested up to: 5.9
Stable tag: 2.6.5
Requires PHP: 5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A modern Floating Cart / Side Cart for WooCommerce that will improve customer buying experience and increase conversions.

== Description ==

A modern Floating Cart / Side Cart for WooCommerce that will improve customer buying experience and increase conversions.

**What Would More Sales and Higher Conversions be Worth to You?**

Have you ever found yourself in a situation where site visitors add products to their cart, but then leave your store without finalizing their purchase?

I present to you Floating Cart for WooCommerce, the perfect solution that will improve customer buying experience and encourage them to click the “checkout” button.

<a target="_blank" href="https://demos.xplodedthemes.com/woo-floating-cart/">👉 **Official Demo**</a>

Your site will look more attractive, a cart icon with item count will always be visible on all pages, and a sliding cart will be visible when the customer clicks it.

**Video Overview**

[youtube https://www.youtube.com/watch?v=_1cRp4E7iEQ]

<a target="_blank" href="https://www.youtube.com/watch?v=_1cRp4E7iEQ">https://www.youtube.com/watch?v=_1cRp4E7iEQ</a>

**Demo**

<a target="_blank" href="https://demos.xplodedthemes.com/woo-floating-cart/">https://demos.xplodedthemes.com/woo-floating-cart/</a>

**Free Version**

- Unobstructive Floating Cart
- Fast add to cart
- Update quantities
- Remove product from cart
- Undo product removal
- Show max quantity reached msg
- Change Cart / Counter Position
- Responsive / Mobile Support

**Premium Features**

Fully customizable right from WordPress Customizer with Live Preview.

- All Free Features
- Live Preview Customizer
- Enable Fly To Cart animation
- Enable Coupons
- Enable Cart Totals
- Enable Total Savings
- Enable Express Checkout Form
- Enable Cart Menu Item
- Enable Auto Height
- Enable Suggested Products (Related / Cross-Sell / Upsell)
- Enable Free Shipping Bar
- Select Between Morph  Slide Animation
- Support variations, bundles & composites
- Clear / Restore entire cart in 1 click.
- Display product attributes within the cart
- Change Cart Width / Height
- Apply Google Fonts
- Custom Colors / Backgrounds
- Custom Icons (SVG / Image / Font Icons)
- Select from 11 loading spinner animations
- Exclude pages from displaying the cart
- Device Visibility options
- Ajax add to cart on Single Product pages
- Ajax add to cart within Quick View Modals
- Select between Checkout Or View Cart button
- Option to trigger the cart on Mouse Over
- Display Subtotal or Total
- RTL Support
- Automated Updates & Security Patches
- Priority Email & Help Center Support

**Compatible With <a target="_blank" href="https://xplodedthemes.com/products/woo-quick-view/">XT Quick View</a>**
**Compatible With <a target="_blank" href="https://xplodedthemes.com/products/woo-variation-swatches/">XT Variation Swatches</a>**
**Compatible With <a target="_blank" href="https://xplodedthemes.com/products/woo-variations-as-singles/">Woo Variations As Singles</a>**

**Translations**

- English - default

*Note:* All our plugins are localized / translatable by default. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful.

== Installation ==

Installing "Floating Cart for WooCommerce" can be done by following these steps:

1. Download the plugin from the customer area at "XplodedThemes.com" 
2. Upload the plugin ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

#### V.2.6.5 - 19.05.2022
- **fix**: When Checkout Form is enabled and Terms and conditions page is set within Customizer -> WooCommerce -> Checkout, the terms text and required checkbox are not visible, making it impossible to complete the checkout. Now fixed: https://d.pr/i/3Tkwdo
- **update**: XT Framework update

#### V.2.6.4 - 12.04.2022
- **fix**: Cart Menu Item: fix alignment option not being applied on mobile / tablet screens
- **fix**: [Bug] Safari Scrolling Issue - The cart stops scrolling after making any cart updates
- **update**: XT Framework update

#### V.2.6.3 - 03.03.2022
- **new**: **Pro** Shipping methods within Cart Totals / Checkout: Added an option to select between Radio buttons or Dropdowns https://d.pr/i/BsOiBg
- **new**: **Pro** Added option to hide 3rd party WooCommerce Notices that appear within the Floating Cart (Info & Success notices only) https://d.pr/i/QiAqIh
- **fix**: Minor css fixes
- **support**: Support for WooCommerce Minima and Maxima plugin.
- **fix**: Freemius Security Fix
- **update**: XT Framework update

#### V.2.6.2 - 05.02.2022
- **support**: Wrapped the **woocommerce_check_cart_items** action output with the **force_balance_tags()** wp function to make sure the returned html is always valid to avoid 3rd party hooks from messing up the cart display.

#### V.2.6.1 - 02.02.2022
- **fix**: Fixed missing dependency error with free version.

#### V.2.6.0 - 01.02.2022
- **new**: Customizer: Visibility options now included within the free version
- **new**: Customizer: **Body colors** are now **Global colors** and they are now included within the free version. This allows easier global color customizations. You can override some of these colors from other sections.
- **new**: Added 2 new JS api functions: **xt_woofc_scroll_to_top()** and **xt_woofc_scroll_to_bottom()**
- **new**: **Pro** Shipping methods within Cart Totals / Checkout form will be displayed in a dropdown instead of multiple radio buttons. **https://d.pr/i/LLK3AW**
- **new**: **Pro** Major Checkout Form Update. Changed the way the checkout is loaded and will handle checkout validation in a better way. For better compatibility with most themes and third party plugins, the checkout form is now embedded in an iframe. It won't be loaded below the cart list anymore, it will be displayed on its own, once the checkout button is clicked as a 2-step process.
- **new**: **Pro** New checkout thank you template. /parts/checkout/thank-you.php. This template will be displayed after placing an order using the embedded checkout form. https://d.pr/i/3FEQDN
- **new**: Apply **woocommerce_check_cart_items** native action to display woocommerce validation errors if any
- **new**: Added option to override WooCommerce notices colors within the Floating Cart. **https://d.pr/i/6ftu5J**
- **enhance**: **Pro** Enhanced the display of the shipping address within Cart Totals. Added Location & Edit icons. **https://d.pr/i/LLK3AW**
- **fix**: When the cart is empty and the trigger is hidden, adding an item to the cart for the first time, the fly to cart animation brings the image way below, before the trigger is even visible. Fixed by showing the trigger before starting the animation.
- **fix**: Multiple CSS fixes, enhancements
- **update**: **Pro** Updated template: /parts/cart/coupon-list.php
- **update**: **Pro** Updated template: /parts/cart/shipping.php
- **update**: **Pro** Removed deprecated template: /parts/checkout/checkout.php
- **update**: **Pro** Removed deprecated template: /parts/checkout/checkout-fields.php
- **update**: Major Code Update / Restructuring
- **update**: XT Framework update
- **support**: Better support for FlatSome theme
- **support**: Support WP v5.9

#### V.2.5.7 - 27.11.2021
- **fix**: Fixed conflict with PPOM plugin (woocommerce product addons)
- **update**: XT Framework update

#### V.2.5.6 - 27.11.2021
- **update**: XT Framework update

#### V.2.5.5 - 17.11.2021
- **new**: **Pro** Added a new filter hook **xt_woofc_custom_fields** to easily add product custom fields within the cart. Simply append meta fields to the array as meta_key / meta_label: https://d.pr/i/rFDqTr / https://d.pr/i/WA7off
- **update**: XT Framework update

#### V.2.5.4 - 16.11.2021
- **fix**: Fix error in backend
- **update**: XT Framework update

#### V.2.5.3 - 10.11.2021
- **update**: Modify plugin name to avoid trademark violation with WooCommerce
- **new**: **Pro** Added a new filter hook **xt_woofc_custom_attributes** to easily add custom product attributes within the cart. Simply append attribute slugs to the array: https://d.pr/i/yegGjD

#### V.2.5.1 - 09.11.2021
- **new**: **Pro** Added a new option to clear the entire cart in 1 click after customer confirmation. An undo link will also be displayed to be able to restore the entire cart once again. Simply enable the Clear All Icon within the header settings. Screencast: **https://d.pr/i/p43ybf**
- **new**: **Pro** The free shipping bar will now take into consideration taxes in case the woocommerce setting: "display prices during cart and checkout" is set to "Including Taxes".
- **fix**: Fixed issue with the "Keep visible on empty" not being applied properly since v2.5.0
- **fix**: Fixed javascript conflict with the "Disable cart page for WooCommerce" plugin
- **fix**: Minor css fixes
- **support**: Paypal Checkout Button will now support and require the download of **https://wordpress.org/plugins/woocommerce-paypal-payments/** instead of the old Paypal Checkout plugin: **https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/** which will stop being supported starting March 1st 2022. The new plugin should inherit the old plugin settings.
- **update**: Updated language file
- **update**: XT Framework update

#### V.2.5.0 - 28.10.2021
- **fix**: When deleting all products from the cart, the close button disappears even if the cart is still open.
- **fix**: Minor css fixes

#### V.2.4.9 - 16.09.2021
- **new**: **Pro** Added new option to hide Add To Cart buttons within Suggested Products
- **fix**: **Pro** Fixed layout issue with the Shipping Table within the native checkout page when "Cart Totals" is enabled.
- **fix**: Minor other fixes
- **fix**: Updated Xirki Customizer Library

#### V.2.4.8 - 15.09.2021
- **fix**: Disable within new Widget Block Editor to avoid conflicts
- **fix**: Fixed intermittent error: Undefined variable: is_bundle_item when updating item quantity
- **fix**: Fixed conflict within the new Block-based Widgets Editor in WordPress 5.8
- **support**: Support native "woocommerce_update_cart_validation" hook to validate item quantity change

#### V.2.4.6 - 30.08.2021
- **fix**: Fixed conflict with Stripe For WooCommerce plugin when Express Checkout Form is enabled.
- **fix**: Disable quantity input for bundled items.

#### V.2.4.4 - 10.08.2021
- **fix**: Fixed issue with single product ajax add to cart event not being tracked by some analytics plugins
- **support**: Support event tracking by analytics plugins. (add to cart / remove product from within the floating cart)
- **support**: Support PHP v8.x. Removed deprecated warnings.
- **update**: XT Framework update

#### V.2.4.2 - 29.07.2021
- **fix**: Fix issue with the "Have a coupon ?" toggle taking time to appear after adding an item to the cart
- **fix**: Fix outdated "shipping.php" template warning in woocommerce status report
- **fix**: Minor CSS Fixes

#### V.2.4.1 - 28.06.2021
- **support**: CodeCanyon version has been retired, and will no longer be maintained or updated. License migration required. More Info: **https://xplodedthemes.com/codecanyon-license-migration/**

#### V.2.4.0 - 16.06.2021
- **support**: Shipping Bar: Added support for **Flexible Shipping** plugin: https://fr.wordpress.org/plugins/flexible-shipping/
- **fix**: Shipping Calculator not populating states when country changes
- **fix**: Minor CSS Fixes
- **update**: Updated language file

#### V.2.3.9 - 15.06.2021
- **fix**: Cart Menu Item: Always Visible option was not applied correctly.
- **new**: Cart Menu Item / Shortcode Trigger: Added option: If cart is empty, Show icon only without counter or price.

#### V.2.3.8 - 14.06.2021
- **new**: Added new Shipping Bar feature. Display a message and a progress bar, letting customers know how much more they need to spend before getting free shipping.
- **new**: Cart Menu Item / Cart Trigger Shortcode: Customize options based on device screen.
- **new**: Cart Menu Item: Apply to different menus based on device screen.
- **fix**: Fixed issue with suggested products titles not being truncated properly
- **update**: Updated language file

#### V.2.3.7 - 09.06.2021
- **fix**: Fix issue with total savings not refreshing on quantity change, only when Cart Totals are disabled.
- **fix**: Minor CSS fixes applied to Cart Menu Item / Cart Trigger Shortcode
- **support**: Allow html to be displayed in product attributes to support third party plugins.

#### V.2.3.6 - 01.06.2021
- **update**: XT Framework update

#### V.2.3.5 - 01.06.2021
- **fix**: Minor Checkout / Login Form CSS fixes

#### V.2.3.4 - 20.05.2021
- **fix**: Fix error: Cannot use object of type stdClass as array. Happens with some themes.
- **update**: XT Framework update

#### V.2.3.3 - 07.05.2021
- **fix**: Fixed issue with Related Products not visible if the cart has only variable products.
- **fix**: Minor CSS Fixes

#### V.2.3.2 - 05.04.2021
- **support**: Better support / price display for subscription products.
- **fix**: Fix cart visibility issue caused by the z-index css property not being calculated properly when using the SG Optimizer plugin to minify CSS.

#### V.2.3.1 - 01.04.2021
- **new**: Trigger / Cart offset option is now available in free version
- **enhance**: Customizer - Reorganised Sections
- **fix**: Minor CSS Fixes

#### V.2.3.0 - 31.03.2021
- **fix**: Fix intermittent issue with paypal express button not showing the first time after adding to cart
- **support**: Multisite - Network Level License Management
- **update**: XT Framework update

#### V.2.2.4 - 23.03.2021
- **fix**: XT Framework update / fixes

#### V.2.2.3 - 22.03.2021
- **fix**: Fixed issue with customizer option values not being pulled correctly
- **update**: XT Framework update

#### V.2.2.2 - 03.03.2021
- **new**: Added option to change cart totals font size
- **new**: Allow displaying total savings (when enabled) even when cart totals are disabled.
- **update**: XT Framework update

#### V.2.2.0 - 02.03.2021
- **new**: Added option to change product thumbnail radius, border color and padding.
- **new**: Added option to change suggested product title color
- **fix**: Minor CSS Fixes
- **update**: XT Framework update

#### V.2.1.8 - 12.02.2021
- **new**: Added Modal Mode within the General Settings. When enabled, the cart will open as a modal in the middle of the screen.
- **fix**: Minor CSS Fixes

#### V.2.1.7 - 10.02.2021
- **new**: Updated the GSAP library
- **fix**: Fixed conflicts between the GSAP version loaded by the plugin and other versions.

#### V.2.1.6 - 28.01.2021
- **new**: Added new **Display Type** option for suggested products. Select between Slider or Rows.
- **update**: Removed "minicart.php" template file.
- **fix**: Fixed total saving calculation to include all discounts
- **fix**: Minor Customizer Fixes
- **fix**: Minor CSS Fixes

#### V.2.1.5 - 26.01.2021
- **fix**: Remove variation attributes from product title only within the floating cart, keep the default behaviour outside the cart.

#### V.2.1.3 - 23.01.2021
- **fix**: Fixed issue with Woo Add To Cart, ajax add to cart option not disabling correctly.

#### V.2.1.2 - 21.01.2021
- **new**: **Pro** Added new paypal express checkout integration. The button will nicely be displayed below the existing checkout button. See: https://d.pr/i/q5pnPb / Requires: https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/
- **new**: Added JS API function: xt_woofc_is_cart_empty()
- **fix**: Minor CSS Fixes
- **fix**: Prevent buy now buttons from adding to cart.
- **fix**: Prevent scrolling to top when clicking on radio buttons within the customizer.

#### V.2.1.1 - 20.01.2021
- **new**: **Pro** Added new option to display available coupon list that can be applied easily.
- **new**: **Pro** Added new option to hide main trigger if you only wish to trigger the cart using the API, shortcode, menu cart item or from custom selectors set above.
- **support**: Added better fallback support older browsers
- **enhance**: Remove variation info from product title (since recent woocommerce version). Only show attributes below the title if the option is enabled.
- **enhance**: Animate counter when count changes
- **enhance**: Minor cart totals / shipping css enhancement
- **enhance**: Throttle quantity update when clicking on the plus / minus buttons. This way, increasing quantity can be done faster while minimizing the number of update requests.
- **fix**: Javascript API Minor fixes
- **update**: Updated .pot language file

#### V.2.1.0 - 18.01.2021
- **support**: Support more third party quick views / modals that contains add to cart buttons
- **fix**: Minor CSS Fixes

#### V.2.0.9 - 14.01.2021
- **new**: Added option within **Woo Add To Cart** module to force fragment refresh after single add to cart. Enable this only if you notice that after adding a product to the cart, the totals are not correct due to conflicts with your theme or other plugins.

#### V.2.0.8 - 06.01.2021
- **fix**: Remove cart quantity form html filter hook to prevent 3rd party themes / plugins from modifying the look and feel of it and avoid conflicts

#### V.2.0.7 - 25.12.2020
- **fix**: Minor fix when debug mode is enabled, Undefined Variable: $output in quantity template file.

#### V.2.0.6 - 16.12.2020
- **fix**: Fixed issue with Germanized for WooCommerce Plugin not showing info for the first cart item.
- **fix**: Fix quantity input display with bundle / composite items.
- **fix**: Fix issue with single add to cart not adding anything only in customizer preview
- **new**: Added new hooks **xt_woofc_after_product_attributes** and **xt_woofc_after_product_attributes** that can be used to inject info before or after product attributes
- **support**: Added support for composite product in cart edit link.

#### V.2.0.5 - 14.12.2020
- **fix**: Minor CSS Fixes
- **enhance**: Always keep cart totals at the bottom.
- **enhance**: **Pro** Much better and smoother auto height
- **enhance**: Smoother delete / remove animation
- **update**: Updated list template **public/templates/parts/list.php**
- **update**: Updated product template **public/templates/parts/cart/list/product.php**
- **update**: Updated product title template **public/templates/parts/cart/list/product/title.php**
- **support**: Added support for **Germanized for WooCommerce** Plugin

#### V.2.0.4 - 11.12.2020
- **fix**: **Pro** Apply product title truncate option to the suggested product titles as well.
- **fix**: **Pro** Fixed error when adding the shortcode within an Elementor page.
- **support**: Support **WooCommerce Min Max Quantities** plugin
- **support**: Added support for Loco Translate by adding a loco.xml bundle config file.
- **update**: Updated translation file

#### V.2.0.3 - 09.12.2020
- **fix**: CSS fixes
- **fix**: Remove duplicated CSS variables in frontend.css
- **enhance**: Keep displaying error notices, hide other notices after couple of seconds
- **support**: Support WooCommerce v4.8
- **support**: Support WP 5.6

#### V.2.0.2 - 08.12.2020
- **support**: Added support for the latest jQuery modern version 3.5.1

#### V.2.0.1 - 07.12.2020
- **fix**: **Pro** Fixed missing css vars for the trigger shortcode
- **fix**: Fixed missing css vars for the default trigger image icon
- **fix**: Fixed issue with single add to cart form validation

#### V.2.0.0 - 05.12.2020
- **new**: **Pro** Added option to display squared or full height product images.
- **new**: **Pro** Added option to adjust product image width
- **new**: **Pro** Added option to truncate / un-truncate product title on overflow.
- **new**: **Pro** Added option to set extra trigger custom css selectors. You can now insert your existing theme cart icon selector or any other elements and they will act as a trigger.
- **new**: **Pro** Added manual product selection option for the suggested products.
- **new**: **Pro** A trash icon can now be selected instead of the remove text link
- **fix**: Fixed issue with quantity minus / plus buttons not working within the customizer.
- **fix**: Fixed issue with quantity validation before adding to cart.
- **fix**: Remove quantity input for bundled product items.
- **enhance**: Major customizer CSS changes. All customizer options are now native CSS Variables. Much leaner styles without css duplications.
- **enhance**: Restructure Customizer Product Item Options
- **enhance**: Added narrow display mode. The cart will automatically be in narrow mode if the cart width is below 300px;
- **support**: Dropped support for IE 11 since it does not support CSS variables and never will. https://xplodedthemes.com/we-abandoned-internet-explorer-11-and-so-should-you/
- **Update**: Updated translation file
- **update**: XT Framework update

#### V.1.8.8 - 27.11.2020
- **fix**: Fixed conflict with Divi Visual Builder.
- **support**: Support WooCommerce Extra Product Options. Single ajax add to cart will support adding all fields including file uploads.
- **support**: Support FB Pixel add to cart event tracking on mobile.
- **new**: Added more notices within the header. On add to cart, remove, restore, shipping info updated, coupon added / removed.
- **update**: XT Framework update

#### V.1.8.7 - 16.11.2020
- **fix**: Fix issue with single add to cart notices not being cleared properly on page reload.
- **update**: XT Framework update

#### V.1.8.6 - 05.11.2020
- **new**: Added option to increase the checkout form overall font size when the Checkout Form is enabled.
- **update**: XT Framework update

#### V.1.8.4 - 04.11.2020
- **fix**: Fix infinite load issue with the place order button when checkout form is enabled
- **support**: Woo Add To Cart Module: Fire the native **adding_to_cart** and **added_to_cart** events on single pages so other cart plugins can also listen to them and perform actions.

#### V.1.8.2 - 30.10.2020
- **fix**: Fix issue with touch events on mobile
- **support**: xootix side cart add to cart support

#### V.1.8.1 - 28.10.2020
- **enhance**: Smoother fade in cart loading
- **enhance**: Preload images before animating the Fly to cart image. Better experience if the image is too large, might take longer to load, by then the animation is already complete.
- **enhance**: Instant clicks on mobile. Replace all click events with touchstart on mobile which removes the extra 300ms delay.
- **update**: XT Framework update

#### V.1.8.0 - 26.10.2020
- **update**: Updated XT Framework.
- **fix**: Fix issue with Fly to cart image being out of proportion on some themes.
- **fix**: Minor CSS Fixes

#### V.1.7.9 - 23.10.2020
- **new**: **Pro** Added new **Cart Header Message** option. Can be used to display promo messages.
- **new**: **Woo Add To Cart** : On single product pages, make the scroll up to **Added to cart notice** optional!
- **new**: **Woo Add To Cart** : Enable Disable Ajax add to cart on shop or single product pages
- **new**: **Woo Add To Cart** : Added Redirect options (to cart, to checkout, to custom page) after add to cart.
- **fix**: Some themes were not showing the "Added to cart" notice on single pages when Ajax add to cart is enabled.
- **update**: Moved the option **Force showing add to cart button on shop page** to the **Woo Add To Cart** since it will be shared with other plugins.
- **update**: Moved the option **Hide "View Cart" Link after add to cart** to the **Woo Add To Cart** since it will be shared with other plugins.

#### V.1.7.8 - 21.10.2020
- **new**: Moved the Add To Cart spinner override settings to it's on **Add To Cart** section. Added custom loading spinners and confirmation checkmarks for the Add to cart button. These settings will now be shared between the **Quick View**, **Floating Cart** & **Variation Swatches** plugins.
- **new**: **Pro** Added new **Cart Product Price Display** option. Select between (Subtotal or Item Price). Item price will also display sale strikethrough prices

#### V.1.7.7 - 15.10.2020
- **support**: **Pro** Better theme support for the fly to cart animation

#### V.1.7.6 - 14.10.2020
- **fix**: **Pro** Cart Menu Item CSS Fixes
- **update**: XT Framework update
- **Update**: Updated translation file

#### V.1.7.5 - 10.10.2020
- **new**: **Pro** Added new option to re-order cart quantity field parts. (Minus / Plus Icons & Input field) https://d.pr/i/MZMkWT
- **enhance**: Auto increase quantity field width based on quantity

#### V.1.7.4 - 07.10.2020
- **new**: XT Framework System Status will now show info about the active theme as well as XT Plugin templates that are overridden by the theme. Similar to woocommerce, it will now be easier to know which plugin templates are outdated.

#### V.1.7.3.3 - 29.09.2020
- **fix**: Minor CSS fixes to the Menu Cart Icon

#### V.1.7.3.1 - 23.09.2020
- **fix**: Fixed minor issue with success message disappearing after applying a coupon code.

#### V.1.7.3 - 20.08.2020
- **new**: Added new "Cart Trigger Shortcode" option. Similar to the "Cart Menu Item" except it's a shortcode that can be inserted anywhere and can trigger the floating cart.

#### V.1.7.2 - 14.08.2020
- **Update**: Update Kirki Framework to v3.1.5
- **fix**: Fixed issue with customizer fields being hidden on WP v5.5

#### V.1.7.1 - 28.07.2020
- **support**: Added support for **WooCommerce SecureSubmit Gateway** plugin within the checkout form.

#### V.1.7.0 - 23.07.2020
- **new**: Cart menu item alignment option based on device screen.
- **fix**: Cart menu item styles not being loaded on native cart / checkout pages.
- **fix**: Fly to cart animation on the single product page is not pulling the correct image on some themes.

#### V.1.6.9 - 21.07.2020
- **update**: Updated translation files

#### V.1.6.8 - 16.07.2020
- **new**: **Pro** Added Total Savings option within Cart Extras Settings. When enabled, savings will be visible within cart totals.
- **new**: **Pro** Added a Badge option for the Cart Menu Item Counter instead of text only.
- **fix**: Fixed issue with main framework

#### V.1.6.7 - 01.07.2020
- **support**: Support Fly to cart animation for products built manually with Elementor Builder
- **new**: **Pro** Added option to override existing theme Add To Cart loading spinner. Choose between 11 different loading spinners and 2 checkmark icons for when a product has been added to the cart.

#### V.1.6.6 - 07.06.2020
- **fix**: Disable floating cart when Elementor Page Builder is active to prevent conflicts.
- **fix**: Fix issue with cart footer flickering on safari when removing products or increasing / decreasing quantities

#### V.1.6.5 - 12.05.2020
- **fix**: Fixed conflict with Avada theme
- **new**: Added option to force showing add to cart button on shop archive page. Some themes do not show it by default, ex: Divi theme.

#### V.1.6.4 - 01.05.2020
- **fix**: Fixed conflict with Divi Theme Page Builder

#### V.1.6.3 - 22.04.2020
- **new**: **Pro** Added Cart Menu Item option. Displays a cart icon with cart count, total price or both inside any menu. The menu item will also toggle the floating cart on click.
- **fix**: Minor CSS fixes

#### V.1.6.1 - 20.04.2020
- **support**: **Pro** Better theme support for the Fly to cart animation

#### V.1.6.0 - 18.04.2020
- **fix**: **Pro** Fixed fly to cart animation issue when Slider Revolution is active
- **update**: **Pro** Updated GSAP animation library.

#### V.1.5.9.1 - 12.04.2020
- **fix**: Minor CSS fixes

#### V.1.5.9 - 11.04.2020
- **fix**: Woocommerce functions is_cart() and is_checkout() always returning true because of the plugin causing issues with some themes that are relying on those functions. This should no longer be a problem.
- **fix**: Minor CSS fixes

#### V.1.5.8 - 07.04.2020
- **new**: **Pro** Added JS API function: xt_woofc_is_cart_open()
- **fix**: Minor CSS fixes
- **enhance**: Dynamically adjust the suggested products slider height based on each slide's height
- **enhance**: Better responsive display for suggested products.
- **support**: **Pro** Support XT Quick View so it can be triggered from within the cart suggested products.
- **support**: **Pro** Fly to cart animation should now support any theme

#### V.1.5.6 - 06.04.2020
- **fix**: Fixed issue cart auto height overflowing window height.

#### V.1.5.5 - 03.04.2020
- **fix**: Fixed issue with cart trigger not being completely visible on all browsers except chrome when Slide animation is enabled.
- **fix**: Fixed issue with media queries not being applied correctly in some cases
- **update**: XT Framework update v1.1.3, better media queries handling

#### V.1.5.4 - 27.03.2020
- **new**: **Pro** Added new cart appear animation option. You can now choose between the default "Morph" or the new "Slide" animation.
- **new**: **Pro** Added option to select between Pixels or Percent when setting cart Width and Height
- **new**: **Pro** Added new cart "Auto Height" option. The height will automatically adjust to fit the cart content as much as possible.
- **new**: **Pro** Added ability to set a different "Width, Height & Border Radius" for each screen view (Desktop, Tablet, Mobile)
- **new**: **Pro** Added ability to set a different "Border Radius" for when the cart is expanded for each screen view (Desktop, Tablet, Mobile)
- **new**: **Pro** Added the option to change the overlay color when the cart is active
- **new**: **Pro** Added a the option to enable a close button within the header (Useful when the cart animation is set to "Slide")
- **new**: Added javascript API function to toggle, open & close cart programmatically.
- **new**: Added the option to adjust the Z-Index for the quick view modal
- **enhance**: Smoother close / open icon transition
- **fix**: Minor fixes

#### V.1.5.3 - 18.02.2020
- **update**: XT Framework update / bug fixes

#### V.1.5.2 - 05.02.2020
- **fix**: Fixed issue with single product page "add to cart" event not being detected by tracking plugins such as Facebook Pixel / Google Analytics and others.

#### V.1.5.1 - 29.01.2020
- **fix**: Fixed issue with plugin TextDomain not being loaded properly
- **update**: Updated translation files

#### V.1.5.0 - 28.01.2020
- **fix**: Show grand total (including taxes, shipping, coupons, etc...) only if cart totals or checkout form are enabled. Otherwise, show cart subtotal instead.

#### V.1.4.9 - 10.01.2019
- **fix**: Fix intermittent issue with Suggested Products slider showing duplicated nav arrows and bullets

#### V.1.4.8 - 10.01.2019
- **fix**: Fix issue with Suggested Products customizer options not applying correctly
- **new**: **Pro** Added Suggested Products position option

#### V.1.4.7 - 10.01.2019
- **update**: XT Framework update.

#### V.1.4.6 - 09.01.2019
- **enhance**: Major backend changes. All XT Plugins will now appear under "XT Plugins" menu.
- **new**: **Pro** Added new option to show product suggestions within the cart (Up-Sell, Cross-Sell and Related products)
- **update**: Cart & Counter Position can now be changed within the free version.
- **update**: Cart Footer settings can now be changed within the free version.

#### V.1.4.5 - 22.11.2019
- **fix**: Minor Fixes

#### V.1.4.4 - 18.11.2019
- **Fix**: Fixed intermittent issue with page being reloaded after adding product to cart when either "Cart Totals" or "Checkout Form" options are enabled.
- **Fix**: Fixed issue with add_to_cart event not being picked up by google tag manager plugins / facebook pixel etc..
- **Fix**: Fixes caching issues
- **Update**: Template changes: /templates/parts/cart/list.php

#### V.1.4.3 - 30.10.2019
- **Fix**: Add proper hooks to min / max cart product quantities to support other plugins such as WooCommerce Min / Max Quantities
- **Fix**: Fix envato license validation issue
- **Update**: Template changes: /templates/parts/cart/list/product/quantity.php

#### V.1.4.2 - 29.10.2019
- **Fix**: Shipping Address fields showing when Virtual/Downloadable product is added in the cart when it's not supposed to.
- **Fix**: **Pro** When the checkout form is enabled within the Floating Cart, the native cart page was not showing the cart totals section.

#### V.1.4.1 - 29.10.2019
- **Support**: Support WordPress v5.2.4

#### V.1.4.0 - 23.10.2019
- **Update**: **Pro** Update customizer library to v3.0.45
- **Fix**: **Pro** Fixed issue with some customizer fields hidden on Flatsome theme and others.

#### V.1.3.9 - 11.10.2019
- **Fix**: Fixed javascript error / infinite loading when adding product to the cart and maximum quantity has been reached.
- **Fix**: Fixed add to cart on archive page not working on some custom themes.
- **Update**: Update Freemius SDK to v2.3.1

#### V.1.3.8 - 27.08.2019
- **Support**: Better support for **Woocommerce Product Addons** plugin. Fix issue with hidden required fields.

#### V.1.3.7.1 - 23.08.2019
- **Update**: Updated pot translation file

#### V.1.3.7 - 19.08.2019
- **Update**: **Pro** Updated customizer library to V3.0.44

#### V.1.3.6 - 09.07.2019
- **Update**: Removed unused libraries within the free version to be conform with WordPress directory guidelines

#### V.1.3.5 - 20.06.2019
- **Fix**: **Pro** Fixed issue with Checkout Form not appearing after adding the first product to the cart unless the page is reloaded.
- **Fix**: **Pro** Fixed issue with shipping method switching on native woocommerce cart / checkout pages

#### V.1.3.4 - 11.06.2019
- **New**: Adding "Please Wait..." loading text to checkout button once clicked. Or "Placing Order..." if Checkout form is enabled.
- **Enhance**: **Pro** Cart Totals: Faster shipping methods switching
- **Support**: Improved theme support

#### V.1.3.3 - 09.06.2019
- **Fix**: **Pro** Fixed couple of event firing issues with the checkout form and totals.
- **Fix**: **Pro** Fixed issue with shipping methods switching not updating totals
- **Enhance**: **Pro** Block cart body scrolling whenever a dropdown (countries, cities etc..) is open. This way, only the dropdown will be scrolling and nothing else.

#### V.1.3.2 - 01.06.2019
- **New**: **Pro** Added option to block main site page scroll when the floating cart is open
- **Support**: **Pro** **WooCommerce for PayPal by AngellEye** - Express Paypal button will be injected within cart totals / checkout form

#### V.1.3.1 - 22.05.2019
- **Fix**: **Pro** Fixed issue with totals not being triggered whenever a coupon is applied.
- **Fix**: Fixed javascript error on shop page.

#### V.1.3.0 - 21.05.2019
- **New**: **Pro** Added option to apply coupons within the floating cart
- **New**: **Pro** Added option to display totals (including tax, shipping, coupons etc..) below product list.
- **New**: **Pro** Added option to enable 1 step checkout form below product list. If enabled, totals will also be displayed.
- **New**: **Pro** Added option to display product SKU.
- **New**: Added option to initialize cart via ajax on page load.
- **Fix**: **Pro** Fixed customizer typography field issue with font variants.
- **Fix**: Fixed issue with WooCommerce error messages disappearing after a second.
- **Fix**: After product removal, the undo action should restore the product to the previous position within the cart
- **Support**: **Pro** Standard fonts can now be selected or can inherit theme fonts without loading google fonts.
- **Support**: Shop page ajax "add to cart" will also work with XT Variation Swatches plugin: http://xplodedthemes.com/products/woo-variation-swatches/
- **Enhance**: Shop page "add to cart" now supports ajax queue. No matter how many products are added to the cart and at what speed, they will all be added one after the other. This should pass any stress test.

#### V.1.2.9 - 04.04.2019
- **Fix**: **Pro** Fixed licensing issue
- **Fix**: Minor CSS Fixes

#### V.1.2.8 - 18.03.2019
- **Fix**: **Pro** Fixed Visibility "Hide on Pages" Dropdown to include all languages if WPML is enabled
- **Fix**: **Pro** Fix issue when validating **WooCommerce Extra Product Options** plugin required fields
- **Fix**: Fix issue with Min / Max quantities not respecting limits
- **Fix**: Minor CSS Fixes
- **Update**: **Pro** Updated Customizer Framework
- **New**: **Pro** Added option to Link / Unlink product to single product page
- **New**: **Pro** Added option to Hide Product Thumbs
- **New**: **Pro** Added new Device Visibility Option (Show on tablet and mobile)
- **Support**: Better WPML Support

#### V.1.2.7 - 11.03.2019
- **Fix**: Fixed conflict with WPML causing floating cart not to add items.

#### V.1.2.6 - 26.02.2019
- **Fix**: Fixed bug with customizer default values

#### V.1.2.5 - 26.02.2019
- **Update**: Update Freemius SDK to v2.2.4
- **Update**: **Pro** Update plugin updater to support php 7.3
- **New**: **Pro** Added option to set trigger / counter position and size based on breakpoint - Desktop, Tablet, Mobile
- **Fix**: **Pro** Fixed trigger counter position not being set within the customizer
- **Fix**: **Pro** Fixed issue with Customizer Link field

#### V.1.2.4 - 27.01.2019
- **Fix**: Force user to set a quantity if manually set to 0
- **Fix**: **Pro** Woocommerce "added to cart notice" on single product page is showing when reloading a page instead of showing directly after the animation
- **New**: **Pro** Added option to change trigger icon colors - Font Icon Only
- **Update**: Updated jquery.mobile library
- **Enhance**: Better composite / bundle product display

#### V.1.2.3 - 18.01.2019
- **Support**: Added support for validating required fields from Product Addon plugin: https://wordpress.org/plugins/woocommerce-product-addon/
- **Update**: Update Freemius SDK to v2.2.3

#### V.1.2.2 - 11.01.2019
- **Fix**: Fixed issue with license key migration

#### V.1.2.1 - 10.01.2019
- **Fix**: Fixed license migration issue

#### V.1.2.0 - 09.01.2019
- **New**: Added option to resize cart trigger & counter
- **Update**: Migrated Licensing / Billing System to Freemius
- **Fix**: Prefixed all plugin css classes and php function with "xt_" example: "woofc" becomes "xt_woofc", if you added custom css or have overridden plugin templates within your theme, make sure to add this prefix or else it will break

#### V.1.1.7 - 27.10.2018
- **Fix**: Fixed issue with some customizer color fields not showing
- **Fix**: Minor cart refresh fixes

#### V.1.1.6 - 24.09.2018
- **Fix**: Fixed intermittent issue with checkout button not being visible when adding first product

#### V.1.1.5 - 11.09.2018
- **Fix**: Prevent variable product from being added to cart if no option has been selected
- **Fix**: Minor Customizer Fixes

#### V.1.1.4 - 04.08.2018
- **Enhance**: Faster Ajax Load
- **Enhance**: Faster product add to cart on single product pages
- **Enhance**: Faster product quantity update, remove and undo

#### V.1.1.3 - 04.08.2018
- **Fix**: Fix issue with undo remove
- **Enhance**: Faster product add to cart on single product pages

#### V.1.1.2 - 25.03.2018
- **Fix**: Fix fly to cart animation to try and only animate the image without the whole container
- **Fix**: Fix conflict with 2 different serializeJSON libraries
- **Fix**: Fix javascript error on some shop pages, especially when using Dokan plugin
- **Fix**: Bypass add to cart buttons within gravity forms so they can work as usual.
- **Support**: Support Woo Product Table Plugin

#### V.1.1.1 - 15.01.2018
- **Support**: Support Woo Variations Table Plugin

#### V.1.1.0 - 25.11.2017
- **Support**: Wordpress v4.9 Customizer Support
- **Enhance**: Added Ajax queue system faster and more reliable Ajax requests

#### V.1.0.9.5 - 24.10.2017
- **Fix**: Fix compatibility issue with the X Theme
- **Support**: Better theme compatibility

#### V.1.0.9.4 - 11.10.2017
- **Fix**: Fix bundled items removal issue with Composite / Bundled products
- **Fix**: Replace deprecated functions
- **Enhance**: Disable the Single Add To Cart button until the floating cart is ready

#### V.1.0.9.3 - 25.09.2017
- **Fix**: Better compatibility with Composite and Bundled products

#### V.1.0.9.2 - 07.07.2017
- **Fix**: Fix multiple domain license check bug

#### V.1.0.9.1 - 21.06.2017
- **New**: Added option to show / hide bundled products for WooCommerce Product Bundles plugin
- **Support**: Support WooCommerce Product Bundles plugin

#### V.1.0.9 - 16.06.2017
- **Fix**: Fix product attributes display issue if attribute value is set to "Any"
- **Update**: Template changes: /templates/parts/cart/list/product.php
- **Update**: Template changes: /templates/parts/cart/list/product/variations.php

#### V.1.0.8.9 - 07.06.2017
- **Fix**: Fixed issue with product remove not updating subtotal on first try

#### V.1.0.8.8 - 20.04.2017
- **Fix**: Fixed deprecated function warnings caused by WooCommerce v3.0.x
- **Update**: Template changes: /templates/parts/cart/list/product/thumbnail.php

#### V.1.0.8.7 - 19.04.2017
- **Fix**: Fixed issue with products not adding to cart right after removing a product

#### V.1.0.8.6 - 18.04.2017
- **Fix**: Fixed issue with having to click twice to remove a product after adding it
- **Enhance**: Changed the Checkout label to Cart in go to cart mode

#### V.1.0.8.5 - 11.04.2017
- **Fix**: Fixed intermittent 502 error with ajax requests
- **Fix**: Fixed fly to cart animation from XT Quick View modal

#### V.1.0.8.4 - 10.04.2017
- **New**: Added option to display product attributes as a list or inline
- **New**: Added option to hide product attributes labels
- **New**: Added option to automatically open the cart after adding a product
- **Fix**: Fixed product attributes display on WooCommerce v3.x.x
- **Update**: Template changes: /templates/parts/cart/list/product/variations.php and /templates/parts/cart/list/product.php

#### V.1.0.8.3 - 10.04.2017
- **New**: Added option to resize default cart width and height
- **Enhance**: Better trigger icon animation when trigger position is set to Top Left or Top Right
- **Fix**: Fixed issue with some third party quick view modals add to cart button infinite loading.
- **Fix**: Fixed single post fly to cart animation on WooCommerce v3.x.x
- **Update**: Template changes: /templates/minicart.php and /templates/parts/cart/footer.php

#### V.1.0.8.2 - 04.04.2017
- **Fix**: Fixed issue with Remove / Undo cart total not updating sometimes.
- **Fix**: Fixed issue with local license being reset by it self.

#### V.1.0.8.1 - 16.03.2017
- **New**: Added color and typography customization options for the newly added error message
- **Support**: Supports WooCommerce Currency Converter Widget
- **Enhance**: Show error message within cart header whenever product quantity has reached stock limit or a minimum quantity is required.
- **Enhance**: Show woocommerce error messages within single product pages if ajax add to cart request failed for X reason

#### V.1.0.8 - 15.03.2017
- **New**: Auto sync cart content with third party mini cart plugins or within themes.
- **New**: Added global javascript function xt_woofc_refresh_cart() for developers to force cart refresh within plugins or themes.
- **Support**: Added support for XT Quick View Plugin
- **Support**: Added Support for caching plugins
- **Fix**: Fix cart issues on non woocommerce pages.

#### V.1.0.7 - 17.02.2017
- **New**: Sync cart with native WooCommerce cart page on Add, remove, update products
- **New**: Fly to cart animation now works on single product pages and within Quick View plugins
- **Support**: Added support for Yith Product Addons Plugin
- **Support**: Better support for third party plugins
- **Enhance**: Centralize template output functions
- **Fix**: Fixed customizer issue with checkout background color not being changed

#### V.1.0.6 - 11.02.2017
- **New**: Added Product Variations Support
- **New**: Added option to display product attributes within the cart
- **New**: Added option to select between Subtotal or Total to be displayed within the checkout button
- **New**: Added option to hide the WooCommerce “View Cart” link that appears after adding an item to cart
- **Enhance**: Better theme compatibility

#### V.1.0.5.1 - 30.01.2017
- **Fix**: Fixed weird issue with customizer fields visibility on WordPress 4.7.2
- **Fix**: Fixed issue with Customizer Typography fields not being displayed

#### V.1.0.5 - 26.01.2017
- **New**: Added option to change the checkout link to redirect to the cart page instead.
- **New**: Added option to trigger the cart on Mouse Over with optional delay
- **New**: Added Device Visibility options

#### V.1.0.4.1 - 19.01.2017
- **Fix**: Fixed minor bug with add to cart button when used with third party gift card plugins
- **Fix**: Fixed bug with customizer sections being hidden on some themes due to a conflict

#### V.1.0.4 - 10.01.2017
- **New**: Ajax Add to cart now supported on single shop pages and within product quick views
- **Enhance**: License System now allows the same purchase code to be valid within a multisite setup. 1 License: unlimited domains, subdomains, folders as long as as it is under a multisite.
- **Fix**: Fixed WooCommerce installation check notice

#### V.1.0.3 - 16.12.2016
- **New**: Now supports RTL
- **Fix**: Minor CSS fix with Fly to cart animation

#### V.1.0.2 - 30.11.2016
- **New**: Added 11 different loading spinners (optional)
- **New**: Added new Fly To Cart animation (optional)
- **New**: Added option to exclude pages from displaying the cart
- **Fix**: Allow html in product titles
- **Fix**: License validation Fix

#### V.1.0.1 - 02.11.2016
- **New**: Added hover background / color option to checkout button.
- **Enhance**: Replaced click with click event for faster taps on mobile.
- **Update**: Updated Translation Files
- **Fix**: Removed hover effect on mobile for faster response
- **Fix**: Fixed bug with checkout button typography options
- **Fix**: Minor CSS Fixes

#### V.1.0.0 - 01.11.2016
- **Initial**: Initial Version

