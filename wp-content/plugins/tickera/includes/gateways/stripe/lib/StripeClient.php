<?php

// File generated from our OpenAPI spec

namespace TCStripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \TCStripe\Service\AccountLinkService $accountLinks
 * @property \TCStripe\Service\AccountService $accounts
 * @property \TCStripe\Service\ApplePayDomainService $applePayDomains
 * @property \TCStripe\Service\ApplicationFeeService $applicationFees
 * @property \TCStripe\Service\BalanceService $balance
 * @property \TCStripe\Service\BalanceTransactionService $balanceTransactions
 * @property \TCStripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \TCStripe\Service\ChargeService $charges
 * @property \TCStripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \TCStripe\Service\CountrySpecService $countrySpecs
 * @property \TCStripe\Service\CouponService $coupons
 * @property \TCStripe\Service\CreditNoteService $creditNotes
 * @property \TCStripe\Service\CustomerService $customers
 * @property \TCStripe\Service\DisputeService $disputes
 * @property \TCStripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \TCStripe\Service\EventService $events
 * @property \TCStripe\Service\ExchangeRateService $exchangeRates
 * @property \TCStripe\Service\FileLinkService $fileLinks
 * @property \TCStripe\Service\FileService $files
 * @property \TCStripe\Service\InvoiceItemService $invoiceItems
 * @property \TCStripe\Service\InvoiceService $invoices
 * @property \TCStripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \TCStripe\Service\MandateService $mandates
 * @property \TCStripe\Service\OAuthService $oauth
 * @property \TCStripe\Service\OrderReturnService $orderReturns
 * @property \TCStripe\Service\OrderService $orders
 * @property \TCStripe\Service\PaymentIntentService $paymentIntents
 * @property \TCStripe\Service\PaymentMethodService $paymentMethods
 * @property \TCStripe\Service\PayoutService $payouts
 * @property \TCStripe\Service\PlanService $plans
 * @property \TCStripe\Service\PriceService $prices
 * @property \TCStripe\Service\ProductService $products
 * @property \TCStripe\Service\PromotionCodeService $promotionCodes
 * @property \TCStripe\Service\Radar\RadarServiceFactory $radar
 * @property \TCStripe\Service\RefundService $refunds
 * @property \TCStripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \TCStripe\Service\ReviewService $reviews
 * @property \TCStripe\Service\SetupIntentService $setupIntents
 * @property \TCStripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \TCStripe\Service\SkuService $skus
 * @property \TCStripe\Service\SourceService $sources
 * @property \TCStripe\Service\SubscriptionItemService $subscriptionItems
 * @property \TCStripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \TCStripe\Service\SubscriptionService $subscriptions
 * @property \TCStripe\Service\TaxRateService $taxRates
 * @property \TCStripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \TCStripe\Service\TokenService $tokens
 * @property \TCStripe\Service\TopupService $topups
 * @property \TCStripe\Service\TransferService $transfers
 * @property \TCStripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \TCStripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \TCStripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
