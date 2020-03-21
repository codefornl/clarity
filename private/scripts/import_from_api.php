<?php

$json = file_get_contents('https://cbase.codefor.nl/cbases');
$data = json_decode($json, true);

foreach ($data['_embedded']['cbase'] as $cbase) {
    // sql insert usecase
    var_dump($cbase);
    foreach ($cbase['_embedded']['usecase'] as $usecase) {
        // sql insert usecase
        var_dump($usecase);
        exit();
    }
}
