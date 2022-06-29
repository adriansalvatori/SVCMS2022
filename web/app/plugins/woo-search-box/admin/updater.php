<?php
global  $gws_api_url,$gws_plugin_slug;
$gws_api_url = 'https://guaven.com/plugin_updater/';
$gws_plugin_slug = 'woo-search-box';


add_action('admin_footer',function(){
    if(get_option('guaven_woos_support_expired_msg')=='')return;
?>
<script>jQuery("#woo-search-box-update .update-message p").html('<?php echo strip_tags(get_option('guaven_woos_support_expired_msg'),'<a>,<i>'); ?>');</script>
<?php 
});

add_filter('pre_set_site_transient_update_plugins', 'check_for_gws_plugin_update');
function check_for_gws_plugin_update($checked_data) {
	global  $gws_api_url,$gws_plugin_slug,$wp_version;


	if (empty($checked_data->checked) or get_option('guaven_woos_purchasecode')=='')
	 return $checked_data;
  
  	if(empty($checked_data->checked[$gws_plugin_slug .'/'. $gws_plugin_slug .'.php'])) return;

  	$args = array(
		'slug' => $gws_plugin_slug,
		'version' => $checked_data->checked[$gws_plugin_slug .'/'. $gws_plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check',
				'request' => serialize($args),
				'api-key' => get_option('guaven_woos_purchasecode')
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	$raw_response = wp_remote_post($gws_api_url, $request_string);
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
	{
		$support_expired_msg = wp_remote_retrieve_header($raw_response, 'support_status_message');
		if (!empty($support_expired_msg)){
			update_option('guaven_woos_support_expired', 2);
			update_option('guaven_woos_support_expired_msg', $support_expired_msg);	
		} 
		else {
			update_option('guaven_woos_support_expired', 0);
			update_option('guaven_woos_support_expired_msg', '');	
		} 
		$response = unserialize($raw_response['body']);
	}	
	else $response ='';

		

	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$gws_plugin_slug .'/'. $gws_plugin_slug .'.php'] = $response;

	return $checked_data;
}

add_filter('plugins_api', 'gws_plugin_api_call', 10, 3);
function gws_plugin_api_call($def, $action, $args) {
	global $gws_plugin_slug, $gws_api_url, $wp_version;

	if (!isset($args->slug) || ($args->slug != $gws_plugin_slug) || get_option('guaven_woos_purchasecode')=='')
		return false;

	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$gws_plugin_slug .'/'. $gws_plugin_slug .'.php'];
	$args->version = $current_version;

	$request_string = array(
			'body' => array(
				'action' => $action,
				'request' => serialize($args),
				'api-key' => get_option('guaven_woos_purchasecode')
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	$request = wp_remote_post($gws_api_url, $request_string);
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if (!empty($request['body']) and strpos($request['body'],'expired')!==false) {
			update_option('guaven_woos_support_expired',1);
		}
		else {
			update_option('guaven_woos_support_expired',$request['body']);
			update_option('guaven_woos_support_expired_msg', '');	
		}

		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}

	return $res;
}
