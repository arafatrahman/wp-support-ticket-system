jQuery(document).ready(function($) {
    'use strict';

    // Initialize DataTables for the user's ticket list
    if ($('#userTicketsTable').length) {
        $('#userTicketsTable').DataTable({
            "order": [] // Disable initial sorting
        });
    }

    // Handle the "Regarding" dropdown change on the new ticket form
    $('#ticket-type').on('change', function() {
        if ($(this).val() === 'product') {
            const productSelect = $('#ticket-product');
            const wrapper = $('#wsts-product-select-wrapper');
            wrapper.show();
            productSelect.prop('disabled', true).html('<option value="">Loading products...</option>');

            // AJAX call to fetch user's purchased products
            $.ajax({
                url: wsts_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wsts_get_user_products',
                    nonce: wsts_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        productSelect.prop('disabled', false).empty();
                        if (response.data.length > 0) {
                             productSelect.append('<option value="">-- Select a product --</option>');
                            $.each(response.data, function(index, product) {
                                productSelect.append($('<option>', {
                                    value: product.id,
                                    text: product.name
                                }));
                            });
                        } else {
                            productSelect.html('<option value="">You have no purchased products.</option>');
                            productSelect.prop('disabled', true);
                        }
                    } else {
                         productSelect.html('<option value="">Could not load products.</option>');
                    }
                },
                error: function() {
                    productSelect.html('<option value="">Error loading products.</option>');
                }
            });

        } else {
            $('#wsts-product-select-wrapper').hide();
        }
    });

    // Handle the submission of the new ticket form
    $('#wsts-submit-ticket').on('click', function(e) {
        e.preventDefault();
        const form = $('#wsts-new-ticket-form');
        const submitButton = $(this);
        const notice = $('#wsts-form-notice');

        // Basic validation
        let hasError = false;
        form.find('input[required], textarea[required]').each(function(){
            if(!$(this).val()){
                $(this).addClass('is-invalid');
                hasError = true;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if(hasError){
            notice.removeClass('alert-success').addClass('alert-danger').text('Please fill all required fields.').show();
            return;
        }

        submitButton.prop('disabled', true).text('Submitting...');
        notice.hide();

        // Collect form data
        const formData = {
            action: 'wsts_create_new_ticket',
            nonce: wsts_ajax.nonce,
            subject: $('#ticket-subject').val(),
            type: $('#ticket-type').val(),
            product_id: $('#ticket-product').val(),
            priority: $('#ticket-priority').val(),
            page_url: window.location.href.split('?')[0] // Get URL without query params
        };

        // AJAX call to create the ticket
        $.ajax({
            url: wsts_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    notice.removeClass('alert-danger').addClass('alert-success').text(response.data.message).show();
                    // Redirect to the new ticket page to add the message
                    window.location.href = response.data.redirect_url;
                } else {
                    notice.removeClass('alert-success').addClass('alert-danger').text(response.data.message).show();
                    submitButton.prop('disabled', false).text('Submit Ticket');
                }
            },
            error: function() {
                notice.removeClass('alert-success').addClass('alert-danger').text('An unknown error occurred.').show();
                submitButton.prop('disabled', false).text('Submit Ticket');
            }
        });
    });

});
