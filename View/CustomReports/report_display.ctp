<?php
/**
 * Copyright (c) 2013 TribeHR Corp - http://tribehr.com
 * Copyright (c) 2012 Luis E. S. Dias - www.smartbyte.com.br
 * 
 * Licensed under The MIT License. See LICENSE file for details.
 * Redistributions of files must retain the above copyright notice.
 */
?>
<h1><?php echo ($reportName == '' ? 'Report Manager' : $reportName);?></h1>
<div id="reportManagerDisplay">
    <?php 
    $counter = 0;
    $columns = 0;
    $floatFields = array();
    ?>     
    <?php if (!empty($reportData)):?>
    <table cellpadding = "0" cellspacing = "0" class="report" width="<?php echo $tableWidth;?>">
        <colgroup>
            <?php foreach ($tableColumnWidth as $field => $width): ?>
            <col width="<?php echo $width;?>">
            <?php endforeach; ?>                    
        </colgroup>        
        <tr class="header">
                <?php foreach ($fieldList as $field): ?>
                <th>
                <?php
                $columns++;
                $displayField = substr($field, strpos($field, '.')+1);
                $displayField = str_replace('_', ' ', $displayField);
                $displayField = ucfirst($displayField);
                echo $displayField; 
                if ( $fieldsType[$field] == 'float') // init array for float fields sum
                    $floatFields[$field] = 0;
                ?>
                </th>
                <?php endforeach; ?>
        </tr>
        <?php 
        $i = 0;        
        foreach ($reportData as $reportItem): 
            $counter++;
            $class = null;
            if ($i++ % 2 == 0) {
                $class = ' altrow';
            } 
        ?>
            <tr class="body<?php echo $class;?>">
                <?php foreach ($fieldList as $field): ?>
                    <td>
                    <?php                     
                    $params = explode('.',$field);
                    if ( $fieldsType[$field] == 'float') {
                        echo $this->element('format_float',array('f'=>$reportItem[$params[0]][$params[1]]));
                        $floatFields[$field] += $reportItem[$params[0]][$params[1]];
                    }                        
                    else
                        echo $reportItem[$params[0]][$params[1]]; 
                    ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ( count($floatFields)>0 ) { ?>
            <tr class="footer">
                <?php foreach ($fieldList as $field): ?>
                <td>
                <?php
                if ( $fieldsType[$field] == 'float') 
                    echo $this->element('format_float',array('f'=>$floatFields[$field]));
                ?>
                </td>
                <?php endforeach; ?>
            </tr>
         <?php } ?>
    </table>
    <?php if ( $showRecordCounter ) { ?>    
        <div class="counter">Count: <?php echo $counter;?></div>
    <?php } ?>
    <div class="timestamp"><?php echo __('Report Generated') . ' : ' . date('Y-m-d H:i:s'); ?></div>
    <?php endif; ?>
</div>