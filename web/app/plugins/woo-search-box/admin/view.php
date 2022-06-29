<?php
if (!defined('ABSPATH')) {
    die;
}
?>
<div class="wrap guaven_woos_admin_container">
<div id="icon-options-general" class="icon32"><br></div><h2><?php _e('WooCommerce Search Engine','guaven_woo_search');?>
  <span style="float:right"><a class="button" href="?page=woo-search-box%2Fadmin%2Fclass-search-analytics.php"><?php _e('Analytics','guaven_woo_search');?></a> </span>

  <?php
  if (get_option('guaven_woos_support_expired')=='2') {
    echo '  <span style="float:right"> <a style="background:red;color:white" class="button" href="https://codecanyon.net/item/woocommerce-search-box/15685698">Renew Expired Support</a></span>';
  }
  else {
    echo '<span style="float:right"> <a class="button" href="https://goo.gl/forms/hh9J7y9JtKMOpAjx2" target="_blank">Request a Feature</a></span>
    <span style="float:right"> <a style="background:#008ec2;color:white"
    class="button" href="https://guaven.com/contact/?fr=settingspage&purchase_code='.get_option('guaven_woos_purchasecode').'">Get Support</a></span>';
  }
  ?>
  </h2>
<?php
settings_errors();
?>

<form action="" method="post" name="settings_form">
<?php
wp_nonce_field('guaven_woos_nonce', 'guaven_woos_nonce_f');
?>

<h3><?php _e('Cache re/builder','guaven_woo_search');?></h3>

<p>
<?php _e('This button generates the needed cached data based on your products by using parameters below.','guaven_woo_search');?></p>
<?php
$guaven_woos_rebuild_via = get_option("guaven_woos_rebuild_via");
if (defined('W3TC') and $guaven_woos_rebuild_via == 'db') {
    echo '<p style="color:blue">It seems you are using W3 Total Cache which blocks rebuilding process by default (due to its Object Cache feature).
Please go to "Data Building" tab and choose "Rebuild via Filesystem" option for "Rebuild the cache via" setting.
</p>';
}
?>
<div style="height:30px">
<input type="button" class="rebuilder  gws_rebuilder inputrebuilder button button-primary" 
value="<?php _e('Rebuild the Cache','guaven_woo_search');?> <?php
echo $this->get_current_language_code()!=''?' - '.$this->get_current_language_code():''; ?>" style="float:left"></div>

<div style="font-weight: bold;font-size:14px;background:#00a747;color:white;margin-top:10px;display:none;clear:both;padding: 10px" id="result_field"></div>

<br>
<div class="tab">
<button class="tablinks" id="guaven_woos_tablink_live" onclick="openSettingTab(event, 'guaven_woos_tab_live');return false;"><?php _e('Live Search','guaven_woo_search');?></button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_backend');return false;"><?php _e('Backend Search','guaven_woo_search');?></button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_admin');return false;"><?php _e('Data Building','guaven_woo_search');?></button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_advanced');return false;"><?php _e('Advanced Settings','guaven_woo_search');?></button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_updates');return false;"><?php _e('Getting Updates','guaven_woo_search');?></button>
<button class="tablinks" onclick="openSettingTab(event, 'guaven_woos_tab_faq');return false;"><?php _e('FAQ','guaven_woo_search');?></button>
</div>

<div id="guaven_woos_tab_live" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>

      <tr valign="top">
      <th scope="row" class="titledesc"><?php _e('Smart Search','guaven_woo_search');?></th>
      <td scope="row">

      <p>
      <label>
              <input name="guaven_woos_corr_act" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_corr_act"), 'checked');
