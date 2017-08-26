<?php
namespace Cymit\Atos\Model\Adminhtml\System\Config\Source\Payment;

class Cctype implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        $options = array(
            array('value' => 'CB', 'label' => 'CB'),
            array('value' => 'VISA', 'label' => 'Visa'),
            array('value' => 'MASTERCARD', 'label' => 'MasterCard'),
            array('value' => 'AMEX', 'label' => 'Amex')
        );

        return $options;
    }

    public function getCardValues()
    {
        return array('CB', 'VISA', 'MASTERCARD', 'AMEX');
    }

}
