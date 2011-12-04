<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Doumail extends Controller_Douban_Console {
	
	private $doumail = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

//		if ( ! $_POST)
//		{
//            die('必须采用 POST 方法请求');
//		}

		$this->doumail = $this->douban->doumail();
		$this->doumail->alt = Arr::get($_POST, 'alt', 'json');
		$this->doumail->format = FALSE;
	}

	public function action_get()
	{
        $id = $this->request->param('id');
		
		$this->result = $this->doumail->get($id);
	}
	
	public function action_getInbox()
	{
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->doumail->get_inbox($index, $max);
	}
	
	public function action_getUnread()
	{
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->doumail->get_unread($index, $max);
	}

	public function action_getOutbox()
	{
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->doumail->get_outbox($index, $max);
	}
	
	public function action_send()
	{
		$this->result = $this->doumail->send($_POST);
	}
	
	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->doumail->alt);
		}
	}

}

	