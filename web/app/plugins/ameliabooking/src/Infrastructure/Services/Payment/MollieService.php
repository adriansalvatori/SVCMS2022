<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Payment;

use AmeliaBooking\Domain\Services\Payment\AbstractPaymentService;
use AmeliaBooking\Domain\Services\Payment\PaymentServiceInterface;
use Omnipay\Mollie\Gateway;
use Omnipay\Omnipay;

/**
 * Class MollieService
 */
class MollieService extends AbstractPaymentService implements PaymentServiceInterface
{
    /**
     *
     * @return mixed
     * @throws \Exception
     */
    private function getGateway()
    {
        /** @var Gateway $gateway */
        $gateway = Omnipay::create('Mollie');

        $gateway->setApiKey(
            $this->settingsService->getCategorySettings('payments')['mollie']['testMode'] ?
                $this->settingsService->getCategorySettings('payments')['mollie']['testApiKey'] :
                $this->settingsService->getCategorySettings('payments')['mollie']['liveApiKey']
        );

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
            $mollieData = [
                'returnUrl'  => $data['returnUrl'],
                'notifyUrl'  => $data['notifyUrl'],
                'amount'     => $data['amount'],
                'currency'   => $this->settingsService->getCategorySettings('payments')['currency'],
            ];

            if ($data['description']) {
                $mollieData['description'] = $data['description'];
            }

            if ($data['metaData']) {
                $mollieData['metaData'] = $data['metaData'];
            }

            if ($data['method']) {
                $mollieData['method'] = $data['method'];
            }

            return $this->getGateway()->purchase($mollieData)->send();
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
    public function fetchPayment($data)
    {
        try {
            return $this->getGateway()->fetchTransaction(
                [
                    'transactionReference' => $data['id'],
                ]
            )->send();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
