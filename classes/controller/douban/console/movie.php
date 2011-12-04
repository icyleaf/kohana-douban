<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Movie extends Controller_Douban_Console {

	private $movie = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
            die('必须采用 POST 方法请求');
		}

		$this->movie = $this->douban->movie();
		$this->movie->alt = Arr::get($_POST, 'alt', 'json');
		$this->movie->format = FALSE;
	}

	public function action_get($id = NULL)
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->movie->get($id);
	}

	public function action_search()
	{
        $query = Arr::get($_POST, 'query');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->movie->search($query, $index, $max);
	}

	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->movie->alt);
		}
	}
}
