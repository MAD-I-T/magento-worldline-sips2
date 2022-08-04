<?php
namespace Madit\Sips2\Model\System\Config\Source;

class Datafield implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Madit\Sips2\Model\Config
     */
    protected $sips2Config;

    /**
     * Datafield constructor.
     *
     * @param \Madit\Sips2\Model\Config $config
     */
    public function __construct(
        \Madit\Sips2\Model\Config $config
    ) {
        $this->sips2Config = $config;
    }

    /**
     * Transform data to array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->sips2Config->getDataFieldKeys() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $options;
    }
}
