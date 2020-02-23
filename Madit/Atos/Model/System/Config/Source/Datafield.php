<?php
namespace Madit\Atos\Model\System\Config\Source;

class Datafield implements \Magento\Framework\Option\ArrayInterface
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

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->atosConfig->getDataFieldKeys() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $options;
    }
}
