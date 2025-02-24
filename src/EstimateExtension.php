<?php

namespace DFT\SilverCommerce\GoogleAnalyticsEcommerce;

use NumberFormatter;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataExtension;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\OrdersAdmin\Model\Estimate;

class EstimateExtension extends DataExtension
{
    public function getGADataArray(): array
    {
        /** @var Estimate */
        $owner = $this->getOwner();
        $currency = new NumberFormatter(
            i18n::get_locale(),
            NumberFormatter::CURRENCY
        );

        $codes = $owner
            ->Discounts()
            ->column('Code');

        $data = [
            "value" => round($owner->Total, 2),
            "tax" => round($owner->TaxTotal, 2),
            "shipping" => $owner->PostageTotal,
            "currency" => $currency
                ->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL)
        ];
        $items = [];

        if (is_a($owner, Invoice::class)) {
            $data['transaction_id'] = $owner->FullRef;
        }

        if (count($codes)) {
            $codes = implode(',', $codes);
            $data['coupon'] = $codes;
        }

        foreach ($owner->Items() as $item) {
            $items[] = $item->getGADataArray();
        }

        if (count($items) > 0) {
            $data['items'] = $items;
        }

        return $data;
    }
}