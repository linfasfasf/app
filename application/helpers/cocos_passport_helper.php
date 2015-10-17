<?php
function is_signin($client_id){
            $ci = &get_instance();
            $ci->config->load("sso_client",true);
            $sso_client_settings = $ci->config->item('sso_client');
            $environment = $sso_client_settings['environment'];
            $settings = $sso_client_settings[$environment];
            $client_id = $settings['sso_client_id'];
    
            $api = $settings['is_signin'];
            $signin_call = $api.'?client_id='.$client_id;
            $response = str_replace('result =','',file_get_contents($signin_call));
            $json_obj = json_decode($response);
            //var_dump($json_obj);
            if($json_obj->sign_status == 'yes'){
                return $json_obj->st;
            }
            return FALSE;
}

/* 验证  $st 的有效性
 * 现在没有用到， 这个功能是 在  sso_client controller 实施的
 */
function st_validate($client_id, $st){
}

/* 在 admin 页面判断已登录时
 * 调用这个来完成登录
 */
function sso_login($st){
    // TODO: implement
    return FALSE; 
}

function my_send_request($url, $params, $method='get', $followlocation=TRUE, $header=FALSE){

    $ch = curl_init();
    /*
       $options = array(
        CURLOPT_URL => $url,
        CURLOPT_POST => TRUE, //使用post提交
        CURLOPT_POSTFIELDS => http_build_query($data), //post的数据
    );
    curl_setopt_array($curlObj, $options);
     */
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla');
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, $header); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//接收服务端范围的html代码而不是直接浏览器输出
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followlocation); 
    // SSL 设置
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 信任任何证书 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //
    if($method=='get'){
        $querystring = http_build_query($params,'','&');
        curl_setopt($ch, CURLOPT_URL, $url . '?' .$querystring);
    }else{
        curl_setopt($ch, CURLOPT_URL, $url);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        ///curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
