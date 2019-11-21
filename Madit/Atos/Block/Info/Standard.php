<?php

namespace Madit\Atos\Block\Info;
class Standard extends \Magento\Payment\Block\Info
{

    protected function _construct()
    {
        parent::_construct();
        //$this->setTemplate('Madit_Atos::atos/Info/standard.phtml');
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
