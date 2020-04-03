<?php
global $wp_settings_sections;

$setting_tabs = array_keys( $wp_settings_sections );
$current_tab  = $_GET['tab'] ?? $setting_tabs[0];
?>

<style type="text/css">
	[x-cloak] { display: none; }
	.derma-settings h2 { margin-top: 2em; }
</style>

<script type="text/javascript">
window.changeTab = function( tab, url, $el ) {
	window.history.pushState({tab: tab}, document.title, url);
	$el.querySelector('[name=_wp_http_referer]').value = url;
}
// handle back button
window.onpopstate = function(e){
	var current_tab = '<?php echo $current_tab; ?>';
	if(e.state && e.state.tab) {
		current_tab = e.state.tab;
	}
	document.querySelector('div.derma-settings').__x.$data["tab"] = current_tab;
};

</script>

<div class="wrap derma-settings" x-cloak x-data='{
	tab: "<?php echo $current_tab; ?>",
	settings: <?php echo json_encode( get_option( $option_name, [] ) ); ?>
}'>
	<h1><?php _e( 'Settings', 'derma' ); ?></h1>

	<div class="nav-tab-wrapper">
	<?php foreach ( $setting_tabs as $key ) { ?>
		<a
			href="<?php echo add_query_arg( 'tab', $key, $_SERVER['REQUEST_URI'] ); ?>"
			class="nav-tab"
			:class="{ 'nav-tab-active': tab === '<?php echo $key; ?>' }"
			x-on:click.prevent="
				tab = '<?php echo $key; ?>';
				changeTab(tab, $event.target.getAttribute('href'), $el);
			"
		>
			<?php echo ucwords( str_replace( '-', ' ', $key ) ); ?>
		</a>
	<?php } ?>
	</div>

	<form action="<?php echo admin_url( 'options.php' ); ?>" method="POST">
		<?php settings_fields( $page_name ); ?>

		<?php foreach ( $setting_tabs as $tab ): ?>
		<div x-show="tab === '<?php echo $tab; ?>'">
			<?php do_settings_sections( $tab ); ?>
		</div>
		<?php endforeach; ?>

		<?php submit_button(); ?>
	</form>

</div>
