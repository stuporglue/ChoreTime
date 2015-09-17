#!/usr/bin/env php
<?php

require_once(__DIR__ . '/app/lib.inc');
// Cron this once a minute. It will subtract one minute from whoever is Active on the computer

$active = getActive();

foreach($active as $username => $sessions){
    $time_left = add_time($username,-1);
    if($time_left < 0){
        foreach($sessions as $session){
            $cmd = "/usr/local/bin/lock_screen.sh {$session['USER']} {$session['Display']}";
            shell_exec($cmd);
        }
    }
}
