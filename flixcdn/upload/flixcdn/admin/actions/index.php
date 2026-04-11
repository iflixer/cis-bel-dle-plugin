<?php

$pageTitle = 'FlixCDN';

include dirname(__FILE__) . '/header.php';

?>

<div class="jumbotron bg-light mb-3">
	<div class="container">
		<h1 class="display-3">FlixCDN</h1>
		<p class="big-text">
			Будь ласка, виконайте початкове налаштування модуля.
		</p>
		<p><a class="btn btn-primary btn" href="<?php echo flixcdn_action('settings'); ?>" role="button">Налаштування модуля</a></p>
	</div>
</div>

<?php

include dirname(__FILE__) . '/footer.php';