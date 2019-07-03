<?php

class ControllerExtensionPaymentConcordpay extends Controller {
    public $codesCurrency = array(
        980 => 'UAH',
    );

    public function index()
    {
        $this->language->load('extension/payment/concordpay');
        $this->load->model('checkout/order');

        $fields = $this->generateFields();
        $names = $fields['productName'];
        $prices = $fields['productPrice'];
        $counts = $fields['productCount'];
        unset($fields['productName']);
        unset($fields['productPrice']);
        unset($fields['productCount']);
        $data['action'] = ConcordPay::URL;
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['fields'] = $fields;
        $data['prod_name'] = $names;
        $data['prod_price'] = $prices;
        $data['prod_count'] = $counts;
        $data['text_loading'] = 'loading';
        $data['order_id'] = $this->session->data['order_id'];

        return $this->load->view('extension/payment/concordpay', $data);
    }

    public function generateFields() {
        $con = new ConcordPay();
        $key = $this->config->get('payment_concordpay_secretkey');
        $con->setSecretKey($key);

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $approve_url = $this->config->get('payment_concordpay_approveUrl');
        $callback_url = $this->config->get('payment_concordpay_callbackUrl');
        $decline_url = $this->config->get('payment_concordpay_decline_url');
        $cancel_url = $this->config->get('payment_concordpay_cancelUrl');


        $currency = isset($this->codesCurrency[$order['currency_code']]) ? $this->codesCurrency[$order['currency_code']] : $order['currency_code'];
        $amount = round(($order['total'] * $order['currency_value']), 2);

        $fields = array(
            'operation' => 'Purchase',
            'merchant_id' => $this->config->get('payment_concordpay_merchant'),
            'amount' => $amount,
            'order_id' => $order['order_id'],
            'currency_iso' => $currency,
            'description' => 'Order description',
            'add_params' => 'AddParams',
            'approve_url' => $approve_url,
            'decline_url' => $decline_url,
            'cancel_url' => $cancel_url,
            'callback_url' => $callback_url,
        );

        $productNames = array();
        $productQty = array();
        $productPrices = array();
        $this->load->model('account/order');
        $products = $this->model_account_order->getOrderProducts($order['order_id']);
        foreach ($products as $product) {
            $productNames[] = str_replace(array("'", '"', '&#39;', '&'), '', htmlspecialchars_decode($product['name']));
            $productPrices[] = $product['price'];
            $productQty[] = $product['quantity'];
        }

        $fields['productName'] = $productNames;
        $fields['productPrice'] = $productPrices;
        $fields['productCount'] = $productQty;
        $fields['clientFirstName'] = $order['payment_firstname'];
        $fields['clientLastName'] = $order['payment_lastname'];
        $fields['clientEmail'] = $order['email'];
        $fields['clientPhone'] = $order['telephone'];
        $fields['clientCity'] = $order['payment_city'];
        $fields['clientAddress'] = $order['payment_address_1'] . ' ' . $order['payment_address_2'];
        $fields['clientCountry'] = $order['payment_iso_code_3'];

        $fields['signature'] = $con->getRequestSignature($fields);

        return $fields;
    }

    public function confirm()
    {
        if ($this->session->data['payment_method']['code'] == 'concordpay') {
            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if ( ! $order_info) {
                return;
            }

            $order_id = $this->session->data['order_id'];

            if ($order_info['order_status_id'] == 0) {
                $this->model_checkout_order->confirm($order_id, $this->config->get('concordpay_order_status_progress_id'), 'ConcordPay');

                return;
            }

            if ($order_info['order_status_id'] != $this->config->get('payment_concordpay_order_status_id')) {
                $this->model_checkout_order->update($order_id, $this->config->get('payment_concordpay_order_status_id'), 'ConcordPay', true);
            }
        }
    }

    public function approve()
    {
        $this->response->redirect($this->url->link('checkout/success','', 'SSL'));
    }

    public function decline() {
        $this->response->redirect($this->url->link('checkout/checkout','', 'SSL'));
    }

    public function callback()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $con = new ConcordPay();
        $key = $this->config->get('payment_concordpay_secretkey');
        $con->setSecretKey($key);
        $data['merchant_id'] = $data['merchantAccount'];
        $data['currency_iso'] = $data['currency'];

        $paymentInfo = $con->isPaymentValid($data);

        if ($paymentInfo === true) {

            $order_id = $data['orderReference'];

            $this->load->model('checkout/order');

            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_concordpay_order_status_id'));
        }
        else {
            echo $paymentInfo;
        }
        exit();
    }
}

class ConcordPay
{
    const ORDER_NEW = 'New';
    const ORDER_DECLINED = 'Declined';
    const ORDER_REFUND_IN_PROCESSING = 'RefundInProcessing';
    const ORDER_REFUNDED = 'Refunded';
    const ORDER_EXPIRED = 'Expired';
    const ORDER_PENDING = 'Pending';
    const ORDER_APPROVED = 'Approved';
    const ORDER_WAITING_AUTH_COMPLETE = 'WaitingAuthComplete';
    const ORDER_IN_PROCESSING = 'InProcessing';
    const ORDER_SEPARATOR = '#';

    const SIGNATURE_SEPARATOR = ';';

    const URL = "https://pay.concord.ua/api/";

    protected $secret_key = '';

    protected $keysForResponse = array(
        'merchant_id',
        'orderReference',
        'amount',
        'currency_iso'
    );

    /** @var array */
    protected $keysForRequest = array(
        'merchant_id',
        'order_id',
        'amount',
        'currency_iso',
        'description'
    );

    /**
     * @param $option
     * @param $keys
     * @return string
     */
    public function getSignature($option, $keys)
    {
        $hash = array();
        foreach ($keys as $dataKey) {
            if (!isset($option[$dataKey])) {
                $option[$dataKey] = '';
            }
            if (is_array($option[$dataKey])) {
                foreach ($option[$dataKey] as $v) {
                    $hash[] = $v;
                }
            } else {
                $hash [] = $option[$dataKey];
            }
        }

        $hash = implode(self::SIGNATURE_SEPARATOR, $hash);
        return hash_hmac('md5', $hash, $this->getSecretKey());
    }

    /**
     * @param $options
     * @return string
     */
    public function getRequestSignature($options)
    {
        return $this->getSignature($options, $this->keysForRequest);
    }

    /**
     * @param $options
     * @return string
     */
    public function getResponseSignature($options)
    {
        return $this->getSignature($options, $this->keysForResponse);
    }

    /**
     * @param $response
     * @return bool|string
     */
    public function isPaymentValid($response)
    {
        $sign = $this->getResponseSignature($response);
        if ($sign != $response['merchantSignature']) {
            return $sign;
        }

        if ($response['transactionStatus'] == self::ORDER_APPROVED) {
            return true;
        }

        return false;
    }

    public function setSecretKey($key)
    {
        $this->secret_key = $key;
    }

    public function getSecretKey()
    {
        return $this->secret_key;
    }
}