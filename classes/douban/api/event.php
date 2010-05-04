<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Event API
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Event extends Douban_Core {
	
	/**
	 * Get event
	 *
	 * @param int $event_id 
	 * @return mixed
	 */
	public function get($event_id)
	{
		$url = Douban_Core::EVENT_URL.$event_id;
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
	 * Get events by people
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_by_people($people_id = NULL, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/events';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_events($url, $post_data);
	}
	
	/**
	 * Get events by location
	 *
	 * @param string $location 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_by_location($location, $index = 1, $max = 10)
	{
		$url = Douban_Core::EVENT_URL.'location/'.urlencode($location);
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_events($url, $post_data);
	}
	
	/**
	 * Get participants from a event
	 *
	 * @param int $event_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_participants($event_id, $index = 1, $max = 10)
	{
		$url = Douban_Core::EVENT_URL.$event_id.'/participants';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::get_peoples($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get wishers from a event
	 *
	 * @param int $event_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_wishers($event_id, $index = 1, $max = 10)
	{
		$url = Douban_Core::EVENT_URL.$event_id.'/wishers';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::get_peoples($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Participant a event
	 *
	 * @param int $event_id 
	 * @return mixed
	 */
	public function participant($event_id)
	{
		$url = Douban_Core::EVENT_URL.$event_id.'/participants';
		$result = $this->_client->post($url);
		
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
	 * Wisher a event
	 *
	 * @param int $event_id 
	 * @return mixed
	 */
	public function wisher($event_id)
	{
		$url = Douban_Core::EVENT_URL.$event_id.'/wishers';
		$result = $this->_client->post($url);

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
	 * Cancel a event status
	 *
	 * @param int $event_id 
	 * @param string $status 
	 * @return mixed
	 */
	public function cancel($event_id, $status)
	{
		if ($status == 'wishers' OR $status == 'participants')
		{
			$url = Douban_Core::EVENT_URL.$event_id.'/'.$status;
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
		}
		else
		{
			$result = NULL;
		}
		
		return $result;
	}
	
	/**
	 * Search events
	 *
	 * @param string $query 
	 * @param string $location 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function search($query, $location = 'all', $index = 1, $max = 10)
	{
		$url = substr(Douban_Core::EVENT_URL, 0, strlen(Douban_Core::EVENT_URL) - 1) . 's';
		$post_data = array
		(
			'q' 			=> $query,
			'location' 		=> $location,
			'start-index'	=> $index,
			'max-results' 	=> $max,
			'alt' 			=> $this->alt
		);

		return $result = $this->_get_events($url, $post_data);
	}
	
	/**
	 * Create a new event (User must be setted location city)
	 *
	 *		$data = array
	 *		(
	 *			'title'			=> 'Event title',
	 *			'category'		=> 'exhibition',
	 *			'content'		=> 'event description',
	 *			'invite_only'	=> 'no',
	 *			'can_invite'	=> 'yes',
	 *			'start_time'	=> '2008-09-30T19:00:00+08:00',
	 *			'end_time'		=> '2008-10-30T19:00:00+08:00',
	 *			'address'		=> 'Houhai beijing'
	 *		);
	 *
	 *		category:
	 *			music, film, exhibition, drama, salon, 
	 *			party, sports, travel, commonweal, others
	 *
	 * @param array $data 
	 * @return mixed
	 */
	public function create($data = array())
	{
		$url = substr(Douban_Core::EVENT_URL, 0, strlen(Douban_Core::EVENT_URL) - 1) . 's';
		$parameters = array
		(
			'title'			=> '',
			'category'		=> '',
			'content'		=> '',
			'invite_only'	=> 'no',
			'can_invite'	=> 'yes',
			'start_time'	=> '',
			'end_time'		=> '',
			'address'		=> ''
		);
		$categories = array
		(
			'music', 'film', 'exhibition', 'drama', 'salon', 
			'party', 'sports', 'travel', 'commonweal'
		);
		$data = array_merge($parameters, $data);
		if ( ! in_array($data['category'], $categories))
		{
			$data['category'] = 'others';
		}
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/" '.
				'xmlns:gd="http://schemas.google.com/g/2005" '.
				'xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/">'.
			'<title>'.$data['title'].'</title>'.
			'<category scheme="http://www.douban.com/2007#kind" '.
				'term="http://www.douban.com/2007#event.'.$data['category'].'"/>'.
			'<content>'.$data['content'].'</content>'.
			'<db:attribute name="invite_only">'.$data['invite_only'].'</db:attribute>'.
			'<db:attribute name="can_invite">'.$data['can_invite'].'</db:attribute>'.
			'<gd:when endTime="'.$data['end_time'].'" startTime="'.$data['start_time'].'"/>'.
			'<gd:where valueString="'.$data['address'].'" />'.
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
	 * Update event
	 *
	 * @param int $event_id 
	 * @param array $data 
	 * @return mixed
	 */
	public function update($event_id, $data = array())
	{
		$parameters = array
		(
			'title'			=> '',
			'category'		=> '',
			'content'		=> '',
			'invite_only'	=> 'no',
			'can_invite'	=> 'yes',
			'start_time'	=> '',
			'end_time'		=> '',
			'address'		=> ''
		);
		$categories = array
		(
			'music', 'film', 'exhibition', 'drama', 'salon', 
			'party', 'sports', 'travel', 'commonweal', 'others'
		);
		//$data = array_merge($parameters, $data);
		if ( ! in_array($data['category'], $categories))
		{
			$data['category'] = 'others';
		}
		$url = Douban_Core::EVENT_URL.$event_id;
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/" '.
				'xmlns:gd="http://schemas.google.com/g/2005" '.
				'xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/">'.
			'<title>'.$data['title'].'</title>'.
			'<category scheme="http://www.douban.com/2007#kind" '.
				'term="http://www.douban.com/2007#event.'.$data['category'].'"/>'.
			'<content>'.$data['content'].'</content>'.
			'<db:attribute name="invite_only">'.$data['invite_only'].'</db:attribute>'.
			'<db:attribute name="can_invite">'.$data['can_invite'].'</db:attribute>'.
			'<gd:when endTime="'.$data['end_time'].'" startTime="'.$data['start_time'].'"/>'.
			'<gd:where valueString="'.$data['address'].'" />'.
			'</entry>';
		$result = $this->_client->put($url, $post_data, $header);

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
	 * Delete a event with a reason to all participants and wishers
	 *
	 * @param int $event_id 
	 * @param string $content 
	 * @return mixed
	 */
	public function delete($event_id, $content)
	{
		$url = Douban_Core::EVENT_URL.$event_id;
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
			'<entry xmlns:ns0="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/">'.
			'<content>'.$content.'</content>'.
			'</entry>';
		$result = $this->_client->delete($url, $post_data, $header);
		
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
	 * Get events
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_events($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$events = $result->to_json();
			$result = new stdClass;
			$result->title = $events['title']['$t'];
			// author
			if (isset($events['author']))
			{
				$result->author = Douban_API_People::format($events['author']);
			}
			// search
			$result->index = $events['opensearch:startIndex']['$t'];
			$result->max = $events['opensearch:itemsPerPage']['$t'];
			$result->total = $events['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// events
				foreach ($events['entry'] as $event)
				{
					$result->entry[] = $this->_format($event);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Format event
	 *
	 * @param array $event 
	 * @return object
	 */
	private function _format($event)
	{
		echo Kohana::debug($event);
		$result = new stdClass;
		// id
		$result->id = substr($event['id']['$t'], strlen(Douban_Core::EVENT_URL));
		// title
		$result->title = $event['title']['$t'];
		// category
		$category_url = Douban_Core::CATEGORY_URL.'event.';
		$result->category = substr($event['category']['@term'], strlen($category_url));
		// date
		$result->date = array
		(
			'start'	=> strtotime($event['gd:when']['@endTime']),
			'end'	=> strtotime($event['gd:when']['@startTime']),
		);
		// location
		$result->location = array
		(
			'id'	=> $event['db:location']['@id'],
			'name'	=> $event['db:location']['$t'],
		);
		// where
		$result->address = $event['gd:where']['@valueString'];
		// geo
		if (isset($event['georss:point']))
		{
			list($lat, $lon) = explode(' ', $event['georss:point']['$t']);
			$result->geo = array
			(
				'lat'	=> $lat,
				'lon'	=> $lon,
			);
		}
		// author
		if (isset($event['author']))
		{
			$result->author = Douban_API_People::format($event['author']);
		}
		// link
		foreach ($event['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// set the default image if not exist
		if ( ! isset($result->link['image']))
		{
			$result->link['image'] = Douban_Core::DEFAULT_EVENT_IMAGE_URL;
		}
		// summary
		$result->summary = $event['summary'][0]['$t'];
		// content
		$result->content = $event['content'][0]['$t'];
		// attributes
		foreach ($event['db:attribute'] as $att)
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}
		
		unset($category_url);
		
		return $result;
	}
	
}

