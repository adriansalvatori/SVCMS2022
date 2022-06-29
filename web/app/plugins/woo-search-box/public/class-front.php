<?php

if (!defined('ABSPATH')) {
    die;
}

class Guaven_woo_search_front
{
    private $cookieprods;
    public $js_css_version;

    public function __construct()
    {
        if (get_option('guaven_woos_ispers') != '') {
            $this->cookieprods = 1;
        }
        $this->js_css_version = (int) get_option('guaven_woos_jscss_version') + GUAVEN_WOO_SEARCH_SCRIPT_VERSION;
    }

    public function personal_interest_collector()
    {
        if ( ($this->skip_transients() or is_singular('product')) and !empty($this->cookieprods) ) {
            $products_personal = '';
            if (!empty($_COOKIE['guaven_woos_lastvisited'])) {
                $products_personal_ids = unserialize(get_transient($_COOKIE['guaven_woos_lastvisited'].'_ids'));
                $uniqcookieid      = $_COOKIE['guaven_woos_lastvisited'];
            } else {
                $uniqcookieid = 'guaven_woos_' . uniqid();
            }
            if(empty($products_personal_ids)) {$products_personal_ids=array();}
            $guaven_woo_search_admin = new Guaven_woo_search_admin();

            if (get_option('guaven_woos_taxes') != '') {
                $guaven_woos_taxenabled = 1;
            } else {
                $guaven_woos_taxenabled = 0;
            }

            $permalink_structure   = get_option('guaven_woos_permalink');
            $number_format         = get_option('guaven_woos_numberformat') != '' ? 0 : 2;
            $htmlkeys='';
            if (is_singular('product')){
              global $post;
              array_unshift($products_personal_ids,$post->ID);
              $products_personal_ids=array_unique($products_personal_ids);
            }
            $max_cookie_res_count  = get_option('guaven_woos_persmax') > 0 ? get_option('guaven_woos_persmax') : 5;
            if (count($products_personal_ids) > ($max_cookie_res_count + 1)) {
                $products_personal_ids = array_slice($products_personal_ids, 0, $max_cookie_res_count);
            }

            foreach($products_personal_ids as $ppid){
              $postitself=get_post($ppid);
              $langfixer_arr           = $guaven_woo_search_admin->langfixer($postitself);
              $langfixer               = $langfixer_arr[0];
              $_product                = wc_get_product($postitself->ID);
              if(empty($_product)) continue;
              $title_and_hidden_sku  = get_the_title($ppid);
              $new_products_personal = $guaven_woo_search_admin->price_and_parparser($postitself, $langfixer, $_product, $guaven_woos_taxenabled, $title_and_hidden_sku, $permalink_structure, $number_format, 'thumbnail');
              $htmlkeys.='<li class=\"guaven_woos_suggestion_list\" tabindex=\"' . $postitself->ID . '\" id=\"prli_'.$postitself->ID.'\">  ' . $guaven_woo_search_admin->parse_template($new_products_personal, 'persprod') . ' </li>';
            }

            set_transient($uniqcookieid.'_ids', serialize($products_personal_ids), 86400);
            set_transient($uniqcookieid, $htmlkeys, 86400);
            if(!headers_sent())setcookie('guaven_woos_lastvisited', $uniqcookieid, time() + 86400, '/', null, 0);
        }
    }

