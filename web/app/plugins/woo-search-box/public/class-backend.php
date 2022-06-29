<?php

if (!defined('ABSPATH')) {
    die;
}

class Guaven_woo_search_backend
{
    protected $replacement_occured;
    protected $find_posts_all_var;
    protected $where;
    public function backend_search_filter($where = '')
    {
        //WOOF filter support
        if (isset($_GET["woof_text"]) and strpos($where,'LOWER(post_title) REGEXP')!==false and strpos($where,"post_type = 'product'")!==false){
            $this->replacement_occured=1;
        }
        
        if (empty($this->replacement_occured)) return $where;

        $recalc_where=apply_filters('gws_sql_restructure_where_part',false);
        if(!empty($this->where) and !$recalc_where)return $this->where; 
        
        $search_query_local_raw=$this->search_query();
        $is_woo                = 1;
        $search_query_local=$this->character_remover($search_query_local_raw);
        $found_posts = $this->find_posts_all($search_query_local);
        $checkkeyword          = $found_posts[0];
        $sanitize_cookie_final = $found_posts[1];
        $leftpart   = explode(" ", $checkkeyword);
        $gsquery    = esc_attr($search_query_local);
        $leftpart_2 = explode(" ", $gsquery);

        global $wpdb;
        if (empty($sanitize_cookie_final)) {
            $sanitize_cookie_final = 0;
        }
        $newwhere='';
        if (get_option('guaven_woos_variation_skus')==2){
          $newwhere_arr=array();
          $proparents=$wpdb->get_results("select post_parent from $wpdb->posts where $wpdb->posts.ID in (" . $sanitize_cookie_final . ")");
          foreach ($proparents as $pps){
            if ($pps->post_parent>0) $newwhere_arr[]=$pps->post_parent;
          }
          if (!empty($newwhere_arr)) {
            $newwhere= " or $wpdb->posts.ID in (" . implode(",",$newwhere_arr) . ")";
          }
        }
	    $where .= " AND ( $wpdb->posts.ID in (" . $sanitize_cookie_final . ") ".$newwhere."  )";
        $where=str_replace('?(.*)',' ',$where);
        $where = $this->query_cleaner(strtolower($where), $checkkeyword,$search_query_local_raw);
        $where = $this->query_cleaner(strtolower($where), $gsquery,$search_query_local_raw);
        $where=str_replace(strtolower($wpdb->prefix), $wpdb->prefix, $where);

        $ignored_products = get_option('guaven_woos_excluded_prods');
        if (!empty($ignored_products)) {
            $where .= " and ($wpdb->posts.ID not in (" . esc_sql($ignored_products) . "))";
        }
        do_action('guaven_woos_where_processing',$where);
        $this->where=$where;
        return $where;
    }

    function backend_search_replacer($search){
      if (is_admin() or empty($_GET["s"]) or empty($_GET["post_type"]) or $_GET["post_type"]!='product') {
        return $search;
      }
      $backend_enable        = get_option('guaven_woos_backend');
      $search_query_local_raw=$this->search_query();

      if (!in_array($backend_enable,array(1,3)) or is_admin() or empty($search_query_local_raw)) {return $search;}

      $search_query_local=explode(" ",strtolower($this->character_remover($search_query_local_raw)));
      if ( strpos($search,'post_title LIKE')!==false and
      ( strpos(strtolower($search),$search_query_local_raw)!==false or
      strpos(strtolower($search),$search_query_local[0])!==false) ) {
        $this->replacement_occured=1;
        return '';
      }
      $this->replacement_occured='';
      return $search;
    }

    function character_remover($str){
      $str=strtolower(str_replace(array(
         "'",
         "/",
         '"',
         //"_",
         "\\"
      ), "", stripslashes($str)));
      $str=str_replace("_","\\\\_",$str);
      $ignorearr = explode(",", get_option('guaven_woos_ignorelist'));
      if (!empty($ignorearr)) $str=str_replace($ignorearr,"",$str);
      return trim($str);
    }

    function slug_formatting($str){
      $transient_name=$this->character_remover($str);
      $transient_name=substr($transient_name, 0, 166);
      $guaven_woo_search_admin = new Guaven_woo_search_admin();
      $transient_name=$guaven_woo_search_admin->translitter($transient_name);
      $transient_name=str_replace(" ", "_", $transient_name);
      return $transient_name;
    }

