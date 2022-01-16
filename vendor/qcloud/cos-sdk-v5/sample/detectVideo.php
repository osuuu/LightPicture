<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$secretId = "SECRETID"; //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$secretKey = "SECRETKEY"; //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$region = "ap-beijing"; //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket
$cosClient = new Qcloud\Cos\Client(
    array(
        'region' => $region,
        'schema' => 'https', //协议头部，默认为http
        'credentials'=> array(
            'secretId'  => $secretId ,
            'secretKey' => $secretKey)));
try {
    //存储桶视频审核
    $result = $cosClient->detectVideo(array(
        'Bucket' => 'examplebucket-125000000', //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
        'Input' => array(
            'Object' => 'test.mp4', // 存储桶文件
        ),
        'Conf' => array(
            'DetectType' => 'Porn,Terrorism,Politics,Ads',
//            'Callback' => 'https://example.com/callback',
//            'BizType' => '',
//            'DetectContent' => 1,
//            'CallbackVersion' => 'Detail',
            'Snapshot' => array(
//                'Mode' => 'Average',
//                'TimeInterval' => 50,
                'Count' => '3',
            ),
        ),
    ));

    //视频url审核
    $videoUrl = 'http://example.com/test.mp4';
    $result = $cosClient->detectVideo(array(
        'Bucket' => 'examplebucket-125000000', //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
        'Input' => array(
            'Url' => $videoUrl, // 视频url
        ),
        'Conf' => array(
            'DetectType' => 'Porn,Terrorism,Politics,Ads',
//            'Callback' => 'https://example.com/callback',
//            'BizType' => '',
//            'DetectContent' => 1,
//            'CallbackVersion' => 'Detail',
            'Snapshot' => array(
//                'Mode' => 'Average',
//                'TimeInterval' => 50,
                'Count' => '3',
            ),
        ),
    ));

    // 请求成功
    print_r($result);
} catch (\Exception $e) {
    // 请求失败
    echo($e);
}

