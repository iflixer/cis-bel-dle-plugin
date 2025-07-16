// Toast

$('#Toast').on('hidden.bs.toast', function() {

	$('#settingsSave').prop('disabled', false);

});

function toast(message) {

	$('.toast-body').html(message);
	$('.toast').toast('show');

}

// Settings -> Save

$('#settingsSave').click(function() {

	$('#settingsSave').prop('disabled', true);

	// Custom Qualities
	
	var customQualities = new Array();

	$.each($('.custom-quality'), function(key, element) {

		var pattern = $(element).find('.custom-quality-from').val();
		var replacement = $(element).find('.custom-quality-to').val();

		if (pattern)
			customQualities.push(pattern + '|' + replacement);

	});

	customQualities = customQualities.join('\r\n');

	$('#settingsCustomQualities').val(customQualities);
	
	// Custom Translations
	
	var customTranslations = new Array();

	$.each($('.custom-translation'), function(key, element) {

		var pattern = $(element).find('.custom-translation-from').val();
		var replacement = $(element).find('.custom-translation-to').val();

		if (pattern)
			customTranslations.push(pattern + '|' + replacement);

	});

	customTranslations = customTranslations.join('\r\n');

	$('#settingsCustomTranslations').val(customTranslations);

	// Priority

	var updateSerialsPriority = new Array();

	$.each($('#serialsPriorityContainer .sortable'), function(i, e) {
		updateSerialsPriority[i] = $(e).data('id');
	});

	updateSerialsPriority = updateSerialsPriority.join();

	$('#settingsUpdateSerialsPriority').val(updateSerialsPriority);

	// Save

	var formData = $('#settingsForm').serialize();

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: baseUrl + '&action=settings',
		data: formData,
		cache: false,
		success: function(data) {
			
			if (data.status == 'success')
				toast('Настройки успешно сохранены');
			else {
				if (data.message)
					toast(data.message);
				else
					toast('Неизвестная ошибка');
			}

		},
		error:  function(xhr, exception) {

			var message = '';

			if (xhr.status === 0)
				message = 'Not connect';
			else if (xhr.status == 404)
				message = '404 Requested page not found';
			else if (xhr.status == 500)
				message = '500 Internal Server Error';
			else if (exception === 'parsererror')
				message = 'Requested JSON parse failed';
			else if (exception === 'timeout')
				message = 'Time out';
			else if (exception === 'abort')
				message = 'Ajax request aborted';
			else
				message = 'Uncaught Error';

			toast(message);

		}
	});

});

// Settings -> Update Row

if (updateType == 1)
	$('#vhUpdateRow > div:nth(1)').hide();
else
	$('#vhUpdateRow > div:nth(2), .cron-doc').hide();

$('#moduleUpdateTypeDefault, #moduleUpdateTypeCron').change(function() {

	if ($('#moduleUpdateTypeDefault').is(':checked')) {
		$('#vhUpdateRow > div:nth(1)').show();
		$('#vhUpdateRow > div:nth(2), .cron-doc').hide();
	}

	if ($('#moduleUpdateTypeCron').is(':checked')) {
		$('#vhUpdateRow > div:nth(2), .cron-doc').show();
		$('#vhUpdateRow > div:nth(1)').hide();
	}

});

// Settings -> Custom Quality

$('body').on('click', '.custom-quality-duplicate', function() {

	var elementParentId = 'qualityWriteAutocomplete' + $('#customQualityList > div').length;
	var elementId = 'qualityAutocomplete' + $('#customQualityList > div').length;

	var element = $('<div id="' + elementParentId + '" data-id="' + elementId + '" class="form-inline custom-quality" style="position:relative"><input id="' + elementId + '" type="text" class="form-control custom-quality-from" placeholder="Название из базы"><input type="text" class="form-control custom-quality-to" placeholder="Своё название"><button type="button" class="btn btn-danger custom-quality-delete" title="Удалить замену"><i class="fas fa-trash"></i></button></div>');

	$('#customQualityList').append(element);

	$('#' + elementId).autocomplete({ position: { my : "right bottom", at: "right top" }, appendTo: '#' + elementParentId, paramName: '', minChars: 0, lookup: qualities });

});

