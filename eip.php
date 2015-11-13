<?php
    require_once('../../config.php');
    require_once('lib/eip.php');

    require_login();

    $sid            = optional_param('_sid', '', PARAM_RAW);
    $tusername      = optional_param('_tusername', '', PARAM_RAW);
    $ban            = optional_param('_ban', '', PARAM_RAW);

if ($sid && !$tusername && !$ban) {
    // School -> Teacher
    //if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM)))
    //    echo asksch($sid);
    //else
    //    echo askth($USER->username, $sid);

    // School -> ban
    echo banlist($sid);
} elseif ($sid && $tusername) {
    echo askth($tusername, $sid);
} elseif ($sid && $ban) {
    askban($sid, $ban);
}
