<?php

$lang = (!empty($_GET['lang'])) ? trim($_GET['lang']) : 'zh_cn';

header('Content-type: application/x-javascript; charset=utf-8');

// include_once('../languages/' . $lang . '/calendar.php');
// 
// foreach ($_LANG['calendar_lang'] AS $cal_key => $cal_data)
// {
//     echo 'var ' . $cal_key . " = \"" . $cal_data . "\";\r\n";
// }

include_once('./calendar/calendar.min.js');
