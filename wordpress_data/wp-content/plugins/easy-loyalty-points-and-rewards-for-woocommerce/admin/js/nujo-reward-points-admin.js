(function ($) {
	'use strict';

	$(function () {

		$('.nrp_update_balance_form').submit(function (event) {

			event.preventDefault();

			$.ajax({
				type: 'POST',
				url: nrp_ajax_var.url,
				data: {
					action: 'nrp_update_balance',
					nonce: nrp_ajax_var.nonce_update_balance,
					form_data: $(this).serialize(),
					new_balance: $(this).find('input[name="new_balance"]').val(),
					account_id: $(this).find('input[name="account_id"]').val(),
				},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						alert(data.data.message);
						$('#nrp_points_balance_' + data.data.account_id).html(data.data.balance);
						$('#nrp_points_balance_' + data.data.account_id).css({opacity: 0});
						$('#nrp_points_balance_' + data.data.account_id).animate({opacity: 1}, 700 );
						$('#nrp_update_balance_form_' + data.data.account_id + ' input[name="new_balance"]').val('');
					} else {
						alert(data.data.message);
					}
				},
				error: function (data) {
					if (data.responseJSON.data.message) {
						alert(data.responseJSON.data.message);
					} else {
						alert('Error ' + data.statusCode);
					}
				},
			});

		});

		$('a.nrp-reset-message').click(function (event) {
			event.preventDefault();
			$('#'+$(this).attr("data-id")).val($(this).attr("data-message"));
			$('#'+$(this).attr("data-id")).css({opacity: 0});
			$('#'+$(this).attr("data-id")).animate({opacity: 1}, 700 );
		});

	});

})(jQuery);

