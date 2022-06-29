<?php

namespace PH\Controllers;

use PH\Models\User;
use PH\Models\Visitor;
use PH\Models\Website;

class WebsiteScriptController
{
    protected $user_name = '',
        $user_email = '',
        $signature = '',
        $access_token = '',
        $signature_user = null;

    public function __construct($project_id, $user_name = '', $user_email = '', $signature = '', $access_token = '')
    {
        $this->website = Website::get($project_id);
        $this->user_name = $user_name ?: $this->user_name;
        $this->user_email = $user_email ?: $this->user_email;
        $this->signature = $signature ?: $this->signature;
        $this->access_token = $access_token ?: $this->access_token;

        $this->saveToken();
        $this->signature_user = $this->handleSignature();
    }

    /**
     * Save the access token in the visitors session
     *
     * @return void
     */
    public function saveToken()
    {
        if ($this->website && $this->access_token) {
            return Visitor::current()->saveToken($this->website, $this->access_token);
        }
        return false;
    }

    /**
     * Handle signature creation/login
     *
     * @return WP_Rest_Response
     */
    public function handleSignature()
    {
        if (!($this->user_email && $this->signature && $this->website->ID && $this->access_token)) {
            return false;
        }

        return User::rest()->create(
            [
                'email'        => sanitize_email($this->user_email),
                'username'     => sanitize_text_field($this->user_name),
                'access_token' => wp_kses_post($this->access_token),
                'project_id'   => $this->website->ID,
            ],
            [
                '_signature' => $this->signature,
            ]
        );
    }

    /**
     * Server data to send to script
     *
     * @return array
     */
    public function getData()
    {
        ob_start();
        ph_get_template('ph-website-iframe.php', '', '', PH_WEBSITE_PLUGIN_DIR . 'templates/');
        $container = ob_get_contents();
        ob_end_clean();

        return [
            "container" => $container,
            "origin" => esc_url_raw(get_site_url()),
            "query_vars" => isset($_GET['ph_query_vars']) ? (bool) $_GET['ph_query_vars'] : '',
            'signature' => isset($_GET['ph_signature']) ? wp_kses_post($_GET['ph_signature']) : '',
        ];
    }

    /**
     * Loads the dynamic script
     */
    public function load()
    {
        if (!Visitor::current()->canAccess($this->website)) {
            return new \WP_Error('access_denied', 'You are not allowed to access this project', ['status' => rest_authorization_required_code()]);
        }
        // set installed
        $this->website->setInstalled(true);
        // print dynamic script
        return $this->printScript();
    }

    /**
     * Prints the script output
     *
     * @return void
     */
    public function printScript()
    {
        ob_start();
?>
        // add refresh token
        <?php if (is_user_logged_in()) : ?>
            <?php $refresh_token = User::current()->getRefreshToken();  ?>
            <?php if ($refresh_token) : ?>
                localStorage.setItem('ph_authorization', '<?php echo wp_kses_post($refresh_token) ?>');
            <?php endif; ?>
        <?php endif; ?>

        var PH_Website = <?php echo json_encode($this->getData()); ?>;

        // comment scroll
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var comment_id = urlParams.get("ph_comment");
        PH_Website.comment_scroll = comment_id || 0;

        // remove query vars
        var parsed = new URL(window.location);
        parsed.search = parsed.search.replace(
        /&?ph_access_token=([^&]$|[^&]*)/i,
        ""
        );
        parsed.search = parsed.search.replace(/&?ph_comment=([^&]$|[^&]*)/i, "");
        window.history.replaceState({}, window.title, parsed.toString());

        var head = document.getElementsByTagName('head')[0];
        var cssnode = document.createElement('link');

        PH_Website.isSSO = true;

        // add css
        cssnode.type = 'text/css';
        cssnode.rel = 'stylesheet';
        cssnode.href = '<?php echo esc_url(PH_PLUGIN_URL . 'assets/css/dist/ph-website-comments-parent.css'); ?>?v=<?php echo esc_html(PH_VERSION); ?>';
        head.appendChild(cssnode);

        var css = '<?php echo ph_parent_website_style_options(); ?>',
        head = document.head || document.getElementsByTagName('head')[0],
        style = document.createElement('style');

        style.type = 'text/css';
        if (style.styleSheet){
        style.styleSheet.cssText = css;
        } else {
        style.appendChild(document.createTextNode(css));
        }

        head.appendChild(style);

        // need to append this on parent domain
        (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)){ return; }
        js = d.createElement(s); js.id = id;
        js.src = "//cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.5/dist/html2canvas.min.js";
        fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'ph-html2canvas'));
<?php
        // create iframe
        $file = PH_WEBSITE_PLUGIN_DIR . 'assets/js/ph-iframe-creator.js';

        if (file_exists($file)) {
            readfile($file);
        }
        do_action('ph_website_script_loaded');
        $output = ob_get_clean();
        return $output;
    }
}
