<?php

namespace Cymit\Atos\Model\System\Config\Source;

class PaymentActions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Cymit\Atos\Model\Config
     */
    protected $atosConfig;

    /**
     * Datafield constructor.
     * @param \Cymit\Atos\Model\Config $config
     */
    public function __construct(
        \Cymit\Atos\Model\Config $config
    )
    {
        $this->atosConfig = $config;
    }



    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $configModel = $this->atosConfig;
        return $configModel->getPaymentActions();
    }

}