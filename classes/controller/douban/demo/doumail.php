<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Demo_Doumail extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	private $_doumail	= NULL;
	
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
		// Douban doumail class
		$this->_doumail = $this->_douban->doumail();
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
		
		$output = '<h1>Douban doumail Demo</h1><p>';
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
	
	public function action_doumail_information()
	{
		if ( ! $_GET)
		{
			echo '通过 doumail id 获得豆邮信息：（你可以从下面操作中查询结果获得）<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_doumail->get($id));
		}
	}
	
	public function action_list_unread_box()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_doumail->get_unread());
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_list_inbox()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_doumail->get_inbox());
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_list_outbox()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_doumail->get_outbox());
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_send_doumail()
	{
		if ($this->_douban->logged_in())
		{
			if ($_POST)
			{
				foreach ($_POST as $key => $value)
				{
					if (empty($_POST[$key]))
					{
						echo '['.$key.'] 不能为空！';
						return;
					}
				}
				
				$result = $this->_doumail->send($_POST);
				if ($result)
				{
					if (is_bool($result) AND $result)
					{
						echo '发送成功！';
						return;
					}
				}
				else
				{
					echo '发送失败:';
					echo Debug::dump($this->_doumail->errors());
					return;
				}
			}

            if (isset($result))
			    echo Debug::dump($result);

			echo '<h1>发送豆邮</h1>';
			echo '<form method="post">people id:<br />';
			echo '<input type="text" name="people_id" style="width: 300px"><br />';
			echo '标题:<br /><input type="text" name="title" style="width: 300px"><br />';
			echo '内容:<br /><input type="text" name="content" style="width: 300px"><br />';
			if (isset($result) AND is_array($result))
			{
				echo '<input type="hidden" name="captcha_token" value="'.trim($result['captcha_token']).'">';
			}
			if (isset($result) AND is_array($result))
			{
				echo '<img src="'.$result['captcha_small_url'].'" /><br />';
				echo '验证码: <input type="text" name="captcha_string"><br />';
			}
			
			echo '<input type="submit"/></form>';
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_doumail()
	{
		if ($this->_douban->logged_in())
		{
		
			if ( ! $_GET)
			{
				echo '通过 doumail id 删除豆邮信息：（你可以从下面操作中查询结果获得） <br/><br/>';
				echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
			}
			else
			{
				$id = trim($_GET['id']);
				if ($this->_doumail->delete($id))
				{
					echo '删除成功！';
				}
				else
				{
					echo '删除失败：';
					echo Debug::dump($this->_doumail->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
}

