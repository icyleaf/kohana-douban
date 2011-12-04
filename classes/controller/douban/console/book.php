<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Book extends Controller_Douban_Console {
	
	private $book = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
			die('必须采用 POST 方法请求');
		}

		$this->book = $this->douban->book();
		$this->book->alt = Arr::get($_POST, 'alt', 'json');
		$this->book->format = FALSE;
	}

	public function action_get()
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->book->get($id);
	}
	
	public function action_search()
	{
        $query = Arr::get($_POST, 'query');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		
		$this->result = $this->book->search($query, $index, $max);
	}
	
	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->book->alt);
		}
	}
}
	