$('#customQualityList').on('click', '.custom-quality-delete', function() {

	var element = $(this).parent();

	$('#' + element.attr('data-id')).autocomplete('dispose');

	element.remove();

});

// Settings -> Custom Translation

$('body').on('click', '.custom-translation-duplicate', function() {

	var elementParentId = 'qualityWriteAutocomplete' + $('#customQualityList > div').length;
	var elementId = 'translationAutocomplete' + $('#customTranslationList > div').length;

	var element = $('<div id="' + elementParentId + '" data-id="' + elementId + '" class="form-inline custom-translation"><input id="' + elementId + '" type="text" class="form-control custom-translation-from" placeholder="Название из базы"><input type="text" class="form-control custom-translation-to" placeholder="Своё название"><button type="button" class="btn btn-danger custom-translation-delete" title="Удалить замену"><i class="fas fa-trash"></i></button></div>');

	$('#customTranslationList').append(element);

	$('#' + elementId).autocomplete({ appendTo: '#' + elementParentId, paramName: '', minChars: 0, lookup: translations });

});

$('#customTranslationList').on('click', '.custom-translation-delete', function() {

	var element = $(this).parent();

	$('#' + element.attr('data-id')).autocomplete('dispose');

	element.remove();

});

// Settings -> Priority

$(function() {

	$('#serialsPriorityContainer').sortable({
		connectWith: '#serialsNoPriorityContainer',
		placeholder: 'empty-space',
		update: function(event, ui) {
			var e = $('#serialsTranslation' + ui.item.data('id'));

			if (e.parent().attr('id') == 'serialsNoPriorityContainer') {
				e.removeClass('sortable-selected');
				e.removeClass('btn-outline-info').addClass('btn-outline-secondary');
				e.find('a').attr('title', 'Добавить перевод');
				e.find('a').find('svg').remove();
				e.find('a').append('<i class="fas fa-plus"></i>');
			}
		}
	});

	$('#serialsNoPriorityContainer').sortable({
		connectWith: '#serialsPriorityContainer',
		placeholder: 'empty-space',
		update: function(event, ui) {
			var e = $('#serialsTranslation' + ui.item.data('id'));

			if (e.parent().attr('id') == 'serialsPriorityContainer') {
				e.addClass('sortable-selected');
				e.addClass('btn-outline-info').removeClass('btn-outline-secondary');
				e.find('a').attr('title', 'Удалить перевод');
				e.find('a').find('svg').remove();
				e.find('a').append('<i class="fas fa-times"></i>');
			}
		}
	});

	$('#serialsNoPriorityContainer').on('click', '.sortable', function() {
		var e = $(this);

		e.addClass('sortable-selected');
		e.addClass('btn-outline-info').removeClass('btn-outline-secondary');
		e.find('a').attr('title', 'Удалить перевод');
		e.find('a').find('svg').remove();
		e.find('a').append('<i class="fas fa-times"></i>');

		$('#serialsPriorityContainer').append(e.clone());

		e.remove();
	});

	$('#serialsPriorityContainer').on('click', '.sortable', function() {
		var e = $(this);

		e.removeClass('sortable-selected');
		e.removeClass('btn-outline-info').addClass('btn-outline-secondary');
		e.find('a').attr('title', 'Добавить перевод');
		e.find('a').find('svg').remove();
		e.find('a').append('<i class="fas fa-plus"></i>');

		$('#serialsNoPriorityContainer').append(e.clone());

		e.remove();
	});

});

// Replacement

