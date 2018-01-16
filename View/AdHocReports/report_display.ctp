<?php
/**
 * Copyright (c) 2013 TribeHR Corp - http://tribehr.com
 * Copyright (c) 2012 Luis E. S. Dias - www.smartbyte.com.br
 *
 * Licensed under The MIT License. See LICENSE file for details.
 * Redistributions of files must retain the above copyright notice.
 */
echo $this->Html->script(array('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'));
?>

<div class="reportDetails">
	<?php
	// @todo - move this TribeHR-specific logo image out of this plugin
	?>
	<?php echo $this->Html->image('tribehr_logo.png', array('class'=>'logo')); ?>
	<h1><?php echo ($reportName == '' ? 'Ad-Hoc Report' : h($reportName));?></h1>
	<h2><?php echo h($settings['Config']['name']); ?></h2>
	<div class="timestamp">Report Generated : <strong><?php echo date('Y-m-d H:i:s'); ?></strong></div>
</div>

<div class="reportTable">
	<?php
	$counter = 0;

	if (!empty($reportData)) { ?>
	<table cellpadding = "0" cellspacing = "0" class="report" width="<?php echo $tableWidth;?>">
		<tr class="header">
			<?php foreach ($fieldList as $field) {
				$displayField = substr($field, strpos($field, '.')+1);
				$displayField = str_replace('_', ' ', $displayField);
				$displayField = ucfirst($displayField);

				// column width is either the actual width (can cause issues for booleans), or 50, or (length * 8) to prevent overflow
				$columnWidth = max(array($tableColumnWidth[$field], 50, strlen($displayField) * 8));
			?>
				<th width="<?php echo $columnWidth; ?>"><?php echo $displayField; ?></th>
			<?php } ?>
		</tr>
		<?php
		foreach ($reportData as $reportItem) {
			$class = null;
			if ($counter++ % 2 == 0) {
				$class = ' altrow';
			}
		?>
			<tr class="body<?php echo $class;?>">
				<?php foreach ($fieldList as $field) {
					$params = explode('.',$field);
					$value = h($reportItem[$params[0]][$params[1]]);
				?>
					<td><?php echo $value; ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</table>
	<?php if ( $showRecordCounter ) { ?>
		<div class="counter">Total Records: <?php echo $counter;?></div>
	<?php } ?>
	<div class="timestamp"><?php echo __('Report Generated') . ' : ' . date('Y-m-d H:i:s'); ?></div>
	<?php } ?>
</div>

<script>
	$(function() {
		// toggle text wrapping on hover to see hidden overflown content
		$('tr.body td').hover(
			function() { $( this ).css('white-space', 'normal'); },
			function() { $( this ).css('white-space', 'nowrap'); }
		);
	});
</script>
