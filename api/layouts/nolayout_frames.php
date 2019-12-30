<?php

$modules = array();

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
$main_response['modules'] = $modules;