?>>
<?php _e('Automatic Correction feature    (usually should be checked)','guaven_woo_search');?> </label>
      <br>
      <small> <?php _e('For example, if a user types <i>ifone</i> instead of <i>iphone</i>, or <i>kidshoe</i> instead of <i>Kids Shoes</i> this feature will understand him/her and will suggest
      corresponding products.','guaven_woo_search');?></small></p>
      <br>
      <p>
      <label>
      <?php 
      $gws_input_field='<input name="guaven_woos_whentypo" type="number" step="1"  id="guaven_woos_whentypo"
      value="'.(get_option("guaven_woos_whentypo") != '' ? ((int) get_option("guaven_woos_whentypo")) : 10).'" class="small-text">';
      
      echo sprintf( __('Show suggestions by autocorrected key if there are %s or fewer suggestions for original input.','guaven_woo_search') ,$gws_input_field);
      ?>
      </label>
    </p><?php  $gws_input_field='<input name="guaven_woos_whentypo" type="number" step="1"  id="guaven_woos_whentypo"
      value="'.(get_option("guaven_woos_whentypo") != '' ? ((int) get_option("guaven_woos_whentypo")) : 10).'" class="small-text">';
      ?>
   
      <p>
      <label>
      <?php 
      $gws_input_field='<input name="guaven_woos_min_symb_sugg" type="number" step="1" min="1" id="guaven_woos_min_symb_sugg"
      value="'.((int) get_option("guaven_woos_min_symb_sugg")).'" class="small-text">';
      echo sprintf( __('Show suggestion after %s characters entered by a visitor.','guaven_woo_search') ,$gws_input_field);
      ?>
      </label>
      </p>
<br>
      <p>
      <label>
      <?php _e('The maximal number of suggestions:','guaven_woo_search');?>
      <input name="guaven_woos_maxres" type="number" step="1" min="1" id="guaven_woos_maxres" value="<?php
echo (int) get_option("guaven_woos_maxres");
?>" class="small-text">

      </label>
      </p>

      </td> </tr>


  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Initial texts','guaven_woo_search');?></th>
  <td scope="row">


  <p>

  <label><?php _e('Initial help message to the visitor when he/she focuses on search area:','guaven_woo_search');?>
  <input name="guaven_woos_showinit_t" type="text" id="guaven_woos_showinit_t"
  value='<?php
echo $this->kses(get_option("guaven_woos_showinit_t"));
?>' class="small-text" style="width:500px"
  placeholder='<?php _e('F.e: Type here any product name you want: f.e. iphone, samsung etc.','guaven_woo_search');?>'>
  </label>
  </p><br>
  <p>
  <label><?php _e('"No match" text','guaven_woo_search');?>
  
  <input name="guaven_woos_showinit_n" type="text" id="guaven_woos_showinit_n"
  value='<?php
echo $this->kses(get_option("guaven_woos_showinit_n"));
?>' class="" style="width:500px"
  placeholder='<?php _e('No any products found...','guaven_woo_search');?>'>
  </label>
  </p>
  <br>
  <p>
  <label>
  <?php _e('"Show all results" link text below live results','guaven_woo_search');?>
  
  <input name="guaven_show_all_text" type="text" id="guaven_show_all_text"
  value='<?php
echo esc_attr(get_option("guaven_show_all_text"));
?>' class="" style="width:500px"
  placeholder='<?php _e('Show all results...','guaven_woo_search');?>'>
  </label>
  <br>
  <small><?php _e('Leave empty if you don\'t want it to appear','guaven_woo_search');?></small>
  </p>


      </td>
  </tr>

    <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Trending products','guaven_woo_search');?></th>
    <td scope="row">
    <p>
    <label>
      <?php 
        $gws_input_field=' <input name="guaven_woos_data_trend_num"  class="small-text" type="number"  
        value="'.intval(get_option("guaven_woos_data_trend_num")).'">';
        echo sprintf( __('Show %s trending products in search suggestion box (will be shown when the cursor is in the search box, but the user has not pressed enter yet.','guaven_woo_search') ,$gws_input_field);
      ?>  
     </label>
  </p><small>
  <?php _e('  Type 0(zero) if you don\'t want to use this block yet. Also, note that trending data isn\'t being collected while you don\'t use this block.','guaven_woo_search');?>  
 </small>
    <br>  <br>
    <p>
    <label>
    <?php _e('Title text for "Trending Products" block:','guaven_woo_search');?> 
    <input name="guaven_woos_trendt" type="text" id="guaven_woos_trendt"
    value="<?php
echo $this->kses(get_option("guaven_woos_trendt"));
?>">
    </label>
    <small><?php _e('(f.e.  Trending products.)','guaven_woo_search');?> </small>
    </p>
    <br>
    <p>
    <label>
    <?php _e('"Trending Products" criterions:','guaven_woo_search');?>
         
    <?php 
        $gws_input_field='  <input name="guaven_woos_trend_days" type="number" id="guaven_woos_trend_days"  
        value="'.intval(get_option("guaven_woos_trend_days")).'" class="small-text">';
        $gws_input_field_2=' <input name="guaven_woos_trend_refresh" type="number" id="guaven_woos_trend_refresh"  
        value="'.intval(get_option("guaven_woos_trend_refresh")).'" class="small-text">';
        
        echo sprintf( __('Trending data should be built on data for the latest %s days and refreshed each %s minutes.','guaven_woo_search') 
        ,$gws_input_field,$gws_input_field_2);
      ?> 
   </label><br>
    <small><?php _e('Recommended default values are "3" and "10" which mean 3 days and 10 minutes.','guaven_woo_search');?> </small>
    </p>
    </td> </tr>


    <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Featured products','guaven_woo_search');?>
    </th>
    <td scope="row">
    <p>
    <label><?php _e('ID numbers of featured products:','guaven_woo_search');?>
    <input name="guaven_woos_pinneds" type="text" id="guaven_woos_pinneds"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinneds"));
?>">
    </label>
    <small><?php _e('(Comma-separated: f.e.  12,23,1,34. Leave empty if you don\'t want to use this yet)','guaven_woo_search');?> </small>
  </p><br>
    <p>
    <label><?php _e('Term ID numbers of featured categories:','guaven_woo_search');?>
    <input name="guaven_woos_pinneds_cat" type="text" id="guaven_woos_pinneds_cat"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinneds_cat"));
?>">
    </label>
    <small><?php _e('(Comma-separated term_IDs. Leave empty if you don\'t want to use this yet)','guaven_woo_search');?> </small>
    </p>

    <br>
    <p>
    <label>
    <?php _e('Title text for this block:','guaven_woo_search');?>

    <input name="guaven_woos_pinnedt" type="text" id="guaven_woos_pinnedt"
    value="<?php
echo $this->kses(get_option("guaven_woos_pinnedt"));
?>">
    </label>
    <small><?php _e('(f.e.  Featured products.)','guaven_woo_search');?> </small>
    </p>
    </td> </tr>

  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Personal "Recently Viewed Products','guaven_woo_search');?>"</th>
  <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_ispers" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_ispers"), 'checked');
?>>
          <?php _e('Enable cookie-based personalized initial suggestions (will be shown when cursor is in the search box, but user has not pressed enter yet)','guaven_woo_search');?></label>
  </p>

  <br>
  <p>
  <label>
  <?php _e('Title text for personalized initial suggestions:','guaven_woo_search');?>

  <input name="guaven_woos_perst" type="text" id="guaven_woos_perst"
  value="<?php
echo $this->kses(get_option("guaven_woos_perst"));
?>">
  </label>
  <small><?php _e('(e.g.  Recently viewed products.)','guaven_woo_search');?> </small>
  </p>


  <p>
  <label>
  <?php _e('Max number of personal suggestions:','guaven_woo_search');?>

  <input name="guaven_woos_persmax" type="number" id="guaven_woos_persmax"
  value="<?php
echo (int) get_option("guaven_woos_persmax");
?>">
  </label>
  <small>(<?php echo  sprintf( __('Default value is %s'),'5');?>) </small>
  </p>

  </td> </tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Show this below when "not found" appears','guaven_woo_search');?></th>
  <td scope="row">

  <p>
  <label>
          <input name="guaven_woos_nomatch_pops" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_nomatch_pops"), 'checked');
?>>
          <?php _e('Show the most popular products below when "no match" (not found) message appears?','guaven_woo_search');?></label>
  </p>


  <br>
  <p>
  <label>
  <?php _e('Meta key name for product popularity:','guaven_woo_search');?>

  <input name="guaven_woos_popsmkey" type="text" id="guaven_woos_popsmkey"
  value="<?php
echo esc_attr(get_option("guaven_woos_popsmkey"));
?>">
  </label>
  <small><?php _e('(f.e. total_sales, view_count, views etc. You should check your products custom fields if you don\'t know its exact name)','guaven_woo_search');?> </small>
  </p>
  <br>
  <p>
  <label>
  <?php _e('Max number of popular products:','guaven_woo_search');?>

  <input name="guaven_woos_popsmax" type="number" id="guaven_woos_popsmax"
  value="<?php
