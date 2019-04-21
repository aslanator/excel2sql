<?php

IncludeModuleLangFile(__FILE__);

if (class_exists("excel2sql"))
    return;

class excel2sql extends CModule
{
    public $errors;

    var $MODULE_ID = "excel2sql";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "N";

    function excel2sql()
    {
        $arModuleVersion = array();
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("EXCEL2SQL_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("EXCEL2SQL_MODULE_DESCRIPTION");
    }

    function InstallDB($arParams = array())
    {
        return ($this->executeDB('install.sql'));
    }

    function UnInstallDB($arParams = array())
    {
        return ($this->executeDB('uninstall.sql'));
    }

    /**
     * Execute sql file from /install/db/
     * @param String $file
     * @return bool
     */
    function executeDB(String $file)
    {
        global $DB, $APPLICATION;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/excel2sql/install/db/mysql/" . $file);
        if (!$this->errors) {
            return true;
        } else {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
        DeleteDirFilesEx("/bitrix/themes/.default/icons/excel2sql/");
        return true;
    }

    function DoInstall()
    {
        global $USER, $APPLICATION;
        if ($USER->IsAdmin())
        {
            if ($this->InstallDB())
            {
                $this->InstallEvents();
                $this->InstallFiles();
                RegisterModule("excel2sql");
            }
        }
        $GLOBALS["errors"] = $this->errors;
        $APPLICATION->IncludeAdminFile(GetMessage("EXCEL2SQL_MODULE_NAME"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/step.php");
    }

    function DoUninstall()
    {
        global $USER, $APPLICATION;
        if ($USER->IsAdmin())
        {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $this->UnInstallFiles();
            UnRegisterModule("excel2sql");
        }
    }
}
