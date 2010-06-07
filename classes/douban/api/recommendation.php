<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Recommendation API
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Recommendation extends Douban_Core {
	
	/**
	 * Get recommendation
	 *
	 * @param int $recommendation_id 
	 * @return mixed
	 */
	public function get($recommendation_id)
	{
		$url = Douban_Core::RECOMMENDATION_URL.$recommendation_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = $this->_format_recommendation($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get recommendations by people
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_by_people($people_id, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/recommendations';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_recommendations($url, $post_data);
	}

	/**
	 * Create a new recommendation
	 *
	 * @param int $subject_url 
	 * @param string $comment 
	 * @return mixed
	 */
	public function create($subject_url, $comment)
	{
		$url = substr(Douban_Core::RECOMMENDATION_URL, 0, strlen(Douban_Core::RECOMMENDATION_URL) - 1) . 's';
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:gd="http://schemas.google.com/g/2005" '.
				'xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/" '.
        		'xmlns:db="http://www.douban.com/xmlns/">'.
			'<title>'.rand(1, 100).'</title>'.
			'<db:attribute name="comment">'.$comment.'</db:attribute>'.
			'<link href="'.$subject_url.'" rel="related" />'.
			'</entry>';
		$result = $this->_client->post($url, $post_data, $header);

		if ($this->format)
		{
			if ($result->status() == 201)
			{
			 	$recommendation = $result->to_xml();
				// get created review id and return
			 	$result = substr($recommendation->id, strlen(Douban_Core::RECOMMENDATION_URL));
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
	 * Reply a recommendation
	 *
	 * @param int $recommendation_id 
	 * @param string $content 
	 * @return mixed
	 */
	public function reply($recommendation_id, $content)
	{
		$url = Douban_Core::RECOMMENDATION_URL.$recommendation_id.'/comments';
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry><content>'.$content.'</content></entry>';
		$result = $this->_client->post($url, $post_data, $header);

		if ($this->format)
		{
			if ($result->status() == 200)
			{
				$comment = $result->to_xml();
				// get created review id and return
			 	$result = $comment->id;
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
	 * Delete recommendation
	 *
	 * @param int $recommendation_id 
	 * @return mixed
	 */
	public function delete_recommendation($recommendation_id)
	{
		$url = Douban_Core::RECOMMENDATION_URL.$recommendation_id;
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
	 * Delete recommendation's comment
	 *
	 * @param string $comment_url 
	 * @return mixed
	 */
	public function delete_comment($comment_url)
	{
		$result = $this->_client->delete($comment_url);
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
	 * Get comments
	 *
	 * @param int $recommendation_id 
	 * @return mixed
	 */
	public function get_comments($recommendation_id)
	{
		$url = Douban_Core::RECOMMENDATION_URL.$recommendation_id.'/comments';
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$comments = $result->to_json();
			
			$result = new stdClass;
			$result->title = $comments['title']['$t'];
			// author
			$result->author = Douban_API_People::format($comments['author']);
			// search
			$result->index = $comments['openSearch:startIndex']['$t'];
			$result->max = $comments['openSearch:itemsPerPage']['$t'];
			$result->total = $comments['openSearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// recommendations
				foreach ($comments['entry'] as $comment)
				{
					$result->entry[] = $this->_format_comment($comment, $url);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Get recommendations
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_recommendations($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$recommendations = $result->to_json();
			
			$result = new stdClass;
			$result->title = $recommendations['title']['$t'];
			// author
			if ( isset($recommendations['author']) )
			{
				$result->author = Douban_API_People::format($recommendations['author']);
			}
			// search
			$result->index = $recommendations['openSearch:startIndex']['$t'];
			$result->max = $recommendations['openSearch:itemsPerPage']['$t'];
			if (count($recommendations['entry']) > 0)
			{
				// recommendations
				foreach ($recommendations['entry'] as $recommendation)
				{
					$result->entry[] = $this->_format_recommendation($recommendation);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Format recommendation
	 *
	 * @param array $recommendation 
	 * @return mixed
	 */
	private function _format_recommendation($recommendation)
	{
		$result = new stdClass;
		// id
		$result->id = substr($recommendation['id']['$t'], strlen(Douban_Core::RECOMMENDATION_URL));
		// title
		$result->title = $recommendation['title']['$t'];
		// attribute
		foreach ($recommendation['db:attribute'] as $att )
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}
		// link
		if (count($recommendation['link']) > 0)
		{
			foreach ($recommendation['link'] as $link)
			{
				$result->link[$link['@rel']] = $link['@href'];
			}
		}
		// content
		$result->content = $recommendation['content']['$t'];
		// published
		$result->published = strtotime($recommendation['published']['$t']);
		
		return $result;
	}
	
	/**
	 * Format recommendation's comment
	 *
	 * @param array $comment 
	 * @param string $url
	 * @return mixed
	 */
	private function _format_comment($comment, $url)
	{
		$result = new stdClass;
		// id
		$result->id = substr($comment['id']['$t'], strlen($url) + 1);
		// authors
		$result->author = Douban_API_People::format($comment['author']);
		// content
		$result->content = $comment['content']['$t'];
		// published
		$result->published = strtotime($comment['published']['$t']);
		
		return $result;
	}
		
}

