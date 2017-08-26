<?php

namespace Cymit\Atos\Block\Info;
class Standard extends \Magento\Payment\Block\Info\AbstractContainer
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Cymit_Atos::atos/info/standard.phtml');
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
