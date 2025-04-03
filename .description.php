<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
    'NAME' => Loc::getMessage('SALE_HPS_INVOICE_TITLE'),
    'SORT' => 100,
    'CODES' => array(
        // Банковские реквизиты
        'BANK_NAME' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_BANK_NAME'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_BANK_NAME_DESC'),
            'SORT' => 100,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'АО "Банк ЦентрКредит"',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'BANK_BIK' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_BIK'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_BIK_DESC'),
            'SORT' => 110,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'KCJBKZKX',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'BANK_ACCOUNT' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_ACCOUNT_NUMBER'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_ACCOUNT_NUMBER_DESC'),
            'SORT' => 120,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'KZ428560000000425746',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'BANK_KBE' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_KBE'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_KBE_DESC'),
            'SORT' => 130,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => '17',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'BANK_CODE' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_CODE'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_CODE_DESC'),
            'SORT' => 140,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => '710',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'PAYMENT_PURPOSE' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_PAYMENT_PURPOSE_CODE'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_PAYMENT_PURPOSE_CODE_DESC'),
            'SORT' => 150,
            'GROUP' => 'BANK_DETAILS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'ЗК25030AУТ0021240001',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),

        // Данные компании
        'COMPANY_NAME' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_NAME'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_NAME_DESC'),
            'SORT' => 200,
            'GROUP' => 'SELLER_COMPANY',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'ТОО Asian Medical Depo Group',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'COMPANY_INN' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_INN'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_INN_DESC'),
            'SORT' => 210,
            'GROUP' => 'SELLER_COMPANY',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => '070940017485',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'COMPANY_ADDRESS' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_ADDRESS'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_ADDRESS_DESC'),
            'SORT' => 220,
            'GROUP' => 'SELLER_COMPANY',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'Республика Казахстан, г.Алматы, ул.Богенбай батыра, дом № 149',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'COMPANY_PHONE' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_PHONE'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_PHONE_DESC'),
            'SORT' => 230,
            'GROUP' => 'SELLER_COMPANY',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => '+7 (727) 313-15-23, доб. 110',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'COMPANY_MANAGER' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_MANAGER'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_COMPANY_MANAGER_DESC'),
            'SORT' => 240,
            'GROUP' => 'SELLER_COMPANY'
        ),

        // Общие настройки
        'VALIDITY_DAYS' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_VALIDITY_DAYS'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_VALIDITY_DAYS_DESC'),
            'SORT' => 300,
            'GROUP' => 'GENERAL_SETTINGS',
            'DEFAULT' => array(
                'PROVIDER_VALUE' => '30',
                'PROVIDER_KEY' => 'VALUE'
            )
        ),
        'LOGO_FILE' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_LOGO_FILE'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_LOGO_FILE_DESC'),
            'SORT' => 310,
            'GROUP' => 'GENERAL_SETTINGS',
            'INPUT' => array(
                'TYPE' => 'FILE'
            )
        ),
        'SHOW_LOGO' => array(
            'NAME' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_SHOW_LOGO'),
            'DESCRIPTION' => Loc::getMessage('SALE_HPS_INVOICE_HANDLER_SHOW_LOGO_DESC'),
            'SORT' => 320,
            'GROUP' => 'GENERAL_SETTINGS',
            'INPUT' => array(
                'TYPE' => 'Y/N'
            ),
            'DEFAULT' => array(
                'PROVIDER_VALUE' => 'Y',
                'PROVIDER_KEY' => 'INPUT'
            )
        )
    )
);
?>
