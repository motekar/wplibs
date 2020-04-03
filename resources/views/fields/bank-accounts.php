<ul>
	<template x-if="settings.<?php echo $raw_name; ?>" x-for="(item, index) in settings.<?php echo $raw_name; ?>" :key="item">
		<li>
			<input type="text" :name="'<?php echo $name; ?>[' + index + '][bank]'" x-model="item.bank">
			<input type="text" :name="'<?php echo $name; ?>[' + index + '][details]'" x-model="item.details">
			<button class="button" @click.prevent="settings.<?php echo $raw_name; ?>.splice(index, 1)">-</button>
		</li>
	</template>
</ul>
<button class="button" @click.prevent="
	if ( ! settings.<?php echo $raw_name; ?> ) settings.<?php echo $raw_name; ?> = [];
	settings.<?php echo $raw_name; ?>.push({});
">Add</button>
