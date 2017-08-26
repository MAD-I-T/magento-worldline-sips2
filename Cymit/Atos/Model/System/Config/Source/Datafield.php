<?php
namespace Cymit\Atos\Model\System\Config\Source;

class Datafield implements \Magento\Framework\Option\ArrayInterface
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

    public function toOptionArray()
    {
        $options = array();

        foreach ($this->atosConfig->getDataFieldKeys() as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

}