echo (int) get_option("guaven_woos_popsmax");
?>">
  </label>
  <small><?php echo  sprintf( __('Default value is %s'),'5');?></small>
  </p>
  </td></tr>

  <?php
  $guaven_woos_live_ui_layout = get_option("guaven_woos_live_ui_layout"); 
  ?>
  <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Live Search Layout','guaven_woo_search');?></th>
    <td scope="row">
  <p>
    <label for="guaven_woos_live_ui_layout">
      <select name="guaven_woos_live_ui_layout" id="guaven_woos_live_ui_layout">
        <?php 
        $layout_ui_files=$this->ui_layouts();
        foreach($layout_ui_files as $lkey=>$layout_ui_file){
          ?>
          <option value="<?php echo $lkey;?>" <?php echo selected($guaven_woos_live_ui_layout, $lkey);?>><?php echo $layout_ui_file;?></option>
          <?php 
        }
        ?>
      </select>
    </label>
  </p>
  <small><?php _e('Live search layout.','guaven_woo_search');?>
      <a target="_blank" href="https://www.dropbox.com/sh/2awgfiw08f4luce/AABSMNt0kj2_Y7XxMLaR3i1da?dl=0"><?php _e('Examples.','guaven_woo_search');?></a> 
  </small>
</td></tr>

    <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('FullScreen Mobile Search','guaven_woo_search');?></th>
    <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_mobilesearch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_mobilesearch"), 'checked');
?>>
         <?php _e('Enable Full-Screen Mobile Search Popup','guaven_woo_search');?>  </label>
  </p>
  <small><?php _e('If you enable this, then all search fields of your website will be turned into full-screen simple search form (for mobile devices only)','guaven_woo_search');?></small>
</td></tr>

<tr valign="top">
<th scope="row" class="titledesc"><?php _e('Smart expressions','guaven_woo_search');?> </th>
<td scope="row">
<p>
<label> <input name="guaven_woos_simple_expressions" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_simple_expressions"), 'checked');?>>
<?php _e('Enable Smart Expressions with prices','guaven_woo_search');?> </label>
<br>
<small><?php _e('If you enable this, then live search would recognize expressions like "smartphones under 100 usd, smartphones under $100, smartphones around 100$" etc.','guaven_woo_search');?></small>
</p>
<br>
<p>
<label>
<?php _e('Comma separated values for "under,around,above" segmentators.','guaven_woo_search');?>
<input name="guaven_woos_expression_segments" type="text" id="guaven_woos_expression_segments"
value="<?php echo esc_attr(get_option("guaven_woos_expression_segments")); ?>">
</label>
<small><?php echo  sprintf( __('Default value is %s'),'under,around,above');?></small>
</p>
<br>
<p>
<label>
<?php _e('Your currency spelling:','guaven_woo_search');?> <br>
<?php _e('Singular:','guaven_woo_search');?> <input name="guaven_woos_expression_spell_s" type="text" id="guaven_woos_expression_spell_s"
value="<?php echo esc_attr(get_option("guaven_woos_expression_spell_s")); ?>" placeholder="f.e. dollar">
<?php _e('Plural:','guaven_woo_search');?> <input name="guaven_woos_expression_spell_p" type="text" id="guaven_woos_expression_spell_p"
value="<?php echo esc_attr(get_option("guaven_woos_expression_spell_p")); ?>" placeholder="<?php _e('f.e. dollars','guaven_woo_search');?>"><br>
</label>
<small><?php _e('by default our engine understands abreviation and currency symbol (f.e. USD and $, GBP and £ and so on). But you can also set oftently used spellings (f.e. dollar)','guaven_woo_search');?>  </small>
</p>

</td></tr>


<tr valign="top">
<th scope="row" class="titledesc"><?php _e('Show found categories','guaven_woo_search');?></th>
<td scope="row">

<p>
<label>
<input name="guaven_woos_catsearch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_catsearch"), 'checked');
?>>
<?php _e('Show categories/taxonomies block in search results?','guaven_woo_search');?></label>
</p>
<br>
<p>
<label>
<?php _e('Max number of shown taxonomies:','guaven_woo_search');?>

<input name="guaven_woos_catsearchmax" type="number" id="guaven_woos_catsearchmax"
value="<?php
echo (int) get_option("guaven_woos_catsearchmax");
?>">
</label>
<small><?php echo  sprintf( __('Default value is %s'),'5');?> </small>
</p>
<br>
 <p>
<label>
<?php _e('Shown taxonomies:','guaven_woo_search');?>
<input name="guaven_woos_shown_taxonomies" type="text" id="guaven_woos_shown_taxonomies"
value="<?php
echo esc_attr(get_option("guaven_woos_shown_taxonomies"));
?>">
</label>
<small><?php echo  sprintf( __('Default value is %s.'),'product_cat');?> <?php _e('If you want to use several taxonomies, type their names comma-separated.','guaven_woo_search');?> </small>
</p>
</td></tr>


  </tbody> </table>

</div>


<div id="guaven_woos_tab_backend" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>
  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Backend Search','guaven_woo_search');?></th>
  <td scope="row">
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '');
?>>
          <?php _e('Don\'t affect default search results of my theme\'s search page','guaven_woo_search');?> </label>
  </p>
<br>
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '1');
?>>
          <?php _e('Deprecated: Display live smart results in the Theme\'s Search Results Page (replaces default search results of your theme.
          It doesn\'t change UI of the theme\'s search results page)','guaven_woo_search');?>
<br>  <small><?php _e('This option works on <b>cookie</b> based algorithm - only for the websites with <=1000 products','guaven_woo_search');?></small>
        </label>
  </p>

  <br>
    <p>
    <label>
            <input name="guaven_woos_backend" type="radio" value="3" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '3');
?>>
            <b><?php _e('Recommended:','guaven_woo_search');?> </b><?php _e('Display  live smart results in the Theme\'s Search Results Page (replaces default search results of your theme.
            It doesn\'t change UI of the theme\'s search results page)','guaven_woo_search');?>

  <br>  <small><?php _e('This option works on <b>WP Transient</b> based algorithm','guaven_woo_search');?> </small>
    </label>
    </p>

<br>
  <p>
  <label>
          <input name="guaven_woos_backend" type="radio" value="2" class="tog" <?php
