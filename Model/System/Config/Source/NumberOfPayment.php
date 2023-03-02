<?php

namespace Madit\Sips2\Model\System\Config\Source;

class NumberOfPayment implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Payment method to array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 2, 'label' => '2'],
            ['value' => 3, 'label' => '3'],
            ['value' => 4, 'label' => '4']
        ];

        return $options;
    }
}
