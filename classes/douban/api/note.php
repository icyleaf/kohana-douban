<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Note API
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Note extends Douban_Core {
	
	/**
	 * Get note
	 *
	 * @param int $note_id 
	 * @return mixed
	 */
	public function get($note_id)
	{
		$url = Douban_Core::NOTE_URL.$note_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = $this->_format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get notes by people
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_by_people($people_id, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/notes';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_notes($url, $post_data);
	}
	
	/**
	 * Create a new note
	 *
	 *		$data = array
	 *		(
	 *			'title'		=> 'My secrets',
	 *			'content'	=> 'note content',
	 *			'privacy'	=> 'private',
	 *			'reply'		=> 'no',
	 *		);
	 *
	 * @param araray $data
	 * @return mixed
	 */
	public function create($data = array())
	{
		$url = substr(Douban_Core::NOTE_URL, 0, strlen(Douban_Core::NOTE_URL) - 1) . 's';
		$parameters = array
		(
			'title'		=> '',
			'content'	=> '',
			'privacy'	=> 'public',	// private or public
			'reply'		=> 'yes',		// yes or no
		);
		$data = array_merge($parameters, $data);
		
		if ($data['privazy'] != 'public')
		{
			$data['privazy'] = 'private';
		}
		if ($data['reply'] != 'yes')
		{
			$data['reply'] = 'no';
		}
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/">'.
			'<title>'.$data['title'].'</title>'.
			'<content>'.$data['content'].'</content>'.
			'<db:attribute name="privacy">'.$data['privazy'].'</db:attribute>'.
			'<db:attribute name="can_reply">'.$data['reply'].'</db:attribute>'.
			'</entry>';
		
		$result = $this->_client->post($url, $post_data, $header);
		
		if ($this->format)
		{
			if ($result->status() == 201)
			{
			 	$result = TRUE;
			} 
			else
			{
				$this->_errors = $result;
				
				$result = FALSE;
			}
		}
		
		return $result;
	}
	
	/**
	 * Update a note
	 *
	 * @param int $note_id 
	 * @param array $data 
	 * @return mixed
	 */
	public function update($note_id, $data = array())
	{
		$url = Douban_Core::NOTE_URL.$note_id;
		$parameters = array
		(
			'title'		=> '',
			'content'	=> '',
			'privazy'	=> 'public',
			'reply'		=> 'yes',
			
		);
		$data = array_merge($parameters, $data);
		
		if ($data['privazy'] != 'public')
		{
			$data['privazy'] = 'private';
		}
		if ($data['reply'] != 'yes')
		{
			$data['reply'] = 'no';
		}

		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/">'.
			'<title>'.$data['title'].'</title>'.
			'<content>'.$data['content'].'</content>'.
			'<db:attribute name="privacy">'.$data['privazy'].'</db:attribute>'.
			'<db:attribute name="can_reply">'.$data['reply'].'</db:attribute>'.
			'</entry>';
		$result = $this->_client->put($url, $post_data, $header);

		if ($this->format)
		{
			if ($result->status() == 202)
			{
			 	$result = TRUE;
			} 
			else
			{
				$this->_errors = $result;
				
				$result = FALSE;
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete a note
	 *
	 * @param int $note_id 
	 * @return mixed
	 */
	public function delete($note_id)
	{
		$url = Douban_Core::NOTE_URL.$note_id;
		$result = $this->_client->delete($url);
		if ($this->format)
		{
			if ($result->status() == 200)
			{
			 	$result = TRUE;
			} 
			else
			{
				$this->_errors = $result;
				
				$result = FALSE;
			}
		}
		
		return $result;
	}
	
	/**
	 * Get notes
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_notes($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$notes = $result->to_json();
			
			$result = new stdClass;
			$result->title = $notes['title']['$t'];
			// author
			$result->author = Douban_API_People::format($notes['author']);
			// links
			foreach ($notes['link'] as $link)
			{
				$result->link[$link['@rel']] = $link['@href'];
			}
			// search
			$result->index = $notes['openSearch:startIndex']['$t'];
			$result->max = $notes['openSearch:itemsPerPage']['$t'];
			if ($notes['entry'] > 0)
			{
				// notes
				foreach ($notes['entry'] as $note)
				{
					$result->entry[] = $this->_format($note);
				}
			}
			else 
			{
				$result->entry = array();
			}
		}
		
		return $result;
	}
	
	/**
	 * Format note
	 *
	 * @param array $note 
	 * @return mixed
	 */
	private function _format($note)
	{
		$result = new stdClass;
		// id
		$result->id = substr($note['id']['$t'], strlen(Douban_Core::NOTE_URL));
		// title
		$result->title = $note['title']['$t'];
		// authors
		if (isset($note['author']))
		{
			$result->author = Douban_API_People::format($note['author']);
		}
		// attribute
		foreach ($note['db:attribute'] as $att)
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}
		// link
		foreach ($note['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// summary
		$result->summary = $note['summary']['$t'];
		// content
		$result->content = $note['content']['$t'];
		// published
		$result->published = strtotime($note['published']['$t']);
		// updated
		$result->updated = strtotime($note['updated']['$t']);
		
		return $result;
	}
	
}

