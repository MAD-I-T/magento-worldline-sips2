<?php

namespace Madit\Sips2\Block\Info;

class Standard extends \Magento\Payment\Block\Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Madit_Sips2::sips2/info/standard.phtml');
    }

    /**
     * Retrieve payment info model
     *
     * @return \Magento\Payment\Model\Info|false
     */
    public function getPaymentInfo()
    {
        // TODO: Implement getPaymentInfo() method.
    }
}
