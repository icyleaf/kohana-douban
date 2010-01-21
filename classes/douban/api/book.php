<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Book API
 *
 * == LINKS ==
 * self 		(http://api.douban.com/book/subject/{id})
 * alternate	(http://www.douban.com/subject/{id})
 * image		(http://t.douban.com/spic/{id}.jpg)
 * collection	(http://api.douban.com/collection/{id})
 *
 * @package		douban
 * @author		icyleaf
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009 icyleaf <icyleaf.cn@gmail.com>
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Book extends Douban_Core {
	
	/**
	 * Get book information
	 *
	 * @param int $book_id 
	 * @return mixed
	 */
	public function get($book_id)
	{
		$url = Douban_Core::BOOK_URL.$book_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_Book::format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get book access isbn number
	 *
	 * @param int $number 
	 * @return mixed
	 */
	public function isbn($number)
	{
		$url = Douban_Core::BOOK_URL.'isbn/'.$number;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_Book::format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get tags form a book
	 *
	 * @param int $book_id 
	 * @return mixed
	 */
	public function tags($book_id)
	{
		$url = Douban_Core::BOOK_URL.$book_id.'/tags';
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$book = $result->to_json();
			
			$result = new stdClass;
			$result->title = $book['title']['$t'];
			// search
			$result->index = $book['opensearch:startIndex']['$t'];
			$result->max = $book['opensearch:itemsPerPage']['$t'];
			$result->total = $book['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// tags
				foreach ($book['entry'] as $tag)
				{
					$result->entry[] = array
					(
						'title'	=> $tag['title']['$t'],
						'count'	=> $tag['db:count']['$t'],
						'url'	=> $tag['id']['$t'],
					);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Search book result
	 *
	 * @param string $query 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function search($query, $index = 1, $max = 10)
	{
		$url = substr(Douban_Core::BOOK_URL, 0, strlen(Douban_Core::BOOK_URL) - 1).'s';
		$post_data = array
		(
			'q' 		 	=> $query,
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt
		);

		return $this->_get_books($url, $post_data);
	}

	/**
	 * Format book
	 *
	 * @param array $book 
	 * @return object
	 */
	public static function format($book)
	{
		$result = new stdClass;
		// id
		$result->id = substr($book['id']['$t'], strlen(Douban_Core::BOOK_URL));
		// title
		$result->title = $book['title']['$t'];
		// category
		$result->category = substr($book['category']['@term'], strlen(Douban_Core::CATEGORY_URL));
		// authors
		if (isset($book['author']))
		{
			foreach ($book['author'] as $author)
			{
				$result->author[] = $author['name']['$t'];
			}
		}
		// attribute
		foreach ($book['db:attribute'] as $att )
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}
		// link
		foreach ($book['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// Set the default book image
		if ( ! isset($result->link['image']))
		{
			$result->link['image'] = Douban_Core::DEFAULT_BOOK_IMAGE_URL;
		}
		// rating
		if (isset($book['gd:rating']))
		{
			foreach ($book['gd:rating'] as $key => $value)
			{
				$result->rating[substr($key, 1)] = $value;
			}
		}
		// tags
		if (isset($book['db:tag']))
		{
			foreach ($book['db:tag'] as $tag)
			{
				$result->tags[$tag['@name']] = $tag['@count'];
			}
		}
		// cummary
		$result->summary = isset($book['summary']['$t']) ? $book['summary']['$t'] : '';
		
		return $result;
	}
		
	/**
	 * Get books
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_books($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$books = $result->to_json();
			
			$result = new stdClass;
			$result->title = $books['title']['$t'];
			// search
			$result->index = $books['opensearch:startIndex']['$t'];
			$result->max = $books['opensearch:itemsPerPage']['$t'];
			$result->total = $books['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// books
				foreach ($books['entry'] as $book)
				{
					$result->entry[] = Douban_API_Book::format($book);
				}
			}
		}
		
		return $result;
	}
	
}