if ($('#replacementStatus').length) {

	// Category

	$('.categoryselect').chosen({
		allow_single_deselect: true,
		no_results_text: 'Ничего не найдено'
	});

	// Replacement -> Working

	function replacementWorking() {
		if ($('#replacementStatus').attr('data-status') == 'stop') {
			replacementStop();
			return false;
		}
		if ($('#replacementStatus').attr('data-status') == 'abort') {
			replacementAbort(true);
		}
		if ($('#replacementStatus').attr('data-status') == 'working') {
			var threads = parseInt($('#threads').val());
			var interval = parseInt($('#interval').val());

			var fields = [];

			if ($('#kinopoisk').is(':checked'))
				fields.push('kinopoisk_id');

			if ($('#imdb').is(':checked'))
				fields.push('imdb_id');

			if (fields.length == 0) {
				replacementAbort(true);
				toast('Не указано ни одного поля для поиска');
				return false;
			}

			var xf = [];

			if ($('#replacementXfeildsSource').is(':checked'))
				xf.push('source');

			if ($('#replacementXfeildsQuality').is(':checked'))
				xf.push('quality');

			if ($('#replacementXfeildsTranslation').is(':checked'))
				xf.push('translation');

			if ($('#replacementXfeildsSeason').is(':checked'))
				xf.push('season');

			if ($('#replacementXfeildsEpisode').is(':checked'))
				xf.push('episode');

			if ($('#replacementXfeildsTitleRus').is(':checked'))
				xf.push('title_rus');

			if ($('#replacementXfeildsTitleOrig').is(':checked'))
				xf.push('title_orig');

			if ($('#replacementXfeildsSlogan').is(':checked'))
				xf.push('slogan');

			if ($('#replacementXfeildsDescription').is(':checked'))
				xf.push('description');

			if ($('#replacementXfeildsYear').is(':checked'))
				xf.push('year');

			if ($('#replacementXfeildsDuration').is(':checked'))
				xf.push('duration');

			if ($('#replacementXfeildsGenres').is(':checked'))
				xf.push('genres');

			if ($('#replacementXfeildsCountries').is(':checked'))
				xf.push('countries');

			if ($('#replacementXfeildsAge').is(':checked'))
				xf.push('age');

			if ($('#replacementXfeildsPoster').is(':checked'))
				xf.push('poster');

			if ($('#replacementXfeildsTranslations').is(':checked'))
				xf.push('translations');

			if ($('#replacementXfeildsCustomQuality').is(':checked'))
				xf.push('custom_quality');

			if ($('#replacementXfeildsCustomTranslation').is(':checked'))
				xf.push('custom_translation');

			if ($('#replacementXfeildsCustomTranslations').is(':checked'))
				xf.push('custom_translations');

			if ($('#replacementXfeildsFormatSeason').is(':checked'))
				xf.push('format_season');

			if ($('#replacementXfeildsFormatEpisode').is(':checked'))
				xf.push('format_episode');

			if (xf.length == 0) {
				replacementAbort(true);
				toast('Не указано ни одного поля для заполнения данных');
				return false;
			}
			
			var threadsUrl = baseUrl + '&action=replacement_threads';

			var category = [];

			var i = 0;
			$('#category :selected').each(function(i, sel){ 
				category[i] = $(sel).val();
				i++;
			});

			if (category.length)
				threadsUrl += '&category=' + category.join(',');
			
			var lastPostId = parseInt($('#replacementStatus').attr('data-post-id'));

			if (lastPostId)
				threadsUrl += '&post_id=' + lastPostId;
			else
				$('#replacementCountContinue, #replacementCountSuccess, #replacementCountExist, #replacementCountNotFound').text('0');
			
			if (threads) {
				var formData = $('#replacementForm').serialize();

				$.ajax({
					type: 'POST',
					url: threadsUrl,
					data: formData,
					dataType: 'json',
					cache: false,
					success: function(data) {
						if (data.status == 'success') {
							if (data.count)
								$('#replacementCountContinue').text(data.count);

							$.each(data.next_posts_id, function(i, val) {
								var num = i + 1;
								
								if (num == data.next_posts_id.length) {
									$('#replacementStatus').attr('data-post-id', val);
								}
								
								replacementThread(val, fields);
							});
						}

						if (data.status == 'end') {
							if (data.code && data.code == '#1')
								toast('По вашему критерию в базе новостей не найдено');
							
							replacementAbort(true);
						}
						
						setTimeout('replacementWorking()', interval);
					},
					error: function(jqXHR, textStatus) {

						setTimeout('replacementWorking()', interval);
						
					}
				});
			}
		}
	}

	// Replacment -> Working -> Thread

	function replacementThread(post_id) {
		if ($('#replacementStatus').attr('data-status') == 'abort')
			return;
		
		var threadUrl = baseUrl + '&action=replacement_thread&post_id=' + post_id;

		var formData = $('#replacementForm').serialize();
		
		$.ajax({
			type: 'POST',
			data: formData,
			url: threadUrl,
			dataType: 'json',
			cache: false,
			success: function(data) {
				
				if ($('#replacementStatus').attr('data-status') == 'abort')
					return false;

				var status_continue = parseInt($('#replacementCountContinue').text());
				$('#replacementCountContinue').text(status_continue - 1);
				
				if (data.status == 'success')
					$('#replacementCountSuccess').text(parseInt($('#replacementCountSuccess').text()) + 1);

				if (data.status == 'exist')
					$('#replacementCountExist').text(parseInt($('#replacementCountExist').text()) + 1);

				if (data.status == 'error')
					$('#replacementCountNotFound').text(parseInt($('#replacementCountNotFound').text()) + 1);

			},
			error: function(jqXHR, textStatus) {
				
				$('#replacementCountContinue').text(parseInt($('#replacementCountContinue').text()) - 1);
				$('#replacementCountNotFound').text(parseInt($('#replacementCountNotFound').text()) + 1);

			}
		});
	}

	// Replacement -> Start

	function replacementStart() {
		$('.vh-replace-field').attr('disabled', 'disabled');
		$('#category').prop('disabled', true).trigger('liszt:updated');
		// отключение полей формы ...
		
		$('#replacementStart').hide().text('Продолжить');
		$('#replacementStop, #replacementAbort').show();
		
		$('#replacementStatus').attr('data-status', 'working');
		
		replacementWorking();
	}

	$('#replacementStart').click(function() {
		replacementStart();
	});

	// Replacemenet -> Stop

	function replacementStop() {
		setTimeout(function () {
			$('#replacementStart').show();
			$('#replacementStop').hide().removeAttr('disabled');
		}, 0);
	}

	$('#replacementStop').click(function() {
		$('#replacementStatus').attr('data-status', 'stop');
		$('#replacementStop').attr('disabled', 'disabled');
	});

	// Replacemenet -> Abort

	function replacementAbort(end) {
		$('#replacementStatus').attr('data-status', 'abort');
		
		setTimeout(function () {
			$('.vh-replace-field').removeAttr('disabled');
			$('#category').prop('disabled', false).trigger('liszt:updated');
			// включение полей формы ...
			
			$('#replacementStart').text('Начать').show();
			$('#replacementStop, #replacementAbort').hide();
			
			if (!end)
				$('#replacementCountContinue, #replacementCountSuccess, #replacementCountExist, #replacementCountNotFound').text('0');
			
			$('#replacementStatus').attr('data-post-id', '0');
		}, 0);
	}
	
	$('#replacementAbort').click(function() {
		replacementAbort();
	});

}

