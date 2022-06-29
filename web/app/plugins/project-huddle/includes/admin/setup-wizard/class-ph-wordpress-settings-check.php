<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PH_WordPress_Settings_check
{
    protected $messages = [];

    public function check()
    {
        $this->check_settings();

        return  $this->messages;
    }

    public function check_settings()
    {
        global $wp_version;
        $current      = get_site_transient( 'update_core' ); 
        
        if (!get_option('permalink_structure')) {
            $this->messages[] = [
                'title' => 'Default Permalinks Detected',
                'message' => __('ProjectHuddle requires permalinks to be enabled in order to access the WordPress API. Please enable permalinks under Settings > Permalinks.', 'project-huddle'),
                'type' => 'error'
            ];
        }

        $home_url = get_option('home');
        $site_url = get_option('siteurl');
        if ($home_url !== $site_url) {
            $this->messages[] = [
                'title' => 'Your home url and site url are different.',
                'message' => __('This can sometimes cause issues with requests, especially if one uses SSL and the other does not.', 'project-huddle'),
                'type' => 'warning'
            ];
        }

        if (parse_url($home_url, PHP_URL_SCHEME) !== 'https' || parse_url($site_url, PHP_URL_SCHEME) !== 'https') {
            $this->messages[] = [
                'title' => 'Your home url and site url are not https.',
                'message' => __('Your home url and site url must start with https or cookies won\'t work cross-domain.', 'project-huddle'),
                'type' => 'warning',
                'link' => esc_url_raw(admin_url('options-general.php'))
            ];
        }



        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->messages[] = [
                'title' => 'WordPress is in debug mode.',
                'message' => __('It\'s recommended that you turn off Debug mode if your site is in production.', 'project-huddle'),
                'article_id' => '614c1a6800c03d6720759b3f',
                'type' => 'warning'
            ];
        }

        // detect smtp on non local hosts
        if (!$this->is_local_url($_SERVER['HTTP_HOST']) && class_exists('PHPMailer')) {
            global $phpmailer;
            // (Re)create it, if it's gone missing
            if (!(is_a($phpmailer, 'PHPMailer'))) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $phpmailer = new PHPMailer(true);
            }

            // Set to use PHP's mail()
            $phpmailer->isMail();
            /**
             * Fires after PHPMailer is initialized.
             *
             * @since 2.2.0
             *
             * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
             */
            do_action_ref_array('phpmailer_init', array(&$phpmailer));

            if ($phpmailer->Mailer !== "smtp") {
                $this->messages[] = [
                    'title' => 'SMTP Mail Service Recommended',
                    'message' => __('It\'s recommended that you use a SMTP service to ensure email delivery. This may already be set up on your host if you\'re seeing this notice and have SMTP enabled.', 'project-huddle'),
                    'article_id' => '614c199b00c03d6720759b37',
                    'type' => 'warning'
                ];
            }
        }

        if ( $wp_version !== $current->version_checked ) {
            $this->messages[] = [
                'title' => 'New WordPress Install Recommended',
                'message' => __('It\'s recommended that you install ProjectHuddle on a new WordPress installation.', 'project-huddle'),
                'article_id' => '5a19f9f6042863319924c18d',
                'type' => 'warning'
            ];
        }
    }

    /**
     * Check if a URL is considered a local one
     *
     * @since  3.2.7
     *
     * @param  string $url The URL Provided
     *
     * @return boolean      If we're considering the URL local or not
     */
    public function is_local_url($url = '')
    {
        $is_local_url = false;

        // Trim it up
        $url = strtolower(trim($url));

        // Need to get the host...so let's add the scheme so we can use parse_url
        if (false === strpos($url, 'http://') && false === strpos($url, 'https://')) {
            $url = 'http://' . $url;
        }

        $url_parts = parse_url($url);
        $host      = !empty($url_parts['host']) ? $url_parts['host'] : false;

        if (!empty($url) && !empty($host)) {
            if (false !== ip2long($host)) {
                if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    $is_local_url = true;
                }
            } else if ('localhost' === $host) {
                $is_local_url = true;
            }

            $tlds_to_check = [
                '.dev', '.local', '.test',
            ];

            foreach ($tlds_to_check as $tld) {
                if (false !== strpos($host, $tld)) {
                    $is_local_url = true;
                    continue;
                }
            }

            if (substr_count($host, '.') > 1) {
                $subdomains_to_check = [
                    'dev.', '*.staging.', '*.test.', 'staging-*.',
                ];

                foreach ($subdomains_to_check as $subdomain) {

                    $subdomain = str_replace('.', '(.)', $subdomain);
                    $subdomain = str_replace(array('*', '(.)'), '(.*)', $subdomain);

                    if (preg_match('/^(' . $subdomain . ')/', $host)) {
                        $is_local_url = true;
                        continue;
                    }
                }
            }
        }

        return $is_local_url;;
    }
}
