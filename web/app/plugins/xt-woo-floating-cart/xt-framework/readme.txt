=== XT Framework ===

Plugin Name: XT Framework
Contributors: XplodedThemes
Author: XplodedThemes
Author URI: https://www.xplodedthemes.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

XT Framework is used as the core of all XT Plugins. It combines all the common functionalities of all plugins.

== Description ==

XT Framework is used as the core of all XT Plugins. It combines all the common functionalities of all plugins.
It takes care of basic plugin hooks, admin tabs, customizer fields, plugin settings, localization, admin & frontend notices, central system status, migrations, asset loading and much more.

== Changelog ==

#### V.2.0.8 - 19.05.2022
- **fix**: Recommended Plugins minor fixes

#### V.2.0.7 - 23.03.2022
- **fix**: Minor fixes

#### V.2.0.6 - 03.03.2022
- **fix**: Freemius Security Fix
- **update**: Updated Freemius SDK to 2.4.3

#### V.2.0.5 - 09.02.2021
- **fix**: Minor fixes
- **fix**: Deduplicate backend & frontend notices.

#### V.2.0.3 - 03.01.2021
- **css**: CSS Map fixes
- **enhancements**: Minor Enhancements

#### V.2.0.2 - 15.12.2021
- **update**: Minor updates
- **enhancements**: Speed Enhancement

#### V.2.0.1 - 08.12.2021
- **update**: Minor updates

#### V.2.0.0 - 27.11.2021
- **fix**: Fixed intermittent error: Undefined property: stdClass::$plugin
- **fix**: Minor fixes

#### V.1.9.9 - 24.11.2021
- **fix**: Minor fixes

#### V.1.9.8 - 16.11.2021
- **fix**: Fix error in backend

#### V.1.9.7 - 10.11.2021
- **fix**: Data Sanitization/Escaping

#### V.1.9.6 - 10.11.2021
- **update**: Updated Xirki Customizer Library to v3.1.9

#### V.1.9.5 - 09.11.2021
- **fix**: Add To Cart Module: Fixed javascript conflict with the "Disable cart page for WooCommerce" plugin

#### V.1.9.4 - 04.11.2021
- **new**: Setting Fields: Added 'before' and 'after' options that can be used to render anything before or after settings fields.
- **new**: Setting Fields: Added new heading field

#### V.1.9.3 - 16.09.2021
- **update**: Updated Xirki Customizer Library to v3.1.6

#### V.1.9.2 - 15.09.2021
- **fix**: Fixed fixes

#### V.1.9.1 - 10.08.2021
- **fix**: Fixed issue with single product ajax add to cart event not being tracked by some analytics plugins

#### V.1.9.0 - 04.08.2021
- **fix**: Minor customizer fixes

#### V.1.8.9 - 08.07.2021
- **fix**: Minor customizer fixes

#### V.1.8.8 - 06.07.2021
- **fix**: Fixed caching issue with System Status info.

#### V.1.8.7 - 02.07.2021
- **support**: Added support for future free XT plugins
- **fix**: Minor CSS fixes for admin notices

#### V.1.8.6 - 28.06.2021
- **support**: CodeCanyon version has been retired, and will no longer be maintained or updated. License migration required. More Info: https://xplodedthemes.com/codecanyon-license-migration/

#### V.1.8.5 - 10.06.2021
- **enhance**: Plugin dependency check, display local link to download plugin instead of an external link.

#### V.1.8.4 - 02.06.2021
- **fix**: Load the latest framework version if more than one XT plugin is enabled with different framework versions. However, only do so if the plugins are no network activated to avoid infinite loop issue.

#### V.1.8.3 - 01.06.2021
- **fix**: Fixed issue with CodeCanyon license validator not caching properly

#### V.1.8.2 - 12.05.2021
- **fix**: Fix conflict with CTX Feed plugin

#### V.1.8.1 - 10.05.2021
- **fix**: Minor fixes

#### V.1.8.0 - 17.04.2021
- **fix**: Color Library: Fixed issue with RGB color string Conversion to Array when providing an empty string.

#### V.1.7.9 - 02.04.2021
- **fix**: Fix undefined functions error

#### V.1.7.8 - 01.04.2021
- **fix**: Minor CSS Fixes
- **update**: Updated framework language pot file

#### V.1.7.7 - 30.03.2021
- **support**: Multisite - Network Level License Management with option to delegate management to sub site admins

#### V.1.7.6 - 23.03.2021
- **fix**: System Status - Minor Fixes
- **fix**: Recommended Plugins - Fixed errors when retrieving plugin details.

#### V.1.7.5 - 22.03.2021
- **fix**: System status returning incorrect php version.
- **new**: Admin Settings now supports previews
- **enhance**: Admin Settings now uses ajax for saving
- **enhance**: Admin Tabs - Better mobile support

#### V.1.7.4 - 04.03.2021
- **enhance**: Optimize admin tabs on mobile
- **fix**: Minor CSS Fixes

#### V.1.7.3 - 03.03.2021
- **fix**: Fix issue with system status tooltips
- **fix**: Fix issue with color pickers
- **fix**: Minor Fixes

