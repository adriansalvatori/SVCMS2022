<?php

/**
 * i8ln functions
 */

function get_json_translation_file()
{
	// Set filter for plugin's languages directory.
	$ph_lang_dir = PH_PLUGIN_DIR . 'languages/';
	$ph_lang_dir = apply_filters('ph_languages_directory', $ph_lang_dir);

	// Traditional WordPress plugin locale filter.
	$locale  = apply_filters('plugin_locale', get_locale(), 'ph');

	// get .jed.json
	$jedfile = sprintf('%1$s-%2$s.jed.json', 'project-huddle', $locale);
	$jedfile_local  = $ph_lang_dir . $jedfile;
	$jedfile_global = WP_LANG_DIR . '/ph/' . $jedfile;

	// get .json
	$jedfile_plain = sprintf('%1$s-%2$s.json', 'project-huddle', $locale);
	$jedfile_local_plain  = $ph_lang_dir . $jedfile_plain;
	$jedfile_global_plain = WP_LANG_DIR . '/ph/' . $jedfile_plain;

	// check for .jed.json global plugins path
	if (!file_exists($jedfile_global)) {
		$jedfile_global = WP_LANG_DIR . '/plugins/' . $jedfile;
	}

	// check for .json global plugins path
	if (!file_exists($jedfile_global_plain)) {
		$jedfile_global_plain = WP_LANG_DIR . '/plugins/' . $jedfile_plain;
	}

	if (file_exists($jedfile_global)) {
		// Look in global /wp-content/languages/ph folder.
		return $jedfile_global;
	} elseif (file_exists($jedfile_local)) {
		// Look in local /wp-content/plugins/project-huddle/languages/ folder.
		return $jedfile_local;
	} elseif (file_exists($jedfile_global_plain)) {
		return $jedfile_global_plain;
	} elseif (file_exists($jedfile_local_plain)) {
		return $jedfile_local_plain;
	} else {
		return '';
	}
}
function ph_get_json_translations($handle)
{
	$file = get_json_translation_file();

	if (!$file || !is_readable($file)) {
		return false;
	}

	if (file_exists($file)) {
		$file = file_get_contents($file);
		if (is_string($file) && $file !== '') {
			return json_decode($file, true);
		}
	}
}
