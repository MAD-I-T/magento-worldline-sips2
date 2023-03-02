<?php

namespace Madit\Sips2\Model\System\Config\Source;

class  TestMode implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var \Madit\Sips2\Model\Config
     */
    protected $sips2Config;

    /**
     * Datafield constructor.
     * @param \Madit\Sips2\Model\Config $config
     */
    public function __construct(
        \Madit\Sips2\Model\Config $config
    ) {
        $this->sips2Config = $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $configModel = $this->sips2Config;
        return $configModel->getTestModeOptions();
    }
}
