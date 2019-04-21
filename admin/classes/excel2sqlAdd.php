<?php


namespace Excel2sql\Admin;

use \Bitrix\Main\Localization\Loc;
use CAdminContextMenu;
use CAdminTabControl;
use CModule;
use Bitrix\Main\Application;
use Excel2sql\excel2sqlCreateTable;


Loc::loadMessages(__FILE__);

class Excel2SqAdd {

    /**
     * @var CAdminContextMenu
     */
    protected $contextMenu = null;

    /**
     * @var string
     */
    protected $form = "";


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
     * @throws \Bitrix\Main\SystemException
     */
    protected function doAction(){
        $request = Application::getInstance()->getContext()->getRequest();
        $files = $request->getFile("documents");

        if((strlen($_POST['save']) > 0 ||  strlen($_POST['apply']) > 0)
            && check_bitrix_sessid()
            && strlen($files['name'][0]) > 0)
        {
            $this->addDocuments($files);
        }
        else{
            $this->initView();
        }
    }

    protected function addDocuments(Array $files){
        foreach($files['name'] as $key => $name){
            if($filePath = $files['tmp_name'][$ket]){
                $createTable = new excel2sqlCreateTable($name, $filePath);
            }
        }
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

        $this->form = ob_get_clean();
    }


    public function display(){
        $this->contextMenu->Show();
        echo $this->form;
    }

}
