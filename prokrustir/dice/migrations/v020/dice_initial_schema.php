<?php

namespace prokrustir\dicedev\migrations\v020;

class dice_initial_schema extends \phpbb\db\migration\migration {

    static public function depends_on()
    {
        return [];
    }

    public function update_schema() {
        return [
			'add_tables'    => array(
				$this->table_prefix . 'dice_posts' => array(
					'COLUMNS' => array(
						'dice_post_id'               => array('UINT', NULL),
						'dice_post_command'          => array('VCHAR_UNI:255', ''),
						'dice_post_result'           => array('VCHAR_UNI:255', '')
					),
					'PRIMARY_KEY' => 'dice_post_id'
				),
			),
        ];
    }

    public function revert_schema()
    {
        return [];
    }
}