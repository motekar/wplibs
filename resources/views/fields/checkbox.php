<?php
if ( empty( $options ) ) {
	$option_value = isset( $value ) ? $value : $default;
	$label_title = isset( $label_title ) ? $label_title : $label;
?>
	<label>
		<input type="checkbox" name="<?php echo $name; ?>" value="1" <?php checked( $option_value, '1' ); ?> />
		<?php echo $label_title; ?>
	</label>
<?php
} else {
	// Multiple checkboxes

	if ( ! is_array( $value ) ) {
		if ( is_string( $value ) ) {
			$value = [ $value => '1' ];
		} else {
			$value = [];
		}
	}

	foreach ( $options as $field_key => $field_label ) {
		$value[$field_key] = $value[$field_key] ?? '0'; // pre-fill value array
		?>
		<label>
			<input type="checkbox" name="<?php echo $name; ?>[<?php echo $field_key ?>]" value="1" <?php checked( $value[$field_key], '1' ) ?> />
			<?php echo $field_label; ?>
		</label>
		<br />
		<?php
	}
}

if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
}
