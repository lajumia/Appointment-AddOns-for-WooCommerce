<?php
/*
 * Plugin Name:       Appointment Add-Ons for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/search/appointment-addons-for-woocommerce/
 * Description:       Enhance WooCommerce appointment bookings with customizable add-on time options for flexible pricing and scheduling.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md Laju Miah
 * Author URI:        https://www.upwork.com/freelancers/~0149190c8d83bae2e2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/lajumia/appointment-addons-for-woocommerce
 * Text Domain:       aafw
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Enqueue scripts and styles for the frontend on the cart and checkout pages.
 *
 * This function hooks into `wp_enqueue_scripts` to conditionally load custom styles and scripts.
 * The styles and scripts are only loaded on the WooCommerce cart and checkout pages.
 */
add_action( 'wp_enqueue_scripts', 'aafw_enqueue_scripts' );
function aafw_enqueue_scripts() {
    if ( is_cart() || is_checkout() ) {
        wp_enqueue_style( 'aafw-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.0.0' );
        wp_enqueue_script( 'aafw-script', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ), '1.0', true );
    }
}



/**
 * Enqueue scripts on add new or edit product page in the WordPress admin.
 *
 * This function hooks into `admin_enqueue_scripts` to load custom admin scripts
 * only on the product edit or add new product pages.
 */
