<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Demo_Broadcast extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	private $_broadcast	= NULL;
	
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
		// Douban broadcast class
		$this->_broadcast = $this->_douban->broadcast();
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
		
		$output = '<h1>Douban broadcast Demo</h1><p>';
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
	
	public function action_broadcast_information()
	{
		if ( ! $_GET)
		{
			echo '通过 broadcast id 获得广播信息：（比如，"255649446"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_broadcast->get($id));
		}
	}
	
	public function action_list_broadcast_replies()
	{
		if ( ! $_GET)
		{
			echo '通过 broadcast id 获得广播回复：（比如，"255633574"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_broadcast->get_comments($id));
		}
	}
	
	public function action_my_broadcasts()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_broadcast->get_mine());
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_contacts_broadcasts()
	{
		if ($this->_douban->logged_in())
		{
			echo Debug::dump($this->_broadcast->get_contacts());
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_create_broadcast()
	{
		if ($this->_douban->logged_in())
		{
			if ( ! $_POST)
			{
				echo '<h1>发布一个广播</h1>';
				echo '<form method="post">消息：<br />';
				echo '<input type="text" name="message" style="width: 300px"><br />';
				echo '<input type="submit"/></form>';
			}
			else
			{
				$message = trim($_POST['message']);
				$result = $this->_broadcast->create($message);
				if ($result)
				{
					echo '创建成功！';
					echo html::anchor('demo_douban_broadcast/broadcast_information?id='.$result, '查看', array('target' => '_blank'));
					echo ' 或 ';
					echo html::anchor('demo_douban_broadcast/reply_broadcast?id='.$result, '回复', array('target' => '_blank'));
					echo ' 或 ';
					echo html::anchor('demo_douban_broadcast/delete_broadcast?id='.$result, '删除', array('target' => '_blank'));
					echo '.';
				}
				else
				{
					echo Debug::dump($this->_broadcast->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_broadcast()
	{
		if ($this->_douban->logged_in())
		{
		
			if ( ! $_GET)
			{
				echo '通过 broadcast id 删除广播回复： <br />';
				echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
			}
			else
			{
				$id = trim($_GET['id']);
				if ($this->_broadcast->delete($id))
				{
					echo '删除成功！';
				}
				else
				{
					echo '删除失败：';
					echo Debug::dump($this->_broadcast->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_reply_broadcast()
	{
		if ($this->_douban->logged_in())
		{
			$id = isset($_GET['id'])?$_GET['id']:'';
			if ( ! $_POST)
			{
				echo '<h1>回应广播</h1>';
				echo '<form method="post">broadcast id:<br />';
				echo '<input type="text" name="miniblog_id" value="'.$id.'" style="width: 300px"><br />';
				echo '消息:<br /><input type="text" name="message" style="width: 300px"><br />';
				echo '<input type="submit"/></form>';
			}
			else
			{
				$miniblog_id = trim($_POST['miniblog_id']);
				$message = trim($_POST['message']);
				$result = $this->_broadcast->reply($miniblog_id, $message);
				if ($result)
				{
					echo '回应成功！';
				}
				else
				{
					echo '回应失败：';
					echo Debug::dump($this->_broadcast->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
}

