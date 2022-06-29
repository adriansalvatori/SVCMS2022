<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Transient' ) ) {

	class XT_Framework_Transient {

        /**
         * Transient prefix
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $prefix
         */
        protected $prefix;

        public function __construct( $prefix = '' ) {

            $this->prefix = $prefix.'_';
		}

        public function set($key, $val, $expiration = YEAR_IN_SECONDS) {

            return set_transient( $this->prefix.$key, $val, $expiration );
        }

        public function get($key) {

            return get_transient( $this->prefix.$key );
        }

        public function delete($key) {

            return delete_transient( $this->prefix.$key );
        }

        public function exists($key) {

            return $this->get( $key ) !== false;
        }

        public function result($key, callable $callback, $expiration = YEAR_IN_SECONDS) {

            $cached = $this->get($key);

            if($cached === false || !empty($_GET['nocache'])) {

                $cached = $callback();

                $this->set($key, $cached, $expiration);
            }

            return $cached;
        }
	}
}