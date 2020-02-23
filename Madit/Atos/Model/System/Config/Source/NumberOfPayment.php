<?php

namespace Madit\Atos\Model\System\Config\Source;

class NumberOfPayment implements \Magento\Framework\Option\ArrayInterface
{
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
