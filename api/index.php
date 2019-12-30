<?php
require_once('json_responder.php');
$JR = new JsonResponder();

$index_response =  Array();

$dont_check = true;
$check_for_update = false;

// require '../header.inc.php';
// require '../includes/credits.inc.php';
require_once('../includes/required.php');

if ($PARAMETERS['settings']['protection']=='ON') {
    require '../protezione.php';
}



/** * Definizione pagina da visualizzare */
if (!empty($_GET['page'])) {
    $page = gdrcd_filter('include', $_GET['page']);
} else {
    $page = 'index';
}
$index_response['page'] = $page;
        
/** * Definizione dell'eventuale contenuto interno
    * Utile se si vuol mantenere la struttura della homepage quando si aprono i link
*/
if (!empty($_GET['content'])) {
    $content = gdrcd_filter('include', $_GET['content']);
} else {
    $content = 'home';
}
$index_response['content'] = $content;

$users = gdrcd_query("SELECT COUNT(nome) AS online FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()");


include 'themes/notheme/home/' . $page . '.php';
$index_response['index'] = $index_notheme_response;

// $JR->responde(array('response' => $index_response));

$JR->responde($index_notheme_response);

