<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class CategoryUrl implements ActionInterface, MviewActionInterface
{
    /**
     * @var UrlRewriteResource
     */
    protected $urlRewriteResource;
    /**
     * @var CategoryResource
     */
    protected $categoryResource;
    /**
     * @var int
     */
    protected $fullRemoveBatch;

    /**
     * @param UrlRewriteResource $urlRewriteResource
     * @param CategoryResource $categoryResource
     * @param int $fullRemoveBatch
     */
    public function __construct(
        UrlRewriteResource $urlRewriteResource,
        CategoryResource $categoryResource,
        int $fullRemoveBatch = 10000
    ) {
        $this->urlRewriteResource = $urlRewriteResource;
        $this->categoryResource = $categoryResource;
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
            ['cat' => $this->categoryResource->getEntityTable()],
            ['category_id' => $this->categoryResource->getLinkField()]
        );

        if (!empty($ids)) {
            $selectCategoriesIds->where('category_id IN (?)', $ids);
        }

        $selectRemovedCategoriesIds = $connection->select()->from(
            ['url_rewrite' => $this->urlRewriteResource->getMainTable()],
            ['url_rewrite_id'],
        )->where('url_rewrite.entity_type = \'category\' AND entity_id NOT IN (?)', $selectCategoriesIds);

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
