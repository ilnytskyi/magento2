<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\AppActionListGenerator;
use Magento\Setup\Module\Di\App\Task\Operation\PluginListGenerator;

/**
 * Factory that creates list of OperationInterface classes
 */
class OperationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Area config generator operation definition
     */
    const AREA_CONFIG_GENERATOR = 'area';

    /**
     * Interception operation definition
     */
    const INTERCEPTION = 'interception';

    /**
     * Interception cache operation definition
     */
    const INTERCEPTION_CACHE = 'interception_cache';

    /**
     * Repository generator operation definition
     */
    const REPOSITORY_GENERATOR = 'repository_generator';

    /**
     * Proxy generator operation definition
     */
    const PROXY_GENERATOR = 'proxy_generator';

    /**
     * Service data attributes generator operation definition
     */
    const DATA_ATTRIBUTES_GENERATOR = 'extension_attributes_generator';

    /**
     * Application code generator operation definition
     */
    const APPLICATION_CODE_GENERATOR = 'application_code_generator';

    /**
     * Application action list generator operation definition
     */
    const APPLICATION_ACTION_LIST_GENERATOR = 'application_action_list_generator';

    /**
     * Plugin list generator operation definition
     */
    const PLUGIN_LIST_GENERATOR = 'plugin_list_generator';

    /**
     * Operations definitions
     *
     * @var array
     */
    private $operationsDefinitions = [
        self::DATA_ATTRIBUTES_GENERATOR =>
            \Magento\Setup\Module\Di\App\Task\Operation\ServiceDataAttributesGenerator::class,
        self::AREA_CONFIG_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\Area::class,
        self::APPLICATION_CODE_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator::class,
        self::INTERCEPTION => \Magento\Setup\Module\Di\App\Task\Operation\Interception::class,
        self::INTERCEPTION_CACHE => \Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache::class,
        self::REPOSITORY_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator::class,
        self::PROXY_GENERATOR => \Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator::class,
        self::APPLICATION_ACTION_LIST_GENERATOR => AppActionListGenerator::class,
        self::PLUGIN_LIST_GENERATOR => PluginListGenerator::class,
    ];

    /**
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(\Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * Creates operation
     *
     * @param string $operationAlias
     * @param mixed $arguments
     * @return OperationInterface
     * @throws OperationException
     */
    public function create($operationAlias, $arguments = null)
    {
        if (!\array_key_exists($operationAlias, $this->operationsDefinitions)) {
            throw new OperationException(
                sprintf('Unrecognized operation "%s"', $operationAlias),
                OperationException::UNAVAILABLE_OPERATION
            );
        }

        return $this->objectManager->create($this->operationsDefinitions[$operationAlias], ['data' => $arguments]);
    }
}
