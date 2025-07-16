var vhWriteTarget,
	vhWriteModal = new bootstrap.Modal(document.getElementById('vhWriteModal'));

if (vh.write.source) {
	if ($(vh.write.source).val())
		$('#vhClearSource').show();
}

$('#vhClearSource').click(function() {

	if (vh.write.source) {
		var element = $(vh.write.source);
		if (element.val())
			element.val('');
	}

	$(this).hide();

});

$('#vhClearSearch').click(function() {

	$('#vhSearchResults').hide().html('');
	$(this).hide();

});

$('#vhSearchTitle').keypress(function(event){
	if (event.keyCode == 13) {
		$('#vhSearch').click();
		return false;
	}
});

$('#vhSearch').click(function() {

	var field, value;

	// Parser Kinopoisk ID

	if ($('#pspvolt_id').length && $('#pspvolt_id').val()) {
		field = 'kinopoisk_id';
		value = $('#pspvolt_id').val();
	}

	// Imdb ID

	if (vh.search.imdb_id) {
		var element = $(vh.search.imdb_id);
		if (element.val()) {
			field = 'imdb_id';
			value = element.val();
		}
	}

	// Kinopoisk ID

	if (vh.search.kinopoisk_id) {
		var element = $(vh.search.kinopoisk_id);
		if (element.val()) {
			field = 'kinopoisk_id';
			value = element.val();
		}
	}

	// title

	if (vh.search.title) {
		var element = $(vh.search.title);
		if (element.val()) {
			field = 'title';
			value = element.val();
		}
	}

	if (!field || !value || value.length < 3) {
		HideLoading();
		alert('Не найдено ни одного поля для поиска!');
		return false;
	}

	ShowLoading('');

	$.ajax({
		url: vhBaseUrl + '&action=search&field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(value),
		dataType: 'json',
		cache: false
	}).done(function(data) {

		HideLoading();

		// notfound

		if (data.notfound) {

			$('#vhSearchResults, #vhClearSearch').hide();
			$('#vhNotFound').show();

		}

		// success

		if (data.success) {

			var result = '';

			$.each(data.result, function(key, item) {

				result += '<div class="vh-item">';

				if (item.type_ru)
					result += '<div class="vh-item-type vh-item-type-' + item.type + '">' + item.type_ru + '</div>';

				if (item.title_rus)
					result += '<div class="vh-item-title">' + item.title_rus + '</div>';

				if (item.title_orig)
					result += '<div class="vh-item-title vh-title-en">' + item.title_orig + '</div>';

				if (item.translation)
					result += '<div class="vh-item-type vh-item-type-translation">' + item.translation + '</div>';

				if (item.quality)
					result += '<div class="vh-item-type vh-item-type-quality">' + item.quality + '</div>';

				if (item.type == 'serial')
					result += '<div class="vh-item-type vh-item-type-episode"><strong>' + item.season + '</strong> Сезон, <strong>' + item.episode + '</strong> Серия</div>';

				result += '<button type="button" class="vh-btn vh-btn-outline-light vh-iframe"';

				result += ' source="' + item.source + '"';

				result += ' title="Вставить ссылку на iframe"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.5 9.38455V14.6162C9.5 15.1858 10.1099 15.5475 10.6097 15.2743L15.3959 12.6582C15.9163 12.3737 15.9162 11.6263 15.3958 11.3419L10.6097 8.72641C10.1099 8.45328 9.5 8.81499 9.5 9.38455ZM5.25 3C3.45507 3 2 4.45507 2 6.25V17.75C2 19.5449 3.45507 21 5.25 21H18.75C20.5449 21 22 19.5449 22 17.75V6.25C22 4.45507 20.5449 3 18.75 3H5.25ZM3.5 6.25C3.5 5.2835 4.2835 4.5 5.25 4.5H18.75C19.7165 4.5 20.5 5.2835 20.5 6.25V17.75C20.5 18.7165 19.7165 19.5 18.75 19.5H5.25C4.2835 19.5 3.5 18.7165 3.5 17.75V6.25Z" fill="#212121"/></svg></button>';

				result += '<button type="button" class="vh-btn vh-btn-outline-light vh-write" title="Заполнить поля"';

				result += ' title_rus="' + item.title_rus + '"';
				result += ' title_orig="' + item.title_orig + '"';
				result += ' slogan="' + item.slogan + '"';
				result += ' description="' + item.description + '"';
				result += ' year="' + item.year + '"';
				result += ' duration="' + item.duration + '"';
				result += ' genres="' + item.genres + '"';
				result += ' countries="' + item.countries + '"';
				result += ' age="' + item.age + '"';
				result += ' poster="' + item.poster + '"';

				result += ' kinopoisk_id="' + item.kinopoisk_id + '"';
				result += ' imdb_id="' + item.imdb_id + '"';

				result += ' source="' + item.source + '"';
				
				result += ' quality="' + item.quality + '"';
				result += ' custom_quality="' + item.custom_quality + '"';

				result += ' translation="' + item.translation + '"';
				result += ' translations="' + item.translations + '"';
				result += ' custom_translation="' + item.custom_translation + '"';
				result += ' custom_translations="' + item.custom_translations + '"';

				result += ' season="' + item.season + '"';
				result += ' episode="' + item.episode + '"';

				result += ' format_season="' + item.format_season + '"';
				result += ' format_episode="' + item.format_episode + '"';

				result += ' seo_url="' + item.seo_url + '"';
				result += ' seo_title="' + item.seo_title + '"';
				result += ' seo_meta_title="' + item.seo_meta_title + '"';
				result += ' seo_meta_description="' + item.seo_meta_description + '"';

				result += '><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17.5 12C20.5376 12 23 14.4624 23 17.5C23 20.5376 20.5376 23 17.5 23C14.4624 23 12 20.5376 12 17.5C12 14.4624 14.4624 12 17.5 12ZM20.1465 15.1464L16.0541 19.2388L14.9 17.7C14.7343 17.4791 14.4209 17.4343 14.2 17.6C13.9791 17.7657 13.9343 18.0791 14.1 18.3L15.6 20.3C15.7826 20.5434 16.1384 20.5687 16.3536 20.3535L20.8536 15.8535C21.0488 15.6583 21.0488 15.3417 20.8536 15.1464C20.6583 14.9512 20.3417 14.9512 20.1465 15.1464Z" fill="#212121"/><path d="M11.0189 17H2.75L2.64823 17.0068C2.28215 17.0565 2 17.3703 2 17.75C2 18.1642 2.33579 18.5 2.75 18.5H11.0764C11.0261 18.174 11 17.8401 11 17.5C11 17.3318 11.0064 17.165 11.0189 17Z" fill="#212121"/><path d="M11.7322 14.5H2.75C2.33579 14.5 2 14.1642 2 13.75C2 13.3703 2.28215 13.0565 2.64823 13.0068L2.75 13H12.8096C12.3832 13.4443 12.0194 13.949 11.7322 14.5Z" fill="#212121"/><path d="M21.25 9H2.75L2.64823 9.00685C2.28215 9.05651 2 9.3703 2 9.75C2 10.1642 2.33579 10.5 2.75 10.5H21.25L21.3518 10.4932C21.7178 10.4435 22 10.1297 22 9.75C22 9.33579 21.6642 9 21.25 9Z" fill="#212121"/><path d="M21.25 5H2.75L2.64823 5.00685C2.28215 5.05651 2 5.3703 2 5.75C2 6.16421 2.33579 6.5 2.75 6.5H21.25L21.3518 6.49315C21.7178 6.44349 22 6.1297 22 5.75C22 5.33579 21.6642 5 21.25 5Z" fill="#212121"/></svg></button>';

				result += '</div>';

			});

			$('#vhSearchResults').html(result);

			$('.vh-iframe').click(function() {

				// source
				
				if (vh.write.source) {
					var source = $(this).attr('source');
					if (source)
						vh_write(vh.write.source, source);

					$('#vhClearSource').show();
				}

			});

			$('.vh-write').click(function() {

				$('#vhWriteModalTitle').html($(this).attr('title_rus'));

				vhWriteTarget = $(this);

				vhWriteModal.show();

			});

			$('#vhWrtieSelectedFields').click(function() {

				// kinopoisk_id
				
				if (vh.write.kinopoisk_id) {
					var kinopoisk_id = vhWriteTarget.attr('kinopoisk_id');
					if (kinopoisk_id)
						vh_write(vh.write.kinopoisk_id, kinopoisk_id);
				}

				// imdb_id
				
				if (vh.write.imdb_id) {
					var imdb_id = vhWriteTarget.attr('imdb_id');
					if (imdb_id)
						vh_write(vh.write.imdb_id, imdb_id);
				}



				// source
				
				if (vh.write.source) {
					var source = vhWriteTarget.attr('source');
					if (source)
						vh_write(vh.write.source, source);

					$('#vhClearSource').show();
				}



				// quality
				
				if (vh.write.quality) {
					var quality = vhWriteTarget.attr('quality');
					if (quality)
						vh_write(vh.write.quality, quality);
				}

				// translation
				
				if (vh.write.translation) {
					var translation = vhWriteTarget.attr('translation');
					if (translation)
						vh_write(vh.write.translation, translation);
				}

				// translations
				
				if (vh.write.translations) {
					var translations = vhWriteTarget.attr('translations');
					if (translations)
						vh_write(vh.write.translations, translations);
				}

				// custom quality
				
				if (vh.write.custom_quality) {
					var custom_quality = vhWriteTarget.attr('custom_quality');
					if (custom_quality)
						vh_write(vh.write.custom_quality, custom_quality);
				}

				// custom translation
				
				if (vh.write.custom_translation) {
					var custom_translation = vhWriteTarget.attr('custom_translation');
					if (custom_translation)
						vh_write(vh.write.custom_translation, custom_translation);
				}

				// custom translations
				
				if (vh.write.custom_translations) {
					var custom_translations = vhWriteTarget.attr('custom_translations');
					if (custom_translations)
						vh_write(vh.write.custom_translations, custom_translations);
				}

				// season
				
				if (vh.write.season) {
					var season = vhWriteTarget.attr('season');
					if (season)
						vh_write(vh.write.season, season);
				}

				// episode
				
				if (vh.write.episode) {
					var episode = vhWriteTarget.attr('episode');
					if (episode)
						vh_write(vh.write.episode, episode);
				}

				// format season
				
				if (vh.write.format_season) {
					var format_season = vhWriteTarget.attr('format_season');
					if (format_season)
						vh_write(vh.write.format_season, format_season);
				}

				// format episode
				
				if (vh.write.format_episode) {
					var format_episode = vhWriteTarget.attr('format_episode');
					if (format_episode)
						vh_write(vh.write.format_episode, format_episode);
				}



				// title_rus
				
				if (vh.write.title_rus) {
					var title_rus = vhWriteTarget.attr('title_rus');
					if (title_rus)
						vh_write(vh.write.title_rus, title_rus);
				}

				// title_orig
				
				if (vh.write.title_orig) {
					var title_orig = vhWriteTarget.attr('title_orig');
					if (title_orig)
						vh_write(vh.write.title_orig, title_orig);
				}

				// slogan
				
				if (vh.write.slogan) {
					var slogan = vhWriteTarget.attr('slogan');
					if (slogan)
						vh_write(vh.write.slogan, slogan);
				}

				// description
				
				if (vh.write.description) {
					var description = vhWriteTarget.attr('description');
					if (description)
						vh_write(vh.write.description, description);
				}

				// year
				
				if (vh.write.year) {
					var year = vhWriteTarget.attr('year');
					if (year)
						vh_write(vh.write.year, year);
				}

				// duration
				
				if (vh.write.duration) {
					var duration = vhWriteTarget.attr('duration');
					if (duration)
						vh_write(vh.write.duration, duration);
				}

				// genres
				
				if (vh.write.genres) {
					var genres = vhWriteTarget.attr('genres');
					if (genres)
						vh_write(vh.write.genres, genres);
				}

				// countries
				
				if (vh.write.countries) {
					var countries = vhWriteTarget.attr('countries');
					if (countries)
						vh_write(vh.write.countries, countries);
				}

				// age
				
				if (vh.write.age) {
					var age = vhWriteTarget.attr('age');
					if (age)
						vh_write(vh.write.age, age);
				}

				// poster
				
				if (vh.write.poster) {
					var poster = vhWriteTarget.attr('poster');
					if (poster)
						vh_write(vh.write.poster, poster);
				}



				// seo

				if (vh.seo.on) {

					// seo url
				
					if (vh.seo.url) {
						var seo_url = vhWriteTarget.attr('seo_url');
						if (seo_url)
							vh_write(vh.seo.url, seo_url);
					}

					// seo title
				
					if (vh.seo.title) {
						var seo_title = vhWriteTarget.attr('seo_title');
						if (seo_title)
							vh_write(vh.seo.title, seo_title);
					}

					// seo meta title
				
					if (vh.seo.meta_title) {
						var seo_meta_title = vhWriteTarget.attr('seo_meta_title');
						if (seo_meta_title)
							vh_write(vh.seo.meta_title, seo_meta_title);
					}

					// seo meta description
				
					if (vh.seo.meta_description) {
						var seo_meta_description = vhWriteTarget.attr('seo_meta_description');
						if (seo_meta_description)
							vh_write(vh.seo.meta_description, seo_meta_description);
					}

				}

				vhWriteModal.hide();

				return false;

			});

			$('#vhNotFound').hide();
			$('#vhSearchResults, #vhClearSearch').show();

		}

		// error

		if (data.error) {

			$('#vhNotFound, #vhSearchResults, #vhClearSearch').hide();

			alert('Search failed: ' + data.message);

		}

	}).fail(function(jqxhr, textStatus) {
		
		HideLoading();

		console.log(jqxhr.responseText);

	});

});