add_action( 'admin_enqueue_scripts', 'aafw_admin_enqueue_scripts' );
function aafw_admin_enqueue_scripts( $hook ) {
    $current_screen = get_current_screen();
    if ( $current_screen && 'product' === $current_screen->post_type && ( 'post-new.php' === $current_screen->base || 'post.php' === $current_screen->base ) ) {
        wp_enqueue_script( 'aafw-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );
    }
}



/**
 * Load custom cart template.
 *
 * @param string $template The path to the template file.
 * @param string $template_name The name of the template.
 * @param string $template_path The path of the template.
 * @return string Custom template path if found, otherwise the default template.
 */
add_filter( 'woocommerce_locate_template', 'aafw_cart_template', 10, 3 );
function aafw_cart_template( $template, $template_name, $template_path ) {
    if ( 'cart/cart.php' === $template_name ) {
        $custom_template = plugin_dir_path( __FILE__ ) . 'templates/cart/cart.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    
    return $template;
}



/**
 * Registers a custom product type called 'Appointment' in WooCommerce.
 * 
 * This function defines a new product type 'appointment' by extending the 
 * WC_Product class. It provides additional properties and methods to manage 
 * appointment products, such as marking them as virtual and enabling AJAX 
 * add-to-cart functionality. The class is only defined if it doesn't already 
 * exist to avoid redeclaring it.
 */
add_action( 'init', 'aafw_register_product_type' );
function aafw_register_product_type() {
    if ( ! class_exists( 'WC_Product_Appointment' ) ) {

        class WC_Product_Appointment extends WC_Product {

            protected $product_type = 'appointment';
            protected $virtual = 'yes';

            public function __construct( $product ) {
                $this->supports[] = 'ajax_add_to_cart';
                parent::__construct( $product );
            }

            public function get_product_type() {
                return $this->product_type;
            }

            public function is_virtual() {
                return $this->virtual === 'yes';
            }
        }
    }
}



/**
 * Filter the WooCommerce product class to use a custom class for the 'appointment' product type.
 *
 * This function checks if the product type is 'appointment' and changes the 
 * product class to use the custom WC_Product_Appointment class, allowing 
 * for specialized behavior for appointment products.
 *
 * @param string $classname The current product class name.
 * @param string $product_type The type of the product being processed.
 *
 * @return string The class name to be used for the product, either the custom
 *                'WC_Product_Appointment' class or the original class.
 */
add_filter( 'woocommerce_product_class', 'aafw_set_appointment_product_class', 10, 2 );
function aafw_set_appointment_product_class( $classname, $product_type ) {
    if ( $product_type === 'appointment' ) {
        $classname = 'WC_Product_Appointment';
    }
    return $classname;
}



/**
 * Allow Add to Cart Functionality for Custom Product Type (Appointment)
 *
 * This function ensures that products of type 'appointment' are purchasable
 * and can be added to the cart. It is applied to the 'woocommerce_is_purchasable' filter.
 *
 * @param bool $purchasable Whether the product can be purchased.
 * @param WC_Product $product The product object.
 *
 * @return bool Whether the product can be added to the cart.
 */
add_filter( 'woocommerce_is_purchasable', 'aafw_enable_appointment_purchasable', 10, 2 );
function aafw_enable_appointment_purchasable( $purchasable, $product ) {
    if ( $product->get_type() === 'appointment' ) {
        $purchasable = true;
    }
    return $purchasable;
}




/**
 * Modify Add to Cart Button Text for Custom Product Type (Appointment)
 *
 * This function changes the 'Add to Cart' button text specifically for products
 * of type 'appointment'. It is applied to the 'woocommerce_product_add_to_cart_text' filter.
 *
 * @param string $text The original text of the 'Add to Cart' button.
 * @param WC_Product $product The product object.
 *
 * @return string The modified button text.
 */
add_filter( 'woocommerce_product_add_to_cart_text', 'aafw_appointment_add_to_cart_text', 10, 2 );
function aafw_appointment_add_to_cart_text( $text, $product ) {
    if ( $product->get_type() === 'appointment' ) {
        $text = __( 'Add to Cart', 'woocommerce' );
    }
    return $text;
}



/**
 * Modify Sold Individually Setting for Custom Product Type (Appointment)
 *
 * This function adjusts the 'Sold Individually' setting specifically for products
 * of type 'appointment'. By default, appointment products can be sold in quantities
 * greater than one.
 *
 * @param bool $sold_individually Whether the product is sold individually or not.
 * @param WC_Product $product The product object.
 *
 * @return bool The modified 'Sold Individually' setting.
 */
add_filter( 'woocommerce_is_sold_individually', 'aafw_appointment_sold_individually', 10, 2 );
function aafw_appointment_sold_individually( $sold_individually, $product ) {
    if ( $product->get_type() === 'appointment' ) {
        $sold_individually = false;
    }
    return $sold_individually;
}




/**
 * Customize Quantity Input for Appointment Product Type
 *
 * This function customizes the quantity input for products of type 'appointment'.
 * It ensures that the quantity input allows only 1 item to be added to the cart
 * since appointments are typically single bookings.
 *
 * @param array $args The quantity input arguments.
 * @param WC_Product $product The product object.
 *
 * @return array Modified quantity input arguments.
 */
add_filter( 'woocommerce_quantity_input_args', 'aafw_customize_quantity_input_for_appointment', 10, 2 );
function aafw_customize_quantity_input_for_appointment( $args, $product ) {
    if ( $product->get_type() === 'appointment' ) {
        $args['input_value'] = 1;
        $args['min_value'] = 1;
        $args['max_value'] = 1;
        $args['type'] = 'number';
    }

    return $args;
}



/**
 * Modify Price for Appointment Product Type
 *
 * This function modifies the price of products of type 'appointment'. It checks if the product
 * is of type 'appointment' and overrides the default price with a custom price stored in
 * a custom meta field associated with the product.
 *
 * @param float $price The original price of the product.
 * @param WC_Product $product The product object.
 *
 * @return float The modified price.
 */
add_filter( 'woocommerce_product_get_price', 'aafw_get_appointment_price', 10, 2 );
function aafw_get_appointment_price( $price, $product ) {
    if ( $product->get_type() === 'appointment' ) {
        $price = get_post_meta( $product->get_id(), '_appointment_base_price', true );
    }

    return $price;
}



/**
 * Add custom product type to WooCommerce product types.
 *
 * This function adds a new product type called 'appointment' to the WooCommerce product type selector.
 * The 'appointment' type will then be available when creating or editing products in WooCommerce.
 *
 * @param array $types The list of existing product types.
 * @return array Modified list of product types, including the new 'appointment' product type.
 */
add_filter( 'product_type_selector', 'aafw_add_custom_product_type' );
function aafw_add_custom_product_type( $types ) {
    $types['appointment'] = __( 'Appointment Product', 'woocommerce' );
    
    return $types;
}



/**
 * Add a custom tab for appointment products in the product data panel.
 *
 * This function adds a new tab to the product data panel, specifically for 'appointment' products.
 * The new tab will be displayed when editing a product of the 'appointment' type.
 *
 * @param array $tabs The existing tabs in the product data panel.
 * @return array Modified tabs array, including the new 'Appointments' tab.
 */
add_filter( 'woocommerce_product_data_tabs', 'aafw_appointment_product_data_tab' );
function aafw_appointment_product_data_tab( $tabs ) {
    $tabs['appointment'] = array(
        'label'  => __( 'Appointments', 'woocommerce' ),
        'target' => 'appointment_product_data',
        'class'  => array('show_if_appointment'),
    );
    return $tabs;
}


/**
 * Add the content for the appointment tab in the product data panel.
 *
 * This function adds fields for the appointment product type in the 'Appointments' tab. 
 * The fields include the base price for a 30-minute appointment and custom pricing for different durations.
 *
 * @return void
 */
add_action( 'woocommerce_product_data_panels', 'aafw_appointment_product_data_tab_content' );
function aafw_appointment_product_data_tab_content() {
    ?>
    <div id="appointment_product_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php
                // Add the 30-minute base price input for the appointment product
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_appointment_base_price',
                        'label'       => __( 'Base Price (30 Minutes)', 'woocommerce' ),
                        'data_type'   => 'price',
                        'desc_tip'    => true,
                        'description' => __( 'Set the base price for a 30-minute appointment.', 'woocommerce' ),
                    )
                );           
                
                // Define duration options array
                $durations = [
                    '1min'  => '1 Minute',
                    '15min' => '15 Minutes',
                    '45min' => '45 Minutes',
                    '1h'    => '1 Hour',
                    '2h'    => '2 Hours',
                    '3h'    => '3 Hours',
                    '6h'    => '6 Hours',
                    '12h'   => '12 Hours',
                    '24h'   => '24 Hours'
                ];

                // Loop through each duration option and create a custom price field
                foreach ( $durations as $key => $label ) {
                    woocommerce_wp_text_input(
                        array(
                            'id'          => '_price_' . $key,
                            'label'       => __( $label . ' Price ($)', 'woocommerce' ),
                            'data_type'   => 'price',
                            'desc_tip'    => true,
                            'description' => __( 'Set the price for ' . $label, 'woocommerce' ),
                        )
                    );
                }
            ?>
        </div>
    </div>
    <?php
}



