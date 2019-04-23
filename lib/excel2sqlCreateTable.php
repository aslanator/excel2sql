<?php

namespace Excel2sql;


use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\IntegerField;

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
    protected $arPrimary = null;

    /**
     * @var array|null
     */
    protected $arRows = null;


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
     * add excel row. If it first row, then it pushes to table fields
     * @param Row $row
     */
    protected function addRow(Row $row) {
        $cells = $row->getCellIterator();
        $rowIndex = $row->getRowIndex();
        foreach($cells as $cellIndex => $cell){
            if($rowIndex === 1){
                $columnName = $cell->getFormattedValue();
                $columnName = $this->getTableNameFriendlyValue($columnName);
                $this->addField($cellIndex, $columnName);
            }
            else{
                $field = $this->arFields[$cellIndex];
                $this->arRows[$rowIndex][$field->getColumnName()] = $cell->getFormattedValue();
            }
        }
    }


    /**
     * create Bitrix ORM fields
     * @param string $key
     * @param string $columnName
     * @throws \Bitrix\Main\SystemException
     */
    protected function addField(string $key, string $columnName){
        if($columnName === "ID"){
            $this->arFields[$key] = new IntegerField($columnName, ['primary' => true]);
            $this->arPrimary = [$key];
        }
        elseif(preg_match("/_ID$/", $columnName)){
            $this->arFields[$key] = new IntegerField($columnName);
        }
        else{
            $this->arFields[$key] = new TextField($columnName, ['default_value' => '123']);
        }
    }

    /**
     * @param $value
     * @return string
     */
    public static function prepareForSql($value){
        $connection = Application::getConnection();
        $sqlHelper =  $connection->getSqlHelper();
        return $sqlHelper->forSql($value);
    }

    /**
     * @param string
     * @return string
     */
    public static function getTableNameFriendlyValue($name):string{
        if(!mb_check_encoding($name, 'ASCII')){
            $name = \Cutil::translit($name,"ru");
        }
        if(!mb_check_encoding($name, 'ASCII')){
            throw new \Exception("Field $name can't be table column name");
        }
        $name = preg_replace('#\..*#', '', $name);
        $name = preg_replace("/[^A-Za-z0-9%_]+/i", "", $name);
        $name = Trim($name);
        $name = static::prepareForSql($name);
        if($name === 'table')
            $name = 'table_name'; //ORM не поддерживает имя tableTable
        return substr($name, 0, 64);
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected function createTable(){
        $connection = Application::getConnection();
        $connection->startTransaction();
        $connection->createTable(
            $this->tableName,
            $this->arFields,
            $this->arPrimary
        );
        foreach($this->arRows as $row){
            $connection->add($this->tableName, $row);
        }
        $connection->commitTransaction();
    }

    /**
     * Generate data used in mustache template to generate ORM for this table
     * @return array
     */
    public function getDataForOrm(){
        $haveID = 'N';
        $collNames = [];
        $ormData = ['reference' => []];
        foreach($this->arFields as $field){
            $name = $field->getColumnName();
            $type = $field instanceof TextField ? 'text' : 'integer';
            $primary = $field->isPrimary();
            $collNames[] = ['name' => $name, 'primary' => $primary, 'type' => $type];
            if($name === 'ID'){
                $haveID = 'Y';
            }
            if(preg_match('#(.*)_ID$#', $name, $match)){
                $reference = $match[1];
                $ormData['reference'][] = [
                    'many' => $reference,
                    'one' => $this->tableName,
                    'reference_field' => $reference . '_ID'
                ];
            }
        }
        $ormData['table_name'] = $this->tableName;
        $ormData['fields'] = $collNames;
        $ormData['have_id'] = $haveID;
        return $ormData;
    }

    /**
     * @return string
     */
    public function getTableName(){
        return $this->tableName;
    }

}