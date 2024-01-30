<?php

use GuzzleHttp\Exception\GuzzleException;
use Nekhbet\admetSARService\admetSAR;
use Nekhbet\admetSARService\Exceptions\admetSARException;

include(__DIR__.'/../vendor/autoload.php');

$api = new admetSAR();
try {
    $id_job = $api
        ->setSMILESCode('Cc1cc(O)c2C(=O)c3c(O)cc(O)c4c3c3c2c1c1c2c3c3c4c(O)cc(O)c3C(=O)c2c(O)cc1C')
        ->submitJob();
    echo 'Job ID: '.$id_job."\n";
    print_r($api->parseJobResults($id_job));
} catch (GuzzleException $e) {
    die("Connection Exception: ".$e->getMessage());
} catch (admetSARException $e) {
    die("LIB Exception: ".$e->getMessage());
}