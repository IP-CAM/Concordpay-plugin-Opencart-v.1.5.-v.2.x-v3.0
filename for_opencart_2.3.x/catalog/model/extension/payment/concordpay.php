<?php

/**
 * Class ModelExtensionPaymentConcordpay
 *
 * @property Loader $load
 * @property Language $language
 * @property Config $config
 */
class ModelExtensionPaymentConcordpay extends Model
{
    /**
     * @param $address
     * @param $total
     * @return array
     */
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/concordpay');

        $method_data = array(
            'code' => 'concordpay',
            'title' => $this->language->get('text_title'),
            'sort_order' => $this->config->get('concordpay_sort_order'),
            'terms' => ''
        );

        return $method_data;
    }
}
