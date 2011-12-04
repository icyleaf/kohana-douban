<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Music extends Controller_Douban_Console {

	private $music = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
            die('必须采用 POST 方法请求');
		}

		$this->music = $this->douban->music();
		$this->music->alt = Arr::get($_POST, 'alt', 'json');
		$this->music->format = FALSE;
	}

	public function action_get()
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->music->get($id);
	}

	public function action_search()
	{
        $query = Arr::get($_POST, 'query');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->music->search($query, $index, $max);
	}

	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->music->alt);
		}
	}
}
