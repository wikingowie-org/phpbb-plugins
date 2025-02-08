<?php

namespace prokrustir\dice\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface {
    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     */
    static public function getSubscribedEvents() {
        return [
            'core.modify_submit_post_data' => 'add_dice_to_post',
        ];
    }

    function processText($input) {
        // Extract content inside <t>...</t>
        if (preg_match('/<t>(.*?)<\/t>/', $input, $matches)) {
            $content = trim($matches[1]);
    
            // Match pattern: starts with 'k' followed by digits, optionally followed by 'x' and more digits
            if (preg_match('/^k(\d+)(?:x(\d+))?$/', $content, $parsed)) {
                $maxNumber = (int)$parsed[1]; // First number after 'k'
                $repeatCount = isset($parsed[2]) ? (int)$parsed[2] : 1; // Number of times to generate (default: 1)
    
                $randomNumbers = [];
                for ($i = 0; $i < $repeatCount; $i++) {
                    $randomNumbers[] = rand(1, $maxNumber);
                }
    
                return implode(', ', $randomNumbers);
            }
        }
    
        return "";
    }
    

    public function add_dice_to_post($event) {
        $data = $event['data'];
        $message = $data['message'];
        $message = $this->processText($message);
        if ($message) {
            $data['message'] = $message;
            $event['data'] = $data;
            $event['update_message'] = true;
        }
    }
}
