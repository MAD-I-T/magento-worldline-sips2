<?php

namespace Madit\Atos\Model\System\Config\Source;

class SIPSVersions implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var \Madit\Atos\Model\Config
     */
    protected $atosConfig;

    /**
     * Datafield constructor.
     * @param \Madit\Atos\Model\Config $config
     */
    public function __construct(
        \Madit\Atos\Model\Config $config
    ) {
        $this->atosConfig = $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $configModel = $this->atosConfig;
        return $configModel->getSIPSVersionOptions();
    }
}

