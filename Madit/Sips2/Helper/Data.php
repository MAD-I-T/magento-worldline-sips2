<?php

namespace Madit\Sips2\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


    /* @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $quoteFactory;

    /* @var \Magento\Sales\Model\Order */
    protected $orderInterface;

    /**
     * @var \Madit\Sips2\Model\Config
     */
    protected $_config;

    /**
     * @var \Madit\Sips2\Model\Api\Request
     */
    protected $_requestApi;

    /* @var \Magento\Customer\Model\Session $customerSession */
    protected $customerSession;

    /**
     * @var \Madit\Sips2\Model\Api\Response
     */
    protected $_responseApi ;


    /**
     * @var \Madit\Sips2\Model\Session
     */
    protected $sips2Session;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

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
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->moduleDirReader = $moduleDirReader;
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
        $this->sips2Session = $sips2Session;
        $this->logger = $logger;
    }

    /**
     * Log Error
     *
     * @param string $class
     * @param string $function
     * @param string $message
     */
    public function logError($class, $function, $message)
    {
        $this->logger->error($class . ' ' . $function . ': ' . $message);
    }

    /*
    public function reorder($incrementId)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

        if ($order->getId()) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                try {
                    $cart->addOrderItem($item);
                } catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                }
            }
        }

        $cart->save();
    }
    */
}
