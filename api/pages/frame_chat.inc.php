<?php
$frame_chat_response = array();

$info = gdrcd_query("SELECT nome, stanza_apparente, invitati, privata, proprietario, scadenza FROM mappa WHERE id=".$_SESSION['luogo']." LIMIT 1");

$frame_chat_response['title'] = $info['name'];
$frame_chat_response['privata'] = $info['privata'];
if ($info['privata']==1) {
    $allowance=false;

    if ((($info['proprietario']==gdrcd_capital_letter($_SESSION['login'])) || (strpos($_SESSION['gilda'], $info['proprietario'])!=false) || (strpos($info['invitati'], gdrcd_capital_letter($_SESSION['login']))!=false) ||
       (($PARAMETERS['mode']['spyprivaterooms']=='ON')&&($_SESSION['permessi']>MODERATOR))) && ($info['scadenza']>strftime('%Y-%m-%d %H:%M:%S'))) {
        $allowance=true;
    }
} else {
    $allowance=true;
}

if ($allowance === false) {
    $frame_chat_response['message'] = $MESSAGE['chat']['whisper']['privat'];
} else {
    // pages/chat.inc.php?ref=30&chat=yes
    $_SESSION['last_message']=0;

    $frame_chat_response['chat'] = array();
    $frame_chat_response['chat']['actions'] = array();
    $frame_chat_response['chat']['actions'][0] = gdrcd_filter('out', $MESSAGE['chat']['type'][0]); //parlato
    $frame_chat_response['chat']['actions'][1] = gdrcd_filter('out', $MESSAGE['chat']['type'][1]); //azione
if ($_SESSION['permessi']>=GAMEMASTER) {
    $frame_chat_response['chat']['actions'][2] = gdrcd_filter('out', $MESSAGE['chat']['type'][2]); //master
    $frame_chat_response['chat']['actions'][3] = gdrcd_filter('out', $MESSAGE['chat']['type'][3]); //png
}
    $frame_chat_response['chat']['actions'][4] = gdrcd_filter('out', $MESSAGE['chat']['type'][4]); //sussurro
    if (($info['privata']==1)&&(($info['proprietario']==$_SESSION['login'])||((is_numeric($info['proprietario'])===true)&&(strpos($_SESSION['gilda'], ''.$info['proprietario']))))) {
        $frame_chat_response['chat']['actions'][5] = gdrcd_filter('out', $MESSAGE['chat']['type'][5]); //invita
    $frame_chat_response['chat']['actions'][6] = gdrcd_filter('out', $MESSAGE['chat']['type'][6]); //caccia
    $frame_chat_response['chat']['actions'][7] = gdrcd_filter('out', $MESSAGE['chat']['type'][7]); //elenco
    }
    $frame_chat_response['chat']['type'] = gdrcd_filter('out', $MESSAGE['chat']['type']['info']);
    $frame_chat_response['chat']['tag'] = gdrcd_filter('out', $MESSAGE['chat']['tag']['info']['tag'].$MESSAGE['chat']['tag']['info']['dst']);
    if ($_SESSION['permessi']>=GAMEMASTER) {
        $frame_chat_response['chat']['tag_png'] = gdrcd_filter('out', $MESSAGE['chat']['tag']['info']['png']);
    }
    $frame_chat_response['chat']['tag_msg'] = gdrcd_filter('out', $MESSAGE['chat']['tag']['info']['msg']);

    if ($PARAMETERS['mode']['chatsave']=='ON') {
        ;
        // window.open('chat_save.proc.php','Log','width=1,height=1,toolbar=no');">
    }

    if (($PARAMETERS['mode']['skillsystem']=='ON')||($PARAMETERS['mode']['dices']=='ON')) {
        $frame_chat_response['skillsystem'] = array();

        if ($PARAMETERS['mode']['skillsystem']=='ON') {
            $result = gdrcd_query("SELECT id_abilita, nome FROM abilita WHERE id_razza=-1 OR id_razza IN (SELECT id_razza FROM personaggio WHERE nome = '".$_SESSION['login']."') ORDER BY nome", 'result');
            $frame_chat_response['skillsystem']['abilita'] = array();
            while ($row = gdrcd_query($result, 'fetch')) {
                $frame_chat_response['skillsystem']['abilita']['id'] = $row['id_abilita'];
                $frame_chat_response['skillsystem']['abilita']['nome'] = gdrcd_filter('out', $row['nome']);
            }
            
            gdrcd_query($result, 'free');
            $frame_chat_response['skills'] = gdrcd_filter('out', $MESSAGE['chat']['commands']['skills']);
            $frame_chat_response['stats'] = array();
            foreach ($PARAMETERS['names']['stats'] as $id_stats => $name_stats) {
                if (is_numeric(substr($id_stats, 3))) {
                    $frame_chat_response['stats']['stats_'.substr($id_stats, 3)] = $name_stats;
                }
            }
    
            $frame_chat_response['stats']['info'] = gdrcd_filter('out', $MESSAGE['chat']['commands']['stats']);
        } else {
            //no skills;
        }
        if ($PARAMETERS['mode']['dices']=='ON') {
            $frame_chat_response['dices'] = array();
            foreach ($PARAMETERS['settings']['skills_dices'] as $dice_name => $dice_value) {
                $frame_chat_response['dices']['name'] = $dice_name;
                $frame_chat_response['dices']['value'] = $dice_value;
            }
            $frame_chat_response['dices']['info'] = gdrcd_filter('out', $MESSAGE['chat']['commands']['dice']);
        } else {
            //no dice;
        }
        if ($PARAMETERS['mode']['skillsystem']=='ON') {
            $result = gdrcd_query("SELECT clgpersonaggiooggetto.id_oggetto, oggetto.nome, clgpersonaggiooggetto.cariche FROM clgpersonaggiooggetto JOIN oggetto ON clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto WHERE clgpersonaggiooggetto.nome = '".$_SESSION['login']."' AND posizione > 0 ORDER BY oggetto.nome", 'result');
            $frame_chat_response['skillsystem']['items'] = array();
            while ($row=gdrcd_query($result, 'fetch')) {
                $frame_chat_response['skillsystem']['items'][$row['id_oggetto'].'-'.$row['cariche'].'-'.gdrcd_filter('out', $row['nome'])] = $row['nome'];
                $frame_chat_response['skillsystem']['items'][$row['id_oggetto']] = array();
                $frame_chat_response['skillsystem']['items'][$row['id_oggetto']]['cariche'] = $row['cariche'];
                $frame_chat_response['skillsystem']['items'][$row['id_oggetto']]['nome'] = $row['nome'];
            }
       
            gdrcd_query($result, 'free');
        } else {
            //no item;
        }
    }
}
