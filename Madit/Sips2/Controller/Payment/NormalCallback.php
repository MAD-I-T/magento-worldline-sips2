<?php
namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Madit\Sips2\Model\Api\Request;
use Madit\Sips2\Model\Api\Response;
use Madit\EdiSync\Helper\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class NormalCallback extends Index
{

    /**
     * Constructor
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        //$dataServ = $this->getRequest();
        //var_dump($this->getRequest()->getPost()['Data']);
        //die();
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

            //echo "<pre> request: print_r($_REQUEST, 1)</pre>";
            $this->logger->critical($errorMessage);
            //$this->sips2Helper->logError(get_class($this), __FUNCTION__, $errorMessage);

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

            //echo $this->_code."cur config". print_r(, 1);
            $this->getConfig()->initMethod('sips2_standard');
            $response = $this->_getSips2Response($requestPostData['DATA']);
        }

        //echo var_dump($_REQUEST['DATA']);
        //$this->logger->debug(var_dump($_REQUEST['DATA']));

        //$this->sips2Helper->logError(get_class($this), __FUNCTION__, $response['hash']);
        // Debug

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
            $this->sips2Helper->logError(get_class($this), __FUNCTION__, $errorMessage);
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
                $this->sips2Helper->logError(get_class($this), __FUNCTION__, $errorMessage);
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

                    // Refill cart
                    //$this->sips2Helper->reorder($response['hash']['order_id']);
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
        //$this->_redirect($response['redirect_url'], ['_secure' => true]);
    }
}
