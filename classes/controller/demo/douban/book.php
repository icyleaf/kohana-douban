<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Demo_Douban_Book extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	
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
		
		$output = '<h1>Douban Book Demo</h1><p>';
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
		$this->response->body($output);
	}
	
	public function action_book_information()
	{
		if ( ! $_GET)
		{
			echo '通过 book id 获得图书信息：（比如，"1082387"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			
			echo Debug::dump($this->_douban->book()->get($id));
		}
	}
	
	public function action_book_tags()
	{
		if ( ! $_GET)
		{
			echo '通过 book id 获得图书标签：（比如，"1082387"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_douban->book()->tags($id));
		}
	}
		
	public function action_book_information_by_ibsn()
	{
		if ( ! $_GET)
		{
			echo '通过 IBSN 获得图书信息：（比如，"9787532729357"）<br />';
			echo '<form method="get"><input type="text" name="isbn"><input type="submit"/></form>';
		}
		else
		{
			$isbn = trim($_GET['isbn']);
			echo Debug::dump($this->_douban->book()->isbn($isbn));
		}
	}
		
	public function action_search_book()
	{
		if ( ! $_GET)
		{
			echo '搜索图书：（比如，"1984"）<br />';
			echo '<form method="get"><input type="text" name="query"><input type="submit"/></form>';
		}
		else
		{
			$query = trim($_GET['query']);
			echo Debug::dump($this->_douban->book()->search($query));
		}
	}
	
}

