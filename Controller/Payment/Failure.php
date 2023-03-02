<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 2:33 PM
 */
namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Config;
use Magento\Framework\View\Result\PageFactory;

class Failure extends Index
{
    /**
     * Failure constructor.
     * @param \Madit\Sips2\Model\Api\Response $responseApi
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Config $config
     * @param \Madit\Sips2\Model\Method\Standard $standardMethod
     * @param \Magento\Sales\Model\Order $orderInterface
     */
    public function __construct(
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Magento\Backend\App\Action\Context $context,
        PageFactory  $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Magento\Sales\Model\Order $orderInterface
    ) {

        parent::__construct(
            $responseApi,
            $customerSession,
            $sips2Session,
            $context,
            $resultPageFactory,
            $checkoutSession,
            $config,
            $standardMethod,
            $orderInterface
        );
    }
    /**
     * Dispatch request
     *
     * When Sips2/Sips returns
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('sips2_failure')->setTitle($this->getSips2Session()->getRedirectTitle());
        $resultPage->getLayout()->getBlock('sips2_failure')->setMessage($this->getSips2Session()->getRedirectMessage());
        $this->getSips2Session()->unsetAll();
        return $resultPage;
    }
}
