<?php

$ratedata = simplexml_load_file('rates.xml');
$from = $_REQUEST['from'];
$to = $_REQUEST['to'];
$amount= $_REQUEST['amnt'];

foreach ($ratedata->currency as $code){

    if($code->code == $from){
        $fromcode =$from;
        $fromrate = $code['rate'];
        echo ($fromcode);
        echo ($fromrate);
        echo ('<br>');
    }
    else if($code->code == $to){
        $tocode = $to;
        $torate = $code['rate'];
        echo ($tocode);
        echo ($torate);
        echo ('<br>');
    }
}

if ($from=='GBP'){
    $exchangerate = $amount * $torate;
}

else if ($to == 'GBP'){
    $exchangerate = $amount / $torate;
}

else {
$ratetobase = $fromrate / $amount ;
$exchangerate = $torate * $ratetobase;
}

echo ($exchangerate);








