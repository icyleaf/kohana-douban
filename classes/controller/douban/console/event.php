<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Event extends Controller_Douban_Console {

	private $event = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
			die('必须采用 POST 方法请求');
		}

		$this->event = $this->douban->event();
		$this->event->alt = Arr::get($_POST, 'alt', 'json');
		$this->event->format = FALSE;
	}

	public function action_get()
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->event->get($id);
	}
	
	public function action_getByPeople()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$this->result = $this->event->get_by_people($id, $index, $max);
	}
	
	public function action_getByLocation()
	{
        $location = Arr::get($_POST, 'location', 'beijing');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
        $type = 'all';
		
		$this->result = $this->event->get_by_location($location, $index, $max, $type);
	}
	
	public function action_getParticipants()
	{
        $id = Arr::get($_POST, 'id');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		
		$this->result = $this->event->get_participants($id, $index, $max);
	}
	
	public function action_getWishers()
	{
        $id = Arr::get($_POST, 'id');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		
		$this->result = $this->event->get_wishers($id, $index, $max);
	}
	
	public function action_participate()
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->event->participate($id);
	}
	
	public function action_wisher()
	{
        $id = Arr::get($_POST, 'id');

		$this->result = $this->event->wisher($id);
	}
	
	public function action_search()
	{
        $query = Arr::get($_POST, 'query');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		
		$this->result = $this->event->search($query, 'all', $index, $max);
	}
	
	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->event->alt);
		}
	}
}

