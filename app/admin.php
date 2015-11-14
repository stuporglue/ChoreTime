<?php

require_once('load_data.inc');

$keys = Array(
    'face' => '',
    'lock'      => '',
    'fhe' => 'FHE Assignment',
    'chore' => 'Todays Chore',
    'loggedin' => 'Logged In?',
    'active' => 'Active?',
    'time_left' => 'Time Left',

    'spending' => 'Spending Money',
    'tithing' => 'Unpaid Tithing',
    'savings' => 'Unbanked Savings',

    'morning'   => 'Morning Chores',
    'night'   => 'Night Chores',
    'extra'   => 'Extra Chores',

    // 'timetoday' => 'Time Used Today',
    // 'vnc'      => 'Start VNC Session',
);

$bodyclass = 'admin';
require_once('template.php');
