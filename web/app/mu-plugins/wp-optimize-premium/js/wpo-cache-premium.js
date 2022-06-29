jQuery(function($) {
	var send_command = wp_optimize.send_command;

	/**
	 * Handle delete from cache on the Advanced settings tab.
	 */
	$('.wpo-exclude-from-cache').on('click', function () {
		var btn = $(this),
			post_id = btn.data('id');

		send_command('change_post_disable_option', {
			post_id: post_id,
			meta_key: '_wpo_disable_caching',
			disable: 0
		}, function (response) {
			if (response.result) {
				var row = btn.closest('tr');
				row.fadeOut('fast', function () {
					if (!(row.prev().is('tr') || row.next().is('tr'))) {
						row.closest('table').remove();
					}
					row.remove();
				});
			}
		});
	});

	/**
	 * Check the response from save cache settings.
	 */
	$(document).on('validate_cloudflare_settings', function(event, response) {
		if (response && response.hasOwnProperty('cloudflare_error')) {
			$('.wpo-error__cloudflare-cache').removeClass('wpo_hidden').find('p').text(response.cloudflare_error);
		} else {
			$('.wpo-error__cloudflare-cache').addClass('wpo_hidden').find('p').text('');
		}
	});

	/**
	 * Handle change "Purge Cloudflare cached pages" checkbox state.
	 */
	$('#purge_cloudflare_cache').on('change', function() {
		var checkbox = $(this),
			cloudflare_credentials_div = $('#wpo_cloudflare_credentials');

		if (checkbox.prop('checked')) {
			cloudflare_credentials_div.show();
		} else {
			cloudflare_credentials_div.hide();
		}
	});

	/**
	 * Allow select only one element from group of elements with .wpo-select-group class.
	 */
	$('.wpo-select-group').on('change', function() {
		var current = $(this);
		if (current.hasClass('wpo-select-group-processing')) return;
		update_select_group_view(current);
	});

	if ($('.wpo-select-group:checked').length > 0) {
		update_select_group_view($('.wpo-select-group:checked').first());
	}

	/**
	 * Update .wpo-select-group elements state.
	 *
	 * @param {Object} current
	 */
	function update_select_group_view(current) {
		$('.wpo-select-group').each(function() {
			var el = $(this);
			if (current[0] != el[0]) {
				el.prop('checked', false)
				.addClass('wpo-select-group-processing')
				.trigger('change')
				.removeClass('wpo-select-group-processing')
				.prop('disabled', current.prop('checked'));
			}
		});
	}
});