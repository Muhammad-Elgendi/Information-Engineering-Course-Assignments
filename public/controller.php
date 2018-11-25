<?php

require_once '../vendor/autoload.php';
use Phpml\Math\Matrix;

// Render as JSON
header('Content-Type: application/json');

// Perpare data variables
$key = strtoupper($_POST['key']);
$plain = strtoupper($_POST['plain']);
$cipher = strtoupper($_POST["cipher"]);
$column_count = strlen($key);
if(empty($cipher) && !empty($plain)){
    // Encryption request
    $row_count = ceil(strlen($plain)/strlen($key));
    
    if(strlen($plain) !== $row_count*$column_count){

        // last Row not full        
        $count_empty_cell = ($row_count*$column_count) - strlen($plain);

        // create array for filler characters
        $fillers = "";
        for ($i = 65 ; $i <= (65+$count_empty_cell); $i++){
            $fillers .= chr($i);
        }

        // add fillers to plain text
        $plain .= $fillers;
    }

    // create matrix of table content
    $matrix=  array();
    $counter = 0;
    foreach (range(1,$row_count) as $row) {
        foreach (range(1,$column_count) as $col) {
            $matrix[$row][$col] = $plain[$counter];
            $counter++;
        }
    }

    // rank the characters in the key
    $order = array();
    for($i =0 ; $i < strlen($key) ; $i++){
        $order[$key[$i]] = ord($key[$i]) - 64;
    }

    // sort order depend on value
    asort($order);

    // perpare cipher text
    $output_cipher = "";
    foreach($order as $char => $rank){
        $col = strpos($key,$char) + 1;
        $output_cipher.= getColumnString($matrix,$row_count,$col);
    }

    // echo json_encode($output_cipher);
    echo ($output_cipher);
}

if(empty($plain) && !empty($cipher)){
    // Decryption request
    $row_count = ceil(strlen($cipher)/strlen($key));

    // create matrix of table content
    // $matrix=  array();
    // $counter = 0;
    // foreach (range(1,$row_count) as $row) {
    //     foreach (range(1,$column_count) as $col) {
    //         $matrix[$row][$col] = $cipher[$counter];
    //         $counter++;
    //     }
    // }

    // rank the characters in the key
    $order = array();
    for($i =0 ; $i < strlen($key) ; $i++){
        $order[$key[$i]] = ord($key[$i]) - 64;
    }

    echo json_encode($order);
    // echo ($order);

}


function getColumnString($matrix,$rows_count,$column){

    $values = "";
    for ($i = 1; $i <= $rows_count; ++$i) {
        $values.= $matrix[$i][$column];
    }

    return $values;
}

function setColumnString($matrix,$rows_count,$column,$value){

    for ($i = 1; $i <= $rows_count; ++$i) {
        $matrix[$i][$column] = $value[$i];
    }

}


