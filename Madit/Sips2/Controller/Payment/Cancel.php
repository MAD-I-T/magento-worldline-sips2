<?php
namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Madit\Sips2\Model\Api\Request;

use Madit\Sips2\Model\Api\Response;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderRepository;

class Cancel extends Index
{
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;

    /**
     * Cancel constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Madit\Sips2\Model\Config $config
     * @param Request $requestApi
     * @param Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param OrderRepository $orderRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Sips2\Helper\Data $sips2Helper
     * @param \Madit\Sips2\Model\Method\Standard $standardMethod
     * @param \Madit\Sips2\Model\Ipn $sips2Ipn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Api\Request $requestApi,
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\Order $orderInterface,
        OrderRepository $orderRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Sips2\Helper\Data $sips2Helper,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Madit\Sips2\Model\Ipn $sips2Ipn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        PageFactory  $resultPageFactory
    ) {
        $this->_orderRepository = $orderRepository;
        parent::__construct(
            $moduleDirReader,
            $scopeConfig,
            $ccType,
            $storeManager,
            $config,
            $requestApi,
            $responseApi,
            $checkoutSession,
            $quoteFactory,
            $quoteRepository,
            $orderInterface,
            $customerSession,
            $sips2Session,
            $logger,
            $sips2Helper,
            $standardMethod,
            $sips2Ipn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }

    /**
     * Dispatch request When a customer cancel payment from Sips2/Sips Standard.
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        $requestPostData = $this->getRequest()->getPost();
        //if (!array_key_exists('DATA', $_REQUEST)) {
        if (!property_exists($requestPostData, 'Data')) {
            // Set redirect message
            $this->getSips2Session()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = ('Customer #' .
                $this->getCustomerSession()->getCustomerId()
                . ' returned successfully from worldline/Sips payment platform but no data received for order #'
                . $this->getCheckoutSession()->getLastRealOrder()->getId()
                . ''
            );
            $this->sips2Helper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getSips2Response($requestPostData['Data']);

        // Debug
        $this->getMethodInstance()->debugResponse($response['hash'], 'Cancel');

        // Set redirect URL
        $response['redirect_url'] = '*/*/failure';

        // Set redirect message
        $this->getSips2Session()->setRedirectTitle(('Your payment has been rejected'));
        $describedResponse = $this->getApiResponse()->describeResponse($response['hash'], 'array');
        $this->getSips2Session()->setRedirectMessage(
            (
                'The payment platform has rejected your transaction with the message: <strong>' .
                $describedResponse['response_code']
                . '</strong>.'
            )
        );

        // Cancel order
        if ($response['hash']['order_id']) {
            $order = $this->_orderRepository->get($response['hash']['order_id']);
            //$order =  $this->orderInterface->loadByIncrementId($response['hash']['order_id']);
            if ($response['hash']['response_code'] == 17) {
                $message = $this->getApiResponse()->describeResponse($response['hash']);
            } else {
                $message = ('Automatic cancel');
                if (array_key_exists('bank_response_code', $describedResponse)) {
                    $this->getSips2Session()->setRedirectMessage(
                        __(
                            'The payment platform has rejected your transaction with the message: <strong>%1</strong>,'.
                            ' because the bank send the error: <strong>%2</strong>.',
                            $describedResponse['response_code'],
                            $describedResponse['bank_response_code']
                        )
                    );
                } else {
                    $this->getSips2Session()->setRedirectMessage(
                        __(
                            'The payment platform has rejected your transaction with the message: <strong>%1</strong>.',
                            $describedResponse['response_code']
                        )
                    );
                }
            }
            if ($order->getId()) {
                if ($order->canCancel()) {
                    try {
                        $order->registerCancellation($message)->save();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->logger->critical($e);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $message .= '<br/><br/>';
                        $message .= ('The order has not been cancelled.') . ' : ' . $e->getMessage();
                        $order->addCommentToStatusHistory($message);
                        $order->save();
                    }
                } else {
                    $message .= '<br/><br/>';
                    $message .= ('The order was already cancelled.');
                    $order->addCommentToStatusHistory($message);
                    $order->save();
                }
            }
            // Refill cart
            //Mage::helper('sips2')->reorder($response['hash']['order_id']);
        }

        // Save Sips2/Sips response in session
        $this->getSips2Session()->setResponse($response);
        //$this->_redirect($response['redirect_url'], ['_secure' => true]);
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($response['redirect_url']);
        return $resultRedirect;
    }
}
