<?php

/**
 * Basic implementation of the Ruby on Rails ActiveResource REST client.
 * Intended to work with RoR-based REST servers, which all share similar
 * API patterns.
 *
 * Usage:
 *
 * <?php
 *
 * require_once ('ActiveResource.php');
 *
 * class Song extends ActiveResource {
 *     var $site = 'http://localhost:3000/';
 *     var $element_name = 'songs';
 * }
 *
 * // create new item
 * $song = new Song (array ('artist' => 'Joe Cocker', 'title' => 'A Little Help From My Friends'));
 * $song->save ();
 *
 * // fetch and update an item
 * $song->find (44)->set ('title', 'The River')->save ();
 *
 * // line by line
 * $song->find (44);
 * $song->title = 'The River';
 * $song->save ();
 *
 * // get all songs
 * $songs = $song->find ('all');
 *
 * // delete a song
 * $song->find (44);
 * $song->destroy ();
 *
 * // custom method
 * $songs = $song->get ('by_year', array ('year' => 1999));
 *
 * ?>
 *
 * @author John Luxford <lux@dojolearning.com>
 * @version 0.4 beta
 * @license http://opensource.org/licenses/lgpl-2.1.php
 */
class ActiveResource {
	/**
	 * The REST site address, e.g., http://user:pass@domain:port/
	 */
	public static $site = false;

	/**
	 * The remote collection, e.g., person or things
	 */
	public static $element_name = false;
	
	// Custom headers
	public static $headers = array();

	/**
	 * The data of the current object, accessed via the anonymous get/set methods.
	 */
	var $_data = array ();
	
	/**
	 * Constructor method.
	 */
	function __construct ($data = array ()) {
		$this->_data = $data;
		$cls = get_class($this);
    // This is horrible hack - I can't believe
    // php doesn't support this:
    // http://bit.ly/vmLd2
    $vars = get_class_vars($cls);
		if($vars['element_name']){
		  $this->element_name = $vars['element_name'];
		} else {
  		$this->element_name = strtolower($cls) . 's';
		}
		$this->site = $vars['site'];
	}

	/**
	 * Saves a new record or updates an existing one via:
	 *
	 * POST /collection.xml
	 * PUT  /collection/id.xml
	 */
	function save () {
		if (isset ($this->_data['id'])) {
			$this->_data = $this->_send_and_receive ($this->site . $this->element_name . '/' . $this->_data['id'] . '.xml', 'PUT', $this->element_name, $this->_data); // update
		} else {
  		$this->_data = $this->_send_and_receive ($this->site . $this->element_name . '.xml', 'POST', $this->element_name, $this->_data); // create
		}
    if($this->_data['errors']){
      return false;
    }
		return true;
	}

	/**
	 * Deletes a record via:
	 *
	 * DELETE /collection/id.xml
	 */
	function destroy () {
		return $this->_send_and_receive ($this->site . $this->element_name . '/' . $this->_data['id'] . '.xml', 'DELETE');
	}

	/**
	 * Finds a record or records via:
	 *
	 * GET /collection/id.xml
	 * GET /collection.xml
	 */
	static function find ($class, $id = false) {
	  // Nasty PHP workaround: http://bit.ly/jc1ks
	  $vars = get_class_vars($class);
		if (!$id || $id == 'all') {
		  $res = array();
		  $data = self::_send_and_receive ($vars['site'] . $vars['element_name'] . '.xml', 'GET', $vars['element_name']);
		  foreach($data as $d){
		    $res[] = new $class($d);
		  }
		} else {
		  $data = self::_send_and_receive ($vars['site'] . $vars['element_name'] . '/' . $id . '.xml', 'GET', $vars['element_name']);
		  $res = new $class($data);
		}
		return $res;
	}

