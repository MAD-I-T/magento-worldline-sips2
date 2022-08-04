<?php

namespace Madit\Sips2\Block\Failure;

use Magento\Framework\View\Element\Template;

class Standard extends Template
{

    protected $standardMethod;

    /**
     * @var \Madit\Sips2\Model\Session
     */
    protected $sips2Session;


    protected $title;
    protected $message;




    /**
     * Standard constructor.
     * @param \Madit\Sips2\Model\Method\Standard $standard
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Madit\Sips2\Model\Method\Standard $standard,
        \Madit\Sips2\Model\Session $sips2Session,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->standardMethod = $standard;
        $this->sips2Session = $sips2Session;
        $this->title = $this->sips2Session->getRedirectTitle();
        $this->message = $this->sips2Session->getRedirectMessage();
        $this->sips2Session->unsetAll();
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
