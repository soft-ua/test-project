<?php
/**
 * Created by PhpStorm.
 * User: soft
 * Date: 10/25/16
 * Time: 4:42 PM
 */

namespace frontend\controllers;

use yii\base\Exception;
use yii\web\Controller;

use Yii;
use common\models\Template;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalConnectionException;

class PaymentController extends Controller {

    public $defaultAction = 'pay';

    private $apiContext = false;

    function actionPay($templateId){
        $description = 'Buy Template #' . $templateId;
        $price = '22.00';
        $currency = 'USD';
        $returnUrl = Yii::$app->urlManager->createAbsoluteUrl('payment/success');
        $cancelUrl = Yii::$app->urlManager->createAbsoluteUrl('payment/cancel');

        if( ! Yii::$app->user->isGuest ) {
            $this->makePayment($templateId, $price, $currency, $description, $returnUrl, $cancelUrl);
        }
        else{

            $this->redirect('/site/login');
        }
    }

    function actionSuccess($paymentId, $token, $PayerID){
        $apiContext = $this->getApiContext();
        $payment = Payment::get($paymentId, $apiContext);

        $execute = new PaymentExecution();
        $execute->setPayerId($PayerID);

        try{
            $result = $payment->execute($execute, $apiContext);

            $this->saveTrancaction($result);

            $templateID = explode('-', $result->transactions[0]->invoice_number);

            Yii::$app->session->set(Template::SESSION_TEMPLATE_ID, $templateID[0]);
            Yii::$app->session->setFlash(Template::FLASH_DOWNLOAD_TRIGGER, 1);

            $this->redirect( Yii::$app->getHomeUrl() );
        }
        catch(Exception $e){
            die($e->getMessage());
        }
    }

    function actionCancel(){
        echo 'cancel';
    }

    function getApiContext(){
        if( $this->apiContext == false ) {
            $this->apiContext = new ApiContext(
                new OAuthTokenCredential(
                    'ATUQ6thUFdBAgS1QZuDY4AB6fDxOCe47u104q8E5deCBrN982kw2EYvW65bVM6S3-9fO23ByVFTR4Tpu',
                    'EK-09E9vM07xgbeUPZE0o2pFNswb5zINfUwRYatTGuT6rO7ra5GRu16u88zkxt8d6PfoyyvqGYBEEERq'
                )
            );

            $this->apiContext->setConfig([
                'mode' => 'sandbox',
                'http.ConnectionTimeOut' => 30,
                'log.LogEnabled' => true,
                'log.FileName' => '/opt/lampp/htdocs/logs/PayPal.log',
                'log.LogLevel' => 'DEBUG'
            ]);
        }

        return $this->apiContext;
    }

    function makePayment($templateId, $total, $currency, $paymentDesc, $returnUrl, $cancelUrl) {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $amount = new Amount();
        $amount->setCurrency($currency);
        $amount->setTotal($total);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription($paymentDesc);
        $transaction->setInvoiceNumber( $templateId.'-'.uniqid() );

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setRedirectUrls($redirectUrls);
        $payment->setIntent("sale");
        $payment->setPayer($payer);
        $payment->setTransactions([ $transaction ]);

        try {
            $payment->create( $this->getApiContext() );
        }
        catch( PayPalConnectionException $e){
            die($e->getMessage());
        }

        $this->redirect( $payment->getApprovalLink() );
    }


    function saveTrancaction($resp){

        Yii::$app->db->createCommand()->insert('transactions', [
            'user_id' => Yii::$app->user->getId(),
            'transaction_id' => $resp->id,
            'hash' => '',
            'complete' => ($resp->state == 'approved') ? 1 : 0,
        ])->execute();
    }
}