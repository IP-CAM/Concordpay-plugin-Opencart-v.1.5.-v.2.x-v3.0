<?php
// Heading
$_['heading_title'] = 'ConcordPay';

// Text
$_['text_payment'] = 'Оплата';
$_['text_concordpay'] = '<a onclick="window.open(\'https://concord.ua/\');">concordpay</a>';
$_['text_success'] = 'Настройки модуля обновлены!';
$_['text_pay'] = 'ConcordPay';
$_['text_card'] = 'Credit Card';
$_['text_all_zones'] = 'All Zones';

// Entry
$_['entry_merchant'] = 'Merchant Account:';
$_['entry_secretkey'] = 'Secret key:';

$_['entry_order_status'] = 'Статус заказа после оплаты:';
$_['entry_currency'] = 'Валюта мерчанта';
$_['entry_approveUrl'] = 'Ссылка возврата клиента при успешном платеже:<br /><span class="help">http://{your_domain}/index.php?route=extension/payment/concordpay/approve</span>';
$_['entry_callbackUrl'] = 'Ссылка возврата для сервера:<br /><span class="help">http://{your_domain}/index.php?route=extension/payment/concordpay/callback</span>';
$_['entry_declineUrl'] = 'Ссылка возврата клиента при не успешном платеже:<br /><span class="help">http://{your_domain}/index.php?route=extension/payment/concordpay/decline</span>';
$_['entry_cancelUrl'] = 'Ссылка возврата клиента при отмене платежа:<br /><span class="help">http://{your_domain}/index.php?route=extension/payment/concordpay/cancel</span>';
$_['entry_language'] = 'Язык страницы:<br /><span class="help">по-умолчанию : RU </span>';
$_['entry_geo_zone']    = 'Geo Zone';

$_['entry_status'] = 'Статус:';
$_['entry_sort_order'] = 'Порядок сортировки:';

// Error
$_['error_permission'] = 'У Вас нет прав для управления этим модулем!';
$_['error_merchant'] = 'Merchant Account не верен!';
$_['error_secretkey'] = 'Secret key пуст!';
$_['error_approveUrl'] = 'Обязателен approveUrl!';
$_['error_callbackUrl'] = 'Обязателен callbackUrl!';
$_['error_declineUrl'] = 'Обязателен declineUrl!';
$_['error_cancelUrl'] = 'Обязателен cancelUrl!';
?>