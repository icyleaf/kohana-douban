<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban_Response class
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_Response {
	
	private $_url;
	private $_method;
	private $_content;
	private $_status;
	
	public function __construct($response)
	{
		$this->_url 	= $response['url'];
		$this->_method 	= $response['method'];
		$this->_content = $response['content'];
		$this->_status 	= $response['status'];
	}
	
	/**
	 * Orgin format content
	 *
	 * @return object
	 */
	public function to_normal()
	{
		return $this->_content;
	}
	
	/**
	 * Format String(url) to Array
	 *
	 * @param string $url 
	 * @return array
	 */
	public function to_array()
	{
		$content = preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $this->_content);
		if ( ! preg_match('/[=|&amp;]/', $content))
		{
			return $content;
		}
		
		$array = array();
		$expression = explode('&amp;', $content);
		for ($i = 0; $i < count($expression); $i++)
		{
			$attribute = explode('=', $expression[$i]);
			$array[$attribute[0]] = $attribute[1];
		}
		
		return $array;
	}
	
	/**
	 * Format to xml
	 *
	 * @return object
	 */
	public function to_xml()
	{
		return new SimpleXMLElement($this->_content);
	}
	
	/**
	 * Format to json
	 *
	 * @param string $assoc 
	 * @return object
	 */
	public function to_json($assoc = TRUE)
	{
		return json_decode($this->_content, $assoc);
	}
	
	public function __call($name, $arguments)
	{
		if ( empty($arguments) )
		{
			if ($name == 'status')
			{
				return (int) $this->_status;
			}
			elseif ($name == 'url' OR $name == 'method')
			{
				return $this->{'_'.$name};
			}
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	 * Get response http status code
	 *
	 * @return int
	 */
	public function status()
	{
		return (int) $this->_status;
	}
	
	/**
	 * Default render string
	 *
	 * @return string
	 */
	public function __toString() 
	{
		return $this->to_normal();
	}

}