echo checked(get_option("guaven_woos_backend"), '2');
?>>
 
Replace my theme's search results page with "SSSS" (Standalone Simple and Smart Search) module by this plugin.
<br> 

<small>
<?php 
  $gws_input_field='<i>'.home_url("/search-results").'</i>';
  echo sprintf( __('To use this option you need to create a new page with %s URL and to put this shortcode to its content:','guaven_woo_search') ,$gws_input_field);
?>  
<i>[woo_search_standalone]</i></small>
  </label>
  </p>

  </td></tr>
</tbody>
</table>


</div>

<div id="guaven_woos_tab_admin" class="tabcontent">

  <table class="form-table" id="box-table-a">
  <tbody>


  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Search by more data','guaven_woo_search');?></th>
  <td scope="row">

  <div>
    <input name="guaven_woos_wootags" type="hidden" id="guaven_woos_wootags"
    value='<?php
$gws_wootags = explode(",", get_option("guaven_woos_wootags"));
echo esc_attr(implode(",", $gws_wootags));
?>'>

  <dl class="dropdown"> <dt><a href="#"><span class="hida"><?php _e('Search by WooCommerce attributes and taxonomies','guaven_woo_search');?></span> <p class="multiSel"></p> </a></dt><dd>
    <div class="mutliSelect">
              <ul>
                  <?php
$this->attribute_checkboxes($gws_wootags);
?>
             </ul>
          </div>
      </dd>
  </dl>
  <br>
  <p>
  <label>
  <?php _e('Search by custom taxonomies and custom attributes (Comma-separated names)','guaven_woo_search');?>



  <input name="guaven_woos_customtags" type="text" id="guaven_woos_customtags"
  value='<?php
echo esc_attr(get_option("guaven_woos_customtags"));
?>' class="small-text" style="width:500px"
  placeholder='<?php _e('F.e: product_tag,product_vendor etc.','guaven_woo_search');?>'>
</label>
</div>
  <small><?php _e('Custom attribute name should start with pa_. If any questions, just write to our support','guaven_woo_search');?></small>
</p>

  <p>
<br>    <label>
<?php _e('Search by custom post fields (Comma-separated names of meta_keys you want to be indexed)','guaven_woo_search');?>
  <input name="guaven_woos_customfields" type="text" id="guaven_woos_customfields"
  value='<?php
