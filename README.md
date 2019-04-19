plugin-magento
=============

LiqPay plugin for magento CMS version 2.1

Более подробно о работе с системой Liqpay https://www.liqpay.ua/documentation/ru

Tested for Magento version:
- 2.1.6

thanks to 
Volodymyr Konstanchuk http://konstanchuk.com

Основные функции:
- прием платежей с помощью платежней системы LiqPay;
- отслеживания оплаты;
- изменения статуса платежа и создания накладной;
- поддержка тестового режима;
- использует официальное SDK LiqPay.

Установка:
- установите официально SDK LiqPay следующей командой:
composer require liqpay/liqpay
Может понадобится добавления строк
"minimum-stability": "dev",
"prefer-stable": true,
в composer.json
- скопируйте папку с модулем в корень сайта;
- запустите следующие команды (может понадобится sudo):
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:clean
- все команды должны закончится успешно. В app/etc/config.php должен появится
данный модуль данный модуль.

Настройка:
- перейдите в admin -> stores -> configuration -> sales -> payment methods -> liqpay
(должен быть в самом низу);
- указать приватный и публичный ключ в настройках и включить модуль в поле
Enabled. (если приватный и публичный ключ не указан, он не будет включен)
- выбрать режим (тестовый или не тестовый)
- после изменения любой конфигурации нужно чистить кеш (php bin/magento
cache:clean).

Проверка:
- положите товар в корзину и перейдите на чекаут.
- на последнем этапе чекаута в выборе оплаты должен появится метод оплаты LiqPay.
- если он не появился, смотрите логи в папке [SITE_ROOT]/var/log
- после выбора ликпея и нажатия на кнопку 'place order' должно перебросить на
страницу оплаты.

Callback:
для получения результата проведения платежа на сервер нужно:
- в настройках мерчанта Liqpay указать server_url​ http://your_host/rest/V1/liqpay/callback, где ​http://your_host - адрес вашего сайта.
- после проведения платежа Liqpay пришлет запрос на http://your_host/rest/V1/liqpay/callback, более подробна на https://www.liqpay.ua/documentation/api/callback



Troubleshooting:

- может понадобиться изменение лимита памяти в файле конфигурации, например:
chown apache:root /var/www/
grep memory_limit /etc/php.ini  # set memory limit for composer
memory_limit = 1280M
;memory_limit = 128M

- если товар не добавляется в корзину, попробуйте
http://magehelper.blogspot.in/2017/03/magento-2-cannot-add-products-to-cart.html

