<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Broadcast extends Controller_Douban_Console {
	
	private $broadcast = NULL;
	private $result = NULL;
	
	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;
		
		if ( ! $_POST)
		{
            die('必须采用 POST 方法请求');
		}	
			
		$this->broadcast = $this->douban->broadcast();
		$this->broadcast->alt = Arr::get($_POST, 'alt', 'json');
		$this->broadcast->format = FALSE;
	}

	public function action_get()
	{
        $id = Arr::get($_POST, 'id');
		
		$this->result = $this->broadcast->get($id);
	}
	
	public function action_getContacts()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->broadcast->get_contacts($id, $index, $max);
	}
	
	public function action_getMine()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->broadcast->get_mine($id, $index, $max);
	}
	
	public function action_post()
	{
        $message = Arr::get($_POST, 'message');
		
		$this->result = $this->broadcast->create($message);
	}
	
	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->broadcast->alt);
		}
	}
}

	