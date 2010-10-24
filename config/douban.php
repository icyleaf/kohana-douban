<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Douban API configuration
 *
 * http://www.douban.com/service/apidoc/
 */
return array(
	/**
	 * Douban API
	 * 
	 * @param api_key		API Key
	 * @param api_secret	API Secret
	 */
	'api_key'		=> '',
	'api_secret'	=> '',
	
	/**
	 * Configuration
	 */
	'lifetime'		=> 3600 * 30 * 30,						// Store for 30 days
	'session_key'	=> array(
		'oauth_token'	=> 'oauth_token',					// Saved access token
		'oauth_user'	=> 'oauth_user',					// Saved current user
		),
);

