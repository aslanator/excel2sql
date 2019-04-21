<?php
namespace Excel2sql;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Result;


/**
 * Class Excel2sqlTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SORT int optional default 500
 * <li> TABLE_NAME string mandatory
 * <li> ORM_PATH text mandatory
 * </ul>
 *
 * @package \Brainkit\Data
 **/

class Excel2sqlTable extends Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'excel2sql_class_list';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('EXCEL2SQL_ENTITY_ID_FIELD'),
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('EXCEL2SQL_ENTITY_SORT_FIELD'),
            ),
            'TABLE_NAME' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('EXCEL2SQL_ENTITY_TABLE_NAME_FIELD'),
            ),
            'ORM_PATH' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('EXCEL2SQL_ENTITY_ORM_PATH_FIELD'),
            ),
        );
    }


    public static function getAll(Array $sort):Result {
        $query = Excel2sqlTable::getQuery();
        $query->setSelect(["*"]);
        $query->setOrder($sort);
        return $query->exec();
    }

    /**
     * @return Entity\Query
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getQuery(){
        return new Entity\Query(Excel2sqlTable::getEntity());
    }
}
