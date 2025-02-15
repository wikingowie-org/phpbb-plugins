<?php

define('IN_PHPBB', true);
$phpbb_root_path='./';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

$auth_tokens = array('70' => 'xxxx');
if (!array_key_exists($_POST['user_id'], $auth_tokens) || $_POST['token'] != $auth_tokens[$_POST['user_id']]) {
  die("404 Authentication Error.");
}

$session = new \phpbb\session();
/*
{
	"user_id": "70",
	"username": "Los",
	"message": "Testowy post z API.",
	"subject": 'API post',
	"forum_id": "10",
	"topic_id": "20"
}
*/

$session->session_begin();
$session->session_create($_POST['user_id'], false, false, false);
$poll = array();
$message = $_POST['message'];
$subject = $_POST['subject'];
// https://www.phpbb.com/community/viewtopic.php?t=2346626
$poll = $uid = $bitfield = $options = ''; 

$user->data['user_id'] = $_POST['user_id'];
$user->data['is_registered'] = true;
$user->data['username'] = $_POST['username'];
$user->data['user_colour'] = '';
$user->data['user_lastmark'] = 0;

$data = array( 
    // General Posting Settings
    'forum_id'            => $_POST['forum_id'],    // The forum ID in which the post will be placed. (int)
    'topic_id'            => $_POST['topic_id'],    // Post a new topic or in an existing one? Set to 0 to create a new one, if not, specify your topic ID here instead.
    'icon_id'            => false,    // The Icon ID in which the post will be displayed with on the viewforum, set to false for icon_id. (int)

    // Defining Post Options
    'enable_bbcode'    => true,    // Enable BBcode in this post. (bool)
    'enable_smilies'    => true,    // Enabe smilies in this post. (bool)
    'enable_urls'        => true,    // Enable self-parsing URL links in this post. (bool)
    'enable_sig'        => true,    // Enable the signature of the poster to be displayed in the post. (bool)

    // Message Body
    'message'            => $message,        // Your text you wish to have submitted. It should pass through generate_text_for_storage() before this. (string)
    'message_md5'    => md5($message),// The md5 hash of your message

    // Values from generate_text_for_storage()
    'bbcode_bitfield'    => $bitfield,    // Value created from the generate_text_for_storage() function.
    'bbcode_uid'        => $uid,        // Value created from the generate_text_for_storage() function.

    // Other Options
    'post_edit_locked'    => 0,        // Disallow post editing? 1 = Yes, 0 = No
    'topic_title'        => $subject,    // Subject/Title of the topic. (string)
	
    // Email Notification Settings
    'notify_set'        => false,        // (bool)
    'notify'            => false,        // (bool)
    'post_time'         => 0,        // Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
    'forum_name'        => '',        // For identifying the name of the forum in a notification email. (string)

    // Indexing
    'enable_indexing'    => true,        // Allow indexing the post? (bool)

    // 3.0.6
    'force_approved_state'    => true, // Allow the post to be submitted without going into unapproved queue

    // 3.1-dev, overwrites force_approve_state
    'force_visibility'            => true, // Allow the post to be submitted without going into unapproved queue, or make it be deleted
);

$ret=submit_post('reply','subject',$_POST['username'],POST_NORMAL,$poll,$data);