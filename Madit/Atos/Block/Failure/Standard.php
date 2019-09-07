<?php

namespace Madit\Atos\Block\Failure;
use Magento\Framework\View\Element\Template;
class Standard extends Template
{

    protected $standardMethod;

    /**
     * @var \Madit\Atos\Model\Session
     */
    protected $atosSession;


    protected $title;
    protected $message;




    /**
     * Standard constructor.
     * @param \Madit\Atos\Model\Method\Standard $standard
     * @param \Madit\Atos\Model\Session $atosSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Madit\Atos\Model\Method\Standard $standard,
        \Madit\Atos\Model\Session $atosSession,
        \Magento\Framework\View\Element\Template\Context $context,
         array $data = []
    ) {
        $this->standardMethod = $standard;
        $this->atosSession = $atosSession;
        $this->title = $this->atosSession->getRedirectTitle();
        $this->message = $this->atosSession->getRedirectMessage();
        $this->atosSession->unsetAll();
        parent::__construct($context, $data);
    }





    public function getTitle()
    {
        return $this->title;
    }

    public function getMessage()
    {
        return $this->message;
    }

}
