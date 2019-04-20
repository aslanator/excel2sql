<?php
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('excel2sql'))
{
    return false;
}

return array(
    'parent_menu' => 'global_menu_content',
    'section' => 'content',
    'sort' => 1,
    'text' => Loc::getMessage('EXCEL2SQL_ADMIN_MENU_TITLE'),
    'url' => $USER->isAdmin() ? 'highloadblock_index.php?lang='.LANGUAGE_ID : '',
    'icon' => 'highloadblock_menu_icon',
    'page_icon' => 'highloadblock_page_icon',
    'more_url' => array(
        'highloadblock_entity_edit.php',
        'highloadblock_rows_list.php',
        'highloadblock_row_edit.php'
    ),
    'items_id' => 'menu_highloadblock',
    'items' => $items
);