<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PH_Plugin_Check
{
    protected $messages = [];
    protected $article_id = "58c17298dd8c8e56bfa85267";

    public function check()
    {
        $this->check_plugins();

        return  $this->messages;
    }

    public function check_plugins()
    {
        // wordfence
        if (class_exists('wfConfig') && method_exists('wfConfig', 'get')) {
            if (wfConfig::get('loginSec_disableAuthorScan')) {
                $this->messages[] = [
                    'title' => 'WordFence Setting Conflict',
                    'message' => __('You need to uncheck "Prevent discovery of usernames through \'/?author=N\' scans. This doesn\'t allow our REST API to send authoring information with comments.', 'project-huddle'),
                    'article_id' => $this->article_id,
                    'type' => 'error'
                ];
            }
        }

        if (class_exists('ITSEC_Modules') && method_exists('ITSEC_Modules', 'get_settings')) {
            $settings = ITSEC_Modules::get_settings('wordpress-tweaks');
            if (!in_array($settings['rest_api'], array('enable', 'default-access'))) {
                $this->messages[] = [
                    'title' => 'iThemes Setting Conflict',
                    'message' => __('You need to enable default access to the REST API or ProjectHuddle\'s roles won\'t be able to access the REST API endpoint.', 'project-huddle'),
                    'article_id' => $this->article_id,
                    'type' => 'error'
                ];
            }
        }

        // really simple ssl
        $options = get_option('rlrsssl_options', false);
        if ($options && is_array($options)) {
            if (isset($options['javascript_redirect']) && $options['javascript_redirect']) {
                $this->messages[] = [
                    'title' => 'Really Simple SSL Setting Conflict',
                    'message' => __('You must turn off Really Simple SSL\'s javascript redirection feature or ProjectHuddle won\'t work on connected non-ssl sites.', 'project-huddle'),
                    'article_id' => '59416ab22c7d3a0747cde5db',
                    'type' => 'warning'
                ];
            }
        }

        // disable comments
        if (is_plugin_active('disable-comments/disable-comments.php') || is_plugin_active('disable-comments-rb/disable-comments-rb.php')) {
            $this->messages[] = [
                'title' => 'Comments Disabled',
                'message' => __('You must turn off your Disable Comments plugin or ProjectHuddle won\'t be able to save any comments!', 'project-huddle'),
                'article_id' => $this->article_id,
                'type' => 'error'
            ];
        }

        // roots soil
        global $_wp_theme_features;
        if (isset($_wp_theme_features['soil-disable-rest-api']) && $_wp_theme_features['soil-disable-rest-api']) {
            $this->messages[] = [
                'title' => 'REST API Disabled',
                'message' => __('The Roots soil plugins is disabling the REST API. You need to remove this functionality.', 'project-huddle'),
                'article_id' => $this->article_id,
                'type' => 'warning'
            ];
        }
        if (isset($_wp_theme_features['soil-relative-urls']) && $_wp_theme_features['soil-relative-urls']) {
            $this->messages[] = [
                'title' => 'Relative URLS detected',
                'message' => __('The Roots soil plugins is changing to relative urls. You need to remove this functionality or ProjectHuddle will not work on client sites.', 'project-huddle'),
                'article_id' => $this->article_id,
                'type' => 'warning'
            ];
        }

        // TODO: 
        // 404 redirect to homepage
        // WP Cerber Security 
        // WebARX
        // WP Secure Hide WordPress Plugin
    }
}
