<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Cache' ) ) {

	class XT_Framework_Cache {

        /**
         * Cache group
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $cache_group
         */
        protected $cache_group;

        public function __construct( $cache_group = '' ) {

            $this->cache_group = $cache_group;
		}

        public function set($key, $val, $expire = 0) {

            return wp_cache_set($key, $val, $this->cache_group, $expire);
        }

        public function get($key) {

            return wp_cache_get( $key, $this->cache_group );
        }

        public function delete($key) {

            return wp_cache_delete( $key, $this->cache_group );
        }

        public function exists($key) {

            return $this->get( $key ) !== false;
        }

        public function flush() {

            return wp_cache_flush();
        }

        public function result($key, callable $callback, $expiration = 0) {

            $cached = $this->get($key);

            if($cached === false || !empty($_GET['nocache'])) {

                $cached = $callback();

                $this->set($key, $cached, $expiration);
            }

            return $cached;
        }
	}
}