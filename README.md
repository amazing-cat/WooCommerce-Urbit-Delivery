# Urb-it - WooCommerce plugin

## Table of Contents

* [Installation](#installation)
* [Basic links](#basic-links)
* [Extension settings](#extension-settings)
    * [Feed Cache](#feed-cache)
    * [Product filtration](#product-filtration)
    * [Custom product attributes](#custom-product-attributes)
* [Troubleshooting](#troubleshooting)

## Installation

To install a Urb-it extensions, place an extension files to Wordpress Plugins directory (`/wp-content/plugins/`)
    
## Basic links

There are two pages in the extension for generating feeds (on front-end side):

- Product Feed - /urbit-product-feed/ 
- Inventory Feed - /urbit-inventory-feed/

And also there are two pages of feed generation settings (in the admin panel)

- Product Feed - In the menu choose `Urbit -> Product Feed`
- Inventory Feed - In the menu, choose `Urbit -> Inventory Feed`


## Extension settings

Extension uses Wordpress setting system - it means that for each “separate” site
will be applied it own configuration if Wordpress will use multisite configuration.

### Feed Cache

The extension uses a caching system to reduce a site load and speed up the plug-in during
the generation of the feed, so feed is created and saved to file at specific time intervals.

The refresh interval is specified on the `Cache duration` field.

Time of updating Inventory Feed specified in minutes, Product Feed - in hours.


### Product filtration

On extension settings pages you can configure a filtering of products that will be present in the created feed.

Filtering of products is possible by following parameters:
- by categories
- by product tags
- by minimal stock of products (Product Feed only)

The filters by categories and tags are drop-down lists where you can select several options.
The number of products filter is a field for entering the whole stock count of products in the store.

If there is no selected filter parameter (no categories or tags are selected, or the number
of products for filtering is zero), the system skips the filtering by this parameter.

### Custom product attributes

**Product Feed extension only**

To generate a feed, you may need a number of parameters that are not standard
for WooCommerce and are not present by default.

These parameters in WooCommerce can add custom attributes with unique names
and descriptions that can be created in the admin page (`Products -> Attribute`).

In this case, the extension provides functionality for standardization and validation of product feed.
On the product feed settings page, you can configure the correspondence of a set of custom attributes
with a set of parameters required to generate the feed.

You can specify dimension parameters (height, width, length), marking and product codes (EAN/UPC and MPN),
and parameters that characterize the product (color, size, gender, material, pattern, age group,
condition, size type, brands/manufacturers).

## Troubleshooting

In some WordPress configuration feeds could be not available by links that described in “Basic Links” section -
it could mean that WordPress have some other page rewrites configuration.

You can search feed link for your site on special entry-point pages
(plugin create it automatically on installation)
which named “Urbit Product Feed” and “Urbit Inventory Feed”.
