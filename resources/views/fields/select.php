<select name="<?php echo $name; ?>" class="">
	<?php
	$placeholder = $placeholder ?? '-- Select Option --';
	echo '<option value="-1">' . $placeholder . '</option>';

	if ( ! empty( $options ) ) {
		foreach ($options as $key => $label) {
			echo '<option value="' . $key . '"' . ($key == $value ? 'selected' : '') . '>' . $label . '</option>';
		}
	}
	?>
</select>
<?php if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
}
