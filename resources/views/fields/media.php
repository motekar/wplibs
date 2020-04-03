<div class="option-media-wrap">
	<div class="option-media-preview">
		<?php if ( $value ) { ?>
			<img src="<?php echo wp_get_attachment_url( $value ); ?>" />
		<?php } ?>
	</div>

	<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">

	<div class="option-media-type-btn-wrap">
		<button class="button js-media-upload-btn">
			<?php echo $btn_text ?? 'Select or Upload'; ?>
		</button>

		<a href="#" class="button button-link-delete js-media-trash-btn" style="display: <?php echo $value? '' : 'none'; ?>;"><?php _e( 'Delete', 'derma' ); ?></a>
	</div>
</div>

<?php
// output JS once
if ( ! defined( 'DERMA_FIELD_MEDIA_JS' ) ):
	define( 'DERMA_FIELD_MEDIA_JS', true );
?>
<script type="text/javascript">
jQuery(function() {
	/**
	* Show media pop up
	*/
	jQuery(document).on('click', '.js-media-upload-btn', function(e){
		e.preventDefault();

		var $that = jQuery(this);
		var frame;
		if ( frame ) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: 'Select or Upload Media',
			button: { text: 'Use this media' },
			multiple: false
		});
		frame.on( 'select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$that.closest('.option-media-wrap').find('.option-media-preview').html('<img src="'+attachment.url+'" alt="" />');
			$that.closest('.option-media-wrap').find('input').val(attachment.id);
			$that.closest('.option-media-wrap').find('.js-media-trash-btn').show();
		});
		frame.open();
	});

	/**
	* Remove option media
	*/
	jQuery(document).on('click', '.js-media-trash-btn', function(e){
		e.preventDefault();

		var $that = jQuery(this);
		$that.closest('.option-media-wrap').find('img').remove();
		$that.closest('.option-media-wrap').find('input').val('');
		$that.closest('.option-media-wrap').find('.js-media-trash-btn').hide();
	});
});
</script>
<?php endif; ?>
