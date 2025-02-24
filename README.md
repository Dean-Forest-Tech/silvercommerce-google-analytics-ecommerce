# SilverCommerce Google Analytics  Ecommerce Tracking

Adds GA4 ecommerce tracking scripts to a SilverCommerce site via Sam Minnee's TagManager module that allow more granular shop tracking of customer journeys via Google Analytics.

Currently this module adds `gtag` events when viewing:

1. `ProductController` (load `view_item`).
2. `ShoppingCart` (load `view_cart`).
3. `Checkout` (on index, load `begin_checkout`).
4. `Checkout` (on complete, load `purchase`).

## Install

Install this module via composer:

    composer require dft/silvercommerce-google-analytics-ecommerce

## Setup

Setup is pretty simple, once installed (and `dev/build` run):

1. Naviage to the Silverstripe admin.
2. Naviage to "Tag Manager".
3. If no "Google Analytics" tag, add that and add your GA ID.
4. Now add a "Google Analytics ecommerce tracking" tag.