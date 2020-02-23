<?php

namespace Madit\Atos\Model\Method;

use Madit\Atos\Model\Api\Request;
use Madit\Atos\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use  Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;

use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;

abstract class AbstractMeans extends Adapter
{
    protected $_isOffline = true;

    protected $_response = null;
    protected $_requestApi;
    protected $_message = null;
    protected $_error = false;
    protected $_config;
    protected $_logger;
    //@var \Magento\Sales\Model\Order $_order
    protected $_order;
    protected $_quote;
    protected $quoteId;
    protected $config;
    protected $checkoutSession;
    protected $quoteFactory;
    protected $lastRealOrderId;
    protected $lastOrderId;

    /* @var Order */
    protected $orderInterface;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param Config $config
     * @param Request $requestApi
     * @param Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param Order $orderInterface
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        Config $config,
        Request $requestApi,
        Session $checkoutSession,
        QuoteFactory $quoteFactory,
        Order $orderInterface,
        \Magento\Payment\Model\Method\Logger $logger,
        array $data = []
    ) {
        $this->config = $config;
        $this->_requestApi = $requestApi;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->orderInterface = $orderInterface;
        $this->_logger = $logger;

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType
        );

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
    abstract public function getOrderPlaceRedirectUrl();

    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {

            case Adapter::ACTION_AUTHORIZE:
            case Adapter::ACTION_AUTHORIZE_CAPTURE:
                $stateObject->setState(Order::STATE_PENDING_PAYMENT);
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
     * @return Config
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
     * @return Request
     */
    public function getApiRequest()
    {
        return $this->_requestApi;
    }

    /**
     * Get current quote
     *
     * @return Quote
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
     * @return Order
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
        if ($this->_getOrder()) {
            $total = $this->_getOrder()->getTotalDue();
        } else {
            $total = 0;
        }

        return number_format($total, 2, '', '');
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    protected function _getCustomerId()
    {
        if ($this->_getOrder()) {
            return (int) $this->_getOrder()->getCustomerId();
        } else {
            return 0;
        }
    }

    /**
     * Get customer e-mail
     *
     * @return string
     */
    protected function _getCustomerEmail()
    {
        if ($this->_getOrder()) {
            return $this->_getOrder()->getCustomerEmail();
        } else {
            return 'undefined';
        }
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
        return $this->moduleDirReader->getModuleDir('', 'Madit_Atos') . 'view/res/' . $this->_code . '/bin/static/request';
    }

    public function debugRequest($data)
    {
        $this->_logger->debug(['type' => 'request', 'parameters' => $data]);
        //$this->debugData(['type' => 'request', 'parameters' => $data]);
    }

    public function debugResponse($data, $from = '')
    {
        ksort($data);

        $this->_logger->debug(['type' => "{$from} response", 'parameters' => $data]);
        //$this->debugData(['type' => "{$from} response", 'parameters' => $data]);
    }
}
