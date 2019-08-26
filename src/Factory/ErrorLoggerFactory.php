<?php


namespace ZF3Belcebur\MongoDBLogger\Factory;


use Interop\Container\ContainerInterface;
use Zend\Log\Logger;

class ErrorLoggerFactory extends MongoDBLoggerFactory
{
    public const SERVICE_NAME = 'ErrorLogger';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Logger
    {
        $logger = parent::__invoke($container, $requestedName, ['name' => self::SERVICE_NAME]);
        Logger::registerErrorHandler($logger);
        return $logger;
    }
}

