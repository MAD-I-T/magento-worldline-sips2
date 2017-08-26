<?php

namespace Cymit\Atos\Block\Redirect;
use Magento\Framework\View\Element\Template;

class Standard extends Template
{

    protected $standardMethod;

    public function __construct(
        \Cymit\Atos\Model\Method\Standard $standard,
        \Magento\Framework\View\Element\Template\Context $context,
         array $data = []
    ) {
        $this->standardMethod = $standard;
        parent::__construct($context, $data);
    }
    public function getBankForm()
    {

        $method = $this->standardMethod;
        //echo "<pre> in to html .'$this->$method->getSystemMessage() '.</pre>";

        $method->callRequest();
        //echo "<pre> in to html .'$method->getSystemMessage() '.</pre>";

        $html = '';

        if ($method->hasSystemError()) {
            // Has error
            $html .= $method->getSystemMessage();
        } else {
            // Active debug in pathfile
            $html .= $method->getSystemMessage();
            $html .= $method->getSystemResponse();
        }
        return $html;

    }

}
