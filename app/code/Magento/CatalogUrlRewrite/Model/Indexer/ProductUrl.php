<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class ProductUrl implements ActionInterface, MviewActionInterface
{
    /**
     * @var UrlRewriteResource
     */
    protected $urlRewriteResource;
    /**
     * @var ProductResource
     */
    protected $productResource;
    /**
     * @var int
     */
    protected $fullRemoveBatch;

    /**
     * @param UrlRewriteResource $urlRewriteResource
     * @param ProductResource $productResource
     * @param int $fullRemoveBatch
     */
    public function __construct(
        UrlRewriteResource $urlRewriteResource,
        ProductResource $productResource,
        int $fullRemoveBatch = 10000
    ) {
        $this->urlRewriteResource = $urlRewriteResource;
        $this->productResource = $productResource;
        $this->fullRemoveBatch = $fullRemoveBatch;
    }

    public function executeFull()
    {
        $this->execute([]);
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    public function execute($ids)
    {
        $ids = array_map('intval', $ids);

        $connection = $this->urlRewriteResource->getConnection();

        $selectCategoriesIds = $connection->select()->from(
            ['pr' => $this->productResource->getEntityTable()],
            ['product_id' => $this->productResource->getLinkField()]
        );

        if (!empty($ids)) {
            $selectCategoriesIds->where('product_id IN (?)', $ids);
        }

        $selectRemovedCategoriesIds = $connection->select()->from(
            ['url_rewrite' => $this->urlRewriteResource->getMainTable()],
            ['url_rewrite_id'],
        )->where('url_rewrite.entity_type = \'product\' AND entity_id NOT IN (?)', $selectCategoriesIds);

        $idsToRemove = array_map('intval', $connection->fetchCol($selectRemovedCategoriesIds));

        if (empty($idsToRemove)) {
            return;
        }

        foreach (array_chunk($idsToRemove, $this->fullRemoveBatch) as $batchIds) {
            $connection->delete(
                $this->urlRewriteResource->getMainTable(),
                $connection->quoteInto('url_rewrite_id' . ' IN (?)', $batchIds)
            );
        }
    }
}
