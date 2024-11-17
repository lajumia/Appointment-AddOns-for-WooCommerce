jQuery(document).ready(function($) {
    // Function to retrieve and parse the base price from the DOM
    function getBasePrice() {
        let basePrice = $('.woocommerce-Price-amount bdi').text();
        
        // Remove any non-numeric characters (e.g., currency symbols)
        basePrice = basePrice.replace(/[^0-9.]/g, '');
        
        // Convert the result to a number and return
        return parseFloat(basePrice) || 0; // Default to 0 if parse fails
    }

    // Function to update the price display
    function updatePrice() {
        // Retrieve base price dynamically each time updatePrice is called
        let basePrice = getBasePrice();
        const additionalRatePerHour = $('.preset-button').eq(2).data('price') || 0; // Get additional rate per hour from the selected preset button (default to 0 if not found)

        let standardTime = 30; // Default standard time in minutes
        let additionalHours = parseInt($('.additional-hours').val()) || 0; // Get additional hours from the input
        let timeUnit = $('.time-unit').val(); // Get the selected time unit (hours or minutes)

        //console.log("Base Price:", basePrice);
        //console.log("Additional Rate per Hour:", additionalRatePerHour);
        //console.log("Additional Hours:", additionalHours);

        // If time unit is minutes, convert the additional time to hours
        if (timeUnit === 'minutes') {
            additionalHours = additionalHours / 60; // Convert additional minutes to hours
        }

        let additionalCost = additionalHours * additionalRatePerHour; // Calculate the additional cost
        let totalCost = basePrice + additionalCost; // Calculate the total cost

        // Update the display
        if (timeUnit === 'minutes') {
            let totalMinutes = additionalHours * 60; // Convert additional hours to minutes
            let displayHours = Math.floor(totalMinutes / 60); // Calculate hours
            let displayMinutes = totalMinutes % 60; // Calculate remaining minutes

            // Format the display based on hours and minutes
            let timeDisplay = (displayHours > 0 ? displayHours + ' hour' + (displayHours > 1 ? 's ' : ' ') : '') + 
                            (displayMinutes > 0 ? displayMinutes + ' minute' + (displayMinutes > 1 ? 's' : '') : '');
            
            $('.session-duration').text(timeDisplay); // Update session duration display
            $('.additional-time').text(' (' + timeDisplay + ')'); // Update additional time display
        } else {
            // Display in hours if time unit is 'hours'
            $('.session-duration').text(additionalHours + ' hours ' + standardTime + ' minutes'); // Show in hours
            $('.additional-time').text(' (' + additionalHours + ' hours)');
        }

        // Update the cost display
        $('.additional-time-cost').text('$' + additionalCost.toFixed(2));
        $('.total-cost').text('$' + totalCost.toFixed(2));

        // Update hidden fields with the appropriate values
        $('.additional-time-hidden').val(additionalHours);
        $('.additional-cost-hidden').val(additionalCost);

        // Enable the Update Cart button
        $('button[name="update_cart"]').prop('disabled', false);
        $('button[name="update_cart"]').on('click', function() {
            location.reload(); // Reload the page to reflect updated totals
        });
    }

    // Event listeners for incrementing and decrementing hours
$('.increment-time').on('click', function() {
    // Target the specific input field related to this button
    let input = $(this).siblings('.additional-hours');
    let currentValue = parseInt(input.val(), 10) || 0;

    // Increment the value
    input.val(currentValue + 1);

    // Call updatePrice after setting the value
    updatePrice();

    // Trigger the change event after updating the value and price
    input.trigger('change');
});


    $('.decrement-time').on('click', function () {
        let input = $(this).siblings('.additional-hours');
        let hours = parseInt($('.additional-hours').val()) || 0;
        if (hours > 0) {
            $('.additional-hours').val(hours - 1);
        }
        updatePrice();
        // Trigger the change event after updating the value and price
        input.trigger('change');
    });

    // Listen for changes in the input field
    $('.additional-hours').on('input', function() {
        updatePrice();
    });

    // Listen for changes in the time-unit dropdown
    $('.time-unit').on('change', function() {
        updatePrice();
    });

    // Preset time buttons (convert minutes to hours and update)
    $('.preset-buttons button').on('click', function() {
        let time = $(this).data('time');
        let price = parseFloat($(this).data('price'));
        let basePrice = parseFloat(getBasePrice());
        let totalPrice = basePrice + price;
        let formatedTotal = totalPrice.toFixed(2);

        let hours = 0;
        let displayText = '';

        if (time.includes('h')) {
            // If time is in hours (e.g., '1h', '2h'), convert directly
            hours = parseInt(time); // Convert '1h' to 1, '2h' to 2, etc.
            displayText = hours + " hours";
        } else if (time.includes('min')) {
            // If time is in minutes (e.g., '15min', '30min')
            let minutes = parseInt(time); // Extract the numeric part (e.g., 15, 30)
            hours = minutes / 60; // Convert minutes to hours
            displayText = minutes + " minutes";
        }

        // Now `hours` contains the time in hours (can be fractional for minutes)
        $('.additional-hours').val(hours);
        $('.additional-time-cost').text('$' + price);
        $('.total-cost').text('$' + formatedTotal);
        $('.additional-time').text(displayText); // Update the display text for additional time

        // Update hidden fields
        $('.additional-time-hidden').val(hours);
        $('.additional-cost-hidden').val(price);

        let input = $('.additional-hours');
        input.trigger('change');
    });

    // Initial check on page load
    $('input[name*="[no_customization]"]').each(function() {
        toggleCustomDuration($(this));
    });

    // Toggle visibility on checkbox change
    $('input[name*="[no_customization]"]').on('change', function() {
        toggleCustomDuration($(this));
    });

    function toggleCustomDuration(checkbox) {
        // Find the parent row containing the custom duration selection
        var cartItem = checkbox.closest('.cart-additional-filed');
        var durationSelection = cartItem.find('.custom-duration-selection');

        // Hide or show the .custom-duration-selection based on checkbox state
        if (checkbox.is(':checked')) {
            durationSelection.hide();
        } else {
            durationSelection.show();
        }
    }

    $('.additional-hours').on('change', function () {
        console.log('Additional hours changed');
        let cartItemKey = $(this).closest('.cart-item-container').find('input[name^="cart["]').attr('name').match(/\[([^\]]+)\]/)[1];
        let additionalTime = $(this).closest('.cart-item-container').find('.additional-hours').val();
        let additionalCost = $(this).closest('.cart-item-container').find('.additional-cost-hidden').val();
        console.log('Cart item key: ' + cartItemKey);
        console.log('Additional time: ' + additionalTime);
        console.log('Additional cost: ' + additionalCost);
        console.log(customCartParams);

        // Send AJAX request
        $.ajax({
            url: customCartParams.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_custom_data',
                security: customCartParams.nonce,
                cart_item_key: cartItemKey,
                additional_time: additionalTime,
                additional_cost: additionalCost
            },
            success: function(response) {
                if (response.success) {
                    let results = response.data;
                    let additional_time = results.additional_time;
                    let additional_cost = results.additional_cost;
                    let total_cost = results.updated_total;

                    // Update the displayed total with the new value
                    $('tr.additional-cost td .woocommerce-Price-amount.amount').first().html('<bdi><span class="woocommerce-Price-currencySymbol">$</span>' + additional_cost + '</bdi>');
                    $('tr.order-total td .woocommerce-Price-amount.amount').first().html('<bdi><span class="woocommerce-Price-currencySymbol">$</span>' + total_cost + '</bdi>');


                   
                    
                    
                } else {
                    console.error('Error updating cart: ' + response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });


    $('.no-customzation').on('change', function() {
        var cartItemKey = $('.no-customzation').attr('name').match(/cart\[([^\]]+)\]/)[1]; // Get the cart item key

        // If checked, reset the cart item to its default state
        if ($(this).is(':checked')) {
            $.ajax({
                url: customCartParams.ajax_url, // Replace with the AJAX URL
                type: 'POST',
                data: {
                    action: 'reset_cart_item', // Your custom AJAX action
                    security: customCartParams.nonce, // Security nonce
                    cart_item_key: cartItemKey
                },
                success: function(response) {
                    if (response.success) {
                        // Reload the cart fragment or page to reflect the changes
                        console.log(response.data);
                        //$(document.body).trigger('wc_update_cart');
                        let results = response.data;
                        let total_cost = results.updated_total;
                        $('tr.order-total td .woocommerce-Price-amount.amount').first().html('<bdi><span class="woocommerce-Price-currencySymbol">$</span>' + total_cost + '</bdi>');
                    } else {
                        alert('Failed to reset the cart item.');
                    }
                },
                error: function(error) {
                    console.error('AJAX error:', error);
                }
            });
        }
    });


    
});
