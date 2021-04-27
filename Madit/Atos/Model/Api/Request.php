<?php
namespace Madit\Atos\Model\Api;

use Madit\Atos\Model\Api\SIPS2\Utils;

class Request
{
    protected $logger;
    protected $utils;
    protected $_config;


    /**
     * Request constructor.
     * @param \Madit\Atos\Model\Api\SIPS2\Utils $utils
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Atos\Model\Config $config
     */
    public function __construct(
        \Madit\Atos\Model\Api\SIPS2\Utils $utils,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Atos\Model\Config $config
    )
    {
        $this->logger = $logger;
        $this->utils = $utils;
        $this->_config = $config;
    }

    public function doRequest($parameters, $binPath, $sipsVersion = 1)
    {

        $sips_result ="";
        $sips_values = "";
        $paysageJsonUrl = "";
        if($sipsVersion == 2){


            $secretKey = $this->_config->getconfigdata("secret_key", "atos_standard");
            $sealAlgorithm = $this->_config->getconfigdata("seal_algorithm", "atos_standard");
            $parameters['seal'] = $this->utils->computePaymentInitSeal(
                $sealAlgorithm,
                $parameters,
                $secretKey
            );
            $parameters["keyVersion"] = $this->_config->getConfigData("secret_key_version", "atos_standard");
            $parameters["sealAlgorithm"] = $sealAlgorithm;

            $requestJson = json_encode($parameters, JSON_UNESCAPED_UNICODE, '512');

            //SENDING OF THE PAYMENT REQUEST

            $option = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "content-type: application/json",
                    'content' => $requestJson
                ),
            );
            $context = stream_context_create($option);
            $paysageJsonUrl = $this->_config->getConfigData("paysage_json_url", "atos_standard");
            $responseJson = file_get_contents($paysageJsonUrl, false, $context);
            $responseArray = json_decode($responseJson, true);

           // if(!array_key_exists('seal',$responseArray)){

           //     $sips_values = array(
           //         1 => $responseArray['redirectionStatusCode'],
           //         2 => $responseArray['redirectionStatusCode'].$responseArray['redirectionStatusMessage'],
           //         3 => $responseArray['redirectionStatusMessage'],
           //         4 => $responseArray
           //     );
           // }
            $receivedSeal = $responseArray['seal'] ?? '';
            unset($responseArray['seal']);
            $calculatedSeal = $this->utils->computePaymentInitSeal($sealAlgorithm, $responseArray, $secretKey);

            if(strcmp($calculatedSeal, $receivedSeal) == 0) {

                $sips_values = array(
                    1 => 00,
                    2 => "",
                    3 => $responseArray['redirectionStatusMessage'],
                    4 => $responseArray
                );
            }else{

                $responseArray['redirectionStatusCode'] = 34;
                $errorFieldName =  $responseArray["errorFieldName"] ?? '';
                echo "error".print_r($responseArray,1);
                $sips_values = array(
                    1 => $responseArray['responseCode'] ?? 34,
                    2 => ($responseArray['responseCode'] ?? '') .' '. $errorFieldName,
                    3 => $errorFieldName,
                    4 => $responseArray
                );
            }


        } else {
            $sips_result = shell_exec("$binPath $parameters");
            //On separe les differents champs et on les met dans une variable tableau
            $sips_values = explode('!', $sips_result);
        }

        // Récupération des paramètres
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
