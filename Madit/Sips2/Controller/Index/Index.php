<?php
namespace Madit\Sips2\Controller\Index;

use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Api\Response;
use Madit\Sips2\Model\Config;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderInterface;

    /**
     * @var \Madit\Sips2\Model\Config
     */
    protected $_config;

    /**
     * @var \Madit\Sips2\Model\Api\Request
     */
    protected $_requestApi;

    /*
     *  @var \Madit\Sips2\Model\Method\Standard
     */
    protected $_standardMethod;

    /* @var \Magento\Customer\Model\Session $customerSession */
    protected $customerSession;

    /**
     * @var \Madit\Sips2\Model\Api\Response
     */
    protected $_responseApi;

    /**
     * @var \Madit\Sips2\Model\Session
     */
    protected $sips2Session;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var \Madit\Sips2\Helper\Data
     */
    protected $sips2Helper;

    /**
     * @var \Madit\Sips2\Model\Ipn $sips2Ipn
     */
    protected $sips2Ipn;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory **/
    protected $resultFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $config
     * @param Request $requestApi
     * @param Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Sips2\Helper\Data $sips2Helper
     * @param \Madit\Sips2\Model\Method\Standard $standardMethod
     * @param \Madit\Sips2\Model\Ipn $sips2Ipn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
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
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Sips2\Helper\Data $sips2Helper,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Madit\Sips2\Model\Ipn $sips2Ipn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
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
        $this->quoteRepository = $quoteRepository;
        $this->orderInterface = $orderInterface;
        $this->customerSession = $customerSession;
        $this->sips2Session = $sips2Session;
        $this->logger = $logger;
        $this->sips2Helper = $sips2Helper;
        $this->_standardMethod = $standardMethod;
        $this->sips2Ipn = $sips2Ipn;
        $this->_blockFactory = $blockFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Get Sips2 Api Response Model
     * @return \Madit\Sips2\Model\Api\Response
     *
     */
    public function getApiResponse()
    {
        return $this->_responseApi;
    }

    /**
     * Get Sips2/Sips Standard config
     *
     * @return \Madit\Sips2\Model\Config
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
     * Get Sips2/Sips Standard session
     *
     * @return \Madit\Sips2\Model\Session
     */
    public function getSips2Session()
    {
        return $this->sips2Session;
    }

    protected function getMethodInstance()
    {
        return $this->_standardMethod;
    }
    /**
     * Treat Sips2/Sips response
     */
    protected function _getSips2Response($data, $options = null)
    {
        $response = [];

        $response = $this->getApiResponse()->doResponsev2($data, $options);

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
