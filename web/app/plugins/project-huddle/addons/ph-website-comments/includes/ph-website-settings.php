<?php

/**
 * Settings for Website Commenting
 */

/**
 * Add approval settings to settings menu
 *
 * @param $settings
 *
 * @return mixed
 *
 * @since 1.1.1
 */
function ph_website_settings($settings)
{

	$websites = apply_filters('ph_settings_website', array(
		'title'  => __('Websites', 'project-huddle'),
		'fields' => array(
			'disable_self' => array(
				'id'          => 'disable_self',
				'label'       => __('Disable Feedback On This Site', 'project-huddle'),
				'description' => __('Check to disable feedback on this site.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => 0
			),
			'small_website_toolbar' => array(
				'id'          => 'small_website_toolbar',
				'label'       => __('Small Website Toolbar', 'project-huddle'),
				'description' => __('Check to enable a smaller website toolbar', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => 0
			),
			'website_misc' => array(
				'id'          => 'website_misc',
				'label'       => __('Miscellaneous', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			'help_link' => array(
				'id'          => 'help_link',
				'label'       => __('Help Link', 'project-huddle'),
				'description' => __('Link when help icon is clicked. I.E. mailto:you@youremail.com or a custom page.', 'project-huddle'),
				'type'        => 'text',
				'default'     => '',
			)
		)
	));

	// right after mockups
	$i = array_search('mockups', array_keys($settings));
	$settings = array_slice($settings, 0, $i + 1, true) +
		array('website' => $websites) +
		array_slice($settings, $i + 1, NULL, true);

	return $settings;
}
add_filter('project_huddle_settings_fields', 'ph_website_settings');

// simple toolbar option
add_filter('ph_simple_toolbar', function () {
	return filter_var(get_option('ph_small_website_toolbar',  false), FILTER_VALIDATE_BOOLEAN);
}, 9);