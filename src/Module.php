<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZF3Belcebur\MongoDBLogger;

use Exception;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Log\Logger;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Mvc\MvcEvent;
use ZF3Belcebur\MongoDBLogger\Factory\ErrorLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\ExceptionLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\FatalErrorLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request2XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request3XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request4XXLoggerFactory;
use ZF3Belcebur\MongoDBLogger\Factory\Request5XXLoggerFactory;

class Module implements DependencyIndicatorInterface
{
    public const CONFIG_KEY = __NAMESPACE__;

    public function getConfig(): array
    {
        return include __DIR__ . '/../config/zf3belcebur-mongodb-logger.global.php.dist';
    }

    public function onBootstrap(MvcEvent $mvcEvent): void
    {
        $app = $mvcEvent->getApplication();
        $serviceManager = $app->getServiceManager();
        $config = $serviceManager->get('Config')[__NAMESPACE__] ?? [];

        if ($config['loggers'][ErrorLoggerFactory::SERVICE_NAME]['enable']) {
            $errorLogger = $serviceManager->get(ErrorLoggerFactory::SERVICE_NAME);
        }
        if ($config['loggers'][ExceptionLoggerFactory::SERVICE_NAME]['enable']) {
            $exceptionLogger = $serviceManager->get(ExceptionLoggerFactory::SERVICE_NAME);
        }
        if ($config['loggers'][FatalErrorLoggerFactory::SERVICE_NAME]['enable']) {
            $fatalErrorLogger = $serviceManager->get(FatalErrorLoggerFactory::SERVICE_NAME);
        }

        if (PHP_SAPI !== 'cli') {
            $app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handleDispatchError']);
            $app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, [$this, 'handleDispatch']);
        }

    }

    public function handleDispatch(MvcEvent $mvcEvent): void
    {

        /**
         * @var Logger $logger
         * @var Request $request
         * @var Response $response
         */
        $app = $mvcEvent->getApplication();
        $serviceManager = $app->getServiceManager();


        $config = $serviceManager->get('Config')[__NAMESPACE__] ?? [];
        $response = $mvcEvent->getResponse();
        $firstCode = (int)substr($response->getStatusCode(), 0, 1);

        $params = array_merge($this->getResponseAndRequestParams($mvcEvent), $this->getExceptionParams($mvcEvent), $this->getSessionParams($mvcEvent));

        if ($firstCode === 2 && $config['loggers'][Request2XXLoggerFactory::SERVICE_NAME]['enable']) {
            $logger = $serviceManager->get(Request2XXLoggerFactory::SERVICE_NAME);
            $logger->info($response->getReasonPhrase(), $params);
        }

        if ($firstCode === 3 && $config['loggers'][Request3XXLoggerFactory::SERVICE_NAME]['enable']) {
            $logger = $serviceManager->get(Request3XXLoggerFactory::SERVICE_NAME);
            $logger->debug($response->getReasonPhrase(), $params);
        }
    }

    private function getResponseAndRequestParams(MvcEvent $mvcEvent): array
    {
        /**
         * @var Request $request
         * @var Response $response
         */
        $request = $mvcEvent->getRequest();
        $response = $mvcEvent->getResponse();
        $routeMatchParam = null;

        $routeMatch = $mvcEvent->getRouter()->match($request);
        if ($routeMatch) {
            $routeMatchParam['params'] = $routeMatch->getParams();
            $routeMatchParam['name'] = $routeMatch->getMatchedRouteName();
        }
        return array_filter([
            'routeMatch' => $routeMatchParam,
            'statusCode' => $response->getStatusCode(),
            'error' => $mvcEvent->getError(),
            'request' => array_filter([
                'referer' => $_SERVER['HTTP_REFERER'] ?? null,
                'uri' => $request->getUriString(),
                'post' => $request->getPost()->toArray(),
                'get' => $request->getQuery()->toArray(),
                'files' => $request->getFiles()->toArray(),
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders()->toArray(),
            ]),
        ]);
    }

    private function getExceptionParams(MvcEvent $mvcEvent): array
    {
        $exception = $mvcEvent->getParam('exception');
        if ($exception instanceof Exception) {
            $previous = $exception->getPrevious();
            $exceptionParams = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'previous-trace' => $previous ? [
                    'code' => $previous->getCode(),
                    'line' => $previous->getLine(),
                    'file' => $previous->getFile(),
                ] : null,
            ];
        } else {
            $exceptionParams = (array)$exception;
        }
        return array_filter([
            'exception' => $exceptionParams,
        ]);
    }

    private function getSessionParams(MvcEvent $mvcEvent): array
    {
        $session = [];
        return array_filter(['session' => $session]);
    }

    public function handleDispatchError(MvcEvent $mvcEvent): void
    {
        /**
         * @var Logger $logger
         * @var Request $request
         * @var Response $response
         */
        $app = $mvcEvent->getApplication();
        $serviceManager = $app->getServiceManager();


        $config = $this->getModuleConfig($mvcEvent);
        $response = $mvcEvent->getResponse();
        $firstCode = (int)substr($response->getStatusCode(), 0, 1);

        $params = array_merge($this->getResponseAndRequestParams($mvcEvent), $this->getExceptionParams($mvcEvent), $this->getSessionParams($mvcEvent));

        if ($firstCode === 4 && $config['loggers'][Request4XXLoggerFactory::SERVICE_NAME]['enable']) {
            $logger = $serviceManager->get(Request4XXLoggerFactory::SERVICE_NAME);
            $logger->warn($mvcEvent->getError(), $params);
        }

        if ($firstCode === 5 && $config['loggers'][Request5XXLoggerFactory::SERVICE_NAME]['enable']) {
            $logger = $serviceManager->get(Request5XXLoggerFactory::SERVICE_NAME);
            $logger->crit($mvcEvent->getError(), $params);
        }
    }

    private function getModuleConfig(MvcEvent $mvcEvent): array
    {
        $app = $mvcEvent->getApplication();
        $serviceManager = $app->getServiceManager();
        return $serviceManager->get('Config')[__NAMESPACE__] ?? [];

    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies(): array
    {
        return [
            'Zend\Log'
        ];
    }
}
