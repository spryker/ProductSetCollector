<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductSetCollector\Business\Collector\Storage;

use Generated\Shared\Transfer\ProductSetStorageTransfer;
use Generated\Shared\Transfer\StorageProductImageTransfer;
use Spryker\Shared\ProductSet\ProductSetConfig;
use Spryker\Zed\Collector\Business\Collector\Storage\AbstractStoragePropelCollector;
use Spryker\Zed\ProductSet\Persistence\ProductSetQueryContainer;
use Spryker\Zed\ProductSet\Persistence\ProductSetQueryContainerInterface;
use Spryker\Zed\ProductSetCollector\Persistence\Storage\Propel\ProductSetCollectorQuery;

class ProductSetCollector extends AbstractStoragePropelCollector
{

    /**
     * @var ProductSetQueryContainerInterface
     */
    protected $productSetQueryContainer;

    /**
     * @return string
     */
    protected function collectResourceType()
    {
        return ProductSetConfig::RESOURCE_TYPE_PRODUCT_SET;
    }

    /**
     * @param string $touchKey
     * @param array $collectItemData
     *
     * @return array
     */
    protected function collectItem($touchKey, array $collectItemData)
    {
        $productSetStorageTransfer = new ProductSetStorageTransfer();
        $productSetStorageTransfer = $this->setIdProductAbstract($collectItemData, $productSetStorageTransfer);

        unset($collectItemData[ProductSetCollectorQuery::FIELD_ID_PRODUCT_ABSTRACTS]);

        $productSetStorageTransfer->fromArray($collectItemData, true);
        $productSetStorageTransfer = $this->setProductSetImageSets($productSetStorageTransfer);

        return $productSetStorageTransfer->modifiedToArray();
    }

    /**
     * @return bool
     */
    protected function isStorageTableJoinWithLocaleEnabled()
    {
        return true;
    }

    /**
     * @param array $collectItemData
     * @param \Generated\Shared\Transfer\ProductSetStorageTransfer $productSetStorageTransfer
     *
     * @return \Generated\Shared\Transfer\ProductSetStorageTransfer
     */
    protected function setIdProductAbstract(array $collectItemData, ProductSetStorageTransfer $productSetStorageTransfer)
    {
        $idProductAbstracts = explode(',', $collectItemData[ProductSetCollectorQuery::FIELD_ID_PRODUCT_ABSTRACTS]);
        $idProductAbstracts = array_map('intval', $idProductAbstracts);

        $productSetStorageTransfer->setIdProductAbstracts($idProductAbstracts);

        return $productSetStorageTransfer;
    }

    /**
     * @param ProductSetStorageTransfer $productSetStorageTransfer
     *
     * @return ProductSetStorageTransfer
     */
    protected function setProductSetImageSets(ProductSetStorageTransfer $productSetStorageTransfer)
    {
        $this->productSetQueryContainer = new ProductSetQueryContainer(); // FIXME: probably should use ProductSetCollectorQueryContainer

        $imageSetEntities = $this->productSetQueryContainer
            ->queryProductImageSet($productSetStorageTransfer->getIdProductSet())
            ->find();

        // TODO: use new ProductImageFacade methods to get only relevant image sets

        $imageSets = [];
        foreach ($imageSetEntities as $imageSetEntity) {
            $result[$imageSetEntity->getName()] = [];
            foreach ($imageSetEntity->getSpyProductImageSetToProductImages() as $productsToImageEntity) {
                $imageEntity = $productsToImageEntity->getSpyProductImage();
                $storageProductImageTransfer = new StorageProductImageTransfer();
                $storageProductImageTransfer->fromArray($imageEntity->toArray(), true);

                $imageSets[$imageSetEntity->getName()][] = $storageProductImageTransfer->modifiedToArray();
            }
        }

        $productSetStorageTransfer->setImageSets($imageSets);

        return $productSetStorageTransfer;
    }

}
