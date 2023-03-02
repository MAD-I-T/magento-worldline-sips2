<?php

namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Config;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class AutoResponse extends Index
{

    /*
     * @var \Madit\Sips2\Model\Ipn
     */
    protected $ipnService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AutoResponse constructor.
     *
     * @param \Madit\Sips2\Model\Ipn $ipnService
     * @param \Madit\Sips2\Model\Api\Response $responseApi
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Config $config
     * @param \Madit\Sips2\Model\Method\Standard $standardMethod
     * @param \Magento\Sales\Model\Order $orderInterface
     */
    public function __construct(
        \Madit\Sips2\Model\Ipn $ipnService,
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Magento\Sales\Model\Order $orderInterface
    ) {
        $this->ipnService = $ipnService;
        $this->logger = $logger;
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
        $options = [];

        $requestPostData = $this->getRequest()->getPost();

        if (!property_exists($requestPostData, 'Data')) {
            // Log error
            $errorMessage = __(
                'Automatic response received but no data received for order #%1.',
                $this->getCheckoutSession()->getLastRealOrderId()
            );
            $this->logger->error(get_class($this) . ' ' .__FUNCTION__. ': ' . $errorMessage);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable');
            return;
        }

        if (property_exists($requestPostData, 'Seal')) {
            $options['Seal'] = $requestPostData['Seal'];
            $options['Data'] = $requestPostData['Data'];
            $options['Encode'] = $requestPostData['Encode'];
            $options['InterfaceVersion'] = $requestPostData['InterfaceVersion'];
            $this->ipnService->processIpnResponse($requestPostData['Data'], $this->getMethodInstance(), $options);
        }
    }
}
