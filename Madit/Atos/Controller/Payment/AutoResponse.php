<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 1:39 PM
 */

namespace Madit\Atos\Controller\Payment;

use Madit\Atos\Controller\Index\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class AutoResponse extends Index
{

    /*
     * @var \Madit\Atos\Model\Ipn
     */
    protected $ipnService;

    /**
     * AutoResponse constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Madit\Atos\Model\Api\Files $filesApi
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Madit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Madit\Atos\Model\Config $config
     * @param \Madit\Atos\Model\Api\Request $requestApi
     * @param \Madit\Atos\Model\Api\Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Atos\Model\Session $atosSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Atos\Helper\Data $atosHelper
     * @param \Madit\Atos\Model\Method\Standard $standardMethod
     * @param \Madit\Atos\Model\Ipn $atosIpn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param PageFactory $resultPageFactory
     * @param \Madit\Atos\Model\Ipn $ipnService
     */
    public function __construct(
        \Madit\Atos\Model\Ipn $ipnService,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Madit\Atos\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Madit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Madit\Atos\Model\Config $config,
        \Madit\Atos\Model\Api\Request $requestApi,
        \Madit\Atos\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Atos\Model\Session $atosSession,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Atos\Helper\Data $atosHelper,
        \Madit\Atos\Model\Method\Standard $standardMethod,
        \Madit\Atos\Model\Ipn $atosIpn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->ipnService = $ipnService;
        parent::__construct(
            $moduleDirReader,
            $filesApi,
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
            $atosSession,
            $logger,
            $atosHelper,
            $standardMethod,
            $atosIpn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }

    /**
     * Dispatch request
     * When Atos/Sips returns
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $options = [];
        if (!(array_key_exists('DATA', $_REQUEST) || array_key_exists('Data', $_REQUEST))) {
            // Log error
            $errorMessage = __('Automatic response received but no data received for order #%1.', $this->getCheckoutSession()->getLastRealOrderId());
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable');
            return;
        }

        if(array_key_exists('Seal', $_REQUEST)) {
            $options['Seal'] = $_REQUEST['Seal'];
            $options['Data'] = $_REQUEST['Data'];
            $options['Encode'] = $_REQUEST['Encode'];
            $options['InterfaceVersion'] = $_REQUEST['InterfaceVersion'];
            $this->ipnService->processIpnResponse($_REQUEST['Data'], $this->getMethodInstance(), $options);
        }else {
            $this->ipnService->processIpnResponse($_REQUEST['DATA'], $this->getMethodInstance());
        }
    }

}
