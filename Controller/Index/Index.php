<?php
namespace Madit\Sips2\Controller\Index;

use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Api\Response;
use Madit\Sips2\Model\Config;
use Magento\Framework\App\ResponseInterface;

class Index  extends  \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

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
     * @var \Madit\Sips2\Model\Config
     */
    protected $_config;


    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderInterface;


    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory **/
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Response $responseApi
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Magento\Sales\Model\Order $orderInterface

    ) {
        $this->_responseApi = $responseApi;
        $this->customerSession = $customerSession;
        $this->sips2Session = $sips2Session;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_config = $config;
        $this->_standardMethod = $standardMethod;
        $this->orderInterface = $orderInterface;
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
