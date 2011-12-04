<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Demo_Douban extends Controller {
	
	protected $_base 		= NULL;
	protected $_config 		= NULL;
	protected $_douban  	= NULL;
	
	public function before()
	{
		$this->_config = Kohana::$config->load('douban');
		if ($this->_config->api_key AND $this->_config->api_secret)
		{
			$this->_douban = Douban::instance();
		}
		else
		{
			throw new Kohana_Exception('豆瓣  API Key 或 Secrect 是空的!');
		}
		
		// base url
		$this->_base = $this->request->uri();
	}
	
	/**
	 * Douban Entry
	 *
	 * @link http://www.douban.com/service/apidoc/
	 */
	public function action_index()
	{
		// get methods
		$methods = new ArrayIterator(get_class_methods($this));
		// methods to ignore
		$ignore = array
		(
			'__construct',
			'__call',
			'action_index',
			'action_check',
			'before',
			'after'
		);
		
		$apis = array(
			'people', 'book', 'movie', 'music', 'broadcast',
			'doumail', 'collection', 'review', 'recommendation',
			'album'
			);
		
		$output = '<h1>豆瓣 API 演示用例</h1><p>';
		if ($people = $this->_douban->get_user())
		{
			$output .= '你好，'.$people->name.'。你已经通过 OAuth 验证，你可以尝试下面操作：';
			$ignore[] = 'action_verity_oauth';
		}
		else
		{
			$output .= '你好，请在执行下面操作前通过豆瓣 OAuth 的'.HTML::anchor('demo_douban/verity_oauth', '验证');
		}
		$output .= '</p><hr /><ol>';
		while ($methods->valid())
		{
			$action = $methods->current();
			if ( ! in_array($action, $ignore))
			{
				$action = str_replace('action_', '', $action);
				$output .= '<li>'.HTML::anchor($this->_base.'/'.$action, $action).'</li>';
			}
			$methods->next();
		}
		$output .= '</ol>';
		$output .= '<hr /><h2>其他API</h2><ol>';
		foreach ($apis as $api)
		{
			$output .= '<li>'.HTML::anchor('demo_douban_'.$api, $api).'</li>';
		}
		$output .= '</ol>';
		// render
        $this->response->body($output);
	}
	
	/**
	 * Douban OAuth
	 */
	public function action_verity_oauth()
	{
		$callback_url = url::site('demo_douban/check');
		if ($auth_url = $this->_douban->verify($callback_url))
		{
			$this->request->redirect($auth_url);
		}
		else
		{
			echo Debug::dump($this->_douban->errors());
		}
	}
	
	/**
	 * Check OAuth Token
	 */
	public function action_check()
	{
		if ($_GET)
		{
			$result = $this->_douban->login();
			
			if ($result)
			{
				$this->request->redirect('demo_douban');
			}
		}
	}
	
	public function action_my_profile()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->people()->get('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	
	public function action_my_friends()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->people()->get_friends('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_contacts()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->people()->get_contacts('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_broadcasts()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->broadcast()->get_mine('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_notes()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->note()->get_by_people('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_collections()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->collection()->get_by_people('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_recommendations()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->recommendation()->get_by_people('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_reviews()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_douban->review()->get_by_people('me'));
		}
		else
		{
			echo HTML::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}		
	
	public function action_logged_in()
	{
		$status = 'Status: ';
		if ($this->_douban->logged_in())
		{
			$status .= '已经登录';
		}
		else
		{
			$status .= '没有登录';
		}
		
		$this->request->response = $status;
	}
	
	public function action_loggout()
	{
		$this->_douban->logout();
		
		$this->request->response = '成功退出';
	}

}

