<?php

require_once('load_data.inc');

$keys = Array(
    'face' => '',
    'fhe' => 'FHE Assignment',
    'chore' => 'Todays Chore',

    'time_left' => 'Time Left',

    'morning'   => 'Morning Chores',
    'night'   => 'Night Chores',

    'spending' => 'Spending Money',
    'tithing' => 'Unpaid Tithing',
    'savings' => 'Unbanked Savings',
);

$accounts_to_skip = Array('stuporglue','mamallama');

$bodyclass = 'index';
require_once('template.php');
