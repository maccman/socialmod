<?php

// Change this to your accout's API key
$api_key = 'change-me';

// Change this to phpBB's public address
$site_url = 'http://127.0.0.1/~Alex/phpBB3';

// Don't edit any further

if (!defined('IN_PHPBB'))
{
	exit;
}

require_once('socialmod/php/moderate.php');
require_once('socialmod/php/callback.php');

Moderate::set_api_key($api_key);
Callback::set_api_key($api_key);

function moderate_post($data){
  global $site_url;
  $mod = new Moderate(array(
    'data'         => $data['message'], 
    'custom_id'    => $data['post_id'],
    'callback_url' => $site_url.'/callback.php'
  ));
	$mod->save();
}

?>