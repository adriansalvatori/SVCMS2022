<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_Review_Notice' ) ) {

    /**
     * Class that takes care of adding plugin review notice after X days
     *
     * @package    XT_Framework
     * @subpackage XT_Framework/includes
     * @author     XplodedThemes
     */

    class XT_Framework_Review_Notice {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   protected
         * @var      XT_Framework   $core    Core Class
         */
        protected $core;

        /**
         * Review url
         *
         * @since    1.0.0
         * @access   public
         * @var      string $review_url
         */
        public $review_url = '';

        /**
         * Interval in days between reminder notice
         *
         * @since    1.0.0
         * @access   public
         * @var      int $days_interval
         */
        public $days_interval = 7;

        /**
         * Max days before stopping the reminder notice
         *
         * @since    1.0.0
         * @access   public
         * @var      int $max_days
         */
        public $max_days = 120;

        /**
         * Check if a notice is currently active
         *
         * @since    1.0.0
         * @access   public
         * @var      bool $active_notice
         */
        public static $active_notice = false;

        /**
         * Construct.
         *
         * @since    1.0.0
         * @access   public
         * @var      XT_Framework $core Core Class
         */
        public function __construct( $core ) {

            $this->core = $core;

            $this->review_url = 'https://wordpress.org/support/plugin/'.$this->core->plugin()->market_product()->freemium_slug.'/reviews/?rate=5&filter=5/#new-post';

            // Add Ajax Events
            add_filter($this->core->plugin_prefix('ajax_add_events'), array($this, 'ajax_add_events'), 1);

            // Append Inline Script, 1 time only for all plugins
            if(!did_action('xtfw_review_scripts_appended')) {
                add_action('xtfw_admin_inline_scripts', array($this, 'append_inline_script'), 1);
                do_action('xtfw_review_scripts_appended');
            }

            if(!$this->enabled()) {
                return;
            }

            self::$active_notice = true;

            // Init Review Notice
            add_action( 'init', array( $this, 'add_review_notice' ) );
        }

        public function enabled() {

            $min_days_trigger = $this->get_min_days_trigger();
            $days_passed = $this->get_days_passed();

            return $min_days_trigger !== -1 && $days_passed >= $min_days_trigger && !self::$active_notice;
        }

        public function ajax_add_events($ajax_events) {

            $ajax_events[] = array(
                'function' => $this->core->plugin_short_prefix('plugin_rate_action'),
                'callback' => array($this, 'ajax_plugin_rate_action')
            );

            return $ajax_events;
        }

        public function get_min_days_trigger() {

            $option_key = $this->core->plugin_short_prefix('review_notice_min_days');
            return intval(get_option($option_key, $this->days_interval));
        }

        public function set_min_days_trigger($value) {

            $option_key = $this->core->plugin_short_prefix('review_notice_min_days');
            return update_option($option_key, $value);
        }

        public function get_days_passed() {

            $install_timestamp = $this->core->plugin_migrations()->installed_time;
            $current_time = time();

            $date_diff = $current_time - $install_timestamp;

            return intval(round($date_diff / (60 * 60 * 24)));
        }

        public function get_readable_days_passed() {

            $install_timestamp = $this->core->plugin_migrations()->installed_time;
            $current_time = time();

            return human_time_diff($install_timestamp, $current_time);
        }

        public function ajax_plugin_rate_action() {

            // Continue only if the nonce is correct
            $nonce = sanitize_text_field($_REQUEST['_nonce']);

            if ( ! wp_verify_nonce( $nonce, $this->core->plugin_short_prefix('wp_rate_action_nonce') ) ) {
                wp_send_json_error();
            }

            $rate_action = sanitize_text_field($_POST['rate_action']);

            $this->rate_plugin($rate_action);

            // Apply the "Nope, maybe later" action to the rest of the active XT plugins as well.
            foreach (XT_Framework::instances() as $instance) {

                if($instance->plugin_slug() === $this->core->plugin_slug()) {
                    continue;
                }

                $reviewNotice = $instance->plugin_review_notice();
                if (!empty($reviewNotice)) {
                    $reviewNotice->rate_plugin('not-enough', 1);
                }
            }

            wp_send_json_success();
        }

        public function rate_plugin($rate_action, $days_interval = null) {

            $min_days_trigger = $this->get_min_days_trigger();
            $days_passed = $this->get_days_passed();
            $days_interval = !empty($days_interval) ? $days_interval : $this->days_interval;

            if ( -1 === $min_days_trigger ) {
                return;
            }

            if ('done-rating' === $rate_action) {

                $min_days_trigger = -1;

            } else {

                if($min_days_trigger > $this->max_days) {
                    $min_days_trigger = -1;
                }else {
                    $min_days_trigger = $days_passed + $days_interval;
                }
            }

            $this->set_min_days_trigger($min_days_trigger);
        }

        public function add_review_notice() {

            $message = xtfw_ob_get_clean(function() {

                $current_user = wp_get_current_user();
                $first_name = ucfirst(strtolower(!empty($current_user->user_firstname) ? $current_user->user_firstname : $current_user->display_name));

                $time_passed = $this->get_readable_days_passed();
                $action = $this->core->ajax()->get_ajax_action('plugin_rate_action');

                echo sprintf(esc_html__("Hey %s, I noticed you've been using %s for the past %s – that’s awesome!", "xt-framework"), $first_name, '<strong>' . $this->core->plugin_menu_name() . '</strong>', '<strong>'.$time_passed.'</strong>');
                echo '&nbsp;';
                echo sprintf(esc_html__('Could you please do me a %1$sBIG favor%2$s and give it a %1$s5-star rating%2$s on WordPress? Just to %1$s help us%2$s spread the word and boost our motivation. Many thanks!', "xt-framework"), "<strong>", "</strong>");
                ?>
                <br><em>~ Georges H</em>
                <br><br>
                <span data-action="<?php echo esc_attr($action);?>" data-nonce="<?php echo wp_create_nonce( $this->core->plugin_short_prefix('wp_rate_action_nonce') ) ?>">
                    <span><span class="dashicons dashicons-thumbs-up"></span> <a target="_blank" href="<?php echo esc_url($this->review_url);?>"><?php echo esc_html__( 'Ok, you deserve it', 'xt-framework' ) ?></a></span>
                    <span><span class="dashicons dashicons-thumbs-down"></span> <a data-rate-action="not-enough" href="#"><?php echo esc_html__( 'Nope, maybe later', 'xt-framework' ) ?></a></span>
                    <span><span class="dashicons dashicons-yes"></span> <a data-rate-action="done-rating" href="#"><?php echo esc_html__( 'I already did', 'xt-framework' ) ?></a></span>
                    <span><span class="dashicons dashicons-sos"></span> <a target="_blank" href="https://xplodedthemes.com/support/"><?php echo esc_html__( 'Help me first', 'xt-framework' ) ?></a></span>
                </span>
                <?php
            });

            $this->core->plugin_notices()->add_success_message($message, array('xtfw-wp-rate-notice'));
        }

        public function append_inline_script($scripts) {

            $scripts .= '
                (function( $ ) {
                    $(function() {
                        var container = $(".xtfw-wp-rate-notice");
                        if (container.length) {
                            container.find("a[data-rate-action]").on("click", function(evt) {
                            
                                var rateAction = $(this).attr("data-rate-action");
                         
                                container.slideUp(function() {
                                    $(this).remove();
                                });
                                
                                evt.preventDefault();     
                                                       
                                $.post(ajaxurl, { 
                                    action: container.find("[data-action]").attr("data-action"),
                                    rate_action: rateAction,
                                    _nonce: container.find("[data-nonce]").attr("data-nonce")
                                }).fail(function(error) { 
                                   console.log(error); 
                                });
                            });
                        }
                    });
                })( jQuery );
            ';

            return $scripts;
        }

    }
}