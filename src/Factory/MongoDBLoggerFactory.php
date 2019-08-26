<?php


namespace ZF3Belcebur\MongoDBLogger\Factory;


use Interop\Container\ContainerInterface;
use MongoDB\Driver\Manager as MongoDriverManager;
use ZF3Belcebur\MongoDBLogger\Module;
use Zend\Log\Logger;
use Zend\Log\Writer\MongoDB as MongoDBWriter;
use Zend\ServiceManager\Factory\FactoryInterface;

class MongoDBLoggerFactory implements FactoryInterface
{
    public const SERVICE_NAME = 'BaseLogger';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Logger
    {

        $config = $container->get('Config');
        $baseOptions = $config[Module::CONFIG_KEY];

        if (array_key_exists('name', $options) && array_key_exists($options['name'], $baseOptions['writers'])) {
            $writerOptions = array_merge($baseOptions['writers']['defaultOptions'], $baseOptions['writers'][$options['name']]);
        } else {
            $writerOptions = $baseOptions['writers']['defaultOptions'];
        }
        $manager = new MongoDriverManager($writerOptions['manager']['uri'], $writerOptions['manager']['uriOptions'], $writerOptions['manager']['driverOptions']);
        $writer = new MongoDBWriter($manager, $writerOptions['database'], $writerOptions['collection'], $writerOptions['writeConcern']);

        foreach ($writerOptions['filters'] as $filter) {
            $writer->addFilter($filter);
        }

        if (array_key_exists('formatter', $writerOptions)) {
            $writer->setFormatter($writerOptions['formatter']);
        }

        if (array_key_exists('name', $options) && array_key_exists($options['name'], $baseOptions['processors'])) {
            $processors = array_merge($baseOptions['processors']['default'], $baseOptions['processors'][$options['name']]);
        } else {
            $processors = $baseOptions['processors']['default'];
        }

        $logger = new Logger(['processors' => $processors]);
        $logger->addWriter($writer);
        return $logger;
    }
}

