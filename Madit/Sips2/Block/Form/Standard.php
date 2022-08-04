<?php
namespace Madit\Sips2\Block\Form;

use Magento\Payment\Block\Form;

class Standard extends Form
{

    /*
     *@var \Madit\Sips2\Model\Config
     */
    //protected $_config;

    /*
    protected function _construct(
        \Madit\Sips2\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {

        $this->_config = $config;
        $this->setTemplate('Madit_Sips2::sips2/form/standard.phtml');
        parent::_construct($context , $data);
    }
    */

    public function getCreditCardsAccepted()
    {
        //return ['CB','VISA','MASTERCARD'];
        //return explode(',', Mage::getStoreConfig('payment/sips2_standard/cctypes'));
        return explode(',', $this->_config->getConfigData('cctypes', 'sips2_standard/default'));
    }
}