echo esc_attr(get_option("guaven_woos_customfields"));
?>' class="small-text" style="width:500px"
  placeholder='<?php _e('F.e: _wc_average_rating,_stock_status etc.','guaven_woo_search');?>'>
  </label>
  </p>
  <small><?php _e('If you enter here some meta key fields, the search suggestion algorithm will include their data to search metadata.
  (e.g. you have a bookstore, you add _book_author field here. And then when a visitor types the name of the author in the search box, his/her
  books will be suggested with a normal title. )','guaven_woo_search');?></small>

  <p>
<br>  <label>
          <input name="guaven_woos_add_shortdescription_too" type="checkbox" value="1" class="tog"
          <?php
echo checked(get_option("guaven_woos_add_shortdescription_too"), 'checked');
?>>
          <?php _e('Search by Product Short Description (not recommended for most cases)','guaven_woo_search');?>   </label>
  </p>
  <small><?php _e('Although short descriptions will be hidden in search suggestions, the plugin will give the results based on short descriptions.<br>
  Check this only if it is very important for your store.','guaven_woo_search');?>
  </small>

  <p>
<br>  <label>
          <input name="guaven_woos_add_description_too" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_add_description_too"), 'checked');
?>>
          <?php _e('Search by Product Full Description (not recommended for most cases)','guaven_woo_search');?>   </label>
  </p>
  <small><?php _e('Although descriptions will be hidden in search suggestions, the plugin will give the results based on descriptions.<br>
  Check this only if it is very important for your store.','guaven_woo_search');?>
  </small>
</td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Cache Size','guaven_woo_search');?></th>
  <td scope="row">




  <p>
  <label>
  <?php _e('Maximum numbers of products in cached data.','guaven_woo_search');?>
  <input name="guaven_woos_maxprod" type="number" step="1000" min="1000" id="guaven_woos_maxprod"
  value="<?php
echo (int) get_option("guaven_woos_maxprod");
?>" class="small-text"> <?php echo  sprintf( __('Default value is %s'),10000);?>
  </label>
  </p>

    </td>
  </tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('How to rebuild','guaven_woo_search');?></th>
  <td scope="row">


  <?php
$guaven_woos_autorebuild = get_option("guaven_woos_autorebuild");
?>
 <p>
  <label>
  <?php _e('Do the Cache Auto-Rebuild after each time when you edit any product / show manual rebuilder button in admin top bar:','guaven_woo_search');?>
  <select name="guaven_woos_autorebuild">
  <option value="b1a0" <?php
echo selected($guaven_woos_autorebuild, 'b1a0');
?>><?php _e('Enable top rebuild button / disable auto-rebuild','guaven_woo_search');?></option>
  <option value="b1a1" <?php
echo selected($guaven_woos_autorebuild, 'b1a1');
?>><?php _e('Enable top rebuild button / enable auto-rebuil','guaven_woo_search');?>d</option>
  <option value="b0a1" <?php
echo selected($guaven_woos_autorebuild, 'b0a1');
?>><?php _e('Disable top rebuild button / enable auto-rebuild','guaven_woo_search');?></option>
  <option value="b0a0" <?php
echo selected($guaven_woos_autorebuild, 'b0a0');
?>><?php _e('Disable top rebuild button / disable auto-rebuild','guaven_woo_search');?></option>
  </select>
  </p>
  <br>

<label><?php _e('Rebuild with cron jobs','guaven_woo_search');?></label>:
  <code>
  php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?>
 </code>
<br>
For WPML websites:
<code>
php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?> LANGUAGE_CODE
</code>
<br>
For Multisite websites:
<code>
  php <?php
echo ABSPATH;
?>index.php  <?php
echo $cron_token;
?> 0 SUBSITE_ID
 </code>
<br>
<small><?php _e('In some servers you might need to use "/usr/local/bin/php ..." prefix instead of "php ...".','guaven_woo_search');?></small>
<br>
<p><br>
<label>
<?php _e('Rebuild the cache via','guaven_woo_search');?>
<select name="guaven_woos_rebuild_via">
<option value="db" <?php
echo selected($guaven_woos_rebuild_via, 'db');
?>><?php _e('Rebuild via Database','guaven_woo_search');?></option>
<option value="fs" <?php
echo selected($guaven_woos_rebuild_via, 'fs');
?>><?php _e('Rebuild via Filesystem','guaven_woo_search');?></option>
</select>
</p>
<small><?php _e('If you choose filesystem, then temporary rebuilding data would be stored in filesystem. Otherwise, it would be stored in database table.
  In some servers there are strict database data size limits which don\'t allow rebuilding process to be finished. That\'s why, recommended option is "FileSystem".
  You should choose "Database" option only if there is writing permission problem in the /plugins directory of your filesystem.','guaven_woo_search');?></small>
<br>

  </td></tr>


    <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Which products will appear','guaven_woo_search');?></th>
    <td scope="row">
    <p>
    <label>
            <input name="guaven_woos_nostock" type="checkbox" value="1" class="tog" <?php
if (get_option("guaven_woos_nostock") != '') {
    echo 'checked="checked"';
}
?>>
<?php _e('Include out of stock products','guaven_woo_search');?></label>
    </p>

    <br>
    <p>
    <label>
            <input name="guaven_woos_removehiddens" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_removehiddens"), 'checked');
?>>
            <?php _e('Hide "Catalog visibility = hidden" products at live search box','guaven_woo_search');?></label>
    </p>

      <br>
    <p>
    <label>
      <input name="guaven_woos_removefilters" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_removefilters"), 'checked');
?>><?php _e('Remove all filters by 3rd party plugins while fetching products for cache rebuilding  (not necessary in most cases)','guaven_woo_search');?></label>
    </p>

    <br>
    <p>
    <label>
    <?php _e('Variations in search process:','guaven_woo_search');?>
      <select name="guaven_woos_variation_skus">
        <option value="" <?php
echo selected(get_option("guaven_woos_variation_skus"), '');
?>><?php _e('Show parent product of variables','guaven_woo_search');?></option>
        <option value="1" <?php
echo selected(get_option("guaven_woos_variation_skus"), 1);
?>>
          <?php _e('Show parent product of variables + search by variation meta data','guaven_woo_search');?></option>
        <option value="2" <?php
echo selected(get_option("guaven_woos_variation_skus"), 2);
?>><?php _e('Show all variations apart from each other, hide main product itself','guaven_woo_search');?></option>

            </select>
           </label>
    </p>

    <br>
    <p>
    <label>
    <?php _e('Order by products by','guaven_woo_search');?>
    <input name="guaven_woos_customorder" type="text"  id="guaven_woos_customorder"
    value="<?php
echo esc_attr(get_option("guaven_woos_customorder"));
?>">
    </label>
    <small><?php echo sprintf( __('Supported formats: default WP orders such as %s etc.','guaven_woo_search'),'<i>date</i>, <i>title</i>, <i>ID</i>, <i>ID ASC</i>,<i>title DESC</i>' );?> 
    <?php echo sprintf( __('And any meta fields %s etc.','guaven_woo_search'),'<i>meta:total_sales</i>, <i>metanum:view_count</i>, <i>metanum:view_count DESC</i>, <i>meta:authorname DESC</i>' );?>
    </small>
    </p>

    <br>
    <p>
    <label>

   <input name="guaven_woos_disablerelevancy" type="checkbox" value="1" class="tog" <?php echo checked(get_option("guaven_woos_disablerelevancy"), 'checked');?>>
   <?php _e('Disable relevancy in ordering','guaven_woo_search');?>
 </label><br>
    <small><?php _e('If you uncheck this setting (default and recommended state is unchecked state), search results would appear ordered by "relevancy (first priority) + the field you set above (second priority)".
      If you check this setting, then search results would appear directly ordered by the field you set in the field above.','guaven_woo_search');?></small>
    </p>

      </td>
    </tr>



  </tbody></table>
</div>

<div id="guaven_woos_tab_advanced" class="tabcontent">


  <table class="form-table" id="box-table-a">
  <tbody>


    <tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Transliterated search','guaven_woo_search');?></th>
    <td scope="row">

    <p>
    <label> <?php _e('Transliterated data should be generated:','guaven_woo_search');?>
    <select name="guaven_woos_translit_data">
      <option value="" <?php
echo selected(get_option("guaven_woos_translit_data"), '');
?>><?php _e('at endusers\' browser sessions','guaven_woo_search');?> </option>
      <option value="1" <?php
echo selected(get_option("guaven_woos_translit_data"), '1');
?>><?php _e('in cache rebuilding process (as pre-saved data) ','guaven_woo_search');?></option>
      <option value="-1" <?php
echo selected(get_option("guaven_woos_translit_data"), '-1');
?>><?php _e('nowhere - disable search by transliterated data - default','guaven_woo_search');?></option>
    </select>
    </p>
    <small><?php _e('1 st option: at endusers\' browser - transliterated data would be generated in each user session - recommended for the websites with < 1000 products;','guaven_woo_search');?>
      <br><?php _e('2nd option: in cache rebuilding - transliterated cache data would be generated during cache rebuild, at once, for all;','guaven_woo_search');?>
      <br><?php _e('3rd option: disable by default - no any transliteration data would be used in search process - recommended for English language websites;','guaven_woo_search');?> </small>
    </td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Customization','guaven_woo_search');?></th>
  <td scope="row">

  <p><?php _e('Custom CSS for plugin elements (don\'t use style tag, just directly put custom CSS code)','guaven_woo_search');?> </p>

   <textarea name="guaven_woos_custom_css" id="guaven_woos_custom_css" class="large-text code" rows="3"><?php
echo esc_attr(stripslashes(get_option("guaven_woos_custom_css")));
?></textarea>

  <br>
  <p><?php _e('Custom JS (don\'t use script tag, just directly put custom JavaScript code)','guaven_woo_search');?> </p>
   <textarea name="guaven_woos_custom_js" id="guaven_woos_custom_js" class="large-text code" rows="3"><?php
echo esc_attr(stripslashes(get_option("guaven_woos_custom_js")));
?></textarea>

<br><br>
<p>
<label>
<?php _e('Search box selector:','guaven_woo_search');?>
<input name="guaven_woos_selector" type="text" id="guaven_woos_selector"
value='<?php
echo esc_attr(stripslashes(get_option("guaven_woos_selector")));
?>' class="small-text" style="width:300px" placeholder=''>
</label>
<br>
<small>
<?php 
  $gws_input_field='<code>[name="s"]</code>';
  echo sprintf( __('Default selector is %s. You can change it if you want to exclude some search forms.','guaven_woo_search') ,$gws_input_field);
?>  
</small>
</p>
<br>
<p>
<label>
<?php _e('Category filter selector of the search box:','guaven_woo_search');?>
<input name="guaven_woos_filter_selector" type="text" id="guaven_woos_filter_selector"
value='<?php
echo esc_attr(stripslashes(get_option("guaven_woos_filter_selector")));
?>' class="small-text" style="width:300px" placeholder=''>
</label>
<br>
<small><?php echo sprintf(__('If your theme\'s search form has its own category drop-down filter, then our live search box will consider its actual value.
To make it work you just need to enter selector name of that drop-down filter. %s','guaven_woo_search'), '(#ID, .CLASS, [name="its_name"])');?> </small>
</p>
<br>

<p>
<label>
<?php _e('Increased Memory Limit for Backend-side processes:','guaven_woo_search');?>
<input name="guaven_woos_memory_limit" type="text" id="guaven_woos_memory_limit"
value='<?php echo (int)get_option("guaven_woos_memory_limit");?>' class="small-text" style="width:300px" placeholder='f.e. 512M'>
</label>
<br>
<small><?php _e('You can can set higher value for this field and let our admin & background processes to work faster. This setting is available just for this plugin
  and it doesn\'t affect the website\'s memory limit.','guaven_woo_search');?><br>
  <?php _e('Recommended value: 512M or 1024M','guaven_woo_search');?> </small>
</p>



  </td> </tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Exclude products','guaven_woo_search');?></th>
  <td scope="row">
  <p>
  <label><?php _e('Comma-separated IDs of product <b>categories</b> which should be excluded from the search','guaven_woo_search');?>
  <input name="guaven_woos_excluded_cats" type="text" id="guaven_woos_excluded_cats"
  value='<?php
echo esc_attr(get_option("guaven_woos_excluded_cats"));
?>' class="small-text" style="width:300px"
  placeholder=''>
  </label>
  </p>

  <p>
  <label>
  <?php _e('Comma-separated <b>product</b> IDs which should be excluded from the search','guaven_woo_search');?>
  <input name="guaven_woos_excluded_prods" type="text" id="guaven_woos_excluded_prods"
  value='<?php
echo esc_attr(get_option("guaven_woos_excluded_prods"));
?>' class="small-text" style="width:300px"
  placeholder=''>
  </label>
  </p>

  </td></tr>







  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Synonym list','guaven_woo_search');?> </th>
  <td scope="row">
  <p><?php _e('Put your product related synonyms there. Our search algorythm will take it into account.','guaven_woo_search');?>  </p>

   <textarea name="guaven_woos_synonyms" id="guaven_woos_synonyms" class="large-text code" rows="2"><?php
echo $this->kses(get_option("guaven_woos_synonyms"));
?></textarea>
  <br /><code><?php _e('Each pair should be in A-B format, Comma-separated. For example: car-auto, lbs-pound, footgear-shoes. If you want to use "-" inside any word, use _ instead. F.e.  casing-t_shirt','guaven_woo_search');?>
  </code>
  </td> </tr>

  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Ignore them','guaven_woo_search');?> </th>
  <td scope="row">
  <p><?php _e('Put your commonly used strings here if you want them to be skipped by our search engine','guaven_woo_search');?> </p>
   <textarea name="guaven_woos_ignorelist" id="guaven_woos_ignorelist" class="large-text code" rows="2"><?php
echo $this->kses(get_option("guaven_woos_ignorelist"));
?></textarea>
  <br /><code><?php echo sprintf(__('Type them in Comma-separated format: f.e. product,and,machine,wearing,from,madeby. Or to ignore characters in search you can use %s','guaven_woo_search'),'_,/+/,!,<,>');?></code>
  </td> </tr>


  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Cache Layout/Structure','guaven_woo_search');?></th>
  <td scope="row">


  <p><?php _e('Search suggestions layout (Don\'t use line-breaks)','guaven_woo_search');?> </p>

   <textarea name="guaven_woos_layout" id="guaven_woos_layout" class="large-text code" rows="3"><?php
echo $this->kses(get_option("guaven_woos_layout"));
?></textarea>

  <code><?php _e('To restore default layout just empty the area and save settings.','guaven_woo_search');?>
  <br> <?php _e('Avaliable tags:','guaven_woo_search');?> {url},{title},{height},{length},{width},{weight},{currency_sale},{saleprice},{currency_regular},{imgurl},{total_sales}, {stock_quantity},
  &#x3C;a {add_to_cart}&#x3E;Add to cart&#x3C;/a&#x3E;, {product_cat}, {pa_someAttributeName}
  </code>

  <p><br><?php _e('More Advanced Layout (for developers only)','guaven_woo_search');?></p>
  <p>
  <?php _e('Each search result is displayed with <i>li</i> container. If you want to edit this container, just create wp_option called "guaven_woos_results_layout" with this default value:','guaven_woo_search');?>
  <br>
  <code>&#x3C;li class=\&#x22;guaven_woos_suggestion_list{guaven_woos_lay_tip}\&#x22;
    tabindex=\&#x22;{guaven_woos_lay_gwsi}\&#x22; id=\&#x22;prli_{guaven_woos_lay_id}\&#x22;&#x3E;  {guaven_woos_lay_parsed} &#x3C;/li&#x3E;</code>
  <br>
  <?php _e('Then, edit this wp_option and REBUILD the cache data. You will get search results with your custom layout.','guaven_woo_search');?>
  </p>


  <br>
  <p>
  <label>
  <?php _e('Image quality in search results:','guaven_woo_search');?> <input name="guaven_woos_thumb_quality" type="text"
  value="<?php
echo get_option("guaven_woos_thumb_quality");
?>"><br>
  <small> <?php _e('(recommended: thumbnail, other values: medium, large or custom size name)','guaven_woo_search');?></small>
  </label>
  </p>


  <br>
  <p>
  <label>
  <input name="guaven_woos_permalink" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_permalink"), 'checked');
?>><?php _e('Disable short links in cache data.','guaven_woo_search');?>
  </label>
  </p>
  <small><?php _e('By default plugin uses ?p=N format in search results to make the cache size smaller. You can easily disable it and let the plugin use the full permalink for products.','guaven_woo_search');?>
  </small>


<br>
<br>
<p>
<label>
<input name="guaven_woos_highlight" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_highlight"), 'checked');
?>><?php _e('Highlight found word\'s first occurence in live search results.','guaven_woo_search');?>
</label>
</p>

  </td></tr>



  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Narrowed Search','guaven_woo_search');?> </th>
  <td scope="row">

    <p>
    <label>
    <input name="guaven_woos_disable_meta_correction" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_disable_meta_correction"), 'checked');
?>>
    <?php _e('Exclude product metadata from autocorrection.','guaven_woo_search');?>
    </label>
    </p>
    <small><?php _e('If you enable this feature, then the autocorrection will work just for the product name and would not work for SKU, custom fields, attributes and so on.','guaven_woo_search');?>
    </small>

<br>
  <p><br>
  <label>
  <input name="guaven_woos_exactmatch" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_exactmatch"), '1');
?>>
  <?php _e('Exact match search (just for special cases - not recommended)','guaven_woo_search');?>
  </label>
  </p>
  <small><?php _e('If you enable this feature, then the algortyhm will search exact match among title,tags,attrbutes etc.. F.e. If the visitor types
    phone, it will only display the products which have indepentent "phone" string in their content.','guaven_woo_search');?>
  </small>

  <p><br>
  <label>
  <input name="guaven_woos_large_data" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_large_data"), '1');
?>>
  <?php _e('Enable "first letter rule" (just for special cases - not recommended)','guaven_woo_search');?>
  </label>
  </p>
  <small><?php _e('If you enable this feature, then it will work so: when user types f.e. Galaxy, it will  search in products which names\' start with "G",
    so it will find only the products which starts with Galaxy,
    the products which start with "Samsung Galaxy"
  will not be displayed.','guaven_woo_search');?>
  </small>



  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Who will rebuild/manage','guaven_woo_search');?></th>
  <td scope="row">


    <p>
    <label>
    <input name="guaven_woos_autorebuild_editor" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_autorebuild_editor"), 'checked');
?>>
    <?php _e('Enable this CACHE REBUILDER for "shop manager" users','guaven_woo_search');?> </label>
    </p>    <small><?php _e('By default, the feature is available only for administrators.','guaven_woo_search');?>
      <br /><?php _e('If you check this, then administrators and shop managers will be able to use rebuild button and auto-rebuild feature.','guaven_woo_search');?> </small>

    </td>
  </tr>


  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Processing Engine','guaven_woo_search');?></th>
  <td scope="row">

      <p>
      <label><input name="guaven_woos_live_server" class="guaven_woos_live_server" type="radio" value="" class="tog"
        <?php  echo checked(get_option("guaven_woos_live_server"), '');  ?>>
         Guaven Live Search Engine <?php _e('(default engine)','guaven_woo_search');?>
      <br> <small><?php _e('Stable and recommended version for all kind of stores','guaven_woo_search');?></small>
      </label>
      </p>

      <p>
      <label><input name="guaven_woos_live_server" class="guaven_woos_live_server" type="radio" value="1" class="tog"
        <?php  echo checked(get_option("guaven_woos_live_server"), '1');  ?>>
        Pure Backend Search Engine
      <br> <small> <b><?php _e('Beta version.','guaven_woo_search');?></b> <?php _e('For stores with >20-30K products.(currently "autocorrection" feature isn\'t supported 
       in this engine. It will be added later. All other features should work OK. )','guaven_woo_search');?> </small> 
      </label>
      </p>

      <p>
    <label>
    <input name="guaven_woos_pureengine_api" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_pureengine_api"), '1');
?>><?php _e('Enable API endpoint for Pure Backend Search Engine','guaven_woo_search');?></label>
    </p>    
    <small><?php _e('If you are using "Pure Backend Search Engine", then you can also enable search API endpoint for using in your 3rd party apps.','guaven_woo_search');?></small>


  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc">Google Analytics</th>
  <td scope="row">
  <p>
  <label>
  <input name="guaven_woos_ga" type="checkbox" value="1" class="tog" <?php
  echo checked(get_option("guaven_woos_ga"), 'checked');
  ?>>
  <?php _e('Enable Google Analytics events in live search','guaven_woo_search');?></label>
  </p>
  <br>
  <p>
  <label>
  <input name="guaven_woos_utm" type="checkbox" value="1" class="tog" <?php
  echo checked(get_option("guaven_woos_utm"), 'checked');
  ?>>
  <?php _e('Enable UTM parameters on live search product URL-s','guaven_woo_search');?></label>
  </p>
  <br>
  </td></tr>

  <tr valign="top">
  <th scope="row" class="titledesc"><?php _e('Miscellaneous','guaven_woo_search');?></th>
  <td scope="row">

    <p>
    <label>
    <?php 
  $gws_input_field=' <input name="guaven_woos_sugbarwidth" type="number" step="1" min="1" id="guaven_woos_sugbarwidth"
  value="'.intval(get_option("guaven_woos_sugbarwidth")).'" class="small-text">';

  echo sprintf( __('Suggestion bar width should be equal to %s percent width of search input field.','guaven_woo_search') ,$gws_input_field);
?>
    </label>
    </p>

<br>
    <p>
    <label>
    <?php _e('Delay duration between a visitor finishes typing and search results appear (in milliseconds).','guaven_woo_search');?>
    <input name="guaven_woos_delay_time" type="number" step="1" min="1" id="guaven_woos_delay_time"
    value="<?php
  echo get_option("guaven_woos_delay_time")!=''?(int) get_option("guaven_woos_delay_time"):500;
  ?>" class="small-text">
    </label>
    <br>
    <small><?php _e('It is recommended to keep this value around 500 - but if you want to show the results more instantly, then instead of 500 set the value to 5, 10 or 15.','guaven_woo_search');?></small>
    </p>

<br>
    <p>
    <label>
    <input name="guaven_woos_cache_version_checker" type="checkbox" value="1" class="tog" <?php
echo checked(get_option("guaven_woos_cache_version_checker"), 'checked');
?>>
    <?php _e('Bypass Static Page Cache when there is new search indexed data.','guaven_woo_search');?> </label>
    </p>
    <small><?php _e('Not needed for most cases. If you are using static page caching (wp-rocket, super cache, total cache etc.) 
      and regularly rebuilding search cache (via cron job f.e.), 
      then by checking this checkbox your website visitors will see newest version of search cache, 
      although your static page cache is older. But if you usually don\'t have outdated page cache in your website, then no need to check this checkbox.','guaven_woo_search');?></small>
    </p>


    </td>
  </tr>

  </tbody> </table>
</div>

<div id="guaven_woos_tab_updates" class="tabcontent">
  <p>
  <label>
  <?php _e('Enter your purchase code and get the plugin\'s updates through Plugins page:','guaven_woo_search');?><br>
  <input name="guaven_woos_purchasecode" type="text"  id="guaven_woos_purchasecode"
  value="<?php echo get_option("guaven_woos_purchasecode") != '' ? (get_option("guaven_woos_purchasecode")) : '';?>">
  </label>
  </p>
</div>

<div id="guaven_woos_tab_faq" class="tabcontent">
<style>#guaven_woos_tab_faq ol li {    display: list-item !important;}</style>
<ol>

  <li>
  Q: What to do first after installation?<br>
  <small><b>Quick start 1:</b>
    <ol><li>Just click to blue button "Rebuild the Cache" and wait the process is done (it can take 3-60 seconds to be finished)
  </li><li>Then check your website's search boxes. That's all.</li></ol> </small>
  <small><b>Quick start 2:</b>
    <ol>
        <li>Go do "Data Building" tab.</li>
        <li>Choose some attributes from the given "Data Building tab / Search by more data" list.</li>
        <li>Go to "Backend Search" tab, and choose 2nd or 3rd option.</li>
        <li>Save the settings</li>
        <li>Click to "Rebuild the cache"</li>
    </ol>

   </small>
  </li>

<li>
Q: How to add attributes to product search data?<br>
<small>A: Go to DATA Building -> Search by more data, and you will see "Search by attributes and tags" field there, enter desired attribute names, save the settings, rebuild the cache. </small>
</li>

<li>
Q: How to add custom fields to product search data?<br>
<small>A: Go to DATA Building -> Search by more data, and you will see "Search by custom post fields" field there, enter desired post field names, save the settings, rebuild the cache. </small>
</li>

<li>
Q: How to show same smart search results at the results page which comes after pressing "Enter"?<br>
<small>A: Go to "Backend Search" tab and choose second "Try to show same..." option. </small>
</li>

<li>
Q: My theme's search bar takes the visitor to WordPress default search page, not to WooCommerce search results page, what to do?<br>
<small>A: You need to add post_type input field to your theme's search forms. To do it simply add this javascript code to "Advanced Settings->Custom JS field: <br>

  <code>jQuery(".searchform").append('&lt;input type="hidden" name="post_type" value="product"&gt;');</code>
<br>You may need to change ".searchform" to actual css class name of your theme's search form.
f.e. for <b>Divi theme</b>, it is <br>
 </small>
 <code>jQuery(".et_pb_searchform").append('&lt;input type="hidden" name="post_type" value="product"&gt;');</code>
 
</li>

<li>Q: How can i hide live search box while scrolling?<br>
  <small>A: You can use this JS code for that</small>

<code>jQuery(window ).scroll(function() {jQuery(".guaven_woos_suggestion").hide();});</code>
</li>

<li>
Q: How to use "Search Analytics" ?<br>
<small>A: Check top-right side of this page, you will see gray "Analytics" button, click to it and activate "Search Analytics". Come there after some time and you will see some reports there. </small>
</li>

<li>
Q: How to use WPML within this plugin?<br>
<small>A: In the settings page you can set wpml supported text to input fields. f.e. <pre>
&#x3C;wpml>&#x3C;en>No product found by your keyword&#x3C;/en>&#x3C;de>Kein Produkt von Ihrem Stichwort gefunden&#x3C;/de>&#x3C;/wpml>
</pre>But if you want to use it at frontend side, in UI layout f.e., you can set such CSS rules:
<pre>html:lang(en-US) .guaven_woos_final_results nl {display:none}html:lang(nl-NL) .guaven_woos_final_results en {display:none}</pre> </small>
</li>

<li>
Q: When we enable backend search option, submission process itself takes 1-2 seconds. How to remove that latency?<br>
<small>A: Just put this JS code to custom JS section (advanced settings tab)
<pre>guaven_woos.setpostform=1;</pre></small>
That's all. Alternatively, you can add the same parameter via PHP.
<pre>&#x3C;?php add_filter('gws_local_values_args',function($args){$args['setpostform']=1; return $args;}); ?> </pre>
</li>

<li>
Q: My search form opens via animated popup, so live search box doesn't appear in right place. What to do to solve that? 
<br><small>A: 
It happens when animation takes some time and after our live search box find it, it continues transition and changes its place.
To solve this issue you just need to use the code below. (note that ".icon-search" here should be replaced with your magnifying glass icon's class name.)
</small>
<pre>jQuery(".icon-search").on('click',function(){setTimeout(function(){guaven_woos.positioner(jQuery(guaven_woos.selector));},300);});
</pre>
</li>

<li>
Q: Is there a list of small snippets than helps us to customize the plugin's functionality? 
<br><small>A: 
We try to collect useful snippets in one single Google Docs. You can find it <a href="https://docs.google.com/document/d/1EXgowcLn9W6kRQmw3RPinWN9_6MITJ07x5wwH2bVtsw/edit
" target="_blank">here</a>.
</small>
</li>


</ol>

</div>


<p>
<input type="submit" class="button button-primary" value="Save settings">
</p>
</form>


<form action="" method="post" name="reset_form">
  <?php
wp_nonce_field('guaven_woos_reset_nonce', 'guaven_woos_reset_nonce_f');
?>


<p>
<br>
<input type="submit" onclick="return confirm('<?php _e('Are you sure to reset all settings to default?','guaven_woo_search');?>')" class="button button-default" value="<?php _e('Reset all settings to default','guaven_woo_search');?>">
</p>
</form>

</div>


<script>
function openSettingTab(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
document.getElementById("guaven_woos_tablink_live").click();
</script>
