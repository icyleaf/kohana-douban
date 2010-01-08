<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Broadcast API
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Broadcast extends Douban_Core {
	
	/**
	 * Get broadcast information
	 *
	 * @param int $broadcat_id 
	 * @return mixed
	 */
	public function get($broadcat_id)
	{
		$url = Douban_Core::MINIBLOG_URL.$broadcat_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = $this->_format_broadcast($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get someone's broadcasts
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_mine($people_id = NULL, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/miniblog';
		$post_data = array
		(
			'start-index' 	=> $index,
			'max-results' 	=> $max,
			'alt' 			=> $this->alt
		);
		
		return $this->_get_broadcasts($url, $post_data);
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
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/miniblog/contacts';
		$post_data = array
		(
			'start-index' 	=> $index,
			'max-results' 	=> $max,
			'alt' 			=> $this->alt
		);
		
		return $this->_get_broadcasts($url, $post_data);
	}
	
	/**
	 * Get a miniblog's comments
	 *
	 * @param mixed $miniblog_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_comments($miniblog_id, $index = 1, $max = 10)
	{
		$url = Douban_Core::MINIBLOG_URL.urlencode($miniblog_id).'/comments';
		$post_data = array
		(
			'start-index' 	=> $index,
			'max-results' 	=> $max,
			'alt' 			=> $this->alt
		);

		return $this->_get_comments($url, $post_data);
	}
	
	/**
	 * Create a new broadcast
	 *
	 * @param string $message 
	 * @return mixed
	 */
	public function create($message)
	{
		$url = Douban_Core::MINIBLOG_URL.'saying';
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
				'<entry xmlns:ns0="http://www.w3.org/2005/Atom" '.
					'xmlns:db="http://www.douban.com/xmlns/">'.
				'<content>'.$message.'</content>'.
				'</entry>';
		$result = $this->_client->post($url, $post_data, $header);
		
		if ($this->format)
		{
			if ($result->status() == 201)
			{
			 	$broadcast = $result->to_xml();
				// get created review id and return
			 	$result = substr($broadcast->id, strlen(Douban_Core::MINIBLOG_URL));
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
	 * Post a new comment to the specified miniblog
	 *
	 * @param int $miniblog_id 
	 * @param string $message 
	 * @return mixed
	 */
	public function reply($miniblog_id, $message)
	{
		$url = Douban_Core::MINIBLOG_URL.urlencode($miniblog_id).'/comments';
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'.
			'<entry><content>'.$message.'</content></entry>';
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
	 * Delete a broadcast
	 *
	 * @param int $broadcast_id 
	 * @return mixed
	 */
	public function delete($broadcast_id)
	{
		$url = Douban_Core::MINIBLOG_URL.$broadcast_id;
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
	 * Get broadcasts
	 *
	 * @param string $url 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	private function _get_broadcasts($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$broadcasts = $result->to_json();

			$result = new stdClass;
			$result->title = $broadcasts['title']['$t'];
			// author
			if (isset($broadcasts['author']))
			{
				$result->author = Douban_API_People::format($broadcasts['author']);
			}
			// search
			$result->index = $broadcasts['opensearch:startIndex']['$t'];
			$result->max = $broadcasts['opensearch:itemsPerPage']['$t'];
			if (count($broadcasts['entry']) > 0)
			{
				// broadcasts
				foreach ($broadcasts['entry'] as $broadcast)
				{
					$result->entry[] = $this->_format_broadcast($broadcast);
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
	 * Format broadcast
	 *
	 * @param array $broadcast 
	 * @return object
	 */
	private function _format_broadcast($broadcast)
	{
		$result = new stdClass;
		// id
		$result->id = substr($broadcast['id']['$t'], strlen(Douban_Core::MINIBLOG_URL));
		// title
		$result->title = $broadcast['title']['$t'];
		// author
		if (isset($broadcast['author']))
		{
			$result->author = Douban_API_People::format($broadcast['author']);
		}
		// link
		if (isset($broadcast['link']))
		{
			foreach ($broadcast['link'] as $link)
			{
				$result->link[$link['@rel']] = $link['@href'];
			}
		}
		// category
		if (isset($broadcast['category']))
		{
			$category_url = Douban_Core::CATEGORY_URL.'miniblog.';
			$result->category = substr($broadcast['category'][0]['@term'], strlen($category_url));
		}
		// attributes
		if (isset($broadcast['db:attribute']))
		{
			foreach ($broadcast['db:attribute'] as $attribute)
			{
				$result->attribute[$attribute['@name']] = $attribute['$t'];
			}
		}
		// content
		$result->content = isset($broadcast['content']) ? $broadcast['content']['$t'] : '';
		// published date
		$result->published = strtotime($broadcast['published']['$t']);
		
		unset($category_url);
		
		return $result;
	}
	
	/**
	 * Get comments
	 *
	 * @param string $url 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	private function _get_comments($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$comments = $result->to_json();

			$result = new stdClass;
			
			$result->title = $comments['title']['$t'];
			// author
			if (isset($comments['author']))
			{
				$result->author = Douban_API_People::format($comments['author']);
			}
			// search
			$result->index = $comments['opensearch:startIndex']['$t'];
			$result->max = $comments['opensearch:itemsPerPage']['$t'];
			if (count($comments['entry']) > 0)
			{
				// comments
				foreach ($comments['entry'] as $comment)
				{
					$result->entry[] = $this->_format_comment($comment);
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
	 * Format comment
	 *
	 * @param array $comment 
	 * @return object
	 */	
	private function _format_comment($comment)
	{
		$result = new stdClass;
		// author
		if (isset($comment['author']))
		{
			$result->author = Douban_API_People::format($comment['author']);
		}
		// content
		$result->content = $comment['content']['$t'];
		// published
		$result->published = strtolower($comment['published']['$t']);
		
		return $result;
	}
	
}

