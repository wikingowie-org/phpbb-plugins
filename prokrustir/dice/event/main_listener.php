<?php

namespace prokrustir\dice\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;

class main_listener implements EventSubscriberInterface {
	
	public function __construct(driver_interface $db, language $language)
	{
		$this->db = $db;
		$this->language = $language;
	}
    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     */
    static public function getSubscribedEvents() {
        return [
            'core.submit_post_end' => 'add_dice_to_post',
			//'core.viewtopic_post_rowset_data' => 'display_post_with_dice',
			'core.viewtopic_modify_post_row' => 'display_post_with_dice',
			// Topic review while replying to post
			'core.topic_review_modify_row' => 'display_post_on_topic_review',
			'core.display_custom_bbcodes'				=> 'setup_media_bbcode',
			'core.posting_modify_cannot_edit_conditions' => 'prevent_edit'
        ];
    }
	
	/**
	 * Set template switch for displaying the [media] BBCode button
	 *
	 * @return void
	 */
	public function setup_media_bbcode()
	{
		$this->language->add_lang('common', 'prokrustir/dice');
	}

    function processText($input) {
		
		if (preg_match('/\[dice\](\d+)(?:x(\d+))?\[\/dice\]/', $input, $match)) {
			$maxNumber = (int)$match[1]; // Pierwsza liczba (np. 8 w [dice]8x2[/dice])
			$repeatCount = isset($match[2]) ? (int)$match[2] : 1; // Druga liczba po "x" (domyślnie 1)

			$randomNumbers = [];
			for ($i = 0; $i < $repeatCount; $i++) {
				$randomNumbers[] = rand(1, $maxNumber);
			}

			return array("dice_type" => $maxNumber, "repeats" => $repeatCount, "result" => implode(', ', $randomNumbers));
		}
    
        return array();
    }
    
	public function prevent_edit($event) {
		if ($this->is_dice_tag($event['post_data']['post_text'])) {
			if ($this->get_dice_post_data($event['post_data']['post_id'])) {
				$cant_edit = $event['s_cannot_edit_locked'];
				$cant_edit = true;
				$event['s_cannot_edit_locked'] = $cant_edit;
			}
		}
	}

    public function add_dice_to_post($event) {
		$data = $this->processText($event['data']['message']);
		if ($data) {
			$sql = 'INSERT INTO phpbb_dice_posts (dice_post_id, dice_post_command, dice_post_result ) VALUES ('.$event['data']['post_id'].', "'.$data['dice_type'].'x'.$data['repeats'].'", "'.$data['result'].'")';
			$result = $this->db->sql_query($sql);
		}
    }
	
	public function display_post_with_dice($event) {
		if ($this->is_dice_tag($event['row']['post_text'])) {
			$post_row = $event['post_row'];
			$dice_data = $this->get_dice_post_data($event['row']['post_id']);
			//$row = $event['row'];
			$post_text = $post_row['MESSAGE'];
			$post_text = $this->replace_dice_tag($dice_data, $post_text);
			$row['post_text'] = $post_text;
			//$event['row'] = $row;
			$event['post_row'] = array_merge($event['post_row'], array(
				'MESSAGE' => $post_text,
				'DICE_THROW' => $dice_data['command'] . ': ' . $dice_data['throw']
			));
		}
	}
	
	public function display_post_on_topic_review($event) {
		if ($this->is_dice_tag($event['post_row']['MESSAGE'])) {
			$post_row = $event['post_row'];
			$dice_data = $this->get_dice_post_data($event['row']['post_id']);
			$post_text = $post_row['MESSAGE'];
			$post_text = $this->replace_dice_tag($dice_data, $post_text);
			$row['post_text'] = $post_text;
			$event['post_row'] = array_merge($event['post_row'], array(
				'MESSAGE' => $post_text
			));			
		}
	}
	
	function replace_dice_tag($dice_data, $post_text) {
		$add_text = "Wykonano rzut kością " . $dice_data['command'] . " z wynikiem " . $dice_data['throw'] .".";
		$post_text = preg_replace('/\[dice\].*?\[\/dice\]/', $add_text, $post_text);
		return $post_text;
	}
	
	function get_dice_post_data($post_id) {
			$sql = 'SELECT * FROM phpbb_dice_posts WHERE dice_post_id = ' . $post_id;
			$result = $this->db->sql_query($sql);
			if (!$result) {
				return array();
			}
			while ($row = $this->db->sql_fetchrow($result)) {
				$command = $row['dice_post_command'];
				$throw = $row['dice_post_result'];
			}
			return array("command" => $command, "throw" => $throw);
	}
	
	function is_dice_tag($content) {
		if (preg_match('/\[dice\](\d+)(?:x(\d+))?\[\/dice\]/', $content, $match)) {
			return true;
		}
		return false;
	}
	
	function display_secure_dice_info($event) {
		
	}
}
