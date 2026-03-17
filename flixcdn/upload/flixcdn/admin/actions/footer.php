		<footer class="container">
			<p><b>FlixCDN</b> v<?php echo $flixcdn->version(); ?></p>
		</footer>

	</div>

	<div id="Toast" class="toast hide vh-toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="1000">
		<div class="toast-header">
			<strong class="me-auto">FlixCDN</strong>
			<button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close">
				<span aria-hidden="true"></span>
			</button>
		</div>
		<div class="toast-body"></div>
	</div>

	<?php echo flixcdn_js(array(
		'/flixcdn/admin/assets/js/jquery.min.js',
		'/flixcdn/admin/assets/js/jquery-ui.min.js',
		'/flixcdn/admin/assets/js/jquery.autocomplete.js',
		'/flixcdn/admin/assets/js/bootstrap.bundle.min.js',
		'/flixcdn/admin/assets/js/chosen.min.js',
	)); ?>

	<script>
		<!--
			
			var baseUrl = '<?php echo $baseUrl; ?>';
			var updateType = <?php echo (intval($flixcdn->config['update']['type']) ? 1 : 0); ?>;

			var qualities = <?php echo json_encode($qualities); ?>;
			var translations = <?php $_translations = array(); foreach ($translations as $translation) { $_translations[] = $translation['title']; } echo json_encode($_translations); ?>;

		//-->
	</script>

	<?php echo flixcdn_js('/flixcdn/admin/assets/js/app.js?v=6'); ?>

</body>
</html>