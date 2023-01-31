<?php
namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Api\Response;
use Madit\EdiSync\Helper\Data;

use Madit\Sips2\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class NormalCallback extends Index
{

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * NormalCallback constructor.
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
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory  $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Quote\Model\QuoteRepository $quoteRepository

    ) {
        $this->quoteRepository = $quoteRepository;
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
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->getRequest()->getPost()['Data']) {

            // Set redirect message
            $this->getSips2Session()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $customerId = $this->getCustomerSession()->getCustomerId();
            $errorMessage = __(
                'Customer #%1 returned successfully from Sips2/Sips'
                .' payment platform but no data received for order #%2.',
                $customerId,
                $this->getCheckoutSession()->getLastRealOrder()->getId()
            );

            $this->logger->critical($errorMessage);

            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Get Sips Server Response
        $response = [];
        $requestPostData = $this->getRequest()->getPost();

        if (property_exists($requestPostData, 'Seal')) {
            $options['Seal'] = $requestPostData['Seal'];
            $options['Data'] = $requestPostData['Data'];
            $options['Encode'] = $requestPostData['Encode'];
            $options['InterfaceVersion'] = $requestPostData['InterfaceVersion'];
            $response = $this->_getSips2Response($requestPostData['Data'], $options);
        } else {

            $this->getConfig()->initMethod('sips2_standard');
            $response = $this->_getSips2Response($requestPostData['DATA']);
        }

        $isDebug = $this->getMethodInstance()->getConfigData("debug");

        if ($isDebug) {
            $this->getMethodInstance()->debugResponse($response['hash'], 'Normal');
        }

        // Check if merchant ID matches
        if ($response['hash']['merchant_id'] != $this->getconfig()->getMerchantId()) {
            // Set redirect message
            $this->getSips2Session()->setRedirectMessage(('An error occured: merchant ID mismatch.'));
            // Log error
            $errorMessage = __(
                'Response Merchant ID (%1) is mismatch with configuration value (%2)',
                $response['hash']['merchant_id'],
                $this->getConfig()->getMerchantId()
            );
            $this->logger->error(get_class($this) . ' ' .__FUNCTION__. ': ' . $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Treat response
        $order = $this->orderInterface;
        if ($response['hash']['order_id']) {
            $order->loadByIncrementId($response['hash']['order_id']);
        }

        switch ($response['hash']['response_code']) {
            case '00':
                $curQuote = $this->getCheckoutSession()->getQuote();
                $curQuote->setIsActive(false);
                $this->quoteRepository->save($curQuote);
                if ($order->getId()) {
                    $checkoutSession  = $this->getCheckoutSession();
                    $checkoutSession->setLastOrderId($order->getId());
                    $checkoutSession->setLastQuoteId($order->getQuoteId());
                    $checkoutSession->setLastSuccessQuoteId($order->getQuoteId());

                    $order->addCommentToStatusHistory(
                        __('Customer returned successfully from Sips2/Sips payment platform.')
                    )->save();
                    //addStatusHistoryComment(('Customer returned successfully from Sips2/Sips payment platform.'))
                     //   ->save();
                }

                $curQuote = $this->getCheckoutSession()->getQuote();
                $curQuote->setIsActive(false);
                $this->quoteRepository->save($curQuote);

                // Set redirect URL
                $response['redirect_url'] = 'checkout/onepage/success';
                break;
            default:
                // Log error
                $errorMessage = __(
                    'Error: code %1.<br /> %1',
                    $response['hash']['response_code'],
                    $response['hash']['error']
                );
                $this->logger->error(get_class($this) . ' ' .__FUNCTION__. ': ' . $errorMessage);
                // Add error on order message, cancel order and reorder
                if ($order->getId()) {
                    if ($order->canCancel()) {
                        try {
                            $order->registerCancellation($errorMessage)->save();
                        } catch (LocalizedException $e) {
                            $this->logger->critical($e);
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                            $errorMessage .= '<br/><br/>';
                            $errorMessage .= __('The order has not been cancelled.') . ' : ' . $e->getMessage();
                            $order->addStatusHistoryComment($errorMessage)->save();
                        }
                    } else {
                        $errorMessage .= '<br/><br/>';
                        $errorMessage .= __('The order was already cancelled.');
                        $order->addStatusHistoryComment($errorMessage)->save();
                    }

                }
                // Set redirect message
                $this->getSips2Session()->setRedirectTitle(('Your payment has been rejected'));
                $describedResponse = $this->getApiResponse()->describeResponse($response['hash'], 'array');
                $this->getSips2Session()->setRedirectMessage(
                    __(
                        'The payment platform has rejected your transaction with the message: <strong>%1</strong>, '.
                        'because the bank send the error: <strong>%2</strong>.',
                        $describedResponse['response_code'],
                        $describedResponse['bank_response_code'] ?? 'None'
                    )
                );
                // Set redirect URL
                $response['redirect_url'] = '*/*/failure';
                break;
        }

        // Save Sips2/Sips response in session
        $this->getSips2Session()->setResponse($response);

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($response['redirect_url']);
        return $resultRedirect;
    }
}
