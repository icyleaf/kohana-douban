<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Demo_Collection extends Controller {
	
	private $_base 		= NULL;
	private $_config 	= NULL;
	private $_douban  	= NULL;
	private $_review	= NULL;
	
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
		// Douban collection class
		$this->_collection = $this->_douban->collection();
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
		
		$output = '<h1>Douban collection Demo</h1><p>';
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
	
	public function action_collection_information()
	{
		if ( ! $_GET)
		{
			echo '通过 collection id 获得收藏信息：（比如，"197872797"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_collection->get($id));
		}
	}
	
	public function action_get_collections_by_people()
	{
		if ( ! $_GET)
		{
			echo '获得用户的收藏信息：（比如，"icyleaf" 或 "1353793"）<br />';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_collection->get_by_people($id));
		}
	}
	
	public function action_create_collection()
	{
		if ($this->_douban->logged_in())
		{
			if ( ! $_POST)
			{
				echo '<h1>创建或更新收藏</h1>';
				echo '<form method="post">Subject URL (book/music/movie)<br />';
				echo '<small>比如：http://api.douban.com/movie/subject/1652587</small><br /><input type="text" name="subject_url" style="width: 300px"><br />';
				echo '状态：<br /><input type="text" name="status" style="width: 300px"><br />';
				echo '评分 (可选, 0-5) :<br /><input type="text" name="rating" value="0" style="width: 300px"><br />';
				echo '简评 (可选):<br /><textarea name="content" style="width: 300px;height:120px"></textarea><br />';
				echo '标签 (可选，使用逗号分隔):<br /><input type="text" name="tags" style="width: 300px"><br />';
				echo '隐私 :<input type="radio" name="privacy" value="public" checked>公开<input type="radio" name="privacy" value="private">私有<br />';
				echo '<input type="submit"/></form>';
				echo '<h3>状态</h3>';
				echo '<p>book(图书)：wish(想读), readin(在读), read(读过)</p>';
				echo '<p>music(音乐)：wish(想听), listening(在听), listened(听过)</p>';
				echo '<p>movie(电影)：wish(想看), watching(在看), watched(看过)</p>';
			}
			else
			{
				foreach ($_POST as $key => $value)
				{
					if ($_POST[$key] == NULL OR $_POST[$key] == '')
					{
						echo '['.$key.'] 不能为空！';
						return;
					}
					else
					{
						if ($key == 'tags' AND ! empty($value))
						{
							$_POST[$key] = array();
							
							$tags = explode(',', $value);
							$tag_count = count($tags);
							
							if ($tag_count > 0)
							{
								for ($i = 0; $i < count($tags); $i++)
								{
									$_POST[$key][$i] = trim($tags[$i]);
								}
							}
						}
						else
						{
							$_POST[$key] = trim($value);
						}
					}
				}
				$result = $this->_collection->create($_POST);
				if ($result)
				{
					echo '创建成功！';
					echo html::anchor('demo_douban_collection/collection_information?id='.$result, '查看');
					echo ' 或 ';
					echo html::anchor('demo_douban_collection/delete_collection?id='.$result, '删除');
				}
				else
				{
					echo Debug::dump($this->_collection->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_collection()
	{
		if ($this->_douban->logged_in())
		{
			if ( ! $_GET)
			{
				echo '通过 collection id 删除收藏信息：<br />';
				echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
			}
			else
			{
				$id = trim($_GET['id']);
				if ($this->_collection->delete($id))
				{
					echo '删除成功！';
				}
				else
				{
					echo '删除失败: ';
					echo Debug::dump($this->_collection->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
}

