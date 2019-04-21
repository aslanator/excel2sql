<?php


namespace Excel2sql\Admin;

use \Bitrix\Main\Localization\Loc;
use CModule;
use CUserOptions;
use CUtil;
use Excel2sql\Excel2sqlTable;
use CAdminSorting;
use CAdminList;
use CAdminResult;
use CLang;


Loc::loadMessages(__FILE__);
loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/excel2sql/lib/excel2sqlTable.php');

class Excel2SqIndex {


    const DEFAULT_PAGE_SIZE = 20;


    /**
     * @var string
     */
    protected $sTableID = "excel2sql_class_list";

    /**
     * Object for work with result list in admin page
     * @var \CAdminList
     */
    protected $lAdmin = null;

    public function __construct()
    {
        global $APPLICATION, $USER;

        if (!$USER->IsAdmin())
        {
            $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
        }

        if (!CModule::IncludeModule('excel2sql'))
        {
            $APPLICATION->AuthForm(Loc::getMessage("MODULE_EXCEL2SQL_NOT_INSTALLED"));
        }

        $this->initView();
    }

    protected function initView(){
        global $APPLICATION;

        $APPLICATION->SetTitle(Loc::getMessage('EXCEL2SQL_ADMIN_MENU_TITLE'));

        $this->setPageSort();
        $oSort = new CAdminSorting($this->sTableID, false, false);
        $this->lAdmin = new CAdminList($this->sTableID, $oSort);

        $this->showContextMenu();
        $this->showList();
        $this->lAdmin->CheckListMode();
    }

    protected function showContextMenu(){
        $aContext = array(
            array(
                "TEXT"	=> Loc::getMessage("ADD_EXCEL_DOCUMENTS"),
                "LINK"	=> "site_edit.php?lang=".LANGUAGE_ID,
                "TITLE"	=> Loc::getMessage("ADD_EXCEL_DOCUMENTS"),
                "ICON"	=> "btn_new"
            ),
        );

        $this->lAdmin->AddAdminContextMenu($aContext);

    }

    /**
     * Set session page sort for CAdminSorting
     */
    protected function setPageSort(){
        global $APPLICATION;

        $uniq = md5($APPLICATION->GetCurPage());
        $sort = $this->getSort();
        $by = array_key_first($sort);
        $order = $sort[0];

        $_SESSION["SESS_SORT_BY"][$uniq] = $by;
        $_SESSION["SESS_SORT_ORDER"][$uniq] = $order;
    }

    protected function getSort(){
        global $by, $order;
        $options = CUserOptions::GetOption("list", $this->sTableID);

        if($_REQUEST["mode"] !== "list"){
            $by = $options['by'] ?: $by;
            $order = $options['order'] ?: $order;
        }
        else{
            $by = $by ?: $options['by'];
            $order = $order ?: $options['order'];
        }

        $by = strtoupper($by);

        return [$by => $order];
    }

    protected function getPageSize(){
        $options = CUserOptions::GetOption("list", $this->sTableID);
        return intval($options['page_size']) > 0 ? intval($options['page_size']) : self::DEFAULT_PAGE_SIZE;
    }

    protected function showList(){
        $sort = $this->getSort();

        $classList = Excel2sqlTable::getAll($sort);

        $this->lAdmin->AddHeaders(array(
            array("id"=>"ID", "content"=>Loc::GetMessage("EXCEL2SQL_ENTITY_ID_FIELD"), "sort"=>"id", "default"=>true),
            array("id"=>"SORT","content"=>Loc::GetMessage("EXCEL2SQL_ENTITY_SORT_FIELD"), "sort"=>"sort", "default"=>true),
            array("id"=>"TABLE_NAME", "content"=>Loc::GetMessage("EXCEL2SQL_ENTITY_TABLE_NAME_FIELD"), "sort"=>"table_name", "default"=>true),
            array("id"=>"ORM_PATH", "content"=>Loc::GetMessage("EXCEL2SQL_ENTITY_ORM_PATH_FIELD"), "sort"=>"orm_path", "default"=>true),
        ));

        while($arClass = $classList->fetch())
        {
            $row =& $this->lAdmin->AddRow($arClass['ID'], $arClass['ID']);
            $row->AddViewField("ID", $arClass['ID']);
            $row->AddViewField("SORT", $arClass['SORT']);
            $row->AddViewField("TABLE_NAME", $arClass['TABLE_NAME']);
            $row->AddViewField("ORM_PATH", $arClass['ORM_PATH']);
        }

    }

    public function display(){
        $this->lAdmin->DisplayList();
    }

}

//$lAdmin->AddGroupActionTable(Array(
//    "delete"=>true,
//));