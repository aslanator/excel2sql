<?php


namespace Excel2sql\Admin;

use \Bitrix\Main\Localization\Loc;
use CAdminContextMenu;
use CAdminTabControl;
use CModule;
use Bitrix\Main\Application;
use Excel2sql\Excel2sqlTable;
use Excel2sql\Excel2sqlMustache;
use Excel2sql\Excel2sqlCreateTable;


Loc::loadMessages(__FILE__);

class Excel2SqAdd {

    /**
     * @var CAdminContextMenu
     */
    protected $contextMenu = null;

    /**
     * @var string
     */
    protected $content = "";

    /**
     * @var array
     */
    protected $errors = [];


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

        $this->doAction();
    }

    /**
     * add documents or initialize view
     */
    protected function doAction(){
        $request = Application::getInstance()->getContext()->getRequest();
        $files = $request->getFile("documents");

        if((strlen($_POST['save']) > 0 ||  strlen($_POST['apply']) > 0)
            && check_bitrix_sessid()
            && strlen($files['name'][0]) > 0)
        {
            $this->createTablesAndOrm($files);
        }
        else{
            $this->initView();
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function checkExists(string $name){
        global $DB;
        $name = Excel2sqlCreateTable::getTableNameFriendlyValue($name);
        $exists = $DB->TableExists($name);
        if($exists === false){
            $exists = file_exists($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/excel2sql/classes/$name.php");
        }
        return $exists;
    }


    /**
     * create tables and orm from documents
     * @param array $files
     */
    protected function createTablesAndOrm(Array $files){
        $ormDataList = [];
        foreach($files['name'] as $key => $name){
            if($filePath = $files['tmp_name'][$key]){
                if(!$this->checkExists($name) || true){
                    try{
                        $table  = new Excel2sqlCreateTable($name, $filePath);
                    }
                    catch (\Exception $exception){
                        $this->errors[] = $exception->getMessage();
                    }
                    if(count($this->errors) > 0 || !isset($table))
                        break;
                    $ormDataList[$table->getTableName()] = $table->getDataForOrm();
                    $this->addRow($name);
                }
                else{
                    $this->errors[] = loc::getMessage('TABLE ALREADY EXISTS', ['{TABLE}' => $name]);
                }
            }
        }
        if(count($ormDataList) > 0 && count($this->errors) === 0){
            $this->createORM($ormDataList);
        }
    }

    protected function createORM($ormDataList){
        $ormDataList = $this->createReferenceORMData($ormDataList);
        $mustache = new Excel2sqlMustache();
        foreach($ormDataList as $ormData){
            $render = $mustache->render("excel2sqlORM", $ormData);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/excel2sql/lib/classes/{$ormData['table_name']}.php", $render);
        }
    }

    protected function addRow($tableName){
        $tableName = Excel2sqlCreateTable::getTableNameFriendlyValue($tableName);
        $path =  "/bitrix/modules/excel2sql/lib/classes/$tableName.php";

        $newTable = Excel2sqlTable::createObject();
        $newTable->set('TABLE_NAME', $tableName);
        $newTable->set('ORM_PATH', $path);
        $newTable->save();
        $this->content = Loc::GetMessage("SUCCESS");
    }

    /**
     * Initialize all data to display table
     */
    protected function initView(){
        global $APPLICATION;

        $APPLICATION->SetTitle(Loc::getMessage('EXCEL2SQL_ADD_DOCUMENTS'));

        $this->setContextMenu();
        $this->setForm();
    }

    /**
     * set data to display context menu (back button)
     */
    protected function setContextMenu(){

        $aMenu = array(
            array(
                "TEXT"	=> Loc::getMessage("GO_BACK"),
                "LINK"	=> "/bitrix/admin/excel2sqlIndex.php?lang=".LANGUAGE_ID,
                "TITLE"	=>  Loc::getMessage("GO_BACK"),
                "ICON"	=> "btn_list"
            )
        );

        $this->contextMenu = new CAdminContextMenu($aMenu);
    }

    /**
     * add reference fields to ORM data
     * @param $ormDataList
     * @return mixed
     */
    protected function createReferenceORMData($ormDataList){
        foreach($ormDataList as $ormData){
            foreach($ormData['reference'] as $reference){
                $referenceName = $reference['many'];
                if($referenceName && isset($ormDataList[$referenceName]) && $ormDataList[$referenceName]['have_id'] === 'Y'){
                    $ormDataList[$referenceName]['one_to_many'][] = ['one' => $ormData['table_name'], 'many' => $ormDataList[$referenceName]['table_name']];
                }
            }

        }
        return $ormDataList;
    }

    /**
     *  set data to display main form
     */
    protected function setForm(){
        global $APPLICATION;
        $aTabs = array(
            array("DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_TAB"), "ICON" => "", "TITLE" => Loc::getMessage("MAIN_TAB")),
        );
        $tabControl = new CAdminTabControl("tabControl", $aTabs);

        ob_start();
        ?>
        <form method="POST" action="<?= $APPLICATION->GetCurPage()?>" name="bform" enctype="multipart/form-data">
            <?php
            $tabControl->Begin();
            $tabControl->BeginNextTab();
            echo bitrix_sessid_post();
            ?>
            <tr class="adm-detail-required-field">
                <td width="40%"><?=Loc::getMessage('ADD_DOCUMENT');?></td>
                <td width="60%">
                    <input type="file" multiple="multiple" name="documents[]" accept=".xls, .xlsx">
                </td>
            </tr>
            <?php
            $tabControl->Buttons(array( "back_url"=>"excel2sqlIndex.php?lang=".LANGUAGE_ID));
            $tabControl->End();
            ?>
        </form>
        <?

        $this->content = ob_get_clean();
    }


    public function display(){
        if(count($this->errors) > 0){
            echo join('<br>', $this->errors);
        }
        else{
            if($this->contextMenu instanceof  CAdminContextMenu)
                $this->contextMenu->Show();
            echo $this->content;
        }
    }

}
