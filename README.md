## 介绍
Laravel Ddd是一个基于领域驱动设计思想的代码框架，帮助你快速地建立起符合领域驱动设计的代码目录结构。

## 项目地址
https://github.com/ronghz/laravel-ddd
https://packagist.org/packages/ronghz/laravel-ddd
https://github.com/ronghz/laravel-ddd-demo

## 安装
```
composer require ronghz/laravel-ddd
```
在config/app.php增加ServiceProvider配置 
```php
Ronghz\LaravelDdd\LaravelDddServiceProvider::class,
```
特别地，需要在\App\Console\Kernel的构造函数里注册一下MigrationServiceProvider，才能使用migrate命令来发现放在领域目录下的迁移脚本。
```php
public function __construct(Application $app, Dispatcher $events)
{
    parent::__construct($app, $events);
    $app->register(MigrationServiceProvider::class);
}
```

## 使用
### 生成领域目录和代码
以文章领域为例，创建新领域时，先执行```php artisan ddd-generator Article```
在app/Domain目录下会生成领域的基本目录结构。

然后在app/Domain/Article/Models/migrations目录下编写这个领域所需要的迁移脚本，如增加一个数据表article_articles表。

执行```php artisan migrate```生成数据表，框架已经对migrate命令作了扩展，可以发现领域目录下的迁移脚本文件。

执行```php artisan ddd-generator Article --model=Article --table=articel_articles```，生成Article聚合根的相关类文件。

最终生成目录和文件如下图所示：
```php
├── app               // 代码目录
│ ├── Domain          // 领域代码目录
│ │ ├── Article       // 文章领域
│ │ │ ├── Commands    // 领域脚本
│ │ │ ├── Events      // 事件
│ │ │ ├── Jobs        // 
│ │ │ ├── Listeners   // 
│ │ │ ├── Models        // 模型层
│ │ │ │ ├── migrations  // 模块迁移脚本
│ │ │ │ ├── Article.php
│ │ │ ├── Repositories  // 仓库层
│ │ │ │ ├── ArticleRepository.php
│ │ │ ├── Resources     // API资源类
│ │ │ │ ├── ArticleResource.php
│ │ │ ├── Ports         // 接口层
│ │ │ │ ├── Cross       // 跨域调用接口
│ │ │ │ │ ├── ArticleCrossDomain.php
│ │ │ │ ├── Platform    // 平台管理端接口
│ │ │ │ │ ├── Controllers // 这个端的接口
│ │ │ │ │ │ ├── ArticleController.php
│ │ │ │ │ ├── Requests   // 这个端的输入参数类
│ │ │ │ │ │ ├── ArticleResource.php
│ │ │ │ │ ├── Services    // 应用服务
│ │ │ │ │ │ ├── ArticleService.php
│ │ │ │ │ ├── routes.php  // 这个端的路由配置
│ │ │ │ ├── Customer // 客户端接口
│ │ │ ├── Services   // 领域服务
│ │ │ │ ├── ArticleService.php
│ │ │ ├── Supports   // 
│ │ │ │ ├── Dtos     // 枚举变量
│ │ │ │ ├── Enums    // 枚举变量
│ │ │ │ ├── Exceptions// 领域内的异常
```

## 配置文件说明 ddd.php
```php
return [
    'router' => [
        'use_auto_router' => true, //是否使用自动映射路由
        'project_prefix' => false, //自动路由是否有项目名称前缀
        'client_version_key' => 'Release-Version', //接口版本请求头
    ],

    'generator' => [
        'ports' => ['Platform', 'Merchant', 'Customer']
    ],
];
```

## 分层职责和调用限制

### Controller
Controller放在{domain}/Ports目录下，是客户端请求的入口，如果系统包括多个不同的客户端，应该分开不同的Controller，例如PC端的管理后台和手机端的App，要分成两个端。

返回数据时，调用```php $this->success($data)``` 会根据$data里的Model类调用对应的Resource类组装数据。

