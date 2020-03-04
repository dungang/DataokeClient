# DataokeClient

最简洁的淘宝客API客户端

## 安装

```sh
composer require dungang/dataoke-client

```

## 使用

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

