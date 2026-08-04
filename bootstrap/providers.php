<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TranslationServiceProvider::class,
    App\Providers\ModulesServiceProvider::class,



    /** CUSTOMER MODULE */
    Modules\Customer\Providers\CustomerServiceProvider::class,

    /** PRODUCT MODULE */
    Modules\Product\Providers\ProductServiceProvider::class,

    /** Order MODULE */
    Modules\Order\Providers\OrderServiceProvider::class,

    Modules\OneC\Providers\OneCServiceProvider::class,
    Modules\OtpCode\Providers\OtpCodeServiceProvider::class,
];
