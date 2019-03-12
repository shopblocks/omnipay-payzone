<?php

namespace Omnipay\PayZone\Message;

use Omnipay\Common\Message\AbstractResponse;

class DummyResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $this->data = $data;

        return $this;
    }

    public function isSuccessful()
    {
        if (is_array($this->form)) {
            return !empty($this->form['endpoint']) && $this->form['endpoint'] == "https://mms.payzoneonlinepayments.com/Pages/PublicPages/PaymentForm.aspx" && !empty($this->form['HashDigest']) && preg_match('/\w+/', $this->form['HashDigest']);
        } else {
            return preg_match("/<form method='post' action='https:\/\/mms.payzoneonlinepayments.com\/Pages\/PublicPages\/PaymentForm.aspx' id='payzone-form'.*<\/form><script>.*payzone-form'\)\.submit.*<\/script>/", $this->form);
        }
    }

    public function isForm()
    {
        return true;
    }

    public function getData()
    {
        return $this->form;
    }

    public function getForm()
    {
        $form = "<form method='post' action='{$this->form['endpoint']}' id='cardsave-form'>";
        foreach ($this->form as $key => $value) {
            if ($key == "endpoint") {
                continue;
            }

            $form .= "<input type='hidden' name='{$key}' value='{$value}'>";
        }
        $form .= "</form>";
        $form .= "<script>document.getElementById('cardsave-form').submit();</script>";

        return $form;
    }
}
