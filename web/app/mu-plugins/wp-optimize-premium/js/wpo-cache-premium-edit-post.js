jQuery(function($) {
	var send_command = wp_optimize.send_command;

	/**
	 * Handle disable/enable post caching on a single post edit page.
	 */
	$('#wpo_disable_single_post_caching').on('change', function () {
		var checkbox = $(this),
			post_id = checkbox.data('id'),
			disable = checkbox.prop('checked');

		checkbox.prop('disabled', true);

		send_command('change_post_disable_option', {
			post_id: post_id,
			meta_key: '_wpo_disable_caching',
			disable: disable
		}, function (response) {
			if (response.result) {
				checkbox.prop('checked', response.disabled);
			}
		})
		.always(function () {
			checkbox.prop('disabled', false);
		});
	});


	/**
	 * Handle disable/enable lazy-load on a single post edit page.
	 */
	$('#wpo_disable_single_post_lazyload').on('change', function () {
	var checkbox = $(this),
		post_id = checkbox.data('id'),
		disable = checkbox.prop('checked');

		checkbox.prop('disabled', true);

		send_command('change_post_disable_option', {
			post_id: post_id,
			meta_key: '_wpo_disable_lazyload',
			disable: disable
		}, function (response) {
			if (response.result) {
				checkbox.prop('checked', response.disabled);
			}
		})
		.always(function () {
			checkbox.prop('disabled', false);
		});
	});

	$('#wpo_always_purge_this_post_type_select').select2();

	$('#wpo_always_purge_this_post_type_select').on('change', function() {

		var select = $(this),
			post_id = select.data('id'),
			post_type = select.val().join(',');

		send_command('always_purge_post_update', {
			post_id: post_id,
			post_type: post_type
		});

	});
});