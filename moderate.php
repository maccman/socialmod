<?php

/**
 * PLEASE DONT'T USE this yet - it's untested.
 *
 * PHP client to SocialMod
 * For more information, please see the API:
 * http://socialmod.com/api
 *
 * Dependencies:
 * - curl
 * - my version of ActiveResource.php
 * 
 * Usage:
 *
 * <?php
 *
 * require_once ('moderate.php');
 * $mod = new Moderate(array('src' => 'http://example.com'));
 * $mod->save();
 *
 * @author Alex MacCaw (info@socialmod.com)
 * @version 0.1
 * @license MIT
 *
 */

require_once ('ActiveResource.php');

class Moderate extends ActiveResource {
    var $site = 'http://api.socialmod.com/';
    var $element_name = 'items';
    
    function set_api_key($key){
      $this->headers = array("Authorization:".$key);
    }
    
    function flag(){
      $this->post('flag');
    }
}

?>