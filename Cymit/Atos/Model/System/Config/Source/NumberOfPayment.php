<?php

namespace Cymit\Atos\Model\System\Config\Source;

class NumberOfPayment implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        $options = array(
            array('value' => 2, 'label' => '2'),
            array('value' => 3, 'label' => '3'),
            array('value' => 4, 'label' => '4')
        );

        return $options;
    }

}