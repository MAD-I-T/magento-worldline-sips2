<?php
namespace Madit\Sips2\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

/**
 * Sips2/Sips Instant Payment Notification processor model
 */
class Ipn
{
    /**
     * @var Api\Response|null
     */
    protected $_api = null;

    /**
     * @var Config|null
     */
    protected $_config = null;

    /**
     * @var null
     */
    protected $_invoice = null;

    /**
     * @var bool
     */
    protected $_invoiceFlag = false;

    /**
     * @var \Madit\Sips2\Model\Method\AbstractMeans
     */
    protected $_methodInstance = null;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * @var null
     */
    protected $_response = null;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\App\ResponseInterface $responseInterface
     */
    protected $responseInterface;

    /**
     * @var Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Model\Order $orderInterface
     */
    protected $orderInterface;

    /**
     * @var Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var mixed
     */
    protected $isDebug;

    /**
     * Ipn constructor.
     * @param Config $config
     * @param Api\Response $responseApi
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ResponseInterface $responseInterface
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param Order $orderInterface
     * @param \Magento\Framework\DB\Transaction $transactionFactory
     * @param Order\Email\Sender\OrderSender $orderSender
     * @param Order\Email\Sender\InvoiceSender $invoiceSender
     * @param OrderRepository $orderRepository
     */