/**
 * Save custom price fields for each appointment duration when a product is saved.
 *
 * This function processes and saves the custom fields for the appointment product type.
 * It stores the base price for a 30-minute appointment and prices for various durations.
 *
 * @param int $post_id The ID of the product being saved.
 * @return void
 */
add_action( 'woocommerce_process_product_meta', 'aafw_save_appointment_duration_prices' );
function aafw_save_appointment_duration_prices( $post_id ) {
    if ( isset( $_POST['_appointment_base_price'] ) ) {
        update_post_meta( $post_id, '_appointment_base_price', sanitize_text_field( $_POST['_appointment_base_price'] ) );
    }    

    $durations = [
        '1min'  => '1 Minute',
        '15min' => '15 Minutes',
        '45min' => '45 Minutes',
        '1h'    => '1 Hour',
        '2h'    => '2 Hours',
        '3h'    => '3 Hours',
        '6h'    => '6 Hours',
        '12h'   => '12 Hours',
        '24h'   => '24 Hours'
    ];

    foreach ( $durations as $key => $label ) {
        $field_id = '_price_' . $key;
        if ( isset( $_POST[ $field_id ] ) ) {
            update_post_meta( $post_id, $field_id, wc_clean( $_POST[ $field_id ] ) );
        }
    }
}


