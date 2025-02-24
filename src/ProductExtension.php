<?php

namespace DFT\SilverCommerce\GoogleAnalyticsEcommerce;

use SilverStripe\ORM\DataExtension;
use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;
use SilverStripe\SiteConfig\SiteConfig;

class ProductExtension extends DataExtension
{
    public function getGADataArray(): array
    {
        /** @var CatalogueProduct */
        $owner = $this->getOwner();
        $config = SiteConfig::current_site_config();
        $categories = [];
        $i = 1;

        foreach ($owner->Categories() as $category) {
            if ($i = 1) {
                $categories['item_category'] = $category->Title;
            } else {
                $categories['item_category' . $i] = $category->Title;
            }
        }

        $data = [
            'item_id' => $owner->StockID,
            'item_name' => $owner->Title,
            'affiliation' => $config->Title,
            'price' => round($owner->getPriceAndTax(), 2),
            'quantity' => 1
        ];

        return array_merge($data, $categories);
    }
}