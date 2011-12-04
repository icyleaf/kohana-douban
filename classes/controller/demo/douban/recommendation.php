<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Demo_Douban_Recommendation extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	private $_recommendation	= NULL;
	
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
		// Douban recommendation class
		$this->_recommendation = $this->_douban->recommendation();
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
		
		$output = '<h1>Douban recommendation Demo</h1><p>';
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
	
	public function action_recommendation_information()
	{
		if ( ! $_GET)
		{
			echo '通过 recommendation id 获得推荐信息：(比如，"15064757")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_recommendation->get($id));
		}
	}
	
	public function action_get_recommendations_by_people()
	{
		if ( ! $_GET)
		{
			echo '获得用户的推荐信息：(比如，"icyleaf" or "1353793")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_recommendation->get_by_people($id));
		}
	}
	
	public function action_get_recommendation_comment()
	{
		if ( ! $_GET)
		{
			echo '通过 recommendation id 获得推荐的回应：(比如，"15064757")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_recommendation->get_comments($id));
		}
	}
	
	public function action_create_recommendation()
	{
		if ($this->_douban->logged_in())
		{
			if ( ! $_POST)
			{
				echo '<h1>创建推荐</h1>';
				echo '<form method="post">Subject URL (book/music/movie)<br />';
				echo '<small>比如，http://api.douban.com/movie/subject/1652587</small><br />';
				echo '<input type="text" name="subject_url" style="width: 300px"><br />';
				echo '推荐语:<br />';
				echo '<textarea name="comment" style="width: 300px;height:120px"></textarea><br />';
				echo '<input type="submit"/></form>';
			}
			else
			{
				foreach ($_POST as $key => $value)
				{
					if ($_POST[$key] == NULL OR $_POST[$key] == '')
					{
						echo '['.$key.'] is empty!';
						return;
					}
					else
					{
						$_POST[$key] = trim($value);
					}
				}
				
				$result = $this->_recommendation->create($_POST['subject_url'], $_POST['comment']);
				if ($result)
				{
					echo '成功创建！ ';
					echo html::anchor('demo_douban_recommendation/recommendation_information?id='.$result, '查看');
					echo ' 或 ';
					echo html::anchor('demo_douban_recommendation/reply_recommendation?id='.$result, '回应', array('target' => '_blank'));
					echo ' 或 ';
					echo html::anchor('demo_douban_recommendation/delete_recommendation?id='.$result, '删除');
				}
				else
				{
					echo Debug::dump($this->_recommendation->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_recommendation()
	{
		if ($this->_douban->logged_in())
		{
		
			if ( ! $_GET)
			{
				echo '通过 recommendation id 删除推荐信息：<br/><br/>';
				echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
			}
			else
			{
				$id = trim($_GET['id']);
				if ($this->_recommendation->delete_recommendation($id))
				{
					echo '删除成功！';
				}
				else
				{
					echo '删除失败：';
					echo Debug::dump($this->_recommendation->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_reply_recommendation()
	{
		if ($this->_douban->logged_in())
		{
			$id = isset($_GET['id'])?$_GET['id']:'';
			if ( ! $_POST)
			{
				echo '<h1>回应推荐</h1>';
				echo '<form method="post">recommendation id:<br />';
				echo '<input type="text" name="miniblog_id" value="'.$id.'" style="width: 300px"><br />';
				echo '内容:<br /><input type="text" name="content" style="width: 300px"><br />';
				echo '<input type="submit"/></form>';
			}
			else
			{
				$miniblog_id = trim($_POST['miniblog_id']);
				$content = trim($_POST['content']);
				$result = $this->_recommendation->reply($miniblog_id, $content);
				if ($result)
				{
					echo '创建成功！';
					echo html::anchor('delete_recommendation_comment?id='.$result, '删除');
				}
				else
				{
					echo Debug::dump($this->_recommendation->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_recommendation_comment()
	{
		if ($this->_douban->logged_in())
		{
		
			if ( ! $_GET)
			{
				echo '通过 recommendation url 删除推荐信息：(比如，http://api.douban.com/recommendation/3671284/comment/178651)<br/><br/>';
				echo '<form method="get"><input type="text" name="url"><input type="submit"/></form>';
			}
			else
			{
				$url = trim($_GET['url']);
				if ($this->_recommendation->delete_comment($url))
				{
					echo 'Delete successful!';
				}
				else
				{
					echo 'Delete failed: ';
					echo Debug::dump($this->_recommendation->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
}

