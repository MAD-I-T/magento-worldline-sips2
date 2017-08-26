<?php

namespace Cymit\Atos\Model\Method;

use  Magento\Payment\Model\Method\AbstractMethod;
use \Magento\Sales\Model\Order;
abstract class AbstractMeans extends AbstractMethod
{

    protected $_isOffline = true;

    protected $_response = null;
    protected $_requestApi;
    protected $_message = null;
    protected $_error = false;
    protected $_config;
    //@var \Magento\Sales\Model\Order $_order
    protected $_order;
    protected $_quote;
    protected $quoteId;
    protected $config;
    protected $checkoutSession;
    protected $quoteFactory;
    protected $lastRealOrderId;
    protected $lastOrderId;

    /* @var \Magento\Sales\Model\Order */
    protected $orderInterface;


    /**
     * @param \Cymit\Atos\Model\Config $config,
     * @param \Cymit\Atos\Model\Api\Request $requestApi,
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory,
     * @param \Magento\Sales\Model\Order $orderInterface
     */
    public function __construct(
        \Cymit\Atos\Model\Config $config,
        \Cymit\Atos\Model\Api\Request $requestApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->config = $config;
        $this->_requestApi = $requestApi;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->orderInterface = $orderInterface;

        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data);
        $this->lastOrderId =  $this->checkoutSession->getLastOrderId();
        $this->lastRealOrderId =  $this->checkoutSession->getLastRealOrderId();
        $this->quoteId = $this->checkoutSession->getQuoteId();
    }
    /**
     * First call to the Atos server
     */
    abstract public function callRequest();

    /**
     * Get Payment Means
     *
     * @return string
     */
    abstract protected function _getPaymentMeans();

    /**
     * Get normal return URL
     *
     * @return string
     */
    abstract protected function _getNormalReturnUrl();

    /**
     * Get cancel return URL
     *
     * @return string
     */
    abstract protected function _getCancelReturnUrl();

    /**
     * Get automatic response URL
     *
     * @return string
     */
    abstract protected function _getAutomaticResponseUrl();

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    abstract function getOrderPlaceRedirectUrl();



    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {

            case AbstractMethod::ACTION_AUTHORIZE:
            case AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus('pending_payment');
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Get redirect block type
     *
     * @return string
     */
    public function getRedirectBlockType()
    {
        return $this->_redirectBlockType;
    }

    /**
     * Get system response
     *
     * @return string
     */
    public function getSystemResponse()
    {
        return $this->_response;
    }

    /**
     * Get system message
     *
     * @return string
     */
    public function getSystemMessage()
    {
        return $this->_message;
    }

    /**
     * Has system error
     *
     * @return boolean
     */
    public function hasSystemError()
    {
        return $this->_error;
    }

    /**
     * Get config model
     *
     * @return \Cymit\Atos\Model\Config
     */
    public function getConfig()
    {
        if (empty($this->_config)) {
            $this->_config = $this->config->initMethod($this->_code);
        }
        return $this->_config;
    }

    /**
     * Get Atos API Request Model
     *
     * @return \Cymit\Atos\Model\Api\Request
     */
    public function getApiRequest()
    {
        return $this->_requestApi;
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {

        if (empty($this->_quote)) {
            $this->_quote = $this->quoteFactory->create()->loadActive($this->quoteId);
        }
        return $this->_quote;
    }

    /**
     * Get current order
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            $this->_order = $this->orderInterface->loadByIncrementId($this->lastRealOrderId);
        }

        //echo '<pre> amount:  '.$this->_order->getTotalDue()
          //  ." \n real id ". $this->lastOrderId
            //." \norder id toto printr ". $this->lastRealOrderId .'</pre>';
        return $this->_order;
    }

    /**
     * Get order amount
     *
     * @return string
     */
    protected function _getAmount()
    {
        if ($this->_getOrder())
            $total = $this->_getOrder()->getTotalDue();
        else
            $total = 0;

        return number_format($total, 2, '', '');
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    protected function _getCustomerId()
    {
        if ($this->_getOrder())
            return (int) $this->_getOrder()->getCustomerId();
        else
            return 0;
    }

    /**
     * Get customer e-mail
     *
     * @return string
     */
    protected function _getCustomerEmail()
    {
        if ($this->_getOrder())
            return $this->_getOrder()->getCustomerEmail();
        else
            return 'undefined';
    }

    /**
     * Get customer IP address
     *
     * @return string
     */
    protected function _getCustomerIpAddress()
    {
        return $this->_getQuote()->getRemoteIp();
    }

    /**
     * Get order increment id
     *
     * @return string
     */
    protected function _getOrderId()
    {
        return $this->_getOrder()->getIncrementId();
    }

    /**
     * Get binary request file path
     *
     * @return string
     */
    protected function _getBinRequest()
    {
        //return Mage::getBaseDir('base') . DS . Mage::getStoreConfig('atos_api/config_bin_files/request_path');
        return $this->moduleDirReader->getModuleDir('','Cymit_Atos').'view/res/'.$this->_code.'/bin/static/request';
    }

    public function debugRequest($data)
    {
        $this->debugData(array('type' => 'request', 'parameters' => $data));
    }

    public function debugResponse($data, $from = '')
    {
        ksort($data);
        $this->debugData(array('type' => "{$from} response", 'parameters' => $data));
    }

}
