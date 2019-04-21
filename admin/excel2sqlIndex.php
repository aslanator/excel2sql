<?php
namespace Excel2sql\Admin;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/excel2sql/admin/classes/excel2sqlIndex.php");

$excel2sqlIndexPage = new Excel2SqIndex();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$excel2sqlIndexPage->display();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

