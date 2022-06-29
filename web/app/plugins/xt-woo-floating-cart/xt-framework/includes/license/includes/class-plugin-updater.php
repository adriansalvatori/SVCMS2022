<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('XT_Framework_Plugin_Updater')) {

	class XT_Framework_Plugin_Updater {

		protected $manager;
		protected $license;
		protected $plugin;
        protected $read_only;

		protected $update_url = "https://repo.xplodedthemes.com/updates.php?action=get_metadata&slug={slug}&market={market}";

		public function __construct( $license, $plugin, $read_only = false ) {

			$this->plugin  = $plugin;
			$this->license = $license;
            $this->read_only = $read_only;

			$this->update_url = str_replace(
				array(
					'{slug}',
					'{market}'
				),
				array(
					$this->plugin->market_product->premium_slug,
					$this->plugin->market,
				),
				$this->update_url
			);

			$updateChecker = Puc_v4_Factory::buildUpdateChecker(
				$this->update_url,
				$this->plugin->file
			);

			$updateChecker->addQueryArgFilter( array( $this, 'filter_update_checker' ) );
		}

		public function filter_update_checker( $queryArgs ) {

			if ( !$this->read_only && ! empty( $this->license ) && $this->license->getLocalLicense() !== false ) {

				$product = $this->license->getLocalLicense()->license;

				$queryArgs['purchase_code'] = $product->purchase_code;
				$queryArgs['product_id']    = $product->product_id;
				$queryArgs['domain']        = $product->domain;

			} else {

				$queryArgs['purchase_code'] = '';
				$queryArgs['product_id']    = '';
				$queryArgs['domain']        = '';
			}

			return $queryArgs;
		}

	}
}