    public function backend_search_orderby($orderby_statement)
    {
        if ( 
            (isset($_GET["orderby"]) and  strpos($_SERVER["REQUEST_URI"],'orderby')!==false ) 
            or 
            empty($this->replacement_occured)
            ) {return $orderby_statement;}

        $search_query_local=$this->search_query();
        $found_posts = !empty($this->find_posts_all_var)?$this->find_posts_all_var:$this->find_posts_all($search_query_local);
        if (!empty($found_posts[1])) {
            global $wpdb;
            $orderby_statement = "FIELD( $wpdb->posts.ID, " . $found_posts[1] . ") ASC";
        }
        return $orderby_statement;
    }

 
    public function find_posts_all($search_query_local)
    {
        if (get_option("guaven_woos_live_server") == ''){
            $found_posts = $this->find_posts($search_query_local);
        } 
        else {
            $found_posts = $this->purengine_find_posts($search_query_local);
        } 
        $this->find_posts_all_var=$found_posts;
        return $found_posts;
    }

    public function find_posts($search_query_local)
    {
        $sanitize_cookie = '';
        $checkkeyword    = '';
        $guaven_woo_search_admin = new Guaven_woo_search_admin();
        $search_query_local_tr_name=$guaven_woo_search_admin->translitter($search_query_local);
        if (!empty($search_query_local)) {
            if (!empty($_POST["guaven_woos_ids"])){
                $sanitize_cookie = preg_replace("/[^0-9,.]/", "", $_POST["guaven_woos_ids"] );
                $clean_kw=$this->slug_formatting(urldecode($_POST["s"]));
                set_transient('gws_' .$clean_kw , $sanitize_cookie, 12 * 3600);
                header("location: ".home_url('?post_type=product&s='.urlencode(stripslashes($_POST["s"]) ) ) );
                exit;
            }
            if(empty($sanitize_cookie )){
                $sanitize_cookie = preg_replace("/[^0-9,.]/", "", get_transient('gws_' . substr(str_replace(" ", "_", $search_query_local_tr_name), 0, 166)));
            }
            $checkkeyword    = $guaven_woo_search_admin->lowercase(urldecode(substr($search_query_local, 0, 166)));
        }

        if ($sanitize_cookie != '') {
            if (substr($sanitize_cookie, -1) != ',') {
                $sanitize_cookie = $sanitize_cookie . ',';
            }
            $sanitize_cookie_final = esc_sql(substr($sanitize_cookie, 0, -1));
        } else {
            $sanitize_cookie_final = '';
        }

        return array(
            $checkkeyword,
            $sanitize_cookie_final
        );
    }

    public function query_cleaner($where, $keyword,$search_query_local)
    {

      $keyword_arr = explode(" ", esc_sql($search_query_local));
      foreach ($keyword_arr as $kwkey => $kwvalue) {
          $where = str_replace(array(
              ']' . strtolower($kwvalue),
              '%' . strtolower($kwvalue) . '%',
              strtolower($kwvalue) . '[',
              strtolower($kwvalue) . '{',
              '}' . strtolower($kwvalue)
          ), array(
              ']',
              '%%',
              '[',
              '{',
              '}'
          ), $where);
      }

        $keyword     = str_replace(array(
            '"'
        ), "", $keyword);
        $keyword_arr = explode(" ", $keyword);
        foreach ($keyword_arr as $kwkey => $kwvalue) {
            $where = str_replace(array(
                ']' . strtolower($kwvalue),
                '%' . $kwvalue . '%',
                strtolower($kwvalue) . '[',
                strtolower($kwvalue) . '{',
                '}' . strtolower($kwvalue)
            ), array(
                ']',
                '%%',
                '[',
                '{',
                '}'
            ), $where);
        }

        $keyword_reg=implode("?(.*)",$keyword_arr)."'";
        if (!empty($keyword_reg)){
          $where=str_replace("REGEXP '".$keyword_reg,"like '%%'",$where);
        }

        $where = str_replace("AND post_title REGEXP '[[:<:]][[:>:]]'", "", $where);
        $where = str_replace("regexp '".strtolower($kwvalue)."'", "like '%%'", $where); //WOOF search query cleaner
        $where = str_replace("regexp '".strtolower($keyword)."'", "like '%%'", $where); //WOOF search query cleaner
        $where = str_replace("regexp '".str_replace("-","\-",strtolower($keyword))."'", "like '%%'", $where); //WOOF search query cleaner
        return $where;
    }
    //cookie based search end

