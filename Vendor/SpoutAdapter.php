<?php
use Box\Spout\Writer;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;


class SpoutAdapter
{
    /**
     * Renders an Excel spreadsheet.
     *
     * @param array $reportData
     * @param array $fieldList
     * @param array $fieldsType
     * @throws Writer\Exception\WriterNotOpenedException
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public function buildXls(&$reportData = array(), &$fieldList = array(), &$fieldsType = array())
    {
        /** @var Writer\WriterInterface $writer */
        $writer = WriterFactory::create(Type::XLSX);
        $writer->setTempFolder(sys_get_temp_dir());
        $writer->openToBrowser('adhoc.xlsx');

        $titleRow = [];
        foreach ($fieldList as $field) {
            $displayField = substr($field, strpos($field, '.') + 1);
            $displayField = str_replace('_', ' ', $displayField);
            $displayField = ucfirst($displayField);
            $titleRow[] = $displayField;
        }
        $writer->addRow($titleRow);

        if (!empty($reportData)) {
            foreach ($reportData as $reportItem) {
                $dataRow = [];
                foreach ($fieldList as $field) {
                    $params = explode('.', $field);
                    $dataRow[] = $reportItem[$params[0]][$params[1]];
                }
                $writer->addRow($dataRow);
            }
        }
        $writer->close();
    }
}