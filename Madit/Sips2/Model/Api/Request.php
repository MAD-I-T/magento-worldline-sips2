<?php
namespace Madit\Sips2\Model\Api;

use Madit\Sips2\Model\Api\SIPS2\Utils;

class Request
{
    protected $logger;
    protected $utils;
    protected $_config;

    /**
     * Request constructor.
     *
     * @param \Madit\Sips2\Model\Api\SIPS2\Utils $utils
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Sips2\Model\Config $config
     */
    public function __construct(
        \Madit\Sips2\Model\Api\SIPS2\Utils $utils,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Sips2\Model\Config $config
    ) {
        $this->logger = $logger;
        $this->utils = $utils;
        $this->_config = $config;
    }

    /**
     * Make call to api
     *
     * @param string|array $parameters
     * @param string $binPath
     * @param int $sipsVersion
     * @return array
     */
    public function doRequest($parameters, $binPath, $sipsVersion = 1)
    {

        $sips_result ="";
        $sips_values = "";
        $paysageJsonUrl = "";

        $secretKey = $this->_config->getConfigData("secret_key", "sips2_standard/default");
        $sealAlgorithm = $this->_config->getConfigData("seal_algorithm", "sips2_standard/default");
        $parameters['seal'] = $this->utils->computePaymentInitSeal(
            $sealAlgorithm,
            $parameters,
            $secretKey
        );
        $parameters["keyVersion"] = $this->_config->getConfigData("secret_key_version", "sips2_standard/default");
        $parameters["sealAlgorithm"] = $sealAlgorithm;

        $requestJson = json_encode($parameters, JSON_UNESCAPED_UNICODE, '512');

        //SENDING OF THE PAYMENT REQUEST

        $option = [
            'http' => [
                'method' => 'POST',
                'header' => "content-type: application/json",
                'content' => $requestJson
            ],
        ];

        //@codingStandardsIgnoreStart
        $context = stream_context_create($option);
        $paysageJsonUrl = $this->_config->getConfigData("sips_test_mode") == 1?
            $this->_config->getConfigData("paysage_json_url_test"):
            $this->_config->getConfigData("paysage_json_url");
        $responseJson = file_get_contents($paysageJsonUrl, false, $context);
        //@codingStandardsIgnoreEnd
        $responseArray = json_decode($responseJson, true);

        $receivedSeal = $responseArray['seal'] ?? '';
        unset($responseArray['seal']);
        $calculatedSeal = $this->utils->computePaymentInitSeal($sealAlgorithm, $responseArray, $secretKey);

        if (strcmp($calculatedSeal, $receivedSeal) == 0) {

            $sips_values = [
                1 => 00,
                2 => "",
                3 => $responseArray['redirectionStatusMessage'],
                4 => $responseArray
            ];
        } else {

            $responseArray['redirectionStatusCode'] = 34;
            $errorFieldName =  $responseArray["errorFieldName"] ?? '';
            //echo "error".print_r($responseArray,1);
            $sips_values = [
                1 => $responseArray['responseCode'] ?? 34,
                2 => ($responseArray['responseCode'] ?? '') .' '. $errorFieldName,
                3 => $errorFieldName,
                4 => $responseArray
            ];
        }

        $sips = [
            'code' => $sips_values[1],
            'error' => $sips_values[2],
            'message' => $sips_values[3],
            'command' => $sipsVersion == 1? "$binPath $parameters": $paysageJsonUrl,
            'sips_version' => $sipsVersion,
            'output' => $sips_values[4]
        ];
        //echo "SIPS from requests".print_r( $sips,1)."END OF Requests --->";

        if (!isset($sips['code'])) {
            $this->logger->critical(new \Exception($sips_result));
        }

        if ($sips['code'] == '-1') {
            $this->logger->critical(new \Exception($sips['error']));
        }

        return $sips;
    }
}