    public function standalone_search_resetter($query)
    {
        if ($query->is_main_query() and isset($_GET["guaven_woos_stdnln"]) and !empty($_GET["s"])) {
            $query->set('s', "");
            $query->set('post_type', "");
        }
    }

    public function guaven_woos_pass_to_backend()
    {
        $sanitized_ids = preg_replace("/[^0-9,.]/", "", $_REQUEST["ids"]);
        if (!empty($sanitized_ids)) {
          $clean_kw=$this->slug_formatting($_REQUEST["kw"]);
          set_transient('gws_' .$clean_kw , $sanitized_ids, 12 * 3600);
        } else set_transient('gws_' . $clean_kw,'0,0', 12 * 3600);
        echo 'ok';
        die();
    }

    public function force_search_reload()
    {
        $search_query_local=$this->search_query();
        if (!empty($search_query_local) and !empty($_GET["post_type"]) and $_GET["post_type"] == 'product') {
            if (get_option('guaven_woos_backend') != 3 or get_option("guaven_woos_live_server") != '') {
                return;
            }
            $transient_name=$this->slug_formatting($search_query_local);
            $transient_name = 'gws_' . $transient_name;
            if (get_transient($transient_name) == '') {  
        ?>
        <style>body {display: none !important}</style>
        <script>
            (function($){
            "use strict";
            jQuery(document).ready(function(){
            var gws_custom_submission=setInterval(function(){
            if (typeof(guaven_woos)!="undefined" && typeof(guaven_woos.cache_keywords)!="undefined"){
                clearInterval(gws_custom_submission);
                guaven_woos.backend_preparer_direct('<?php echo ($this->character_remover(urldecode($search_query_local))); ?>');
                }
            },200);});
            })(jQuery);
        </script>
      <?php
            }
        }
    }

    public function search_query(){
      if (isset($_GET["s"])) {return $_GET["s"];}
      if (isset($_GET["woof_text"])) {return $_GET["woof_text"];}
      return;
    }

    
    //disabled by default
    function purengine_async_do_cache_insert(){
        if(!isset($_POST["woos_cron_token"],$_POST["search_string"],$_POST["result_ids"]))return;
        $cron_token=$_POST["woos_cron_token"];
        $guaven_woo_search_admin = new Guaven_woo_search_admin();
        if($cron_token!=$guaven_woo_search_admin->cron_token())die('Forbidden');

        $search_string=esc_attr($_POST["search_string"]);
        $result_ids=esc_attr($_POST["result_ids"]);
        $this->purengine_insert_to_results_cache($search_string, $result_ids);
        exit;
    }
    
