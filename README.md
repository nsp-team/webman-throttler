# webman-throttler
限流类（Throttler)提供了一种非常简单的方法，可以将用户要执行的活动限制为在设定的时间段内只能进行一定次数的尝试。这最常用于对 API 进行速率限制，或限制用户针对表单进行的尝试次数，以帮助防止暴力攻击。 该类可用于你根据设置的时间来进行限制的操作。




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