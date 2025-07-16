<tr>
	<td>
		<?php
			$_links = [];
			if ($item['kinopoisk_id']) { $_links[] = '<a href="' . 'https://www.kinopoisk.ru/film/' . $item['kinopoisk_id'] . '/' . '" target="_blank">' . $item['kinopoisk_id'] . '</a>'; }
			if ($item['imdb_id']) { $_links[] = '<a href="' . 'https://www.imdb.com/title/' . $item['imdb_id'] . '/' . '" target="_blank">' . $item['imdb_id'] . '</a>'; }
			echo implode(', ', $_links);
		?>
	</td>
	<td><?php echo $item['created'] ? $item['created'] : ''; ?></td>
	<td>
		<?php if ($full_link) { ?>
			<a href="<?php echo $full_link; ?>" target="_blank">
		<?php } ?>
		<?php echo $item['title_rus'] . ($item['title_orig'] ? "<div style=\" margin-top: -2px; font-size: 87%;\">{$item['title_orig']}</div>" : ''); ?>
		<?php if ($full_link) { ?>
			</a>
		<?php } ?>
		<div>
			<span style="display:inline-block;font-size:14px;color:#888">
				<?php if ($item['season']) { ?>
					<?php echo $item['season']; ?> Сезон,
				<?php } ?>
				<?php if ($item['episode']) { ?>
					<?php echo $item['episode']; ?> Серия
				<?php } ?>
			</span>
		</div>
	</td>
	<td>
		<span class="badge bg-dark" style="font-size: 97%; font-weight: 400;"><?php echo $item['quality']; ?></span>
	</td>
	<td>
		<?php foreach ($item['translations'] as $translation) { ?>
			<span class="badge bg-info" style="font-size: 97%; font-weight: 400;margin-bottom:2px"><?php echo $translation['title']; ?></span><br>
		<?php } ?>
	</td>
	<td><?php echo $item['year'] ? $item['year'] : ''; ?></td>
	<td>
		<?php if ($_links) { ?>
		<div class="form-check">
			<input type="checkbox" class="form-check-input base-insert" id="baseInsert<?php echo $i; ?>"<?php echo ($item['kinopoisk_id'] ? " data-kpid=\"{$item['kinopoisk_id']}\" name=\"base[]\" value=\"{$item['imdb_id']}\"" : ''); ?><?php echo ($item['imdb_id'] ? " data-kpid=\"{$item['imdb_id']}\" name=\"base[]\" value=\"{$item['imdb_id']}\"" : ''); ?>>
			<label class="form-check-label" for="baseInsert<?php echo $i; ?>"></label>
		</div>
		<?php } ?>
	</td>
	<td>
		<?php if ($_links) { ?>
			<?php echo ($exist ? '<span style="display:inline-block;padding: 0 6px;" class="bg-success text-white pl-1 pr-1" title="Наличие новости на сайте">Да</span>' : '<div style="display:inline-block;color:#ccc;padding: 0 6px;" class="bg-light pl-1 pr-1" title="Наличие новости на сайте">Нет</div>'); ?>
		<?php } ?></td>
</tr>