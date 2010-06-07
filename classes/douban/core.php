<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban API Entry library
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_Core {
	
	// Release version
	const VERSION 						= '1.0.3';
	
	// Douban OAuth URL
	const SERVER_URL    				= 'http://api.douban.com/';
	const REQUEST_TOKEN_URL				= 'http://www.douban.com/service/auth/request_token';
	const AUTHORIZATION_URL				= 'http://www.douban.com/service/auth/authorize';
	const ACCESS_TOKEN_URL				= 'http://www.douban.com/service/auth/access_token';
	const VERITY_TOKEN_URL				= 'http://api.douban.com/access_token/';
	// Douban API URL
	const PEOPLE_URL 					= 'http://api.douban.com/people/';
	const MINIBLOG_URL 					= 'http://api.douban.com/miniblog/';
	const BOOK_URL 						= 'http://api.douban.com/book/subject/';
	const MUSIC_URL 					= 'http://api.douban.com/music/subject/';
	const MOVIE_URL 					= 'http://api.douban.com/movie/subject/';
	const EVENT_URL 					= 'http://api.douban.com/event/';
	const DOUMAIL_URL 					= 'http://api.douban.com/doumail/';
	const COLLECTION_URL 				= 'http://api.douban.com/collection/';
	const NOTE_URL		 				= 'http://api.douban.com/note/';
	const REVIEW_URL					= 'http://api.douban.com/review/';
	const RECOMMENDATION_URL			= 'http://api.douban.com/recommendation/';
	const ALBUM_URL						= 'http://api.douban.com/album/';
	// Douban Category URL
	const CATEGORY_URL					= 'http://www.douban.com/2007#';
	// The Default Image URL
	const DEFAULT_PEOPLE_AVATAR_URL 	= 'http://t.douban.com/icon/user.jpg';
	const DEFAULT_BOOK_IMAGE_URL 		= 'http://t.douban.com/pics/book-default-small.gif';
	const DEFAULT_MUSIC_IMAGE_URL 		= 'http://t.douban.com/pics/music-default-small.gif';
	const DEFAULT_MOVIEE_IMAG_URL 		= 'http://t.douban.com/pics/movie-default-small.gif';
	const DEFAULT_EVENT_IMAGE_URL 		= 'http://t.douban.com/pics/event/bpic/event_dft.jpg';
	
	public $alt               			= 'json';	// Return format type from Douban API. Available: json, atom(xml)
	public $format            			= TRUE;		// Set 'TRUE', it will return 'Douban->alt' format type.
	public $session           			= NULL;
	
	protected $_config        			= NULL;		// Douban configuration
	protected $_client        			= NULL;		// OAuth
	protected $_method        			= NULL;		// OAurh method
	protected $_errors		            = NULL;		// Throw Errors
	
	private static $_instance;
		
	/**
	 * Instance Douban
	 *
	 * @param string $api_key    - Douban API key
	 * @param string $api_secret - Douban API secret
	 * @return object
	 */
	public static function instance($api_key = NULL, $api_secret = NULL)
	{
		empty(Douban_Core::$_instance) AND Douban_Core::$_instance = new Douban_Core($api_key, $api_secret);

		return Douban_Core::$_instance;
	}
	
	/**
	 * Construct Douban function
	 *
	 * @param string $api_key    - Douban API key
	 * @param string $api_secret - Douban API secret
	 */
	public function __construct($api_key = NULL, $api_secret = NULL)
	{
		$api_key = empty($api_key) ? Kohana::config('douban.api_key') : $api_key;
		$api_secret = empty($api_secret) ? Kohana::config('douban.api_secret') : $api_secret;
		
		if ($api_key AND $api_secret)
		{
			$this->_client = Douban_OAuth::instance($api_key, $api_secret);
			$this->_method = $this->_client->sign_method('HMAC_SHA1');
			
			$this->session = Session::instance();
			$this->_config = Kohana::config('douban');
		}
		else
		{
			throw new Exception('豆瓣  API Key 或 Secrect 是空的!');
		}
	}
		
	/**
	 * Get request token
	 *
	 * @return mixed
	 */
	public function request_token()
	{
		return $this->_client->request_token(Douban_Core::REQUEST_TOKEN_URL);
	}
	
	/**
	 * Get Authorization URL
	 *
	 * @param string $key - request token key
	 * @param string $secret - request token secret
	 * @param string $callback - callback url after authorization
	 * @return string
	 */
	public function auth_url($request_token = array(), $callback = NULL)
	{
		$parameters = array
		(
			'oauth_token' => $request_token['oauth_token']
		);

		if ($callback) 
		{
			// set the callback url
			$parameters['oauth_callback'] = $callback;
		}
		
		$oauth_request = $this->_client->get_request('GET', Douban_Core::AUTHORIZATION_URL, $parameters);
		
		return $oauth_request->to_url();
	}
	
	/**
	 * Get access token
	 *
	 * @param mixed $token - request token
	 * @param string $secret - request token secret
	 * @param string $returnURL - return type, default is array
	 * @return mixed
	 */
	public function access_token($token, $secret = NULL)
	{
		if (is_array($token))
		{
			$request_token = $this->_client->oauth_token($token['oauth_token'], $token['oauth_token_secret']);
		}
		elseif ($token AND $secret)
		{
			$request_token = $this->_client->oauth_token($token, $secret);
		}
		else
		{
			return FALSE;
		}
		
		$oauth_request = $this->_client->consumer_request($this->_client, $request_token, 'GET', Douban_Core::ACCESS_TOKEN_URL);
		$oauth_request->sign_request($this->_method, $this->_client, $request_token);
		$access_token = Douban_Request::get($oauth_request);
		
		return $access_token;
	}
	
	/**
	 * Verify api and general auth url
	 *
	 * @param string $callback_url 
	 * @return mixed
	 */
	public function verify($callback_url)
	{
		$request_token = $this->request_token();

		if ($request_token->status() == 200)
		{
			$token = $request_token->to_array();
			$this->session->set('request_token', $token);
			
			// get Authorization URL from douban.com
			return $this->auth_url($token, $callback_url);
		}
		else 
		{
			$this->_errors = $request_token;
			
			return FALSE;
		}
	}
	
	/**
	 * OAuth login
	 *
	 * @return boolean
	 */
	public function login()
	{
		// Request token
		$request_token = $this->session->get('request_token');
		$this->session->delete('request_token');
		
		// Get Access token
		$access_token = $this->access_token($request_token);
		if ($access_token->status() == 200)
		{
			$access_token = $access_token->to_array();
			// Save the access token into both cookie and session.
			Cookie::set($this->_config->session_key['oauth_token'], serialize($access_token), $this->_config->lifetime);
			$this->session->set($this->_config->session_key['oauth_token'], $access_token);
			// Get oauth user information
			$people = $this->people()->get('me');
			$this->session->set($this->_config->session_key['oauth_user'], $people);

			return TRUE;
		}
		else
		{
			$this->_errors = $request_token;
			
			return FALSE;
		}
	}
	
	/**
	 * Get Current User profile
	 *
	 * @return mixed
	 */
	public function get_user()
	{
		if ($user = $this->session->get($this->_config->session_key['oauth_user']))
		{
			return $user;
		}
		else
		{
			if ($token = $this->_get_oauth_token())
			{
				// Save the access token into both cookie and session.
				Cookie::set($this->_config->session_key['oauth_token'], serialize($token), $this->_config->lifetime);
				$this->session->set($this->_config->session_key['oauth_token'], $token);
				// Get oauth user information
				$people = $this->people()->get('me');
				$this->session->set($this->_config->session_key['oauth_user'], $people);
				
				return $people;
			}
			else
			{
				return FALSE;
			}
		}
	}
	
	/**
	 * Check user if logged in
	 *
	 * @return boolean
	 */
	public function logged_in()
	{
		$access_token = $this->_get_oauth_token();
		if (is_array($access_token))
		{
			$url = Douban_Core::VERITY_TOKEN_URL.$access_token['oauth_token'];
			$request = $this->_client->get($url);
			if ($request->status() == 200)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Destory oauth token
	 */
	public function logout()
	{
		$this->session->delete($this->_config->session_key['oauth_token']);
		$this->session->destroy();
		
		Cookie::delete('oauth_token');
		Cookie::delete('oauth_token_secret');
	}
	
	/**
	 * Default call Douban API by $name without $arguments
	 *
	 * @param string $name 
	 * @param string $arguments 
	 */
	public function __call($name, $arguments)
	{
		if (empty($arguments))
		{
			if ($name == 'errors')
			{
				return $this->_errors;
			}
			else
			{
                // Create the Douban API instance
				$interface = 'Douban_API_' . ucfirst($name);

				// Load douban api interface
				return new $interface();
			}
		}
		else
		{
			return NULL;
		}
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
		else if ($token = $this->session->get($this->_config->session_key['oauth_token']))
		{
			return $token;
		}
		else
		{
			return FALSE;
		}
	}
	
	protected function to_array($string)
	{
		$string = preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $string);
		if ( ! preg_match('/[=|&amp;]/', $string))
		{
			return $string;
		}
		
		$array = array();
		$expression = explode('&amp;', $string);
		for ($i = 0; $i < count($expression); $i++)
		{
			$attribute = explode('=', $expression[$i]);
			$array[$attribute[0]] = $attribute[1];
		}
		
		return $array;
	}

}

