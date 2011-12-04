<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Douban API configuration
 *
 * @link http://www.douban.com/service/apidoc/
 */
return array(
	/**
	 * Douban API
	 * 
	 * @param api_key		API Key
	 * @param api_secret	API Secret
	 */
	'api_key'		=> '0816a2ebb89b35331843e8c329d19f99',
	'api_secret'	=> 'a0e0012eeda56b40',
	
	/**
	 * Configuration
	 */
	'lifetime'		=> 3600 * 30 * 30,						// Store for 30 days
	'session_key'	=> array(
		'oauth_token'	=> 'oauth_token',					// Saved access token
		'oauth_user'	=> 'oauth_user',					// Saved current user
		),
);

