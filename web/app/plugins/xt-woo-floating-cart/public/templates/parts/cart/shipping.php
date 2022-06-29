<?php

/**
 * This file is used to markup the shipping form.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/cart-shipping.php.
 *
 * Available global vars:
 *
 * @var $package
 * @var $package_name
 * @var $available_methods
 * @var $chosen_method
 * @var $index
 * @var $show_package_details
 * @var $package_details
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$show_shipping_calculator = true;
$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';

$shipping_methods_display = xt_woofc_option('shipping_methods_display', 'dropdown');
?>

<tr class="woocommerce-shipping-totals shipping">
    <td colspan="2">
        <table class="shop_table shop_table_responsive">
            <tbody>
                <tr>
                    <th><?php echo wp_kses_post( $package_name ); ?></th>
                    <td data-title="<?php echo esc_attr( $package_name ); ?>">
                        <?php if ( $available_methods ) : ?>

                            <?php if($shipping_methods_display === 'radio'): ?>
                                <ul id="shipping_method" class="woocommerce-shipping-methods">
                                    <?php foreach ( $available_methods as $method ) : ?>
                                        <li>
                                            <?php
                                            if ( 1 < count( $available_methods ) ) {
                                                printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // WPCS: XSS ok.
                                            } else {
                                                printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // WPCS: XSS ok.
                                            }
                                            printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // WPCS: XSS ok.
                                            do_action( 'woocommerce_after_shipping_rate', $method, $index );
                                            ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="xt_woofc-select">
                                    <select id="shipping_method" class="woocommerce-shipping-methods shipping_method" data-index="<?php echo esc_attr($index);?>" name="shipping_method[<?php echo esc_attr($index);?>]">
                                        <?php foreach ( $available_methods as $method ) : ?>

                                            <?php
                                            $label = wc_cart_totals_shipping_method_label( $method );
                                            $label .= xtfw_ob_get_clean(function() use($method, $index) {
                                                do_action( 'woocommerce_after_shipping_rate', $method, $index );
                                            });

                                            printf( '<option id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />%5$s</option>', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), selected( $method->id, $chosen_method, false ), $label ); // WPCS: XSS ok.
                                            ?>

                                        <?php endforeach; ?>
                                    </select>
                                </span>
                            <?php endif ?>

                        <?php endif; ?>

                        <?php if ( $show_package_details ) : ?>
                            <?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
                        <?php endif; ?>

                    </td>
                </tr>
                <?php if ( $show_package_details || $show_shipping_calculator) : ?>
                <tr>
                    <td colspan="2" class="xt_woofc-shipping-footer" data-title="<?php echo esc_attr( $package_name ); ?>">
                        <p class="woocommerce-shipping-destination">

                            <span class="xt_woofc-shipping-location">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 349.661 349.661">
                                  <g>
                                    <path d="M174.831,0C102.056,0,42.849,59.207,42.849,131.981c0,30.083,21.156,74.658,62.881,132.485   c30.46,42.215,61.363,76.607,61.671,76.95l7.429,8.245l7.429-8.245c0.309-0.342,31.211-34.734,61.671-76.95   c41.725-57.828,62.881-102.402,62.881-132.485C306.812,59.207,247.605,0,174.831,0z M174.83,319.617   c-37.058-42.692-111.98-139.048-111.98-187.636C62.849,70.235,113.084,20,174.831,20s111.981,50.235,111.981,111.981   C286.812,180.54,211.888,276.915,174.83,319.617z"/>
                                    <circle cx="174.831" cy="131.982" r="49.696"/>
                                  </g>
                                </svg>
                            </span>
                            <span class="xt_woofc-shipping-info">
                                <?php if ( $available_methods ) :

                                    if ( $formatted_destination ) {
                                        echo esc_html( $formatted_destination );
                                        $calculator_text = esc_html__( 'Change address', 'woo-floating-cart' );
                                    } else {
                                        echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', esc_html__( 'Shipping options will be updated during checkout.', 'woo-floating-cart' ) ) );
                                    }

                                elseif ( ! $has_calculated_shipping || ! $formatted_destination ) :
                                    echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', esc_html__( 'Enter your address to view shipping options.', 'woo-floating-cart' ) ) );
                                else:
                                    // Translators: $s shipping destination.
                                    echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s', 'woo-floating-cart' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '.</strong>' ) ) );
                                    $calculator_text = esc_html__( 'Enter a different address', 'woo-floating-cart' );
                                endif; ?>
                            </span>
                            <?php if ( $show_shipping_calculator ) : ?>
                            <span class="xt_woofc-shipping-edit" title="<?php echo esc_attr($calculator_text);?>">
                                <svg viewBox="0 0 217.855 217.855" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m215.66 53.55l-51.353-51.354c-1.406-1.406-3.314-2.196-5.303-2.196s-3.897 0.79-5.303 2.196l-149.89 149.89c-1.35 1.352-2.135 3.166-2.193 5.075l-1.611 52.966c-0.063 2.067 0.731 4.069 2.193 5.532 1.409 1.408 3.317 2.196 5.303 2.196 0.076 0 0.152-1e-3 0.229-4e-3l52.964-1.613c1.909-0.058 3.724-0.842 5.075-2.192l149.89-149.89c2.928-2.929 2.928-7.678-1e-3 -10.607zm-158.39 147.79l-42.024 1.28 1.279-42.026 91.124-91.125 40.75 40.743-91.129 91.128zm101.74-101.73l-40.751-40.742 40.752-40.753 40.746 40.747-40.747 40.748z"/>
                                </svg>
                            </span>
                            <?php endif; ?>
                        </p>

                        <?php if ( $show_shipping_calculator ) : ?>
                            <?php woocommerce_shipping_calculator( $calculator_text ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </td>
</tr>