/**
 * Add a "No Customization" checkbox after the product name in the cart for appointment products.
 *
 * This function adds a checkbox labeled "No Customization" after the product name in the cart for products of the type "appointment."
 * The checkbox allows users to opt out of customization for the appointment product.
 *
 * @param array $cart_item The current cart item.
 * @param string $cart_item_key The unique key for the cart item.
 * @return void
 */
add_action( 'woocommerce_after_cart_item_name', 'aafw_add_no_customization_checkbox', 10, 2 );
function aafw_add_no_customization_checkbox( $cart_item, $cart_item_key ) {
    $product = wc_get_product( $cart_item['product_id'] );

    if ( $product && $product->get_type() === 'appointment' ) {
        $no_customization_checked = ! empty( $cart_item['no_customization'] ) ? 'checked' : '';
        
        echo '<div class="cart-no-customization">';
        echo '<label>';
        echo '<input type="checkbox" class="no-customzation" name="cart[' . $cart_item_key . '][no_customization]" value="1" ' . $no_customization_checked . '>';
        echo ' No Customization';
        echo '</label>';
        echo '</div>';
    }
}



/**
 * Add the custom appointment duration field to the custom hook 'aafw_custom_filed'.
 *
 * This function displays a custom appointment duration field in the cart for products of type 'appointment'.
 * It includes options for additional hours and preset buttons for selecting session durations.
 *
 * @param array|null $cart_item The cart item data (optional, will iterate over cart if not provided).
 * @param string|null $cart_item_key The unique key for the cart item (optional, will iterate over cart if not provided).
 * @return void
 */
add_action( 'aafw_custom_filed', 'aafw_custom_appointment_duration_field', 10, 2 );
function aafw_custom_appointment_duration_field( $cart_item = null, $cart_item_key = null ) {
    if ( ! $cart_item && WC()->cart ) {
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            $cart_item = $item;
            $cart_item_key = $key;
            aafw_custom_appointment_duration_field( $cart_item, $cart_item_key );
        }
        return;
    }

    $product = wc_get_product( $cart_item['product_id'] );

    if ( $product && $product->get_type() === 'appointment' ) {
        $standard_price = get_post_meta( $product->get_id(), '_appointment_base_price', true );
        $durations = ['15min' => '15min', '45min' => '45min', '1h' => '1h', '2h' => '2h', '6h' => '6h', '12h' => '12h', '24h' => '24h(max)'];

        $standard_price_display = number_format( $standard_price, 2 );
        $additional_hours = esc_attr( $cart_item['additional_hours'] ?? 0 );
        $additional_cost = esc_attr( $cart_item['additional_cost'] ?? 0 );

        echo "
        <div class='custom-duration-selection'>
            <h4>Unlock a deeper sense of peace</h4>
            <div class='time-selection'>
                <span>I want additional:</span>
                <button type='button' class='decrement-time'>-</button>
                <input type='number' name='cart[$cart_item_key][additional_hours]' class='additional-hours' min='0' max='24' value='0' />
                <button type='button' class='increment-time'>+</button>
                
                <select class='time-unit'>
                    <option value='hours'>hours</option>
                    <option value='minutes'>minutes</option>
                </select>
            </div>

            <input type='hidden' name='cart[$cart_item_key][additional_time]' class='additional-time-hidden' value='0' />
            <input type='hidden' name='cart[$cart_item_key][additional_cost]' class='additional-cost-hidden' value='$additional_cost' /> 

            <div class='preset-buttons'>";

        foreach ($durations as $key => $label) {
            $price = get_post_meta($product->get_id(), '_price_' . $key, true);
            if ($price) {
                echo "<button type='button' class='preset-button' data-time='$key' data-price='$price'>$label</button>";
            }
        }

        echo "
            </div>
            <p class='display-sd'>Your session duration: <span class='session-duration'>30 minutes</span></p>
            <p>Standard package (30 minutes): <span class='standard-package'>$$standard_price_display</span></p>
            <p>Additional time: <span class='additional-time'></span> <span class='additional-time-cost'>$0</span></p>
            <p class='display-t'>Total: <span class='total-cost'>$$standard_price_display</span></p>
        </div>";
    }
}


