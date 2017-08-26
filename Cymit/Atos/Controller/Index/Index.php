<?php
namespace Cymit\Atos\Controller\Index;
use Cymit\Atos\Model\Api\Request;
use Cymit\Atos\Model\Api\Response;
use Cymit\Atos\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Cymit\EdiSync\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class Index extends \Magento\Framework\App\Action\Action
{

    /* @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $quoteFactory;

    /* @var \Magento\Sales\Model\Order */
    protected $orderInterface;

    /**
    * @var \Cymit\Atos\Model\Config
    */
   protected $_config;

   /**
    * @var \Cymit\Atos\Model\Api\Request
    */
   protected $_requestApi;

   /*
    *  @var \Cymit\Atos\Model\Method\Standard
    */
   protected $_standardMethod;

   /* @var \Magento\Customer\Model\Session $customerSession */
   protected $customerSession;


   /**
    * @var \Cymit\Atos\Model\Api\Response
    */
   protected $_responseApi ;


    /**
     * @var \Cymit\Atos\Model\Session
     */
    protected $atosSession;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /*
     * @var \Cymit\Atos\Helper\Data
     */
    protected $atosHelper;

    /*
     *  @var \Cymit\Atos\Model\Ipn $atosIpn
     */
    protected  $atosIpn;




    protected $_blockFactory;



    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory **/
    protected $resultFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Cymit\Atos\Model\Api\Files $filesApi
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Cymit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $config
     * @param Request $requestApi
     * @param Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Cymit\Atos\Model\Session $atosSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Cymit\Atos\Helper\Data $atosHelper
     * @param \Cymit\Atos\Model\Method\Standard $standardMethod
     * @param \Cymit\Atos\Model\Ipn $atosIpn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Cymit\Atos\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cymit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cymit\Atos\Model\Config $config,
        \Cymit\Atos\Model\Api\Request $requestApi,
        \Cymit\Atos\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Cymit\Atos\Model\Session $atosSession,
        \Psr\Log\LoggerInterface $logger,
        \Cymit\Atos\Helper\Data $atosHelper,
        \Cymit\Atos\Model\Method\Standard $standardMethod,
        \Cymit\Atos\Model\Ipn $atosIpn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->filesApi = $filesApi;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        $this->_config = $config;
        $this->_requestApi = $requestApi;
        $this->_responseApi = $responseApi;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->orderInterface = $orderInterface;
        $this->customerSession = $customerSession;
        $this->atosSession = $atosSession;
        $this->logger = $logger;
        $this->atosHelper = $atosHelper;
        $this->_standardMethod = $standardMethod;
        $this->atosIpn = $atosIpn;
        $this->_blockFactory = $blockFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Get Atos Api Response Model
     * @return \Cymit\Atos\Model\Api\Response
     *
     */
    public function getApiResponse()
    {
        return $this->_responseApi;
    }

    /**
     * Get Atos/Sips Standard config
     *
     * @return \Cymit\Atos\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get checkout session
     *
     * @return  \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get Atos/Sips Standard session
     *
     * @return \Cymit\Atos\Model\Session
     */
    public function getAtosSession()
    {
        return $this->atosSession;
    }

    /**
     * When a customer chooses Atos/Sips Standard on Checkout/Payment page
    public function redirectAction()
    {
        // checkutsession ->getLastQuoteId()
        $this->getAtosSession()->setQuoteId($this->getCheckoutSession()->getLastRealOrder()->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock($this->getMethodInstance()->getRedirectBlockType(), 'atos_redirect')->toHtml());
        $this->getCheckoutSession()->unsQuoteId();
        $this->getCheckoutSession()->unsRedirectUrl();
    }
     */

    /**
     * When a customer cancel payment from Atos/Sips Standard.
    public function cancelAction()
    {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = ('Customer #'.$this->getCustomerSession()->getCustomerId().' returned successfully from Atos/Sips payment platform but no data received for order #'.  $this->getCheckoutSession()->getLastRealOrder()->getId().'' );
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);

        // Debug
        $this->getMethodInstance()->debugResponse($response['hash'], 'Cancel');

        // Set redirect URL
        $response['redirect_url'] = 'failure';

        // Set redirect message
        $this->getAtosSession()->setRedirectTitle(('Your payment has been rejected'));
        $describedResponse = $this->getApiResponse()->describeResponse($response['hash'], 'array');
        $this->getAtosSession()->setRedirectMessage(('The payment platform has rejected your transaction with the message: <strong>'.$describedResponse['response_code'].'</strong>.'));

        // Cancel order
        if ($response['hash']['order_id']) {
            $order =  $this->orderInterface->loadByIncrementId($response['hash']['order_id']);
            if ($response['hash']['response_code'] == 17) {
                $message = $this->getApiResponse()->describeResponse($response['hash']);
            } else {
                $message = ('Automatic cancel');
                if (array_key_exists('bank_response_code', $describedResponse)) {
                    $this->getAtosSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>, because the bank send the error: <strong>%1</strong>.', $describedResponse['response_code'], $describedResponse['bank_response_code']));
                } else {
                    $this->getAtosSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>.', $describedResponse['response_code']));
                }
            }
            if ($order->getId()){
                if ($order->canCancel()) {
                    try {
                        $order->registerCancellation($message)->save();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->logger->critical($e);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $message .= '<br/><br/>';
                        $message .= ('The order has not been cancelled.'). ' : ' . $e->getMessage();
                        $order->addStatusHistoryComment($message)->save();
                    }
                } else {
                    $message .= '<br/><br/>';
                    $message .= ('The order was already cancelled.');
                    $order->addStatusHistoryComment($message)->save();
                }
            }
            // Refill cart
            //Mage::helper('atos')->reorder($response['hash']['order_id']);
        }

        // Save Atos/Sips response in session
        $this->getAtosSession()->setResponse($response);
        $this->_redirect($response['redirect_url'], array('_secure' => true));
    }

     */

    /**
     * When customer returns from Atos/Sips payment platform
    public function normalAction()
    {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = __('Customer #%1 returned successfully from Atos/Sips payment platform but no data received for order #%1.', $this->getCustomerSession()->getCustomerId(), $this->getCheckoutSession()->getLastRealOrder()->getId());
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);

        // Debug
        $this->getMethodInstance()->debugResponse($response['hash'], 'Normal');

        // Check if merchant ID matches
        if ($response['hash']['merchant_id'] != $this->getConfig()->getMerchantId()) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage(('An error occured: merchant ID mismatch.'));
            // Log error
            $errorMessage = __('Response Merchant ID (%1) is mismatch with configuration value (%2)', $response['hash']['merchant_id'], $this->getConfig()->getMerchantId());
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('failure');
            return;
        }

        // Treat response
        $order = $this->orderInterface;
        if ($response['hash']['order_id']) {
            $order->loadByIncrementId($response['hash']['order_id']);
        }

        switch ($response['hash']['response_code']) {
            case '00':
                if ($order->getId()) {
                    $order->addStatusHistoryComment(('Customer returned successfully from Atos/Sips payment platform.'))
                            ->save();
                }
                $this->getCheckoutSession()->getQuote()->setIsActive(false)->save();
                // Set redirect URL
                $response['redirect_url'] = 'checkout/onepage/success';
                break;
            default:
                // Log error
                $errorMessage = __('Error: code %1.<br /> %1', $response['hash']['response_code'], $response['hash']['error']);
                $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
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
                            $errorMessage .= __('The order has not been cancelled.'). ' : ' . $e->getMessage();
                            $order->addStatusHistoryComment($errorMessage)->save();
                        }
                    } else {
                        $errorMessage .= '<br/><br/>';
                        $errorMessage .= __('The order was already cancelled.');
                        $order->addStatusHistoryComment($errorMessage)->save();
                    }

                    // Refill cart
                    Mage::helper('atos')->reorder($response['hash']['order_id']);

                }
                // Set redirect message
                $this->getAtosSession()->setRedirectTitle(('Your payment has been rejected'));
                $describedResponse = $this->getApiResponse()->describeResponse($response['hash'], 'array');
                $this->getAtosSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>, because the bank send the error: <strong>%2</strong>.', $describedResponse['response_code'], $describedResponse['bank_response_code']));
                // Set redirect URL
                $response['redirect_url'] = 'failure';
                break;
        }

        // Save Atos/Sips response in session
        $this->getAtosSession()->setResponse($response);

        $this->_redirect($response['redirect_url'], array('_secure' => true));
    }
     */

    /**
     * When Atos/Sips returns
    public function automaticAction()
    {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Log error
            $errorMessage = __('Automatic response received but no data received for order #%1.', $this->getCheckoutSession()->getLastRealOrderId());
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable');
            return;
        }

        //Mage::getModel('atos/ipn')->processIpnResponse($_REQUEST['DATA'], $this->getMethodInstance());
    }

     */
    /**
     * When has error in treatment
    public function failureAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('atos_failure')->setTitle($this->getAtosSession()->getRedirectTitle());
        $this->getLayout()->getBlock('atos_failure')->setMessage($this->getAtosSession()->getRedirectMessage());
        $this->getAtosSession()->unsetAll();
        $this->renderLayout();
    }
     */

    /*
    public function saveAuroreDobAction()
    {
        $dob = Mage::app()->getLocale()->date($this->getRequest()->getParam('dob'), null, null, false)->toString('yyyy-MM-dd');
        try {
            $this->getAtosSession()->setCustomerDob($dob);
            $this->getResponse()->setBody('OK');
        } catch (Exception $e) {
            $this->getResponse()->setBody('KO - ' . $e->getMessage());
        }
    }
    */

    protected function getMethodInstance(){
        return $this->_standardMethod;
    }
    /**
     * Treat Atos/Sips response
     */
    protected function _getAtosResponse($data)
    {
        $response = $this->getApiResponse()->doResponse($data, array(
            'bin_response' => $this->getConfig()->getBinResponse(),
            'pathfile' => $this->getMethodInstance()->getConfig()->getPathfile()
        ));

        //die('Hash code: '.isset($response['hash']['code']).' reponse is: '.print_r($response, 1));
        if (!isset($response['hash']['code'])) {
            $this->_redirect('*/*/failure');
            return;
        }

        if ($response['hash']['code'] == '-1') {
            $this->_redirect('*/*/failure');
            return;
        }

        return $response;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