    public function __construct(
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResponseInterface $responseInterface,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Framework\DB\Transaction $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender  $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        OrderRepository $orderRepository
    ) {
        $this->_api = $responseApi;
        $this->logger = $logger;
        $this->responseInterface = $responseInterface;
        $this->_config = $config;
        $this->orderInterface = $orderInterface;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceService = $invoiceService;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Decode Sips server response
     *
     * @param string $data
     * @param \Madit\Sips2\Model\Method\AbstractMeans $methodInstance
     * @param array|null $options
     */
    public function processIpnResponse($data, $methodInstance, $options = null)
    {
        // Init instance
        $this->_methodInstance = $methodInstance;
        $this->_config = $this->_methodInstance->getConfig();
        $this->isDebug = $this->_methodInstance->getConfigData("debug");

        $response = [];
        // Decode Sips Server Response
        $response = $this->_decodeResponse($data, $options);

        if ($this->isDebug) {
            $this->logger->debug("Poccess auto response", $response);
        }
        if (!array_key_exists('hash', $response)) {
            $this->_methodInstance->debugResponse('Can\'t retrieve Sips decoded response.');
            $this->responseInterface
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                    ->sendResponse();
            return;
        }

        // Debug
        $this->_methodInstance->debugResponse($response['hash'], 'Automatic');

        // Check IP address
        if (!$this->_checkIpAddresses()) {
            $this->responseInterface
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                ->sendResponse();
            return;
        }

        // Update order
        $this->_processOrder();
    }

    /**
     * Decode Sips server response
     *
     * @param string $response
     * @param array|null $options
     * @return array
     */
    protected function _decodeResponse(string $response, $options = null)
    {

            $this->_response = $this->_api->doResponsev2($response, $options);

        return $this->_response;
    }

    /**
     * Check if the server IP Address is allowed
     *
     * @return boolean
     */
    protected function _checkIpAddresses()
    {
        if ($this->_config->getCheckByIpAddress()) {
            $ipAdresses = $this->_response['sips2_server_ip_adresses'];
            $authorizedIps = $this->_config->getAuthorizedIps();
            $isIpOk = false;

            foreach ($ipAdresses as $ipAdress) {
                if (in_array(trim($ipAdress), $authorizedIps)) {
                    $isIpOk = true;
                    break;
                }
            }

            if (!$isIpOk) {
                //$filename = 'payment_' . $this->getMethodInstance()->getCode() . '.log';
                $this->logger->warning(implode(', ', $ipAdresses) . ' tries to connect to our server' . "\n");
                return false;
            }
        }

        return true;
    }

    /**
     * Load order
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            // Check order ID existence
            if (!array_key_exists('order_id', $this->_response['hash'])) {
                $this->_methodInstance->debugResponse('No order ID found in response data.');
                $this->responseInterface
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                    ->sendResponse();
                return ;
            }

            // Load order
            $id = $this->_response['hash']['order_id'];
            $this->_order = $this->orderInterface->loadByIncrementId($id);
            if (!$this->_order->getId()) {
                $this->_methodInstance->debugData(__('Wrong order ID: "%1".', $id));
                $this->responseInterface
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                    ->sendResponse();
                return ;
            }
        }
        return $this->_order;
    }

    /**
     * Update order with Sips response
     */
    protected function _processOrder()
    {
        // Check response code existence
        if (!array_key_exists('response_code', $this->_response['hash'])) {
            $this->_methodInstance->debugData('No response code found in response data.');
            $this->responseInterface
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                ->sendResponse();
            return;
        }

        // Get order to update
        $this->_getOrder();
        $messages = [];

        switch ($this->_response['hash']['response_code']) {
            case '00': // Success order
                // Get sips return data
                $messages[] = __('Payment accepted by Sips') .
                    '<br /><br />' .
                    $this->_api->describeResponse($this->_response['hash']);

                // Update payment
                $this->_processOrderPayment();

                // Create invoice
                if ($this->_invoiceFlag) {
                    $invoiceId = $this->_processInvoice();
                    $messages[] = __('Invoice #%1 created', $invoiceId);
                }

                // Add messages to order history
                foreach ($messages as $message) {
                    $this->_order->addCommentToStatusHistory($message);
                }

                // Save order
                try {
                    $this->orderRepository->save($this->_order);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $this->responseInterface
                        ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                        ->sendResponse();
                    return;
                }

                // Send order confirmation email
                if (!$this->_order->getEmailSent() && $this->_order->getCanSendNewEmailFlag()) {
                    try {
                        $this->orderSender->send($this->_order);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }

                // Send invoice email
                if ($this->_invoiceFlag) {
                    try {
                        // $this->_invoice->sendEmail();
                        $this->invoiceSender->send($this->_invoice);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }
                break;
            default: // Rejected payment or error
                $this->_processCancellation();
        }
    }

    /**
     * Update order payment
     */
    protected function _processOrderPayment()
    {
        try {
            // Set transaction
            $payment = $this->_order->getPayment();
            $payment->setTransactionId($this->_response['hash']['transaction_id']);
            $data = [
                'cc_type' => $this->_response['hash']['payment_means'],
                'cc_exp_month' => substr($this->_response['hash']['card_validity'], 4, 2),
                'cc_exp_year' => substr($this->_response['hash']['card_validity'], 0, 4),
                'cc_last4' => $this->_response['hash']['card_number']
            ];

            $payment->addData($data);
            $payment->save();

            // Add authorization transaction
            if (!$this->_order->isCanceled()) {
                $payment->authorize(true, $this->_order->getBaseGrandTotal());
                $payment->setAmountAuthorized($this->_order->getTotalDue());
                if ($this->_response['hash']['capture_mode'] == \Madit\Sips2\Model\Config::PAYMENT_ACTION_CAPTURE ||
                    (
                        $this->_response['hash']['capture_mode'] == \Madit\Sips2\Model\Config::PAYMENT_ACTION_AUTHORIZE
                    )
                ) {
                    $this->_invoiceFlag = true;
                }
            }

            $this->orderRepository->save($this->_order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->responseInterface
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                ->sendResponse();
            return;
        }
    }

    /**
     * Create invoice
     *
     * @return string
     */
    protected function _processInvoice()
    {
        try {
            $this->_invoice = $this->invoiceService->prepareInvoice($this->_order);
            $this->_invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $this->_invoice->register();

            $transactionSave = $this->transactionFactory
                    ->addObject($this->_invoice)->addObject($this->_invoice->getOrder())
                    ->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->responseInterface
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                ->sendResponse();
            return ;
        }

        return $this->_invoice->getIncrementId();
    }

    /**
     * Cancel order
     */
    protected function _processCancellation()
    {
        $message = __('Payment rejected by Sips') .
            '<br /><br />'
            . $this->_api->describeResponse($this->_response['hash']);

        $hasError = true;

        if ($this->_order->canCancel()) {
            try {
                $this->_order->registerCancellation($message);
                $this->orderRepository->save($this->_order);
            } catch (LocalizedException $e) {
                $hasError = true;
                $this->logger->critical($e);
            } catch (\Exception $e) {
                $hasError = true;
                $this->logger->critical($e);
                $message .= '<br /><br />';
                $message .= __('The order has not been cancelled.') . ' : ' . $e->getMessage();
                $this->_order->addStatusHistoryComment($message)->save();
            }
        } else {
            $message .= '<br /><br />';
            $message .= __('The order was already cancelled.');
            $this->_order->addStatusHistoryComment($message)->save();
        }

        if ($hasError) {
            $this->responseInterface
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_503)
                ->sendResponse();
            return;
        }
    }
}
