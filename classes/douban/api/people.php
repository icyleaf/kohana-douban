<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban People API
 *
 * == LINKS ==
 * self 		(http://api.douban.com/people/subject/{id})
 * alternate	(http://www.douban.com/people/{id})
 * icon			(http://t.douban.com/icon/{id}.jpg)
 * homepage		(people's custom url)
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_People extends Douban_Core {
	
	/**
	 * Get people information
	 *
	 * @param mixed $people_id 
	 * @return mixed
	 */
	public function get($people_id = NULL)
	{	
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id);
		$post_data = array
		(
			'alt' => $this->alt
		);
		
		$result = $this->_client->get($url, $post_data);
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get someone's friends
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_friends($people_id = NULL, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/friends';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt,
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::get_peoples($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get someone's contacts
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_contacts($people_id = NULL, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/contacts';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt,
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::get_peoples($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Search peoples
	 *
	 * @param string $query 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function search($query, $index = 1, $max = 10)
	{
		$url = substr(Douban_Core::PEOPLE_URL, 0, strlen(Douban_Core::PEOPLE_URL) - 1);
		$post_data = array
		(
			'q' 		 	=> $query,
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_People::get_peoples($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get peoples
	 *
	 * @param array $peoples 
	 * @return mixed
	 */
	public static function get_peoples($peoples)
	{
		$result = new stdClass;
		$result->title = $peoples['title']['$t'];
		// author
		if (isset($peoples['author']))
		{
			$result->author = Douban_API_People::format($peoples['author']);
		}
		// search
		$result->index = $peoples['opensearch:startIndex']['$t'];
		$result->max = $peoples['opensearch:itemsPerPage']['$t'];
		$result->total = $peoples['opensearch:totalResults']['$t'];
		if ($peoples['opensearch:totalResults']['$t'] > 0)
		{
			// peoples
			foreach ($peoples['entry'] as $people)
			{
				$result->entry[] = Douban_API_People::format($people);
			}
		}
		else
		{
			$result->entry = array();
		}
		
		return $result;
	}
	
	/**
	 * Format eople
	 *
	 * @param array $people 
	 * @return object
	 */
	public static function format($people)
	{
		$result = new stdClass;
		// id
		if (isset($people['uri']))
		{
			if (isset($people['uri'][0]))
			{
				$result->id = substr($people['uri'][0]['$t'], strlen(Douban_Core::PEOPLE_URL));
			}
			else
			{
				$result->id = substr($people['uri']['$t'], strlen(Douban_Core::PEOPLE_URL));
			}
			
		}
		elseif (isset($people['id']))
		{
			$result->id = substr($people['id']['$t'], strlen(Douban_Core::PEOPLE_URL));
		}
		// name
		if (isset($people['title']))
		{
			$result->name = $people['title']['$t'];
		}
		elseif (isset($people['name']))
		{
			$result->name = $people['name']['$t'];
		}
		// nick
		if (isset($people['db:uid']))
		{
			$result->nick = $people['db:uid']['$t'];
		}
		// location
		if (isset($people['db:location']))
		{
			$result->location = array
			(
				'id' 	=> $people['db:location']['@id'],
				'title' => $people['db:location']['$t'],
			);
		}
		// signature
		if (isset($people['db:signature']))
		{
			$result->signature = $people['db:signature']['$t'];
		}
		// links
		if (isset($people['link']))
		{
			foreach ($people['link'] as $link)
			{
				$result->link[$link['@rel']] = $link['@href'];
			}
		}
		// Set the default people avatar
		if ( ! isset($result->link['icon']))
		{
			$result->link['icon'] = Douban_Core::DEFAULT_PEOPLE_AVATAR_URL;
		}
		// content
		if (isset($people['content']))
		{
			$result->content = $people['content']['$t'];
		}
		
		return $result;
	}

}

