<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Settings_Tab_Abstract;
use AutomateWoo\Fields_Helper;
use AutomateWoo\Integrations;
use AutomateWoo\Events;
use AutomateWoo\Emails;

defined( 'ABSPATH' ) || exit;

/**
 * @class Settings_Tab
 */
class Settings_Tab extends Admin_Settings_Tab_Abstract {

	/** @var bool */
	public $show_tab_title = false;

	/** @var string  */
	public $prefix = 'aw_referrals_';

	function __construct() {
		$this->id   = 'referrals';
		$this->name = __( 'Refer A Friend', 'automatewoo-referrals' );
	}


	function load_settings() {

		$this->section_start( 'main', __( 'Referral campaign options', 'automatewoo-referrals' ) );

		$this->add_setting(
			'enabled',
			[
				'title'    => __( 'Enable referrals', 'automatewoo-referrals' ),
				'type'     => 'checkbox',
				'autoload' => true,
			]
		);

		$this->add_setting(
			'type',
			[
				'title'    => __( 'Share type', 'automatewoo-referrals' ),
				'desc'     => __( 'Choose whether you would like to offer a coupon incentive in your referral campaign. PLEASE NOTE: If you choose link based you must adjust the default text below to suit.', 'automatewoo-referrals' ),
				'type'     => 'select',
				'autoload' => true,
				'options'  => [
					'coupon' => __( 'Coupon based', 'automatewoo-referrals' ),
					'link'   => __( 'Link based', 'automatewoo-referrals' )
				]
			]
		);

		$this->add_setting(
			'referrals_page',
			[
				'title'    => __( 'Share page', 'automatewoo-referrals' ),
				'desc'     => __( 'Ensure you add the shortcode <code>[automatewoo_referrals_page]</code> to the content of the page you select.', 'automatewoo-referrals' ),
				'tooltip'  => __( 'This is the main page that advocates can use to refer people to your store. This should not be set to the checkout as the referral widget will automatically be displayed on the order recieved page.', 'automatewoo-referrals' ),
				'type'     => 'single_select_page',
				'class'    => 'wc-enhanced-select-nostd',
				'required' => true
			]
		);

		$this->add_setting(
			'social_share_url',
			[
				'title'       => __( 'Share URL', 'automatewoo-referrals' ),
				'tooltip'     => __( 'This URL is used when an advocate shares via email, Facebook or Twitter. If you are using link based referrals then the tracking parameter will be automatically added to this URL. You can also add analytics tracking parameters to the URL.', 'automatewoo-referrals' ),
				'placeholder' => home_url( '/' ),
				'type'        => 'text',
			]
		);

		$this->add_setting(
			'advocate_must_paying_customer',
			[
				'title' => __( "Limit sharing to paying customers", 'automatewoo-referrals' ),
				'desc'  => __( "If unchecked any customer with an account can share.", 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'auto_approve',
			[
				'title'   => __( 'Auto-approval referrals', 'automatewoo-referrals' ),
				'tooltip' => __( "Referrals must be approved before the advocate is rewarded. Automatically approve referrals once the related order is paid or marked as complete. Please note referrals that could be fraudulent will never be auto-approved.", 'automatewoo-referrals' ),
				'type'    => 'select',
				'options' => [
					'completed' => __( 'On order completed', 'automatewoo-referrals' ),
					'paid'      => __( 'On order paid', 'automatewoo-referrals' ),
					'no'        => __( 'Disable and approve referrals manually', 'automatewoo-referrals' ),
				]

			]
		);

		if ( Integrations::is_subscriptions_active() ) {
			$this->add_setting(
				'use_credit_on_subscription_renewals',
				[
					'title' => __( 'Use store credit on subscription renewal payments', 'automatewoo-referrals' ),
					'desc'  => sprintf(
						__( 'If checked, any store credit earned by a subscriber will be automatically applied to WooCommerce Subscription renewal payments. This will only work with payment gateway extensions that support %1$srecurring total modifications%2$s.', 'automatewoo-referrals' ),
						'<a href="https://docs.woocommerce.com/document/subscriptions/payment-gateways/#section-3" target="_blank">',
						'</a>'
					),
					'type'  => 'checkbox',
				]
			);
		}

		$this->section_end( 'main' );


		$this->section_start( 'privacy', __( 'Privacy', 'automatewoo-referrals' ) );

		$this->add_setting(
			'anonymize_invited_emails',
			[
				'title' => __( 'Anonymize invited email data', 'automatewoo-referrals' ),
				'desc'  => __( "GDPR requires consent to be given before personal data is stored. Since it is impossible for invited emails to give consent it is recommended to anonymize these emails. Once enabled any existing stored emails will be anonymized.", 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'hide_referred_customer_data_from_advocates',
			[
				'title' => __( 'Hide referred customer data from advocates', 'automatewoo-referrals' ),
				'desc'  => __( 'If checked, advocates will not be able to view personal data of the customers they refer. If unchecked, the referred customer\'s full name will be shown in the referral account tab.', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->section_end( 'privacy' );


		$this->section_start(
			'offer',
			__( 'Referral offer (for the friend)', 'automatewoo-referrals' ),
			__( 'The coupon discount that advocates can offer to their friends. This coupon will only be valid for new customers. By default referral coupons expire after 4 weeks which means that a single advocate may own many different referral coupons at one time. If you would like advocates to have a single coupon for all time set the <b>Coupon Expiry</b> to 0.', 'automatewoo-referrals' )
		);

		$this->add_setting(
			'offer_type',
			[
				'title'   => __( 'Offer type', 'automatewoo-referrals' ),
				'type'    => 'select',
				'options' => AW_Referrals()->get_offer_types(),
			]
		);

		$expiry_tooltip = __( "Make this field blank if you do not want coupons to expire.", 'automatewoo-referrals' );

		$this->add_setting(
			'offer_amount',
			[
				'title'             => __( 'Offer amount', 'automatewoo-referrals' ),
				'type'              => 'number',
				'custom_attributes' => $this->get_price_setting_attrs()
			]
		);

		$this->add_setting(
			'offer_coupon_expiry',
			[
				'title'       => __( 'Coupon expiry', 'automatewoo-referrals' ),
				'desc'        => __( 'weeks', 'automatewoo-referrals' ),
				'tooltip'     => $expiry_tooltip,
				'type'        => 'number',
				'placeholder' => __( 'unlimited', 'automatewoo-referrals' )
			]
		);

		$this->section_end( 'offer' );


		$this->section_start(
			'link',
			__( 'Link options', 'automatewoo-referrals' ),
			__( 'By default share links expire after 4 weeks which means that a single advocate may own many different unique share links at one time. If you would like advocates to have a single share link for all time set the <b>Share Link Expiry</b> to 0.', 'automatewoo-referrals' )
		);

		$this->add_setting(
			'share_link_parameter',
			[
				'title'   => __( 'Share link parameter', 'automatewoo-referrals' ),
				'tooltip' => sprintf(
					__( 'This parameter is used when generating unique share links e.g. %s', 'automatewoo-referrals' ),
					home_url() . '?[link-parameter]=[advocate-share-key]'
				),
				'type'    => 'text',
			]
		);

		$this->add_setting(
			'share_link_expiry',
			[
				'title'       => __( 'Share link expiry', 'automatewoo-referrals' ),
				'desc'        => __( 'weeks', 'automatewoo-referrals' ),
				'tooltip'     => $expiry_tooltip,
				'type'        => 'number',
				'placeholder' => __( 'unlimited', 'automatewoo-referrals' )
			]
		);

		$this->section_end( 'link' );


		$this->section_start(
			'referral-requirements',
			__( 'Referral requirements', 'automatewoo-referrals' ),
			__( 'These are the requirements of referred purchases, if these are not met the advocate does not receive a reward and the friend does not get a discount.', 'automatewoo-referrals' )
		);

		$this->add_setting(
			'offer_min_purchase',
			[
				'title'             => __( 'Minimum purchase amount', 'automatewoo-referrals' ),
				'tooltip'           => __( 'The minimum purchase amount that the referral offer is valid for.', 'automatewoo-referrals' ),
				'type'              => 'number',
				'custom_attributes' => $this->get_price_setting_attrs()
			]
		);

		$this->add_setting(
			'referral_required_categories',
			[
				'title'       => __( 'Product categories', 'automatewoo-referrals' ),
				'type'        => 'multiselect',
				'placeholder' => __( 'All categories', 'automatewoo-referrals' ),
				'options'     => Fields_Helper::get_categories_list(),
				'tooltip'     => __( 'Only allow referrals for orders that contain at least one product from one of these categories.', 'automatewoo-referrals' ),
			]
		);

		$this->add_setting(
			'referral_excluded_categories',
			[
				'title'       => __( 'Excluded categories', 'automatewoo-referrals' ),
				'type'        => 'multiselect',
				'placeholder' => __( 'No categories', 'automatewoo-referrals' ),
				'options'     => Fields_Helper::get_categories_list(),
				'tooltip'     => __( "Don't allow referrals for orders that only contain products from these categories.", 'automatewoo-referrals' ),
			]
		);

		$this->section_end( 'referral-requirements' );


		$this->section_start(
			'reward',
			__( 'Referral reward (for the advocate)', 'automatewoo-referrals' ),
			__(
				'The reward given to the advocate for each time they successfully refer a customer. This reward is only granted for the friends first purchase. '
				. 'If you would like to notify advocates each time they receive a referral reward you should create a workflow with the <strong>New Referral</strong> trigger.',
				'automatewoo-referrals'
			)
		);

		$this->add_setting(
			'reward_type',
			[
				'title'   => __( 'Reward type', 'automatewoo-referrals' ),
				'type'    => 'select',
				'options' => AW_Referrals()->get_reward_types(),
				'tooltip' => __( 'By selecting no reward you can instead reward them by using the AutomateWoo referral triggers and for example you could generate a coupon for the advocate.', 'automatewoo-referrals' ),
			]
		);

		$this->add_setting(
			'reward_amount',
			[
				'title'             => __( 'Reward amount', 'automatewoo-referrals' ),
				'type'              => 'number',
				'custom_attributes' => $this->get_price_setting_attrs(),
			]
		);

		$this->add_setting(
			'reward_min_purchase',
			[
				'title'             => __( 'Minimum purchase amount', 'automatewoo-referrals' ),
				'tooltip'           => __( 'The minimum purchase amount required for store credit to be used.', 'automatewoo-referrals' ),
				'type'              => 'number',
				'custom_attributes' => $this->get_price_setting_attrs(),
			]
		);

		$this->section_end( 'reward' );

		$this->add_referral_limit_section();

		$this->section_start(
			'widget',
			__( 'Share widget', 'automatewoo-referrals' ),
			sprintf(
				__( 'The share widget is a mini version of the share page that can added to the order confirmation page, order emails and be inserted in a workflow email with %s. Please note that customers will be prompted to create an account before they can refer their friends.', 'automatewoo-referrals' ),
				'<code>{{ customer.referral_widget }}</code>'
			)
		);

		$this->add_setting(
			'widget_on_order_confirmed',
			[
				'title'   => __( 'Show widget on order confirmation page', 'automatewoo-referrals' ),
				'type'    => 'select',
				'options' => [
					'bottom' => __( 'Bottom Of Page', 'automatewoo-referrals' ),
					'top'    => __( 'Top Of Page', 'automatewoo-referrals' ),
					'no'     => __( 'Do Not Display', 'automatewoo-referrals' ),
				]
			]
		);

		$this->add_setting(
			'widget_on_order_emails',
			[
				'title' => __( 'Show widget on order emails', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'widget_on_new_account_email',
			[
				'title' => __( 'Show widget on new account email', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'widget_heading',
			[
				'title' => __( 'Widget heading', 'automatewoo-referrals' ),
				'type'  => 'text',
			]
		);

		$this->add_setting(
			'widget_text',
			[
				'title'             => __( 'Widget paragraph text', 'automatewoo-referrals' ),
				'type'              => 'textarea',
				'custom_attributes' => [
					'rows' => 5
				]
			]
		);

		$this->section_end( 'widget' );


		$this->section_start( 'social', __( 'Social sharing', 'automatewoo-referrals' ) );

		$this->add_setting(
			'enable_facebook_share',
			[
				'title' => __( 'Enable sharing via Facebook', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'enable_twitter_share',
			[
				'title' => __( 'Enable sharing via Twitter', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'enable_whatsapp_share',
			[
				'title' => __( 'Enable sharing via WhatsApp', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'social_share_text',
			[
				'title'   => __( 'Default share text', 'automatewoo-referrals' ),
				'type'    => 'textarea',
				'tooltip' => __( "This is the default text used when an advocate shares via Twitter or Facebook. If you chose 'Coupon Based' you must include the variable {{ coupon_code }}. A link to your shop will be added automatically.", 'automatewoo-referrals' ),
			]
		);

		$this->add_setting(
			'social_share_text_twitter',
			[
				'title'   => __( 'Twitter default share text (optional)', 'automatewoo-referrals' ),
				'type'    => 'textarea',
				'tooltip' => __( "Optionally specify different text for Twitter shares as they are limited to 280 characters. If left blank the default share text will be used.", 'automatewoo-referrals' ),
			]
		);

		$this->section_end( 'social' );


		$this->section_start(
			'email',
			__( 'Email sharing', 'automatewoo-referrals' ),
			__( 'The email template that is sent when an advocate refers a friend view email. ', 'automatewoo-referrals' )
			. __( 'You can insert dynamic content with the following variables: ', 'automatewoo-referrals' )
			. '<br><code>{{ coupon_code }}</code> <code>{{ share_url }}</code> <code>{{ advocate.first_name }}</code> <code>{{ advocate.full_name }}</code>'
			. '<br><br><a href="#" class="button js-aw-referrals-preview-share-email">' . __( 'Preview email', 'automatewoo-referrals' ) . '</a>'
		);

		$this->add_setting(
			'enable_email_share',
			[
				'title' => __( 'Enable sharing via Email', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);

		$this->add_setting(
			'share_email_subject',
			[
				'title' => __( 'Email subject', 'automatewoo-referrals' ),
				'type'  => 'text',
			]
		);

		$this->add_setting(
			'share_email_heading',
			[
				'title' => __( 'Email heading', 'automatewoo-referrals' ),
				'type'  => 'text',
			]
		);

		$this->add_setting(
			'share_email_template',
			[
				'title'   => __( 'Email template', 'automatewoo-referrals' ),
				'type'    => 'select',
				'options' => Emails::get_email_templates(),
				'tooltip' => __( 'The template that will be used to send referral share emails. For info on creating custom templates please refer to the AutomateWoo documentation.', 'automatewoo-referrals' ),
			]
		);

		$this->add_setting(
			'share_email_body',
			[
				'title' => __( 'Email body', 'automatewoo-referrals' ),
				'type'  => 'tinymce',
				'desc'  => __( "If are using 'Coupon Based' referrals you must include the variable {{ coupon_code }}. If you chose 'Link Based' ensure you have at least one link in the email body. Tracking parameters will be automatically added to all links in the email.", 'automatewoo-referrals' ),
			]
		);

		$this->section_end( 'email' );


		$this->section_start( 'advanced', __( 'Advanced', 'automatewoo-referrals' ) );

		$this->add_setting(
			'allow_existing_customer_referrals',
			[
				'title' => __( 'Allow existing customer referrals', 'automatewoo-referrals' ),
				'desc'  => __( 'If this is unchecked, existing customers will be blocked from receiving referrals (default). If checked, existing customers can be referred but they can only be referred a single time.', 'automatewoo-referrals' ),
				'type'  => 'checkbox',
			]
		);


		$this->add_setting(
			'reward_event',
			[
				'title'    => __( 'Reward event', 'automatewoo-referrals' ),
				'desc'     => __( "The reward event determines when a referral should be created. When setting to 'Purchase' (default) referrals will be created when a friend makes a purchase. When setting to 'Sign Up' referrals will be created when a friend creates a customer account and no purchase is required.", 'automatewoo-referrals' ),
				'type'     => 'select',
				'autoload' => true,
				'options'  => [
					'purchase' => __( 'Purchase', 'automatewoo-referrals' ),
					'signup'   => __( 'Sign up', 'automatewoo-referrals' )
				]
			]
		);

		$this->section_end( 'advanced' );
	}

	/**
	 * Get attributes for a price number field setting.
	 *
	 * @return array
	 */
	private function get_price_setting_attrs() {
		return [
			'min'  => 0,
			'step' => '0.01',
		];
	}


	/**
	 * @param $id
	 * @return mixed
	 */
	protected function get_default( $id ) {
		return isset( AW_Referrals()->options()->defaults[ $id ] ) ? AW_Referrals()->options()->defaults[ $id ] : false;
	}


	function save() {
		parent::save();
		\AW()->action_scheduler()->enqueue_async_action( 'automatewoo/referrals/settings_updated_async' );
	}

	private function add_referral_limit_section() {
		$this->section_start(
			'referral_limit',
			__( 'Advocate referral limit', 'automatewoo-referrals' ),
			__( 'Limits the number of referrals an advocate can make within a certain time period. When an advocate\'s limit is reached they are prevented from earning referral credit and their shared referral coupons are disabled.', 'automatewoo-referrals' )
		);

		$this->add_setting(
			'limit_number_referrals',
			[
				'title'       => __( 'Limit the number of referrals', 'automatewoo-referrals' ),
				'desc'        => __( 'If checked, the number of referrals an Advocate can make is limited.', 'automatewoo-referrals' ),
				'type'        => 'checkbox',
				'set_default' => true,
			]
		);

		$this->add_setting(
			'referral_limit_timeframe',
			[
				'title'       => __( 'Referral limit timeframe', 'automatewoo-referrals' ),
				'desc'        => __( 'Select the length of time that where the referral limit applies.', 'automatewoo-referrals' ),
				'type'        => 'select',
				'set_default' => true,
				'options'     => [
					'lifetime' => __( 'Lifetime', 'automatewoo-referrals' ),
					'year'     => __( 'Yearly', 'automatewoo-referrals' ),
					'month'    => __( 'Monthly', 'automatewoo-referrals' ),
					'week'     => __( 'Weekly', 'automatewoo-referrals' ),
				],
			]
		);

		$this->add_setting(
			'referral_limit',
			[
				'title'       => __( 'Referral limit', 'automatewoo-referrals' ),
				'desc'        => __( 'The number of referrals an Advocate is limited to.', 'automatewoo-referrals' ),
				'type'        => 'number',
				'set_default' => true,
			]
		);

		$this->section_end( 'referral_limit' );
	}
}

return new Settings_Tab();
