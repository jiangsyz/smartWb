<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-store',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'store\controllers',
    'defaultRoute' => 'product/list',
    'bootstrap' => ['log'],
    'aliases' => [  
        //'@mdm/admin' => '@vendor/mdmsoft/yii2-admin-2.2',
        '@mdm/admin' => '@vendor/mdmsoft/yii2-admin',
    ],  
    'modules' => [  
        'admin' => [  
            'class' => 'mdm\admin\Module',  
            //'layout' => 'left-menu',  
            'controllerMap' => [  
                'assignment' => [  
                    'class' => 'mdm\admin\controllers\AssignmentController',  
                    //'userClassName' => 'common\models\User',  
                    'userClassName' => 'store\models\Staff',  
                    'idField' => 'id'  
                ]  
            ],  
            'menus' => [
                'assignment' => [  
                    'label' => 'Grand Access' // change label  
                ],  
              //'route' => null, // disable menu route  
            ]  
        ],  
        'debug' => [  
            'class' => 'yii\debug\Module',
            ],  
    ],
    //yii2admin默认访问控制
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [//允许访问  
            'site/*',  
            'admin/*',
            'zs-api/*',
            'tb-tool/*',
            'forlulu/*',
            'tool/*',
        ]  
    ],  
    'components' => [
        'staff' => [
        //管理员  
            'class' => '\yii\web\User',  
            'loginUrl' => array('/site/login'),//没有登录就跳转到  
            'idParam'           => '_aId',  
            'identityCookie'    => ['name'=>'_aa','httpOnly' => true],  
            'identityClass' => 'store\models\Staff',  
            'enableAutoLogin' => true,
        ],  
        "authManager" => [
            "class" => 'yii\rbac\DbManager',
            "defaultRoles" => ["guest"],
        ],
        /*'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,//设为false则隐藏index.php 
            //'enableStrictParsing' => false,
            //'suffix' => '.html',//后缀，如果设置了此项，那么浏览器地址栏就必须带上.html后缀，否则会报404错误
            'rules' => [
                //'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],*/
        'request' => [
            'csrfParam' => '_csrf-store',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-store', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the store
            'name' => 'advanced-store',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        //api调用
        'smartApi' => [
            //模块类
            'class' => 'common\components\smartApi\smartApi',
            //超时秒数
            'timeOut' => 5,
        ],
        //微信
        'smartWechat' => [
            //模块类
            'class' => 'common\components\smartWechat\smartWechat',
        ],
    ],
    'timeZone'=>'Asia/Shanghai',
    //'timeZone'=>'PRC', 
    'params' => $params,
];
