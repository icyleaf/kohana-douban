<?php
/**
 * Douban Book API
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Album extends Douban_Core {

	/**
	 * Get album
	 * @param int $album_id
	 * @return object
	 */
	public function get($album_id)
	{
		$url = Douban_Core::ALBUM_URL.$album_id;
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
	 * Format album
	 * @param string $album
	 * @return object
	 */
	public function _format($album)
	{
		$result = new stdClass;
		// id
		$result->id = substr($album['id']['$t'], strlen(Douban_Core::ALBUM_URL));
		// title
		$result->title = $album['title']['$t'];
		// author
		if (isset($album['author']))
		{
			$result->author = Douban_API_Event::format($album['author']);
		}
		// published
		$result->published = strtotime($album['published']['$t']);
		// updated
		$result->updated = strtotime($album['updated']['$t']);
		// link
		foreach ($album['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// attributes
		foreach ($album['db:attribute'] as $att)
		{
			$result->attribute[$att['@name']] = $att['$t'];
		}

		return $result;
	}
}
?>
