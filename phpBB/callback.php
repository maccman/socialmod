<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

if (!function_exists('delete_posts'))
{
	include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
}

require_once('includes/socialmod.php');

$callback = new Callback($_POST);

if(!$callback->valid()){
  exit;
}

$result = $callback->state;
$post_id = $callback->custom_id;

if($result == 'failed') {
  
  delete_posts('post_id', array($post_id));
  
} if ($result == 'deferred') {
  
   $sql = 'UPDATE ' . POSTS_TABLE . "
     SET post_approved = 0
     WHERE post_id = $post_id";
   $result = $db->sql_query($sql);
   
} else if ($result = 'passed') {
  
  $sql = 'UPDATE ' . POSTS_TABLE . "
    SET post_approved = 1
    WHERE post_id = $post_id";
  $result = $db->sql_query($sql);
  
}

?>