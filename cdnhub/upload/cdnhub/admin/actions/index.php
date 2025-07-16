<?php

$pageTitle = 'CDNHub';

include dirname(__FILE__) . '/header.php';

?>

<div class="jumbotron bg-light mb-3">
	<div class="container">
		<h1 class="display-3">CDNHub</h1>
		<p class="big-text">
			Пожалуйста, пройдите первичную настройку модуля.
		</p>
		<p><a class="btn btn-primary btn" href="<?php echo cdnhub_action('settings'); ?>" role="button">Настройки модуля</a></p>
	</div>
</div>

<?php

include dirname(__FILE__) . '/footer.php';