#### V.1.7.0 - 02.03.2021
- **enhance**: Minor optimizations
- **update**: Updated customizer library
- **update**: Updated settings library

#### V.1.6.9 - 08.02.2021
- **enhance**: Code restructuring and optimizations

#### V.1.6.4 - 28.01.2021
- **enhance**: Minor enhancements

#### V.1.6.3 - 26.01.2021
- **fix**: Minor fixes

#### V.1.6.2 - 23.01.2021
- **fix**: Fixed issue with Woo Add To Cart, ajax add to cart option not disabling correctly.

#### V.1.6.1 - 22.01.2021
- **fix**: Prevent buy now buttons from adding to cart.
- **support**: Minor Fixes

#### V.1.6.0 - 21.01.2021
- **fix**: Prevent scrolling to top when clicking on radio buttons within the customizer.
- **support**: Minor CSS Fixes

#### V.1.5.9 - 19.01.2021
- **support**: Added better fallback support older browsers

#### V.1.5.8 - 18.01.2021
- **support**: Support more third party quick views / modals that contains add to cart buttons

#### V.1.5.7 - 14.01.2021
- **new**: Added option to force fragment refresh after single add to cart. Enable this only if you notice that after adding a product to the cart, the totals are not correct due to conflicts with your theme or other plugins.

#### V.1.5.6 - 06.01.2021
- **enhance**: Show option to cancel subscription on plugin deactivation

#### V.1.5.4 - 16.12.2020
- **fix**: Fix issue with single add to cart not adding anything only in customizer preview
- **support**: Added support for composite product in cart edit link.

#### V.1.5.3 - 15.12.2020
- **support**: Support WP 5.6

#### V.1.5.0 - 10.12.2020
- **fix**: Added pot language file for the XT Framework so it can be translated separately

#### V.1.4.9 - 09.12.2020
- **fix**: Minor CSS Fixes

#### V.1.4.8 - 07.12.2020
- **fix**: Fixed issue with single add to cart form validation

#### V.1.4.7 - 05.12.2020
- **fix**: Minor plugin notices fixes
- **fix**: Fixed issue with quantity validation before adding to cart.

#### V.1.4.6 - 27.11.2020
- **support**: Support WooCommerce Extra Product Options. Single ajax add to cart will support adding all fields including file uploads.
- **support**: Support FB Pixel add to cart event tracking on mobile.

#### V.1.4.4 - 16.11.2020
- **fix**: Fix issue with single add to cart notices not being cleared properly on page reload.

#### V.1.4.4 - 05.11.2020
- **fix**: Minor Fixes

#### V.1.4.0 - 04.11.2020
- **new**: Added customizer function to retrieve all registered image sizes.
- **fix**: Minor Fixes

#### V.1.3.9 - 02.11.2020
- **support**: Woo Add To Cart Module: Fire the native **adding_to_cart** and **added_to_cart** events on single pages so other cart plugins can also listen to them and perform actions.

#### V.1.3.8 - 30.10.2020
- **fix**: Fix issue with touch events on mobile
- **fix**: Minor CSS Fixes
- **update**: Jquery Touch Library

#### V.1.3.7 - 26.10.2020
- **fix**: Fix issue with the "Ajax add to cart" option not being applied correctly
- **new**: Added this changelog tab within XT Framework Panel

#### V.1.3.6 - 23.10.2020
- **new**: **Pro** Added new **Cart Header Message** option. Can be used to display promo messages.
- **new**: **Woo Add To Cart** : On single product pages, make the scroll up to **Added to cart notice** optional!
- **new**: **Woo Add To Cart** : Enable Disable Ajax add to cart on shop or single product pages
- **new**: **Woo Add To Cart** : Added Redirect options (to cart, to checkout, to custom page) after add to cart.
- **fix**: Some themes were not showing the "Added to cart" notice on single pages when Ajax add to cart is enabled.
- **update**: Moved the option **Force showing add to cart button on shop page** to the **Woo Add To Cart** since it will be shared with other plugins.
- **update**: Moved the option **Hide "View Cart" Link after add to cart** to the **Woo Add To Cart** since it will be shared with other plugins.

#### V.1.3.5 - 21.10.2020
- **new**: Added shared module **Woo Add To Cart**

#### V.1.3.4 - 14.10.2020
- **fix**: Minor fixes

#### V.1.3.3 - 07.10.2020
- **new**: XT Framework System Status will now show info about the active theme as well as XT Plugin templates that are overridden by the theme. Similar to woocommerce, it will now be easier to know which plugin templates are outdated.

#### V.1.3.2 - 14.08.2020
- **Update**: Update Kirki Framework to v3.1.5
- **fix**: Fixed issue with customizer fields being hidden on WP v5.5

#### V.1.3.1 - 16.07.2020
- **fix**: Minor fixes

#### V.1.3.0 - 03.04.2020
- **update**: XT Framework update v1.1.3, better media queries handling

#### V.1.1.1 - 18.02.2020
- **update**: XT Framework update
- **fix**: Bug fixes

#### V.1.1.0 - 09.01.2019
- **Initial**: Initial Version
- **enhance**: All XT Plugins will now appear under "XT Plugins" menu.