<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Douban Doumail API
 *
 * @package		douban
 * @author		icyleaf <icyleaf.cn@gmail.com>
 * @link 		http://icyleaf.com
 * @copyright	(c) 2009-2010 icyleaf
 * @license		http://www.apache.org/licenses/LICENSE-2.0
 */
class Douban_API_Doumail extends Douban_Core {
	
	/**
	 * get a doumail
	 *
	 * @param int $mail_id 
	 * @return mixed
	 */
	public function get($mail_id)
	{
		$url = Douban_Core::DOUMAIL_URL.$mail_id;
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
	 * get inbox 
	 *
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_inbox($index = 1, $max = 10)
	{
		$url = Douban_Core::DOUMAIL_URL.'inbox';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_doumails($url, $post_data);
	}
	
	/**
	 * get unread doumails
	 *
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_unread($index = 1, $max = 10)
	{
		$url = Douban_Core::DOUMAIL_URL.'inbox/unread';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_doumails($url, $post_data);
	}
	
	/**
	 * get outbox
	 *
	 * @param int $index 
	 * @param int $max 
	 * @return mixed
	 */
	public function get_outbox($index = 1, $max = 10)
	{
		$url = Douban_Core::DOUMAIL_URL.'outbox';
		$post_data = array
		(
			'start-index'	=> $index,
			'max-results'	=> $max,
			'alt'			=> $this->alt
		);
		
		return $this->_get_doumails($url, $post_data);
	}
	
	/**
	 * Send a new doumail
	 * 
	 *		$data = array
	 *		(
	 *			'people_id'			=> 'icyleaf',
	 *			'title'				=> 'Hello world',
	 *			'content'			=> 'The world is in your hand!',
	 *			'captcha_token'		=> '1234567890',
	 *			'captcha_string'	=> 'hello',
	 *		);
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function send($data = array())
	{
		$url = substr(Douban_Core::DOUMAIL_URL, 0, strlen(Douban_Core::DOUMAIL_URL) - 1) . 's';
		$parameters = array
		(
			'people_id'			=> '',
			'title'				=> '',
			'content'			=> '',
			'captcha_token'		=> '',
			'captcha_string'	=> '',
		);	
		$data = array_merge($parameters, $data);
		
		if ( ! empty($data['captcha_token']) AND ! empty($data['captcha_string']))
		{
			$captcha = '<db:attribute name="captcha_token">'.$data['captcha_token'].'</db:attribute>
				<db:attribute name="captcha_string">'.$data['captcha_string'].'</db:attribute>';
		}
		else
		{
			$captcha = '';
		}
		
		$header = array('Content-Type: application/atom+xml');
		$post_data = '<?xml version="1.0" encoding="UTF-8"?>'.
			'<entry xmlns="http://www.w3.org/2005/Atom" '.
				'xmlns:db="http://www.douban.com/xmlns/" '.
				'xmlns:gd="http://schemas.google.com/g/2005" '.
				'xmlns:opensearch="http://a9.com/-/spec/opensearchrss/1.0/">'.
			'<db:entity name="receiver">'.
				'<uri>http://api.douban.com/people/'.$data['people_id'].'</uri>'.
			'</db:entity>'.
			'<title>'.$data['title'].'</title>'.
			'<content>'.$data['content'].'</content>'.
			$captcha.
			'</entry>';	
		$result = $this->_client->post($url, $post_data, $header);

		if ($this->format)
		{
			if ($result->status() == 201)
			{
				$result = TRUE;
			} 
			else if ($result->status() == 403)
			{
				$array = $result->to_array();
				$array['captcha_url'] = $array['captcha_url'].'='.$array['captcha_token'];
				$array['captcha_small_url'] = $array['captcha_url'].'&size=s';
				unset($array['size']);
				$result = $array;
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
	 * Delete a doumail
	 *
	 * @param int $mail_id 
	 * @return mixed
	 */
	public function delete($mail_id)
	{
		$url = Douban_Core::DOUMAIL_URL.$mail_id;
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
	 * Get doumails
	 *
	 * @param string $url 
	 * @param array $post_data 
	 * @return mixed
	 */
	private function _get_doumails($url, $post_data = array())
	{
		$result = $this->_client->get($url, $post_data);

		if ($this->alt == 'json' AND $this->format AND $result->status() == 200)
		{
			$doumails = $result->to_json();

			$result = new stdClass;
			$result->title = $doumails['title']['$t'];
			// search
			$result->index = $doumails['openSearch:startIndex']['$t'];
			$result->max = $doumails['openSearch:itemsPerPage']['$t'];
			if (isset($doumails['openSearch:totalResults']))
			{
				$result->total = $doumails['openSearch:totalResults']['$t'];
			}
			if ($doumails['entry'] > 0)
			{
				// doumails
				foreach ($doumails['entry'] as $doumail)
				{
					$result->entry[] = $this->_format($doumail);
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
	 * Format doumail
	 *
	 * @param array $doumail 
	 * @return mixed
	 */
	private function _format($doumail)
	{
        //echo Debug::dump($doumail);
		$result = new stdClass;
		// id
		$result->id = substr($doumail['id']['$t'], strlen(Douban_Core::DOUMAIL_URL));
		// title
		$result->title = $doumail['title']['$t'];
		// published
		$result->published = strtotime($doumail['published']['$t']);
		// authors
		if (isset($doumail['author']))
		{
			$result->author = Douban_API_People::format($doumail['author']);
            $result->receiver = FALSE;
		}
        else if (isset($doumail['db:entity']))
        {
            $result->author = Douban_API_People::format($doumail['db:entity']);
            $result->receiver = TRUE;
        }

        // type
		if (isset($result->author->id))
		{
            if ( ! $result->author->id)
            {
               $result->type = 'system';
            }
            else
            {
                $result->type = 'normal';
            }
		}
		else
		{
			$result->type = 'host';
		}
		// attribute
		if (isset($doumail['db:attribute']))
		{
			foreach ($doumail['db:attribute'] as $att)
			{
                if ($att['@name'] == 'unread')
                {
                    if (strtolower($att['$t']) == 'true')
                    {
                        $result->attribute[$att['@name']] = TRUE;
                    }
                    else
                    {
                        $result->attribute[$att['@name']] = FALSE;
                    }
                }
                else
                {
                    $result->attribute[$att['@name']] = $att['$t'];
                }
				
			}
		}
		// link
		foreach ($doumail['link'] as $link)
		{
			$result->link[$link['@rel']] = $link['@href'];
		}
		// content
		if (isset($doumail['content']))
		{
			$result->content = $doumail['content']['$t'];
		}
		
		return $result;
	}
	
}