    public function local_values(){
      $wpupdir               = wp_upload_dir();
      $guaven_woos_pure_home = explode("?", home_url());
      $enqname               = 'guaven_woos';

      $cache_url=plugin_dir_url(__FILE__) . 'assets';
      $updir=wp_upload_dir();
      $cache_dir=$updir['basedir'].'/woos_search_engine_cache';
      if (file_exists($cache_dir) and is_writable($cache_dir)) $cache_url=$updir['baseurl'].'/woos_search_engine_cache';

      if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"]) or (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"]=='on') ){
        	$cache_url=str_replace("http:","https:",$cache_url);
      	}

      $guaven_woos_backend=(int)get_option('guaven_woos_backend');
      if ($guaven_woos_backend==1) $guaven_woos_backend=3;
      $data_path=$cache_url.'/guaven_woos_data'.GUAVEN_WOO_SEARCH_CACHE_ENDFIX.$this->get_current_language_code().'.js';
      if (get_option("guaven_woos_live_server")==1){
        $data_path=$cache_url.'/guaven_woos_data'.GUAVEN_WOO_SEARCH_CACHE_ENDFIX.$this->get_current_language_code().'_lite.js';
      }

      $guaven_woos_local_values = array(
          'focused' => 0,
          'backend' => $guaven_woos_backend,
          'search_results' => home_url('/search-results'),
          'data_path' => $data_path.'?v=' . $this->js_css_version,
          'engine_start_delay' => wp_is_mobile() ? 700 : 500,
          'highlight' => get_option('guaven_woos_highlight') != '' ? 1 : 0,
          'disable_meta_correction' => get_option('guaven_woos_disable_meta_correction') != '' ? 1 : 0,
          'show_all_text' => $this->get_string(get_option('guaven_show_all_text')),
          'showinit' => get_option('guaven_woos_showinit_t') != '' ? stripslashes($this->get_string(get_option('guaven_woos_showinit_t'), 'html')) : '',
          'shownotfound' => $this->get_string(get_option('guaven_woos_showinit_n'), 'html'),
          'populars_enabled' => get_option('guaven_woos_nomatch_pops') != '' ? 1 : 0,
          'categories_enabled' => get_option('guaven_woos_catsearch') != '' ? 1 : 0,
          'cmaxcount' => get_option('guaven_woos_catsearchmax') > 0 ? (int) get_option('guaven_woos_catsearchmax') : 5,
          'correction_enabled' => get_option('guaven_woos_corr_act') != '' ? 1 : 0,
          'pinnedtitle' => (get_option('guaven_woos_pinneds') != '' or get_option('guaven_woos_pinneds_cat') != '') ? $this->get_string(get_option('guaven_woos_pinnedt'), 'html') : '',
          'trendtitle' => get_option('guaven_woos_data_trend_num') != '0' ? $this->get_string(get_option('guaven_woos_trendt'), 'html') : '',
          'sugbarwidth' => get_option('guaven_woos_sugbarwidth') > 0 ? (round(get_option('guaven_woos_sugbarwidth') * 100) / 10000) : '1',
          'minkeycount' => get_option('guaven_woos_min_symb_sugg') > 0 ? (int) get_option('guaven_woos_min_symb_sugg') : 3,
          'maxcount' => get_option('guaven_woos_maxres') != '' ? (int) get_option('guaven_woos_maxres') : 10,
          'maxtypocount' => get_option('guaven_woos_whentypo') != '' ? (int) get_option('guaven_woos_whentypo') : 'maxcount',
          'large_data' => get_option('guaven_woos_large_data') > 0 ? (int) get_option('guaven_woos_large_data') : '0',
          'translit_data' => get_option('guaven_woos_translit_data'),
          'updir' => $wpupdir['baseurl'],
          'exactmatch' => get_option('guaven_woos_exactmatch') > 0 ? (int) get_option('guaven_woos_exactmatch') : '0',
          'perst' => ($this->cookieprods != '' and !empty($_COOKIE["guaven_woos_lastvisited"])) ? $this->get_string(get_option('guaven_woos_perst')) : '',
          'persprod' => ($this->cookieprods != '' and !empty($_COOKIE["guaven_woos_lastvisited"])) ? urlencode(stripslashes($this->get_string(stripslashes(get_transient($_COOKIE["guaven_woos_lastvisited"])), 'html'))) : '',
          'mobilesearch' => get_option('guaven_woos_mobilesearch') != '' ? 1 : 0,
          'wpml' => ($this->get_current_language_code()!='' ? 'woolan_' . $this->get_current_language_code() : ''),
          'homeurl' => $guaven_woos_pure_home[0],
          'selector' => get_option('guaven_woos_selector') != '' ? stripslashes(get_option('guaven_woos_selector')) : '[name="s"]',
          'live_filter_selector' => get_option('guaven_woos_filter_selector') != '' ? stripslashes(get_option('guaven_woos_filter_selector')) : '',
          'orderrelevancy'=>get_option('guaven_woos_disablerelevancy') != '' ? 0 : 1,
          'simple_expressions'=>get_option('guaven_woos_simple_expressions') != '' ? 1 : 0,
          'expression_segments'=>get_option('guaven_woos_expression_segments')!=''?explode(",",get_option('guaven_woos_expression_segments')):array('under','around','above'),
          'currency_abv'=>get_woocommerce_currency(),
          'currency_symb'=>get_woocommerce_currency_symbol(),
          'currency_singular' => get_option('guaven_woos_expression_spell_s')!=''?esc_attr(get_option('guaven_woos_expression_spell_s')):'',
          'currency_plural'=>get_option('guaven_woos_expression_spell_p')!=''?esc_attr( get_option('guaven_woos_expression_spell_p') ):'',
          'delay_time'=>get_option('guaven_woos_delay_time')!=''?intval( get_option('guaven_woos_delay_time') ):'500',
          'live_server_path'=> 'guaven_purengine_search', 
          'validate_code'=>wp_create_nonce( "gws_live_validate_code" ),
          'live_server'=>get_option("guaven_woos_live_server"),
          'ga_enabled' => get_option('guaven_woos_ga') != '' ? 1 : 0,
          'utm_enabled' => get_option('guaven_woos_utm') != '' ? 1 : 0,
          'cache_version_checker'=>get_option('guaven_woos_cache_version_checker') != '' ? 'yes' : '',
          'v2_2_structure'=>get_option('guaven_woos_v2_2_structure') != '' ? 1 : 0,
          'live_ui_layout'=> get_option("guaven_woos_live_ui_layout"),
      );
      $trendnum=get_option('guaven_woos_data_trend_num');
      if (!empty($trendnum)) {
          $guaven_woos_data_trend                  = $this->trend_calculator();
          
		  $guaven_woos_data_trend[0]=array_slice($guaven_woos_data_trend[0],0,$trendnum);
		  $guaven_woos_data_trend[1]=array_slice($guaven_woos_data_trend[1],0,$trendnum);

          if(empty($guaven_woos_local_values['v2_2_structure'])):
          $guaven_woos_local_values['trending'][0] = '{' . str_replace("'", "\'", $guaven_woos_data_trend[0]) . '}';
          $guaven_woos_local_values['trending'][1] = '{' . str_replace("'", "\'", $guaven_woos_data_trend[1]) . '}';
          else:
            $guaven_woos_local_values['trending'][0] = json_encode($guaven_woos_data_trend[0]);
            $guaven_woos_local_values['trending'][1] = json_encode($guaven_woos_data_trend[1]);
          endif;
      }

      $guaven_woos_local_values=$this->multi_currency_args($guaven_woos_local_values);
      $guaven_woos_local_values=apply_filters('gws_local_values_args',$guaven_woos_local_values);

      return array('enqname'=>$enqname,'local_values'=>$guaven_woos_local_values);
    }

    public function enqueue()
    {
        if (get_option('guaven_woos_firstrun') != '') {
            $local_values=$this->local_values();
            $guaven_woos_main_js_url=apply_filters( 'guaven_woos_main_js_url', plugin_dir_url(__FILE__) . 'assets/' . $local_values['enqname'] . '.js' );
            wp_register_script($local_values['enqname'], $guaven_woos_main_js_url, array(
                'jquery'
            ), $this->js_css_version, true);
            wp_localize_script($local_values['enqname'], $local_values['enqname'], $local_values['local_values']);
            wp_enqueue_script($local_values['enqname']);
        }

        wp_enqueue_style('guaven_woos', plugin_dir_url(__FILE__) . 'assets/guaven_woos.css', array(), $this->js_css_version);

        $guaven_woos_live_ui_layout_files=Guaven_woo_search_admin::ui_layouts();
        $guaven_woos_live_ui_layout_file = esc_attr(get_option("guaven_woos_live_ui_layout")); 
        if(in_array($guaven_woos_live_ui_layout_file,array_keys($guaven_woos_live_ui_layout_files))){
            $layout_file=$guaven_woos_live_ui_layout_file;
        }
        else {
            $layout_file='default';
        }
        wp_enqueue_style('guaven_woos_layout', plugin_dir_url(__FILE__) . 'assets/gws_layouts/'.$layout_file.'.css', array(), $this->js_css_version);
    }

    public function trend_calculator()
    {
        $ret   = get_transient('guaven_woos_data_trend');
        $count = get_option('guaven_woos_data_trend_num');
        if (empty($ret)
         or $this->skip_transients()
            ) {
            $date_for_data       = get_option('guaven_woos_trend_days') > 0 ? get_option('guaven_woos_trend_days') : 3;
            $transient_life_time = get_option('guaven_woos_trend_refresh') > 0 ? (60 * get_option('guaven_woos_trend_refresh')) : 600;
            global $wpdb;
            $res       = $wpdb->get_results($wpdb->prepare("SELECT post_id,sum(point) say FROM " . $wpdb->prefix . "woos_search_trends " . "WHERE search_day>( CURDATE() - INTERVAL %d DAY ) " . "GROUP BY post_id ORDER BY say DESC limit %d", $date_for_data, $count));
            $trend_ids = array();
            foreach ($res as $key => $value) {
                $trend_ids[] = $value->post_id;
            }

            if (empty($trend_ids)) {
                return array(
                    '',
                    ''
                );
            }
            $args                      = array(
                'post_type' => 'product',
                'posts_per_page' => $count,
                'suppress_filters' => true,
                'post__in' => $trend_ids,
                'orderby' => 'post__in',
                'order' => 'desc'
            );

            $args=apply_filters('gws_trend_calculator_args',$args);

            $trend_get_posts           = get_posts($args);
            $guaven_woo_search_admin   = new Guaven_woo_search_admin();
            $guaven_woos_product_cache = $guaven_woo_search_admin->json_processing($trend_get_posts);
            $ret                       = $guaven_woo_search_admin->cache_li_loop(array_values($guaven_woos_product_cache));
            set_transient('guaven_woos_data_trend', serialize($ret), $transient_life_time);
        } else {
            $ret = unserialize($ret);
        }

        return $ret;
    }

    private function skip_transients(){
      $skip_transients=apply_filters('woos_trend_skip_transient',false);
      if($skip_transients)return $skip_transients;
      if (defined('WOOCOMMERCE_MULTICURRENCY_VERSION')) return true;
      return false;
    }

    public function get_jscss_version(){
        echo $this->js_css_version;
    }

    public function add_purchase_score_when_new_order($order_id)
    {
        $prods = array();
        if (defined('WC_VERSION') and substr(WC_VERSION, 0, 1) == 2) {
            $order = new WC_Order($order_id);
            $items = $order->get_items();
            foreach ($items as $item) {
                $prods[] = $item['product_id'];
            }
        } else {
            $order = wc_get_order($order_id);
            $items = $order->get_items();
            foreach ($items as $item) {
                $prods[] = $item->get_product_id();
            }
        }

        foreach ($prods as $key => $value) {
            $unid = (!empty($_COOKIE["gws_unid"]) and $_COOKIE["gws_unid"] != '') ? $_COOKIE["gws_unid"] : '';
            $this->guaven_woos_trend_inserter($value, $unid, 5);
        }
    }

    public function inline_js()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/view.php';
    }

    public function kses($str)
    {
        return addslashes(wp_kses(stripslashes($str), array(
            'a' => array(
                'href' => array(),
                'class' => array()
            ),
            'img' => array(
                'src' => array(),
                'class' => array()
            ),
            'p' => array(
                'class' => array()
            ),
            'strong' => array(),
            'i' => array(),
            'del' => array(),
            'em' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(
              'class' => array(),
              'id'=>array(),
              'tabindex'=>array()
            ),
            'b' => array(),
            'br' => array(),
            'div' => array(
                'class' => array()
            ),
            'span' => array(
                'class' => array()
            ),
            'small' => array()
        )));
    }

    public function get_string($str, $stype = '')
    {
        if (strpos($str, 'wpml') === false) {
            return $stype == 'html' ? $this->kses($str) : esc_js($str);
        }

        if ($this->get_current_language_code()!='') {
            $filtered         = urldecode(html_entity_decode(esc_attr($str)));
            $strarr           = simplexml_load_string($filtered);
            $current_language = $this->get_current_language_code();
            if (!empty($strarr->$current_language)) {
                return $stype == 'html' ? $this->kses($strarr->$current_language) : esc_js($strarr->$current_language);
            }
        }

        return $stype == 'html' ? $this->kses($str) : esc_js($str);
    }

    public function get_current_language_code(){
        $sapi_type = php_sapi_name();
        if (defined('ICL_LANGUAGE_CODE') and substr($sapi_type, 0, 3) != 'cli') return ICL_LANGUAGE_CODE;
        return '';
    }

    public function guaven_woos_tracker_inserter($failsuccess, $state, $froback, $unid)
    {
        $failed_arr                           = explode(", ", $failsuccess);
        $failed_arr_f[count($failed_arr) - 1] = $failed_arr[count($failed_arr) - 1];
        for ($i = count($failed_arr) - 2; $i >= 0; $i--) {
            if (strpos($failed_arr[$i + 1], $failed_arr[$i]) === false) {
                $failed_arr_f[$i] = $failed_arr[$i];
            }
        }

        $failed_arr_f = array_unique($failed_arr_f);
        global $wpdb;
        foreach ($failed_arr_f as $faf) {
            if (!empty($faf)) {
                $wpdb->insert($wpdb->prefix . "woos_search_analytics", array(
                    'keyword' => $faf,
                    'created_date' => date("Y:m:d"),
                    'user_info' => $unid,
                    'state' => $state,
                    'device_type' => (wp_is_mobile() ? 'mobile' : 'desktop'),
                    'side' => $froback
                ), array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ));
            }
        }
    }

    public function guaven_woos_tracker_callback()
    {
        if (!isset($_POST["failed"]) or !isset($_POST["success"]) or !isset($_POST["corrected"]) or !isset($_POST["unid"])) {
            exit;
        }

        global $wpdb;
        $current_timestamp = time();
        $addcontrol        = esc_attr($_POST["addcontrol"]);
        if ($current_timestamp - intval($addcontrol) > 3600) {
            exit;
        }
        do_action('guaven_woos_tracker_insert',$_POST["unid"]);
        check_ajax_referer('guaven_woos_tracker_' . $addcontrol, 'ajnonce');
        $this->guaven_woos_tracker_inserter($_POST["failed"], 'fail', 'frontend', $_POST["unid"]);
        $this->guaven_woos_tracker_inserter($_POST["success"], 'success', 'frontend', $_POST["unid"]);
        $this->guaven_woos_tracker_inserter($_POST["corrected"], 'corrected', 'frontend', $_POST["unid"]);
        exit;
    }

    public function guaven_woos_trend_inserter($pid, $unid, $score = 1)
    {
        $insert_or_not=apply_filters('gws_trend_inserter',true);
        if(!$insert_or_not)return;
        global $wpdb;
        $wpdb->query($wpdb->prepare("
      INSERT INTO " . $wpdb->prefix . "woos_search_trends (post_id, search_count, user_info,point,search_day) VALUES(%d, 1, %s,%d,%s)
      ON DUPLICATE KEY UPDATE search_count=search_count+1", $pid, $unid, $score, date("Y-m-d")));
    }

    function multi_currency_args($guaven_woos_local_values){
        //for WooCommerce Multicurrency
        if (defined('WOOCOMMERCE_MULTICURRENCY_VERSION') and get_option('woocommerce_multicurrency_rates')!=''
        and !empty($_COOKIE['woocommerce_multicurrency_forced_currency'])){
          $allrates=get_option('woocommerce_multicurrency_rates');
          if (isset($allrates[$_COOKIE['woocommerce_multicurrency_forced_currency']])) {
            $curcurcode=$_COOKIE['woocommerce_multicurrency_forced_currency'];
            $curcurposition=get_option('woocommerce_multicurrency_price_format_'.$curcurcode)=='%2$s%1$s'?'right':'left';
            $guaven_woos_local_values['woo_multicurrency']=array('position'=>$curcurposition,
            'symbol'=>html_entity_decode(get_woocommerce_currency_symbol()),'rate'=>$allrates[$curcurcode],
            'conv'=>get_option('woocommerce_multicurrency_fee_percent'),'charm'=>get_option('woocommerce_multicurrency_price_charm'),
            'round'=>get_option('woocommerce_multicurrency_round_to'));
          }
        }
        elseif(class_exists('WooCommerce_Ultimate_Multi_Currency_Suite_Main')){
          //for MultiCurrency Suite plugin
          $wcums_cur_cur=!empty($_COOKIE['wcumcs_user_currency_session'])?$_COOKIE['wcumcs_user_currency_session']:$this->getcookie('wcumcs_user_currency_session');
          if(empty($wcums_cur_cur))return;
          $wcumcs_default=get_option('wcumcs_woocommerce_base_currency');
          $wcumcs_listdata = get_option('wcumcs_available_currencies');
          $wcumcs_listdata_array = json_decode($wcumcs_listdata , true);
          if(!empty($wcumcs_listdata_array[$wcums_cur_cur]) 
          and $wcumcs_default!=$wcums_cur_cur){
              $wcums_rate=get_option('wcumcs_exchange_rate_'.$wcums_cur_cur);
              $guaven_woos_local_values['woo_multicurrency']=
              array(
                  'position'=>$wcumcs_listdata_array[$wcums_cur_cur]['position'],
                  'symbol'=>html_entity_decode(get_woocommerce_currency_symbol()),
                  'rate'=>$wcums_rate,
                  'conv'=>'',
                  'charm'=>'',
                  'round'=>''
              );
          }
        }
        return $guaven_woos_local_values;
      }
  
    function getcookie($name) {
        $cookies = [];
        $headers = headers_list();
        foreach($headers as $header) {
            if (strpos($header, 'Set-Cookie: ') === 0) {
                $value = str_replace('&', urlencode('&'), substr($header, 12));
                parse_str(current(explode(';', $value, 1)), $pair);
                $cookies = array_merge_recursive($cookies, $pair);
            }
        }
        if(isset($cookies[$name])){
            $cookies_name=explode(";",$cookies[$name]);
            return $cookies_name[0];
        }
        return '';
    }

    public function guaven_woos_trend_data()
    {
        if (!isset($_POST["pid"]) or !isset($_POST["unid"])) {
            exit;
        }
        $this->guaven_woos_trend_inserter($_POST["pid"], $_POST["unid"]);
        exit;
    }

    public function standalone()
    {
        ob_start();
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/standalone.php';
        return ob_get_clean();
    }

}
