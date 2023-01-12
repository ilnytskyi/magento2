<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

use GraphQL\Type\Definition\NonNull;

/**
 * Common cases for types that need extra formatting like wrapping or additional properties added to their definition
 */
class TypeMetaWrapperReader
{
    public const ARGUMENT_PARAMETER = 'Argument';

    public const OUTPUT_FIELD_PARAMETER = 'OutputField';

    public const INPUT_FIELD_PARAMETER = 'InputField';

    /**
     * Read from type meta data and determine wrapping types that are needed and extra properties that need to be added
     *
     * @param \GraphQL\Type\Definition\Type $meta
     * @param string $parameterType Argument|OutputField|InputField
     * @return array
     */
    public function read(\GraphQL\Type\Definition\Type $meta, string $parameterType) : array
    {
        $result = [];
        if ($meta instanceof NonNull) {
            $result['required'] = true;
            $meta = $meta->getWrappedType();
        } else {
            $result['required'] = false;
        }
        if ($meta instanceof \GraphQL\Type\Definition\ListOfType) {
            $itemTypeMeta = $meta->getInnermostType();
            if ($itemTypeMeta instanceof NonNull) {
                $result['itemsRequired'] = true;
                $itemTypeMeta = $itemTypeMeta->getWrappedType();
            } else {
                $result['itemsRequired'] = false;
            }
            $itemTypeName = $itemTypeMeta->name;
            $result['itemType'] = $itemTypeName;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
                $result['type'] = 'ScalarArray' . $parameterType;
            } else {
                $result['type'] = 'ObjectArray' . $parameterType;
            }
        } else {
            $result['type'] = $meta->name;
        }

        return $result;
    }
}
