(function ($) {
	'use strict';

	$(function () {

		$(document.body).on('click', '.nrp_apply_redemption_coupon', function (event) {
			event.preventDefault();
			nrp_apply_coupon(this);
		});

		function nrp_apply_coupon(el) {

			var coupon = $(el).data('coupon');
			var data = {
				coupon_code: coupon,
				security: nrp_ajax_var.nonce_apply_coupon,
			};

			$.post(nrp_ajax_var.site_url+'/?wc-ajax=apply_coupon', data).done(function (data) {
				$(document.body).trigger('wc_update_cart');
				$(document.body).trigger('update_checkout');
			});
		}

	});

})(jQuery);
