<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban OAuth library
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @version 	0.6
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 * 
 * REQUEST: OAuth official library for PHP (http://oauth.net/code)
 * 			which is store in 'vender' folder
 */
class Douban_OAuth {
	
	public $key			= NULL;		// API key
	public $secret 		= NULL;		// API secret
	
	private $_config 	= NULL;		// douban configurations
	private static $_instance;
	
	public static function instance($key = NULL, $secret = NULL)
	{
		empty(Douban_OAuth::$_instance) AND Douban_OAuth::$_instance = new Douban_OAuth($key, $secret);

		return Douban_OAuth::$_instance;
	}
	
	/**
	 * Construct function
	 *
	 * @param string $key			- API key
	 * @param string $secret		- API secret
	 */
	public function __construct($key = NULL, $secret = NULL)
	{
		// include OAuth classes
		include_once Kohana::find_file('vendor', 'OAuth');
		
		$this->key = $key;
		$this->secret = $secret;
		$this->_config = Kohana::$config->load('douban');
	}
	
	/**
	 * Get Requset token
	 *
	 * @param string $url  			- requset token url
	 * @param string $method 		- http sending method
	 * @param array $parameters 	- sending parameters
	 * @return string
	 */
	public function request_token($url, $method = 'POST', $parameters = array())
	{
		$request = $this->_prepare_request(NULL, $method, $url, $parameters);
		if ($method == 'POST')
		{
			return Douban_Request::post($url, $request->to_postdata()); 
		}
		else
		{
			return Douban_Request::get($request->to_url()); 
		}
	}
	
	/**
	 * Ger access token
	 *
	 * @param string $url 			- access token url
	 * @param object $token 		- request token
	 * @param string $method 		- http sending method
	 * @param array $parameters 	- sending parameters
	 * @return string
	 */
	public function access_token($url, $token, $method = 'POST', $parameters = array())
	{
		$request = $this->_prepare_request($token, $method, $url, $parameters);
		if ($method == 'POST')
		{
			return Douban_Request::post($url, $request->to_postdata()); 
		}
		else
		{
			return Douban_Request::get($request->to_url()); 
		}
	}
	
	/**
	 * OAuth validate method
	 *
	 * @param string $type - activate in HMAC_SHA1, RSA_SHA1, PLAINTEXT
	 * @return object
	 */
	public function sign_method($type = 'HMAC_SHA1')
	{
		switch (strtoupper($type))
		{
			default:
			case 'HMAC_SHA1':
				return new OAuthSignatureMethod_HMAC_SHA1();
			case 'RSA_SHA1':
				return new OAuthSignatureMethod_RSA_SHA1();
			case 'PLAINTEXT':
				return new OAuthSignatureMethod_PLAINTEXT();
		}
	}
	
	/**
	 * Get request/access token
	 *
	 * @param mixed $token - request/access token
	 * @param string $secret - request/access secret
	 * @return object
	 */
	public function oauth_token($token, $secret = NULL)
	{
		if (is_array($token))
		{
			return new OAuthToken($token['oauth_token'], $token['oauth_token_secret']);
		}
		elseif($token AND $secret)
		{
			return new OAuthToken($token, $secret);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get request url
	 *
	 * @param string $method - http sending method
	 * @param string $url - http sending url
	 * @param array $parameters - http sending parameters
	 * @return object
	 */
	public function get_request($method, $url, $parameters)
	{
		return new OAuthRequest($method, $url, $parameters);
	}
	
	/**
	 * Get request url from Consumer
	 *
	 * @param string $consumer - OAuth consumer
	 * @param mixed $token - request token
	 * @param string $method - http sending url
	 * @param string $url - http sending method
	 * @param array $parameters - http sending parameters
	 * @return object
	 */
	public function consumer_request($consumer = NULL, $token = NULL, $method = NULL, $url = NULL, $parameters = NULL)
	{
		return OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parameters);
	}
		
	/**
	 * Create OAuth token
	 *
	 * @param array $response - requset token
	 * @return object
	 */
	protected function create_oauth_token($response)
	{
		if (isset($response['oauth_token']) AND isset($response['oauth_token_secret'])) {
			return $this->oauth_token($response['oauth_token'], $response['oauth_token_secret']);
		}
		
		return NULL;
	}
	
	/**
	 * Create OAuth Consumer
	 *
	 * @return object
	 */
	private function consumer()
	{
		return new OAuthConsumer($this->key, $this->secret);
	}
	
	/**
	 * Call API with a GET request
	 *
	 * @param string $url 
	 * @param mixed $data 
	 * @param mixed $header 
	 * @param boolean $headers_only 
	 * @return object
	 */
	public function get($url, $data = NULL, $header = array(), $headers_only = FALSE)
	{
		if ($token = $this->_get_oauth_token())
		{
			// get data with oauth token
			$accessToken = $this->oauth_token($token);
			$request = $this->_prepare_request($accessToken, 'GET', $url, $data);
			$header = array
			(
				$request->to_header()
			);
			
			$result = Douban_Request::get($request->to_url(), $header, $headers_only);
		}
		else
		{
			// get data with api key
			$data['apikey'] = $this->key;
			$url = $this->_build_url($url, $data);
			$result = Douban_Request::get($url, $header, $headers_only);
		}
	
		return $result;
	}
	
	/**
	 * Call API with a POST request
	 *
	 * @param string $url 
	 * @param mixed $data 
	 * @param mixed $header 
	 * @param boolean $headers_only 
	 * @return object
	 */
	public function post($url, $data = NULL, $header = NULL, $headers_only = FALSE)
	{
		if ($token = $this->_get_oauth_token())
		{
			@$header OR $header = array();
			$accessToken = $this->oauth_token($token);
			$request = $this->_prepare_request($accessToken, 'POST', $url);
			$header = array_merge(array($request->to_header()), $header);
	
			return Douban_Request::post($url, $data, $header, $headers_only);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Call API with a PUT request
	 * 
	 * @param string $url 
	 * @param mixed $data 
	 * @param mixed $header 
	 * @param boolean $headers_only 
	 * @return object
	 */
	public function put($url, $data = NULL, $header = NULL, $headers_only = FALSE)
	{
		if ($token = $this->_get_oauth_token())
		{
			@$header OR $header = array();
			$accessToken = $this->oauth_token($token);
			$request = $this->_prepare_request($accessToken, 'PUT', $url);
			$header = array_merge(array($request->to_header()), $header);
	
			return Douban_Request::put($url, $data, $header, $headers_only);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Call API with a DELETE request
	 *
	 * @param string $url 
	 * @param mixed $data 
	 * @param mixed $header 
	 * @param boolean $headers_only 
	 * @return object
	 */
	public function delete($url, $data = NULL, $header = NULL, $headers_only = FALSE)
	{
		if ($token = $this->_get_oauth_token())
		{
			@$header OR $header = array();
			$accessToken = $this->oauth_token($token);
			$request = $this->_prepare_request($accessToken, 'DELETE', $url);
			$header = array_merge(array($request->to_header()), $header);
	
			return Douban_Request::delete($url, $data, $header, $headers_only);
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Prepere Request
	 *
	 * @param object $token 
	 * @param string $httpMethod 
	 * @param string $url 
	 * @param string $parameters 
	 * @return void
	 */
	private function _prepare_request($token, $httpMethod, $url, $parameters = NULL)
	{
		$consumer = $this->consumer();
		$request = $this->consumer_request($consumer, $token, $httpMethod, $url, $parameters);
		$request->sign_request($this->sign_method('HMAC_SHA1'), $consumer, $token);
		
		return $request;
	}
	
	/**
	 * Get oauth access token
	 *
	 * @return mixed
	 */
	protected function _get_oauth_token()
	{
		if ($token = Cookie::get($this->_config->session_key['oauth_token']))
		{
			return unserialize($token);
		}
		else if ($token = Session::instance()->get($this->_config->session_key['oauth_token']))
		{
			return $token;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Build url with GET parameters
	 *
	 * @param string $url 
	 * @param array $parameters 
	 * @return string
	 */
	private function _build_url($url, $parameters)
	{
		$total = array();
		foreach ($parameters as $key => $value)
		{
			$total[] = $key . "=" . $value;
		}
		
		$url = $url . '?' . implode("&", $total);
		
		return $url;
	}
}
