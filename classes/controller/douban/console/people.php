<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_People extends Controller_Douban_Console {
	
	private $people = NULL;
	private $result = NULL;
	
	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
			die('必须采用 POST 方法请求');
		}
			
		$this->people = $this->douban->people();
		$this->people->alt = Arr::get($_POST, 'alt', 'json');
		$this->people->format = FALSE;
	}

	public function action_get()
	{
        $id = Arr::get($_POST, 'id', '@me');

		$this->result = $this->people->get($id);
	}
	
	public function action_getFriends()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->people->get_friends($id, $index, $max);
	}
	
	public function action_getContacts()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->people->get_contacts($id, $index, $max);
	}
	
	public function action_search()
	{
        $query = Arr::get($_POST, 'query');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->people->search($query, $index, $max);
	}
	
	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->people->alt);
		}
	}
}

