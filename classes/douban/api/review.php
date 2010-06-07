<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Review API
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Review extends Douban_Core {

	/**
	 * Get review
	 *
	 * @param int $review_id 
	 * @return mixed
	 */
	public function get($review_id)
	{
		$url = Douban_Core::REVIEW_URL.$review_id;
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
	 * Get reviews by people
	 *
	 * @param mixed $people_id 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_by_people($people_id, $index = 1, $max = 10)
	{
		$people_id = (empty($people_id) OR $people_id == 'me') ? '@me' : $people_id;
		$url = Douban_Core::PEOPLE_URL.urlencode($people_id).'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}
	
	/**
	 * Get book's reviews
	 *
	 * @param int $book_id 
	 * @param int $index 
	 * @param int $max 
	 * @param string $order_by 
	 * @return mixed
	 */
	public function get_by_book($book_id, $index = 1, $max = 10, $order_by = 'time')
	{
		$url = Douban_Core::BOOK_URL.$book_id.'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'orderby'		=> ($order_by == 'time') ? 'time' : 'score',
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}
	
	/**
	 * Get book's reviews by isbn
	 *
	 * @param int $isbn 
	 * @return mixed
	 */
	public function get_by_isbn($isbn, $index = 1, $max = 10, $order_by = 'time')
	{
		$url = Douban_Core::BOOK_URL.'isbn/'.$isbn.'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'orderby'		=> ($order_by == 'time') ? 'time' : 'score',
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}
	
	/**
	 * Get moive's reviews
	 *
	 * @param int $movie_id 
	 * @param int $index 
	 * @param int $max 
	 * @param string $order_by 
	 * @return mixed
	 */
	public function get_by_movie($movie_id, $index = 1, $max = 10, $order_by = 'time')
	{
		$url = Douban_Core::MOVIE_URL.$movie_id.'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'orderby'		=> ($order_by == 'time') ? 'time' : 'score',
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}
	
	/**
	 * Get moive's reviews by imdb
	 *
	 * @param int $imdb 
	 * @return mixed
	 */
	public function get_by_imdb($imdb, $index = 1, $max = 10, $order_by = 'time')
	{
		$url = Douban_Core::MOVIE_URL.'imdb/'.$imdb.'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'orderby'		=> ($order_by == 'time') ? 'time' : 'score',
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}

	/**
	 * Get music's reviews
	 *
	 * @param int $music_id 
	 * @param int $index 
	 * @param int $max 
	 * @param string $order_by 
	 * @return mixed
	 */
	public function get_by_music($music_id, $index = 1, $max = 10, $order_by = 'time')
	{
		$url = Douban_Core::MUSIC_URL.$music_id.'/reviews';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'orderby'		=> ($order_by == 'time') ? 'time' : 'score',
			'alt'			=> $this->alt
		);
		
		return $this->_get_reviews($url, $post_data);
	}
	
	/**
	 * Create a new review
	 *
	 *		$data = array
	 *		(
	 *			'subject_url'	=> 'http://api.douban.com/movie/subject/1652587',
	 *			'title'			=> 'Review title',
	 *			'content'		=> 'Review content',
	 *			'rating'		=> 4,
	 *		);
	 * 
	 * @param array $data
	 * @return mixed
	 */
	public function create($data = array())
	{
		$url = substr(Douban_Core::REVIEW_URL, 0, strlen(Douban_Core::REVIEW_URL) - 1) . 's';
		$parameters = array
		(
			'subject_url'	=> '',
			'title'			=> '',
			'content'		=> '',
			'rating'		=> 0,
		);
		$data = array_merge($parameters, $data);
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns:ns0="http://www.w3.org/2005/Atom">'.
			'<db:subject xmlns:db="http://www.douban.com/xmlns/">'.
				'<id>'.$data['subject_url'].'</id>'.
			'</db:subject>'.
			'<title>'.$data['title'].'</title>'.
			'<content>'.$data['content'].'</content>'.
			'<gd:rating xmlns:gd="http://schemas.google.com/g/2005" value="'.$data['rating'].'" ></gd:rating>'.
			'</entry>';
		$result = $this->_client->post($url, $post_data, $header);
		
		if ($this->format)
		{
			if ($result->status() == 201)
			{
				$review = $result->to_xml();
				// get created review id and return
			 	$result = substr($review->id, strlen(Douban_Core::REVIEW_URL));
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
	 * Update a review
	 *
	 * @param int $review_id 
	 * @param array $data 
	 * @return mixed
	 */
	public function update($review_id, $data = array())
	{
		$url = Douban_Core::REVIEW_URL.$review_id;
		$parameters = array
		(
			'title'			=> '',
			'content'		=> '',
			'rating'		=> '',
		);
		$data = array_merge($parameters, $data);
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns:ns0="http://www.w3.org/2005/Atom">'.
			'<db:subject xmlns:db="http://www.douban.com/xmlns/">'.
				'<id>'.$data['subject_url'].'</id>'.
			'</db:subject>'.
			'<title>'.$data['title'].'</title>'.
			'<content>'.$data['content'].'</content>'.
			'<gd:rating xmlns:gd="http://schemas.google.com/g/2005" value="'.$data['rating'].'" ></gd:rating>'.
			'<db:entity></db:entity>'.
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
	 * Delete review
	 *
	 * @param int $review_id 
	 * @return mixed
	 */
	public function delete($review_id)
	{
		$url = Douban_Core::REVIEW_URL.$review_id;
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
	 * Get reviews
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_reviews($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$reviews = $result->to_json();
			
			$result = new stdClass;
			$result->title = $reviews['title']['$t'];
			// author
			if ( isset($reviews['author']) )
			{
				$result->author = Douban_API_People::format($reviews['author']);
			}
			// link
			foreach ($reviews['link'] as $link)
			{
				$result->link[$link['@rel']] = $link['@href'];
			}
			// search
			$result->index = $reviews['openSearch:startIndex']['$t'];
			$result->max = $reviews['openSearch:itemsPerPage']['$t'];
			$result->total = $reviews['openSearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// reviews
				foreach ($reviews['entry'] as $review)
				{
					$result->entry[] = $this->_format($review);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Format review
	 *
	 * @param array $review 
	 * @return mixed
	 */
	private function _format($review)
	{
		$result = new stdClass;
		// id
		$result->id = substr($review['id']['$t'], strlen(Douban_Core::REVIEW_URL));
		// title
		$result->title = $review['title']['$t'];
		// authors
		if (isset($review['author']))
		{
			$result->author = Douban_API_People::format($review['author']);
		}
		// subject
		if (isset($review['db:subject']))
		{
			$category = substr($review['db:subject']['category']['@term'], strlen(Douban_Core::CATEGORY_URL));
			call_user_func(array('Douban_API_'.ucfirst($category), 'format'), $review['db:subject']);
		}
		// link
		foreach ($review['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// rating
		if (isset($review['gd:rating']))
		{
			foreach ($review['gd:rating'] as $key => $value)
			{
				$result->rating[substr($key, 1)] = $value;
			}
		}
		// summary (posted by the other people)
		if (isset($review['summary']))
		{
			$result->summary = $review['summary']['$t'];
		}
		// content (posted by mine)
		if (isset($review['content']))
		{
			$result->content = $review['content']['$t'];
		}
		// published
		$result->published = strtotime($review['published']['$t']);
		// updated
		$result->updated = strtotime($review['updated']['$t']);
		
		unset($category);
		
		return $result;
	}
	
}

