<?php

/**
 * Class ControllerPaymentConcordpay
 *
 * @property Loader $load
 * @property Document $document
 * @property Request $request
 * @property ModelSettingSetting $model_setting_setting
 * @property Session $session
 * @property Response $response
 * @property Language $language
 * @property Url $url
 * @property ModelLocalisationOrderStatus $model_localisation_order_status
 * @property ModelLocalisationCurrency $model_localisation_currency
 * @property Config $config
 * @property User $user
 * @property Error $error
 */
class ControllerPaymentConcordpay extends Controller
{
    /**
     * @var array
     */
    private $error = array();

    /**
     * @var string[]
     */
    protected $allowed_payment_page_locales;

    /**
     * ControllerPaymentConcordpay constructor.
     *
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->allowed_payment_page_locales = array('ru', 'uk', 'en');
    }

    /**
     * Shows plugin settings page.
     *
     * @throws Exception
     */
    public function index()
    {
        $this->load->language('payment/concordpay');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Update settings.
        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('concordpay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->redirect(
                $this->url->link(
                    'extension/payment',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        // Show translated plugin settings page.
        $arr = array(
            'heading_title',
            'text_payment',
            'text_success',
            'text_pay',
            'text_card',
            'entry_merchant',
            'entry_secretkey',
            'entry_order_status',
            'entry_order_reverse_status',
            'entry_currency',
            'entry_approve_url',
            'entry_decline_url',
            'entry_cancel_url',
            'entry_callback_url',
            'entry_language',
            'entry_status',
            'entry_sort_order',
            'error_permission',
            'error_merchant',
            'error_secretkey',
            'help_approve_url',
            'help_decline_url',
            'help_cancel_url',
            'help_callback_url',
            'help_sort_order',
        );

        foreach ($arr as $v) {
            $this->data[$v] = $this->language->get($v) ? $this->language->get($v) : '';
        }
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');

        $arr = array(
            'warning',
            'merchant',
            'secretkey',
            'type'
        );

        foreach ($arr as $v) {
            $this->data['error_' . $v] = (isset($this->error[$v])) ? $this->error[$v] : '';
        }

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/home',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link(
                'extension/payment',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'payment/concordpay',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link(
            'payment/concordpay',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $this->data['cancel'] = $this->url->link(
            'extension/payment',
            'token=' . $this->session->data['token'],
            'SSL'
        );

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $arr = array(
            'concordpay_status',
            'concordpay_merchant',
            'concordpay_secretkey',
            'concordpay_currency',
            'concordpay_language',
            'concordpay_approve_url',
            'concordpay_decline_url',
            'concordpay_cancel_url',
            'concordpay_callback_url',
            'concordpay_order_status_id',
            'concordpay_order_reverse_status_id',
            'concordpay_sort_order',
        );

        foreach ($arr as $v) {
            $this->data[$v] = isset($this->request->post[$v]) ?
                $this->request->post[$v] :
                $this->config->get($v);
            if (defined('HTTP_CATALOG') &&
                defined('HTTPS_CATALOG') &&
                ! isset($this->request->post[$v]) &&
                empty($this->data[$v])
            ) {
                if ($v === 'concordpay_approve_url') {
                    $this->data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) .
                        'index.php?route=payment/concordpay/approve';
                } elseif ($v === 'concordpay_decline_url') {
                    $this->data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) .
                        'index.php?route=payment/concordpay/decline';
                } elseif ($v === 'concordpay_cancel_url') {
                    $this->data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) .
                        'index.php?route=payment/concordpay/cancel';
                } elseif ($v === 'concordpay_callback_url') {
                    $this->data[$v] = (isset($_SERVER['HTTPS']) ? HTTPS_CATALOG : HTTP_CATALOG) .
                        'index.php?route=payment/concordpay/callback';
                }
            }
        }

        $this->load->model('localisation/currency');
        $this->data['currencies'] = array();
        $currencies = $this->model_localisation_currency->getCurrencies();
        foreach ($currencies as $currency) {
            if ($currency['status']) {
                $this->data['currencies'][] = array(
                    'title'        => $currency['title'],
                    'code'         => $currency['code'],
                    'symbol_left'  => $currency['symbol_left'],
                    'symbol_right' => $currency['symbol_right']
                );
            }
        }

        $this->data['allowed_payment_page_locales'] = $this->allowed_payment_page_locales;

        $this->template = 'payment/concordpay.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /**
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/concordpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['concordpay_merchant']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }

        if (!$this->request->post['concordpay_secretkey']) {
            $this->error['secretkey'] = $this->language->get('error_secretkey');
        }

        return ! $this->error;
    }
}
