<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Support extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Support', 'checkout-wc' ), 'manage_options', 'support' );
	}

	public function output() {
		?>
		<div class="max-w-3xl pb-8">
			<div>
				<p class="text-5xl font-bold text-gray-900">
					<?php cfw_e( 'Awesome support is in our DNA.', 'checkout-wc' ); ?>
				</p>
				<p class="max-w-xl mt-5 text-2xl text-gray-500">
					<?php cfw_e( 'Our Knowledge Base is packed with tips, tricks, and common troubleshooting steps.', 'checkout-wc' ); ?>
				</p>
				<p class="mt-6">
					<a href="https://kb.checkoutwc.com" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-lg shadow font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
						<?php cfw_e( 'Read Our Documentation', 'checkout-wc' ); ?>
					</a>
				</p>
			</div>
		</div>
		<div class="hidden sm:block" aria-hidden="true">
			<div class="py-8">
				<div class="border-t border-gray-300"></div>
			</div>
		</div>

		<p class="text-2xl text-gray-900">
			<?php cfw_e( 'Some Popular Knowledge Base Articles', 'checkout-wc' ); ?>
		</p>

		<ul class="mt-4 text-base">
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/35-getting-started">Getting Started</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/36-troubleshooting">Troubleshooting</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/53-upgrading-your-license">Upgrading Your License</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/69-how-to-enable-billing-and-shipping-phone-fields">How To Enable Billing and Shipping Phone Fields</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/70-how-to-enable-cart-editing">How To Enable Cart Editing</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/86-how-to-get-and-configure-your-google-api-key">How To Register and Configure Your Google API Key</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/49-how-to-add-a-custom-field">How To Add a Custom Field to Checkout for WooCommerce</a></li>
			<li><a class="text-blue-600 underline" target="_blank" href="https://kb.checkoutwc.com/article/34-how-to-enable-the-woocommerce-notes-field">How to Enable The WooCommerce Notes Field</a></li>
		</ul>

		<p class="text-2xl text-gray-900 mt-6">
			<?php cfw_e( 'Still Need Help?', 'checkout-wc' ); ?>
		</p>

		<input type="submit" id="checkoutwc-support-button" class="mt-4 cursor-pointer inline-flex items-center px-6 py-3 border border-transparent text-base shadow font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" value="Contact Support">

		<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
		<script type="text/javascript">window.Beacon('init', '355a5a54-eb9d-4b64-ac5f-39c95644ad36')</script>
		<script>
			jQuery("#checkoutwc-support-button").on( 'click', function() {
				Beacon("open");
			});
		</script>
		<?php
	}
}
