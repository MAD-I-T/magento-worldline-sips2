<?php
namespace Cymit\Atos\Block\Adminhtml\System\Config\Form\Field;

class Ips extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    public function __construct()
    {
        $this->addColumn('ip', array(
            'label' => __('Payment server IP address'),
            'style' => 'width:120px'
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add new IP address');
        parent::__construct();
    }

}
