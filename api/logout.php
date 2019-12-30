<?php
require_once('../vendor/autoload.php');

require_once('json_responder.php');
$JR = new JsonResponder();

session_start();

require '../config.inc.php';
require '../includes/functions.inc.php';
require '../vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';

$handleDBConnection = gdrcd_connect();

gdrcd_query("UPDATE personaggio SET ora_uscita = NOW() WHERE nome='" . gdrcd_filter('in', $_SESSION['login']) . "'");

session_regenerate_id(true);

gdrcd_close_connection($handleDBConnection);

unset($MESSAGE);
unset($PARAMETERS);

session_unset();
session_destroy();

$JR->responde(array('class' => 'info', 'message' => gdrcd_filter('out', $_SESSION['login']) .' '.$MESSAGE['logout']['confirmation']));