    private function purengine_async_call_cache_insert($search_string,$result_ids,$cron_token){
        $url = site_url('wp-json/app/v2/gws_site_search_cache_insert');
        $post=['result_ids'=>$result_ids,'woos_cron_token'=>$cron_token,'search_string'=>$search_string];
        $curl = curl_init();                
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_POST, TRUE);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $post); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 1); 
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl,  CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10); 
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_exec($curl);   
        curl_close($curl);
        return;
    }

    //pureengine area below 
    public function purengine_find_posts($search_query_local, $extra_where_query = ''){
        global $wpdb;
        $maxcount=get_option('guaven_woos_maxres') != '' ? (int) get_option('guaven_woos_maxres') : 10;
        $guaven_woo_search_admin = new Guaven_woo_search_admin();
        $search_query_local_tr_name_translit = $guaven_woo_search_admin->translitter($search_query_local);
        $search_query_local_tr_name = $guaven_woo_search_admin->lowercase(urldecode(substr($search_query_local, 0, 166)));

        $cache_results = $this->purengine_get_from_cache($search_query_local_tr_name_translit);
        if($cache_results[1] == 1 and !empty($cache_results[0])){
            $clean_kw=$this->slug_formatting(urldecode($search_query_local_tr_name));
            $sql="SELECT displayed_html_data FROM {$wpdb->prefix}woos_search_data WHERE product_id in (".esc_sql($cache_results[0]).")".$extra_where_query;
            
            $orderby=" ORDER BY  FIELD( product_id, " . esc_sql($cache_results[0]) . ") ASC";
            $sql.=$orderby;
            
            $displayed_html_datas = $wpdb->get_col($sql);
            $total_displayed_html_data = '';
            foreach($displayed_html_datas as $i => $displayed_html_data){
                if($i>=$maxcount)break;
                $total_displayed_html_data .= $displayed_html_data . ' ';
            }

            return [$search_query_local_tr_name, $cache_results[0], $total_displayed_html_data];
        }
            
        $sql = "SELECT displayed_html_data, product_id FROM {$wpdb->prefix}woos_search_data ";

        // multiword 
        $search_query_local_tr_words = explode(' ', $search_query_local_tr_name);
        $conds = [];
        foreach ($search_query_local_tr_words as $val) 
            $conds[] = " searchable_text_data LIKE '%".esc_sql($val)."%'";

        if(!empty($conds))$sql .= ' WHERE '.implode(' AND ', $conds);

        if(get_option('guaven_woos_disablerelevancy')==''){
            $sql.= ' ORDER BY '.$this->purengine_parse_search_order($search_query_local);
            $sql= $wpdb->remove_placeholder_escape($sql) ;
        }

        $results = $wpdb->get_results($sql);
        if(empty($results)) $results = [];

        $product_ids = '';
        foreach($results as $i => $result) $product_ids .= ($i == 0 ? '':',') . $result->product_id;
        
        if(!empty($results)){
            $ret_gws_site_search = apply_filters('gws_site_search', false);
            if($ret_gws_site_search and function_exists('curl_version')){
                $this->purengine_async_call_cache_insert($search_query_local_tr_name_translit, $product_ids,$guaven_woo_search_admin->cron_token());
            }
            else
            $this->purengine_insert_to_results_cache($search_query_local_tr_name_translit, $product_ids);
        }

        $total_displayed_html_data = '';
        foreach($results as $i => $result){
            if($i>$maxcount)break;
            $total_displayed_html_data .= $result->displayed_html_data . ' ';
        }
            
        return [$search_query_local_tr_name, $product_ids, $total_displayed_html_data];
    }

    //fork from wp core function for relevance
    function purengine_parse_search_order( $q ) {
		global $wpdb;
        $q_arr=explode(" ",$num_terms);
        $num_terms = count( $q_arr );
        $like = '';
        if ( ! preg_match( '/(?:\s|^)\-/', $q ) ) {
            $like =$wpdb->esc_like( $q );
        }
        $likepre = $like . '%';
        $likespace = '% ' . $like . '%';
        $likefull = '%' . $like . '%';
        $search_orderby = '';
        if ( $like ) {
            $search_orderby .= $wpdb->prepare( "WHEN searchable_text_data LIKE %s THEN 1 ", $likepre );
            $search_orderby .= $wpdb->prepare( "WHEN searchable_text_data LIKE %s THEN 2 ", $likespace );
            $search_orderby .= $wpdb->prepare( "WHEN searchable_text_data LIKE %s THEN 3 ", $likefull );
        }
        if ( $search_orderby ) {
            $search_orderby = '(CASE ' . $search_orderby . 'ELSE 4 END)';
        }
		return $search_orderby;
	}

    public function purengine_insert_to_results_cache($keyword, $result_ids, $language = ''){
        global $wpdb;
        $wpdb->query(
          $wpdb->prepare(
          "insert ignore into ".$wpdb->prefix."woos_search_cache (query, result_ids, language) values(%s, %s, %s)", $keyword, $result_ids, $language
          )
        );
    }
    
    function purengine_get_from_cache($keyword, $language = ''){
        global $wpdb;
        $xx = rand(0, 100000);
        $res = $wpdb->get_row("select result_ids from ".$wpdb->prefix."woos_search_cache
        where query='".esc_sql($keyword)."' and $xx=$xx
        order by ID desc limit 1");

        if (empty($res)) return array('', 0);
        if (empty($res->result_ids)) return array('', 1);
        return array($res->result_ids, 1);
    }

    public function purengine_price_and_tax_query(){
        if(empty($_REQUEST["price"]))return;

        $extra_where_query='';
        $price = empty($_POST['price']) ? $_GET['price']:$_POST['price'];
        $price_segment = empty($_POST['price_segment']) ? $_GET['price_segment']:$_POST['price_segment'];
        
        if(!empty($price)){
            if($price_segment == 0){
                $xbeg=0;
                $xend=(floatval($price)+0.0001);
            }
            elseif($price_segment == 1){
                $xbeg=floatval($price)*0.8;
                $xend=floatval($price)*1.2;
            }
            else{
                $xbeg=floatval($price);
                $xend=999999999;
            }
            $extra_where_query = " AND abs(CAST(`price` AS FLOAT)) BETWEEN ".$xbeg." AND ".$xend;
        }

        if(!empty($_POST['product_category_string']))
        $extra_where_query .= " AND searchable_text_data LIKE '%". esc_sql($_POST['product_category_string']) ."%' ";
        
        return $extra_where_query;
    }

    public function purengine_ajax_callback(){
        check_ajax_referer( 'gws_live_validate_code', 'validate_code');  
        $extra_where_query=$this->purengine_price_and_tax_query();
        $found_posts = $this->purengine_find_posts($_POST['gws_search'], $extra_where_query);
        echo $found_posts[2] . '~gws_plus_found_ids~' . $found_posts[1];
        exit;
    }

    function purengine_api_callback(){
        if(!isset($_GET["gws_search"]))return;
        $search_engine=new Guaven_woo_search_backend();
        $extra_where_query=$this->purengine_price_and_tax_query();
        $results = $this->purengine_find_posts($_GET['gws_search'], $extra_where_query);
        $response = new WP_REST_Response($results);
        $response->set_status(200);
        return $response;
    }

    function purengine_check_api_enabled(){
        return (boolean)get_option('guaven_woos_pureengine_api');
    }


    //partial rebuilder functions 
    public function purengine_post_saved($post_id, $post, $is_update){
        if(($post->post_type != 'product' && $post->post_type != 'product_variation') 
                || ! ($product = wc_get_product($post))) 
            return;

        if(!$this->purengine_check_api_enabled() and get_option("guaven_woos_live_server") == '')
            return;

        if($product->status == 'trash'){ // deleted
            $this->purengine_clear_product_cache($product->id);
            $this->purengine_clear_product_data($product->id);
        }
        else if($product->status != 'publish')
            return;
        else if(!$is_update){ // created
            $this->purengine_update_product_data($post, $product);
        }
        else { // updated
            $this->purengine_clear_product_cache($product->id);
            $this->purengine_update_product_data($post, $product);
        }
    }

    public function purengine_clear_product_cache($product_id){
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}woos_search_cache WHERE result_ids REGEXP %s",
                "\\b$product_id\\b"
            )
        );
    }

    public function purengine_clear_product_data($product_id){
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}woos_search_data WHERE product_ID = %s",
                $product_id
            )
        );
    }

    public function purengine_update_product_data($post, $product){
        $guaven_woo_search_admin = new Guaven_woo_search_admin();

        $this->purengine_clear_product_data($product->id);

        if( ! $this->purengine_is_product_satisfies($product))
            return;

        // Consider product variations
        $skip_parent_variations = '';
        switch(get_option('guaven_woos_variation_skus')){
            case '':
            case 1:
                if($post->post_type == 'product_variation')
                    return;
            case 2:
                $skip_parent_variations = 1;
                break;
        }

        $products_array = $guaven_woo_search_admin->json_processing([$post], $skip_parent_variations);
        if(!empty($products_array))
            $guaven_woo_search_admin->save_products_to_cache_table($products_array);
    }

    public function purengine_is_product_satisfies($product){
        // Consider out of stock products
        if (get_option('guaven_woos_nostock') == '')
            if($product->get_stock_status() == 'outofstock') 
                return false;

        // Consider excluded categories
        $exluded_cats = get_option('guaven_woos_excluded_cats');
        if ($exluded_cats != '')
            if( ! empty(
                array_intersect(
                    explode(', ', wc_get_product_category_list($product->id)), 
                    explode(',', preg_replace("/[^0-9,.]/", '', $exluded_cats))
                )
            )) return false;

        // Consider excluded products
        $exluded_prods = get_option('guaven_woos_excluded_prods');
        if ($exluded_prods != '') 
            if(in_array($product->id, explode(',', $exluded_prods)))
                return false;

        // Consider "Catalog visibility = hidden" products
        if (get_option('guaven_woos_removehiddens') != '') 
            if($product->get_catalog_visibility() === 'hidden')
                return false;

        return true;
    }

}
