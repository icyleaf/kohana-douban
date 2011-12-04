<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console extends Controller_Template {

	public $douban		= NULL;
	public $auth		= NULL;
	public $session		= NULL;
	public $header		= NULL;
	public $template	= 'douban/console/home';

    protected $_config  = NULL;
	
	public function before()
	{
		$this->_config = Kohana::$config->load('douban_console');
		$this->session = Session::instance();
		$this->douban = Douban::instance($this->_config->api_key, $this->_config->api_secret);
		$people = $this->douban->get_user();
		if ($people instanceof Douban_Response)
		{
			unset($people);
		}
		
		$this->header = Head::instance();
		$this->header->title->set('豆瓣 API 测试控制台');
		$this->header->css->append_file('media/douban/console/css/screen.css', 'date', 'all');
		$this->header->css->append_file('media/douban/console/css/layout.css', 'date');
		$this->header->css->brower_hacks('media/douban/console/css/ie6.css', 'ie', '6');
		$this->header->css->brower_hacks('media/douban/console/css/ie7.css', 'ie', '7');
		$this->header->javascript->append_file('media/douban/console/js/jquery-1.4.2.min.js');
		$this->header->javascript->append_file('media/douban/console/js/console.js', 'date');

		$this->template = View::factory($this->template)
			->set('header', $this->header)
			->bind('people', $people);
	}
	
	public function action_index()
	{
		$this->template->action = 'home';
	}
	
	public function action_about()
	{
		$this->header->title->prepend('关于');
		$this->template->action = 'about';
	}
	
	public function action_json()
	{
        $this->auto_render = FALSE;

        $type = $this->request->param('id');
		$this->request->headers = array(
			'Content-Type'	=> 'application/json; charset=utf-8'
		);
		$this->response->body(json_encode($this->_config->$type));
	}
	
	public function action_auth()
	{
		$auth_url = $request_token = $this->douban->verify(URL::site('douban_console/check'));
		
		$this->request->redirect($auth_url);
	}
	
	public function action_check()
	{
		if ($this->douban->login())
		{
			$this->request->redirect('douban_console');
		}
		else
		{
			$this->template->content = 'OAuth 验证失败，'.HTML::anchor('douban_console/auth', '重试');
		}
	}
	
	protected static function render($result = NULL, $alt = 'atom')
	{
		$url = $result->url();
		$url_array = parse_url($url);
		if (isset($url_array['query']))
		{
			$query_array = explode('&', $url_array['query']);
			$removed_query = array(
				'oauth_version', 'oauth_nonce', 'oauth_timestamp',
				'oauth_consumer_key', 'oauth_token', 'oauth_signature_method',
				'oauth_signature'
				);
	
			$post_data = array();
			$i = 0;
			foreach ($query_array as $item)
			{
				list($key, $value) = explode('=', $item);
				if (in_array($key, $removed_query))
				{
					unset($query_array[$i]);
				}
				else
				{
					$post_data[$key] = $value;
				}
				$i++;
			}
			$post_url = $url_array['scheme'].'://'.$url_array['host'].
				$url_array['path'].'?'.http_build_query($post_data, '&');	
		}
		else
		{
			$post_url = $url;
		}

		$response = $result->to_normal();
		$status = $result->status();
		$success_array = array(200, 201, 202);
		if ( ! in_array($status, $success_array))
		{
			echo $post_url.'[-icyleaf-](<b>'.$status.'</b>) '.$response;
		}
		else
		{
			if ($alt == 'json')
			{
				echo $post_url.'[-icyleaf-]'.htmlspecialchars(self::format_json($response));
			}
			else
			{
				echo $post_url.'[-icyleaf-]'.htmlspecialchars($response);
			}
		}
	}
		
	private function login()
	{
		$status = FALSE;
		if ($token = $this->session->get('md_token'))
		{
			$this->douban->login($token['oauth_token'], $token['oauth_token_secret']);
			$status = TRUE;
		} 
		else
		{
			$key = cookie::get('oauth_token');
			$secret = cookie::get('oauth_token_secret');
			if ( ! empty($key) AND ! empty($secret))
			{
                $status = TRUE;
                $this->douban->login($key, $secret);

				$this->session->set(
					'md_token',
					array(
						'oauth_token'=> $key,
						'oauth_token_secret' => $secret
						)
					);
				$this->session->set('auth_user', $this->douban->people('me'));
			}
		}

		return $status;
	}
	
	private static function format_json($json)
	{
		$tab = '  ';
		$new_json = '';
		$indent_level = 0;
		$in_string = false;

		$json_obj = json_decode($json);

		if ( ! $json_obj)
		{
			return $new_json;
		}

		$len = strlen($json);
		for ($c = 0; $c < $len; $c++)
		{
			$char = $json[$c];
			switch($char)
			{
				case '{':
				case '[':
					if ( ! $in_string)
					{
						$new_json .= $char."\n".str_repeat($tab, $indent_level+1);
						$indent_level++;
					}
					else
					{
						$new_json .= $char;
					}
				break;
				case '}':
				case ']':
					if (!$in_string)
					{
						$indent_level--;
						$new_json .= "\n".str_repeat($tab, $indent_level).$char;
					}
					else
					{
						$new_json .= $char;
					}
				break;
				case ',':
					if (!$in_string)
					{
						$new_json .= ",\n".str_repeat($tab, $indent_level);
					}
					else
					{
						$new_json .= $char;
					}
				break;
				case ':':
					if (!$in_string)
					{
						$new_json .= ': ';
					}
					else
					{
						$new_json .= $char;
					}
				break;
				case '"':
					$in_string = !$in_string;
				default:
					$new_json .= $char;
					break;
			}
		}
		
		return $new_json;
	}
}

