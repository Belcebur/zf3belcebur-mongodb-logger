# zf3-mongodb-logger
MongoDB Logger

## Installation

Installation of this module uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
composer require zf3belcebur/mongodb-logger
```

Then add `ZF3Belcebur\MongoDBLogger` to your `config/application.config.php` and copy `config/zf3belcebur-mongodb-logger.global.php.dist` to your autoload config folder


```php
namespace ZF3Belcebur\MongoDBLogger;

use ZF3Belcebur\MongoDBLogger\Factory\ErrorLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\ExceptionLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\FatalErrorLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request2XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request3XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request4XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request5XXLoggerFactory;
use Zend\Log\Processor\RequestId;

return [
    'service_manager' => [
        'factories' => [
            FatalErrorLoggerFactory::SERVICE_NAME => FatalErrorLoggerFactory::class,
            ErrorLoggerFactory::SERVICE_NAME => ErrorLoggerFactory::class,
            ExceptionLoggerFactory::SERVICE_NAME => ExceptionLoggerFactory::class,
            Request2XXLoggerFactory::SERVICE_NAME => Request2XXLoggerFactory::class,
            Request3XXLoggerFactory::SERVICE_NAME => Request3XXLoggerFactory::class,
            Request4XXLoggerFactory::SERVICE_NAME => Request4XXLoggerFactory::class,
            Request5XXLoggerFactory::SERVICE_NAME => Request5XXLoggerFactory::class,
        ],
    ],
    __NAMESPACE__ => [
        'writers' => [
            'defaultOptions' => [
                'manager' => [
                    'uri' => 'mongodb://127.0.0.1/',
                    'uriOptions' => [],
                    'driverOptions' => [],
                ],
                'database' => 'rubi_log',
                'collection' => 'base',
                'writeConcern' => null,
                'formatter' => null,
                'filters' => [],
            ],
            ErrorLoggerFactory::SERVICE_NAME => [
                'collection' => 'error_handler',
            ],
            ExceptionLoggerFactory::SERVICE_NAME => [
                'collection' => 'exception',
            ],
            FatalErrorLoggerFactory::SERVICE_NAME => [
                'collection' => 'fatal_error',
            ],
            Request2XXLoggerFactory::SERVICE_NAME => [
                'collection' => 'request_2xx',
            ],
            Request3XXLoggerFactory::SERVICE_NAME => [
                'collection' => 'request_3xx',
            ],
            Request4XXLoggerFactory::SERVICE_NAME => [
                'collection' => 'request_4xx',
            ],
            Request5XXLoggerFactory::SERVICE_NAME => [
                'collection' => 'request_5xx',
            ],
        ],
        'processors' => [
            'default' => [
                'requestId' => [
                    'name' => RequestId::class,
                ],
            ],
        ],
        'loggers' => [
            FatalErrorLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            ErrorLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            ExceptionLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            Request2XXLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            Request3XXLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            Request4XXLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
            Request5XXLoggerFactory::SERVICE_NAME => [
                'enable' => true,
            ],
        ],
    ],
];

```

## Custom Configuration
You can enable or disable each logger and override the logger settings.