/**
 * Enqueue custom JavaScript to handle "No Customization" checkbox functionality on the cart page.
 *
 * This script toggles the visibility of the custom duration selection based on the state of the "No Customization" checkbox.
 * It hides the custom duration selection when the checkbox is checked and shows it when unchecked.
 */
add_action( 'wp_footer', 'aafw_toggle_custom_duration_selection' );
function aafw_toggle_custom_duration_selection() {
    if ( is_cart() ) :
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.no-customzation').each(function() {
                aafw_toggleCustomDuration($(this));
            });

            $('.no-customzation').on('change', function() {
                aafw_toggleCustomDuration($(this));
            });

            function aafw_toggleCustomDuration(checkbox) {
                var cartItem = checkbox.closest('.cart-container');
                var durationSelection = cartItem.find('.custom-duration-selection');

                if (checkbox.is(':checked')) {
                    durationSelection.hide();
                } else {
                    durationSelection.show();
                }
            }
        });
    </script>
    <?php
    endif;
}



/**
 * Update custom data for cart items via AJAX.
 *
 * This function processes the AJAX request to update the additional time and cost for a cart item.
 * It recalculates the cart totals and sends the updated data back to the frontend.
 */
add_action('wp_ajax_update_cart_custom_data', 'aafw_update_cart_custom_data');
add_action('wp_ajax_nopriv_update_cart_custom_data', 'aafw_update_cart_custom_data');
function aafw_update_cart_custom_data() {
    check_ajax_referer('update_cart_nonce', 'security');

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $additional_time = sanitize_text_field($_POST['additional_time']);
    $additional_cost = sanitize_text_field($_POST['additional_cost']);

    if (isset(WC()->cart->cart_contents[$cart_item_key])) {
        WC()->cart->cart_contents[$cart_item_key]['additional_time'] = $additional_time;
        WC()->cart->cart_contents[$cart_item_key]['additional_cost'] = $additional_cost;

        WC()->cart->set_session();
        WC()->cart->calculate_totals();

        $updated_total = WC()->cart->get_total('raw');
        
        wp_send_json_success(array(
            'additional_time' => $additional_time,
            'additional_cost' => $additional_cost,
            'updated_total'    => $updated_total
        ));
    } else {
        wp_send_json_error('Cart item not found');
    }

    wp_die();
}



/**
 * Display custom data (additional time and cost) on the cart and checkout pages.
 *
 * This function adds custom data to the cart items displayed on the cart and checkout pages.
 * It checks for additional time and cost, and then formats them for display.
 *
 * @param array $item_data The existing item data for a cart item. This array is passed to the filter and contains the information already displayed for the cart item.
 * @param array $cart_item The cart item data for the individual product in the cart. This includes information such as product ID, price, quantity, and any custom meta data such as additional time and cost.
 *
 * @return array The modified $item_data array, with additional time and cost added if applicable.
 */
add_filter('woocommerce_get_item_data', 'aafw_display_custom_duration_and_cost', 10, 2);
function aafw_display_custom_duration_and_cost($item_data, $cart_item) {
    if (isset($cart_item['additional_time']) && $cart_item['additional_time'] > 0) {
        $item_data[] = array(
            'name'  => __('Additional Time', 'aafw'),
            'value' => sanitize_text_field($cart_item['additional_time'] . ' hours')
        );
    }
    if (isset($cart_item['additional_cost']) && $cart_item['additional_cost'] > 0) {
        $item_data[] = array(
            'name'  => __('Additional Cost', 'aafw'),
            'value' => wc_price($cart_item['additional_cost'])
        );
    }

    return $item_data;
}



