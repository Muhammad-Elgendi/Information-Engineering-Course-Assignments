<?php

require_once "des.php";

// Render as JSON 
header('Content-Type: application/json');

// Perpare data variables
$key = $_POST['key'];
$plain = $_POST['plain'];
$cipher = $_POST["cipher"];

if(empty($cipher) && !empty($plain)){
    // Encryption request

    // $key ='0001001100110100010101110111100110011011101111001101111111110001';
    // $data ='0000000100100011010001010110011110001001101010111100110111101111';
    $myDes = new Des($key,$plain);
    $myDes->generateSubKeys()->encryptData();
    echo json_encode($myDes->encryptedData);
}
if(empty($plain) && !empty($cipher)){
    // Decryption request

}