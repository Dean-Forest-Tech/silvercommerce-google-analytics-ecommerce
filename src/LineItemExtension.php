<?php

namespace DFT\SilverCommerce\GoogleAnalyticsEcommerce;

use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\OrdersAdmin\Model\LineItem;

class LineItemExtension extends DataExtension
{
    public function getGADataArray(): array
    {
        /** @var LineItem */
        $owner = $this->getOwner();
        $config = SiteConfig::current_site_config();
        $product = $owner->findStockItem();

        if (empty($product)) {
            $data = [
                'item_id' => $owner->StockID,
                'item_name' => $owner->Title,
                'affiliation' => $config->Title
            ];
        } else {
            $data = $product->getGADataArray();
        }

        $data['price'] = round($owner->getPriceAndTax(), 2);
        $data['quantity'] = $owner->Quantity;

        return $data;
    }
}
