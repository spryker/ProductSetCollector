<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductSetCollector\Plugin\Elasticsearch\ResultFormatter;

use Elastica\ResultSet;
use Generated\Shared\Search\PageIndexMap;
use Generated\Shared\Transfer\ProductSetStorageTransfer;
use Spryker\Client\Search\Plugin\Elasticsearch\ResultFormatter\AbstractElasticsearchResultFormatterPlugin;

class ProductSetListResultFormatterPlugin extends AbstractElasticsearchResultFormatterPlugin
{

    const NAME = 'productSets';

    /**
     * @api
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \Elastica\ResultSet $searchResult
     * @param array $requestParameters
     *
     * @return mixed
     */
    protected function formatSearchResult(ResultSet $searchResult, array $requestParameters)
    {
        $productSets = [];
        foreach ($searchResult->getResults() as $document) {
            $productSetStorageData = $document->getSource()[PageIndexMap::SEARCH_RESULT_DATA];
            $productSetStorageTransfer = $this->mapToTransfer($productSetStorageData);

            $productSets[] = $productSetStorageTransfer;
        }

        return $productSets;
    }

    /**
     * @param array $productSetStorageData
     *
     * @return \Generated\Shared\Transfer\ProductSetStorageTransfer
     */
    protected function mapToTransfer(array $productSetStorageData)
    {
        $productSetStorageTransfer = new ProductSetStorageTransfer();
        $productSetStorageTransfer->fromArray($productSetStorageData, true);

        return $productSetStorageTransfer;
    }

}