<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban_Request
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 *
 * Base on Curl Library by Matt Wells (www.ninjapenguin.co.uk)
 */
class Douban_Request {
	
	private $_config 		= array();
	private $_resource 		= NULL;
	private static $_status = NULL;
	
	/**
	 * Factory Method
	 *
	 * @param string $data 
	 * @chainable
	 */
	public static function factory($data = array())
	{
		return new Douban_Request($data);
	}
	
	/**
	 * Constructor
	 *
	 * @param array 	curl data
	 */
	public function __construct($data = array())
	{
		if( ! function_exists('curl_init'))
		{
			throw new Exception('A cURL error occurred. It appears you do not have cURL installed!');
		}
		
		$config = array
		(
			CURLOPT_HEADER => FALSE
		);
		
		//Apply any passed configuration
		$data += $config;
		$this->_config = $data;
		
		$this->_resource = curl_init();
				
		//Apply configuration settings
		foreach ($this->_config as $key => $value) 
		{
			$this->set_opt($key, $value);
		}
	}
	
	/**
	 * Set option
	 *
	 * @param string 	Curl option to set
	 * @param string	Value for option
	 * @chainable
	 */
	public function set_opt($key, $value)
	{
		curl_setopt($this->_resource, $key, $value);
		
		return $this;
	}
	
	/**
	 * Execute the curl request and return the response
	 *
	 * @return string	Returned output from the requested resource
	 * @throws Kohana_User_Exception
	 */
	public function exec()
	{
		$ret = curl_exec($this->_resource);
		
		//Wrap the error reporting in an exception
		if($ret === FALSE)
		{
			throw new Exception("Curl Error: ".curl_error($this->_resource));
		}
		else
		{
			Douban_Request::$_status = curl_getinfo($this->_resource, CURLINFO_HTTP_CODE);
			return $ret;
		}
	}
	
	/**
	 * Get curl Error
	 * 
	 * @return string	any current error for the curl request
	 */
	public function get_error()
	{
		return curl_error($this->_resource);
	}
	
	/**
	 * Destructor
	 */
	function __destruct()
	{
		curl_close($this->_resource);
	}
	
	/**
	 * Request GET method 
	 * Execute an HTTP GET request using curl
	 * 
	 * @param string	url to request
	 * @param array		additional headers to send in the request
	 * @param boolean	flag to return only the headers
	 * @param array		Additional curl options to instantiate curl with
	 */
	public static function get($url, Array $headers = array(), $headers_only = FALSE, Array $curl_options = array())
	{
		return Douban_Request::request('GET', $url, NULL, $headers, $headers_only, $curl_options);
	}
	
	/**
	 * Request POST method 
	 * Execute an HTTP POST request using curl
	 *
	 * @param string	url to request
	 * @param mixed		past data to post to $url
	 * @param array		additional headers to send in the request
	 * @param boolean	flag to return only the headers
	 * @param array		Additional curl options to instantiate curl with
	 */
	public static function post($url, $data = '', Array $headers = array(), $headers_only = FALSE, Array $curl_options = array())
	{
		return Douban_Request::request('POST', $url, $data, $headers, $headers_only, $curl_options);
	}
	
	/**
	 * Request PUT method 
	 * Execute an HTTP PUT request using curl
	 *
	 * @param string	url to request
	 * @param array		additional headers to send in the request
	 * @param boolean	flag to return only the headers
	 * @param array		Additional curl options to instantiate curl with
	 */
	public static function put($url, $data = '', Array $headers = array(), $headers_only = FALSE, Array $curl_options = array())
	{
		return Douban_Request::request('PUT', $url, $data, $headers, $headers_only, $curl_options);
	}
	
	/**
	 * Request DELETE method 
	 * Execute an HTTP DELETE request using curl
	 *
	 * @param string	url to request
	 * @param array		additional headers to send in the request
	 * @param boolean	flag to return only the headers
	 * @param array		Additional curl options to instantiate curl with
	 */
	public static function delete($url, $data = '', Array $headers = array(), $headers_only = FALSE, Array $curl_options = array())
	{
		return Douban_Request::request('DELETE', $url, $data, $headers, $headers_only, $curl_options);
	}
	
	/**
	 * Execute an HTTP request
	 *
	 * @param string 	request method
	 * @param string 	url to request
	 * @param string 	additional headers to send in the request
	 * @param boolean	flag to return only the headers
	 * @param array 	flag to return only the headers
	 */
	private static function request($method, $url, $data = NULL, Array $headers = array(), $headers_only = FALSE, Array $curl_options = array())
	{
		$ch = Douban_Request::factory($curl_options);
		$method = strtoupper($method);	
		
		$ch->set_opt(CURLOPT_URL, $url)
		->set_opt(CURLOPT_RETURNTRANSFER, TRUE)
		->set_opt(CURLOPT_NOBODY, $headers_only);
		
		// Available methods: GET, POST, PUT, DELETE
		switch( $method )
		{
			default:
			case 'GET':
				break;
			case 'POST':
				$request = '';
				if (is_array($data))
				{
					foreach ($data as $key => $value)
					{
						$request .= $key.'='.$value.'&';
					}
				}
				else
				{
					$request = $data;
				}
				
				$ch->set_opt(CURLOPT_POST, TRUE)->set_opt(CURLOPT_POSTFIELDS, $request);
				break;
			case 'PUT':
				$request = '';
				if (is_array($data))
				{
					foreach ($data as $key => $value)
					{
						$request .= $key.'='.$value.'&';
					}
				}
				else
				{
					$request = $data;
				}
				$ch->set_opt(CURLOPT_CUSTOMREQUEST, 'PUT')->set_opt(CURLOPT_POSTFIELDS, $request);
				break;
			case 'DELETE':
				$ch->set_opt(CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
		}
		
		//Set any additional headers
		if( ! empty($headers)) 
		{
			$ch->set_opt(CURLOPT_HTTPHEADER, $headers);
		}
		
		$response = array
		(
			'url'		=> $url,
			'method'	=> $method,
			'content'	=> $ch->exec(),
			'status'	=> Douban_Request::$_status,
		);

		return new Douban_Response($response);
	}
}