function vh_write(field, value) {

	if (typeof(value) == 'undefined')
		return false;

	if ($('#xfield_holder_' + field.replace('#xf_', '') + ' select').length) {
		var options = $('#xfield_holder_' + field.replace('#xf_', '') + ' select option');

		if (options.length) {
			$.each(options, function (key, element) {
				var xfvalue = $(element).text();

				if (xfvalue && xfvalue == value)
					$('#xfield_holder_' + field.replace('#xf_', '') + ' select').val(key).change();
			});
		}

		return false;
	}

	var element = $(field);

	element.val(value);

	if ($(field + '_tag').length) {
		$(field).importTags('');
		var tags = value.split(', ');
		for (var i = 0; i < tags.length; i++) {
			$(field).addTag(tags[i]);
		}
	}

	if ($(field + "-tokenfield").length) {
		$(field).tokenfield("setTokens", []);
		$(field + "-tokenfield").val();

		if (value)
			$(field).tokenfield('setTokens', value.split(', '));
	}

	if ('froalaEditor' in element)
		element.froalaEditor('html.set', value);

	if (typeof tinymce != 'undefined') {
		var _field = field.replace('#', '');

		if (field && tinymce.get(_field))
			tinymce.get(_field).setContent(value);
	}

}

$("input.checkAll").click(function() {
	var elements = $(this).parent().parent().next().find("input.form-check-input:not(:disabled)");
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