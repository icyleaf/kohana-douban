<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Movie API
 *
 * == LINKS ==
 * self 		(http://api.douban.com/movie/subject/{id})
 * alternate	(http://www.douban.com/subject/{id})
 * image		(http://t.douban.com/spic/{id}.jpg)
 * collection	(http://api.douban.com/collection/{id})
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Movie extends Douban_Core {
	
	/**
	 * Get movie information
	 *
	 * @param int $movie_id 
	 * @return mixed
	 */
	public function get($movie_id)
	{
		$url = Douban_Core::MOVIE_URL.$movie_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_Movie::format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get movie access imdb number
	 *
	 * @param int $number 
	 * @return mixed
	 */
	public function imdb($number)
	{
		$url = Douban_Core::MOVIE_URL.'imdb/'.$number;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_Movie::format($result->to_json());
		}
		
		return $result;
	}
	
	/**
	 * Get tags form a movie
	 *
	 * @param int $movie_id 
	 * @return mixed
	 */
	public function tags($movie_id)
	{
		$url = Douban_Core::MOVIE_URL.$movie_id.'/tags';
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$movie = $result->to_json();
			
			$result = new stdClass;
			$result->title = $movie['title']['$t'];
			// search
			$result->index = $movie['openSearch:startIndex']['$t'];
			$result->max = $movie['openSearch:itemsPerPage']['$t'];
			$result->total = $movie['openSearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// tags
				foreach ($movie['entry'] as $tag)
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
	 * Search movie result
	 *
	 * @param string $query 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function search($query, $index = 1, $max = 10)
	{
		$url = substr(Douban_Core::MOVIE_URL, 0, strlen(Douban_Core::MOVIE_URL) - 1).'s';
		$post_data = array
		(
			'q' 		 	=> $query,
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt
		);

		return $this->_get_movies($url, $post_data);
	}
	
	/**
	 * Format movie
	 *
	 * @param array $movie 
	 * @return object
	 */
	public static function format($movie)
	{
		$result = new stdClass;
		// id
		$result->id = substr($movie['id']['$t'], strlen(Douban_Core::MOVIE_URL));
		// title
		$result->title = $movie['title']['$t'];
		// category
		$result->category = substr($movie['category']['@term'], strlen(Douban_Core::CATEGORY_URL));
		// authors
		if (isset($movie['author']))
		{
			foreach ($movie['author'] as $author)
			{
				$result->author[] = $author['name']['$t'];
			}
		}
		// attribute
		foreach ($movie['db:attribute'] as $att)
		{
            if (isset($result->attribute[$att['@name']]))
            {
                if (is_array($result->attribute[$att['@name']]))
                {
                    $result->attribute[$att['@name']][] = $att['$t'];
                }
                else
                {
                    $temp = $result->attribute[$att['@name']];
                    $result->attribute[$att['@name']] = array($temp, $att['$t']);
                }
            }
            else
            {
                $result->attribute[$att['@name']] = $att['$t'];
            }
		}
        
		if (isset($result->attribute['imdb']))
		{
			$imdb = $result->attribute['imdb'];
			$imdb_url = 'http://www.imdb.com/title/';
			$result->attribute['imdb'] = substr(substr($imdb, strlen($imdb_url)), 0, -1);
			unset($imdb, $imdb_url);
		}
		
		// link
		foreach ($movie['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// Set the default music image
		if ( ! isset($result->link['image']))
		{
			$result->link['image'] = Douban_Core::DEFAULT_MUSIC_IMAGE_URL;
		}
		// rating
		if (isset($movie['gd:rating']))
		{
			foreach ($movie['gd:rating'] as $key => $value)
			{
				$result->rating[substr($key, 1)] = $value;
			}
		}
		// tags
		if (isset($movie['db:tag']))
		{
			foreach ($movie['db:tag'] as $tag)
			{
				$result->tags[$tag['@name']][] = $tag['@count'];
			}
		}
		// cummary
		$result->summary = isset($movie['summary']['$t']) ? $movie['summary']['$t'] : '';
		
		return $result;
	}
	
	/**
	 * Get movies
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_movies($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$movies = $result->to_json();

			$result = new stdClass;
			$result->title = $movies['title']['$t'];
			// search
			$result->index = $movies['opensearch:startIndex']['$t'];
			$result->max = $movies['opensearch:itemsPerPage']['$t'];
			$result->total = $movies['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// musics
				foreach ($movies['entry'] as $movie)
				{
					$result->entry[] = Douban_API_Movie::format($movie);
				}
			}
		}
		
		return $result;
	}
	
}