Controller应该只包含参数的校验和输出的格式化逻辑，不应该在Controller里编写业务逻辑。

### routes
路由文件routes.php放在对应的Ports目录下，约定所有接口的url按{客户端}/{领域名}/{聚合名}/{其它}的规则来定义，避免不同领域的之间的冲突。

### Application Service
应用服务，每个端的Controller会有一个对应的ApplicationService，用来处理这个端独有的业务逻辑。

ApplicationService不是必须的，允许调用同领域的DomainService和Repository，或者跨域的CrossDomainService。

### Domain Service
领域服务，负责领域的业务逻辑。

DomainService可以调用同领域的Repository，禁止直接调用的Model，避免调用CrossDomain。

### Repository
封装数据查询、变更操作，如findByXXX()方法。

只允许调用同领域的Model。

### Model
只需要定义模型的表名、字段名以及关联关系。
migration文件也按领域目录放置。

### Resource
跟Model一一对应，在Controller的方法里里调用$this->success()返回数据时，会自动根据Model的类型调用对应的Resource类。

### Cross Domain Service
跨域调用服务，调用其它领域的方法时，必须通过CrossDomainService。

CrossDomainService的地位等同于Controller，可以调用同领域的ApplicationService和DomainService，或者跨域的CrossDomainService。

### DTO （未实现）
Data Transfer Object，数据传输对象，跨域调用的时候不应该直接返回Model对象，而是应该使用DTO再在不同领域间传递数据。

## 其它特性
### 自动加载相关类
多数类会根据类名自动加载同一领域下的同一聚合根的相关类，例如ArticleController会自动加载ArticleService,ArticleService会自动加载ArticleRepository。

### 自动映射路由
可选特性，默认不开启。如果需要使用，在\App\Http\Kernel里增加一个Middleware```\Ronghz\LaravelDdd\Framework\Middleware\AutoRouter::class```，再把配置文件中的router.use_auto_router设置为true。

开启自动映射路由后，可以不在routes.php里配置路由规则，框架会自动按照{客户端}/{领域名}/{控制器名}/{方法名}的规则来把url解析到对应的方法。
例如 GET /customer/article/author/index会映射到App\Domain\Article\Ports\Customer\Controllers\AuthorController:getIndex()。
{方法名}后面的segment全部作为url参数。

url的单词用中划线区分，对应的类名和方法名会转成驼峰格式。
如果url需要加上项目前缀，就把配置文件中的router.project_prefix，自动映射时会把url的第一段识别为项目名。

同一个url如果在routes.php里做了配置，会优先使用配置的映射关系，不使用自动路由的映射。

### 接口版本控制
项目迭代过程中，经常需要对接口进行版本升级，针对不同的客户端版本返回不同的数据。

框架支持按照客户端请求头的版本来做接口版本控制，只要跟客户端约定接口版本的命名方式以及传参方式，框架默认通过Release-Version请求头。

初始情况下，不需要做任何处理。当需要升级接口时，覆盖对应的Controller的VERSION_RANGE变量，然后再增加一个新版本的方法，新方法的名字用原方法名加上版本号。

例如，文章列表的接口在1.2.3和2.2.1两个版本做了升级，这时候ArticleController会变成这样。
```php
class ArticleController extends DddController
{
    const VERSION_RANGE = [
        'getRange' => ['1.2.3', '2.2.1']
    ];

    public function getRange()
    {
        echo 'default';
    }

    public function getRangeV1_2_3()
    {
        echo '1.2.3';
    }

    public function getRangeV2_2_1()
    {
        echo '2.2.1';
    }
}
```

### 异常处理


### 枚举基类
枚举类型都应该继承Ronghz\LaravelDdd\Framework\Base\DddEnum基类，这个类里实现了枚举名、枚举值和描述文本三者互相转换的方法。

### 单元测试
使用代码生成器生成代码的时候，会同时生成领域服务的单元测试类。
