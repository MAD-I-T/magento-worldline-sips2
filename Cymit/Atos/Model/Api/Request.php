<?php
namespace Cymit\Atos\Model\Api;
class Request
{


    protected $logger;
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function doRequest($parameters, $binPath)
    {
        $sips_result = shell_exec("$binPath $parameters");

        //On separe les differents champs et on les met dans une variable tableau
        $sips_values = explode('!', $sips_result);

        // Récupération des paramètres
        $sips = array(
            'code' => $sips_values[1],
            'error' => $sips_values[2],
            'message' => $sips_values[3],
            'command' => "$binPath $parameters",
            'output' => $sips_result
        );

        if (!isset($sips['code'])) {
            $this->logger->critical(new \Exception($sips_result));
        }

        if ($sips['code'] == '-1') {
            $this->logger->critical(new \Exception($sips['error']));
        }

        return $sips;
    }

}
