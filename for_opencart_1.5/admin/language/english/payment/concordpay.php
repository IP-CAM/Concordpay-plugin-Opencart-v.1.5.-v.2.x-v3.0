<?php
// Heading
$_['heading_title'] = 'ConcordPay';

// Text
$_['text_payment'] = 'Payment';
$_['text_concordpay'] = '<a href="https://pay.concord.ua" target="_blank" style="background: url(view/image/payment/concordpay.svg) 0 0 no-repeat !important;"><img src="view/image/payment/concordpay.png" alt="ConcordPay" title="ConcordPay"></a>';
$_['text_success'] = 'Settings updated';
$_['text_pay'] = 'ConcordPay';
$_['text_card'] = 'Credit Card';

// Entry
$_['entry_merchant'] = 'Merchant Account';
$_['entry_secretkey'] = 'Secret key';

$_['entry_order_status'] = 'Order status after successful payment';
$_['entry_order_reverse_status'] = 'Order status after refunded payment';
$_['entry_currency'] = 'Currency';
$_['entry_language'] = 'Payment page language';

$_['entry_approve_url'] = 'Redirect URL on successful payment';
$_['entry_decline_url'] = 'Redirect URL on failed payment';
$_['entry_cancel_url'] = 'Redirect URL on canceled payment';
$_['entry_callback_url'] = 'URL of the result information';

$_['entry_status'] = 'Status';
$_['entry_sort_order'] = 'Ordering';

// Help
$_['help_approve_url'] = 'Default: http://{YOUR_SITE}/index.php?route=payment/concordpay/approve';
$_['help_decline_url'] = 'Default: http://{YOUR_SITE}/index.php?route=payment/concordpay/decline';
$_['help_cancel_url'] = 'Default: http://{YOUR_SITE}/index.php?route=payment/concordpay/cancel';
$_['help_callback_url'] = 'Default: http://{YOUR_SITE}/index.php?route=payment/concordpay/callback';
$_['help_sort_order'] = 'ConcordPay position in the list of payment methods';

// Error
$_['error_permission'] = "You haven't permission!";
$_['error_merchant'] = 'Merchant Account is incorrect!';
$_['error_secretkey'] = 'Secret key is empty!';
?>
