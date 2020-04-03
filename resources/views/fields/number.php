<input
  type="number"
  name="<?php echo $name; ?>"
  id="<?php echo $id; ?>"
  value="<?php echo $value; ?>"
  class="<?php echo $class; ?>"
>
<?php if ( isset( $description ) ) {
	echo '<p class="description">' . $description . '</p>';
} ?>
