<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Payment;

use AmeliaBooking\Domain\Services\Payment\AbstractPaymentService;
use AmeliaBooking\Domain\Services\Payment\PaymentServiceInterface;
use Exception;
use Razorpay\Api\Api;

/**
 * Class RazorpayService
 */
class RazorpayService extends AbstractPaymentService implements PaymentServiceInterface
{
    private $keyId = '';


    /**
     *
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     *
     * @return Api
     * @throws Exception
     */
    private function getApi()
    {
        $keyId = $this->settingsService->getCategorySettings('payments')['razorpay']['testMode'] ?
            $this->settingsService->getCategorySettings('payments')['razorpay']['testKeyId'] :
            $this->settingsService->getCategorySettings('payments')['razorpay']['liveKeyId'];

        $this->keyId = $keyId;

        $keySecret = $this->settingsService->getCategorySettings('payments')['razorpay']['testMode'] ?
            $this->settingsService->getCategorySettings('payments')['razorpay']['testKeySecret'] :
            $this->settingsService->getCategorySettings('payments')['razorpay']['liveKeySecret'];


        return new Api($keyId, $keySecret);
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function execute($data)
    {
        $orderData = [
            'amount'     => $data['amount'],
            'currency'   => $this->settingsService->getCategorySettings('payments')['currency'],
        ];

        return $this->getApi()->order->create($orderData);
    }


    /**
     * @param $paymentId
     * @param $paymentAmount
     *
     * @return mixed
     * @throws Exception
     */
    public function capture($paymentId, $paymentAmount)
    {
        $payment = $this->getApi()->payment->fetch($paymentId);

        if ($payment &&
            ($paymentData = $payment->toArray()) &&
            !empty($paymentData['status']) &&
            $paymentData['status'] === 'captured'
        ) {
            return [
                'error_code' => 0,
            ];
        }

        return $payment->capture(
            [
                'amount'   => intval($paymentAmount * 100),
                'currency' => $this->settingsService->getCategorySettings('payments')['currency']
            ]
        );
    }

    /**
     * @param $attributes
     *
     * @return mixed
     * @throws Exception
     */
    public function verify($attributes)
    {
        return $this->getApi()->utility->verifyPaymentSignature($attributes);
    }
}
