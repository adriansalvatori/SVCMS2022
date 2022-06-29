<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PH_Hosting_Check
{
    protected $messages = [];

    public function check()
    {
        $this->known_hosts();
        $this->settings();
        $this->caching_check();
        $this->php_version_check();

        return  $this->messages;
    }

    public function php_version_check()
    {
        if ( version_compare( phpversion(), '7.4', '<' ) ) {
            $this->messages[] = [
                'title' => 'Older PHP version detected',
                'message' => __('You\'ll need PHP version 7.4 or above for some features of ProjectHuddle to work. You are running ' . PHP_VERSION . '.', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }
    }

    public function known_hosts()
    {
        if (defined('WPE_APIKEY')) {
            $this->messages[] = [
                'title' => 'WPEngine Hosting Detected',
                'message' => __('You\'ll need to request a cache exclusion in order for ProjectHuddle access links to work properly.', 'project-huddle'),
                'article_id' => '614c194112c07c18afdd6be4',
                'type' => 'error'
            ];
        }
        if (defined('FLYWHEEL_CONFIG_DIR')) {
            $this->messages[] = [
                'title' => 'Flywheel Hosting Detected',
                'message' => __('You\'ll need to request a cache exclusion in order for ProjectHuddle to work properly.', 'project-huddle'),
                'article_id' => '614c1cc70754e74465f12f00',
                'type' => 'warning'
            ];
        }

        if (isset($_SERVER['SERVER_SOFTWARE']) && (strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false)) {
            $this->messages[] = [
                'title' => 'LiteSpeed Hosting Detected',
                'message' => __('You\'ll need to enable the LiteSpeed WordPress plugin and add cache exclusions in order for ProjectHuddle to work properly.', 'project-huddle'),
                'article_id' => '614c197b00c03d6720759b34',
                'type' => 'warning'
            ];
        }
    }

    public function settings()
    {
        // ssl
        if (!is_ssl()) {
            $this->messages[] = [
                'title' => 'SSL Not Detected',
                'message' => __('Your site does not appear to be using a secure connection. A HTTPS SSL connection is required for ProjectHuddle to work with external website connections.', 'project-huddle'),
                'article_id' => '5be1c1af2c7d3a01757ad94e',
                'type' => 'error'
            ];
        }

        // memory limit
        $limit = $this->let_to_num((WP_MEMORY_LIMIT)) / (1024);
        if ($limit < 128) {
            $this->messages[] = [
                'title' => 'WordPress memory limit too low',
                'message' => sprintf(__('Your WordPress memory limit is set to %sMB. The recommended memory limit is 128MB.', 'project-huddle'), $limit),
                'article_id' => '614c19040754e74465f12ef2',
                'type' => 'warning'
            ];
        } else {
            $php_limit = $this->let_to_num(ini_get('memory_limit')) / (1024);
            if ($limit < 128) {
                $this->messages[] = [
                    'title' => 'PHP memory limit too low',
                    'message' => sprintf(__('Your PHP memory limit is set to %s. The recommended memory limit is 128MB.', 'project-huddle'), $php_limit),
                    'article_id' => '614c19040754e74465f12ef2',
                    'type' => 'warning'
                ];
            }
        }

        if (!has_filter('template_redirect', 'redirect_canonical')) {
            $this->messages[] = [
                'title' => 'Canonical Redirect Error.',
                'message' => __('A theme or plugin is disabling canonical redirects. Please reach out to support.', 'project-huddle'),
                'type' => 'error',
                'contact_us' => true
            ];
        }
    }

    public function caching_check()
    {
        if (defined('BREEZE_VERSION')) {
            $this->messages[] = [
                'title' => 'Breeze Caching Detected',
                'message' => __('ProjectHuddle needs special cache exclusions in order to work with Breeze.', 'project-huddle'),
                'article_id' => '614c1b790332cb5b9e9ad282',
                'type' => 'warning'
            ];
            $this->messages[] = [
                'title' => 'Cloudways Hosting Detected',
                'message' => __('Please ensure you\'ve disabled varnish or added the correct varnish exclusions for ProjectHuddle', 'project-huddle'),
                'article_id' => '614abd690754e74465f1276f',
                'type' => 'warning'
            ];
            return;
        }

        if (defined('WP_ROCKET_VERSION')) {
            $this->messages[] = [
                'title' => 'WPRocket Caching Detected',
                'message' => __('ProjectHuddle needs special cache exclusions in order to work with WPRocket.', 'project-huddle'),
                'article_id' => '614c1a4100c03d6720759b3e',
                'type' => 'warning'
            ];
        }

        // check for advanced cache
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $filesystem            = new WP_Filesystem_Direct(new StdClass());
        $cache_file_is_file    = $filesystem->is_file(WP_CONTENT_DIR . '/advanced-cache.php');

        // check for object caching
        $object_caching        = defined('ENABLE_CACHE') && true === ENABLE_CACHE;

        if ($cache_file_is_file || $object_caching) {
            $this->messages[] = [
                'title' => 'Caching detected.',
                'message' => __('You\'ll need to add some cache exclusions for ProjectHuddle to work properly.', 'project-huddle'),
                'article_id' => '614ab5e40332cb5b9e9acac3',
                'type' => 'warning'
            ];
        }
    }

    /**
     * Size Conversions
     *
     * @author Chris Christoff
     * @since  1.0
     *
     * @param  unknown $v
     *
     * @return int|string
     */
    protected function let_to_num($v)
    {
        $l   = substr($v, -1);
        $ret = substr($v, 0, -1);

        switch (strtoupper($l)) {
            case 'P': // fall-through
            case 'T': // fall-through
            case 'G': // fall-through
            case 'M': // fall-through
            case 'K': // fall-through
                $ret *= 1024;
                break;
            default:
                break;
        }

        return $ret;
    }
}
