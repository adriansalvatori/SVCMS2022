<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Payment;

use AmeliaBooking\Domain\Services\Payment\AbstractPaymentService;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

/**
 * Class CurrencyService
 */
class CurrencyService extends AbstractPaymentService
{
    /**
     * @param Price  $amount
     *
     * @return mixed
     * @throws \Money\Exception\ParserException
     */
    public function getAmountInFractionalUnit($amount)
    {
        $currencies = new ISOCurrencies();

        $moneyParser = new DecimalMoneyParser($currencies);

        return $moneyParser->parse(
            (string)$amount->getValue(),
            $this->settingsService->getCategorySettings('payments')['currency']
        )->getAmount();
    }
}
