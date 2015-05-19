# WC Autoship Cart Coupon
Apply coupons to a shopping cart containing autoship items.

**Note:** This plugin will apply coupons to the WooCommerce shopping cart and checkout only. 
Recurring autoship orders will not be affected.

## Features
* Enable cart coupons conditionally based on autoship items in the cart.
* Set the minimum number of autoship items required to enable the coupon.
* Optionally, apply coupons automatically to the cart.

## Installing
1. Download the plugin zip archive.
2. Navigate to the Wordpress Admin Dashboard > Plugins > Add New.
3. Upload the plugin zip archive to the site and activate the plugin.

## Setup
1. Navigate to WooCommerce > Coupons > Add Coupon.
2. Create a coupon with your desired behavior. 
   See the WooCommerce Coupon Management documentation for more information: http://docs.woothemes.com/document/coupon-management/
3. In the Coupon Data menu, select the Auto-Ship tab.
4. Check the box to Enable for Autoship.
5. Enter 1 or greater into the Minimum Quantity field.
6. Optionally, check the box to Apply Automatically.

## Examples

### Add free shipping for autoship

#### Enable free shipping
1. Navigate to WooCommerce > Settings > Shipping > Free Shipping.
2. Check the box to Enable Free Shipping.
3. Select Free Shipping Requires a valid free shipping coupon.

#### Create a free shipping coupon
1. Navigate to WooCommerce > Coupons > Add Coupon.
2. Enter a coupon code.
3. Check the box to Allow free shipping.
4. Select the Auto-Ship tab.
6. Check the box to Enable for Autoship.
7. Enter 1 into the Minimum Quantity field.
8. Check the box to Apply Automatically.
