<?php

namespace Omnipay\PayZone\Message;

use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractRequest
{
    private $endpoint = "https://mms.payzoneonlinepayments.com/Pages/PublicPages/PaymentForm.aspx";

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getPreSharedKey()
    {
        return $this->getParameter('preSharedKey');
    }

    public function setPreSharedKey($value)
    {
        return $this->setParameter('preSharedKey', $value);
    }

    public function getTransactionType()
    {
        return $this->getParameter('transactionType');
    }

    public function setTransactionType($value)
    {
        return $this->setParameter('transactionType', $value);
    }

    public function getData()
    {
        $data = [];

        $data['HashDigest'] = "";
		$data['PreSharedKey'] = $this->getPreSharedKey();
        $data['MerchantID'] = $this->getMerchantId();
        $data['Amount'] = $this->getAmountInteger();
        $data['FullAmount'] = $this->getAmountInteger();
        $data['CurrencyCode'] = $this->getCurrencyNumeric();

        $data['OrderID'] = $this->getTransactionId();
        $data['TransactionType'] = $this->getTransactionType();

        $now = \Carbon\Carbon::now();
        $data['TransactionDateTime'] = $now->format('Y-m-d H:i:s');

        $data['CallbackURL'] = $this->getNotifyUrl();

        $data['OrderDescription'] = '';
        $data['CustomerName'] = '';
        $data['Address1'] = '';
        $data['Address2'] = '';
        $data['Address3'] = '';
        $data['Address4'] = '';
        $data['City'] = '';
        $data['State'] = '';
        $data['PostCode'] = '';
        $data['CountryCode'] = '';
        $data['EmailAddress'] = '';
        $data['PhoneNumber'] = '';
        $data['EmailAddressEditable'] = 'false';
        $data['PhoneNumberEditable'] = 'false';

        $data['CV2Mandatory'] = 'true';
        $data['Address1Mandatory'] = 'false';
        $data['CityMandatory'] = 'false';
        $data['PostCodeMandatory'] = 'false';
        $data['StateMandatory'] = 'false';
        $data['CountryMandatory'] = 'false';

        $data['ResultDeliveryMethod'] = 'POST';
        $data['ServerResultURL'] = '';
        $data['PaymentFormDisplaysResult'] = 'false';
        $data['ThreeDSecureCompatMode'] = 'false';

        $hashDigest = $this->generateHash($data);
		$data['HashDigest'] = $hashDigest;

		unset($data['PreSharedKey']);

		return $data;
    }

    public function sendData($data)
    {

    }

	private function generateHash($data)
    {
        $hashString = "";
        $hashString .= "PreSharedKey=" . ($data['PreSharedKey'] ?? '');
        $hashString .= "&MerchantID=" . ($data['MerchantID'] ?? '');
        $hashString .= "&Password=" . ($this->getPassword() ?? '');
        $hashString .= "&Amount=" . ($data['Amount'] ?? 0);
        $hashString .= "&CurrencyCode=" . ($data['CurrencyCode'] ?? '');
        $hashString .= "&EchoAVSCheckResult=false";
        $hashString .= "&EchoCV2CheckResult=true";
        $hashString .= "&EchoThreeDSecureAuthenticationCheckResult=false";
        $hashString .= "&EchoCardType=false";
        $hashString .= "&OrderID=" . ($data['OrderID'] ?? '');
        $hashString .= "&TransactionType=" . ($data['TransactionType'] ?? 'SALE');
        $hashString .= "&TransactionDateTime=" . ($data['TransactionDateTime'] ?? '');
        $hashString .= "&CallbackURL=" . ($data['CallbackURL'] ?? '');
        $hashString .= "&OrderDescription=" . ($data['OrderDescription'] ?? '');
        $hashString .= "&CustomerName=" . ($data['CustomerName'] ?? '');
        $hashString .= "&Address1=" . ($data['Address1'] ?? '');
        $hashString .= "&Address2=" . ($data['Address2'] ?? '');
        $hashString .= "&Address3=" . ($data['Address3'] ?? '');
        $hashString .= "&Address4=" . ($data['Address4'] ?? '');
        $hashString .= "&City=" . ($data['City'] ?? '');
        $hashString .= "&State=" . ($data['State'] ?? '');
        $hashString .= "&PostCode=" . ($data['PostCode'] ?? '');
        $hashString .= "&CountryCode=" . ($data['CountryCode'] ?? '');
        $hashString .= "&EmailAddress=";
        $hashString .= "&PhoneNumber=";
        $hashString .= "&EmailAddressEditable=false";
        $hashString .= "&PhoneNumberEditable=false";
        $hashString .= "&CV2Mandatory=true";
        $hashString .= "&Address1Mandatory=false";
        $hashString .= "&CityMandatory=false";
        $hashString .= "&PostCodeMandatory=false";
        $hashString .= "&StateMandatory=false";
        $hashString .= "&CountryMandatory=false";
        $hashString .= "&ResultDeliveryMethod=POST";
        $hashString .= "&ServerResultURL=";
        $hashString .= "&PaymentFormDisplaysResult=false";
        return sha1($hashString);
    }
}