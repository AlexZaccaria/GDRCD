<?php

$index_notheme_response = Array();

$index_notheme_response['users'] = Array();
$index_notheme_response['users']['online'] = $users['online'];
$index_notheme_response['users']['message'] = gdrcd_filter('out', $MESSAGE['homepage']['forms']['online_now']);
    
include 'themes/notheme/home/user_stats.php';
$index_notheme_response['user_stats'] = $user_stats_notheme_response;

if (file_exists('themes/'. $PARAMETERS['themes']['current_theme'] .'/home/' . $content . '.php')) {
    include 'themes/'. $PARAMETERS['themes']['current_theme'] .'/home/' . $content . '.php';
}
