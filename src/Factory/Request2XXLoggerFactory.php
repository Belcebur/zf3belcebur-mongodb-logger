<?php


namespace ZF3Belcebur\MongoDBLogger\Factory;


use Interop\Container\ContainerInterface;
use Zend\Log\Logger;

class Request2XXLoggerFactory extends MongoDBLoggerFactory
{
    public const SERVICE_NAME = 'Request2XXLogger';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Logger
    {
        return parent::__invoke($container, $requestedName, ['name' => self::SERVICE_NAME]);
    }
}

