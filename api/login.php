<?php
require_once('../vendor/autoload.php');

require_once('json_responder.php');
$JR = new JsonResponder();

$_POST = json_decode(file_get_contents('php://input'), true);

session_start();

require_once('../includes/required.php');

$handleDBConnection=gdrcd_connect();

$login1	= gdrcd_filter('get', $_POST['login1']);
$pass1	= gdrcd_filter('get', $_POST['pass1']);

switch ($_SERVER['REMOTE_ADDR']) {
    case '::1':
    case '127.0.0.1':	$host = 'localhost';
        break;
    default:		$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        break;
}
$result = gdrcd_query("SELECT * FROM blacklist WHERE ip = '".$_SERVER['REMOTE_ADDR']."' AND granted = 0", 'result');

if (gdrcd_query($result, 'num_rows') > 0) {
    gdrcd_query($result, 'free');

    $JR->responde(array("class" => "error_major", 'message' => $MESSAGE['warning']['blacklisted']));
    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$login1."', 'Login_procedure', NOW(), ".BLOCKED.", '".$_SERVER['REMOTE_ADDR']."')");
    exit();
}

$login1 = ucwords(strtolower(trim($login1)));

$record = gdrcd_query("SELECT personaggio.pass, personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.id_razza, personaggio.ultimo_messaggio, personaggio.blocca_media, personaggio.ora_entrata, personaggio.ora_uscita, personaggio.ultimo_refresh, razza.sing_m, razza.sing_f, razza.icon AS url_img_razza FROM personaggio LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE nome = '".gdrcd_filter('in', $login1)."' LIMIT 1");

if (!empty($record) and gdrcd_password_check($pass1, $record['pass']) && ($record['permessi']>-1) && (strtotime($record['ora_entrata']) < strtotime($record['ora_uscita'])||(strtotime($record['ultimo_refresh'])+300) < time())) {
    $_SESSION['login'] = $record['nome'];
    $_SESSION['cognome'] = $record['cognome'];
    $_SESSION['permessi'] = $record['permessi'];
    $_SESSION['sesso'] = $record['sesso'];
    $_SESSION['blocca_media'] = $record['blocca_media'];
    $_SESSION['ultima_uscita'] = $record['ora_uscita'];

    if ($record['sesso']=='f') {
        $_SESSION['razza'] = $record['sing_f'];
    } else {
        $_SESSION['razza'] = $record['sing_m'];
    }
    
    $_SESSION['img_razza']	= $record['url_img_razza'];
    $_SESSION['id_razza']	= $record['id_razza'];
    $_SESSION['posizione']	= $record['posizione'];
    if (empty($record['ultima_mappa'])===true) {
        $_SESSION['mappa']	= 1;
    } else {
        $_SESSION['mappa']	= $record['ultima_mappa'];
    }
    if (empty($record['ultimo_luogo'])===true) {
        $_SESSION['luogo']	= -1;
    } else {
        $_SESSION['luogo']	= $record['ultimo_luogo'];
    }
    
    $_SESSION['Tag'] = "";
    $_SESSION['last_message'] = 0;
    $_SESSION['last_istant_message'] = $record['ultimo_messaggio'];

    $res = gdrcd_query("SELECT ruolo.gilda, ruolo.immagine FROM ruolo JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio = '".gdrcd_filter('in', $record['nome'])."'", 'result');

    while ($row = gdrcd_query($res, 'fetch')) {
        $_SESSION['gilda'] .= ',*'.$row['gilda'].'*';
        $_SESSION['img_gilda'] .= $row['immagine'].',';
    }

    gdrcd_query($res, 'free');

    $lastlogindata = gdrcd_query("SELECT nome_interessato, autore FROM log WHERE nome_interessato = '".gdrcd_filter('in', $_SESSION['login'])."' AND codice_evento=".LOGGEDIN." ORDER BY data_evento DESC LIMIT 1");
    if ((isset($_COOKIE['lastlogin'])===true) && ($_COOKIE['lastlogin'] != $_SESSION['login'])) {
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','doppio (cookie)', NOW(), ".ACCOUNTMULTIPLO.", '".$_COOKIE['lastlogin'] ."')");
    } elseif ($lastlogindata['autore'] == $_SERVER['REMOTE_ADDR']) {
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','doppio (ip)', NOW(), ".ACCOUNTMULTIPLO.", '".gdrcd_filter('in', $lastlogindata['nome_interessato']) ."')");
    }
    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','".$_SERVER['REMOTE_ADDR']."', NOW(), ".LOGGEDIN." ,'".$_SERVER['REMOTE_ADDR']."')");
} elseif (strtotime($record['ora_entrata']) > strtotime($record['ora_uscita'])||(strtotime($record['ultimo_refresh'])+300) > time()) {
    $JR->responde(array("class" => "error_major", 'message' => $MESSAGE['warning']['double_connection']));

    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$login1."', 'Login_procedure', NOW(), ".BLOCKED.", '".$_SERVER['REMOTE_ADDR']."')");
    exit();
} else {
    $_SESSION['login'] = '';

    if (($login1 != '') && ($pass1 != '')) {
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','".$host."', NOW(), ".ERRORELOGIN." ,'".$_SERVER['REMOTE_ADDR']."')");


        $record = gdrcd_query("SELECT count(*) FROM log WHERE descrizione_evento = '".$_SERVER['REMOTE_ADDR']."' AND codice_evento = ".ERRORELOGIN." AND DATE_ADD(data_evento, INTERVAL 60 MINUTE) > NOW()");
        $iErrorsNumber = $record['count(*)'];

        if ($iErrorsNumber>=10) {
            gdrcd_query("INSERT INTO blacklist (ip, nota, ora, host) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$login1." (tenta password)', NOW(), '".$Host."')");
        }
    }
}

if ($_SESSION['login'] != '') {
    if (gdrcd_controllo_esilio($_SESSION['login'])===true) {
        session_destroy();
        $JR->responde(array("class" => "?", 'message' => $PARAMETERS['info']['homepage_name']));
        exit();
    } else {
        setcookie('lastlogin', $_SESSION['login'], 0, '', '', 0);
        if ($PARAMETERS['mode']['log_back_location']=='OFF') {
            $_SESSION['luogo']='-1';
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_luogo='-1', ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."',  is_invisible = 0 WHERE nome =  '".gdrcd_filter('in', $_SESSION['login'])."'");
            $JR->responde(array('location' => 'main.php?page=mappaclick&map_id='.$_SESSION['mappa']));
        } else {
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."',  is_invisible = 0 WHERE nome =  '".$_SESSION['login']."'");
            $JR->responde(array('location' => 'main.php?dir='.$_SESSION['luogo']));
        }
    }
} else {
    session_destroy();
    $JR->responde(array('class' => 'error_major', 'message' => 'Username or Password are wrong!'));
}

gdrcd_close_connection($handleDBConnection);
?>
