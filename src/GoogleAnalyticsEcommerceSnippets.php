<?php

namespace DFT\SilverCommerce\GoogleAnalyticsEcommerce;

use InvalidArgumentException;
use LeKoala\Uuid\UuidExtension;
use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;
use SilverCommerce\CatalogueFrontend\Control\CatalogueController;
use SilverCommerce\Checkout\Control\Checkout;
use SilverCommerce\OrdersAdmin\Model\Estimate;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\ShoppingCart\Model\ShoppingCart as ShoppingCartModel;
use SilverCommerce\ShoppingCart\Control\ShoppingCart;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\TagManager\SnippetProvider;
use SilverStripe\View\HTML;

class GoogleAnalyticsEcommerceSnippet implements SnippetProvider
{
    const SESSION_NAME = "GAEcommerce.OrderID";

    public function getTitle()
    {
        return "Google Analytics Ecommerce Measurement";
    }

    public function getParamFields()
    {
        $spacer = HTML::createTag(
          'div',
          ['class' => 'form__field-label']
        );
        $info = HTML::createTag(
          'div',
          ['class' => 'form__field-holder alert alert-info'],
          'NOTE: You must also add a "Google Analytics" tag and position it before this tag'
        );
        $container = HTML::createTag(
          'div',
          ['class' => 'form-group field'],
          $spacer . $info
        );
        return FieldList::create(
            LiteralField::create('GAAlertInfo', $container)
        );
    }

    public function getSummary(array $params)
    {
      return $this->getTitle();
    }

    protected function getItemDetailsCode(
        CatalogueProduct $product
    ): string {
        $data = json_encode($product->getGADataArray());
        $content = <<<HTML
<script>gtag("event","view_item",{$data});</script>
HTML;
        return $content;
    }

    protected function getViewCartCode(
        ShoppingCartModel $cart
    ): string {
        $data = json_encode($cart->getGADataArray());
        $content = <<<HTML
<script>gtag("event","view_cart",{$data});</script>
HTML;
        return $content;
    }

    protected function getInitiateCheckoutCode(
        Estimate $estimate
    ): string {
        $data = json_encode($estimate->getGADataArray());
        $content = <<<HTML
<script>gtag("event","begin_checkout",{$data});</script>
HTML;
        return $content;
    }

    protected function getMakePurchaseCode(
        Invoice $invoice
    ): string {
        $data = json_encode($invoice->getGADataArray());
        $content = <<<HTML
<script>gtag("event","purchase",{$data});</script>
HTML;
        return $content;
    }

    public function getSnippets(array $params)
    {
        /** @var HTTPRequest */
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $action = $request->param('Action');
        $controller = Controller::curr();
        $content = "";

        if (is_a($controller, CatalogueController::class)
          && is_a($controller->data(), CatalogueProduct::class)
        ) {
          $content = $this->getItemDetailsCode(
              $controller->data()
          );
        } elseif (is_a($controller, ShoppingCart::class)) {
          $content = $this->getViewCartCode(
              $controller->data()
          );
        } elseif (is_a($controller, Checkout::class)
          && (empty($action) || $action === 'index')
        ) {
          $estimate = $controller->getEstimate();

          if (!$estimate->isInDB()) {
            return [];
          }

          $session->set(
              self::SESSION_NAME,
              $estimate
                ->dbObject(UuidExtension::UUID_FIELD)
                ->Base62()
          );
          $content = $this->getInitiateCheckoutCode(
              $estimate
          );
        } elseif (is_a($controller, Checkout::class)
          && $action === 'complete'
        ) {
            $order_uuid = $session->get(self::SESSION_NAME);
            $invoice = UuidExtension::getByUuid(
                Invoice::class,
                $order_uuid
            );

            if (empty($invoice)) {
              return [];
            }

            // Have to rely on ID from session
            $content = $this->getMakePurchaseCode(
                $invoice
            );
        }

        if (empty($content)) {
          return [];
        }

        return [
            self::ZONE_HEAD_START => $content
        ];
    }
}
