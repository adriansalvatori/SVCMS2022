<?php

if (!defined('ABSPATH')) {
    die;
}

class Guaven_woo_search_admin
{
    public $woo_activeness;
    public $guaven_woos_firstrun;
    public $argv;

    public function __construct()
    {
        if (get_option('guaven_woos_firstrun') == '') {
            $this->guaven_woos_firstrun = 1;
        }
    }

    public function run()
    {
        $this->save_settings();
        $cron_token = $this->cron_token();
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/view.php';
    }

    public function save_settings()
    {
        if ($this->woo_activeness != 1) {
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Error: Before the using this plugin, required WooCommerce plugin has to be active. Activate it, please, then come back here to continue', 'error');
        }
        if (get_option('guaven_woos_trend_table_done') == '') {
            $this->trend_db_construct();
            update_option('guaven_woos_trend_table_done', 1);
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'You can check the 1st QuickStart question&answer at FAQ tab to find out how easily start using the plugin', 'updated');
        }
        if (get_option('guaven_woos_search_data_table_done') == '') {
            $this->search_data_db_construct();
            update_option('guaven_woos_search_data_table_done', 1);
        }

        if (isset($_POST['guaven_woos_nonce_f']) and wp_verify_nonce($_POST['guaven_woos_nonce_f'], 'guaven_woos_nonce')) {
            $this->to_default_runner();
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Success! All changes have been saved. Now just rebuild the cache.', 'updated');
            $this->live_server_cache_clean();
        } elseif (isset($_POST['guaven_woos_reset_nonce_f']) and wp_verify_nonce($_POST['guaven_woos_reset_nonce_f'], 'guaven_woos_reset_nonce')) {
            $this->to_default_runner();
            add_settings_error('guaven_pnh_settings', esc_attr('settings_updated'), 'Success! All settings now same as they were preinstalled', 'updated');
        } elseif (!empty($this->guaven_woos_firstrun)) {
            $this->to_default_runner();
            update_option('guaven_woos_firstrun', 1);
        }
    
