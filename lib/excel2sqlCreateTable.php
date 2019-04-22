<?php

namespace Excel2sql;

use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel2sqlCreateTable {


    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null
     */
    protected $activeSheet = null;

    /**
     * @var array|null
     */
    protected $arFields = null;


    /**
     * @var array|null
     */
    protected $arCollNames = null;


    /**
     * @var string
     */
    protected $tableName = "";

    /**
     * Excel2sqlCreateTable constructor.
     * @param $name
     * @param $filePath
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct($name, $filePath)
    {
        $spreadSheet = IOFactory::load($filePath);
        $this->activeSheet = $spreadSheet->getActiveSheet();
        $this->tableName = $this->getTableNameFriendlyValue($name);
        $this->addFields();
        $this->createTable();
    }

    /**
     * fill $arFields and $arCollNames class property, that uses in getSql
     */
    protected function addFields() {
        $rows = $this->activeSheet->getRowIterator();

        foreach($rows as $row){
            $this->addRow($row);
        }
    }

    /**
     * @param Row $row
     */
    protected function addRow(Row $row) {
        $cells = $row->getCellIterator();
        $rowIndex = $row->getRowIndex();
        foreach($cells as $cellIndex => $cell){
            if($rowIndex === 1){
                $columnName = $cell->getFormattedValue();
                $this->arCollNames[$cellIndex] = $this->getTableNameFriendlyValue($columnName);
            }
            else{
                $this->arFields[$rowIndex][] = $cell->getFormattedValue();
            }
        }
    }

    /**
     * @param string
     * @return string
     */
    public static function getTableNameFriendlyValue($name):string{
        global $DB;
        if(!mb_check_encoding($name, 'ASCII')){
            $name = \Cutil::translit($name,"ru");
        }
        if(!mb_check_encoding($name, 'ASCII')){
            throw new \Exception("Field $name can't be table column name");
        }
        $name = preg_replace('#\..*#', '', $name);
        $name = preg_replace("/[^A-Za-z0-9%_]+/i", "", $name);
        $name = Trim($name);
        $name = $DB->ForSql($name);
        return substr($name, 0, 64);
    }

    /**
     * Make wrapped string from array
     * @param array $array
     * @param string $wrapLeft
     * @param string $wrapRight
     * @return string
     */
    protected function wrap(array $array, string $wrapLeft, string $wrapRight):string {
        global $DB;
        return array_reduce($array, function($carry, $item) use ($DB, $wrapLeft, $wrapRight){
            $carry = $carry ? $carry . ', ' : "";
            return $carry . $wrapLeft . $DB->ForSql($item) . $wrapRight;
        }, false);

    }

    /**
     * Get array of sql commands, which needed to create table with data
     * @return array
     */
    public function getArSql(): array{
        global $DB;
        $tableName = $this->tableName;
        $arSql = [];
        $insertCollNames = $this->wrap($this->arCollNames, "`", "`");
        $createCollNames = $this->wrap($this->arCollNames, '`', '` TEXT NULL');


        $arSql[] = "CREATE TABLE IF NOT EXISTS `$tableName` ( $createCollNames ) ENGINE = InnoDB;";
        foreach($this->arFields as $arValues){
            $values = $this->wrap($arValues, "'", "'");
            $arSql[] = " INSERT INTO `$tableName` ($insertCollNames) VALUES ($values)";
        }
        return $arSql;
    }


    protected function createTable(){
        global $DB;
        $arSql = $this->getArSql();
        $DB->StartTransaction();
        foreach($arSql as $sql){
            $this->query($sql);
        }
        $DB->Commit();
    }

    /**
     * @param string
     */
    protected function query(string $value){
        global $DB, $APPLICATION;
        try{
            $DB->Query($value);
        }
        catch (\Exception $exception){
            $APPLICATION->ThrowException(implode("<br>", [$DB->GetErrorMessage()]));
        }
    }

    public function getDataForOrm(){
        $haveID = 'N';
        $collNames = [];
        foreach($this->arCollNames as $name){
            $collNames[] = $name;
            if($name === 'ID'){
                $haveID = 'Y';
            }
            if(preg_match('#(.*)_ID$#', $name, $match)){
                $reference = $match[1];
            }
        }
        $ormData = ['table_name' => $this->tableName, 'fields' => $collNames, 'have_id' => $haveID];
        if(isset($reference)){
            $ormData['reference_table_name'] = $reference;
            $ormData['reference_field'] = $reference . "_ID";
        }
        return $ormData;
    }

    public function getTableName(){
        return $this->tableName;
    }

}