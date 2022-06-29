<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Payment;

use AmeliaBooking\Domain\Services\Payment\AbstractPaymentService;
use AmeliaBooking\Domain\Services\Payment\PaymentServiceInterface;
use Omnipay\Omnipay;
use Omnipay\PayPal\ExpressGateway;

/**
 * Class PayPalService
 */
class PayPalService extends AbstractPaymentService implements PaymentServiceInterface
{
    /**
     *
     * @return mixed
     * @throws \Exception
     */
    private function getGateway()
    {
        /** @var ExpressGateway $gateway */
        $gateway = Omnipay::create('PayPal_Rest');

        $gateway->initialize([
            'clientId' => $this->settingsService->getCategorySettings('payments')['payPal']['sandboxMode'] ?
                $this->settingsService->getCategorySettings('payments')['payPal']['testApiClientId'] :
                $this->settingsService->getCategorySettings('payments')['payPal']['liveApiClientId'],
            'secret'   => $this->settingsService->getCategorySettings('payments')['payPal']['sandboxMode'] ?
                $this->settingsService->getCategorySettings('payments')['payPal']['testApiSecret'] :
                $this->settingsService->getCategorySettings('payments')['payPal']['liveApiSecret'],
            'testMode' => $this->settingsService->getCategorySettings('payments')['payPal']['sandboxMode'],
        ]);

        return $gateway;
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function execute($data)
    {
        try {
            $payPalData = [
                'cancelUrl'  => $data['cancelUrl'],
                'returnUrl'  => $data['returnUrl'],
                'amount'     => $data['amount'],
                'currency'   => $this->settingsService->getCategorySettings('payments')['currency'],
                'noShipping' => 1,
            ];

            if ($data['description']) {
                $payPalData['description'] = $data['description'];
            }

            return $this->getGateway()->purchase($payPalData)->send();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function complete($data)
    {
        try {
            $response = $this->getGateway()->completePurchase([
                'transactionReference' => $data['transactionReference'],
                'PayerID'              => $data['PayerID'],
                'amount'               => $data['amount'],
                'currency'             => $this->settingsService->getCategorySettings('payments')['currency']
            ])->send();

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
