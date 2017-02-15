<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\BillFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\CreditCardFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\IyzipayAuthenticationException;
use Actuallymab\IyzipayLaravel\Exceptions\IyzipayConnectionException;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Traits\PreparesCreditCardRequest;
use Iyzipay\Model\ApiTest;
use Iyzipay\Options;
use Iyzipay\Model\Locale;
use Actuallymab\IyzipayLaravel\PayableContract as Payable;

class IyzipayLaravel
{

    use PreparesCreditCardRequest;

    /**
     * @var Options
     */
    protected $apiOptions;

    public function __construct()
    {
        $this->initializeApiOptions();
        $this->checkApiOptions();
    }

    /**
     * Adds credit card for billable & payable model.
     *
     * @param PayableContract $payable
     * @param array $attributes
     * @return CreditCard
     * @throws BillFieldsException
     * @throws CreditCardFieldsException
     */
    public function addCreditCard(Payable $payable, array $attributes = [])
    {
        $this->validateBillable($payable);
        $this->validateCreditCardAttributes($attributes);

        $card = $this->createCardOnIyzipay($payable, $attributes);

        $creditCardModel = new CreditCard([
            'alias' => $card->getCardAlias(),
            'number' => $card->getBinNumber(),
            'token' => $card->getCardToken(),
            'bank' => $card->getCardBankName()
        ]);
        $payable->creditCards()->save($creditCardModel);

        $payable->setBillFields([
            'iyzipay_key' => $card->getCardUserKey()
        ]);

        return $creditCardModel;
    }

    /**
     * Initializing API options with the given credentials.
     */
    private function initializeApiOptions()
    {
        $this->apiOptions = new Options();
        $this->apiOptions->setBaseUrl(config('iyzipay.baseUrl'));
        $this->apiOptions->setApiKey(config('iyzipay.apiKey'));
        $this->apiOptions->setSecretKey(config('iyzipay.secretKey'));
    }

    /**
     * Check if api options has been configured successfully.
     *
     * @throws IyzipayAuthenticationException
     * @throws IyzipayConnectionException
     */
    private function checkApiOptions()
    {
        try {
            $check = ApiTest::retrieve($this->apiOptions);
        } catch (\Exception $e) {
            throw new IyzipayConnectionException();
        }

        if ($check->getStatus() != 'success') {
            throw new IyzipayAuthenticationException();
        }
    }

    /**
     * @param PayableContract $payable
     * @throws BillFieldsException
     */
    private function validateBillable(Payable $payable): void
    {
        if (! $this->isBillable($payable)) {
            throw new BillFieldsException();
        }
    }

    /**
     * Check if payable model has bill attributes
     *
     * @param PayableContract $payable
     * @return bool
     */
    private function isBillable(Payable $payable): bool
    {
        return (!empty($payable->getBillFields()));
    }

    protected function getLocale(): string
    {
        return (config('app.locale') == 'tr') ? Locale::TR : Locale::EN;
    }

    protected function getOptions(): Options
    {
        return $this->apiOptions;
    }
}
