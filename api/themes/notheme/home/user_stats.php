<?php

$user_stats_notheme_response = Array();

/** * Raccolta statistiche sito */
$site_activity 			= gdrcd_query("SELECT MIN(data_iscrizione) AS date_of_activity FROM personaggio");
$registered_users 		= gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio");
$banned_users 			= gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE esilio > NOW()");
$master_users			= gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE permessi = ". GAMEMASTER);
$admin_users			= gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE permessi >= ". MODERATOR);
$weekly_posts			= gdrcd_query("SELECT COUNT(id_messaggio) AS num FROM messaggioaraldo WHERE data_messaggio > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_actions			= gdrcd_query("SELECT COUNT(id) AS num FROM chat WHERE ora > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_signup			= gdrcd_query("SELECT COUNT(nome) AS num FROM personaggio WHERE data_iscrizione > DATE_SUB(NOW(), INTERVAL 7 DAY)");

$user_stats_notheme_response['site_activity'] = gdrcd_format_date($site_activity['date_of_activity']);
$user_stats_notheme_response['registered_users'] = $registered_users['num'];
$user_stats_notheme_response['banned_users'] = $banned_users['num'];
$user_stats_notheme_response['master_users'] = $master_users['num'];
$user_stats_notheme_response['admin_users'] = $admin_users['num'];
$user_stats_notheme_response['weekly_posts'] = $weekly_posts['num'];
$user_stats_notheme_response['weekly_actions'] = $weekly_actions['num'];
$user_stats_notheme_response['weekly_signup'] = $weekly_signup['num'];

$user_stats_notheme_response['page_name'] = gdrcd_filter('out',$MESSAGE['interface']['user']['stats']['page_name']);