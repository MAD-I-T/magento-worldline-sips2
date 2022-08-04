<?php
namespace Madit\Sips2\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'sips2_standard';

    /**
     * @var \Madit\Sips2\Model\Config
     */
    protected $_config;

    /*
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * ConfigProvider constructor.
     * @param \Madit\Sips2\Model\Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        \Madit\Sips2\Model\Config $config,
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
