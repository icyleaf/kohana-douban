<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Music API
 *
 * == LINKS ==
 * self 		(http://api.douban.com/music/subject/{id})
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
class Douban_API_Music extends Douban_Core {
	
	/**
	 * Get music information
	 *
	 * @param int $music_id 
	 * @return mixed
	 */
	public function get($music_id)
	{
		$url = Douban_Core::MUSIC_URL.$music_id;
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$result = Douban_API_MUSIC::format($result->to_json());
		}
		
		return $result;
	}
	
		
	/**
	 * Get tags form a music
	 *
	 * @param int $music_id 
	 * @return mixed
	 */
	public function tags($music_id)
	{
		$url = Douban_Core::MUSIC_URL.$music_id.'/tags';
		$post_data = array
		(
			'alt' => $this->alt
		);
		$result = $this->_client->get($url, $post_data);
		
		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$music = $result->to_json();
			
			$result = new stdClass;
			$result->title = $music['title']['$t'];
			// search
			$result->index = $music['openSearch:startIndex']['$t'];
			$result->max = $music['openSearch:itemsPerPage']['$t'];
			$result->total = $music['openSearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// tags
				foreach ($music['entry'] as $tag)
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
	 * Search music result
	 *
	 * @param string $query 
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function search($query, $index = 1, $max = 10)
	{
		$url = substr(Douban_Core::MUSIC_URL, 0, strlen(Douban_Core::MUSIC_URL) - 1).'s';
		$post_data = array
		(
			'q' 		 	=> $query,
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt' 			=> $this->alt
		);

		return $this->_get_musics($url, $post_data);
	}

	/**
	 * Format music
	 *
	 * @param array $music 
	 * @return object
	 */
	public static function format($music)
	{
		$result = new stdClass;
		// id
		$result->id = substr($music['id']['$t'], strlen(Douban_Core::MUSIC_URL));
		// title
		$result->title = $music['title']['$t'];
		// category
		$result->category = substr($music['category']['@term'], strlen(Douban_Core::CATEGORY_URL));
		// authors
		if (isset($music['author']))
		{
			foreach ($music['author'] as $author)
			{
				$result->author[] = $author['name']['$t'];
			}
		}
		// attribute
        foreach ($music['db:attribute'] as $att)
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}
		// link
		foreach ($music['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// Set the default music image
		if ( ! isset($result->link['image']))
		{
			$result->link['image'] = Douban_Core::DEFAULT_MUSIC_IMAGE_URL;
		}
		// rating
		if (isset($music['gd:rating']))
		{
			foreach ($music['gd:rating'] as $key => $value)
			{
				$result->rating[substr($key, 1)] = $value;
			}
		}
		// tags
		if (isset($music['db:tag']))
		{
			foreach ($music['db:tag'] as $tag)
			{
				$result->tags[$tag['@name']] = $tag['@count'];
			}
		}
		// cummary
		$result->summary = isset($music['summary']['$t']) ? $music['summary']['$t'] : '';
		
		return $result;
	}
		
	/**
	 * Get musics
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_musics($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$musics = $result->to_json();
			
			$result = new stdClass;
			$result->title = $musics['title']['$t'];
			// search
			$result->index = $musics['opensearch:startIndex']['$t'];
			$result->max = $musics['opensearch:itemsPerPage']['$t'];
			$result->total = $musics['opensearch:totalResults']['$t'];
			if ($result->total > 0)
			{
				// musics
				foreach ($musics['entry'] as $music)
				{
					$result->entry[] = Douban_API_MUSIC::format($music);
				}
			}
		}
		
		return $result;
	}
	
}

