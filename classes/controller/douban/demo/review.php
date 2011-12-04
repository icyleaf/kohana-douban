<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Demo_Review extends Controller {
	
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
		// Douban Review class
		$this->_review = $this->_douban->review();
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
		
		$output = '<h1>Douban review Demo</h1><p>';
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
	
	public function action_review_information()
	{
		if ( ! $_GET)
		{
			echo '通过 review id 获得评论信息: (比如， "1579992")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_review->get($id));
		}
	}
	
	public function action_get_reviews_by_people()
	{
		if ( ! $_GET)
		{
			echo '获取用户评论：(比如，"icyleaf" or "1353793")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_review->get_by_people($id));
		}
	}
		
	public function action_get_reviews_by_book()
	{
		if ( ! $_GET)
		{
			echo '通过 book id 获取某图书的评论：(比如， "1082387")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_review->get_by_book($id));
		}
	}
	
	public function action_get_reviews_by_movie()
	{
		if ( ! $_GET)
		{
			echo '通过 movie id 获取某电影的评论：(比如，"1652587")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_review->get_by_movie($id));
		}
	}
	
	public function action_get_reviews_by_music()
	{
		if ( ! $_GET)
		{
			echo '通过 music id 获取某音乐的评论：(比如，"3040149")<br/><br/>';
			echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
		}
		else
		{
			$id = trim($_GET['id']);
			echo Debug::dump($this->_review->get_by_music($id));
		}
	}
	
	public function action_get_reviews_by_ibsn()
	{
		if ( ! $_GET)
		{
			echo '通过  IBSN 获取某图书的评论：(比如，"9787532729357")<br/><br/>';
			echo '<form method="get"><input type="text" name="ibsn"><input type="submit"/></form>';
		}
		else
		{
			$ibsn = trim($_GET['ibsn']);
			echo Debug::dump($this->_review->get_by_isbn($ibsn));
		}
	}
	
	public function action_get_reviews_by_imdb()
	{
		if ( ! $_GET)
		{
			echo '通过 IMDB 获取某电影的评论：(比如，"tt0499549")<br/><br/>';
			echo '<form method="get"><input type="text" name="imdb"><input type="submit"/></form>';
		}
		else
		{
			$imdb = trim($_GET['imdb']);
			echo Debug::dump($this->_review->get_by_imdb($imdb));
		}
	}
	
	public function action_create_review()
	{
		if ($this->_douban->logged_in())
		{
			if ( ! $_POST)
			{
				echo '<h1>发布评论</h1>';
				echo '<form method="post">Subject URL (book/music/movie)<br />';
				echo '<small>比如，http://api.douban.com/movie/subject/1652587</small><br /><input type="text" name="subject_url" style="width: 300px"><br />';
				echo '标题:<br /><input type="text" name="title" style="width: 300px"><br />';
				echo '评价 (0-5):<br /><input type="text" name="rating" value="0" style="width: 300px"><br />';
				echo '评语 (大于 150 个字):<br /><textarea name="content" style="width: 300px;height:120px"></textarea><br />';
				echo '<input type="submit"/></form>';
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
						$_POST[$key] = trim($value);
					}
				}
				
				$result = $this->_review->create($_POST);
				if ($result)
				{
					echo '创建成功！';
					echo html::anchor('demo_douban_review/review_information?id='.$result, '查看');
					echo ' 或 ';
					echo html::anchor('demo_douban_review/delete_review?id='.$result, '删除');
				}
				else
				{
					echo Debug::dump($this->_review->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
	public function action_delete_review()
	{
		if ($this->_douban->logged_in())
		{
		
			if ( ! $_GET)
			{
				echo '通过 review id 删除评论信息：<br />';
				echo '<form method="get"><input type="text" name="id"><input type="submit"/></form>';
			}
			else
			{
				$id = trim($_GET['id']);
				if ($this->_review->delete($id))
				{
					echo '删除成功！';
				}
				else
				{
					echo '删除失败';
					echo Debug::dump($this->_review->errors());
				}
			}
		}
		else
		{
			echo html::anchor('demo_douban/verity_oauth', 'OAuth 验证');
		}
	}
	
}

