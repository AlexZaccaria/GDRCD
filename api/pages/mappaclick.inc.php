<?php
if (isset($_GET['map_id'])===true) {
    $current_map=gdrcd_filter('num', $_GET['map_id']);
} else {
    $current_map=$_SESSION['mappa'];
}
$redirect_pc=0;

if ((isset($_POST['op'])===true) && (($_POST['op']==gdrcd_filter('out', $MESSAGE['interface']['maps']['leave']))||($_POST['op']==gdrcd_filter('out', $MESSAGE['interface']['maps']['arrive'])))) {
    gdrcd_query("UPDATE mappa_click SET posizione = ".gdrcd_filter('num', $_POST['destination'])." WHERE id_click = ".gdrcd_filter('num', $_REQUEST['map_id'])." LIMIT 1");
}

if ((isset($_POST['op'])===true) && ($_POST['op']==gdrcd_filter('out', $MESSAGE['interface']['maps']['set_meteo']))) {
    gdrcd_query("UPDATE mappa_click SET meteo = '".gdrcd_filter('num', $_POST['temperature'])."°C - ".gdrcd_filter('in', $_POST['climate'])."' WHERE id_click = ".gdrcd_filter('num', $_REQUEST['map_id'])." LIMIT 1");
}

$result=gdrcd_query("SELECT mappa.id, mappa.nome, mappa.chat, mappa.x_cord, mappa.y_cord, mappa.id_mappa, mappa_click.nome AS nome_mappa, mappa_click.immagine, mappa_click.posizione, mappa_click.id_click, mappa_click.mobile FROM mappa_click LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click WHERE mappa_click.id_click = ".$current_map."", 'result');

if (gdrcd_query($result, 'num_rows')==0) {
    $result = gdrcd_query("SELECT id_click FROM mappa_click LIMIT 1", 'result');
}

$response = array();
$map_detail = array();

