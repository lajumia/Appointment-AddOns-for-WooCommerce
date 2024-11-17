jQuery(document).ready(function($) {
    // Initially disable all input fields except 1 Minute Price
    $('.options_group .form-field input').not('#_price_1min').prop('disabled', true);

    // Watch for changes in the 1 Minute Price field
    $('#_price_1min').on('input', function() {
        let oneMinutePrice = parseFloat($(this).val());

        // If the input is a valid number, update other fields based on your conditions
        if (!isNaN(oneMinutePrice)) {
            $('#_appointment_base_price').val((oneMinutePrice * 30).toFixed(2));
            $('#_price_15min').val((oneMinutePrice * 15).toFixed(2));
            $('#_price_45min').val((oneMinutePrice * 45).toFixed(2));
            $('#_price_1h').val((oneMinutePrice * 60).toFixed(2));
            $('#_price_2h').val((oneMinutePrice * 120).toFixed(2));
            $('#_price_3h').val((oneMinutePrice * 180).toFixed(2));
            $('#_price_6h').val((oneMinutePrice * 360).toFixed(2));
            $('#_price_12h').val((oneMinutePrice * 720).toFixed(2));
            $('#_price_24h').val((oneMinutePrice * 1440).toFixed(2));

            // Enable all input fields when the 1 Minute Price has a valid value
            $('.options_group .form-field input').prop('disabled', false);
        } else {
            // If the input is not valid, disable all fields again except 1 Minute Price
            $('.options_group .form-field input').not('#_price_1min').prop('disabled', true);
        }
    });
});
