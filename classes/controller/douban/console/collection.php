<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Douban_Console_Collection extends Controller_Douban_Console {
	
	private $collection = NULL;
	private $result = NULL;

	public function before()
	{
		parent::before();

		$this->auto_render = FALSE;

		if ( ! $_POST)
		{
			die('必须采用 POST 方法请求');
		}

		$this->collection = $this->douban->collection();
		$this->collection->alt = Arr::get($_POST, 'alt', 'json');
		$this->collection->format = FALSE;
	}

	public function action_getByPeople()
	{
        $id = $this->request->param('id', '@me');
        $index = $this->request->param('index', 1);
        $max = $this->request->param('max', 10);

		$this->result = $this->collection->get_by_people($id, $index, $max);
	}

	public function action_getBookByPeople()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);

		$data = array
		(
			'cat' 		 	=> 'book',
		);
		$this->result = $this->collection->get_by_people($id, $index, $max, $data);
	}

	public function action_getMusicByPeople()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		$data = array
		(
			'cat' 		 	=> 'music',
		);
		$this->result = $this->collection->get_by_people($id, $index, $max, $data);
	}

	public function action_getMovieByPeople()
	{
        $id = Arr::get($_POST, 'id', '@me');
        $index = Arr::get($_POST, 'index', 1);
        $max = Arr::get($_POST, 'max', 10);
		$data = array
		(
			'cat' 		 	=> 'movie',
		);
		$this->result = $this->collection->get_by_people($id, $index, $max, $data);
	}

	public function after()
	{
		if ( ! empty($this->result))
		{
			parent::render($this->result, $this->collection->alt);
		}
	}

}