if (gdrcd_query($result, 'num_rows')==0) {
    $map_detail['nome_mappa'] = 'no map';
    $map_detail['info'] = gdrcd_filter('out', $MESSAGE['error']['can_t_find_any_map']);
} else {
    $just_one_click=gdrcd_query($result, 'fetch');
    gdrcd_query($result, 'free');

    $result=gdrcd_query("SELECT mappa.id, mappa.nome, mappa.chat, mappa.link_immagine, mappa.descrizione, mappa.link_immagine_hover, mappa.id_mappa_collegata, mappa.x_cord, mappa.y_cord, mappa.id_mappa, mappa.pagina, mappa_click.nome AS nome_mappa, mappa_click.immagine, mappa_click.posizione, mappa_click.id_click, mappa_click.mobile, mappa_click.larghezza, mappa_click.altezza FROM mappa_click LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click WHERE mappa_click.id_click = ".$just_one_click['id_click']."", 'result');
    $redirect_pc=1;
    $echoed_title=false;
    $echo_bottom=false;
    $vicinato=0;
    $self=0;
    $mobile=0;
    while ($row=gdrcd_query($result, 'fetch')) {
        if ($redirect_pc==1) {
            gdrcd_query("UPDATE personaggio SET ultima_mappa=".gdrcd_filter('get', $row['id_click'])." WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'");
        }
        
        if ($echoed_title===false) {
            $map_detail['nome_mappa'] = $row['nome_mappa'];
            $map_detail['map_tooltip'] = $PARAMETERS['mode']['map_tooltip'];
            $map_detail['map'] = array();
            $map_detail['map']['img'] = 'themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/maps/'.$row['immagine'];
            $map_detail['map']['w'] = $row['larghezza'];
            $map_detail['map']['h'] = $row['altezza'];
            $map_detail['vicinato'] = $row['posizione'];
            $map_detail['self'] = $row['id_click'];
            $map_detail['mobile'] = $row['mobile'];
        }
        $map_detail['coords'] = array();
        $map_detail['coords']['x'] = $row['x_cord'];
        $map_detail['coords']['y'] = $row['y_cord'];

        $qstring_link = '';
        $label_link = '';

        if ($row['chat'] == 1) {
            $qstring_link = 'dir='. $row['id'];
        } elseif ($row['id_mappa_collegata'] != 0) {
            $qstring_link = 'page=mappaclick&map_id='. $row['id_mappa_collegata'];
        } else {
            $qstring_link = 'page='. $row['pagina'];
        }

        if (empty($row['link_immagine'])) {
            $label_link = $row['nome'];
        } else {
            $baseimg_link = 'themes/'. $PARAMETERS['themes']['current_theme'] .'/imgs/maps/';
            $switchimg_link['link'] = array();
            if (!empty($row['link_immagine_hover'])) {
                $switchimg_link['link']['hover'] = $baseimg_link . $row['link_immagine_hover'];
                $switchimg_link['link']['out'] = $baseimg_link . $row['link_immagine'];
            } else {
                $switchimg_link = array();
            }
            $switchimg_link['link']['img'] = $baseimg_link . $row['link_immagine'];
            $switchimg_link['link']['nome'] = $row['nome'];
        }

        $response[] = $switchimg_link;

        $fadedesc_link = '';

        if ($PARAMETERS['mode']['map_tooltip'] == 'ON') {
            if (!empty($row['descrizione'])) {
                $descrizione = trim(nl2br(gdrcd_filter('in', $row['descrizione'])));
                $descrizione = strtr($descrizione, array("\n\r" => '', "\n" => '', "\r" => '', '"' => '&quot;'));

                $fadedesc_link = $descrizione;
            }
        }

        $map_detail['location'] = array();
        $map_detail['location']['link'] = $qstring_link;
        $map_detail['location']['description'] = $fadedesc_link;
        $map_detail['location']['label'] = $label_link;

        $response[] = $map_detail;
    }
    
    if ($vicinato!=INVIAGGIO) {
        $response['page_title'] = gdrcd_filter('out', $MESSAGE['interface']['maps']['more']);

        $result = gdrcd_query("SELECT id_click, nome FROM mappa_click WHERE posizione = ".$vicinato." AND id_click <> ".$self." ORDER BY nome", 'result');

        $response['vicinato'] = array();
        if (gdrcd_query($result, 'num_rows')>0) {
            while ($record=gdrcd_query($result, 'fetch')) {
                $vicinato_arr = array();
                $vicinato_arr['link']= "main.php?page=mappaclick&map_id=".$record;
                $vicinato_arr['nome'] = $record['nome'];
                $response['vicinato'][] = $vicinato_arr;
            }
            gdrcd_query($result, 'free');
        } else {
            $response['vicinato'][] = $MESSAGE['interface']['maps']['no_more'];
        }
    } else {
        $response['page_title'] = gdrcd_filter('out', $MESSAGE['interface']['maps']['traveling']);
    }
    if ($_SESSION['permessi']>=GAMEMASTER) {
        if ($mobile==1) {
            $form_gioco = array();
            $form_gioco['action'] = "main.php?page=mappaclick&map_id=".$_SESSION['mappa'];
            if ($vicinato!=INVIAGGIO) {
                $form_gioco['destination'] = INVIAGGIO;
            } else {
                $result=gdrcd_query("SELECT posizione, nome FROM mappa_click WHERE posizione <> -1 AND id_click <> ".$_SESSION['mappa']." ORDER BY nome", 'result');
                
                if (gdrcd_query($result, 'num_rows')>0) {
                    $form_gioco['destination'][] = gdrcd_filter('out', $record['nome']);
                    gdrcd_query($result, 'free');
                } else {
                    $form_gioco['destination'][] = 0;
                }
            }
        }
        if ($PARAMETERS['mode']['auto_meteo']=='OFF') {
            $form_gioco = array();
            $form_gioco['action'] = "main.php?page=mappaclick&map_id=". $_SESSION['mappa'];
            $form_gioco['temperature'] = Array();
			for ($i=45; $i>=-45; $i--) {
                $form_gioco['temperature'][] = $i;
            }
            $form_gioco['climate'] = Array();
            foreach ($MESSAGE['interface']['meteo']['status'] as $climate) {
                $form_gioco['climate'][] = $climate;
            }
        }
        $response['form_gioco'] = $form_gioco;
    }
}
$main_response['mappa'] = $response;