<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Collection API
 *
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Collection extends Douban_Core {
	
	/**
	 * Get collection
	 *
	 * @param int $collection_id 
	 * @return mixed
	 */
	public function get($collection_id)
	{
		$url = Douban_Core::COLLECTION_URL.$collection_id;
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
	 * Get collections by people
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @param mixed $data 
	 * @return mixed
	 */
	public function get_by_people($people_id, $index = 1, $max = 10, $data = NULL)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/collection';
		$post_data = array
		(
			'cat' 		 	=> '',
			'tag' 		 	=> '',
			'status'		=> '',
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt
		);
		if ( ! empty($data) AND is_array($data) AND count($data) > 0)
		{
			$post_data = array_merge($post_data, $data);
		}
		
		return $this->_get_collections($url, $post_data);
	}
	
	/**
	 * Create a new collection
	 *
	 *		$data = array
	 *		(
	 *			'subject_url'	=> 'http://api.douban.com/movie/subject/1652587',
	 *			'status'		=> 'watched',
	 *			'rating'		=> 5,
	 *			'content'		=> 'left some words about this collection',
	 *			'privacy'		=> 'public',
	 *			'tags'			=> array('avatar', '3D', '2009', 'IMAX'),
	 *		);
	 *
	 *		status:
	 *			book	wish, reading, read
	 *			music	wish, listening, listened
	 *			movie	wish, watching, watched
	 *
	 * @param array $data 
	 * @return mixed
	 */
	public function create($data = array())
	{
		$url = substr(Douban_Core::COLLECTION_URL, 0, (strlen(Douban_Core::COLLECTION_URL) - 1));
		$parameters = array
		(
			'subject_url'	=> '',
			'status'		=> '',
			'content'		=> '',
			'rating'		=> 0,			// 0-5
			'privacy'		=> 'public',	// private or public
			'tags'			=> array(),		// array
		);
		$data = array_merge($parameters, $data);
		
		if (is_array($data['tags']) AND count($data['tags']) > 0)
		{
			$tags ='';
			foreach($data['tags'] as $tag)
			{
				$tags .= '<db:tag name="'.$tag.'" />';
			}
		}
		else
		{
			$tags = '';
		}
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
			'<entry xmlns:ns0="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/">'.
			'<db:status>'.$data['status'].'</db:status>'.
			$tags.
			'<gd:rating xmlns:gd="http://schemas.google.com/g/2005" value="'.$data['rating'].'" />'.
			'<content>'.$data['content'].'</content>'.
			'<db:subject>'.
			'<id>'.$data['subject_url'].'</id>'.
			'</db:subject>'.
			'<db:attribute name="privacy">'.$data['privacy'].'</db:attribute>'.
			'</entry>';
		$result = $this->_client->post($url, $post_data, $header);

		if ($this->format)
		{
			if ($result->status() == 201)
			{
			 	$collection = $result->to_xml();
				// get created review id and return
			 	$result = substr($collection->id, strlen(Douban_Core::COLLECTION_URL));
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
	 * Delete a collection
	 *
	 * @param int $collection_id 
	 * @return mixed
	 */
	public function delete($collection_id)
	{
		$url = Douban_Core::COLLECTION_URL.$collection_id;
		$result = $this->_client->delete($url);
		
		if ($result->status() == 200)
		{
			return TRUE;
		}
		else
		{
			$this->_errors = $result;
			
			return FALSE;
		}
	}
	
	/**
	 * Get collections
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_collections($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$collections = $result->to_json();
			
			$result = new stdClass;
			$result->title = $collections['title']['$t'];
			// author
			if (isset($collections['author']))
			{
				$result->author = Douban_API_People::format($collections['author']);
			}
			// search
			$result->index = $collections['opensearch:startIndex']['$t'];
			$result->max = $collections['opensearch:itemsPerPage']['$t'];
			$result->total = $collections['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// collections
				foreach ($collections['entry'] as $collection)
				{
					$result->entry[] = $this->_format($collection);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Format collection
	 *
	 * @param array $collection 
	 * @return mixed
	 */
	private function _format($collection)
	{
		$result = new stdClass;
		// id
		$result->id = substr($collection['id']['$t'], strlen(Douban_Core::COLLECTION_URL));
		// title
		$result->title = $collection['title']['$t'];
        // author
        if (isset($collection['author']))
        {
            $result->author = Douban_API_People::format($collection['author']);
        }
		// status
		$result->status = $collection['db:status']['$t'];
		// subject
		$category = substr($collection['db:subject']['category']['@term'], strlen(Douban_Core::CATEGORY_URL));
		$result->subject = call_user_func(array('Douban_API_'.ucfirst($category), 'format'), $collection['db:subject']);
		// summary
        if (isset($collection['summary']))
        {
            $result->summary = $collection['summary']['$t'];
        }
        // rating
        if (isset($collection['gd:rating']))
        {
            foreach ($collection['gd:rating'] as $key => $value)
            {
                $result->rating[substr($key, 1)] = $value;
            }
        }
        // tags
        if (isset($collection['db:tag']))
        {
            foreach ($collection['db:tag'] as $tag)
            {
                $result->tags[] = $tag['@name'];
            }
        }
        // updated
		$result->updated = strtotime($collection['updated']['$t']);
		
		unset($category, $class);
		
		return $result;
	}
}

