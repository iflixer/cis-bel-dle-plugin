<?php

$pageTitle = 'CDNHub - Мониторинг новинок';

include dirname(__FILE__) . '/header.php';

?>

<?php if ($_SESSION['mass_insert_success']) { ?>
	<div class="alert alert-success">Выбранные результаты добавлены успешно</div>
<?php } unset($_SESSION['mass_insert_success']); ?>

<?php if ($this->config['api']['token']) { ?>

<div class="accordion" id="accordionBase">

	
	<div class="card vh-card mb-3">

		<div id="collapseSearch" class="collapse show" aria-labelledby="headingSearch">
			<div class="card-body">
			
				<form action="<?php echo $PHP_SELF; ?>" method="GET">
				  <input type="hidden" name="mod" value="cdnhub">
				  <input type="hidden" name="action" value="base">
				  <div class="row">
				    <div class="col">
				      <input type="text" name="search" class="form-control" value="<?php echo $search; ?>" placeholder="Название ролика или Кинопоиск ID">
				    </div>
				    <div class="col-5" style="margin-left: -20px;">
				      <button type="submit" class="btn btn-primary">Искать</button>
				    </div>
				  </div>
				</form>

			</div>
		</div>

	</div>


	<div class="card bg-secondary mb-3">
  	<div class="card-header">Мониторинг новинок</div>
  	<div class="card-body">

				<form id="baseForm" action="" method="POST">

				<div style="overflow-x: auto; max-width: 100%;">
				
				<table class="table table-hover" style="min-width: 890px;">
					<thead>
						<tr>
							<th scope="col" style="min-width:143px">ID</th>
							<th scope="col" style="min-width:100px">Дата</th>
							<th scope="col">Название</th>
							<th scope="col">Качество</th>
							<th scope="col">Озвучка</th>
							<th scope="col">Год</th>
							<th scope="col">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" title="Выделить все" id="baseInsertAll">
									<label class="form-check-label" for="baseInsertAll"></label>
								</div>
							</th>
							<th scope="col">Наличие</th>
						</tr>
					</thead>
					<tbody>
						<?php

							$i = 1;

							foreach ($data as $item) {

								$exist = false;

								// kinopoisk_id

								if (intval($item['kinopoisk_id'])) {
									$kinopoisk_id = intval($item['kinopoisk_id']);

									$xfield = $this->config['xfields']['search']['kinopoisk_id'];

									$query = "SELECT id, category, alt_name, SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) `kinopoisk_id` FROM " . PREFIX . "_post WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) = {$kinopoisk_id}";

									$result = $db->query($query);

									if ($result->num_rows)
										$exist = true;
									else
										$exist = false;
								}

								// imdb_id

								if (!$exist && $item['imdb_id']) {
									$imdb_id = $item['imdb_id'];

									$xfield = $this->config['xfields']['search']['imdb_id'];

									$query = "SELECT id, category, alt_name, SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) `imdb_id` FROM " . PREFIX . "_post WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$xfield}|', -1), '||', 1) = '{$imdb_id}'";

									$result = $db->query($query);

									if ($result->num_rows)
										$exist = true;
									else
										$exist = false;
								}

								$row = $result->fetch_assoc();

								if ($row) {
									if ($config['allow_alt_url']) {
										if( $config['seo_type'] == 1 OR $config['seo_type'] == 2  ) {
											if( $row['category'] and $config['seo_type'] == 2 )
												$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
											else
												$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
										} else
											$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
									} else
										$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
								} else
									$full_link = false;

								require dirname(__FILE__) . '/../actions/base.item.php';

								$i++;

							}

						?>
					</tbody>
				</table>

				</div>

					<?php if ($prev || $next) { ?>
							
						<nav aria-label="..." class="base-nav">
						  <ul class="pagination base-pagination">

						    <?php if ($prev) { ?>
							    <li class="page-item">
							      <a class="page-link" href="<?php echo $prev; ?>">Назад</a>
							    </li>
						  	<?php } else { ?>
						  		<li class="page-item disabled">
							      <span class="page-link">Назад</span>
							    </li>
						  	<?php } ?>

						  	<?php if ($prev) { ?>
							    <li class="page-item">
							      <a class="page-link" href="<?php echo $prev; ?>"><?php echo ($page - 1); ?></a>
							    </li>
						  	<?php } ?>

						    <li class="page-item active" aria-current="page">
						      <span class="page-link">
						        <?php echo $page; ?>
						        <span class="sr-only">(current)</span>
						      </span>
						    </li>

						    <?php if ($next) { ?>
							    <li class="page-item">
							      <a class="page-link" href="<?php echo $next; ?>"><?php echo ($page + 1); ?></a>
							    </li>
						  	<?php } ?>

						    <?php if ($next) { ?>
							    <li class="page-item">
							      <a class="page-link" href="<?php echo $next; ?>">Далее</a>
							    </li>
						  	<?php } else { ?>
						  		<li class="page-item disabled">
							      <span class="page-link">Далее</span>
							    </li>
						  	<?php } ?>

						  </ul>
						</nav>

					<?php } ?>
					
					<div class="row row-mass">

						<div class="col" style="padding-right:0">
							<select class="form-select" id="baseMassAction" name="mass_action">
								<option value="">Выберите действие</option>
								<option value="kp_ids">Получить список Кинопоиск ID</option>
								<option value="add_news">Добавить новости на сайте</option>
							</select>
						</div>

						<div class="col-4" style="padding-right:0">
							<button type="submit" class="btn btn-success base-submit" title="Выполнить действие">Выполнить</button>
						</div>

					</div>


				</form>

		</div>

	</div>

</div>

<!-- Kinopoisk IDs List Modal -->
<div class="modal fade" id="kpIdsListModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="kpIdsListModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="kpIdsListModalLabel">Список выбранных Кинопоиск ID</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true"></span>
		        </button>
			</div>
			<div class="modal-body">
				
				<textarea id="kpIdsList" class="form-control"></textarea>

			</div>
		</div>
	</div>
</div>

<?php } else { ?>

<div class="alert alert-warning">Чтобы получить доступ к этому разделу укажите в настройках свой персональный api токен</div>

<?php } ?>

<?php

include dirname(__FILE__) . '/footer.php';