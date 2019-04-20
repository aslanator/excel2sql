<?php

IncludeModuleLangFile(__FILE__);
if (class_exists("excel2sql"))
    return;

class excel2sql extends CModule
{
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
        RegisterModule("excel2sql");
        return true;
    }

    function UnInstallDB($arParams = array())
    {
        UnRegisterModule("excel2sql");
        return true;
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
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/excel2sql/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
        return true;
    }

    function UnInstallFiles()
    {
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
            }
        }
    }

    function DoUninstall()
    {
        global $USER, $APPLICATION, $step;
        if ($USER->IsAdmin())
        {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $this->UnInstallFiles();
        }
    }
}
