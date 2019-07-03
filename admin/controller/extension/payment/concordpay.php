<?php

class ControllerExtensionPaymentConcordpay extends Controller {
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/concordpay');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_concordpay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true));
        }

        $arr = array(
            "heading_title", "text_payment", "text_success", "text_pay", "text_card",
            "entry_merchant", "entry_secretkey", "entry_order_status", "entry_currency",
            "entry_approveUrl", "entry_callbackUrl", "entry_declineUrl", 'entry_cancelUrl',
            "entry_language", "entry_status", "entry_sort_order",
            "error_permission", "error_merchant", "error_secretkey",
            "error_approveUrl", "error_callbackUrl", 'error_declineUrl', 'error_cancelUrl'
        );

        foreach ($arr as $v) {
            $data[$v] = $this->language->get($v);
        }
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');

        $arr = array('warning', 'merchant', 'secretkey', 'type', 'approveUrl', 'callbackUrl', 'declineUrl', 'cancelUrl');
        foreach ($arr as $v)
            $data['error_' . $v] = (isset($this->error[$v])) ? $this->error[$v] : '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']  . '&type=payment', true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/concordpay', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('extension/payment/concordpay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $arr = array("payment_concordpay_merchant", "payment_concordpay_secretkey", "payment_concordpay_currency",
            "payment_concordpay_approveUrl", "payment_concordpay_callbackUrl", 'payment_concordpay_declineUrl', 'payment_concordpay_cancelUrl',
            "payment_concordpay_language", "payment_concordpay_status", "payment_concordpay_sort_order", "payment_concordpay_order_status_id");

        foreach ($arr as $v) {
            $data[$v] = (isset($this->request->post[$v])) ? $this->request->post[$v] : $this->config->get($v);
            if (defined('HTTP_CATALOG') and defined('HTTPS_CATALOG') and !isset($this->request->post[$v])) {
                if ($v == 'payment_concordpay_approveUrl' and empty($data[$v])) {
                    $data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=extension/payment/concordpay/approve';
                }
                elseif ($v == 'payment_concordpay_callbackUrl' and empty($data[$v])) {
                    $data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=extension/payment/concordpay/callback';
                }
                elseif (($v == 'payment_concordpay_declineUrl' || $v == 'payment_concordpay_cancelUrl') and empty($data[$v])) {
                    $data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=extension/payment/concordpay/decline';
                }
            }
        }
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        if (isset($this->request->post['payment_concordpay_geo_zone_id'])) {
            $data['payment_concordpay_geo_zone_id'] = $this->request->post['payment_concordpay_geo_zone_id'];
        } else {
            $data['payment_concordpay_geo_zone_id'] = $this->config->get('payment_concordpay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/concordpay', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/concordpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_concordpay_merchant']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }

        if (!$this->request->post['payment_concordpay_secretkey']) {
            $this->error['secretkey'] = $this->language->get('error_secretkey');
        }

        if (!$this->request->post['payment_concordpay_approveUrl']) {
            $this->error['returnUrl'] = $this->language->get('error_approveUrl');
        }

        if (!$this->request->post['payment_concordpay_callbackUrl']) {
            $this->error['serviceUrl'] = $this->language->get('error_callbackUrl');
        }

        if (!$this->request->post['payment_concordpay_declineUrl']) {
            $this->error['declineUrl'] = $this->language->get('error_declineUrl');
        }

        if (!$this->request->post['payment_concordpay_cancelUrl']) {
            $this->error['cancelUrl'] = $this->language->get('error_cancelUrl');
        }
        $ret = !$this->error;
        return $ret;
    }
}
