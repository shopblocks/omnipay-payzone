<?php

namespace Omnipay\PayZone\Message;

use DOMDocument;
use SimpleXMLElement;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\PayZone\Message\DummyResponse;

class PurchaseRequest extends AbstractRequest
{
    protected $endpoint = "https://gw1.payzoneonlinepayments.com:4430";
    protected $namespace = 'https://www.thepaymentgateway.net/';

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
        return "SALE"; //$this->getParameter('transactionType');
    }

    public function setTransactionType($value)
    {
        return $this->setParameter('transactionType', $value);
    }

	public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getOrderId()
    {
        return $this->getParameter('OrderId');
    }

    public function setOrderId($value)
    {
        return $this->setParameter('OrderId', $value);
    }

    public function getReturnForm()
    {
        return $this->getParameter('returnForm');
    }

    public function setReturnForm($value)
    {
        return $this->setParameter('returnForm', $value);
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

        $data["EchoAVSCheckResult"] = 'false';
        $data["EchoCV2CheckResult"] = 'true';
        $data["EchoThreeDSecureAuthenticationCheckResult"] = 'false';
        $data["EchoCardType"] = 'false';

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
        if (is_array($data)) {
            $this->endpoint = "https://mms.payzoneonlinepayments.com/Pages/PublicPages/PaymentForm.aspx";

            $form = "<form method='post' action='{$this->endpoint}' id='payzone-form'>";
            $response = [];

            $response['endpoint'] = $this->endpoint;
            foreach ($data as $key => $value) {
                $form .= "<input type='hidden' name='{$key}' value='{$value}'>";
                $response[$key] = $value;
            }

            $form .= "</form>";

            $form .= "<script>document.getElementById('payzone-form').submit();</script>";

            if ($this->getReturnForm()) {
                return new DummyResponse($response);
            }

            echo($form);
            exit;
        }
        
        // the PHP SOAP library sucks, and SimpleXML can't append element trees
        // TODO: find PSR-0 SOAP library
        $document = new DOMDocument('1.0', 'utf-8');
        $envelope = $document->appendChild(
            $document->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soap:Envelope')
        );
        $envelope->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $envelope->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $body = $envelope->appendChild($document->createElement('soap:Body'));
        $body->appendChild($document->importNode(dom_import_simplexml($data), true));

        // post to Cardsave
        $headers = array(
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $this->namespace.$data->getName());

        $httpResponse = $this->httpClient->post($this->endpoint, $headers, $document->saveXML())->send();

        return $this->response = new Response($this, $httpResponse->getBody());
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
        $hashString .= "&TransactionType=" . ($data['TransactionType'] ?? '');
        $hashString .= "&TransactionDateTime=" . ($data['TransactionDateTime'] ?? '');
        $hashString .= "&CallbackURL=" . ($data['CallbackURL'] ?? '');
        $hashString .= "&OrderDescription=" . ($data['Description'] ?? '');

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
