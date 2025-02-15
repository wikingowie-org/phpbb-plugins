<?php

namespace prokrustir\createpostapi;

class ext extends \phpbb\extension\base
{
    public function enable_step($old_state)
    {
        if ($old_state === false) {
            copy('../ext/prokrustir/createpostapi/root-files/api.php', '../create-post-api.php');
        }
        return parent::enable_step($old_state);
    }
}