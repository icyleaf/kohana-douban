<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Demo_Douban_People extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	
	public function before()
	{
		$this->_config = Kohana::config('douban');
		if ($this->_config->api_key AND $this->_config->api_secret)
		{
			$this->_douban = Douban::instance();
		}
		else
		{
			throw new Kohana_Exception('豆瓣  API Key 或 Secrect 是空的!');
		}
		
		// base url
		$this->_base = $this->request->uri;
	}
	
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
			'before',
			'after'
		);
		
		$output = '<h1>Douban People Demo</h1><p>';
		if ($people = $this->_douban->get_user())
		{
			$output .= '你好，'.$people->name.'。你已经通过 OAuth 验证，你可以尝试下面操作：';
		}
		else
		{
			$output .= '你好，请在执行下面操作前通过豆瓣 OAuth 的'.html('demo_douban/verity_oauth', '验证');
		}
		$output .= '</p><ol>';
		
		while ($methods->valid())
		{
			$action = $methods->current();
			if ( ! in_array($action, $ignore))
			{
				$action = str_replace('action_', '', $action);
				$output .= '<li>'.html::anchor($this->_base.'/'.$action, $action).'</li>';
			}
			$methods->next();
		}
		$output .= '</ol>';
		// render
		$this->request->response = $output;
	}
	
	public function action_my_profile()
	{
		if ($this->_douban->logged_in())
		{
			echo Kohana::debug($this->_douban->people()->get('me'));
		}
		else
		{
			echo html::anchor('douban_demo/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_friends()
	{
		if ($this->_douban->logged_in())
		{
			echo Kohana::debug($this->_douban->people()->get_friends('me'));
		}
		else
		{
			echo html::anchor('douban_demo/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_my_contacts()
	{
		if ($this->_douban->logged_in())
		{
			echo Kohana::debug($this->_douban->people()->get_contacts('me'));
		}
		else
		{
			echo html::anchor('douban_demo/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_people_profile()
	{
		if ( ! $_GET)
		{
			echo '通过 people id 获取用户资料： (比如，"icyleaf" or "1353793")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Kohana::debug($this->_douban->people()->get($id));
		}
	}
	
	public function action_search_people()
	{
		if ( ! $_GET)
		{
			echo '搜索用户： (比如，"icyleaf")<br/><br/>';
			echo '<form method="get"><input type="text" name="query"><input type="submit"/></form>';
		}
		else
		{
			$query = trim($_GET['query']);
			echo Kohana::debug($this->_douban->people()->search($query));
		}
	}
	
}

