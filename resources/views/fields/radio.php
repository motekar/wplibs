<?php
if ( ! empty( $options ) ){
	foreach ($options as $key => $option){
		$option_value = empty( $value ) ? $default : $value;
		?>
        <p>
            <label>
                <input type="radio" name="<?php echo $name; ?>"  value="<?php echo $key ?>" <?php checked($option_value,  $key) ?> /> <?php echo $option ?>
            </label>
        </p>
		<?php
	}
}

if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
}

