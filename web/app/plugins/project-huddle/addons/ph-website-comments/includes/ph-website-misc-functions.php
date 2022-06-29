<?php

/**
 * Miscellaneous Functions
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2017, Andre Gagnon
 * @since       2.6.0
 */

/**
 * Validates a url or partial url
 *
 * @param $url string URL to validate
 *
 * @return bool|WP_Error True if valid, WP Error if not
 */
function ph_validate_partial_url($url)
{
	if (parse_url($url, PHP_URL_SCHEME) != '') {
		// URL has http/https/...
		if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			return new WP_Error('invalid_url', sprintf(__('%1$s is not a valid url.', 'project-huddle'), $url));
		}
	} else {
		// PHP filter_var does not support relative urls, so we simulate a full URL
		if (filter_var('http://www.example.com/' . ltrim($url, '/'), FILTER_VALIDATE_URL) === false) {
			return new WP_Error('invalid_url', sprintf(__('%1$s is not a valid url.', 'project-huddle'), $url));
		}
	}

	return true;
}

/**
 * Turns an absolute url to relative
 *
 * @param $url string Absolute or Relative URL
 *
 * @return string relative URL
 */
function ph_url_relative($url)
{
	$parsed = parse_url(esc_url($url));

	// store only path and query in case domain changes
	$url = $parsed['path'];
	if (isset($parsed['query'])) {
		$url .= '?' . $parsed['query'];
	}

	return $url;
}

function ph_normalize_url($url, $queries = true)
{
	$parsed = parse_url($url);
	$normalized = trailingslashit($parsed['host'] . (isset($parsed['path']) ? $parsed['path'] : ''));

	if ($queries && isset($parsed['query'])) {
		// remove access token
		$queries = [];
		parse_str($parsed['query'], $queries);
		if (isset($queries['ph_access_token'])) {
			unset($queries['ph_access_token']);
		}

		$normalized = $normalized . "?" . http_build_query($queries);
	}


	return $normalized;
}

function ph_unparse_url($parsed_url)
{
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}
