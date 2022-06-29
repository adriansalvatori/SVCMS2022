<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Plugin' ) ) {

	class XT_Framework_Plugin {

		public $version;
		public $name;
		public $menu_name;
		public $url;
		public $icon;
		public $slug;
		public $prefix;
		public $short_prefix;
		public $market;
		public $markets = array();
		public $market_product;
		public $dependencies = array();
		public $conflicts = array();
		public $top_menu = false;
        public $premium_only = false;
        public $trial_days = 14;
        public $file;

		public function __construct( $params ) {

			$params = json_decode( json_encode( $params ) );

            if(!empty($params->market) && !empty($params->markets)) {

                $params->market_product = $params->markets->{$params->market};

            }

			foreach ( $params as $attribute => $value ) {

				$this->{$attribute} = $value;
			}

            if(!empty($this->market_product) && !isset($this->market_product->freemium_slug)) {
                $this->market_product->freemium_slug = '';
            }
        }

		public function version() {
			return $this->version;
		}

		public function name() {
			return $this->name;
		}

		public function menu_name() {
			return $this->menu_name;
		}

		public function url() {
			return $this->url;
		}

		public function icon() {
			return $this->icon;
		}

		public function slug() {
			return $this->slug;
		}

		public function prefix() {
			return $this->prefix;
		}

		public function short_prefix() {
			return $this->short_prefix;
		}

		public function market() {
			return $this->market;
		}

		public function markets() {
			return $this->markets;
		}

		public function market_product() {
			return $this->market_product;
		}

		public function dependencies() {
			return $this->dependencies;
		}

		public function conflicts() {
			return $this->conflicts;
		}

		public function top_menu() {
			return $this->top_menu;
		}

        public function premium_only() {
            return $this->premium_only;
        }

        public function trial_days() {
            return $this->trial_days;
        }

		public function file() {
			return $this->file;
		}

		public function __call( $name, $arguments ) {
			if ( isset( $this->{$name} ) ) {
				return $this->{$name};
			} else {
				return null;
			}
		}
	}
}