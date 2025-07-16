		<footer class="container">
			<p><b>CDNHub</b> v<?php echo $cdnhub->version(); ?></p>
		</footer>

	</div>

	<div id="Toast" class="toast hide vh-toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="1000">
		<div class="toast-header">
			<strong class="me-auto">CDNHub</strong>
			<button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close">
				<span aria-hidden="true"></span>
			</button>
		</div>
		<div class="toast-body"></div>
	</div>

	<?php echo cdnhub_js(array(
		'/cdnhub/admin/assets/js/jquery.min.js',
		'/cdnhub/admin/assets/js/jquery-ui.min.js',
		'/cdnhub/admin/assets/js/jquery.autocomplete.js',
		'/cdnhub/admin/assets/js/bootstrap.bundle.min.js',
		'/cdnhub/admin/assets/js/chosen.min.js',
	)); ?>

	<script>
		<!--
			
			var baseUrl = '<?php echo $baseUrl; ?>';
			var updateType = <?php echo (intval($cdnhub->config['update']['type']) ? 1 : 0); ?>;

			var qualities = <?php echo json_encode($qualities); ?>;
			var translations = <?php $_translations = array(); foreach ($translations as $translation) { $_translations[] = $translation['title']; } echo json_encode($_translations); ?>;

		//-->
	</script>

	<?php echo cdnhub_js('/cdnhub/admin/assets/js/app.js?v=6'); ?>

</body>
</html>