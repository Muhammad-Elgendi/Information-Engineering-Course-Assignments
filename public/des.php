<?php
/**
 * Data Encryption Standard implemented class
 * © Muhammad Elgendi
 * Date : 24/12/2018
 */
$key ='0001001100110100010101110111100110011011101111001101111111110001';
$data = null;
$myDes = new Des($key,$data);
$myDes->generateSubKeys();

class Des{

    public $key;
    public $data;
    public $subKeys;

    function __construct($key,$data = null){
        $this->key = $key;
        $this->data = $data;
    }

    function generateSubKeys(){
        // read key to an array
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

        // Create C(n) and D(n)
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