# webman-throttler
webman of webman-throttler plugin 


限流类

* 全局中间件，整个应用接口限流，
* 路由中间件，某些功能接口请求速率限制

缓存依据的是`Support\Cache的 instance()`, 其他类只要是实现 `get($key, $default = null)`, `set($key, $value, $ttl = null)`, `delete($key)` funtion就行.


> 项目地址：https://github.com/nsp-team/webman-throttler

## 安装
`composer require nsp-team/webman-throttler`

## 基本用法
> 默认 开启全局中间件限流
```php
return [
    '' => [
        \NspTeam\WebmanThrottler\Middleware\ThrottlerMiddleware::class,
    ]
];
```

> 你也可以启用路由中间件，控制接口请求速率限制
例如：
```php
Route::group('/sys/user', static function () {
    Route::post('/test', [User::class, 'test']);
})->middleware([
    \NspTeam\WebmanThrottler\Middleware\ThrottlerMiddleware::class
]);
```