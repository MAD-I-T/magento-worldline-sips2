<?php

namespace Cymit\Atos\Block\Failure;
use Magento\Framework\View\Element\Template;
class Standard extends Template
{

    protected $standardMethod;

    /**
     * @var \Cymit\Atos\Model\Session
     */
    protected $atosSession;


    protected $title;
    protected $message;




    /**
     * Standard constructor.
     * @param \Cymit\Atos\Model\Method\Standard $standard
     * @param \Cymit\Atos\Model\Session $atosSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Cymit\Atos\Model\Method\Standard $standard,
        \Cymit\Atos\Model\Session $atosSession,
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
