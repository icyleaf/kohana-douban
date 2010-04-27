<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Overwrite The default Kohana error reporting
 *
 * The default Kohana error reporting will throws a exception about that:
 * openSearch:index, openSearch:max, openSearch:total is undefined index
 * in formated json array.
 */
error_reporting(E_ALL & ~E_NOTICE);