<?php
namespace Madit\Sips2\Model\Api\SIPS2;

class Utils
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /*
     * simple array
     */
    private $singleDimArray = [];

    /**
     * Utils constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Compute seal
     *
     * @param string $sealAlgorithm
     * @param array|string $data
     * @param string $secretKey
     * @return false|string
     */
    public function computePaymentInitSeal($sealAlgorithm, $data, $secretKey)
    {
        $dataStr = $this->flatten($data);

        //echo print_r("flatten str ".$dataStr,1);
        return $this->computeSealFromString($sealAlgorithm, $dataStr, $secretKey, true);
    }

    /**
     * Compute responseSeal
     *
     * @param string $sealAlgorithm
     * @param array|string $data
     * @param string $secretKey
     * @return false|string
     */
    public function computePaymentResponseSeal($sealAlgorithm, $data, $secretKey)
    {
        return $this->computeSealFromString($sealAlgorithm, $data, $secretKey, false);
    }

    /**
     * Compute seal from string
     *
     * @param string $sealAlgorithm
     * @param array|string $data
     * @param string $secretKey
     * @param bool $hmac256IsDefault
     * @return false|string
     */
    private function computeSealFromString($sealAlgorithm, $data, $secretKey, $hmac256IsDefault)
    {
        $hmac256 = '';
        if (strcmp($sealAlgorithm, "HMAC-SHA-256") == 0) {
            $hmac256 = true;
        } elseif (empty($sealAlgorithm)) {
            $hmac256 = $hmac256IsDefault;
        } else {
            $hmac256 = false;
        }
        return $this->computeSeal($hmac256, $data, $secretKey);
    }

    /**
     * Compute seal
     *
     * @param string $hmac256
     * @param array|string $data
     * @param string $secretKey
     * @return false|string
     */
    private function computeSeal($hmac256, $data, $secretKey)
    {
        $serverEncoding = mb_internal_encoding();
        $dataUtf8 = "";
        $secretKeyUtf8 = "";
        $seal = "";

        if (strcmp($serverEncoding, "UTF-8") == 0) {
            $dataUtf8 = $data;
            $secretKeyUtf8 = $secretKey;
        } else {
            $dataUtf8 = iconv($serverEncoding, "UTF-8", $data);
            $secretKeyUtf8 = iconv($serverEncoding, "UTF-8", $secretKey);
        }
        if ($hmac256) {
            $seal = hash_hmac('sha256', $dataUtf8, $secretKeyUtf8);
        } else {
            $seal = hash('sha256', $secretKeyUtf8);
        }
        return $seal;
    }

    /**
     * Flatten array
     *
     * @param array $multiDimArray
     * @return string
     */
    public function flatten($multiDimArray)
    {
        $sortedMultiDimArray = $this->recursiveTableSort($multiDimArray);
        array_walk_recursive($sortedMultiDimArray, function ($value, $key) {
            $this->valueResearch($value, $key);
        });
        $string = implode("", $this->singleDimArray);
        $this->singleDimArray = [];
        return $string;
    }

    /**
     * Alphabetical order of field names in the table
     *
     * @param array $table
     * @return mixed
     */
    public function recursiveTableSort($table)
    {
        ksort($table);
        foreach ($table as $key => $value) {
            if (is_array($value)) {
                $value = $this->recursiveTableSort($value);
                $table[$key] = $value;
            }
        }
        return $table;
    }

    /**
     * Search value
     *
     * @param string $value
     * @param mixed $key
     * @return array
     */
    private function valueResearch($value, $key): array
    {
        $this->singleDimArray[] = $value;
        return $this->singleDimArray;
    }

    /**
     * Extract data from response
     *
     * @param array|string $data
     * @return array
     */
    public function extractDataFromThePaymentResponse($data): array
    {
        $responseData = [];
        $singleDimArray = explode("|", $data);

        foreach ($singleDimArray as $value) {
            $fieldTable = explode("=", $value);
            $key = $fieldTable[0];
            $value = $fieldTable[1];
            $responseData[$key] = $value;
            unset($fieldTable);
        }
        return $responseData;
    }
}
