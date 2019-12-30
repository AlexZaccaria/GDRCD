<?php
require_once('json_responder.php');
$JR = new JsonResponder();
$response = Array();

session_start();

$_REQUEST = json_decode(file_get_contents('php://input'), true);

require '../config.inc.php';
require '../includes/functions.inc.php';
require '../vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';

gdrcd_controllo_sessione();
$strInnerPage = "";
$strInnerPageLbl = "";

if (!empty($_GET['map_id'])) {
    $_SESSION['mappa'] = (int)$_GET['map_id'];
    gdrcd_query("UPDATE personaggio SET ultima_mappa=".gdrcd_filter('num', $_SESSION['mappa']).", ultimo_luogo=-1 WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'");
}

if (isset($_REQUEST['page'])) {
    $strInnerPage = gdrcd_filter('include', $_REQUEST['page']).'.inc.php';
} elseif (isset($_REQUEST['dir']) && is_numeric($_REQUEST['dir'])) {
    if ($_REQUEST['dir'] >= 0) {
        $strInnerPage = 'frame_chat.inc.php';
        $strInnerPageLbl = 'chat';
    } else {
        $strInnerPage = 'mappaclick.inc.php';
        $strInnerPageLbl = 'mappa';
        $_REQUEST['id_map'] = $_SESSION['mappa'];
    }
    gdrcd_query("UPDATE personaggio SET ultimo_luogo=".gdrcd_filter('num', $_REQUEST['dir'])." WHERE nome='".gdrcd_filter('in', $_SESSION['login'])."'");
} else {
    $strInnerPage = 'mappaclick.inc.php';
    $strInnerPageLbl = 'mappa';
    $_REQUEST['id_map'] = $_SESSION['mappa'];
}
require('pages/'.$strInnerPage);
$main_response[$strInnerPageLbl] = $frame_chat_response;

if (gdrcd_controllo_esilio($_SESSION['login']) === true) {
    session_destroy();
} else {
    require('layouts/nolayout_frames.php');
}

$JR->responde($main_response);