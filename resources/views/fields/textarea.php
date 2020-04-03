<p><textarea name="<?php echo $name; ?>" id="<?php echo $id; ?>" rows="10" cols="50"><?php echo $value; ?></textarea></p>
<?php if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
} ?>
