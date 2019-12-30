<?php
require_once('json_responder.php');
$JR = new JsonResponder();

require '../config.inc.php';
require '../includes/functions.inc.php';
require '../vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';

$modules = array();

$strInnerPage = "";

if (isset($_REQUEST['page'])) {
    $strInnerPage = gdrcd_filter('include', $_REQUEST['page']).'.inc.php';
} elseif (isset($_REQUEST['dir']) && is_numeric($_REQUEST['dir'])) {
    if ($_REQUEST['dir'] >= 0) {
        $strInnerPage = 'frame_chat.inc.php';
    } else {
        $strInnerPage = 'mappaclick.inc.php';
        $_REQUEST['id_map'] = $_SESSION['mappa'];
    }
} else {
    $strInnerPage = 'mappaclick.inc.php';
}
$module = [
    "path"=> 'pages/'.$strInnerPage,
    "box"=> "inner"
];
$modules[] = $module;


if ($PARAMETERS['left_column']['activate'] == 'ON') {
    foreach ($PARAMETERS['left_column']['box'] as $box) {
        $module = [
            "path"=> 'pages/'.$box['page'].'.inc.php',
            "box"=> $box
        ];
        $modules[] = $module;
    }
}

if ($PARAMETERS['right_column']['activate'] == 'ON') {
    foreach ($PARAMETERS['right_column']['box'] as $box) {
        $module = [
            "path"=> 'pages/'.$box['page'].'.inc.php',
            "box"=> $box
        ];
        $modules[] = $module;
    }
}

$JR->responde(array('modules' => $modules));