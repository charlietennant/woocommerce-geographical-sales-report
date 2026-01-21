# WooCommerce Order Country Revenue Report

A very very light, barebones WooCommerce orders report grouped by **shipping country**.

This plugin is intentionally **HPOS-free** and is designed for stores that rely on the traditional WordPress post-based order storage.

Upon installing the plugin you will find `Geographical Sales` on your WP admin sidebar. Click on this to view the report. Then click on each individual country to scope the report by that country.

## HPOS (High-Performance Order Storage)

This plugin **does not currently support WooCommerce HPOS**.

It relies on the classic WordPress data model where orders are stored as posts (`shop_order`) with metadata.  
If HPOS is enabled on your store, this plugin will **not function correctly**.

To use this plugin:
- HPOS must be disabled or orders synced to `wp_posts` and `wp_postmeta` tables

---

## Requirements

- WordPress 5.8+
- WooCommerce 6.x – 7.x
- PHP 7.4+

---

## Example Output

| Shipping Country | Year | Month | Order Count | Total Revenue |
|------------------|------|-------|-------------|----------------|
| United States (US) | 2026 | January | 124 | £18,450.00 |
| United Kingdom (GB) | 2026 | January | 87 | £9,230.50 |
