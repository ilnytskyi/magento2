<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Converter Mapper Interface
 */
namespace Magento\Config\Model\Config\Structure;

use function array_key_exists;
use function explode;
use function is_array;

/**
 * @api
 * @since 100.0.2
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * Check value existence
     *
     * @param string $key
     * @param array $target
     * @return bool
     */
    protected function _hasValue($key, $target)
    {
        if (!is_array($target)) {
            return false;
        }

        $paths = explode('/', $key);
        foreach ($paths as $path) {
            if (array_key_exists($path, $target)) {
                $target = $target[$path];
            } else {
                return false;
            }
        }
        return true;
    }
}