// Base

$('#baseInsertAll').click(function() {

	if ($(this).is(':checked'))
		$('.base-insert').each(function() { this.checked = true; });
	else
		$('.base-insert').each(function() { this.checked = false; });

});

$('#baseForm').submit(function() {
	var mass_action = $('#baseMassAction').val();

	if (!mass_action) {
		toast('Выберите действие');

		return false;
	}

	if (mass_action == 'kp_ids') {
		var kp_ids = [];

		$('.base-insert').each(function() {
			if (this.checked && $(this).data('kpid'))
				kp_ids.push($(this).data('kpid'));
		});

		if (kp_ids.length == 0) {
			toast('Вы ничего не выделили');
			return false;
		}

		$('#kpIdsList').val(kp_ids.join(', '));

		$('#kpIdsListModal').modal('show');

		return false;
	}
	
});

$("input.checkAll").click(function() {
	var elements = $(this).closest('.card-header').next().find("input.form-check-input:not(:disabled)");
	if($(this).is(":checked")){
		elements.prop("checked", true);
	} else {
		elements.prop("checked", false);
	}
});

$("input.form-check-input").click(function() {
	var target = $(this).parent().parent().parent().prev().find('input.checkAll');
	if($(this).parent().parent().parent().find("input.form-check-input:checked:not(:disabled)").length == 0){
		target.prop("checked", false);
	} else {
		target.prop("checked", true);
	}
});