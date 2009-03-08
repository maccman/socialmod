<?php

/**
 *
 * PHP client to SocialMod
 * For more information, please see the API:
 * http://socialmod.com/api
 *
 * Dependencies:
 * - curl
 * - custom version of ActiveResource.php
 * http://github.com/maccman/socialmod/blob/master/ActiveResource.rb
 * 
 * Usage:
 *
 * <?php
 *
 * require_once ('moderate.php');
 * Moderate::set_api_key('foo');
 *
 * $mod = new Moderate(array('src' => 'http://example.com'));
 * $mod->save();
 *
 * // Find and flag
 * $mod2 = Moderate::find($mod->id);
 * $mod2->flag();
 *
 * // Sync
 * $mods = Moderate::sync();
 *
 *
 * @author Alex MacCaw (info@socialmod.com)
 * @version 0.1
 * @license MIT
 *
 */

require_once ('ActiveResource.php');

class Moderate extends ActiveResource {
    public static $site = 'http://api.socialmod.com/';
    public static $element_name = 'items';
    
    static function set_api_key($key){
      self::$headers = array("Authorization:".$key);
    }
    
    static function find($id = false){
      return parent::find(__CLASS__, $id);
    }
    
    static function sync(){
  		$req = self::$site . self::$element_name;
      $req .= '/sync.xml';
  		
		  $res = array();
		  $data = self::_send_and_receive($req, 'GET', self::$element_name);
		  foreach($data as $d){
		    $res[] = new self($d);
		  }
  		return $res;
    }
    
    function flag(){
      $this->post('flag');
    }
}

?>