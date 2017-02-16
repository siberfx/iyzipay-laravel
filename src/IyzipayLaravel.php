<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\Card\PayableMustHaveCreditCardException;
use Actuallymab\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Card\CardRemoveException;
use Actuallymab\IyzipayLaravel\Exceptions\Card\CreditCardFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Actuallymab\IyzipayLaravel\Exceptions\Iyzipay\IyzipayAuthenticationException;
use Actuallymab\IyzipayLaravel\Exceptions\Iyzipay\IyzipayConnectionException;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Actuallymab\IyzipayLaravel\Traits\PreparesCreditCardRequest;
use Actuallymab\IyzipayLaravel\Traits\PreparesTransactionRequest;
use Iyzipay\Model\ApiTest;
use Iyzipay\Options;
use Iyzipay\Model\Locale;
use Actuallymab\IyzipayLaravel\PayableContract as Payable;

class IyzipayLaravel
{

    use PreparesCreditCardRequest, PreparesTransactionRequest;

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
    public function addCreditCard(Payable $payable, array $attributes = []): CreditCard
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
     * Remove credit card for billable & payable model.
     * @param CreditCard $creditCard
     * @return bool
     * @throws CardRemoveException
     */
    public function removeCreditCard(CreditCard $creditCard): bool
    {
        $this->removeCardOnIyzipay($creditCard);
        $creditCard->delete();

        return true;
    }

    /**
     * @param PayableContract $payable
     * @param $products
     * @param $currency
     * @param $installment
     * @return Transaction $transactionModel
     * @throws BillFieldsException
     * @throws PayableMustHaveCreditCardException
     * @throws TransactionSaveException
     */
    public function singlePayment(Payable $payable, $products, $currency, $installment): Transaction
    {
        $this->validateBillable($payable);
        $this->validateHasCreditCard($payable);

        $message = '';
        foreach ($payable->creditCards as $creditCard) {
            try {
                $transaction = $this->createTransactionOnIyzipay($payable, $creditCard,
                    compact('products', 'currency', 'installment'));

                $transactionModel = new Transaction([
                    'amount' => $transaction->getPaidPrice(),
                    'products' => $products
                ]);
                $transactionModel->creditCard()->associate($creditCard);
                $payable->transactions()->save($transactionModel);

                return $transactionModel->fresh();
            } catch (TransactionSaveException $e) {
                $message = $e->getMessage();
                continue;
            }
        }

        throw new TransactionSaveException($message);
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
        if (!$this->isBillable($payable)) {
            throw new BillFieldsException();
        }
    }

    /**
     * @param PayableContract $payable
     * @throws PayableMustHaveCreditCardException
     */
    private function validateHasCreditCard(Payable $payable): void
    {
        if ($payable->creditCards->isEmpty()) {
            throw new PayableMustHaveCreditCardException();
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
