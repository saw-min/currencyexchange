<?php

$ratedata = simplexml_load_file('rates.xml');
$cur = $_REQUEST['cur'];
$action = $_REQUEST['action'];

parse_str(file_get_contents('php://input'), $_DELETE);

if($action=='DELETE'){
    foreach ($ratedata->currency as $currency){
        if($currency->code == $cur){
            
        }
    }

}