	/**
	 * Gets a specified custom method on the current object via:
	 *
	 * GET /collection/id/method.xml
	 * GET /collection/id/method.xml?attr=value
	 */
	function get ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) { 
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		if (count ($options) > 0) {
			$req .= '?' . http_build_query ($options);
		}
		return self::_send_and_receive ($req, 'GET');
	}

	/**
	 * Posts to a specified custom method on the current object via:
	 *
	 * POST /collection/id/method.xml
	 */
	function post ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) {
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		return self::_send_and_receive ($req, 'POST', $options);
	}

	/**
	 * Puts to a specified custom method on the current object via:
	 *
	 * PUT /collection/id/method.xml
	 */
	function put ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) { 
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		if (count ($options) > 0) {
			$req .= '?' . http_build_query ($options);
		}
		return self::_send_and_receive ($req, 'PUT');
	}

	/**
	 * Build the request, call _fetch() and parse the results.
	 */
	static function _send_and_receive ($url, $method, $element_name = '', $data = array ()) {
		$params = '';
		$el = substr ($element_name, 0, -1);
		foreach ($data as $k => $v) {
			if ($k != 'id' && $k != 'created-at' && $k != 'updated-at') {
				$params .= '&' . $el . '[' . str_replace ('-', '_', $k) . ']=' . rawurlencode ($v);
			}
		}
		$params = substr ($params, 1);

		$res = self::_fetch ($url, $method, $params);
    list ($headers, $res) = explode ("\r\n\r\n", $res, 2);

		if (preg_match ('/HTTP\/[0-9]\.[0-9] ([0-9]+)/', $headers, $regs)) {
			$response_code = $regs[1];
		} else {
			$response_code = false;
		}
		
		switch ($response_code) {
		  case 400:
		    trigger_error('Bad Request', E_USER_ERROR);
		    break;
		  case 401:
		    trigger_error('Unauthorized Access', E_USER_ERROR);
		    break;
  	  case 403:
  	    trigger_error('Forbidden Access', E_USER_ERROR);
  	    break;
		  case 404:
		    trigger_error('Resource Not Found', E_USER_ERROR);
		    break;
  	  case 405:
  	    trigger_error('Method Not Allowed', E_USER_ERROR);
  	    break;
  	  case 409:
  	    trigger_error('Resource Conflict', E_USER_ERROR);
  	    break;
  	  case 422:
  	    trigger_error('Resource Invalid', E_USER_WARNING);
  	    break;
		  default:
		    if(! $response_code){
		      trigger_error('Invalid response code');
		    } else if($response_code >= 401 and $response_code <= 500){
		      trigger_error('Client Error', E_USER_ERROR);
		    } else if($response_code >= 500 and $response_code <= 600){
		      trigger_error('Server Error', E_USER_ERROR);
	      } else if($response_code < 200 and $response_code > 399){
	        trigger_error('Unknown response code: '.$response_code, E_USER_ERROR);
    		}
		}		

		if (! $res) {
			return self;
		} elseif ($res == ' ') {
			return self;
		}

		// parse XML response
		$xml = new SimpleXMLElement ($res);
    
		if ($xml->getName() == $element_name) {
			// multiple
			$res = array();
			$data = array();
			foreach ($xml->children() as $child) {
				foreach ((array) $child as $k => $v) {
					$k = str_replace('-', '_', $k);
					if (isset($v['nil']) && $v['nil'] == 'true') {
						continue;
					} else {
						$data[$k] = $v;
					}
				}
				$res[] = $data;
			}
			return $res;
		}
		
	  if($xml->getName() == 'errors'){
	    $res = array();
	    $data = array();
	    foreach ((array) $xml as $k => $v) {
        $data[] = $v;
      }
	    $res['errors'] = $data;
		  return $res;
		}
	
	  $data = array();
		
		if($xml->getName() == 'nil-classes'){
		  return $data;
		}
		
		foreach ((array) $xml as $k => $v) {
      $k = str_replace ('-', '_', $k);
      if (isset ($v['nil']) && $v['nil'] == 'true') {
        continue;
      } else {
        $data[$k] = $v;
      }
    }
    return $data;
	}

	/**
	 * Fetch the specified request via cURL.
	 */
	static function _fetch ($url, $method, $params) {
		if (! extension_loaded ('curl')) {
			trigger_error('cURL extension not loaded', E_USER_ERROR);
		}
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, self::$headers);
		switch ($method) {
			case 'POST':
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				//curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/x-www-form-urlencoded\n"));
				break;
			case 'DELETE':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case 'PUT':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				//curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/x-www-form-urlencoded\n"));
				break;
			case 'GET':
			default:
				break;
		}
		$res = curl_exec ($ch);
		if (! $res) {
			trigger_error(curl_error($ch), E_USER_ERROR);
			curl_close ($ch);
			return false;
		}
		curl_close ($ch);
		return $res;
	}

	/**
	 * Getter for internal object data.
	 */
	function __get ($k) {
		if (isset ($this->_data[$k])) {
			return $this->_data[$k];
		}
		return $this->{$k};
	}

	/**
	 * Setter for internal object data.
	 */
	function __set ($k, $v) {
		if (isset ($this->_data[$k])) {
			$this->_data[$k] = $v;
			return;
		}
		$this->{$k} = $v;
	}

	/**
	 * Quick setter for chaining methods.
	 */
	function set ($k, $v = false) {
		if (! $v && is_array ($k)) {
			foreach ($k as $key => $value) {
				$this->_data[$key] = $value;
			}
		} else {
			$this->_data[$k] = $v;
		}
		return $this;
	}
}

?>