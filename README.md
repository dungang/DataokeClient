# DataokeClient

最简洁的淘宝客API客户端

## 安装

```sh
composer require dungang/dataoke-client

```

## 使用

> 淘宝客pid组成介绍：pid=mm_1_2_3（其中1/2/3分别代表一串数字，举例pid=mm_98836808_12970065_68140878）
> 说明：
> 1这串数字对应淘宝客的账户id(通常称member)；
> 2这串数字对应媒体管理中备案的网站或APP(统称site，包含网站ID、APPID)；
> 3这串数字对应网站或APP中的具体推广位(通常称adzone)。
> 
> 每一个网站(网站ID)或APP(APPID)，均可申请自己的appkey，供对应网站/APP使用，
> 调用api时系统会校验是否应用于对应网站ID或APPID。
> 如果appkey不匹配或传递参数错误，会不算淘客交易，切记!

* 只用传递业务参数
* 方法参数不用传递，客户端自动识别，比如 获取商品详情的api为taobao.tbk.item.info.get,则执行的方法就是为taobaoTbkItemInfoGet
* 接口中的true和false都必须字符串，如：`'true'` `'false'` 而不是 `true` `false`

```php
$client = new Client('你的APP_KEY', '你的APP_SECRET');
try {
    $data = $client->(array(
        'num_iids' => '563889454088'
    ));
    echo "最后执行的完整的URL：" . $client->getLastRequestUrl() . "\n";
    echo "获取的数据：\n";
    print_r($data);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

