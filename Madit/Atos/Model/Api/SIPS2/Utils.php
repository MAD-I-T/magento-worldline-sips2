<?php
namespace Madit\Atos\Model\Api\SIPS2;

class Utils
{
    protected $logger;

     private $singleDimArray = array();



    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function computePaymentInitSeal($sealAlgorithm, $data, $secretKey)
    {
        $dataStr = $this->flatten($data);

        //echo print_r("flatten str ".$dataStr,1);
        return $this->computeSealFromString($sealAlgorithm, $dataStr, $secretKey, true);
    }

    public function computePaymentResponseSeal($sealAlgorithm, $data, $secretKey)
    {
        return $this->computeSealFromString($sealAlgorithm, $data, $secretKey, false);
    }

    private function computeSealFromString($sealAlgorithm, $data, $secretKey, $hmac256IsDefault)
    {
        if (strcmp($sealAlgorithm, "HMAC-SHA-256") == 0){
            $hmac256 = true;
        }elseif(empty($sealAlgorithm)){
            $hmac256 = $hmac256IsDefault;
        }else{
            $hmac256 = false;
        }
        return $this->computeSeal($hmac256, $data, $secretKey);
    }

    private function computeSeal($hmac256, $data, $secretKey)
    {
        $serverEncoding = mb_internal_encoding();
        $dataUtf8 = "";
        $secretKeyUtf8 = "";
        $seal = "";

        if(strcmp($serverEncoding, "UTF-8") == 0){
            $dataUtf8 = $data;
            $secretKeyUtf8 = $secretKey;
            //echo print_r("utf8".$data,1);
        }else{
            $dataUtf8 = iconv($serverEncoding, "UTF-8", $data);
            $secretKeyUtf8 = iconv($serverEncoding, "UTF-8", $secretKey);
            //echo print_r("utf8 converted".$dataUtf8,1);
        }
        if($hmac256){
            $seal = hash_hmac('sha256', $dataUtf8, $secretKeyUtf8);

            //echo print_r("hmac converted".$seal,1);
        }else{
            $seal = hash('sha256',  $secretKeyUtf8);

            //echo print_r("sha256 converted".$seal,1);
        }
        return $seal;
    }

    public function flatten($multiDimArray)
    {
        $sortedMultiDimArray = $this->recursiveTableSort($multiDimArray);
        array_walk_recursive($sortedMultiDimArray, function ($value, $key){$this->valueResearch($value, $key);});
        $string = implode("", $this->singleDimArray);
        $this->singleDimArray = array();
        return $string;
    }

//Alphabetical order of field names in the table

    public function recursiveTableSort($table)
    {
        ksort($table);
        foreach($table as $key => $value)
        {
            if(is_array($value)){
                $value = $this->recursiveTableSort($value);
                $table[$key] = $value;
            }
        }
        return $table;
    }


    private function valueResearch($value, $key): array
    {
        $this->singleDimArray[] = $value;
        return $this->singleDimArray;
    }

    public function extractDataFromThePaymentResponse($data): array
    {
        $responseData = [];
        $singleDimArray = explode("|", $data);

        foreach($singleDimArray as $value)
        {
            $fieldTable = explode("=", $value);
            $key = $fieldTable[0];
            $value = $fieldTable[1];
            $responseData[$key] = $value;
            unset($fieldTable);
        }
        return $responseData;
    }

}

