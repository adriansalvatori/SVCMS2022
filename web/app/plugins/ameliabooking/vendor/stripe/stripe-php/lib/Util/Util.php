<?php

namespace AmeliaStripe\Util;

use AmeliaStripe\StripeObject;

abstract class Util
{
    private static $isMbstringAvailable = null;
    private static $isHashEqualsAvailable = null;

    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     * A list is defined as an array for which all the keys are consecutive
     * integers starting at 0. Empty arrays are considered to be lists.
     *
     * @param array|mixed $array
     * @return boolean true if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($array === []) {
            return true;
        }
        if (array_keys($array) !== range(0, count($array) - 1)) {
            return false;
        }
        return true;
    }

    /**
     * Recursively converts the PHP Stripe object to an array.
     *
     * @param array $values The PHP Stripe object to convert.
     * @return array
     */
    public static function convertStripeObjectToArray($values)
    {
        $results = [];
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof StripeObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertStripeObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the Stripe API to the corresponding PHP object.
     *
     * @param array $resp The response from the Stripe API.
     * @param array $opts
     * @return StripeObject|array
     */
    public static function convertToStripeObject($resp, $opts)
    {
        $types = [
            // data structures
            \AmeliaStripe\Collection::OBJECT_NAME => 'AmeliaStripe\\Collection',

            // business objects
            \AmeliaStripe\Account::OBJECT_NAME => 'AmeliaStripe\\Account',
            \AmeliaStripe\AccountLink::OBJECT_NAME => 'AmeliaStripe\\AccountLink',
            \AmeliaStripe\AlipayAccount::OBJECT_NAME => 'AmeliaStripe\\AlipayAccount',
            \AmeliaStripe\ApplePayDomain::OBJECT_NAME => 'AmeliaStripe\\ApplePayDomain',
            \AmeliaStripe\ApplicationFee::OBJECT_NAME => 'AmeliaStripe\\ApplicationFee',
            \AmeliaStripe\Balance::OBJECT_NAME => 'AmeliaStripe\\Balance',
            \AmeliaStripe\BalanceTransaction::OBJECT_NAME => 'AmeliaStripe\\BalanceTransaction',
            \AmeliaStripe\BankAccount::OBJECT_NAME => 'AmeliaStripe\\BankAccount',
            \AmeliaStripe\BitcoinReceiver::OBJECT_NAME => 'AmeliaStripe\\BitcoinReceiver',
            \AmeliaStripe\BitcoinTransaction::OBJECT_NAME => 'AmeliaStripe\\BitcoinTransaction',
            \AmeliaStripe\Capability::OBJECT_NAME => 'AmeliaStripe\\Capability',
            \AmeliaStripe\Card::OBJECT_NAME => 'AmeliaStripe\\Card',
            \AmeliaStripe\Charge::OBJECT_NAME => 'AmeliaStripe\\Charge',
            \AmeliaStripe\Checkout\Session::OBJECT_NAME => 'AmeliaStripe\\Checkout\\Session',
            \AmeliaStripe\CountrySpec::OBJECT_NAME => 'AmeliaStripe\\CountrySpec',
            \AmeliaStripe\Coupon::OBJECT_NAME => 'AmeliaStripe\\Coupon',
            \AmeliaStripe\CreditNote::OBJECT_NAME => 'AmeliaStripe\\CreditNote',
            \AmeliaStripe\Customer::OBJECT_NAME => 'AmeliaStripe\\Customer',
            \AmeliaStripe\CustomerBalanceTransaction::OBJECT_NAME => 'AmeliaStripe\\CustomerBalanceTransaction',
            \AmeliaStripe\Discount::OBJECT_NAME => 'AmeliaStripe\\Discount',
            \AmeliaStripe\Dispute::OBJECT_NAME => 'AmeliaStripe\\Dispute',
            \AmeliaStripe\EphemeralKey::OBJECT_NAME => 'AmeliaStripe\\EphemeralKey',
            \AmeliaStripe\Event::OBJECT_NAME => 'AmeliaStripe\\Event',
            \AmeliaStripe\ExchangeRate::OBJECT_NAME => 'AmeliaStripe\\ExchangeRate',
            \AmeliaStripe\ApplicationFeeRefund::OBJECT_NAME => 'AmeliaStripe\\ApplicationFeeRefund',
            \AmeliaStripe\File::OBJECT_NAME => 'AmeliaStripe\\File',
            \AmeliaStripe\File::OBJECT_NAME_ALT => 'AmeliaStripe\\File',
            \AmeliaStripe\FileLink::OBJECT_NAME => 'AmeliaStripe\\FileLink',
            \AmeliaStripe\Invoice::OBJECT_NAME => 'AmeliaStripe\\Invoice',
            \AmeliaStripe\InvoiceItem::OBJECT_NAME => 'AmeliaStripe\\InvoiceItem',
            \AmeliaStripe\InvoiceLineItem::OBJECT_NAME => 'AmeliaStripe\\InvoiceLineItem',
            \AmeliaStripe\IssuerFraudRecord::OBJECT_NAME => 'AmeliaStripe\\IssuerFraudRecord',
            \AmeliaStripe\Issuing\Authorization::OBJECT_NAME => 'AmeliaStripe\\Issuing\\Authorization',
            \AmeliaStripe\Issuing\Card::OBJECT_NAME => 'AmeliaStripe\\Issuing\\Card',
            \AmeliaStripe\Issuing\CardDetails::OBJECT_NAME => 'AmeliaStripe\\Issuing\\CardDetails',
            \AmeliaStripe\Issuing\Cardholder::OBJECT_NAME => 'AmeliaStripe\\Issuing\\Cardholder',
            \AmeliaStripe\Issuing\Dispute::OBJECT_NAME => 'AmeliaStripe\\Issuing\\Dispute',
            \AmeliaStripe\Issuing\Transaction::OBJECT_NAME => 'AmeliaStripe\\Issuing\\Transaction',
            \AmeliaStripe\LoginLink::OBJECT_NAME => 'AmeliaStripe\\LoginLink',
            \AmeliaStripe\Order::OBJECT_NAME => 'AmeliaStripe\\Order',
            \AmeliaStripe\OrderItem::OBJECT_NAME => 'AmeliaStripe\\OrderItem',
            \AmeliaStripe\OrderReturn::OBJECT_NAME => 'AmeliaStripe\\OrderReturn',
            \AmeliaStripe\PaymentIntent::OBJECT_NAME => 'AmeliaStripe\\PaymentIntent',
            \AmeliaStripe\PaymentMethod::OBJECT_NAME => 'AmeliaStripe\\PaymentMethod',
            \AmeliaStripe\Payout::OBJECT_NAME => 'AmeliaStripe\\Payout',
            \AmeliaStripe\Person::OBJECT_NAME => 'AmeliaStripe\\Person',
            \AmeliaStripe\Plan::OBJECT_NAME => 'AmeliaStripe\\Plan',
            \AmeliaStripe\Product::OBJECT_NAME => 'AmeliaStripe\\Product',
            \AmeliaStripe\Radar\EarlyFraudWarning::OBJECT_NAME => 'AmeliaStripe\\Radar\\EarlyFraudWarning',
            \AmeliaStripe\Radar\ValueList::OBJECT_NAME => 'AmeliaStripe\\Radar\\ValueList',
            \AmeliaStripe\Radar\ValueListItem::OBJECT_NAME => 'AmeliaStripe\\Radar\\ValueListItem',
            \AmeliaStripe\Recipient::OBJECT_NAME => 'AmeliaStripe\\Recipient',
            \AmeliaStripe\RecipientTransfer::OBJECT_NAME => 'AmeliaStripe\\RecipientTransfer',
            \AmeliaStripe\Refund::OBJECT_NAME => 'AmeliaStripe\\Refund',
            \AmeliaStripe\Reporting\ReportRun::OBJECT_NAME => 'AmeliaStripe\\Reporting\\ReportRun',
            \AmeliaStripe\Reporting\ReportType::OBJECT_NAME => 'AmeliaStripe\\Reporting\\ReportType',
            \AmeliaStripe\Review::OBJECT_NAME => 'AmeliaStripe\\Review',
            \AmeliaStripe\SetupIntent::OBJECT_NAME => 'AmeliaStripe\\SetupIntent',
            \AmeliaStripe\SKU::OBJECT_NAME => 'AmeliaStripe\\SKU',
            \AmeliaStripe\Sigma\ScheduledQueryRun::OBJECT_NAME => 'AmeliaStripe\\Sigma\\ScheduledQueryRun',
            \AmeliaStripe\Source::OBJECT_NAME => 'AmeliaStripe\\Source',
            \AmeliaStripe\SourceTransaction::OBJECT_NAME => 'AmeliaStripe\\SourceTransaction',
            \AmeliaStripe\Subscription::OBJECT_NAME => 'AmeliaStripe\\Subscription',
            \AmeliaStripe\SubscriptionItem::OBJECT_NAME => 'AmeliaStripe\\SubscriptionItem',
            \AmeliaStripe\SubscriptionSchedule::OBJECT_NAME => 'AmeliaStripe\\SubscriptionSchedule',
            \AmeliaStripe\TaxId::OBJECT_NAME => 'AmeliaStripe\\TaxId',
            \AmeliaStripe\TaxRate::OBJECT_NAME => 'AmeliaStripe\\TaxRate',
            \AmeliaStripe\ThreeDSecure::OBJECT_NAME => 'AmeliaStripe\\ThreeDSecure',
            \AmeliaStripe\Terminal\ConnectionToken::OBJECT_NAME => 'AmeliaStripe\\Terminal\\ConnectionToken',
            \AmeliaStripe\Terminal\Location::OBJECT_NAME => 'AmeliaStripe\\Terminal\\Location',
            \AmeliaStripe\Terminal\Reader::OBJECT_NAME => 'AmeliaStripe\\Terminal\\Reader',
            \AmeliaStripe\Token::OBJECT_NAME => 'AmeliaStripe\\Token',
            \AmeliaStripe\Topup::OBJECT_NAME => 'AmeliaStripe\\Topup',
            \AmeliaStripe\Transfer::OBJECT_NAME => 'AmeliaStripe\\Transfer',
            \AmeliaStripe\TransferReversal::OBJECT_NAME => 'AmeliaStripe\\TransferReversal',
            \AmeliaStripe\UsageRecord::OBJECT_NAME => 'AmeliaStripe\\UsageRecord',
            \AmeliaStripe\UsageRecordSummary::OBJECT_NAME => 'AmeliaStripe\\UsageRecordSummary',
            \AmeliaStripe\WebhookEndpoint::OBJECT_NAME => 'AmeliaStripe\\WebhookEndpoint',
        ];
        if (self::isList($resp)) {
            $mapped = [];
            foreach ($resp as $i) {
                array_push($mapped, self::convertToStripeObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']])) {
                $class = $types[$resp['object']];
            } else {
                $class = 'AmeliaStripe\\StripeObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            if (!self::$isMbstringAvailable) {
                trigger_error("It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@stripe.com if you have any questions.", E_USER_WARNING);
            }
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    public static function secureCompare($a, $b)
    {
        if (self::$isHashEqualsAvailable === null) {
            self::$isHashEqualsAvailable = function_exists('hash_equals');
        }

        if (self::$isHashEqualsAvailable) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }

    /**
     * Recursively goes through an array of parameters. If a parameter is an instance of
     * ApiResource, then it is replaced by the resource's ID.
     * Also clears out null values.
     *
     * @param mixed $h
     * @return mixed
     */
    public static function objectsToIds($h)
    {
        if ($h instanceof \AmeliaStripe\ApiResource) {
            return $h->id;
        } elseif (static::isList($h)) {
            $results = [];
            foreach ($h as $v) {
                array_push($results, static::objectsToIds($v));
            }
            return $results;
        } elseif (is_array($h)) {
            $results = [];
            foreach ($h as $k => $v) {
                if (is_null($v)) {
                    continue;
                }
                $results[$k] = static::objectsToIds($v);
            }
            return $results;
        } else {
            return $h;
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function encodeParameters($params)
    {
        $flattenedParams = self::flattenParams($params);
        $pieces = [];
        foreach ($flattenedParams as $param) {
            list($k, $v) = $param;
            array_push($pieces, self::urlEncode($k) . '=' . self::urlEncode($v));
        }
        return implode('&', $pieces);
    }

    /**
     * @param array $params
     * @param string|null $parentKey
     *
     * @return array
     */
    public static function flattenParams($params, $parentKey = null)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $calculatedKey = $parentKey ? "{$parentKey}[{$key}]" : $key;

            if (self::isList($value)) {
                $result = array_merge($result, self::flattenParamsList($value, $calculatedKey));
            } elseif (is_array($value)) {
                $result = array_merge($result, self::flattenParams($value, $calculatedKey));
            } else {
                array_push($result, [$calculatedKey, $value]);
            }
        }

        return $result;
    }

    /**
     * @param array $value
     * @param string $calculatedKey
     *
     * @return array
     */
    public static function flattenParamsList($value, $calculatedKey)
    {
        $result = [];

        foreach ($value as $i => $elem) {
            if (self::isList($elem)) {
                $result = array_merge($result, self::flattenParamsList($elem, $calculatedKey));
            } elseif (is_array($elem)) {
                $result = array_merge($result, self::flattenParams($elem, "{$calculatedKey}[{$i}]"));
            } else {
                array_push($result, ["{$calculatedKey}[{$i}]", $elem]);
            }
        }

        return $result;
    }

    /**
     * @param string $key A string to URL-encode.
     *
     * @return string The URL-encoded string.
     */
    public static function urlEncode($key)
    {
        $s = urlencode($key);

        // Don't use strict form encoding by changing the square bracket control
        // characters back to their literals. This is fine by the server, and
        // makes these parameter strings easier to read.
        $s = str_replace('%5B', '[', $s);
        $s = str_replace('%5D', ']', $s);

        return $s;
    }

    public static function normalizeId($id)
    {
        if (is_array($id)) {
            $params = $id;
            $id = $params['id'];
            unset($params['id']);
        } else {
            $params = [];
        }
        return [$id, $params];
    }

    /**
     * Returns UNIX timestamp in milliseconds
     *
     * @return integer current time in millis
     */
    public static function currentTimeMillis()
    {
        return (int) round(microtime(true) * 1000);
    }
}
