<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Madit\Atos\Model\Ui;

use Madit\Atos\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'atos_standard';

    protected $_config;

    /*
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        \Madit\Atos\Gateway\Config\Config $config,
        SessionManagerInterface $session
    ) {
        $this->_config = $config;
        $this->session = $session;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        return [
            'payment' => [
                self::CODE => [
                    'merchantId' => $this->_config->getMerchantId(),

                ]
            ]
        ];
    }
}
