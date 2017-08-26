<?php
namespace Cymit\Atos\Block\Form;
use Magento\Payment\Block\Form;
class Standard extends Form
{

    protected function _construct()
    {
        $this->setTemplate('Cymit_Atos::atos/form/standard.phtml');
        parent::_construct();
    }

    public function getCreditCardsAccepted()
    {
        return ['CB','VISA','MASTERCARD'];
        //explode(',', Mage::getStoreConfig('payment/atos_standard/cctypes'));
    }

}
