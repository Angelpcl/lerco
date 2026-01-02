<?php

$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/db.php';

$config = [
    'id'         => 'panelSeaExpress',
    'name'       => "LERCO Tickets",
    'version'    => '0.1.0',
    'language'   => 'es-MX',
    'timeZone'   => 'America/Mexico_City',
    'basePath'   => dirname(__DIR__),
    'bootstrap'  => ['log', 'app\components\Aliases'],
    /*'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],*/
    'modules'    => [
        'admin'          => [
            'class' => 'app\modules\admin\Module',
        ],
        'caja'           => [
            'class' => 'app\modules\caja\Module',
        ],
        'crm'            => [
            'class' => 'app\modules\crm\Module',
        ],
        'operacion'      => [
            'class' => 'app\modules\operacion\Module',
        ],
        'productos'      => [
            'class' => 'app\modules\productos\Module',
        ],
        'promociones'    => [
            'class' => 'app\modules\promociones\Module',
        ],
        'descarga'       => [
            'class' => 'app\modules\descarga\Module',
        ],
        'sucursales'     => [
            'class' => 'app\modules\sucursales\Module',
        ],
        'logistica'      => [
            'class' => 'app\modules\logistica\Module',
        ],
        'reportes'       => [
            'class' => 'app\modules\reportes\Module',
        ],
        'pagos'          => [
            'class' => 'app\modules\pagos\Module',
        ],
        'paises'         => [
            'class' => 'app\modules\paises\Module',
        ],
        'zonas'          => [
            'class' => 'app\modules\zonas\Module',
        ],
        'promocionessuc' => [
            'class' => 'app\modules\promocionessuc\Module',
        ],
        'v1'             => [
            'class' => 'app\modules\v1\Module',
        ],
        'movil'          => [
            'class' => 'app\modules\movil\Module',
        ],
    ],
    'components' => [
        'request'          => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'nteSskI2Fd9kGJzWokwYRhv37Wx8oH5V',

            /* IMPLEMENTACION PARA WEB SERVICE*/
            'parsers'             => [
                'application/json' => 'yii\web\JsonParser',
            ],
            /**/
        ],
        'cache'            => [
            'class' => 'yii\caching\FileCache',
        ],
        'assetManager'     => [
            'bundles' => [
                'yii\web\JqueryAsset'                 => [
                    'jsOptions' => [
                        'position' => \yii\web\View::POS_HEAD,
                    ],
                    'js'        => [
                        'jquery.min.js',
                    ],
                ],
                'yii\bootstrap4\BootstrapAsset'       => [
                    'css' => [
                        'css/bootstrap.min.css',
                    ],
                ],
                'yii\bootstrap4\BootstrapPluginAsset' => [
                    'js' => [
                        'js/bootstrap.min.js',
                    ],
                ],
                'kartik\form\ActiveFormAsset'         => [
                    'bsDependencyEnabled' => false, // do not load bootstrap assets for a specific asset bundle
                ],
            ],
        ],
        'nifty'            => [
            'class' => 'app\components\NiftyComponent',
        ],
        'barcodegenerator' => [
            'class' => 'app\components\BarcodeGeneratorComponent',
        ],
        'user'             => [
            'identityClass'   => 'app\models\user\UserIdentity',
            'enableAutoLogin' => false,
            'loginUrl'      => array('admin/user/login'),

        ],
        /*'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],*/
        'session'          => [
            'class'    => 'yii\web\Session',
            'savePath' => '@app/runtime/session',
        ],
        'errorHandler'     => [
            'errorAction' => 'site/error',
        ],
        'authManager'      => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
        ],
        'formatter'        => [
            'class'          => 'yii\i18n\Formatter',
            'dateFormat'     => 'php:d-m-Y',
            'datetimeFormat' => 'php:d-m-Y H:i:s',
            'timeFormat'     => 'php:H:i:s',
            //'locale' => 'es-MX', // (opcional si necesitas formato en español)
        ],

        /*'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/translations',
                    'sourceLanguage' => 'es-MX',
                ],
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/translations',
                    'sourceLanguage' => 'es-MX'
                ],
            ],
        ],*/
        /*
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'lerco.mx',
                'username'   => 'daniel.gaona@app.lerco.mx',
                'password'   => 'daniel2019',
                'port'       => 25,
                'encryption' => false,
            ],
        ], */

        'mailer'           => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport'        => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'lercomx.com',
                'username'   => 'tickets@lercomx.com',
                'password'   => 'tickets@2025',
                'port'       => '465',
                'encryption' => 'ssl',

            ],
        ],

        /* 'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],*/
        'log'              => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'       => 'yii\log\FileTarget',
                    'levels'      => ['error', 'warning', 'info'],                    // Agregar 'info' aquí
                    'categories'  => ['application', 'emailDebug*', 'ticketStatus*'], // Categorías personalizadas
                    'logFile'     => '@runtime/logs/app.log',                         // Ruta explícita
                    'maxFileSize' => 1024 * 2,                                        // 2MB máximo
                    'maxLogFiles' => 20,                                              // Máximo 20 archivos
                ],
                // Opcional: puedes agregar otro target para errores graves
                [
                    'class'       => 'yii\log\FileTarget',
                    'levels'      => ['error'],
                    'logFile'     => '@runtime/logs/errors.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                ],
            ],
        ],
        'db'               => $db,
        'urlManager'       => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            //'suffix' => '.html',
            'rules'           => [
                ''             => 'operacion/ticket/index',
                '<action:\w+>' => 'site/<action>',
            ],
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params'     => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
