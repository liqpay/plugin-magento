plugin-magento
=============

Плагін LiqPay для Magento CMS версії 2.4

Детальніше про роботу з системою LiqPay [https://www.liqpay.ua/doc](https://www.liqpay.ua/doc)

Тестовано для версії Magento:

    2.4.6

Дякуємо
Володимир Констанчук http://konstanchuk.com

Основні функції:

    прийом платежів за допомогою платіжної системи LiqPay;
    відстеження оплати;
    зміна статусу платежу та створення накладної;
    використовує офіційне SDK LiqPay.

Встановлення:

    встановіть офіційне SDK LiqPay за допомогою команди:
    composer require liqpay/liqpay
    Може знадобитися додавання рядків
    "minimum-stability": "dev",
    "prefer-stable": true,
    у composer.json
    скопіюйте папку з модулем до кореня сайту;
    запустіть наступні команди (може знадобитися sudo):
    php bin/magento setup
    php bin/magento setup:di
    php bin/magento setup:static-content
    php bin/magento cache
    всі команди повинні завершитися успішно. У файлі app/etc/config.php має з'явитися
    цей модуль.

Налаштування:

    перейдіть до admin -> stores -> configuration -> sales -> payment methods -> liqpay
    (має бути в самому низу);
    вкажіть приватний та публічний ключ у налаштуваннях та увімкніть модуль у полі
    Enabled. (якщо приватний та публічний ключ не вказані, він не буде увімкнений)
    виберіть режим (тестовий або не тестовий)
    після зміни будь-якої конфігурації потрібно чистити кеш (php bin/magento
    cache
    ).

Перевірка:

    додайте товар до кошика та перейдіть до чекауту.
    на останньому етапі чекауту у виборі оплати має з'явитися метод оплати LiqPay.
    якщо він не з'явився, перегляньте логи у папці [SITE_ROOT]/var/log
    після вибору LiqPay та натискання на кнопку 'place order' має перекинути на
    сторінку оплати.

Callback:
для отримання результату проведення платежу на сервер потрібно:

    у налаштуваннях мерчанта Liqpay вказати server_url https://your_host/liqpay/callback/index, де https://your_host - адреса вашого сайту.
    після проведення платежу Liqpay надішле запит на https://your_host/liqpay/callback/index, детальніше на https://www.liqpay.ua/doc

Вирішення проблем:

    може знадобитися зміна ліміту пам'яті у файлі конфігурації, наприклад:
    chown apache
    /var/www/
    grep memory_limit /etc/php.ini # встановіть ліміт пам'яті для composer
    memory_limit = 1280M
    ;memory_limit = 128M

    якщо товар не додається до кошика, спробуйте
    http://magehelper.blogspot.in/2017/03/magento-2-cannot-add-products-to-cart.html
