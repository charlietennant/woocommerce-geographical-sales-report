<?php
/**
 * Plugin Name: WooCommerce Geographical Sales Report
 * Plugin URI:  https://github.com/charlietennant/wp-woocommerce-geographical-sales-report
 * Description: This plugin adds a new report to your WooCommerce site to allow you to analyse sales by country. 
 * Author: Charlie Tennant
 * Version: 1.0
 * Author URI: https://github.com/charlietennant
 * Requires Plugins: woocommerce
 * 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * @package extension
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the geographical sales report to the WP menu
 */
function add_geographical_sales_menu_item() {
  add_menu_page(
    'Geographical Sales',
    'Geographical Sales',
    'manage_options',
    'geographical-sales',
    'display_geographical_sales_page',
    'dashicons-admin-site',
  );
}
add_action( 'admin_menu', 'add_geographical_sales_menu_item' );

/**
 * Render the geographical sales report
 */
function display_geographical_sales_page() {

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Geographical Sales Report</h1>';

    // Has the user scoped the report to a specific country?
    $country = isset($_GET['country']) ? $_GET['country'] : null;

    if(!empty($country)) {
        echo '<div><a href="' . remove_query_arg('country') . '">Back to main report</a></div>';

        // Validate the country is acceptable
        if(!in_array($country, array_keys(WC()->countries->countries))) {
            wp_admin_notice('Invalid country specified', array( 'type' => 'error' ));
            exit;
        }
    }

    echo "<br /><br />";

    $results = match(empty($country)) {
        true => get_main_geo_report(),
        false => get_scoped_geo_report($country),
    };

    // Display the results in a table
    if ( $results ) {

        echo '<table class="wp-list-table widefat fixed striped table-view-list"><thead>';

        foreach ( $results as $index => $grouped ) {
            
            // Render heading row
            if( $index === 0 ) {
                echo "<tr>";

                array_walk($grouped, function($value, $key) {
                    echo "<th>{$key}</th>";
                });

                echo "</tr></thead><tbody>";
            }

            echo "<tr>";

                foreach( $grouped as $key => $value )
                {
                    echo "<td>" . match($key) {
                        'Month' => date('F', mktime(0, 0, 0, $value)),
                        'Shipping Country' => match(empty($country)) {
                            true => '<a href="' . add_query_arg(array("country" => $value)) .'">' . WC()->countries->countries[$value] . '</a>',
                            false => WC()->countries->countries[$value],
                        },
                        'Total Revenue' => wc_price($value),
                        'Order Count' => number_format($value),
                        default => $value,
                    } . "</td>";
                }

            echo "</tr>";
        }
        echo '</tbody></table>';
    } else {
        echo 'No results found.';
    }

    echo '</div>';
}

/**
 * Get a top level report of order values and the associated shipping country
 * 
 * @return array
 */
function get_main_geo_report () {
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT
            pm_country.meta_value AS 'Shipping Country',
            COUNT(p.ID) AS 'Order Count',
            SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) AS 'Total Revenue'
        FROM
            {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta pm_country
            ON pm_country.post_id = p.ID
            AND pm_country.meta_key = '_shipping_country'
        INNER JOIN {$wpdb->prefix}postmeta pm_total
            ON pm_total.post_id = p.ID
            AND pm_total.meta_key = '_order_total'
        WHERE
            p.post_type = 'shop_order'
            AND p.post_status IN ('wc-processing', 'wc-completed')
        GROUP BY
            pm_country.meta_value
        ORDER BY
            pm_country.meta_value ASC;
        ", ARRAY_A);

    return $results;
}


/**
 * Get a scoped version of the report to a particular country code
 *
 * @param string $country
 * @return array
 */
function get_scoped_geo_report ( $country ) {
    global $wpdb;
    $results = $wpdb->get_results("
        SELECT
            YEAR(p.post_date_gmt) AS 'Year',
            MONTH(p.post_date_gmt) AS 'Month',
            pm_country.meta_value AS 'Shipping Country',
            COUNT(p.ID) AS 'Order Count',
            SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) AS 'Total Revenue'
        FROM
            {$wpdb->prefix}posts p
        INNER JOIN {$wpdb->prefix}postmeta pm_country
            ON pm_country.post_id = p.ID
            AND pm_country.meta_key = '_shipping_country'
        INNER JOIN {$wpdb->prefix}postmeta pm_total
            ON pm_total.post_id = p.ID
            AND pm_total.meta_key = '_order_total'
        WHERE
            p.post_type = 'shop_order'
            AND p.post_status IN ('wc-processing', 'wc-completed')
            AND pm_country.meta_value = '{$country}'
        GROUP BY
            YEAR(p.post_date_gmt),
            MONTH(p.post_date_gmt),
            pm_country.meta_value
        ORDER BY
            p.post_date_gmt DESC;
        ", ARRAY_A);

    return $results;
}