        /** can also be applied to new version */
        if (get_option("guaven_woos_live_server")!='' and get_option('guaven_woos_searchcache_table_done') != 2) {
          update_option('guaven_woos_searchcache_table_done', 2);
          $this->searchcache_db_construct();
        }
    }

    private function to_default_runner()
    {
        $this->is_checked('guaven_woos_corr_act', 'checked');
        $this->is_checked('guaven_woos_ispers', '');
        $this->is_checked('guaven_woos_nostock', '');
        $this->is_checked('guaven_woos_removehiddens', '');
        $this->is_checked('guaven_woos_removefilters', '');
        $this->string_setting('guaven_woos_variation_skus', '');
        $this->string_setting('guaven_woos_backend', '');
        $this->is_checked('guaven_woos_add_description_too', '');
        $this->is_checked('guaven_woos_add_shortdescription_too', '');
        $this->string_setting('guaven_woos_translit_data', '-1',"justforthefirsttime");
        $this->string_setting('guaven_woos_live_server', '');
        $this->string_setting('guaven_woos_pureengine_api', '');
        

        $this->string_setting('guaven_show_all_text', '');
        $this->string_setting('guaven_woos_autorebuild', '');
        $this->string_setting('guaven_woos_rebuild_via', 'fs');
        $this->is_checked('guaven_woos_autorebuild_editor', '');

        $this->is_checked('guaven_woos_nomatch_pops', '');
        $this->string_setting('guaven_woos_popsmkey', 'total_sales');
        $this->string_setting('guaven_woos_popsmax', 5);

        $this->string_setting('guaven_woos_customorder', 'date');
        $this->is_checked('guaven_woos_disablerelevancy', '');

        $this->is_checked('guaven_woos_catsearch', '');
        $this->string_setting('guaven_woos_catsearchmax', '5');
        $this->string_setting('guaven_woos_shown_taxonomies', 'product_cat');
        $this->is_checked('guaven_woos_ga', '');
        $this->is_checked('guaven_woos_utm', '');
        $this->is_checked('guaven_woos_cache_version_checker', '');
        $this->string_setting('guaven_woos_large_data', '');
        $this->string_setting('guaven_woos_exactmatch', '');

        $this->string_setting('guaven_woos_perst', 'Recently Viewed Products');
        $this->string_setting('guaven_woos_persmax', '5');
        $this->string_setting('guaven_woos_pinneds', '');
        $this->string_setting('guaven_woos_pinneds_cat', '');
        $this->string_setting('guaven_woos_pinnedt', 'Featured products');
        $this->string_setting('guaven_woos_trendt', 'Trending products');
        $this->string_setting('guaven_woos_trend_days', '3');
        $this->string_setting('guaven_woos_trend_refresh', '10');
        $this->string_setting('guaven_woos_data_trend_num', 0);
        $this->string_setting('guaven_woos_maxres', '10');
        $this->string_setting('guaven_woos_maxprod', '10000');
        $this->string_setting('guaven_woos_sugbarwidth', '100');
        $this->string_setting('guaven_woos_showinit_t', 'Find your product with fast search. Enter some keyword such as iphone, samsung, wear etc.','justforthefirsttime');
        $this->string_setting('guaven_woos_showinit_n', 'No product found by your keyword');

        $this->string_setting('guaven_woos_expression_segments', 'under,around,above');
        $this->string_setting('guaven_woos_expression_spell_s', '');
        $this->string_setting('guaven_woos_expression_spell_p', '');
        $this->is_checked('guaven_woos_simple_expressions', '');

        $this->string_setting('guaven_woos_customfields', '');
        $this->string_setting('guaven_woos_excluded_cats', '');
        $this->string_setting('guaven_woos_excluded_prods', '');
        $this->string_setting('guaven_woos_customtags', '');
        $this->string_setting('guaven_woos_wootags', '');
        $this->string_setting('guaven_woos_custom_css', '');
        $this->string_setting('guaven_woos_custom_js', '');
        $this->string_setting('guaven_woos_selector', '[name="s"]');
        $this->string_setting('guaven_woos_filter_selector', '');
        $this->string_setting('guaven_woos_synonyms', '');
        $this->string_setting('guaven_woos_delay_time', 500);
        $this->string_setting('guaven_woos_memory_limit', 512);

        $this->is_checked('guaven_woos_permalink', '');
        $this->is_checked('guaven_woos_highlight', '');
        $this->is_checked('guaven_woos_disable_meta_correction', '');
        $this->string_setting('guaven_woos_ignorelist', '');
        $this->string_setting('guaven_woos_thumb_quality', 'thumbnail');

        $this->string_setting('guaven_woos_layout', ($this->default_layout()));
        $this->string_setting('guaven_woos_min_symb_sugg', '3');
        $this->int_setting('guaven_woos_whentypo');
        $this->is_checked('guaven_woos_mobilesearch', '');
        $this->string_setting('guaven_woos_live_ui_layout', ''); 
        $this->string_setting('guaven_woos_purchasecode', '');

    }

    public function admin_notice_create(string $body, string $data_notice, string $type = 'info')
    {
        printf('
            <div class="guaven-woos-notice notice notice-%s is-dismissible" data-notice=\'%s\'>
                %s
            </div>', $type, $data_notice, $body);
    }

    public function check_for_support_expired()
    {
        if(!is_admin() or isset($_GET["tab"]) or (function_exists('wp_doing_ajax') and wp_doing_ajax()) or !current_user_can('administrator'))return;
        if(get_option('guaven_woos_support_expired') == '2' and !get_transient('guaven_woos_support_expired_dismissed')
        and get_option('guaven_woos_support_expired_msg')!=''
        ){
            $this->admin_notice_create('
                        <p class="notice-content">
                           '.get_option('guaven_woos_support_expired_msg').'
                        </p>',
                'support_expired', 'warning');
        }
    }

    public function notice_dismissed()
    {
        check_ajax_referer('notice_dismissed', 'nonce');
        // pick up the notice "type" - passed via jQuery (the "data-notice" attribute on the notice)
        $type = $_POST['type'];
        switch ($type){
            case 'support_expired':
                set_transient('guaven_woos_support_expired_dismissed', true, 14 * 24 * 3600);  
                break;
        }
    }

    private function is_checked($par, $defval = '')
    {
        if (isset($_POST[$par])) {
            $k = 'checked';
        } elseif (empty($_POST['guaven_woos_nonce_f']) and $defval != '') {
            $k = $defval;
        } else {
            $k = '';
        }
        update_option($par, $k);
    }

    private function string_setting($par, $def,$firsttime='')
    {
        if (!empty($_POST[$par])) {
            $k = trim($_POST[$par]);
        }
        elseif($firsttime!='' and empty($_POST[$par]) and !empty($_POST["guaven_woos_selector"])){
          $k='';
        }
        else {
            $k = $def;
        }
          update_option($par, $k);
    }

    private function int_setting($par)
    {
        if (!empty($_POST[$par])) {
            $k = (int) $_POST[$par];
        } else {
            $k = 0;
        }
        update_option($par, $k);
    }

    public function default_layout()
    {
        return '<a href="{url}"><div class="guaven_woos_div"><img class="guaven_woos_img" src="{imgurl}"></div><div class="guaven_woos_titlediv"><span>{title}</span><br><small>{price} {saleprice}</small></div></a>';
    }

    public function cron_token()
    {
        $ret = get_option('guaven_woos_cronkey');
        if ($ret == '') {
            $ret = uniqid(time());
            update_option('guaven_woos_cronkey', $ret);
        }

        return $ret;
    }


    private function dont_do_rebuild()
    {
        $check_perm = get_option('guaven_woos_autorebuild');
        if ($check_perm == '') {
            $check_perm = 'b1a0';
        }
        $check_editor_role = get_option('guaven_woos_autorebuild_editor');
        if ($check_perm == 'b0a0') {
            return '0';
        } elseif (!current_user_can('manage_woocommerce')) {
            return '0';
        } elseif ($check_editor_role == '' and !current_user_can('manage_options')) {
            return '0';
        }

        return $check_perm;
    }

    public function edit_hook_rebuilder()
    {
        if ($this->dont_do_rebuild() == '0' or $this->dont_do_rebuild() == 'b1a0') {
            return;
        }
        global $post;
        if (!empty($post->post_type) and $post->post_type == 'product') {
            update_option('do_woosearchbox_rebuild', time());
        }
    }

    public function do_rebuilder_at_footer()
    {
        // if ($this->dont_do_rebuild() == '0') {
        //     return;
        // }

        echo '<script>
    woos_dontclose=0;
    woos_data = {
      "action": "cache_rebuild_ajax",
      "ajnonce": "' . wp_create_nonce('cache_rebuild_ajax') . '"
  };
window.onbeforeunload=function(){if (woos_dontclose==0) return; return "Cache rebuilding process is in progress... Are you sure to cancel it and close the page?";}
  jQuery(".gws_rebuilder").click(function($) {
    jQuery("#result_field").html("0% done...");
    if (document.cookie!=undefined && document.cookie.indexOf("woocommerce_multicurrency_forced_currency")!=-1){
      document.cookie = "woocommerce_multicurrency_forced_currency='.get_woocommerce_currency().';path=/";
    }
      guaven_woos_start_rebuild(woos_data);
  });
    function guaven_woos_start_rebuild(data) {
        jQuery(".Rebuild-SearchBox-Cache a").text("Rebuilding started...");
        jQuery(".inputrebuilder").val("Rebuilding started...");
        jQuery("#result_field").css("display","block");
        woos_dontclose=1;
       jQuery.post(ajaxurl, data, function(response) {
              jQuery("#result_field").html(response+"% done...");
               if (response.indexOf("success_message") ==-1) {console.log("Woo Search Box Cache Rebuilding: "+response+"% done...");guaven_woos_start_rebuild(data);}
               else { jQuery("#result_field").html(response);
                 jQuery(".Rebuild-SearchBox-Cache a").text("Rebuilding done!");
                 jQuery(".inputrebuilder").val("Rebuilding done!");
                 woos_dontclose=0;
                 console.log("Woo Search Box Cache Rebuilding has been completed!"); }
           }).fail(function() {
             woos_dontclose=0;
             jQuery("#result_field").html("Internal Server Error happened while building the cache data. It can be because of some limits of your server. Please contact to the plugin support team.");
             jQuery("#result_field").css("background","red");
             jQuery(".Rebuild-SearchBox-Cache a").text("Rebuilding failed!");
             jQuery(".inputrebuilder").val("Rebuilding failed!");
  });
    }
';
        if (get_option('do_woosearchbox_rebuild') != '') {
            echo 'guaven_woos_start_rebuild(woos_data);';
        }
        echo '
jQuery(".Rebuild-SearchBox-Cache a").attr("href","javascript://");
</script>
  <style>.Rebuild-SearchBox-Cache a {background:#008ec2 !important}</style>';
    }


    public function woos_rebuild_top_button($wp_admin_bar)
    {
        if ($this->dont_do_rebuild() == '0' or $this->dont_do_rebuild() == 'b0a1' or !is_admin()) {
            return;
        }
        $args = array(
            'id' => 'my_page',
            'title' => 'Rebuild Search Cache',
            'href' => 'javascript://',
            'meta' => array(
                'class' => 'Rebuild-SearchBox-Cache rebuilder gws_rebuilder'
            )
        );
        $wp_admin_bar->add_node($args);
    }

    public function set_memory_limit(){
      $default_memory_limit=(int)ini_get('memory_limit');
      $guaven_woos_memory_limit=get_option('guaven_woos_memory_limit');
      if($guaven_woos_memory_limit=='-1'){
        ini_set('memory_limit','-1');
        return;
      }
      $guaven_woos_memory_limit=(int)$guaven_woos_memory_limit;
      if ($guaven_woos_memory_limit>128){
        ini_set('memory_limit',$guaven_woos_memory_limit.'M');
      }
      elseif ($default_memory_limit<256){
        ini_set('memory_limit','512M');
      }
    }

    public function cache_rebuild_cron_mode(){

        if($this->argv[1] != $this->cron_token())return;

        $GLOBALS["woos_cron_step"]=1;
        $GLOBALS["woos_cron_step_max"]=10;//temp
        while (@ob_end_flush());      
        ob_implicit_flush(true);
        while ($GLOBALS["woos_cron_step"]<=$GLOBALS["woos_cron_step_max"]){
            $this->cache_rebuild_ajax_callback();
            $GLOBALS["woos_cron_step"]++;
            usleep(100000);
        }
        die('Finished'.$GLOBALS["woos_cron_step_max"].PHP_EOL);
    }
    public function cache_rebuild_ajax_callback()
    {

        $this->set_memory_limit();

        $step_size = apply_filters('gws_cache_rebuilder_stepsise',100);

        $_SERVER['HTTP_REFERER']=home_url();//to avoid wpml admin-side limits
        global $wpdb;
        $ptype="post_status='publish' and post_type='product'";
        if (get_option('guaven_woos_variation_skus') == 2) $ptype="(post_status='publish' and post_type='product') or post_type='product_variation' ";
        $pcount = $wpdb->get_var("select count(*) from $wpdb->posts where  ".$ptype);
        $mcount = (get_option('guaven_woos_maxprod') > 0 ? get_option('guaven_woos_maxprod') : 10000);
        $pcount = min($mcount, $pcount);

        $all_steps = ceil($pcount / $step_size);

        if (isset($this->argv[1]) and $this->argv[1] == $this->cron_token()) {
            $GLOBALS["woos_cron_step_max"]=$all_steps;
            $step_size = $pcount;
        } elseif(!empty($this->argv[1])) {
            return;
        } else {
            check_ajax_referer('cache_rebuild_ajax', 'ajnonce');
        }

        update_option('do_woosearchbox_rebuild', '');


        $msteps = (int) get_transient('guaven_woos_crs') + 1;

        $offset = $step_size * ($msteps - 1);

        if ($msteps == 1) {
            $this->cache_clean();
            $this->new_backend_cache_clean();
            $this->cache_rebuilder(0, array(), $step_size, 'guaven_woos_pinned_cache');

            if (get_option('guaven_woos_nomatch_pops') != '') {
                $max_pops_size = get_option('guaven_woos_maxres');
                $this->cache_rebuilder(0, array(), $max_pops_size, 'guaven_woos_populars_cache');
            }

            if (get_option('guaven_woos_catsearch') != '') {
                $max_pops_size    = get_option('guaven_woos_maxres');
                $shown_taxonomies = (get_option('guaven_woos_shown_taxonomies') == '') ? array(
                    'product_cat'
                ) : explode(",", get_option('guaven_woos_shown_taxonomies'));
                foreach ($shown_taxonomies as $key => $value) {
                    if ($value != '') {
                        $this->cache_category_rebuilder(trim($value) );
                    }
                }
            }
        }

        if ($this->fs_or_db() != '') {
            $old_option_data = unserialize(file_get_contents($this->fs_or_db()));
        } else {
            $old_option_data = unserialize(get_option('guaven_woos_product_cache'));
        }

        if (!is_array($old_option_data)) {
            $old_option_data = array();
        }

        set_transient('guaven_woos_crs', $msteps, 3600);

        $this->cache_rebuilder($offset, $old_option_data, $step_size, 'guaven_woos_product_cache', $pcount);

        if ($all_steps <= $msteps) {
            $final_version_for_js = $this->cache_final_prepare();
            $cache_dir=$this->fs_or_db('dir');
            $guaven_woows_jsfile = $cache_dir.'/guaven_woos_data'.GUAVEN_WOO_SEARCH_CACHE_ENDFIX.$this->get_current_language_code().'.js';
            if (is_writable($cache_dir)) {
                file_put_contents($guaven_woows_jsfile, $final_version_for_js);
                chmod($guaven_woows_jsfile, 0777);

                $guaven_woows_jsfile_lite = substr($guaven_woows_jsfile,0,-3).'_lite.js';

                $show_categories_in_pure_engine=apply_filters( 'gws_show_categories_in_pureengine',1);

                if($show_categories_in_pure_engine==1){
                    //this block stores all non-product data to the lite_cache JSON
                    $decoded_final_version_for_js=json_decode($final_version_for_js, true);
                    $decoded_final_version_for_js['guaven_woos_cache_html']=(Object)[];
                    $decoded_final_version_for_js['guaven_woos_cache_keywords']=(Object)[];
                    file_put_contents($guaven_woows_jsfile_lite, json_encode($decoded_final_version_for_js));
                }
                else {
                    //this block stores only pinned data to the lite_cache JSON
                    $final_version_for_js_lite['guaven_woos_built_date'] = json_decode($final_version_for_js, true)['guaven_woos_built_date'];
                    $final_version_for_js_lite['guaven_woos_cache_html'] = json_decode($final_version_for_js, true)['guaven_woos_cache_html'];
                    $final_version_for_js_lite['guaven_woos_cache_keywords'] = json_decode($final_version_for_js, true)['guaven_woos_cache_keywords'];
                    
                    file_put_contents($guaven_woows_jsfile_lite, json_encode(array(
                        'guaven_woos_cache_html' =>  (Object)[],
                        'guaven_woos_cache_keywords' =>  (Object)[],
                        'guaven_woos_pinned_html' => json_decode($final_version_for_js, true)['guaven_woos_pinned_html'],
                        'guaven_woos_pinned_keywords' => json_decode($final_version_for_js, true)['guaven_woos_pinned_keywords'],
                        'guaven_woos_pinned_cat_html' => json_decode($final_version_for_js, true)['guaven_woos_pinned_cat_html']
                    )));
                }
               

                if(get_option('guaven_woos_v2_2_structure')=='')update_option('guaven_woos_v2_2_structure', 1);

               
                chmod($guaven_woows_jsfile_lite, 0777);
                update_option('guaven_woos_nojsfile', '');
                update_option('guaven_woos_product_cache', '');
                delete_transient('guaven_woos_data_trend');
                echo 'Cache Data has successfully been generated.<br>';
            } else {
                echo '<b>Notice: </b> '.$cache_dir.' directory is not writable by the system. That\'s why generated js data will be
            printed in your html code, not in separated js file.(it works in both cases, so don\'t worry) If you want to held js data separately, just make your plugins folder
            writable and then Rebuild the Cache again.<br><br>';
                update_option('guaven_woos_nojsfile', 1);
                update_option('guaven_woos_js_data', $final_version_for_js, false);
            }
            echo '<span class="success_message"></span>';
            $js_css_version = (int) get_option('guaven_woos_jscss_version') + 1;
            update_option('guaven_woos_jscss_version', $js_css_version);
            
            delete_transient('guaven_woos_crs');
            $this->die_or_return();
        }
        echo round($msteps * 10000 / $all_steps) / 100;
        $this->die_or_return();
    }

    function die_or_return(){
        if(!empty($this->argv[1]))return;
        die();
    }

    private function cache_clean()
    {
        if ($this->fs_or_db() != '') {
            file_put_contents($this->fs_or_db(), '');
        }
        update_option('guaven_woos_product_cache', '');
        update_option('guaven_woos_pinned_cache', '');
        $this->live_server_cache_clean();
    }
    private function new_backend_cache_clean(){
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}woos_search_data");
    }
    private function live_server_cache_clean()
    {
        if (get_option("guaven_woos_live_server")=='') return;
        global $wpdb;
        update_option( 'guaven_woos_live_server_pid','');
        $wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."woos_search_cache");
    }

    private function cache_rebuilder($offset, $old_option_data, $step_size, $op_name = 'guaven_woos_product_cache', $totalproducts = 0)
    {
        $curlan=$this->get_current_language_code();
        $suppress_filters=empty($curlan);
        if (!$suppress_filters and isset($this->argv[2]) and $this->argv[2]!='' and strlen($this->argv[2])>1){
          global $sitepress;
          if (!empty($sitepress))
          $sitepress->switch_lang($this->argv[2]);
          //do_action( 'wpml_switch_language', $this->argv[2] ) //since 2.2.*, above 3 lines would gone away
        }
        if (isset($this->argv[3]) and $this->argv[3]>0 and is_multisite()) switch_to_blog((int)$this->argv[3]);

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $step_size,
            'offset' => $offset,
            'suppress_filters' => $suppress_filters
        );
        $skip_parent_variations='';
        if (get_option('guaven_woos_variation_skus') == 2) {
            $args['post_type']      = array(
                'product',
                'product_variation'
            );
            $skip_parent_variations = 1;
        }
        if (get_option('guaven_woos_customorder') != '') {
            $custom_order = get_option('guaven_woos_customorder');
            $order_val='DESC';
            $custom_order_arr=explode(" ",$custom_order);
            if (!empty($custom_order_arr[1])){$custom_order=$custom_order_arr[0];$order_val=$custom_order_arr[1];}
            if (strpos($custom_order, 'meta:') !== false) {
                $args['meta_key'] = substr($custom_order, 5);
                if (strpos($args['meta_key'], 'count') !== false or strpos($args['meta_key'], 'price') !== false or strpos($args['meta_key'], 'total') !== false) {
                    $args['orderby'] = 'meta_value_num';
                } else {
                    $args['orderby'] = 'meta_value';
                }
            }
            elseif (strpos($custom_order, 'metanum:') !== false) {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = substr($custom_order, 8);
            }
            else {
                $args['orderby'] = $custom_order;
            }
            $args['order'] = $order_val;
        }

        if ($op_name == 'guaven_woos_pinned_cache') {
            $pinstoimplode    = get_option('guaven_woos_pinneds');
            $args['post__in'] = explode(',', $pinstoimplode);
        } elseif ($op_name == 'guaven_woos_populars_cache') {
            $popviews = get_option('guaven_woos_popsmkey');
            if ($popviews != '') {
                $args['meta_key']       = $popviews;
                $args['orderby']        = 'meta_value_num';
                $args['order']          = 'DESC';
                $args['posts_per_page'] = get_option('guaven_woos_popsmax');
            }
        }

        if (get_option('guaven_woos_nostock') == '') {
            $args['meta_query'] = array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare'=>'!='
                )
            );
        }

        if (get_option('guaven_woos_excluded_cats') != '') {
            $excludedquery_fortaxquery = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => explode(",", preg_replace("/[^0-9,.]/", "", get_option('guaven_woos_excluded_cats'))),
                'operator' => 'NOT IN'
            );
        } else {
            $excludedquery_fortaxquery = array();
        }


        if (!empty($excludedquery_fortaxquery)) {
            $args['tax_query'] = array(
                $excludedquery_fortaxquery
            );
        }

        if (get_option('guaven_woos_excluded_prods') != '') {
            $args['post__not_in'] = explode(",", get_option('guaven_woos_excluded_prods'));
        }

        if (get_option('guaven_woos_removehiddens') != '' ) {
            if ($this->get_wc_version() == 2) {
                $args['meta_query'][] = array(
                    'key' => '_visibility',
                    'value' => 'hidden',
                    'compare' => '!='
                );
            }
            else{
                $args_temp = array();
                if (!empty($args['post__not_in'])) {
                    $args_temp = $args['post__not_in'];
                }
                $args['post__not_in'] = array_merge($this->get_hidden_products(), $args_temp);
            }      
        }
        if (get_option('guaven_woos_removefilters') != '') {remove_all_filters('pre_get_posts');}

        if($op_name == 'guaven_woos_product_cache' and isset($args['orderby']) and isset($args['order'])
           and $args['orderby']!='ID'
        ){
            $args['orderby']=[$args['orderby']=>$args['order'],'ID'=>'DESC'];
            unset($args['order']);
        } 

        $args=apply_filters('gws_cache_rebuilder_args',$args);
        
        $args['post_status']='publish';
        $args['no_found_rows'] = true;
        $args['update_post_meta_cache'] = false;
        $args['update_post_term_cache'] = false;

        $products_for_json = get_posts($args);
        $products_array    = $this->json_processing($products_for_json,$skip_parent_variations);
        if ($op_name == 'guaven_woos_product_cache' and $this->fs_or_db() != '') {
            $this->save_products_to_cache_table($products_array);
            file_put_contents($this->fs_or_db(), serialize(array_merge($old_option_data, $products_array)));
            chmod($this->fs_or_db(), 0777);
            return;
        }
        update_option($op_name, serialize(array_merge($old_option_data, $products_array)), false);
    }

    public function save_products_to_cache_table($products_array){
        global $wpdb;
        $cache_li_loop_main  = $this->cache_li_loop($products_array, 'persprod');
        if(empty($cache_li_loop_main[1]))return;
        $sql = "INSERT INTO  {$wpdb->prefix}woos_search_data (product_ID, displayed_html_data, searchable_text_data, price) VALUES ";
        $sql_orig=$sql;
        foreach($cache_li_loop_main[1] as $i => $one_product_cache ){
            $price = (float) wc_get_product($i)->get_price();
            $sql .= "($i, '".esc_sql($cache_li_loop_main[0][$i])."', '".esc_sql($one_product_cache)."', $price),";
        }
        if($sql[strlen($sql) - 1] == ',')
            $sql[strlen($sql) - 1] = ';';
        if($sql_orig!=$sql){
            $result = $wpdb->query($sql);
        }
    }

    public function json_processing($products_for_json,$skip_parent_variations='')
    {
        $products_array = array();
        $guaven_woos_taxenabled=0;//deprecated
        $permalink_structure = get_option('guaven_woos_permalink');

        foreach ($products_for_json as $key => $value) {
            $_product = wc_get_product($value->ID);
            if (empty($_product) or (!empty($skip_parent_variations) and $this->get_product_type($_product) == 'variable')) {
                continue;
            }
            if ($value->post_type=='product_variation' and $value->post_parent==0 ){continue;}
            if ($value->post_parent > 0 and !empty($skip_parent_variations)){
              $varparentpost=get_post($value->post_parent);
              if ($varparentpost->post_status!='publish') {continue;}
            }
            //wpml part from v1.2.4
            $langfixer_arr        = $this->langfixer($value);
            $langfixer            = $langfixer_arr[0];
            $guaven_woos_wpml_key = $langfixer_arr[1];
            //end of wpml part

            $title_and_hidden_sku = ($value->post_title . ' <span class="woos_sku"> ' . 
            (get_post_meta($value->ID, '_sku', true)!=$value->post_title?(get_post_meta($value->ID, '_sku', true) . 
            (strpos(get_post_meta($value->ID, '_sku', true), ' ') !== false ? ('</span><span class="gwshd">, ' . str_replace(" ", "", get_post_meta($value->ID, '_sku', true))) : '')):'') . ' </span>' . $guaven_woos_wpml_key);

            if (get_option('guaven_woos_variation_skus') == 1) {
                //gather variation sku-s
                global $wpdb;
                $variation_skus_str = '';
                $variation_skus     = $wpdb->get_results("select a.meta_value as mv from $wpdb->postmeta a inner join $wpdb->posts b on a.post_id=b.ID
where meta_key='_sku' and b.post_type='product_variation' and b.post_parent=" . intval($value->ID));
                foreach ($variation_skus as $vskkey) {
                    $variation_skus_str .= $vskkey->mv . ', ' . (strpos($vskkey->mv, ' ') !== false) ? str_replace(" ", "", $vskkey->mv) . ', ' : '';
                }
                if ($variation_skus_str != '') {
                    $title_and_hidden_sku .= (' <span class="woos_sku woos_sku_variations"> ' . substr($variation_skus_str, 0, -2) . ' </span>');
                }
            }
            if ($this->get_product_type($_product) == 'grouped'){
              $title_and_hidden_sku.='<span class="gwshd">'.$this->get_group_children_skus($value->ID).'</span>';
            }

            $add_description_too = get_option('guaven_woos_add_description_too');
            if ($add_description_too) {
                $title_and_hidden_sku .= preg_replace('/[\x00-\x1F\x7F]/u', '', (' <span class="guaven_woos_hidden_description"> ' . preg_replace('/\s+/', ' ', trim(strip_tags($value->post_content))) . ' </span>'));
            }

            $guaven_woos_add_shortdescription_too = get_option('guaven_woos_add_shortdescription_too');
            if ($guaven_woos_add_shortdescription_too) {
                $title_and_hidden_sku .= preg_replace('/[\x00-\x1F\x7F]/u', '', (' <span class="guaven_woos_hidden_description gwsbsd"> ' . preg_replace('/\s+/', ' ', trim(strip_tags($value->post_excerpt))) . ' </span>'));
            }

            $add_tags_too    = get_option('guaven_woos_customtags');
            $add_wootags_too = get_option('guaven_woos_wootags');
            $tba_searchdata='';
            if ($add_tags_too != '' or $add_wootags_too != '') {
                $taxes_tba      = array_merge(explode(',', $add_tags_too), explode(',', $add_wootags_too));

                $tba_searchdata =$this->get_term_and_attribute_data($value->ID,$taxes_tba,$_product);
                if ($value->post_type=='product_variation'){
                  $_product_parent = wc_get_product($value->post_parent);
                  if (!empty($_product_parent)){
                    $tba_searchdata.=$this->get_term_and_attribute_data($value->post_parent,$taxes_tba,$_product_parent);
                  }
                }
                $title_and_hidden_sku .= (' <span class="guaven_woos_hidden guaven_woos_hidden_tags"> ' . $tba_searchdata . ' </span>');
            }

            $custom_fields             = get_option('guaven_woos_customfields');
            $custom_fields_search_data = '';
            if ($custom_fields != '') {
                $cf_arr = explode(',', $custom_fields);
                foreach ($cf_arr as $cf_arr_el) {
                    if ($cf_arr_el != '') {
                        $tempstrarr = get_post_meta($value->ID, trim($cf_arr_el), true);
                        if (is_array($tempstrarr)) {
                            $tempstrarr = json_encode($tempstrarr,JSON_UNESCAPED_UNICODE);
                        }
                        $custom_fields_search_data .= ' ' . $tempstrarr;
                        if (get_option('guaven_woos_variation_skus') == 1) {
                            $custom_fields_search_data .= ' ' . $this->add_variations_metadata($value->ID, $cf_arr_el);
                        }
                    }
                }
                $custom_fields_search_data=apply_filters('gws_cache_metadata',$custom_fields_search_data);
                $title_and_hidden_sku .= (' <span class="guaven_woos_hidden"> ' . $custom_fields_search_data . ' </span>');
            }

            $add_synonyms_too = get_option('guaven_woos_synonyms');
            if ($add_synonyms_too != '') {
                $ptitle           = get_the_title($value->ID) . ($custom_fields_search_data != '' ? ' ' . $custom_fields_search_data : '') . ($tba_searchdata != '' ? ' ' . $tba_searchdata : '');
                $corresp_synonyms = $this->synonym_list($add_synonyms_too, $ptitle);
                if ($corresp_synonyms != '') {
                    $title_and_hidden_sku .= (' <span class="gwshd"> ' . str_replace(array(
                        "'",
                        '"',
                        '’'
                    ), '', stripslashes($corresp_synonyms)) . ' </span>');
                }
            }

            $vterms          = get_the_terms($value->ID, 'product_cat');
            $vproduct_cat_id = array();
            if (is_array($vterms)){
              foreach ($vterms as $vterm) {
                  $vproduct_cat_id[] = $vterm->term_id;
              }
            }
            $title_and_hidden_sku .= (' <span class="gwshd">~' . implode('~', $vproduct_cat_id) . '~</span>');

            $title_and_hidden_sku=apply_filters('gws_cache_rebuilder_result_line',$title_and_hidden_sku,$value->ID);

            $thumb_quality              = get_option('guaven_woos_thumb_quality') != '' ? get_option('guaven_woos_thumb_quality') : 'thumbnail';
            $products_array[$value->ID] = $this->price_and_parparser($value, $langfixer, $_product, $guaven_woos_taxenabled, $title_and_hidden_sku, $permalink_structure, $thumb_quality);
        }
        return $products_array;
    }


    public function get_term_and_attribute_data($pid,$taxes_tba,$_product){
      $tba_searchdata=' ';
      $taxes_tba_attributes=[];
      $taxes_tba_non_attributes=[];
      foreach($taxes_tba as $tax_tba){
        if(strpos($tax_tba,'pa_')===0){
            $taxes_tba_attributes[]=$tax_tba;
        }
        elseif(!empty($tax_tba)){
            $taxes_tba_non_attributes[]=$tax_tba;
        }
      }
      $product_attributes=array_keys($_product->get_attributes());
      $product_attributes=array_map(  function($a){return strpos($a,"pa_")!==0?("pa_".$a):$a;} ,$product_attributes);
      $taxes_tba_filtered=array_merge($taxes_tba_non_attributes,array_intersect($taxes_tba_attributes,$product_attributes)); 
      foreach ($taxes_tba_filtered as $ttba) {
          if ($ttba != '') {
              $term_list_itstag = 0;
              $term_list        = wp_get_post_terms($pid, $ttba);
              foreach ($term_list as $term_single) {
                  if (!empty($term_single->name) and strpos($tba_searchdata, $term_single->name) === false) {
                      $tba_searchdata .= $term_single->name . ' ';
                      $term_list_itstag = 1;
                  }
                  if (!empty($term_single->parent) and $term_single->parent > 0) {
                      $pterm = get_term($term_single->parent);
                      if (strpos($tba_searchdata, $pterm->name) === false) {
                          $tba_searchdata .= $pterm->name . ' ';
                      }
                  }
              }
              if ($term_list_itstag == 0 and strpos($ttba, 'pa_') === 0) {
                  $term_list_custom = $_product->get_attribute(str_replace("pa_", "", $ttba));
                  $tba_searchdata .= $term_list_custom . ' ';
              }
          }
      }
      return $tba_searchdata;
    }

    function get_group_children_skus($pid){
      global $wpdb;
      $ret='';
      $children_pid=$wpdb->get_var($wpdb->prepare("select meta_value from $wpdb->postmeta where post_id=%d and meta_key='_children'",$pid));
      if (!empty($children_pid)) {
        $children_pid=unserialize($children_pid);
        $children_pid="(".implode(",",$children_pid).")";
        $children_skus=$wpdb->get_results("select meta_value from $wpdb->postmeta where meta_key='_sku' and post_id IN ".esc_sql($children_pid));
        foreach($children_skus as $csku){
          $ret.=$csku->meta_value.' , ';
        }
      }
      return $ret;
    }

    public function enqueue()
    {
        $vers = intval(get_option('guaven_woos_jscss_version'))+GUAVEN_WOO_SEARCH_SCRIPT_VERSION;
        wp_enqueue_script('guaven_woos_admin', plugin_dir_url(__FILE__) . 'assets/guaven_woos_admin.js?v=' . $vers, array(
            'jquery'
        ), true);
        wp_localize_script('guaven_woos_admin', 'guaven_woos_notice_dismissed', array(
            'action' => 'guaven_notice_dismissed',
            'nonce' => wp_create_nonce('notice_dismissed')
        ));
        wp_enqueue_style('guaven_woos_admin', plugin_dir_url(__FILE__) . 'assets/guaven_woos_admin.css?v=' . $vers, true);
        if (!isset($_GET['page']) or strpos($_GET['page'], 'class-search-analytics') === false) {
            return;
        }
        wp_enqueue_script('guaven_woos_chartist', plugin_dir_url(__FILE__) . 'assets/chartist.min.js');
        wp_enqueue_style('guaven_woos_chartist', plugin_dir_url(__FILE__) . 'assets/chartist.min.css');
    }



    public function langfixer($value)
    {
        $guaven_woos_wpml_key = '';
        $langfixer            = '';
        if ($this->get_current_language_code()!='') {
                $guaven_woos_wpml_key = ' <span id="woolan_' . $this->get_current_language_code() . '"></span>';
                $langfixer            = '&lang=' . $this->get_current_language_code();
        }
        return array(
            $langfixer,
            $guaven_woos_wpml_key
        );
    }



    public function price_and_parparser($value, $langfixer, $_product, $guaven_woos_taxenabled, $title_and_hidden_sku, $permalink_structure, $thumb_quality)
    {
      $_regular_price=($_product->get_price_html());
      $_sale_price = '';//deprecated

        if ($permalink_structure == '' and $this->get_product_type($_product) != 'variation') {
            $perlink = 'gwp={gwsvid}' . $langfixer;
        } else {
            $perlink = get_permalink($value->ID);
        }

        $thumbid = get_post_thumbnail_id($value->ID);
        if ($thumbid=='' and $this->get_product_type($_product) == 'variation'){
          $thumbid = get_post_thumbnail_id($value->post_parent);
        }

        $length         = get_post_meta($value->ID, '_length', true);
        $weight         = get_post_meta($value->ID, '_weight', true);
        $height         = get_post_meta($value->ID, '_height', true);
        $width          = get_post_meta($value->ID, '_width', true);
        $total_sales    = get_post_meta($value->ID, 'total_sales', true);
        $stock_quantity = get_post_meta($value->ID, '_stock', true);
        $stock_status = get_post_meta($value->ID, '_stock_status', true);
        if (!empty($stock_quantity)) {
            $stock_quantity = (int) $stock_quantity;
        }
        if ($this->get_product_type($_product) == 'variable') {
            $add_to_cart = 'href="#" style="display:none" ';
        } else {
            $add_to_cart = 'href="/?post_type=product&add-to-cart=' . $value->ID . '" class="gwsq_' . $stock_status  . '" data-product_id="' . $value->ID . '" rel="nofollow" data-quantity="1"';
        }

        if (!empty($thumbid)) {
            $thumber_arr = wp_get_attachment_image_src($thumbid, $thumb_quality);
            $thumber     = $thumber_arr[0];
        } else {
            $thumber = (wc_placeholder_img_src());
        }

        $products_array = array(
            'ID' => $value->ID,
            'thumb' => $thumber,
            'url' => $perlink,
            'title' => $title_and_hidden_sku,
            'price' => $_regular_price,
            'product_type'=>$_product->get_type(),
            'sale' => '',
            'length' => $length,
            'weight' => $weight,
            'height' => $height,
            'width' => $width,
            'total_sales' => $total_sales,
            'stock_quantity' => $stock_quantity,
            'stock_status' => $stock_status,
            'add_to_cart' => $add_to_cart,
        );
        $guaven_woos_layout = stripslashes(get_option('guaven_woos_layout'));
        $displayed_brands=explode(",",get_option('guaven_woos_wootags').(!empty(get_option('guaven_woos_customtags'))?(','.get_option('guaven_woos_customtags')):'') );
        
        $taxonomy_separator_character=apply_filters('gws_taxonomy_separator_character',', ');

        foreach($displayed_brands as $dbs){
          $objid=$value->post_type=='product_variation'?$value->post_parent:$value->ID;
          $product_pwbbrabd = get_the_terms($objid, trim($dbs));
  		    if (strpos(trim($dbs),'pa_')!==0){
            if (!is_wp_error($product_pwbbrabd) and $product_pwbbrabd){
                $pwval_arr=[];
                foreach($product_pwbbrabd as $pwkey=>$pwvalue){
                    if( !empty($pwvalue->term_id)){
                       $pwval_arr[]=$pwvalue->name;
                    }
                }
                $products_array['taxonomies'][$dbs]=implode($taxonomy_separator_character,$pwval_arr);
            }
          }
          else {
            $products_array['attributes'][$dbs]=$_product->get_attribute($dbs);
          }
        }
        return $products_array;
    }

    public function synonym_list($add_synonyms_too, $ptitle)
    {
        $corresp_synonyms = array();
        $ptitle=$this->lowercase($ptitle);
        $synonym_list     = explode(',', $add_synonyms_too);
        $synonym_list_res = array();
        $title_elements   = explode(' ', ($ptitle));
        foreach ($synonym_list as $syn) {
            $syn_lr = explode('-', $this->lowercase($syn));
            if(empty($syn_lr[1]))continue;
            if (in_array(trim(str_replace("_", "-", $syn_lr[0])), $title_elements) or
            (strpos($ptitle, ' ') !== false and strpos((html_entity_decode($ptitle)), trim(str_replace("_", "-", $syn_lr[0]))) !== false)) {
                $synonym_list_res[] = trim(str_replace("_", "-", $syn_lr[1]));
            } elseif (in_array(trim(str_replace("_", "-", $syn_lr[1])), $title_elements) or
            (strpos($ptitle, ' ') !== false and strpos((html_entity_decode($ptitle)), trim(str_replace("_", "-", $syn_lr[1]))) !== false)) {
                $synonym_list_res[] = trim(str_replace("_", "-", $syn_lr[0]));
            }
        }

        return implode(',', $synonym_list_res);
    }

    private function cache_category_rebuilder($tip)
    {

        $pcats_arg = array(
            'taxonomy' => $tip,
            'hide_empty' => false,
            'number' => 1000
        );
        if (get_option('guaven_woos_excluded_cats') != '') {
            $pcats_arg['exclude'] = explode(",", preg_replace("/[^0-9,.]/", "", get_option('guaven_woos_excluded_cats')));
        }
        $pcats_arg=apply_filters('gws_cache_category_rebuilder_args',$pcats_arg);

        $pcats = get_terms($tip, $pcats_arg);

        $products_cats_array = array();
        foreach ($pcats as $key => $value) {
          $thumbnail_id = get_term_meta( $value->term_id, 'thumbnail_id', true );
          if ($thumbnail_id>0)
          $image_url = wp_get_attachment_url( $thumbnail_id );
          elseif(get_term_meta($value->term_id,$value->taxonomy.'_swatches_id_photo',true)!=''){
            $image_url = wp_get_attachment_url( get_term_meta($value->term_id,$value->taxonomy.'_swatches_id_photo',true) );
          }
          else $image_url='';
            $products_cats_array[$value->term_id] = array(
                'ID' => $value->term_id,
                'title' => $value->name,
                'slug' => $value->slug,
                'parent' => $value->parent,
                'description' => $value->description,
                'image'=>$image_url
            );
        }
        $tip = preg_replace("/[^A-Za-z0-9 ]/", '', $tip);
        update_option('guaven_woos_' . $tip . '_cache', serialize($products_cats_array));
    }

    private function cache_featured_category($termids)
    {
        if (empty($termids)) {
            return '';
        }
        $termids             = explode(",", $termids);
        $pcats_arg           = array(
            'taxonomy' => 'product_cat', // >=WP 4.5.0
            'hide_empty' => false,
            'include' => $termids
        );

        $pcats_arg=apply_filters('gws_cache_featured_category_args',$pcats_arg);

        $pcats               = get_terms('product_cat', $pcats_arg);
        $ret                 = '';
        $products_cats_array = array();
        foreach ($pcats as $key => $value) {
            $products_cats_array = array(
                'ID' => $value->term_id,
                'title' => $value->name,
                'slug' => $value->slug,
                'parent' => $value->parent,
                'description' => $value->description
            );
            $ret .= '<li class="gwspc">' . $this->parse_template($products_cats_array, 'taxonomy:product_cat') . '</li>';
        }
        return $ret;
    }

    public function cache_final_prepare()
    {
        if ($this->fs_or_db() != '') {
            $guaven_woos_product_cache = unserialize(file_get_contents($this->fs_or_db()));
        } else {
            $guaven_woos_product_cache = unserialize(get_option('guaven_woos_product_cache'));
        }

        $guaven_woos_pinned_cache   = unserialize(get_option('guaven_woos_pinned_cache'));
        $guaven_woos_populars_cache = unserialize(get_option('guaven_woos_populars_cache'));

        $guaven_woos_category_cache = unserialize(get_option('guaven_woos_category_cache'));

        $cache_li_loop_main        = $this->cache_li_loop($guaven_woos_product_cache);
        $cache_li_loop_pinned      = $this->cache_li_loop($guaven_woos_pinned_cache);
        $cache_li_loop_pops        = $this->cache_li_loop($guaven_woos_populars_cache);
        $cache_li_loop_pinned_cats = $this->cache_featured_category(get_option('guaven_woos_pinneds_cat'));

        $shown_taxonomies = (get_option('guaven_woos_shown_taxonomies') == '') ? array(
            'product_cat'
        ) : explode(",", get_option('guaven_woos_shown_taxonomies'));
        $cat_html_data    = [];
        $cat_extend_data  = array();
        foreach ($shown_taxonomies as $key => $value) {
            $value_raw                  = trim($value);
            $value                      = preg_replace("/[^A-Za-z0-9 ]/", '', $value);
            $guaven_woos_category_cache = unserialize(get_option('guaven_woos_' . $value . '_cache'));
            if (!empty($value)) {
                $cache_li_loop_cats = $this->cache_li_loop($guaven_woos_category_cache, 'taxonomy:' . $value_raw);
            }
            $cat_html_data['guaven_woos_' . $value . '_html'] = $cache_li_loop_cats[0]; 
            $cat_html_data['guaven_woos_' . $value . '_keywords'] = $cache_li_loop_cats[1];
            
            $cat_extend_data['html'][]    = 'guaven_woos_' . $value . '_html';
            $cat_extend_data['keyword'][] = 'guaven_woos_' . $value . '_keywords';
        }
        if (count($cat_extend_data['html']) == 1) {
            $cat_extend_data['final']['guaven_woos_category_keywords'] = $cat_extend_data['keyword'][0];
            $cat_extend_data['final']['guaven_woos_category_html'] = $cat_extend_data['html'][0];
        } else {
            $cat_extend_data['final']['guaven_woos_category_keywords'] = implode(',', $cat_extend_data['keyword']);
            $cat_extend_data['final']['guaven_woos_category_html'] =  implode(',', $cat_extend_data['html']);
        }
        return json_encode(array_merge(array(
            "guaven_woos_built_date" => gmdate("Y-m-d H:i:s"),
            "guaven_woos_cache_html" => $cache_li_loop_main[0],
            "guaven_woos_cache_keywords" =>  $cache_li_loop_main[1],
            "guaven_woos_pinned_html" => $cache_li_loop_pinned[0],
            "guaven_woos_pinned_keywords" => $cache_li_loop_pinned[1],
            "guaven_woos_pinned_cat_html" => $cache_li_loop_pinned_cats,
            "guaven_woos_populars_html" =>  $cache_li_loop_pops[0],
            "guaven_woos_populars_keywords" => $cache_li_loop_pops[1]
        ), $cat_html_data, $cat_extend_data ['final']),JSON_UNESCAPED_UNICODE );
    }

    public function cache_li_loop($guaven_woos_product_cache, $tip = '')
    {
        $gwsi        = 0;
        $htmlkeys    = [];
        $keywordkeys = [];
        $translit_data  = get_option('guaven_woos_translit_data');
        if (is_array($guaven_woos_product_cache)) {
            foreach ($guaven_woos_product_cache as $guaven_woos_pck => $guaven_woos_pcv) {
                ++$gwsi;
                $htmlkeys[$guaven_woos_pck] = $this->results_layout($tip, $gwsi, $guaven_woos_pcv["ID"], $this->parse_template($guaven_woos_pcv, $tip));
                $keywordpart=str_replace(array(
                    "\n",
                    "\r"
                ), '', (stripslashes($guaven_woos_pcv['title'])));
                $translit_part='';
                if ($translit_data==1){
                  $translit_part=$this->translitter($keywordpart);
                  if ($this->lowercase($keywordpart)==$translit_part) {
                    $translit_part='';
                  }
                  else {
                    $translit_part=' <span class="gwstrn">'.(strip_tags(stripslashes($translit_part))).'</span> ';
                  }
                }
                $keywordkeys[$guaven_woos_pck] = $keywordpart.$translit_part;
            }
        } else {
            return array(
                [],
                []
            );
        }
        foreach ($htmlkeys as &$htmlaval)
            $htmlaval = str_replace(array(
                '<li class="guaven_woos_suggestion_list' . $tip . '" tabindex="',
                '"><div class="guaven_woos_div"><img class="guaven_woos_img" src="',
                '"></div><div class="guaven_woos_titlediv">',
                '</div></a> </li>',
                '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">',
                '</span>',
                '<small>',
                '</small>'
            ), array(
                '{{l}}',
                '{{d}}',
                '{{i}}',
                '{{e}}',
                '{{c}}',
                '{{p}}',
                '{{m}}',
                '{{a}}'
            ), $htmlaval);      
        foreach($keywordkeys as &$keywordval)
            $keywordval = str_replace(array(
                '</span> <span class="guaven_woos_hidden guaven_woos_hidden_tags">',
                '<span class="guaven_woos_hidden">',
                '</span> <span class="gwshd">',
                '</span> <span class="woos_sku woos_sku_variations">',
                '<span class="woos_sku">',
                '<span class="gwstrn">',
                '<span class="gwshd">',
                '</span><span class="gwstrn">',
                '</span>',
                '<span class="guaven_woos_hidden_description'
            ), array(
                '{{s}}',
                '{{h}}',
                '{{g}}',
                '{{v}}',
                '{{k}}',
                '{{n}}',
                '{{j}}',
                '{{w}}',
                '{{p}}',
                '{{o}}'
            ), $keywordval);
        return array(
            $htmlkeys,
            $keywordkeys
        );
    }

    public function parse_template($guaven_woos_pcv, $tip = '')
    {
        if (strpos($tip, 'taxonomy:') === 0) {
            $tip      = str_replace("taxonomy:", "", $tip);
            $parcat_s = '';
            if (!empty($guaven_woos_pcv['parent']) and $guaven_woos_pcv['parent'] > 0) {
                $parcat   = get_term($guaven_woos_pcv['parent']);
                $parcat_s = !empty($parcat->name)?('<span class="woos_cat_par_span">' . $parcat->name . ' / </span>'):'';
            }
            $pclink = get_term_link($guaven_woos_pcv['slug'], $tip);
            if (is_wp_error($pclink)) {
                return;
            } //for rare cases
            return '<a class="guaven_woos_titlediv_cat" href="' . $pclink . '">' .(!empty($guaven_woos_pcv['image'])?'<img class="gws_cat_img" src="'.$guaven_woos_pcv['image'].'">':''). ($parcat_s . $guaven_woos_pcv['title']) . '</a>';
        }

        $saleprice = $guaven_woos_pcv['sale'];
        $price     = $guaven_woos_pcv['price'];
        if ($saleprice != '') {
            $price = '<del>' . $price . '</del>';
        }
        $currency_regular = ''; //deprecated
        $currency_sale    = ''; //deprecated

        $wpuploaddir = wp_upload_dir();
        $find        = array(
            '{url}',
            '{title}',
            '{imgurl}',
            '{price}',
            '{saleprice}',
            '{currency_regular}',
            '{currency_sale}',
            '{length}',
            '{height}',
            '{weight}',
            '{width}',
            '{total_sales}',
            '{stock_quantity}',
            '{stock_status}',
            '{add_to_cart}',
        );
        $replace     = array(
            $guaven_woos_pcv['url'],
            str_replace(array(
                "\n",
                "\r"
            ), '', $guaven_woos_pcv['title']),
            $guaven_woos_pcv['thumb'],
            $price,
            $saleprice,
            $currency_regular,
            $currency_sale,
            isset($guaven_woos_pcv['length']) ? $guaven_woos_pcv['length'] : '',
            isset($guaven_woos_pcv['height']) ? $guaven_woos_pcv['height'] : '',
            isset($guaven_woos_pcv['weight']) ? $guaven_woos_pcv['weight'] : '',
            isset($guaven_woos_pcv['width']) ? $guaven_woos_pcv['width'] : '',
            isset($guaven_woos_pcv['total_sales']) ? $guaven_woos_pcv['total_sales'] : '',
            isset($guaven_woos_pcv['stock_quantity']) ? $guaven_woos_pcv['stock_quantity'] : '',
            isset($guaven_woos_pcv['stock_status']) ? $guaven_woos_pcv['stock_status'] : '',
            isset($guaven_woos_pcv['add_to_cart']) ? $guaven_woos_pcv['add_to_cart'] : '',
        );

        $data = stripslashes(get_option('guaven_woos_layout'));
        global $wpdb;

        //taxonomy printing on layout
          $taxolist=get_taxonomies();
          foreach ($taxolist as $taxokey=>$taxovalue) {
            if (strpos($data,$taxokey)!==false ) {
              $find[]='{'.$taxokey.'}';
              if (!empty($guaven_woos_pcv['attributes'][$taxokey])){
                $replace[]=($guaven_woos_pcv['attributes'][$taxokey]);
              }
              elseif (!empty($guaven_woos_pcv['taxonomies'][$taxokey])){
                $replace[]=($guaven_woos_pcv['taxonomies'][$taxokey]);
              }
              else {
                $replace[]='';
              }
            }
          }



        if ($tip != 'persprod') {
            $replace[1] = '{{t}}';
            $replace[2] = str_replace($wpuploaddir['baseurl'], '{{u}}', $guaven_woos_pcv['thumb']);
        }

        $guaven_woos_pcv['custom_fields']=get_option('guaven_woos_customfields');
        if (!empty($guaven_woos_pcv['custom_fields'])) {
            $guaven_woos_pcv['custom_fields']=explode(",",$guaven_woos_pcv['custom_fields']);
            foreach ($guaven_woos_pcv['custom_fields'] as $cs_key) {
                $cs_value=get_post_meta($guaven_woos_pcv['ID'],$cs_key,true);
                $find[]    = '{' . $cs_key . '}';
                $replace[] = $cs_value;
            }
        }

        
        $find = apply_filters('gws_dynamic_tags_find',$find,$guaven_woos_pcv);
        $replace = apply_filters('gws_dynamic_tags_replace',$replace,$guaven_woos_pcv);

        return str_replace($find, $replace, $data);
    }


    public function results_layout($tip, $gwsi, $gwid, $parsed)
    {
        $custom_layout = get_option('guaven_woos_results_layout');
        if (empty($custom_layout)) {
            $custom_layout = '<li class="guaven_woos_suggestion_list{guaven_woos_lay_tip}" tabindex="{guaven_woos_lay_gwsi}" id="prli_{guaven_woos_lay_id}">  {guaven_woos_lay_parsed} </li>';
        }
        $scodes       = array(
            '{guaven_woos_lay_tip}',
            '{guaven_woos_lay_gwsi}',
            '{guaven_woos_lay_id}',
            '{guaven_woos_lay_parsed}'
        );
        $final_layout = str_replace($scodes, array(
            $tip,
            $gwsi,
            $gwid,
            $parsed
        ), $custom_layout);
        $final_layout=preg_replace( "/\r|\n/", "", $final_layout );
        return $final_layout;
    }


    public function add_variations_metadata($parent_ID, $field)
    {
        global $wpdb;
        $ret        = '';
        $variations = $wpdb->get_results("select ID from $wpdb->posts where post_parent=" . esc_sql($parent_ID) . " and post_type='product_variation'");
        if (empty($variations)) {
            return;
        }
        foreach ($variations as $variation) {
            $tempstrarr = get_post_meta($variation->ID, $field, true);
            if (is_array($tempstrarr)) {
                $tempstrarr = json_encode($tempstrarr,JSON_UNESCAPED_UNICODE);
            }
            $ret .= ' ' . $tempstrarr;
        }
        return $ret;
    }

    public function admin_menu()
    {
        $role_to_use_the_plugin=apply_filters('gws_role_to_use_the_plugin','manage_options');
        add_submenu_page('woocommerce', 'Guaven Woo Search', 'Search Engine', $role_to_use_the_plugin, __FILE__, array(
            $this,
            'run'
        ));
    }

    public function kses($str)
    {
        return esc_attr(stripslashes($str));
    }

    function lowercase($str){
      if (function_exists('mb_strtolower')){
        return mb_strtolower($str);
      }
      return strtolower($str);
    }

    public function get_hidden_products()
    {
        global $wpdb;
        $excl_keys     = array();
        $final_exclude = array();
        $excludes1     = $wpdb->get_results("select term_id,name from $wpdb->terms where name='exclude-from-search' or name='exclude-from-catalog'");

        foreach ($excludes1 as $excl1) {
            $termtaxid=$wpdb->get_var($wpdb->prepare("select term_taxonomy_id from $wpdb->term_taxonomy where term_id=%d",$excl1->term_id));
            $excludes2 = $wpdb->get_results("select a.object_id object_id,a.term_taxonomy_id from $wpdb->term_relationships a
            inner join $wpdb->posts b on a.object_id=b.ID
            where b.post_type='product' and a.term_taxonomy_id=" . ((int) $termtaxid));

            foreach ($excludes2 as $excl2) {
                $excl_keys[$excl1->name][] = $excl2->object_id;
            }
        }
        if (!empty($excl_keys['exclude-from-search']) and !empty($excl_keys['exclude-from-catalog'])) {
            $final_exclude = array_intersect($excl_keys['exclude-from-search'], $excl_keys['exclude-from-catalog']);
        }
        if (get_option('guaven_woos_variation_skus') == 2 and !empty($final_exclude)){
            $var_excl_sql="select ID from $wpdb->posts where (post_type='product_variation' and post_parent 
            IN (".esc_sql(implode(",",$final_exclude)).") )";
            $var_excl_sql=apply_filters('gws_variation_excludes',$var_excl_sql);
            $variations_of_hidden_parents=$wpdb->get_col();
            $final_exclude=array_merge($variations_of_hidden_parents,$final_exclude );
        }
        return $final_exclude;
    }

    public function create_cache_dir(){
      $updir=wp_upload_dir();
      $cache_dir=$updir['basedir'].'/woos_search_engine_cache';
      if (file_exists($cache_dir) and is_writable($cache_dir)) return $cache_dir;
      if (!file_exists($cache_dir) and is_writable($updir['basedir'])) {
        mkdir($cache_dir);
        chmod($cache_dir,0777);
        return $cache_dir;
      }
      return '';
    }

    public function fs_or_db($dironly='')
    {
        if (get_option('guaven_woos_rebuild_via') != 'fs' and $dironly=='') {
            return;
        }
        $cache_dir=$this->create_cache_dir();
        if (empty($cache_dir)) $cache_dir= GUAVEN_WOO_SEARCH_PLUGIN_PATH . 'public/assets';
        if (!empty($dironly))return $cache_dir;
        return $cache_dir.'/guaven_woos_data'.GUAVEN_WOO_SEARCH_CACHE_ENDFIX.'_processing.js';
    }

    public function get_product_type($prdct)
    {
        if ($this->get_wc_version() < 3) {
            return $prdct->product_type;
        }
        return $prdct->get_type();
    }
    public function get_wc_version()
    {
        if (defined('WC_VERSION')) {
            $guaven_woos_wooversion = substr(WC_VERSION, 0, 1);
        }
         else {
            $guaven_woos_wooversion = 2;
        }
        return $guaven_woos_wooversion;
    }

    public function get_current_language_code(){
        if (defined('ICL_LANGUAGE_CODE')) return ICL_LANGUAGE_CODE;
        if (isset($this->argv[2])) return $this->argv[2];
        return '';
    }

    public function translitter($str)
    {
        $specials           = array(
            ' x ',"'", '"', 'ä', 'ö', 'ü', 'à', 'â', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ù', 'û', 'ÿ', 'å', 'ó', 'ú', 'ů', 'ý', 'ž', 'á', 'č', 'ď', 'ě',
            'í', 'ň', 'ř', 'š', 'ť', 'ñ', 'ç', 'ğ', 'ı', 'İ', 'ş', 'ã', 'õ', 'ά', 'έ', 'ή', 'ί', 'ϊ', 'ΐ', 'ό', 'ύ', 'ϋ', 'ΰ', 'ώ', 'ə', 'а', 'б',
            'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы',
            'ь', 'э', 'ю', 'я', 'љ', 'њ', 'ѓ', 'ќ', 'џ', '-', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ',
            'ς', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω','Ã­','σ',
            'đ','ệ','ơ','ư','ả','ờ','ă','ỏ',"ố","ế",'ắ','ậ','ử','ộ','ẳ','ứ','ự','ớ','ấ','ổ','ẫ','ổ','ầ','ợ','ừ','ữ', 'ỳ','ỹ','ẩ','ẻ','ẽ','ẹ','ì','ị','ĩ','ỉ','ò','ọ','ồ','ỗ','ỡ',
            'ș','ț', 'ă', 'î','â'
        );
        $specials_replacers = array(
            "x","", "", 'a', 'o', 'u', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'y', 'a', 'o', 'u', 'u', 'y', 'z', 'a', 'c', 'd', 'e',
            'i', 'n', 'r', 's', 't', 'n', 'c', 'g', 'i', 'i', 's', 'a', 'o', 'α', 'ε', 'η', 'ι', 'ι', 'ι', 'ο', 'u', 'υ', 'υ', 'ω', 'e', 'a', 'b',
            'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'c', 'sh', 'sht', 'a', 'i',
            'y', 'e', 'yu', 'ya', 'lj', 'nj', 'g', 'k', 'dz', ' ', 'a', 'b', 'g', 'd', 'e', 'z', 'h', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'ch', 'ps', 'w','i','s',
            'd','e','o','u','a','o','a','o','o','e','a','a','u','o','a','u','u','o','a','o','a','o','a','o','u','u', 'y','y','a','e','e','e','i','i','i','i','o','o','o','o','o',
            's','t','a','i','a'
        );
        $str                = $this->lowercase($str);
        return str_replace($specials, $specials_replacers, $str);
    }

    public static function ui_layouts(){
        return [
            'default'=>'List view with thumbnails - default',
            'grid_1'=>'Grid 1',
            'grid_2'=>'Grid 2',
            'list_text'=>'List view without thumbnails',
        ];
    }

    public function attribute_checkboxes($gws_wootags)
    {
        global $wpdb;
        $ret = ''; ?>
<li> <label><input type="checkbox" value="product_cat" <?php
        echo in_array('product_cat', $gws_wootags) ? 'checked' : ''; ?> />Product Categories</label></li>
<li> <label><input type="checkbox" value="product_tag" <?php
        echo in_array('product_tag', $gws_wootags) ? 'checked' : ''; ?> />Product Tags</label></li>
<?php
        $allatts = $wpdb->get_results("select * from " . $wpdb->prefix . "woocommerce_attribute_taxonomies");
        foreach ($allatts as $att) {
            echo '<li> <label><input type="checkbox" value="pa_' . $att->attribute_name . '"  ' . (in_array('pa_' . $att->attribute_name, $gws_wootags) ? 'checked' : '') . '/>Attribute "' . $att->attribute_label . '"</label></li>';
        }
    }

    private function search_data_db_construct()
    {
        global $wpdb;
        $tablename = $wpdb->prefix . "woos_search_data";
        $wpdb->query("
          DROP TABLE IF EXISTS `" . $tablename . "`;");
        $wpdb->query("CREATE TABLE `" . $tablename . "` (
          `ID` bigint(20) NOT NULL AUTO_INCREMENT,
          `product_ID` bigint(20) NOT NULL, 
          `displayed_html_data` text CHARACTER SET utf8, 
          `searchable_text_data` text CHARACTER SET utf8, 
          `price` varchar(20),
          `minprice` varchar(20),
          `maxprice` varchar(20),
          `cache_version` int(20),
          PRIMARY KEY (`ID`),
          UNIQUE (`product_ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }
    private function trend_db_construct()
    {
        global $wpdb;
        $tablename = $wpdb->prefix . "woos_search_trends";
        $wpdb->query("
          DROP TABLE IF EXISTS `" . $tablename . "`;");
        $wpdb->query("CREATE TABLE `" . $tablename . "` (
      `ID` bigint(20) NOT NULL AUTO_INCREMENT,
      `post_id` bigint(20) NOT NULL,
      `search_count` int(11) NOT NULL,
      `user_info` varchar(100) NOT NULL,
      `point` tinyint(1) NOT NULL DEFAULT '1',
      `search_day` date NOT NULL,
      PRIMARY KEY (`ID`),
      UNIQUE KEY `post_id_search_day_user_info_point` (`post_id`,`search_day`,`user_info`,`point`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }
    private function searchcache_db_construct()
    {
        global $wpdb;
        $tablename = $wpdb->prefix . "woos_search_cache";
        $wpdb->query("
          DROP TABLE IF EXISTS `" . $tablename . "`;");
        $wpdb->query("CREATE TABLE `" . $tablename . "` (
          `ID` bigint(20) NOT NULL AUTO_INCREMENT,
          `query` text CHARACTER SET utf8 NOT NULL,
          `result_ids` text CHARACTER SET utf8 NOT NULL,
          `language` varchar(10) NOT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }
}