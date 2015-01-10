<?php
/*
Mygateway Payment Controller
By: Junaid Bhura
www.junaidbhura.com
*/

class Paycoingateway_Paycoin_PaymentController extends Mage_Core_Controller_Front_Action {
	// The redirect action is triggered when someone places an order
	public function callbackAction() {
        require_once(Mage::getModuleDir('paycoingateway-php', 'community_paycoingateway_paycoin') . "/paycoingateway-php/paycoin.php");

        $secret = $_REQUEST['secret'];
        $postBody = json_decode(file_get_contents('php://input'));
        $correctSecret = Mage::getStoreConfig('payment/Paycoin/callback_secret');

        // To verify this callback is legitimate, we will:
        //   a) check with Coinbase the submitted order information is correct.
        $apiKey = Mage::getStoreConfig('payment/PaycoinGateway/api_key');
        $apiSecret = Mage::getStoreConfig('payment/PaycoinGateway/api_secret');
        $PaycoinGateway = PaycoinGateway::withApiKey($apiKey, $apiSecret);
        $cbOrderId = $postBody->order->id;
        $orderInfo = $PaycoinGateway->getOrder($cbOrderId);
        if(!$orderInfo) {
            Mage::log("PaycoinGateway: incorrect callback with incorrect PaycoinGateway order ID $cbOrderId.");
            header("HTTP/1.1 500 Internal Server Error");
            return;
        }

        //   b) using the verified order information, check which order the transaction was for using the custom param.
        $orderId = $orderInfo->custom;
        $order = Mage::getModel('sales/order')->load($orderId);
        if(!$order) {
            Mage::log("PaycoinGateway: incorrect callback with incorrect order ID $orderId.");
            header("HTTP/1.1 500 Internal Server Error");
            return;
        }

        //   c) check the secret URL parameter.
        if($secret !== $correctSecret) {
            Mage::log("PaycoinGateway: incorrect callback with incorrect secret parameter $secret.");
            header("HTTP/1.1 500 Internal Server Error");
            return;
        }
        // The callback is legitimate. Update the order's status in the database.
        $payment = $order->getPayment();
        $payment->setTransactionId($cbOrderId)
            ->setPreparedMessage("Paid with PaycoinGateway order $cbOrderId.")
            ->setShouldCloseParentTransaction(true)
            ->setIsTransactionClosed(0);

        if("completed" == $orderInfo->status) {
            $payment->registerCaptureNotification($orderInfo->total_native->cents);
        } else {
            $cancelReason = $postBody->cancellation_reason;
            $order->registerCancellation("PaycoinGateway order $cbOrderId cancelled: $cancelReason");
        }

        Mage::dispatchEvent('PaycoinGateway_callback_received', array('status' => $orderInfo->status, 'order_id' => $orderId));
        $order->save();
	}
}