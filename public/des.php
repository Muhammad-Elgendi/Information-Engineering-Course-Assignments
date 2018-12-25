<?php
/**
 * Data Encryption Standard implemented class
 * © Muhammad Elgendi
 * Date : 24/12/2018
 */

 /**
  * Des class usage :
     1- intiate constractor from binary-formated data
        $myDes = new Des($key_in_bin,$data_in_bin);
     2- intiate constractor from hex-formated data
        $myDes = (new Des)->setDataHex($data_in_hex)->setKeyHex($key_in_hex);
    you can chain the methods calls

  */
$key ='0001001100110100010101110111100110011011101111001101111111110001';
$data ='0000000100100011010001010110011110001001101010111100110111101111';
$myDes = new Des($key,$data);
$myDes->generateSubKeys()->encryptData();
echo $myDes->encryptedData."\n";
 

class Des{

    /**
     * Array of Key,data,subKeys,encryptedData
     */
    public $key;
    public $data;
    public $subKeys;
    public $encryptedData;

    function __construct($key = null,$data = null){
        $this->key = $key;
        $this->data = $data;
    }

    function setKeyHex($hex){
        $this->key = hex2bin($hex);
        return $this;
    }

    function setDataHex($hex){
        $this->data = hex2bin($hex);
        return $this;
    }

    function encryptData(){
        // read data into an array
        $dataArray = str_split($this->data);    

        // apply pc-1 permutation matrix
        $ip = $this->readFromCSV('matrices/IP.csv');
        $permutedData = array();
        foreach ($ip as $line) {
            foreach($line as $num){
                $permutedData[] = $dataArray[(int)$num - 1]; 
            }
        }

        // split permuted data into L0 -->0 and R0 -->1
        $permutedData = array_chunk($permutedData, 32);

        // Des round
        for($i = 1; $i <= 16 ;$i++){
            if($i == 1){
                $left = $permutedData[1];
                $right = $this->applyXor($permutedData[0],$this->mangler($i,$permutedData[1]));
            }
            else{
                $oldLeft = $left;
                $left = $right;
                $right = $this->applyXor($oldLeft,$this->mangler($i,$right));
            }
        }

        // reverse order of L16 and R16 to be R16L16
        $reversedOrder = array_merge($right,$left);

        // apply inverse of intail permutation matrix        
        $inverse_ip = $this->readFromCSV('matrices/inverse-ip.csv');
        $permutedData = array();
        foreach ($inverse_ip as $line) {
            foreach($line as $num){
                $permutedData[] = $reversedOrder[(int)$num - 1]; 
            }
        }
        $this->encryptedData =implode("",$permutedData);
        return $this;
    }    

    function generateSubKeys(){
        // read key into an array
        $keyArray = str_split($this->key);    
        
        // apply pc-1 permutation matrix
        $pc1 = $this->readFromCSV('matrices/pc-1.csv');
        $keyPlus = array();
        foreach ($pc1 as $line) {
            foreach($line as $num){
                $keyPlus[] = $keyArray[(int)$num - 1]; 
            }
        }

        // split permuted Key into c0 and d0
        $permutedKey = array_chunk($keyPlus, 28);
        $c0 = $permutedKey[0];
        $d0 = $permutedKey[1];
        $shift_values = [1,1,2,2,2,2,2,2,1,2,2,2,2,2,2,1];

        // Create C(n) and D(n) and apply shift
        $cds= array();        
        for($i =0 ;$i < 16;$i++){
            if($i==0){
                $cds[1] = array($this->shiftLeftDes($c0,$shift_values[$i]),$this->shiftLeftDes($d0,$shift_values[$i]));
            }
            else{
                $cds[1+$i] = array($this->shiftLeftDes($cds[$i][0],$shift_values[$i]),$this->shiftLeftDes($cds[$i][1],$shift_values[$i]));
            }            
        }

        // apply pc-2 permutation matrix
        $pc2 = $this->readFromCSV('matrices/pc-2.csv');
        $subKeys = array();
        for($i =1;$i<=16 ;$i++){

            // Generate concatenated pairs of CnDn
            $concatenatedPair = array_merge($cds[$i][0], $cds[$i][1]);

            $tempSubKey = array();

            foreach ($pc2 as $line) {
                foreach($line as $num){             
                    array_push($tempSubKey, $concatenatedPair[(int)$num - 1]);
                }
            }

            $subKeys[$i] = $tempSubKey;
        }
        $this->subKeys = $subKeys;
        return $this;
    }

    private function mangler($roundNumber,$previousRight){
        $expandedRight = $this->expandRight($previousRight);

        // apply xor between key(n) and expanded Right
        $exoredWithKey = $this->applyXor($this->subKeys[$roundNumber],$expandedRight);

        // split into 8 blocks 
        $blocks = array_chunk($exoredWithKey,6);

        // load sboxs
        $sboxs = array();
        for($i = 1 ; $i <= 8; $i++){
            
            $sbox = $this->readFromCSV("matrices/sbox/$i.csv");
            $sboxLine = array();
            $sboxArray = array();
            foreach ($sbox as $line) {
                foreach($line as $num){
                    $sboxLine[] = (int)$num; 
                }
                $sboxArray[]= $sboxLine;
                $sboxLine = array();
            }
            $sboxs[$i] = $sboxArray;
            $sboxArray = array();          
        }

        // apply corresponding sbox
        $sboxOutput = array();
        foreach($blocks as $key => $block){
            $row = bindec($block[0]."".$block[5]);
            $column = bindec($block[1]."".$block[2]."".$block[3]."".$block[4]);
            $output = decbin($sboxs[$key+1][$row][$column]);
            $sboxOutput = array_merge($sboxOutput,str_split(str_pad($output,4,"0",STR_PAD_LEFT)));
        }

        // apply P permutation matrix
        $p = $this->readFromCSV('matrices/P.csv');
        $permutedData = array();
        foreach ($p as $line) {
            foreach($line as $num){
                $permutedData[] = $sboxOutput[(int)$num - 1]; 
            }
        }
        return $permutedData;
    }

    private function applyXor($a,$b){
        // apply xor between two operands
        $output = array();
        foreach($a as $key => $num){
            $output[] = (int)((bool)$num xor (bool)$b[$key]);
        }
        return $output;
    }

    private function expandRight($right){
        // apply E-bit-selection-table
        $selection = $this->readFromCSV('matrices/E-bit-selection-table.csv');
        $expandedRight = array();
        foreach ($selection as $line) {
            foreach($line as $num){
                $expandedRight[] = $right[(int)$num - 1]; 
            }
        }
        return $expandedRight;
    }

    private function readFromCSV($path){
        $csvFile = file($path);
        $data = [];
        foreach ($csvFile as $line) {
            $data[] = str_getcsv($line);
        }
        return $data;
    }

    private function shiftLeftDes($a,$num_of_shift){
        for($i = 0; $i < $num_of_shift; $i++){
            $temp = $a[0];
            array_shift($a);
            array_push($a,$temp);
        }
        return $a;
    }
}