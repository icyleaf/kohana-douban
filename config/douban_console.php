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
	
	'methods'		=> array(
		'people'		=> array(
			'get'				=> array('id'),
			'getFriends'		=> array('id', 'index', 'max'),
			'getContacts'		=> array('id', 'index', 'max'),
			'search'			=> array('query', 'index', 'max'),
			),
		'broadcast'		=> array(
			'get'				=> array('id'),
			'getContacts'		=> array('id', 'index', 'max'),
			'getMine'			=> array('id', 'index', 'max'),
			'post'				=> array('message'),
			),
		'collection'			=> array(
			'getByPeople'		=> array('id', 'index', 'max'),
			'getBookByPeople'	=> array('id', 'index', 'max'),
			'getMusicByPeople'	=> array('id', 'index', 'max'),
			'getMovieByPeople'	=> array('id', 'index', 'max'),
		),
		'doumail'		=> array(
			'get'				=> array('id'),
			'getUnread'			=> array('index', 'max'),
			'getInbox'			=> array('index', 'max'),
			'getOutbox'			=> array('index', 'max'),
			'send'				=> array('people_id', 'title', 'content', 'captcha_token', 'captcha_string'),
			),
		'book'		=> array(
			'get'				=> array('id'),
			'search'			=> array('query', 'index', 'max'),
			),
		'movie'		=> array(
			'get'				=> array('id'),
			'search'			=> array('query', 'index', 'max'),
			),
		'music'		=> array(
			'get'				=> array('id'),
			'search'			=> array('query', 'index', 'max'),
			),
		'event'		=> array(
			'get'				=> array('id'),
			'getByPeople'		=> array('id', 'index', 'max'),
			'getByLocation'		=> array('location', 'index', 'max'),
			'getParticipants'	=> array('id', 'index', 'max'),
			'getWishers'		=> array('id', 'index', 'max'),
			'search'			=> array('query', 'index', 'max'),
			),
		),
);