/**
 * Adjust the calculated cart total to include additional costs for custom durations.
 *
 * This function loops through each cart item, checks for any additional costs associated with custom durations, and adds them to the total cart amount.
 *
 * @param float $total The current calculated total for the cart before adjustments.
 * @param WC_Cart $cart The WooCommerce cart object containing all cart items and their data.
 *
 * @return float The modified cart total including additional custom costs.
 */
add_filter('woocommerce_calculated_total', 'aafw_adjust_cart_total', 20, 2);
function aafw_adjust_cart_total($total, $cart) {
    $additional_total_cost = 0;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['additional_cost'])) {
            $additional_cost = floatval($cart_item['additional_cost']);
            $additional_total_cost += $additional_cost;
        }
    }

    $new_total = $total + $additional_total_cost;

    return $new_total;
}



/**
 * Display additional service costs on the cart and checkout pages.
 *
 * This function calculates and displays any additional service costs associated with custom durations in the order totals section.
 */
add_action('woocommerce_cart_totals_after_order_total', 'aafw_display_additional_cost_in_totals');
add_action('woocommerce_review_order_after_order_total', 'aafw_display_additional_cost_in_totals'); // For the checkout page
function aafw_display_additional_cost_in_totals() {
    $additional_total_cost = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (isset($cart_item['additional_cost'])) {
            $additional_total_cost += floatval($cart_item['additional_cost']);
        }
    }

    if ($additional_total_cost > 0) {
        echo "<tr class='additional-cost'>
                <th>Additional Service Cost</th>
                <td>" . wc_price($additional_total_cost) . "</td>
              </tr>";
    }
}



/**
 * Force display of quantity input fields on the cart and checkout pages.
 *
 * This function ensures that hidden quantity input fields are converted to visible numeric input fields and set to a default value of 1.
 */
add_action('wp_footer', 'aafw_force_quantity_input_display');
function aafw_force_quantity_input_display() {
    if (is_cart() || is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('input.qty').each(function() {
                    if ($(this).attr('type') === 'hidden') {
                        $(this).attr('type', 'number').val(1).show();
                    }
                });
            });
        </script>
        <?php
    }
}



/**
 * Adds custom data for AJAX operations in the cart and checkout pages.
 *
 * This function provides a JavaScript object containing AJAX URL and nonce for security, which can be accessed by scripts for handling custom cart data updates.
 */
add_action( 'wp_footer', 'aafw_add_custom_data', 20 ); 
function aafw_add_custom_data() {
    if ( is_cart() || is_checkout() ) { 
        ?>
        <script type="text/javascript">
            var customCartParams = {
                ajax_url: "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>",
                nonce: "<?php echo wp_create_nonce( 'update_cart_nonce' ); ?>"
            };
        </script>
        <?php
    }
}

/**
 * Registers the AJAX action for resetting cart items for logged-in users.
 */
add_action( 'wp_ajax_reset_cart_item', 'reset_cart_item' );

/**
 * Registers the AJAX action for resetting cart items for guest users.
 */
add_action( 'wp_ajax_nopriv_reset_cart_item', 'reset_cart_item' );

/**
 * Handles the AJAX request to reset a cart item.
 *
 * This function removes custom fields (e.g., 'additional_time' and 'additional_cost') 
 * from a specific cart item, recalculates the cart totals, and sends a JSON response 
 * with the updated total cost or an error message.
 */
function reset_cart_item() {
    check_ajax_referer( 'update_cart_nonce', 'security' );

    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );

    if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
        unset( WC()->cart->cart_contents[ $cart_item_key ]['additional_time'] );
        unset( WC()->cart->cart_contents[ $cart_item_key ]['additional_cost'] );

        WC()->cart->set_session();
        WC()->cart->calculate_totals();

        $updated_total = WC()->cart->get_total('raw');

        wp_send_json_success( [
            'message' => 'Cart item reset successfully.',
            'updated_total' => $updated_total
        ] );
    } else {
        wp_send_json_error( 'Cart item not found.' );
    }
}









