<?php
/**
 * Copyright (c) 2013 TribeHR Corp - http://tribehr.com
 * Copyright (c) 2013 Luis E. S. Dias - www.smartbyte.com.br
 * 
 * Based on an article from AppServ Open Project
 * http://www.appservnetwork.com/modules.php?name=News&file=article&sid=8
 *
 * Licensed under The MIT License. See LICENSE file for details.
 * Redistributions of files must retain the above copyright notice.
 *
 * Updated to use PHPExcel library instead of directly outputting
 * binary excel format.
 */
class Excel {

    public function sendHeaders()
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=Report.xlsx");
        header("Content-Transfer-Encoding: binary ");
    }

    /*
     * Renders an Excel spreadsheet.
     * refactored gently to allow an empty dataset to output an
     * empty Excel spreadsheet. If the input data is an empty array, then the output Excel will have
     * only the header row.
     *
     * @param array $reportData     A data array, constructed like a Cake find, with Models and fields.
     * @param array $fieldList      An array describing what fields should be included in each row, expressed as "Model.fieldName"
     * @param array $fieldsType     An array of field types, e.g. "float" or "bool", keyed by "Model.fieldName"
     *
     * return null                  This function doesn't return anything; rather it echoes the Excel binary straight out to the buffer.
     */
    public function buildXls(&$reportData = array(), &$fieldList=array(), &$fieldsType=array()) {
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();

        $row = 1;
        $col = 0;

        foreach ($fieldList as $field) {
            $displayField = substr($field, strpos($field, '.')+1);
            $displayField = str_replace('_', ' ', $displayField);
            $displayField = ucfirst($displayField);

            $sheet->setCellValueExplicitByColumnAndRow($col, $row, $displayField,PHPExcel_Cell_DataType::TYPE_STRING);

            $col++;
        }
        $row++;

        if (!empty($reportData)) {
            foreach ($reportData as $reportItem) {
                $col = 0;
                foreach ($fieldList as $field) {
                    $params = explode('.', $field);
                    if ( $fieldsType[$field] == 'float') {
                        $sheet->setCellValueExplicitByColumnAndRow($col, $row, $reportItem[$params[0]][$params[1]], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValueExplicitByColumnAndRow($col, $row, $reportItem[$params[0]][$params[1]], PHPExcel_Cell_DataType::TYPE_STRING);
                    }

                    $col++;
                }
                $row++;
            }
        }

        $this->sendHeaders();
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
?>