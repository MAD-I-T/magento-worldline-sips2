<?php
namespace Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment;

class Cctype implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * CB labels
     *
     * @return \string[][]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'CB', 'label' => 'CB'],
            ['value' => 'VISA', 'label' => 'Visa'],
            ['value' => 'MASTERCARD', 'label' => 'MasterCard'],
            ['value' => 'AMEX', 'label' => 'Amex']
        ];

        return $options;
    }

    /**
     * CB possible values
     *
     * @return string[]
     */
    public function getCardValues()
    {
        return ['CB', 'VISA', 'MASTERCARD', 'AMEX'];
    }
}
