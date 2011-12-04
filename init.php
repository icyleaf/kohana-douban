<?php defined('SYSPATH') or die('No direct script access.');
/**
 * douban api json formatted
 */
Route::set('douban/console/api', 'douban_console_<controller>/<action>(/<id>)', array(
		'controller'	=> '\w+',
		'action'		=> '\w+',
		'index'			=> '\d+',
		'max'			=> '\d+',
	))
	->defaults(array(
		'directory'		=> 'douban/console',
	));

/**
 * douban api console
 */
Route::set('douban/console', 'douban_console(/<action>(/<id>))', array(
		'action'		=> '\w+',
		'id'			=> '\w+',
	))
	->defaults(array(
        'directory'		=> 'douban',
		'controller' 	=> 'console',
		'action'		=> 'index',
	));

/**
 * douban api demo
 */
Route::set('douban/demo', 'douban_demo(/<action>(/<id>))', array(
		'action'		=> '\w+',
		'id'			=> '\w+',
	))
	->defaults(array(
        'directory'		=> 'douban',
		'controller' 	=> 'demo',
		'action'		=> 'index',
	));


/**
 * Remap media folder
 */
Route::set('media', 'media(/<file>)', array(
		'file' 	=> '.+'
	))
	->defaults(array(
        'directory'		=> 'douban',
		'controller'	=> 'console',
		'action'		=> 'media',
		'file'		 	=> NULL,
	));