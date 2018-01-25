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

<span style="display: <?php echo $overflow ? 'block' : 'none'?>">
	<input type="checkbox" id="expandCollapseAll">
	<label for="expandCollapseAll" data-collapse="<?php echo $labels['collapse'];?>" data-expand="<?php echo $labels['expand'];?>">
		<?php echo $labels['expand'];?>
	</label>
</span>
<div class="reportTable">
	<table cellpadding = "0" cellspacing = "0" class="report" width="<?php echo array_sum($width);?>">
		<?php
			$rows = array(
				'head' => array('class' => 'header', 'colType' => 'th'),
				'body' => array('class' => 'body', 'colType' => 'td'),
			);
			foreach($data as $ridx => $rowData) {
				$row = $ridx ? $rows['body'] : $rows['head'];

				$rowClass = $row['class'] . ($ridx % 2 ? ' altrow' : '');
				echo sprintf('<tr class="%s">', $rowClass);

				foreach ($rowData as $cidx => $colData) {
					// width is defined for header cols only
					$colWidth = !$ridx ? sprintf(' width="%s"', $width[$cidx]) : '';
					echo "<{$row['colType']}{$colWidth}>{$colData}</{$row['colType']}>";
				}
				echo '</tr>';
			}
		?>
	</table>
	<?php if ( $showRecordCounter ) { ?>
		<div class="counter">Total Records: <?php echo $ridx; ?></div>
	<?php } ?>
	<div class="timestamp"><?php echo __('Report Generated') . ' : ' . date('Y-m-d H:i:s'); ?></div>
</div>

<script>
	$(function() {
		/* Logic related to expanding/collapsing columns with long, overflowing text */
		var expandCollapse = {
			checkbox: $('#expandCollapseAll'),
			label: $('label[for=expandCollapseAll'),
			cells: $('tr.body td'),

			/* Expand - don't hide the overflowing text*/
			expand: function(target) {
				target.css('white-space', 'normal');
			},
			/* Collapse - hide the overflowing text */
			collapse: function(target) {
				target.css('white-space', 'nowrap');
			},
			/* Enable/disable expand/collapse on mouse hover */
			hover: {
				on: function (target) {
					target.hover(
						function () { expandCollapse.expand($(this)); },
						function () { expandCollapse.collapse($(this)); },
					);
				},
				off: function (target) {
					target.off('hover');
				}
			},
			init: function() {
				/* Init expand/collapse ALL checkbox */
				expandCollapse.checkbox.change(function() {
					if ($(this).is(':checked')) {
						expandCollapse.label.text(expandCollapse.label.attr('data-collapse'));
						expandCollapse.expand(expandCollapse.cells);
						// when cells are expanded, prevent expand/collapse on mouse hover
						expandCollapse.hover.off(expandCollapse.cells);
					} else {
						expandCollapse.label.text(expandCollapse.label.attr('data-expand'));
						expandCollapse.collapse(expandCollapse.cells);
						// when cells are collapsed, enable expand/collapse on mouse hover
						expandCollapse.hover.on(expandCollapse.cells);
					}
				});

				/* Init expand/collapse on mouse hover */
				expandCollapse.hover.on(expandCollapse.cells);
			}
		};

		expandCollapse.init();
	});
</script>
