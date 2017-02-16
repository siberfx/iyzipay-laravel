<?php


namespace Actuallymab\IyzipayLaravel\Traits;

use Actuallymab\IyzipayLaravel\Exceptions\Fields\TransactionFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\PayableContract as Payable;
use Actuallymab\IyzipayLaravel\ProductContract;
use Illuminate\Support\Facades\Validator;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Currency;
use Iyzipay\Model\Payment;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\PaymentChannel;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreatePaymentRequest;


trait PreparesTransactionRequest
{

    protected function validateTransactionFields($attributes): void
    {
        $v = Validator::make($attributes, [
            'installment' => 'required|numeric|min:1',
            'currency' => 'required|in:' . implode(',', [
                    Currency::TL,
                    Currency::EUR,
                    Currency::GBP,
                    Currency::IRR,
                    Currency::USD
                ])
        ]);

        foreach ($attributes['products'] as $product) {
            if (!$product instanceof ProductContract) {
                throw new TransactionFieldsException();
            }
        }

        if ($v->fails()) {
            throw new TransactionFieldsException();
        }
    }

    /**
     * @param Payable $payable
     * @param CreditCard $creditCard
     * @param array $attributes
     * @return Payment
     * @throws TransactionSaveException
     */
    protected function createTransactionOnIyzipay(Payable $payable, CreditCard $creditCard, array $attributes): Payment
    {
        $this->validateTransactionFields($attributes);
        $paymentRequest = $this->createPaymentRequest($attributes);
        $paymentRequest->setPaymentCard($this->preparePaymentCard($payable, $creditCard));
        $paymentRequest->setBuyer($this->prepareBuyer($payable));
        $paymentRequest->setShippingAddress($this->prepareAddress($payable, 'shipping_address'));
        $paymentRequest->setBillingAddress($this->prepareAddress($payable, 'billing_address'));
        $paymentRequest->setBasketItems($this->prepareBasketItems($attributes['products']));

        try {
            $payment = Payment::create($paymentRequest, $this->getOptions());
        } catch (\Exception $e) {
            throw new TransactionSaveException();
        }

        unset($paymentRequest);

        if ($payment->getStatus() != 'success') {
            throw new TransactionSaveException($payment->getErrorMessage());
        }

        return $payment;
    }

    private function createPaymentRequest(array $attributes): CreatePaymentRequest
    {
        $paymentRequest = new CreatePaymentRequest();
        $paymentRequest->setLocale($this->getLocale());

        $totalPrice = 0;
        foreach ($attributes['products'] as $product) {
            $totalPrice += $product->getPrice();
        }

        $paymentRequest->setPrice($totalPrice);
        $paymentRequest->setPaidPrice($totalPrice); // @todo this may change
        $paymentRequest->setCurrency($attributes['currency']);
        $paymentRequest->setInstallment($attributes['installment']);
        $paymentRequest->setPaymentChannel(PaymentChannel::WEB);
        $paymentRequest->setPaymentGroup(PaymentGroup::PRODUCT);

        return $paymentRequest;
    }

    private function preparePaymentCard(Payable $payable, CreditCard $creditCard): PaymentCard
    {
        $paymentCard = new PaymentCard();
        $paymentCard->setCardUserKey($payable->getBillFields()['iyzipay_key']);
        $paymentCard->setCardToken($creditCard->token);

        return $paymentCard;
    }

    private function prepareBuyer(Payable $payable): Buyer
    {
        $buyer = new Buyer();
        $buyer->setId($payable->getId());

        $billFields = $payable->getBillFields();
        $buyer->setName($billFields['first_name']);
        $buyer->setSurname($billFields['last_name']);
        $buyer->setEmail($billFields['email']);
        $buyer->setGsmNumber($billFields['mobile_number']);
        $buyer->setIdentityNumber($billFields['identity_number']);
        $buyer->setCity($billFields['billing_address']['city']);
        $buyer->setCountry($billFields['billing_address']['country']);
        $buyer->setRegistrationAddress($billFields['billing_address']['address']);

        return $buyer;
    }

    private function prepareAddress(Payable $payable, $type = 'shipping_address'): Address
    {
        $address = new Address();

        $billFields = $payable->getBillFields();
        $address->setContactName($billFields['first_name'] . ' ' . $billFields['last_name']);
        $address->setCountry($billFields[$type]['country']);
        $address->setAddress($billFields[$type]['address']);
        $address->setCity($billFields[$type]['city']);

        return $address;
    }

    private function prepareBasketItems(array $products = []): array
    {
        $basketItems = [];

        foreach ($products as $product) {
            $item = new BasketItem();
            $item->setId($product->getKey());
            $item->setName($product->getName());
            $item->setCategory1($product->getCategory());
            $item->setPrice($product->getPrice());
            $item->setItemType(BasketItemType::VIRTUAL); // @todo check out this.
            $basketItems[] = $item;
        }

        return $basketItems;
    }

    abstract protected function getLocale(): string;

    abstract protected function getOptions(): Options;
}