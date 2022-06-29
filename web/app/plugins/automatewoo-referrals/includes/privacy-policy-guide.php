<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

defined( 'ABSPATH' ) || exit;

/**
 * @class Privacy_Policy_Guide
 * @since 2.0
 */
class Privacy_Policy_Guide extends \AutomateWoo\Privacy_Policy_Guide {


	/**
	 * @return string
	 */
	static function get_content() {
		ob_start();
		?>
<div class="wp-suggested-text">
	<h2><?php esc_html_e( 'What we collect and store', 'automatewoo-referrals' ); ?></h2>
	<h3><?php esc_html_e( 'Cookies', 'automatewoo-referrals' ); ?></h3>
	<p class="privacy-policy-tutorial"><?php esc_html_e( 'Refer A Friend uses one cookie if your store is configured to use link-based referrals. This cookie is added when a user clicks on a referral link and stores a code that is unique to the advocate who shared the link.', 'automatewoo-referrals' ); ?></p>
	<p><?php self::suggest_text_html(); ?> <?php esc_html_e( 'We use one cookie to enable the function of our customer referral program. This cookie stores a unique code that cannot identify you as a website visitor. This unique code can only identify the person who shared the coupon code or referral link.', 'automatewoo-referrals' ); ?></p>
	<p><?php printf( esc_html__( '%s - Enables the function of our referral program - Expires after 1 year', 'automatewoo-referrals' ), '<strong>aw_referral_key</strong>' ); ?></p>
	<h3><?php esc_html_e( 'IP Addresses', 'automatewoo-referrals' ); ?></h3>
	<p class="privacy-policy-tutorial"><?php esc_html_e( "Customers who use the referral program will have their IP address stored each time they share. Depending on your settings the IP address of referred customers may also be stored for the purpose of fraud prevention. This may not need to be included in your privacy policy since WooCommerce also tracks IP addresses.", 'automatewoo-referrals' ); ?></p>
	<h3><?php esc_html_e( 'Customer referral data', 'automatewoo-referrals' ); ?></h3>
	<p class="privacy-policy-tutorial"><?php esc_html_e( "Refer A Friend stores a record of all referred orders and creates unique referral codes that can identify each advocate. The plugin keeps a record each time the advocate shares via email. The invited emails may be anonymized depending on your settings.", 'automatewoo-referrals' ); ?></p>
	<p><?php self::suggest_text_html(); ?> <?php esc_html_e( 'To enable our customer referral program we may create and store unique codes that are linked to your account on our website. These codes will not be shared with anyone unless you choose to. We also keep a record when you share our store with a friend via email with our customer referral program. The email address is anonymized before it is stored.', 'automatewoo-referrals' ); ?></p>
	<p><?php esc_html_e( 'When an order is placed which is the result of a customer referral, we store a record of the details of this referral. Your IP address and other personal data supplied for the order may also be used to prevent fraud.', 'automatewoo-referrals' ); ?></p>
	<p><?php esc_html_e( 'Referral codes, referral details and email invite logs are retained until you request removal of your data.', 'automatewoo-referrals' ); ?></p>
	<h2><?php esc_html_e( 'What we share with others', 'automatewoo-referrals' ); ?></h2>
	<p class="privacy-policy-tutorial"><?php esc_html_e( "Refer A Friend allows advocates to share via email, Facebook or Twitter. Their personal data may be used in this process.", 'automatewoo-referrals' ); ?></p>
	<p><?php self::suggest_text_html(); ?> <?php esc_html_e( 'If you choose to use our customer referral program, your personal data, such as your name and your unique referral code, may be shared with those you choose to share with. This data may be shared via Facebook, Twitter or email if you click on the share buttons or submit the share via email form.', 'automatewoo-referrals' ); ?></p>
</div>
		<?php
		return ob_get_clean();
	}



}
