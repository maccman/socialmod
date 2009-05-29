<?php

class CallBack
{
  private static $api_key = 'items';
  var $_params = array();
  
  function __construct($params)
  {
    $this->_params = $params;
  }
  
  function __get ($k) {
		if (isset ($this->_params[$k])) {
			return $this->_params[$k];
		}
		return $this->{$k};
	}
	  
  static function set_api_key($key){
    self::$api_key = $key;
  }
	
	function valid(){
    return($this->hmac() == $this->_params['signature']);
	}
	
	private function hmac(){
	  $data = $this->_params['timestamp'].
	          $this->_params['id'].
	          $this->_params['state'].
	          $this->_params['data']. 
	          $this->_params['src'];
	  return(hash_hmac('SHA1', $data, self::$api_key, false));
	}
}

?>