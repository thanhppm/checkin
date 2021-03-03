<?php

namespace TCStripe\Util;

use TCStripe\StripeObject;

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
            \TCStripe\Collection::OBJECT_NAME => 'TCStripe\\Collection',

            // business objects
            \TCStripe\Account::OBJECT_NAME => 'TCStripe\\Account',
            \TCStripe\AccountLink::OBJECT_NAME => 'TCStripe\\AccountLink',
            \TCStripe\AlipayAccount::OBJECT_NAME => 'TCStripe\\AlipayAccount',
            \TCStripe\ApplePayDomain::OBJECT_NAME => 'TCStripe\\ApplePayDomain',
            \TCStripe\ApplicationFee::OBJECT_NAME => 'TCStripe\\ApplicationFee',
            \TCStripe\Balance::OBJECT_NAME => 'TCStripe\\Balance',
            \TCStripe\BalanceTransaction::OBJECT_NAME => 'TCStripe\\BalanceTransaction',
            \TCStripe\BankAccount::OBJECT_NAME => 'TCStripe\\BankAccount',
            \TCStripe\BitcoinReceiver::OBJECT_NAME => 'TCStripe\\BitcoinReceiver',
            \TCStripe\BitcoinTransaction::OBJECT_NAME => 'TCStripe\\BitcoinTransaction',
            \TCStripe\Capability::OBJECT_NAME => 'TCStripe\\Capability',
            \TCStripe\Card::OBJECT_NAME => 'TCStripe\\Card',
            \TCStripe\Charge::OBJECT_NAME => 'TCStripe\\Charge',
            \TCStripe\Checkout\Session::OBJECT_NAME => 'TCStripe\\Checkout\\Session',
            \TCStripe\CountrySpec::OBJECT_NAME => 'TCStripe\\CountrySpec',
            \TCStripe\Coupon::OBJECT_NAME => 'TCStripe\\Coupon',
            \TCStripe\CreditNote::OBJECT_NAME => 'TCStripe\\CreditNote',
            \TCStripe\Customer::OBJECT_NAME => 'TCStripe\\Customer',
            \TCStripe\Discount::OBJECT_NAME => 'TCStripe\\Discount',
            \TCStripe\Dispute::OBJECT_NAME => 'TCStripe\\Dispute',
            \TCStripe\EphemeralKey::OBJECT_NAME => 'TCStripe\\EphemeralKey',
            \TCStripe\Event::OBJECT_NAME => 'TCStripe\\Event',
            \TCStripe\ExchangeRate::OBJECT_NAME => 'TCStripe\\ExchangeRate',
            \TCStripe\ApplicationFeeRefund::OBJECT_NAME => 'TCStripe\\ApplicationFeeRefund',
            \TCStripe\File::OBJECT_NAME => 'TCStripe\\File',
            \TCStripe\File::OBJECT_NAME_ALT => 'TCStripe\\File',
            \TCStripe\FileLink::OBJECT_NAME => 'TCStripe\\FileLink',
            \TCStripe\Invoice::OBJECT_NAME => 'TCStripe\\Invoice',
            \TCStripe\InvoiceItem::OBJECT_NAME => 'TCStripe\\InvoiceItem',
            \TCStripe\InvoiceLineItem::OBJECT_NAME => 'TCStripe\\InvoiceLineItem',
            \TCStripe\IssuerFraudRecord::OBJECT_NAME => 'TCStripe\\IssuerFraudRecord',
            \TCStripe\Issuing\Authorization::OBJECT_NAME => 'TCStripe\\Issuing\\Authorization',
            \TCStripe\Issuing\Card::OBJECT_NAME => 'TCStripe\\Issuing\\Card',
            \TCStripe\Issuing\CardDetails::OBJECT_NAME => 'TCStripe\\Issuing\\CardDetails',
            \TCStripe\Issuing\Cardholder::OBJECT_NAME => 'TCStripe\\Issuing\\Cardholder',
            \TCStripe\Issuing\Dispute::OBJECT_NAME => 'TCStripe\\Issuing\\Dispute',
            \TCStripe\Issuing\Transaction::OBJECT_NAME => 'TCStripe\\Issuing\\Transaction',
            \TCStripe\LoginLink::OBJECT_NAME => 'TCStripe\\LoginLink',
            \TCStripe\Order::OBJECT_NAME => 'TCStripe\\Order',
            \TCStripe\OrderItem::OBJECT_NAME => 'TCStripe\\OrderItem',
            \TCStripe\OrderReturn::OBJECT_NAME => 'TCStripe\\OrderReturn',
            \TCStripe\PaymentIntent::OBJECT_NAME => 'TCStripe\\PaymentIntent',
            \TCStripe\PaymentMethod::OBJECT_NAME => 'TCStripe\\PaymentMethod',
            \TCStripe\Payout::OBJECT_NAME => 'TCStripe\\Payout',
            \TCStripe\Person::OBJECT_NAME => 'TCStripe\\Person',
            \TCStripe\Plan::OBJECT_NAME => 'TCStripe\\Plan',
            \TCStripe\Product::OBJECT_NAME => 'TCStripe\\Product',
            \TCStripe\Radar\EarlyFraudWarning::OBJECT_NAME => 'TCStripe\\Radar\\EarlyFraudWarning',
            \TCStripe\Radar\ValueList::OBJECT_NAME => 'TCStripe\\Radar\\ValueList',
            \TCStripe\Radar\ValueListItem::OBJECT_NAME => 'TCStripe\\Radar\\ValueListItem',
            \TCStripe\Recipient::OBJECT_NAME => 'TCStripe\\Recipient',
            \TCStripe\RecipientTransfer::OBJECT_NAME => 'TCStripe\\RecipientTransfer',
            \TCStripe\Refund::OBJECT_NAME => 'TCStripe\\Refund',
            \TCStripe\Reporting\ReportRun::OBJECT_NAME => 'TCStripe\\Reporting\\ReportRun',
            \TCStripe\Reporting\ReportType::OBJECT_NAME => 'TCStripe\\Reporting\\ReportType',
            \TCStripe\Review::OBJECT_NAME => 'TCStripe\\Review',
            \TCStripe\SKU::OBJECT_NAME => 'TCStripe\\SKU',
            \TCStripe\Sigma\ScheduledQueryRun::OBJECT_NAME => 'TCStripe\\Sigma\\ScheduledQueryRun',
            \TCStripe\Source::OBJECT_NAME => 'TCStripe\\Source',
            \TCStripe\SourceTransaction::OBJECT_NAME => 'TCStripe\\SourceTransaction',
            \TCStripe\Subscription::OBJECT_NAME => 'TCStripe\\Subscription',
            \TCStripe\SubscriptionItem::OBJECT_NAME => 'TCStripe\\SubscriptionItem',
            \TCStripe\SubscriptionSchedule::OBJECT_NAME => 'TCStripe\\SubscriptionSchedule',
            \TCStripe\SubscriptionScheduleRevision::OBJECT_NAME => 'TCStripe\\SubscriptionScheduleRevision',
            \TCStripe\TaxId::OBJECT_NAME => 'TCStripe\\TaxId',
            \TCStripe\TaxRate::OBJECT_NAME => 'TCStripe\\TaxRate',
            \TCStripe\ThreeDSecure::OBJECT_NAME => 'TCStripe\\ThreeDSecure',
            \TCStripe\Terminal\ConnectionToken::OBJECT_NAME => 'TCStripe\\Terminal\\ConnectionToken',
            \TCStripe\Terminal\Location::OBJECT_NAME => 'TCStripe\\Terminal\\Location',
            \TCStripe\Terminal\Reader::OBJECT_NAME => 'TCStripe\\Terminal\\Reader',
            \TCStripe\Token::OBJECT_NAME => 'TCStripe\\Token',
            \TCStripe\Topup::OBJECT_NAME => 'TCStripe\\Topup',
            \TCStripe\Transfer::OBJECT_NAME => 'TCStripe\\Transfer',
            \TCStripe\TransferReversal::OBJECT_NAME => 'TCStripe\\TransferReversal',
            \TCStripe\UsageRecord::OBJECT_NAME => 'TCStripe\\UsageRecord',
            \TCStripe\UsageRecordSummary::OBJECT_NAME => 'TCStripe\\UsageRecordSummary',
            \TCStripe\WebhookEndpoint::OBJECT_NAME => 'TCStripe\\WebhookEndpoint',
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
                $class = 'TCStripe\\StripeObject';
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
        if ($h instanceof \TCStripe\ApiResource) {
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
