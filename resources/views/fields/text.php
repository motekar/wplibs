<input
  type="text"
  name="<?php echo $name; ?>"
  id="<?php echo $id; ?>"
  value="<?php echo $value; ?>"
  class="regular-text"
>
<?php if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
} ?>
