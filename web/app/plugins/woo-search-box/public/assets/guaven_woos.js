(function($){
  "use strict";
  var gws_specials_0 = ["'", '"', '-','\\.',','];
  var gws_specials_replacers_0 = ["", "", " "," ",' '];
  var ilkherf = '';
  var prids_object = "";
  var gwsc_keywords_filtered = new Array();
  var gwsc_keywords_filtered_raw = new Array();
  var guaven_woos_init_scrollstate = jQuery('html').css('overflow');
  var gws_current_segment='';
  var gws_current_segment_text='';
  var gws_parceprice=false;
  var gws_parceprice_final='';
  var gws_queued_object="";
  var gws_xhr='';
  var gws_keyhelper_to_push=new Array();
  var guaven_woos_input = '';
  var liSelected=-1;
  var gws_specials = [' x ',"'", '"', 'ä', 'ö', 'ü', 'à', 'â', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ù', 'û', 'ÿ', 'å', 'ó', 'ú', 'ů', 'ý', 'ž',
    'á', 'č', 'ď', 'ě', 'í', 'ň', 'ř', 'š', 'ť', 'ñ', 'ç', 'ğ',
    'ı', 'İ', 'ş', 'ã', 'õ', 'ά', 'έ', 'ή', 'ί', 'ϊ', 'ΐ', 'ό', 'ύ', 'ϋ', 'ΰ', 'ώ', 'ə',
    'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', //russian cyrillic
    'љ', 'њ', 'ѓ', 'ќ', 'џ', 
    'đ','ệ','ơ','ư','ả','ờ','ă','ỏ',"ố","ế",'ắ','ậ','ử','ộ','ẳ','ứ','ự','ớ','ấ','ổ','ẫ','ổ','ầ','ợ','ừ','ữ', 'ỳ','ỹ','ẩ','ẻ','ẽ','ẹ','ì','ị','ĩ','ỉ','ò','ọ','ồ','ỗ','ỡ','ș','ț', 'ă', 'î','â',
  ];
  var gws_specials_replacers = ["x","", "", 'a', 'o', 'u', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'y', 'a', 'o', 'u', 'u', 'y', 'z',
    'a', 'c', 'd', 'e', 'i', 'n', 'r', 's', 't', 'n', 'c', 'g',
    'i', 'i', 's', 'a', 'o', 'α', 'ε', 'η', 'ι', 'ι', 'ι', 'ο', 'υ', 'υ', 'υ', 'ω', 'e',
    'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'c', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
    'lj', 'nj', 'g', 'k', 'dz',
    'd','e','o','u','a','o','a','o','o','e','a','a','u','o','a','u','u','o','a','o','a','o','a','o','u','u', 'y','y','a','e','e','e','i','i','i','i','o','o','o','o','o','s','t','a','i','a',
  ];
  var $gws_specials   = [
      ' x ',"'", '"', 'ä', 'ö', 'ü', 'à', 'â', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ù', 'û', 'ÿ', 'å', 'ó', 'ú', 'ů', 'ý', 'ž', 'á', 'č', 'ď', 'ě',
      'í', 'ň', 'ř', 'š', 'ť', 'ñ', 'ç', 'ğ', 'ı', 'İ', 'ş', 'ã', 'õ', 'ά', 'έ', 'ή', 'ί', 'ϊ', 'ΐ', 'ό', 'ύ', 'ϋ', 'ΰ', 'ώ', 'ə', 'а', 'б',
      'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы',
      'ь', 'э', 'ю', 'я', 'љ', 'њ', 'ѓ', 'ќ', 'џ', 
      'đ','ệ','ơ','ư','ả','ờ','ă','ỏ',"ố","ế",'ắ','ậ','ử','ộ','ẳ','ứ','ự','ớ','ấ','ổ','ẫ','ổ','ầ','ợ','ừ','ữ', 'ỳ','ỹ','ẩ','ẻ','ẽ','ẹ','ì','ị','ĩ','ỉ','ò','ọ','ồ','ỗ','ỡ',
      '-', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ',
      'ς', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω','Ã­','σ','ș','ț', 'ă', 'î','â'
  ];
  var $gws_specials_replacers = [
      "x","", "", 'a', 'o', 'u', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'y', 'a', 'o', 'u', 'u', 'y', 'z', 'a', 'c', 'd', 'e',
      'i', 'n', 'r', 's', 't', 'n', 'c', 'g', 'i', 'i', 's', 'a', 'o', 'α', 'ε', 'η', 'ι', 'ι', 'ι', 'ο', 'υ', 'υ', 'υ', 'ω', 'e', 'a', 'b',
      'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'c', 'sh', 'sht', 'a', 'i',
      'y', 'e', 'yu', 'ya', 'lj', 'nj', 'g', 'k', 'dz', 
      'd','e','o','u','a','o','a','o','o','e','a','a','u','o','a','u','u','o','a','o','a','o','a','o','u','u', 'y','y','a','e','e','e','i','i','i','i','o','o','o','o','o',
      ' ', 'a', 'b', 'g', 'd', 'e', 'z', 'h', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p',
      'r', 's', 't', 'u', 'f', 'ch', 'ps', 'w','i','s','s','t','a','i','a'
  ];

  var guaven_woos_pinned_html, 
  guaven_woos_pinned_keywords, guaven_woos_pinned_cat_html, guaven_woos_populars_html, guaven_woos_populars_keywords, 
  guaven_woos_category_keywords, guaven_woos_category_html, gwsc_keywords_arr, guaven_woos_cache_cat_keywords_arr, 
  runSearch, is_runSearch, runSearch_live, is_runSearch_live, gws_global_ret, guaven_woos_lastval, woos_search_existense_sku, 
  gws_foundids, guaven_woos_display_in,gws_this,gws_this_tempval,gws_this_tempval_nofilter,
  guaven_woos_sugbarwidth,fixedtempwidh,gws_adjust_left,guaven_woos_pinned_final,gws_trend_html,gws_trend_keywords,guaven_woos_finalresult,guaven_woos_trend_final,
  rescount,guaven_woos_tempval,gws_match,guaven_woos_tempval_space,
  guaven_woos_tempval_nospace,guaven_woos_curcatid_str,tempformatted,maxpercent,finalpercent,guaven_woos_cfinalresult,guaven_show_all,
  gws_taxonomylist_html,gws_taxonomylist_key;


  function gws_cache_init_old(){
    jQuery.ajax({
      url: guaven_woos.data_path,
      dataType: "script",
      cache: true
    }).done(function() {
      guaven_woos.runner();
      if (gws_queued_object!='' && gws_queued_object!=undefined){
        jQuery(gws_queued_object).blur();
        jQuery(gws_queued_object).trigger("focus");
      }
    });
  }

  function gws_cache_init(){
    jQuery.ajax({
      url: guaven_woos.data_path,
      dataType: "json",
      cache: true
    }).done(function(res, _textStatus, _request) {
      guaven_woos.built_date = res.guaven_woos_built_date || '';
      guaven_woos.cache_html = res.guaven_woos_cache_html || '';
      guaven_woos.cache_keywords = res.guaven_woos_cache_keywords || '';
      guaven_woos_pinned_html = res.guaven_woos_pinned_html || '';
      guaven_woos_pinned_keywords = res.guaven_woos_pinned_keywords || '';
      guaven_woos_pinned_cat_html = res.guaven_woos_pinned_cat_html || '';
      guaven_woos_populars_html = res.guaven_woos_populars_html || '';
      guaven_woos_populars_keywords = res.guaven_woos_populars_keywords  || '';
      if(res.guaven_woos_category_keywords && res.guaven_woos_category_keywords.indexOf(",") > -1){
        gws_taxonomylist_html=res.guaven_woos_category_html.split(",");
        gws_taxonomylist_key=res.guaven_woos_category_keywords.split(",");
        guaven_woos_category_keywords={};
        guaven_woos_category_html={};
        for (var i = 0; i < gws_taxonomylist_html.length; i++) {
          guaven_woos_category_keywords=jQuery.extend(guaven_woos_category_keywords,res[gws_taxonomylist_key[i]]);
          guaven_woos_category_html=jQuery.extend(guaven_woos_category_html,res[gws_taxonomylist_html[i]]);
        }
      }
      else{
        guaven_woos_category_keywords=res[res.guaven_woos_category_keywords];
        guaven_woos_category_html=res[res.guaven_woos_category_html];
      }
      guaven_woos.runner();
      if (gws_queued_object!='' && gws_queued_object!=undefined){
        jQuery(gws_queued_object).blur();
        jQuery(gws_queued_object).trigger("focus");
      }
    });
  }

  function gws_cache_activator(){
    "use strict";
    guaven_woos.data_path = gws_version_checker();
    if(guaven_woos.v2_2_structure!=undefined && guaven_woos.v2_2_structure>0){
      gws_cache_init();
    }
    else{
      gws_cache_init_old();
    }
  }

  //this function can be redeclared anywhere - can be used as content-filter hook
  window.gws_filter_final_html = function (gws_final_html_parts) {
    return gws_final_html_parts[0]+gws_final_html_parts[1]+gws_final_html_parts[2]+gws_final_html_parts[3]+gws_final_html_parts[4];
  }

  function gws_urldecode(url) {
    return decodeURIComponent(url.replace(/\+/g, ' '));
  }

  function gws_version_checker(){
    if(guaven_woos.cache_version_checker=='')return guaven_woos.data_path;
    if(guaven_woos_getcookie('GWS_VERSION')){
      var gws_data_search_params = new URLSearchParams(guaven_woos.data_path.split('?')[1]);
      if( parseFloat(gws_data_search_params.get('v')) <  parseFloat(guaven_woos_getcookie('GWS_VERSION'))){
        gws_data_search_params.set('v', guaven_woos_getcookie('GWS_VERSION'));
        return guaven_woos.data_path.split('?')[0] + '?' + gws_data_search_params.toString();
      }
      else 
        return guaven_woos.data_path;
    }
    else{
      jQuery.ajax({
        url: guaven_woos.ajaxurl, 
        data:{
          action: 'guaven_get_data_version'
        },
      }).done(function(version){
        document.cookie = "GWS_VERSION="+ version +"; max-age=300; path=/";
      });
      return guaven_woos.data_path;
    }
  }


  window.guaven_woos_getcookie = function(name) {
    var match = document.cookie.match(new RegExp(name + '=([^;]+)'));
    if (match)
      return match[1];
  }

  var gws_VECTOR = [],
  gws_CODES = [];
  function gws_levenstein_helper(max, a, b) {
    if (a === b)
      return 0;
    const tmp = a;
    if (a.length > b.length) {
      a = b;
      b = tmp;
    }
    let la = a.length,
        lb = b.length;
    if (!la)
      return lb > max ? Infinity : lb;
    if (!lb)
      return la > max ? Infinity : la;

    while (la > 0 && (a.charCodeAt(~-la) === b.charCodeAt(~-lb))) {
      la--;
      lb--;
    }
    if (!la)
      return lb > max ? Infinity : lb;
    let start = 0;
    while (start < la && (a.charCodeAt(start) === b.charCodeAt(start)))
      start++;
    la -= start;
    lb -= start;
    if (!la)
      return lb > max ? Infinity : lb;
    const diff = lb - la;
    if (max > lb)
      max = lb;
    else if (diff > max)
      return Infinity;
    const v0 = gws_VECTOR;
    let i = 0;
    while (i < max) {
      gws_CODES[i] = b.charCodeAt(start + i);
      v0[i] = ++i;
    }
    while (i < lb) {
      gws_CODES[i] = b.charCodeAt(start + i);
      v0[i++] = max + 1;
    }
    const offset = max - diff,
          haveMax = max < lb;
    let jStart = 0,
        jEnd = max;
    let current = 0,
        left,
        above,
        charA,
        j;
    for (i = 0; i < la; i++) {
      left = i;
      current = i + 1;
      charA = a.charCodeAt(start + i);
      jStart += (i > offset) ? 1 : 0;
      jEnd += (jEnd < lb) ? 1 : 0;
      for (j = jStart; j < jEnd; j++) {
        above = current;
        current = left;
        left = v0[j];
        if (charA !== gws_CODES[j]) {
          if (left < current)
            current = left;
          if (above < current)
            current = above;
          current++;
        }
        v0[j] = current;
      }
      if (haveMax && v0[i + diff] > max)
        return Infinity;
    }
    return current <= max ? current : Infinity;
  }


  function guaven_woos_levenshtein (a, b)
  {
    let gws_sens=(window.guaven_woos_typo_sens==undefined )?5:window.guaven_woos_typo_sens;
    if(gws_sens<=0)gws_sens=5;
    gws_sens=Math.ceil( Math.min(a.length,20)/gws_sens );

    return gws_levenstein_helper(gws_sens,a,b);
  }

  function guaven_woos_levenshtein_deprecated(r, n) {
    if (r == n) return 0;
    var e = r.length,
      t = n.length;
    if (0 === e) return t;
    if (0 === t) return e;
    var a = !1;
    try {
      a = !"0" [0]
    } catch (v) {
      a = !0
    }
    a && (r = r.split(""), n = n.split(""));
    var f = new Array(e + 1),
      i = new Array(e + 1),
      o = 0,
      u = 0,
      l = 0;
    for (o = 0; e + 1 > o; o++) f[o] = o;
    var h = "",
      s = "";
    for (u = 1; t >= u; u++) {
      for (i[0] = u, s = n[u - 1], o = 0; e > o; o++) {
        h = r[o], l = h == s ? 0 : 1;
        var c = f[o + 1] + 1,
          g = i[o] + 1,
          w = f[o] + l;
        c > g && (c = g), c > w && (c = w), i[o + 1] = c
      }
      var y = f;
      f = i, i = y
    }
    return f[e]
  }

  String.prototype.gws_replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'ig'), replacement);
  };

  function guaven_woos_replace_array(replaceString, find, replace, quotes) {
    for (var i = 0; i < find.length; i++) {
      if (quotes == 1)
        replaceString = replaceString.gws_replaceAll(find[i], replace[i]);
    }
    return replaceString;
  }

  function guaven_woos_concatsearch(arrdata, str) {
    var hasil = 0;
    var respoint = 0;
    var arrdata_arr = arrdata.split(" ");
    for (var i = 0; i < arrdata_arr.length; i++) {
      respoint = respoint + str.indexOf(arrdata_arr[i]);
      if (str.indexOf(arrdata_arr[i]) == -1) hasil = -1;
    }
    if (hasil == -1) respoint = -1;
    return respoint;
  }

  function guaven_woos_stripQuotes(s) {
    var t = s.length;
    if (s.charAt(0) == '"') s = s.substring(1, t--);
    if (s.charAt(--t) == '"') s = s.substring(0, t);
    return s;
  }


  guaven_woos.mobclose = function () {
    jQuery(guaven_woos.selector).trigger('focusout');
    jQuery('html').css('overflow', guaven_woos_init_scrollstate);
    setTimeout(function() {
      jQuery(".guaven_woos_mobilesearch").hide();
      jQuery("body").removeClass("guaven_woos_mobile_div_state");
      jQuery("#wpadminbar").show();
    }, 250);
  }

  function guaven_woos_format(str, ttl) {

    var fetch_pid_1 = str.split("prli_");
    var fetch_pid_2;
    if (fetch_pid_1.length>1){
      for (var i=1;i<fetch_pid_1.length;i++){
        fetch_pid_2 = fetch_pid_1[i].split('"');
        if (fetch_pid_2!==null){
          str=str.replace("{gwsvid}",fetch_pid_2[0]);
        }
      }
    }

    str = str.gws_replaceAll('{{t}}', ttl);
    str = str.gws_replaceAll('{{s}}', '</span> <span class=\"guaven_woos_hidden guaven_woos_hidden_tags\">');
    str = str.gws_replaceAll('{{h}}', '<span class=\"guaven_woos_hidden\">');
    str = str.gws_replaceAll('{{l}}', '<li class=\"guaven_woos_suggestion_list\" tabindex=');
    str = str.gws_replaceAll('{{d}}', '\"><div class=\"guaven_woos_div\"><img class=\"guaven_woos_img\" src=\"');
    str = str.gws_replaceAll('{{i}}', '\"></div><div class=\"guaven_woos_titlediv\">');
    str = str.gws_replaceAll('{{e}}', '</div></a> </li>');
    str = str.gws_replaceAll('{{p}}', '</span>');
    str = str.gws_replaceAll('{{m}}', '<small>');
    str = str.gws_replaceAll('{{a}}', '</small>');
    str = str.gws_replaceAll('{{g}}', '</span> <span class=\"gwshd\">');
    str = str.gws_replaceAll('{{v}}', '</span> <span class=\"woos_sku woos_sku_variations\">');
    str = str.gws_replaceAll('{{k}}', '<span class=\"woos_sku\">');
    str = str.gws_replaceAll('{{n}}', '<span class=\"gwstrn\">');
    str = str.gws_replaceAll('{{j}}', '<span class=\"gwshd\">');
    str = str.gws_replaceAll('{{w}}', '</span><span class=\"gwstrn\">');
    str = str.gws_replaceAll('{{o}}', '<span class=\"guaven_woos_hidden_description');
    str = str.gws_replaceAll('{{c}}', '<span class=\"woocommerce-Price-amount amount\"><span class=\"woocommerce-Price-currencySymbol\">');
    str = str.gws_replaceAll('{{u}}', guaven_woos.updir);
    str = str.gws_replaceAll('"gwp=', '"' + guaven_woos.homeurl + '?post_type=product&p=');
    return str;
  }

  function guaven_woos_add_utm_parameters(url, needle){
    const utm = {
      source: 'guaven_woos',
      medium: 'live_search',
      campaign: needle
    }
    var query = Object.keys(utm)
      .map(function(k){return encodeURIComponent(k) + '=' + encodeURIComponent(utm[k]);} )
      .join('&');

    url += (url.includes('?') ? '&':'?') + query;

    return url;
  }

  guaven_woos.prepush_highlight=function(str){
    if (guaven_woos.highlight == 1) {
      var gwsf_position = str.toLowerCase().indexOf(guaven_woos.tempval_raw);
      if (gwsf_position > -1) {
        str = str.slice(0, gwsf_position) + "<em>" +
        str.slice(gwsf_position, gwsf_position + guaven_woos_tempval.length) +
          "</em>" + str.slice(gwsf_position + guaven_woos_tempval.length, str.length);
      }
    }
    return str;
  }

   guaven_woos.prepare_push=function(guaven_woos_temphtml, guaven_woos_temptitle, woos_search_existense, guaven_woos_tempval, stortype,woos_key,_prefix_num) {
    rescount++;
    gws_foundids.push(woos_key);
    guaven_woos_temptitle=guaven_woos.prepush_highlight(guaven_woos_temptitle);
    
    tempformatted = guaven_woos_temphtml+'~g~v~n~'+guaven_woos_temptitle;

    if (stortype == '') {
      if (guaven_woos.dttrr == 1 && typeof(Storage) !== "undefined" && localStorage.keywordsuccess.indexOf(guaven_woos_tempval) == -1) {
        localStorage.keywordsuccess = localStorage.keywordsuccess + guaven_woos_tempval + ', ';
        if(guaven_woos.ga_enabled == 1 && typeof(ga)!="undefined" && ga.getAll){
          ga.getAll()[0].send('event', 'Live Search', 'success', guaven_woos_tempval);
        }
      }
    } else if (guaven_woos.dttrr == 1 && typeof(Storage) !== "undefined" && localStorage.keywordcorrected.indexOf(guaven_woos_tempval) == -1) {
      localStorage.keywordcorrected = localStorage.keywordcorrected + guaven_woos_tempval + ', ';
      if(guaven_woos.ga_enabled == 1 && typeof(ga)!="undefined" && ga.getAll){
        ga.getAll()[0].send('event', 'Live Search', 'corrected', guaven_woos_tempval);
      }
    }
    
    let prepare_push_indexhook_var=guaven_woos.prepare_push_indexhook(woos_search_existense,woos_key,guaven_woos_temphtml,_prefix_num,stortype);
    return prepare_push_indexhook_var[2]+'_'+guaven_woos.keyformat(prepare_push_indexhook_var[0])+guaven_woos.keyformat(prepare_push_indexhook_var[1]) + '~g~v~n~' + tempformatted;
  }

  guaven_woos.prepare_push_indexhook = function (_woos_search_existense,_woos_key,_guaven_woos_temphtml,_prefix_num,stortype){
    return [_woos_search_existense,_woos_key,_prefix_num];
  }

   guaven_woos.keyformat=function(numm) {
    let numstr = numm;
    if (numm < 10) numstr = '000' + numm;
    else if (numm < 100) numstr = '00' + numm;
    else if (numm < 1000) numstr = '0' + numm;
    return numstr;
  }


  function guaven_woos_result_catadd() {
    var crescount = 0;
    var guaven_woos_cfinalresult = '';
    var kehelpercat_relevant = new Array();
    var kehelpercat = new Array();

    ilkherf = guaven_woos_tempval.toLowerCase().substring(0, 1);
    var guaven_woos_findin_data_cat;
    if (guaven_woos.large_data == 1) {
      guaven_woos_findin_data_cat = guaven_woos_cache_cat_keywords_arr[ilkherf];
    } else {
      guaven_woos_findin_data_cat = guaven_woos_category_keywords;
    }

    for (var guaven_woos_ckey in guaven_woos_findin_data_cat) {

      var guaven_woos_ctemptitle = guaven_woos_category_keywords[guaven_woos_ckey];
      if(guaven_woos.stripcharacters!=undefined) guaven_woos_ctemptitle = guaven_woos_replace_array(guaven_woos_ctemptitle, gws_specials_0, gws_specials_replacers_0, 1);
      if (guaven_woos.translit_data == '') guaven_woos_ctemptitle = guaven_woos_replace_array(guaven_woos_ctemptitle, gws_specials, gws_specials_replacers, 1);
      var guaven_woos_ctemphtml = guaven_woos_category_html[guaven_woos_ckey];

      guaven_woos_ctemptitle = guaven_woos_ctemptitle.toLowerCase();
      if (guaven_woos.orderrelevancy==1){
        var woos_searchcat_existense_relevant_pre = guaven_woos_ctemptitle.indexOf(" "+guaven_woos_tempval+" ");
        var woos_searchcat_existense_relevant = guaven_woos_ctemptitle.indexOf(guaven_woos_tempval + " ");
      } 
      else {
        var woos_searchcat_existense_relevant=-1;
        var woos_searchcat_existense_relevant_pre=-1;
      }

      if (woos_searchcat_existense_relevant ===0) { 
        kehelpercat_relevant.push(guaven_woos.keyformat(woos_searchcat_existense_relevant)+guaven_woos.keyformat(guaven_woos_ckey) + '~g~v~n~' +  guaven_woos_ctemphtml);
      } 
      
      else if (woos_searchcat_existense_relevant_pre >-1) {
        kehelpercat_relevant.push(guaven_woos.keyformat(woos_searchcat_existense_relevant_pre)+guaven_woos.keyformat(guaven_woos_ckey) + '~g~v~n~' +  guaven_woos_ctemphtml);
      }
      else if (woos_searchcat_existense_relevant >-1) {
        kehelpercat_relevant.push(guaven_woos.keyformat(woos_searchcat_existense_relevant)+guaven_woos.keyformat(guaven_woos_ckey) + '~g~v~n~' +  guaven_woos_ctemphtml);
      }
      else {
        var woos_searchcat_existense = guaven_woos_ctemptitle.indexOf(guaven_woos_tempval);
        if (woos_searchcat_existense > -1) {
          kehelpercat.push(guaven_woos.keyformat(woos_searchcat_existense)+guaven_woos.keyformat(guaven_woos_ckey) + '~g~v~n~' +  guaven_woos_ctemphtml);
        } else if (guaven_woos_tempval.indexOf(" ") > -1) {
          var concatsearch = guaven_woos_concatsearch(guaven_woos_tempval, guaven_woos_ctemptitle);
          if (concatsearch > -1) {
            kehelpercat.push(guaven_woos.keyformat(concatsearch + guaven_woos.cmaxcount)+guaven_woos.keyformat(guaven_woos_ckey) + '~g~v~n~ ' +  guaven_woos_ctemphtml);
          }
        }
      }
    }
    if (guaven_woos.orderrelevancy==1){
      kehelpercat_relevant.sort();
      kehelpercat.sort();
      kehelpercat = kehelpercat_relevant.concat(kehelpercat);
    }

    for (var i = 0; i < guaven_woos.cmaxcount && i < kehelpercat.length; i++) {
      var guaven_woos_ctemphtml = kehelpercat[i].split("~g~v~n~")[1].trim();
      if(guaven_woos_ctemphtml)
          guaven_woos_cfinalresult = guaven_woos_cfinalresult + guaven_woos_format(guaven_woos_ctemphtml, '');
    }
    return guaven_woos_cfinalresult;
  }

  guaven_woos.send_tr_data = function () {
    guaven_woos.data.failed = localStorage.keywordfailed;
    guaven_woos.data.success = localStorage.keywordsuccess;
    guaven_woos.data.corrected = localStorage.keywordcorrected;
    let temporary_sum = guaven_woos.data.failed + guaven_woos.data.success + guaven_woos.data.corrected;
    guaven_woos.data.unid = localStorage.unid;
    if (temporary_sum.length > 0) {
      jQuery.post(guaven_woos.ajaxurl, guaven_woos.data, function(response) {
        localStorage.keywordfailed = '';
        localStorage.keywordsuccess = '';
        localStorage.keywordcorrected = '';
      });
    }
  }

  guaven_woos.send_trend = function(pid, unid) {
    jQuery.post(guaven_woos.ajaxurl, {
      'action': 'guaven_woos_trend',
      'pid': pid,
      'unid': unid
    }, function(response) {});
  }

  function guaven_woos_uniqid() {
    var ts = String(new Date().getTime()),
      i = 0,
      out = '';
    for (i = 0; i < ts.length; i += 2) {
      out += Number(ts.substr(i, 2)).toString(36);
    }
    return ('d' + out);
  }


  function guaven_woos_left_setter(guaven_woos_offset_left,fixedtempwidh) {
    
    if(guaven_woos.live_ui_layout.substr(0,5)!='grid_')return guaven_woos_offset_left;
    
    if(guaven_woos_offset_left>window.outerWidth/2){
      gws_adjust_left=guaven_woos_offset_left-jQuery(".guaven_woos_suggestion").outerWidth()+fixedtempwidh;
    }
    else {
      gws_adjust_left=guaven_woos_offset_left;
    }
    return gws_adjust_left;
  }

  guaven_woos.positioner = function(guaven_woos_input) {
    var guaven_woos_offset = guaven_woos_input.offset();
    guaven_woos_input.attr('autocomplete', 'off');
    
    guaven_woos_sugbarwidth=guaven_woos.sugbarwidth;
    if(guaven_woos_input.outerWidth()+100>window.outerWidth)guaven_woos_sugbarwidth=1;

    jQuery(".guaven_woos_suggestion").css('top', guaven_woos_offset.top + parseFloat(guaven_woos_input.outerHeight()));
    jQuery(".guaven_woos_suggestion").outerWidth(parseFloat(guaven_woos_input.outerWidth()) *guaven_woos_sugbarwidth);
    fixedtempwidh = guaven_woos_input.outerWidth();
    gws_adjust_left=guaven_woos_left_setter(guaven_woos_offset.left,fixedtempwidh);
    jQuery(".guaven_woos_suggestion").css('left', gws_adjust_left);
    setTimeout(function() {
      if (guaven_woos_input.outerWidth() == fixedtempwidh) {
        jQuery(".guaven_woos_suggestion").css('display', 'block');
      } // if no animation
    }, 100);
    setTimeout(function() {
      jQuery(".guaven_woos_suggestion").css('top', guaven_woos_offset.top + parseFloat(guaven_woos_input.outerHeight()));
      jQuery(".guaven_woos_suggestion").outerWidth(parseFloat(guaven_woos_input.outerWidth()) * guaven_woos_sugbarwidth);
      gws_adjust_left=guaven_woos_left_setter(guaven_woos_input.offset().left);
      jQuery(".guaven_woos_suggestion").css('left',gws_adjust_left,fixedtempwidh);
      jQuery(".guaven_woos_suggestion").css('display', 'block');
      //for animated search forms
    }, 1000);
  }

  guaven_woos.backend_preparer_direct = function(searchterm) {
    jQuery("body").append('<form method="get" id="gws_hidden_form">' +
      '<input name="post_type" value="product" type="hidden">' +
      '<input name="s" id="s" value="' + searchterm + '"></form>');
    var searchterm_formatted_nofilter=gws_tempval(searchterm,'nofilter');
    jQuery("#gws_hidden_form #s").trigger("focus");
    guaven_woos_display_in = ".guaven_woos_suggestion";
    jQuery("#gws_hidden_form #s").trigger("keyup");
    var gws_resubmit=setInterval(function() {
      if (prids_object===null || prids_object==undefined || prids_object.length==0){return;}
      else {clearInterval(gws_resubmit);}
      var guaven_woos_data_2 = {
        'action': 'guaven_woos_pass_to_backend',
        "ids": prids_object,
        "kw": searchterm_formatted_nofilter
      };

      jQuery.ajax({
        method:'post',
        url: guaven_woos.ajaxurl,
        data: guaven_woos_data_2,
      }).done(function(response) {
          if (response == 'ok'){gws_global_ret=1;jQuery("#gws_hidden_form").submit();}
    });
    }, 300);
  }

  guaven_woos.backend_preparer = function (gws_this,gws_this_tempval,gws_this_tempval_nofilter){
    gws_this.children("input:submit").attr("disabled", "disabled");
    if (prids_object=='') return 'direct_submit';

    if(guaven_woos.setpostform==2 || (guaven_woos.setpostform!=undefined && guaven_woos.live_server!=1) ){
      guaven_woos.turn_form_to_post(gws_this);
      gws_this.find("#guaven_woos_ids").val(prids_object);
      gws_global_ret=1;
      jQuery(gws_this).submit();
      return;
    }

    var guaven_woos_data_2 = {
      'action': 'guaven_woos_pass_to_backend',
      "ids": prids_object,
      "kw": gws_this_tempval_nofilter
    };

    jQuery.ajax({
      method:'post',
      url: guaven_woos.ajaxurl,
      data: guaven_woos_data_2,
      }).done(function(response) {
        gws_this.children("input:submit").removeAttr("disabled");
        if (response == 'ok'){gws_global_ret=1;jQuery(gws_this).submit();}
      });
  }

  window.gws_tempval_filter=function(str){return str;}
  function gws_tempval(str, nofilter){
    if(!nofilter || nofilter=='') str=gws_tempval_filter(str);
    guaven_woos_tempval = str.trim();
    if(guaven_woos.stripcharacters!=undefined) guaven_woos_tempval = guaven_woos_replace_array(guaven_woos_tempval, gws_specials_0, gws_specials_replacers_0, 1);
    if (guaven_woos.translit_data == "") guaven_woos_tempval = guaven_woos_replace_array(guaven_woos_tempval.toLowerCase(), gws_specials, gws_specials_replacers, 1);
    else if (guaven_woos.translit_data == 1) guaven_woos_tempval = guaven_woos_replace_array(guaven_woos_tempval.toLowerCase(), $gws_specials, $gws_specials_replacers, 1);
    guaven_woos_tempval=gws_ignore_filter(guaven_woos_tempval);
    return guaven_woos_tempval;
  }

  function gws_ignore_filter(str){
    if (typeof(guaven_woos.ignorelist)=="undefined") return str;
    if (guaven_woos.ignorelist[0] != '' && guaven_woos.ignorelist[0] != ' ') {
      for (var i = 0; i < guaven_woos.ignorelist.length; i++) {
        if (guaven_woos.ignorelist[i].length > 0 && str.length >= (guaven_woos.ignorelist[i].length + 2))
          str = str.gws_replaceAll(guaven_woos.ignorelist[i], "");
      }
    }
    return str;
  }

  function gws_currency_solver(){
    var gws_current_currency = typeof woocs_current_currency !== 'undefined' ? 1 : 0;
    if (guaven_woos.woo_multicurrency!=undefined){
        gws_current_currency=2; woocs_current_currency=guaven_woos.woo_multicurrency;
      }
    if (gws_current_currency==0) return;
    jQuery(".guaven_woos_suggestion .woocommerce-Price-amount").each(function(){
      var price_old_cur,price_new_cur;
      price_old_cur=jQuery(this).html();
      price_old_cur=price_old_cur.replace(",","");
      if (price_old_cur.indexOf(woocs_current_currency.symbol)==-1){
        price_old_cur=price_old_cur.match(/[+-]?([0-9]*[.])?[0-9]+/)[0];
        price_new_cur=0.01*Math.round(100*price_old_cur*woocs_current_currency.rate);
        if (gws_current_currency==2){
          gws_woo_mc_calculator();
          price_new_cur=Math.ceil(price_old_cur * woocs_current_currency.rate * (1 + woocs_current_currency.conv / 100)/woocs_current_currency.round)*woocs_current_currency.round-woocs_current_currency.charm;
        }
        if (woocs_current_currency.position=='right'){
          jQuery(this).html(price_new_cur.toFixed(2)+woocs_current_currency.symbol);
        }
        else {
          jQuery(this).html(woocs_current_currency.symbol+price_new_cur.toFixed(2));
        }
      }
    });
  }

  function gws_woo_mc_calculator(){
    if (guaven_woos.woo_multicurrency.charm=='')guaven_woos.woo_multicurrency.charm=0.01;
    if (guaven_woos.woo_multicurrency.conv=='')guaven_woos.woo_multicurrency.conv=0;
    if (guaven_woos.woo_multicurrency.round=='')guaven_woos.woo_multicurrency.round=1;
    if (guaven_woos.woo_multicurrency.rate=='')guaven_woos.woo_multicurrency.rate=1;
  }

  function gws_simple_expression_sanitizer(guaven_woos_tempval_par,gws_parceprice){
      guaven_woos_tempval_par=guaven_woos_tempval_par.replace(gws_current_segment_text,"");
      guaven_woos_tempval_par=guaven_woos_tempval_par.replace(gws_parceprice[0],"");
      guaven_woos_tempval_par=guaven_woos_tempval_par.replace(gws_parceprice[1],"");
      guaven_woos_tempval_par=guaven_woos_tempval_par.replace(guaven_woos.currency_abv.toLowerCase(),"");
      if (guaven_woos.currency_singular!='' && guaven_woos.currency_plural!=''){
        guaven_woos_tempval_par=guaven_woos_tempval_par.replace(guaven_woos.currency_singular.toLowerCase(),"");
        guaven_woos_tempval_par=guaven_woos_tempval_par.replace(guaven_woos.currency_plural.toLowerCase(),"");
      }
      return guaven_woos_tempval_par;
  }

  function gws_simple_expression_response(gws_parceprice,gws_parceprice_final,guaven_woos_temphtml,current_segment,current_segment_text){
      var cont='';
      var pureprice,pureprice_final;

      pureprice=guaven_woos_temphtml.replace(" ","").match('{{p}}(.*){{p}}');
      if (pureprice==null) pureprice=["{{p}}0{{p}}","{{p}}0{{p}}"];
      if (pureprice!='null'){
        pureprice=pureprice[0].split("{{p}}");
        pureprice[pureprice.length-2]=pureprice[pureprice.length-2].replace(",","");
        pureprice_final=parseFloat(pureprice[pureprice.length-2].replace( /^\D+/g, ''));
        if (isNaN(pureprice_final) || pureprice_final==undefined) {
          pureprice[pureprice.length-3]=pureprice[pureprice.length-3].replace(",","");
          pureprice_final=parseFloat(pureprice[pureprice.length-3].replace( /^\D+/g, ''));
        }
        guaven_woos_tempval=gws_simple_expression_sanitizer(guaven_woos_tempval,gws_parceprice);
        if (current_segment==1 && Math.abs(pureprice_final-gws_parceprice_final)>(pureprice_final*0.2)) cont='continue';
        else if (current_segment==2 && pureprice_final<gws_parceprice_final) {cont='continue';}
        else if (current_segment==0 && pureprice_final>gws_parceprice_final) {cont='continue';}
      }
        return cont;
  }

  function gws_simple_expression_scanner(guaven_woos_tempval_raw){
    
    let guaven_woos_tempval_raw_in=guaven_woos_tempval_raw.replace(guaven_woos.currency_abv.toLowerCase(),guaven_woos.currency_symb);
    guaven_woos_tempval_raw_in=guaven_woos_tempval_raw_in.replace(guaven_woos.currency_abv.toLowerCase()+" ",guaven_woos.currency_symb);
    if (guaven_woos.currency_singular!='' && guaven_woos.currency_plural!=''){
      guaven_woos_tempval_raw_in=guaven_woos_tempval_raw_in.replace(guaven_woos.currency_singular.toLowerCase(),guaven_woos.currency_symb);
      guaven_woos_tempval_raw_in=guaven_woos_tempval_raw_in.replace(guaven_woos.currency_plural.toLowerCase(),guaven_woos.currency_symb);
    }
    gws_match=new Array();
    gws_match[0]=new RegExp("["+guaven_woos.currency_symb+"]([0-9]+[\.]*[0-9]*)");
    gws_match[1]=new RegExp("([0-9]+[\.]*[0-9]*)["+guaven_woos.currency_symb+"]");
    gws_match[2]=new RegExp("([0-9]+[\.]*[0-9]*)[ ]["+guaven_woos.currency_symb+"]");

    for (var k=0;k<gws_match.length;k++){
      gws_parceprice=guaven_woos_tempval_raw_in.match(gws_match[k]);
      if (gws_parceprice && guaven_woos_tempval_raw_in.toLowerCase().indexOf(guaven_woos.currency_symb.toLowerCase())>-1){
        gws_parceprice_final=gws_parceprice[0].replace(/\D/g,'');
        break;
      }
    }
    gws_current_segment=1;gws_current_segment_text='';
    for(var j=0;j<guaven_woos.expression_segments.length;j++){
      if (guaven_woos_tempval_raw_in.indexOf(guaven_woos.expression_segments[j])>-1){

        gws_current_segment=j;gws_current_segment_text=guaven_woos.expression_segments[j];
      }
    }
    return gws_parceprice;
  }

   guaven_woos.turn_form_to_post = function(gws_current_form){
      var selector=gws_current_form.find('[name="s"]');
      gws_current_form.attr("method","post");
      var action=gws_current_form.attr("action");
      var extension='post_type=product&s='+encodeURIComponent(selector.val());
      var prefix=action.indexOf("?")>-1?'&':'?';
      if(action.indexOf(extension)==-1){
        gws_current_form.attr("action",action+prefix+extension);
        gws_current_form.append('<input type="hidden" name="guaven_woos_ids" id="guaven_woos_ids" value="">');
      }
   }


  function guaven_woos_finish_rendering(){
    guaven_woos_cfinalresult = '';
    if (guaven_woos.categories_enabled == 1) {
      guaven_woos_cfinalresult = guaven_woos_result_catadd();
      if (guaven_woos_cfinalresult != '')
        guaven_woos_cfinalresult = "<ul class='guaven_woos_suggestion_catul'>" + guaven_woos_cfinalresult + "</ul>";
    }

    if (guaven_woos.backend == 3 && prids_object=='') {
      prids_object='0,0';
    }

    guaven_show_all = '';
    if (guaven_woos.show_all_text != '' && rescount>1) {
      guaven_show_all = '<li class="guaven_woos_showallli"><a onclick="guaven_woos.current_input_object.closest(\'form\').submit()" href="javascript://">' +
        guaven_woos.show_all_text + '</a></li>';
    }

    if (typeof guaven_woos_display_in == "undefined") {
      guaven_woos_display_in=gws_define_suggestion_area(guaven_woos.current_input_object);
    }
    let gws_final_html_parts=[guaven_woos_cfinalresult , "<ul class=\"guaven_woos_final_results\">" , guaven_woos_finalresult , guaven_show_all , "</ul>"];
    let gws_final_html=gws_filter_final_html(gws_final_html_parts);
    jQuery('.guaven_woos_suggestion').html(gws_final_html);
    if(guaven_woos.utm_enabled == 1){
      jQuery('.guaven_woos_suggestion_list > a').each(function(i, el) {
        el.href = guaven_woos_add_utm_parameters(el.href, guaven_woos_tempval); 
      });
    }
    gws_currency_solver();
    if (guaven_woos_display_in != '.guaven_woos_suggestion_standalone') {
      if (rescount > 0) jQuery(".guaven_woos_suggestion").css('display', 'block');
      else if (guaven_woos.shownotfound == '' && guaven_woos_cfinalresult == '')
        jQuery(".guaven_woos_suggestion").css('display', 'none');
    }
    if (guaven_woos.shownotfound != '' && guaven_woos_finalresult == '' && guaven_woos_cfinalresult == '') {
      jQuery('.guaven_woos_suggestion').html("<ul  class=\"guaven_woos_final_results\"><li>" + guaven_woos.shownotfound + "</li></ul>");
      if (guaven_show_all != '') jQuery(".guaven_woos_showallli").remove();
      if (guaven_woos.dttrr == 1 && typeof(Storage) !== "undefined") {
        localStorage.keywordfailed = localStorage.keywordfailed + guaven_woos_tempval + ', ';
        if(guaven_woos.ga_enabled == 1 && typeof(ga)!="undefined" && ga.getAll){
          ga.getAll()[0].send('event', 'Live Search', 'failed', guaven_woos_tempval);
        }
      }
      if (guaven_woos.populars_enabled == 1 && guaven_woos_populars_html) {
        let guaven_woos_populars_final = '';
        for (var guaven_woos_pps in guaven_woos_populars_html) {
          if(!(guaven_woos_pps>0))continue;
          guaven_woos_populars_final += guaven_woos_format(guaven_woos_populars_html[guaven_woos_pps], guaven_woos_populars_keywords[guaven_woos_pps]);
        }
        jQuery('.guaven_woos_suggestion').append("<ul class='guaven_woos_suggestion_unlisted guaven_woos_suggestion_populars'>" +
          guaven_woos_populars_final + "</ul>");
      }
    }
  }

  window.gws_push_row = function (_tries,_keyhelper,guaven_woos_temphtml, guaven_woos_temptitle, woos_search_existense, guaven_woos_tempval, stortype,woos_key,_prefix_num){
    _keyhelper.push(guaven_woos.prepare_push(guaven_woos_temphtml, guaven_woos_temptitle, woos_search_existense, guaven_woos_tempval, stortype,woos_key,_prefix_num));
    return _keyhelper;
  }


  function guaven_woos_result_loop(tries, _rescount_prev) {
    var keyhelper = new Array();
    var keyhelper_relevant = new Array();

    guaven_woos_tempval = guaven_woos_tempval.gws_replaceAll(".00''", "''");
    guaven_woos_tempval = guaven_woos_tempval.toLowerCase();
    ilkherf = guaven_woos_tempval.substring(0, 1);

    if (guaven_woos_tempval.indexOf('guaven') > -1) return;
    var guaven_woos_findin_data;
    if (guaven_woos.large_data == 1) {
      guaven_woos_findin_data = gwsc_keywords_arr[ilkherf];
    } else {
      guaven_woos_findin_data = guaven_woos.cache_keywords;
    }
    gws_parceprice=false;
    if (guaven_woos.simple_expressions==1){
      gws_parceprice=gws_simple_expression_scanner(guaven_woos.tempval_raw);
    }
    guaven_woos_tempval_space=guaven_woos_tempval.indexOf(" ");
    guaven_woos_tempval_nospace=guaven_woos_tempval;
    if (guaven_woos_tempval_space > -1) {
      guaven_woos_tempval_nospace=guaven_woos_tempval.gws_replaceAll(" ","");
    }

    var guaven_woos_temptitle = '';
    var guaven_woos_temptitle_raw = '';
    var guaven_woos_temptitle_temp = '';
    var guaven_woos_temphtml = '';
    guaven_woos_curcatid_str = "~"+jQuery(guaven_woos.live_filter_selector).val()+"~";
    //MAIN LOOP STARTS
    for (var guaven_woos_key in guaven_woos_findin_data) {
      if(!(guaven_woos_key>=0) ){continue;}
      guaven_woos_temphtml = guaven_woos.cache_html[guaven_woos_key];
      if (gwsc_keywords_filtered[guaven_woos_key] != undefined) {
        guaven_woos_temptitle = gwsc_keywords_filtered[guaven_woos_key];
        guaven_woos_temptitle_raw = gwsc_keywords_filtered_raw[guaven_woos_key];
      } else {
        guaven_woos_temptitle = guaven_woos.cache_keywords[guaven_woos_key];
        guaven_woos_temptitle_raw = guaven_woos_temptitle;
        guaven_woos_temptitle = guaven_woos_temptitle.toLowerCase();
        if(guaven_woos.stripcharacters!=undefined) guaven_woos_temptitle = guaven_woos_replace_array(guaven_woos_temptitle, gws_specials_0, gws_specials_replacers_0, 1);
        if (guaven_woos.translit_data == '') guaven_woos_temptitle = guaven_woos_replace_array(guaven_woos_temptitle, gws_specials, gws_specials_replacers, 1);
        gwsc_keywords_filtered[guaven_woos_key] = guaven_woos_temptitle;
        gwsc_keywords_filtered_raw[guaven_woos_key] = guaven_woos_temptitle_raw;
      }

      if (guaven_woos.live_filter_selector != '' && guaven_woos_temptitle_raw.indexOf(guaven_woos_curcatid_str) == -1) {
        continue;
      }

      if (gws_parceprice && gws_parceprice_final!='') {
        let simple_expression_response=gws_simple_expression_response(gws_parceprice,gws_parceprice_final,guaven_woos_temphtml,gws_current_segment,gws_current_segment_text);
        if (simple_expression_response=='continue') continue;
      }

      if (guaven_woos.exactmatch == 1) {
        guaven_woos_temptitle_exact_string = guaven_woos_temptitle.replace(/(<([^>]+)>)/ig, ""); 
        guaven_woos_temptitle_exact_string = guaven_woos_temptitle_exact_string.gws_replaceAll(",", " ");
        guaven_woos_temptitle_exact_string = guaven_woos_stripQuotes(guaven_woos_temptitle_exact_string);
        guaven_woos_temptitle_exact = guaven_woos_temptitle_exact_string.split(" ");
        for (var exact_key in guaven_woos_temptitle_exact) {
          if (guaven_woos_temptitle_exact[exact_key] == guaven_woos_tempval) {
            keyhelper=gws_push_row(tries,keyhelper,guaven_woos_temphtml, guaven_woos_temptitle_raw, exact_key, guaven_woos_tempval, '',guaven_woos_key,0);      
          }
        }

      } else if (tries == 0) {
        gws_keyhelper_to_push=[-1,-1];
        if (guaven_woos.orderrelevancy==1){
          var woos_search_existense_relevant_pre = guaven_woos_temptitle.indexOf(" "+guaven_woos_tempval+" ");
          var woos_search_existense_relevant = guaven_woos_temptitle.indexOf(guaven_woos_tempval + " ");
        } 
        else {
          var woos_search_existense_relevant=-1;
          var woos_search_existense_relevant_pre=-1;
        }


        if (woos_search_existense_relevant ===0) {
          gws_keyhelper_to_push=[woos_search_existense_relevant,0];
        } 
        
        else if (woos_search_existense_relevant_pre >-1) {
          gws_keyhelper_to_push=[woos_search_existense_relevant_pre,0];
        }
        
        else {
          var woos_search_existense = guaven_woos_temptitle.indexOf(guaven_woos_tempval);
          if (woos_search_existense > -1) {
            gws_keyhelper_to_push=[woos_search_existense,1];
          } else if (guaven_woos_tempval_space > -1) {
            var concatsearch = guaven_woos_concatsearch(guaven_woos_tempval, guaven_woos_temptitle);
            if (concatsearch > -1) {
              gws_keyhelper_to_push=[(concatsearch + guaven_woos.maxcount),1];
            }
          }
        }

        if(gws_keyhelper_to_push[0]>-1){
          keyhelper=gws_push_row(tries,keyhelper,guaven_woos_temphtml, guaven_woos_temptitle_raw, gws_keyhelper_to_push[0], guaven_woos_tempval, '',guaven_woos_key, gws_keyhelper_to_push[1]);
        }

        if (guaven_woos.disable_meta_correction == 1 && woos_search_existense_sku == -1) {
          woos_search_existense_sku = guaven_woos_temptitle_raw.toLowerCase().indexOf(" " + guaven_woos.tempval_raw + " ");
          if (woos_search_existense_sku == -1 || woos_search_existense_sku <= guaven_woos_temptitle_raw.indexOf("woos_sku")) {
            woos_search_existense_sku = -1;
          }
        }

      } else if (guaven_woos.correction_enabled == 1) {
        if (woos_search_existense_sku > -1) break; //special section - need to be improved for general use

      if (guaven_woos.disable_meta_correction==1){

        guaven_woos_temptitle_temp=guaven_woos_temptitle_raw.split("woos_sku");
        guaven_woos_temptitle=guaven_woos_temptitle_temp[0];

        if (guaven_woos_temptitle_raw.indexOf("woos_sku")>-1){
          guaven_woos_temptitle_temp=guaven_woos_temptitle_raw.split("woos_sku");
        }
        else{
          guaven_woos_temptitle_temp=guaven_woos_temptitle_raw.split("{{k}}");
        }        
        guaven_woos_temptitle=guaven_woos_temptitle_temp[0];
      }


        if (jQuery.inArray(guaven_woos_key,gws_foundids)>-1) continue;
        var lev_a = guaven_woos_tempval;
        var guaven_woos_temptitle_startpoint = guaven_woos_temptitle.indexOf(lev_a.substring(0, 1));
        if (guaven_woos_temptitle_startpoint == -1) guaven_woos_temptitle_startpoint = 0;
        var lev_b = guaven_woos_temptitle.substr(guaven_woos_temptitle_startpoint, lev_a.length
          +1
          ).toLowerCase().gws_replaceAll(" ","").gws_replaceAll("-","");

        var corrected_push = 0;
        
        var lev_a_start=lev_a;
        if(typeof(guaven_woos_tempval_nospace)!='undefined')lev_a_start=guaven_woos_tempval_nospace;
        finalpercent = guaven_woos_levenshtein(lev_a_start, lev_b);
        var finalpercent_weight;
        
        if (finalpercent <= 100 ) {
          corrected_push = 1;
          finalpercent_weight=10+guaven_woos_temptitle_startpoint;       
        } else {
          var lev_a = guaven_woos_tempval.replace(" ", "");
          var gwtsp_splitted = guaven_woos_temptitle.split(' ');

          for (var gwtsp in gwtsp_splitted) {
            if (gwtsp_splitted[gwtsp].length < 3) continue;
            finalpercent = guaven_woos_levenshtein(lev_a, gwtsp_splitted[gwtsp]);
          
            if (finalpercent <= 100) {
              corrected_push = 1;
              finalpercent_weight=100+guaven_woos_temptitle_startpoint;
            }
          }
        }
        if (corrected_push == 1) {
          finalpercent_weight=finalpercent_weight+100 + parseInt(guaven_woos.maxcount) + parseInt(guaven_woos_temptitle.indexOf(guaven_woos.wpml));
          finalpercent_weight=finalpercent_weight+finalpercent;
          keyhelper=gws_push_row(tries,keyhelper,guaven_woos_temphtml, guaven_woos_temptitle_raw, 
            finalpercent_weight, guaven_woos_tempval, 'corrected',guaven_woos_key,2);
        }
      }
    }


    if (guaven_woos.orderrelevancy==1){
      //keyhelper = keyhelper_relevant.concat(keyhelper);
      keyhelper.sort();
    }  
    

    var rescount_new = _rescount_prev;
    let purevalue_str;
    let purevalue;
    let purevalue_1;
    let purevalue_2;
    for (var keyh in keyhelper) {
      purevalue_str=keyhelper[keyh]+'';
      purevalue = purevalue_str.split("~g~v~n~");
      if (purevalue[1]==undefined) continue;
      if (guaven_woos_finalresult.indexOf(purevalue[1]) == -1) {
        if (guaven_woos.backend == 3) {
          purevalue_1 = purevalue[1].split("prli_");
          purevalue_2 = purevalue_1[1].split('"');
          prids_object = prids_object + purevalue_2[0] + ",";
        }
        if (rescount_new < guaven_woos.maxcount) {
          rescount_new++;
          guaven_woos_finalresult = guaven_woos_finalresult + guaven_woos_format(purevalue[1], purevalue[2]);
        }
      }
    }
  }

  guaven_woos.get_unid = function() {
    if (typeof(Storage) !== "undefined") return localStorage.unid;
    else return guaven_woos_getcookie('gws_unid');
  }

  function gws_define_suggestion_area (gwsjqthis){
    if (gwsjqthis.attr("id") != 'guaven_woos_standalone_s') {
      return '.guaven_woos_suggestion';
    } else {
      return '.guaven_woos_suggestion_standalone';
    }
  }

  guaven_woos.mobile_search_open =function(jthis){
    jQuery('.guaven_woos_mobilesearch').show();
    jQuery("body").addClass("guaven_woos_mobile_div_state");
    jQuery('.guaven_woos_suggestion').css({
      'overflow-y': 'scroll',
      'max-height': jQuery(".guaven_woos_mobilesearch").height() - 120 + 'px'
    });
    jQuery("#wpadminbar").hide();
    jQuery('html').css('overflow', 'hidden');
    if (jthis == '' || jthis.attr("id") != 'guaven_woos_s') {
      if(jthis!='')jthis.blur();
      setTimeout(function() {
        jQuery("#guaven_woos_s").trigger('focus');
      }, 400);
      return;
    }
  }
  
  //section main procedure


  guaven_woos.runner = function(){
    guaven_woos.runner_initialized=1;
    var guaven_woos_object_name = guaven_woos.selector;
    var guaven_woos_object = jQuery(guaven_woos_object_name);
    var newunid = '';
    if (typeof(Storage) !== "undefined") {
      if (guaven_woos.dttrr == 1) {
        if ((localStorage.keywordsuccess == undefined)) localStorage.setItem("keywordsuccess", "");
        if ((localStorage.keywordfailed == undefined)) localStorage.setItem("keywordfailed", "");
        if ((localStorage.keywordcorrected == undefined)) localStorage.setItem("keywordcorrected", "");
      }
      if ((localStorage.unid == undefined)) {
        newunid = "user_" + guaven_woos_uniqid();
        localStorage.setItem("unid", newunid);
        document.cookie = "gws_unid=" + newunid + ";path=/";
      } else if (guaven_woos_getcookie('gws_unid') == undefined) document.cookie = "gws_unid=" + localStorage.unid + ";path=/";
    } else if (guaven_woos_getcookie('gws_unid') == undefined) document.cookie = "gws_unid=" + "user_" + guaven_woos_uniqid() + ";path=/";


    gwsc_keywords_arr = new Array();
    guaven_woos_cache_cat_keywords_arr = new Array();

    if (guaven_woos.large_data == 1) {
      for (var guaven_woos_key in guaven_woos.cache_keywords) {
        var indexA = guaven_woos.cache_keywords[guaven_woos_key].substring(0, 1).toLowerCase();
        if (!gwsc_keywords_arr.hasOwnProperty(indexA)) {
          gwsc_keywords_arr[indexA] = new Array();
        }
        gwsc_keywords_arr[indexA][guaven_woos_key] = guaven_woos.cache_keywords[guaven_woos_key];
      }
      for (var guaven_woos_key in guaven_woos_category_keywords) {
        var indexA = guaven_woos_category_keywords[guaven_woos_key].substring(0, 1).toLowerCase();
        if (!guaven_woos_cache_cat_keywords_arr.hasOwnProperty(indexA)) {
          guaven_woos_cache_cat_keywords_arr[indexA] = new Array();
        }
        guaven_woos_cache_cat_keywords_arr[indexA][guaven_woos_key] = guaven_woos_category_keywords[guaven_woos_key];
      }
    }


    jQuery(document).on('touchstart focus', guaven_woos_object_name, function() {
      if (guaven_woos.mobilesearch == 1 && jQuery('.guaven_woos_mobilesearch').width() < '768') {
        guaven_woos.mobile_search_open(jQuery(this));
      }

      guaven_woos_input = jQuery(this);
      guaven_woos_display_in=gws_define_suggestion_area(guaven_woos_input);
      if(guaven_woos_display_in=='.guaven_woos_suggestion'){
        guaven_woos.positioner(guaven_woos_input);
      }

      if (jQuery(this).val() != '') {
        jQuery(this).trigger("keyup");
      } else if (guaven_woos.focused == 0) {
        jQuery(guaven_woos_display_in).html("");
        if (guaven_woos.showinit.length > 2) jQuery(guaven_woos_display_in).html("<ul class=\"guaven_woos_init_text\"><li tabindex=\"-1\">" + guaven_woos.showinit + "</li></ul>");
        if (guaven_woos.pinnedtitle && (guaven_woos_pinned_cat_html != '' || guaven_woos_pinned_html)) {

          guaven_woos_pinned_final = '';
          for (var guaven_woos_ph in guaven_woos_pinned_html) {
            if (guaven_woos_pinned_keywords[guaven_woos_ph].indexOf(guaven_woos.wpml) > -1)
              guaven_woos_pinned_final += guaven_woos_format(guaven_woos_pinned_html[guaven_woos_ph], guaven_woos_pinned_keywords[guaven_woos_ph]);
          }
          guaven_woos_pinned_final = guaven_woos_pinned_cat_html + guaven_woos_pinned_final;
          jQuery(guaven_woos_display_in).append("<p class=\"guaven_woos_pinnedtitle guaven_woos_feattitle\">" + guaven_woos.pinnedtitle + "</p><ul class='guaven_woos_suggestion_unlisted'>" +
            guaven_woos_pinned_final + "</ul>");
        }

        if (guaven_woos.trending != undefined && guaven_woos.trending[0].length > 2) {
          gws_trend_html = JSON.parse(guaven_woos.trending[0]);
          gws_trend_keywords = JSON.parse(guaven_woos.trending[1]);
          guaven_woos_trend_final = '';
          for (var guaven_woos_pps in gws_trend_html) {
            if ( !(guaven_woos_pps>0) || gws_trend_keywords[guaven_woos_pps].indexOf(guaven_woos.wpml) == -1) continue;
            guaven_woos_trend_final = guaven_woos_trend_final + guaven_woos_format(gws_trend_html[guaven_woos_pps], gws_trend_keywords[guaven_woos_pps]).replace(/\\/g, '');
          }
          jQuery(guaven_woos_display_in).append("<p class=\"guaven_woos_pinnedtitle guaven_woos_trendtitle\">" + guaven_woos.trendtitle + "</p><ul class='guaven_woos_suggestion_unlisted guaven_woos_suggestion_trend'>" +
            guaven_woos_trend_final + "</ul>");
        }

        if (guaven_woos.persprod != '' && guaven_woos.persprod != undefined) {
            guaven_woos.persprod=gws_urldecode(guaven_woos.persprod);
            jQuery(guaven_woos_display_in).append("<p class=\"guaven_woos_pinnedtitle guaven_woos_perstitle\">" + guaven_woos.perst +
            "</p><ul class='guaven_woos_suggestion_unlisted'>" + guaven_woos_format(guaven_woos.persprod) + "</ul>");
        }
        gws_currency_solver();
      }
      guaven_woos.focused = 1;
    });
    guaven_woos.set_to_hide='';
    jQuery(document).on('focusout', guaven_woos_object_name, function() {
      guaven_woos.focused = 0;
      guaven_woos.set_to_hide=setTimeout(function() {
        if(guaven_woos.set_to_hide_inside!=undefined)guaven_woos.set_to_hide_inside();
        else if (jQuery(".guaven_woos_mobilesearch").css("display")!='block') {
          jQuery(".guaven_woos_suggestion").css('display', 'none');
        }
      }, guaven_woos.delay_time);
    });

    jQuery(document).on('click',".guaven_woos_suggestion",function(){
			clearTimeout( guaven_woos.set_to_hide);
      guaven_woos.set_to_hide='';
      setTimeout(function(){guaven_woos.set_to_hide='tobefocusedout';},200);
		});
    jQuery(document).on('click',function(){
			if(guaven_woos.set_to_hide=='tobefocusedout')jQuery(guaven_woos.selector).trigger('focusout');
		});
    

    runSearch = '';is_runSearch=0;runSearch_live = '';is_runSearch_live=0;
    gws_global_ret=0;
    guaven_woos_lastval = '';
    woos_search_existense_sku = -1;
    guaven_woos.current_input_object = '';
    gws_foundids=[];
    jQuery(document).on('keyup', guaven_woos_object_name, function(e) {
      guaven_woos.current_input_object = jQuery(this);
      if (guaven_woos_lastval == jQuery(this).val()) return;
      else guaven_woos_lastval = jQuery(this).val();
      woos_search_existense_sku = -1;
      prids_object = "";
      if (e.which === 40 || e.which === 38)
        return;
      guaven_woos_finalresult = '';
      rescount = 0;

      guaven_woos.tempval=gws_tempval(jQuery(this).val());
      guaven_woos_tempval=guaven_woos.tempval;
      guaven_woos.tempval_raw = jQuery(this).val().toLowerCase();
      is_runSearch=1;
      is_runSearch_live=1;
      gws_foundids=[];
      clearTimeout(runSearch);
      clearTimeout(runSearch_live);
      runSearch_live=setTimeout(function() {
        if (guaven_woos.live_server!=1) return;
        if (guaven_woos_tempval.length >= (guaven_woos.minkeycount - 1)) {
          is_runSearch_live=0;
          guaven_woos_do_newer_search(guaven_woos_tempval);
        }
        else if (guaven_woos.showinit.length > 2) {
          jQuery('.guaven_woos_suggestion').html("<ul  class=\"guaven_woos_init_text\"><li>" + guaven_woos.showinit + "</li></ul>");
        } else if (guaven_woos.showinit.length == 0) {
          jQuery('.guaven_woos_suggestion').html("");
        }
        if (e.which != undefined && guaven_woos.dttrr == 1) {
          setTimeout('guaven_woos.send_tr_data()', 3000);
        }
      },guaven_woos.engine_start_delay);

      runSearch = setTimeout(function() {
        if (guaven_woos.live_server==1) return;
        if (guaven_woos_tempval.length >= (guaven_woos.minkeycount - 1)) {
          guaven_woos_result_loop(0,0);
          if (rescount <= guaven_woos.maxtypocount && rescount <= guaven_woos.maxcount) {
            maxpercent = 0;
            finalpercent = 0;
            let maxsimilarword = '';
            guaven_woos_result_loop(1,rescount);
          }
        is_runSearch=0;
        guaven_woos_finish_rendering();
        } else if (guaven_woos.showinit.length > 2) {
          jQuery('.guaven_woos_suggestion').html("<ul  class=\"guaven_woos_init_text\"><li>" + guaven_woos.showinit + "</li></ul>");
        } else if (guaven_woos.showinit.length == 0) {
          jQuery('.guaven_woos_suggestion').html("");
        }
        if (e.which != undefined && guaven_woos.dttrr == 1) {
          setTimeout('guaven_woos.send_tr_data()', 3000);
        }
      }, guaven_woos.engine_start_delay);
    });



    jQuery(window).keydown(function(e) {
      let li = jQuery('.guaven_woos_suggestion li');
      let next=1;let prev=0;
      if (e.which === 40) {
        if (liSelected!=-1) {
          li.eq(liSelected).removeClass('guaven_woos_selected');
          next = liSelected+1;
          if (next <= li.length-1) {
            liSelected = liSelected+1;
          } else {
            liSelected =0;
          }
          li.eq(liSelected).addClass('guaven_woos_selected');
        } else {
          liSelected=0;
          li.eq(liSelected).addClass('guaven_woos_selected');
        }
      } else if (e.which === 38) {
        if (liSelected!=-1) {
          li.eq(liSelected).removeClass('guaven_woos_selected');
          prev = liSelected-1;
          if (prev >= 0) {
            liSelected = liSelected-1;
          } else {
            liSelected =li.length-1;
          }
          li.eq(liSelected).addClass('guaven_woos_selected');
        } else {
          liSelected=0;
          li.eq(liSelected).addClass('guaven_woos_selected');
        }
      }
      else if(e.which == 13 && jQuery(".guaven_woos_selected").html()!=undefined && jQuery(".guaven_woos_selected>a").html()!=undefined) {
        if (jQuery(".guaven_woos_selected>a").attr("href")!=undefined && jQuery(".guaven_woos_selected>a").attr("href").indexOf("http")>-1){
          e.preventDefault();
          window.location.href=jQuery(".guaven_woos_selected>a").attr("href");
          return false;
        }
      }
    });

    jQuery("form").each(function() {
      if (guaven_woos.backend == 2 && jQuery(this).has(guaven_woos.selector).length > 0) {
        jQuery(this).attr("action", guaven_woos.search_results);
        jQuery(this).append('<input type="hidden" name="guaven_woos_stdnln" value="1">');
      }
    });

    jQuery(guaven_woos.selector).closest('form').on('submit',function() {
      if (gws_global_ret==1 || guaven_woos.backend!=3 || guaven_woos.live_server==1) return true;
      gws_this=jQuery(this);
      gws_this_tempval=gws_tempval(gws_this.find('[name="s"]').val());
      gws_this_tempval_nofilter=gws_tempval(gws_this.find('[name="s"]').val(),'nofilter');
      if (gws_this_tempval=='') return true;
      if (is_runSearch==1) {
        setTimeout(function(){
          guaven_woos.backend_preparer(gws_this,gws_this_tempval,gws_this_tempval_nofilter); 
          jQuery(gws_this).submit();
        },1000);
      }
      else {
        var check_direct_submit=guaven_woos.backend_preparer(gws_this,gws_this_tempval,gws_this_tempval_nofilter);
        if(check_direct_submit=='direct_submit') return true;
      }
      return false;
    });

    jQuery(".guaven_woos_showallli a").on("click", function(e) {
      e.preventDefault();
      jQuery(guaven_woos.selector).closest('form').submit();
    });
  }



  function guaven_woos_do_newer_search (keyword){
    if (typeof(gws_xhr)!='string'){
      gws_xhr.abort();
    }

    gws_parceprice = false;
    if (guaven_woos.simple_expressions == 1){
      gws_parceprice = gws_simple_expression_scanner(keyword);
      if(gws_parceprice_final!=undefined && gws_parceprice_final!=""){
        keyword=gws_simple_expression_sanitizer(keyword,gws_parceprice);
      }
    }
    

    var gws_pe_data = {
      action: guaven_woos.live_server_path, 
      product_category_string: jQuery(guaven_woos.live_filter_selector).val() ? '~'+jQuery(guaven_woos.live_filter_selector).val()+'~':'',
      gws_search: keyword,
      price: gws_parceprice_final,
      price_segment: gws_current_segment,
      gws_lang: guaven_woos.wpml,
      validate_code: guaven_woos.validate_code,
    };

    gws_xhr=jQuery.post(guaven_woos.ajaxurl, gws_pe_data, function(response){
        let response_split=response.split("~gws_plus_found_ids~");
        guaven_woos_finalresult= guaven_woos_format(response_split[0]);
        prids_object=response_split[1];
        guaven_woos_finish_rendering();
    });
  }

  //////////////Action Part/////////

  jQuery(window).on('load',function() {
    if(guaven_woos.callafterrender!=undefined){
      gws_cache_activator();
    }
    else {
      jQuery(document).trigger('ready');
    }
    
  });

  jQuery(document).ready(function() {
    jQuery('.guaven_woos_mobilesearch').css({
      'height': (jQuery(window).height()) + 'px'
    });

    jQuery(guaven_woos.selector).each(function() {
      if (jQuery(this).is(":focus")) {
        jQuery(this).blur();
      }
    });
    //section init

    
    jQuery(document).on('keyup', guaven_woos.selector, function(e) {
      if (typeof guaven_woos.cache_keywords=="undefined") {gws_queued_object=jQuery(this);}
    });

    if(guaven_woos.callafterrender==undefined){
      //section load
      gws_cache_activator();
    }
    

    jQuery(".gws_clearable").each(function() {
      var gws_inp = jQuery(this).find("input:text"),
          gws_cle = jQuery(this).find(".gws_clearable__clear");
      gws_inp.on("input", function(){
        gws_cle.toggle(!!this.value);
      });
      gws_cle.on("touchstart click", function(e) {
        e.preventDefault();
        gws_inp.val("").trigger("input");
      });
    });
  });
})(jQuery);