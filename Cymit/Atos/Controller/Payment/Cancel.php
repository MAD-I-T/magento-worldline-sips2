<?php
namespace Cymit\Atos\Controller\Payment;
use Cymit\Atos\Model\Api\Request;
use Cymit\Atos\Model\Api\Response;
use Cymit\Atos\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action;
use Cymit\EdiSync\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

use Cymit\Atos\Controller\Index\Index;
class Cancel extends Index
{
    /**
     * Dispatch request
     * When a customer cancel payment from Atos/Sips Standard.
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!array_key_exists('DATA', $_REQUEST)) {
            // Set redirect message
            $this->getAtosSession()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = ('Customer #'.$this->getCustomerSession()->getCustomerId().' returned successfully from Atos/Sips payment platform but no data received for order #'.  $this->getCheckoutSession()->getLastRealOrder()->getId().'' );
            $this->atosHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        // Get Sips Server Response
        $response = $this->_getAtosResponse($_REQUEST['DATA']);

        // Debug
        $this->getMethodInstance()->debugResponse($response['hash'], 'Cancel');

        // Set redirect URL
        $response['redirect_url'] = '*/*/failure';

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
                    $this->getAtosSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>, because the bank send the error: <strong>%2</strong>.', $describedResponse['response_code'], $describedResponse['bank_response_code']));
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
}