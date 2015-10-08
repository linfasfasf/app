<?php

$config = array(
    'sso_enabled'=>FALSE,
    'sso_mock_server_enabled'=>FALSE,
    'environment' => 'mockup_api',
    //'environment' => 'sandbox_api',
    'mockup_api' =>array(
        'sso_client_id'=>102,         // client_id = 102 对应 cocos开发者平台
        'is_signin' =>'http://localhost/sso_mock_server/is_signin_success',
        //'is_signin' =>'http://localhost/sso_mock_server/is_signin_fail',
        'signin'=>'http://localhost/sso_mock_server/signin',
        //'st_validate'=>'http://localhost/sso_mock_server/st_validate_fail',
        'st_validate'=>'http://localhost/sso_mock_server/st_validate_success',
        'signout' => 'http://localhost/sso_mock_server/signout',
    ), 
    'sandbox_api'=>array(
        'sso_client_id'=>102,         // client_id = 102 对应 cocos开发者平台
        // 实际为 192.168.90.17 , 改 hosts
        'is_signin' =>'http://passport.cocos.com/sso/is_signin',
        'signin'=>'http://passport.cocos.com/sso/signin',
        'st_validate'=>'http://passport.cocos.com/sso/st_validate',
        'signout' => 'http://passport.cocos.com/sso/signout',
    ),
    'product_api'=>array(
        'sso_client_id'=>102,         // client_id = 102 对应 cocos开发者平台
        'is_signin' =>'https://passport.cocos.com/sso/is_signin',
        'signin'=>'https://passport.cocos.com/sso/signin',
        'st_validate'=>'https://passport.cocos.com/sso/st_validate',
        'signout' => 'https://passport.cocos.com/sso/signout',
    ),
);
                  
