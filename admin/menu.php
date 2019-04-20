<?php
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('excel2sql'))
{
    return false;
}

return array(
    'parent_menu' => 'global_menu_content',
    'section' => 'excel2sql',
    'sort' => 1,
    'text' => Loc::getMessage('EXCEL2SQL_ADMIN_MENU_TITLE'),
    'url' => $USER->isAdmin() ? 'excel2sql_index.php?lang='.LANGUAGE_ID : '',
    'icon' => 'excel2sql_menu_icon',
    'page_icon' => 'excel2sql_menu_icon',
    'items_id' => 'menu_excel2sql',
);