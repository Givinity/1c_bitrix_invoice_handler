<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type;
use Bitrix\Main\Type\Date;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Currency;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

require_once __DIR__.'/vendor/autoload.php';

class InvoiceHandler extends PaySystem\BaseServiceHandler
{
    private const UPLOAD_DIR = '/upload/invoices/';

    public function initiatePay(Payment $payment, Request $request = null)
    {
        $result = new PaySystem\ServiceResult();

        $params = $this->getParamsBusValue($payment);
        $params['PAYMENT_ID'] = $payment->getId();
        $params['SUM'] = $payment->getSum();
        $params['CURRENCY'] = $payment->getField('CURRENCY');
        $params['DATE_BILL'] = new Date();

        $dateUntil = new Date();
        $dateAdd = $this->getBusinessValue($order, 'VALIDITY_DAYS') ?: '30';
        $dateUntil->add($dateAdd . 'D');
        $params['DATE_BILL_UNTIL'] = $dateUntil;

        $order = $payment->getOrder();
        $params['ORDER_ID'] = $order->getId();
        $params['BASKET_ITEMS'] = $this->getBasketItems($order);
        $params['DELIVERY_PRICE'] = $this->getDeliveryPrice($order);
        $params['BUYER_INFO'] = $this->getBuyerInfo($order);
        $params['BANK_INFO'] = $this->getBankInfo();
        $params['COMPANY_INFO'] = $this->getCompanyInfo();

        $pdfContent = $this->generatePdf($params);

        if ($pdfContent) {
            $fileName = $this->saveInvoiceFile($payment, $pdfContent);
            if ($fileName) {
                $downloadUrl = $this->getDownloadUrl($fileName);

                $html = '
                    <div class="invoice-download-link">
                        <a href="' . htmlspecialcharsbx($downloadUrl) . '" class="btn" target="_blank">
                            Скачать счет (№' . $payment->getId() . ')
                        </a>
                    </div>
                ';

                // setTemplate() для буфера $arPaySystem['BUFFERED_OUTPUT']
                $result->setTemplate($html);

                $result->setData([
                    'PDF_CONTENT' => $pdfContent,
                    'INVOICE_NUMBER' => $params['PAYMENT_ID'],
                    'INVOICE_DATE' => $params['DATE_BILL']->format('d.m.Y'),
                    'DOWNLOAD_URL' => $downloadUrl,
                ]);
            } else {
                $result->addError(new PaySystem\Error('Error saving invoice file'));
            }
        } else {
            $result->addError(new PaySystem\Error(Loc::getMessage('SALE_HANDLERS_INVOICE_HANDLER_ERROR_PDF_GENERATION')));
        }

        return $result;
    }

    private function saveInvoiceFile(Payment $payment, $pdfContent)
    {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR;
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'invoice_' . $payment->getId() . '_' . time() . '.pdf';
        $filePath = $uploadDir . $fileName;

        if (file_put_contents($filePath, $pdfContent) !== false) {
            return $fileName;
        }
        return false;
    }

    private function getDownloadUrl($fileName)
    {
        return self::UPLOAD_DIR . $fileName;
    }

    private function getBasketItems(Sale\Order $order)
    {
        $result = [];
        $basket = $order->getBasket();

        // PROPS свойства товара которые нужно вывести в шаблоне
        $propertyCode = $this->getBusinessValue(payment:null, code:'PROP_ARTICLE') ?:'CML2_ARTICLE';
        // END PROPS

        $filePath = $_SERVER['DOCUMENT_ROOT'].'/basket_log.txt';
        if ($basket) {
            $i = 1;
            foreach ($basket as $basketItem) {
                $productId = $basketItem->getProductId();
                $property = PropertyTable::getRow([
                    'filter' => ['CODE' => $propertyCode],
                    'select' => ['ID']
                ]);

                if ($property) {
                    $propertyValue = ElementPropertyTable::getRow([
                        'filter' => [
                            'IBLOCK_ELEMENT_ID' => $productId,
                            'IBLOCK_PROPERTY_ID' => $property['ID']
                        ],
                        'select' => ['VALUE']
                    ]);

                    $article = $propertyValue['VALUE'] ?? '-';
                }

                $result[] = [
                    'NUMBER' => $i++,
                    'ARTICLE' => $article,
                    'NAME' => $basketItem->getField('NAME'),
                    'QUANTITY' => $basketItem->getQuantity(),
                    'PRICE' => $basketItem->getPrice(),
                    'SUM' => $basketItem->getFinalPrice(),
                ];

                $logEntry = date('Y-m-d H:i:s') . " - " . print_r($properties, true) . "\n";
                file_put_contents($filePath, $logEntry, FILE_APPEND);
            }
        }

        return $result;
    }

    private function getDeliveryPrice(Sale\Order $order)
    {
        $deliveryPrice = 0;
        $shipmentCollection = $order->getShipmentCollection();

        foreach ($shipmentCollection as $shipment) {
            if (!$shipment->isSystem()) {
                $deliveryPrice += $shipment->getPrice();
            }
        }

        return $deliveryPrice;
    }

    private function getBuyerInfo(Sale\Order $order)
    {
        $result = [];
        $propertyCollection = $order->getPropertyCollection();

        $result['COMPANY_NAME'] = '';
        $result['INN'] = '';
        $result['ADDRESS'] = '';
        $result['PHONE'] = '';

        $companyNameProp = $propertyCollection->getItemByOrderPropertyCode('COMPANY_NAME');
        if ($companyNameProp) {
            $result['COMPANY_NAME'] = $companyNameProp->getValue();
        }

        $innProp = $propertyCollection->getItemByOrderPropertyCode('INN');
        if ($innProp) {
            $result['INN'] = $innProp->getValue();
        }

        $address = [];
        $addrProps = ['ZIP', 'CITY', 'ADDRESS', 'STREET', 'HOUSE', 'FLAT'];

        foreach ($addrProps as $code) {
            $prop = $propertyCollection->getItemByOrderPropertyCode($code);
            if ($prop && $prop->getValue()) {
                $address[] = $prop->getValue();
            }
        }

        $result['ADDRESS'] = implode(', ', $address);

        $phoneProp = $propertyCollection->getItemByOrderPropertyCode('PHONE');
        if ($phoneProp) {
            $result['PHONE'] = $phoneProp->getValue();
        }

        return $result;
    }

    private function getBankInfo()
    {
        return [
            'BANK_NAME' => $this->getBusinessValue($order, 'BANK_NAME') ?: 'АО "Банк ЦентрКредит"',
            'BIK' => $this->getBusinessValue($order, 'BANK_BIK') ?: 'KCJBKZKX',
            'ACCOUNT_NUMBER' => $this->getBusinessValue($order, 'BANK_ACCOUNT') ?: 'KZ428560000000425746',
            'KBE' => $this->getBusinessValue($order, 'BANK_KBE') ?: '17',
            'CODE' => $this->getBusinessValue($order, 'BANK_CODE') ?: '710',
            'PAYMENT_PURPOSE_CODE' => $this->getBusinessValue($order, 'PAYMENT_PURPOSE') ?: 'ЗК25030AУТ0021240001',
        ];
    }

    private function getCompanyInfo()
    {
        return [
            'NAME' => $this->getBusinessValue($order, 'COMPANY_NAME') ?: 'ТОО Asian Medical Depo Group',
            'INN' => $this->getBusinessValue($order, 'COMPANY_INN') ?: '070940017485',
            'ADDRESS' => $this->getBusinessValue($order, 'COMPANY_ADDRESS') ?: 'Республика Казахстан, г.Алматы, ул.Богенбай батыра, дом № 149',
            'PHONE' => $this->getBusinessValue($order, 'COMPANY_PHONE') ?: '+7 (727) 313-15-23, доб. 110',
            'MANAGER' => $this->getBusinessValue($order, 'COMPANY_MANAGER') ?: '',
        ];
    }

    private function generatePdf($params)
    {
        ob_start();
        include(__DIR__ . '/template/invoice_template.php');
        $html = ob_get_clean();

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
            ]);

            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCurrencyList()
    {
        $currencyList = [];

        if (Loader::includeModule('currency'))
        {
            $currencyIterator = Currency\CurrencyTable::getList([
                'select' => ['CURRENCY'],
                'cache' => ['ttl' => 86400],
            ]);
            while ($currency = $currencyIterator->fetch())
            {
                $currencyList[] = $currency['CURRENCY'];
            }
        }

        return $currencyList;
    }

    public function processRequest(Request $request)
    {
        $result = new PaySystem\ServiceResult();
        return $result;
    }

    public static function getIndicativeFields()
    {
        return ['BX_HANDLER' => 'INVOICE_HANDLER'];
    }

    public function isRefundable(Payment $payment, Request $request = null)
    {
        return false;
    }

    public function getProps()
    {
        $configPath = __DIR__ . '/settings.php';

        if (file_exists($configPath)) {
            return include($configPath);
        }

        return [];
    }

    public function refund(Payment $payment, Request $request = null)
    {
        return new PaySystem\ServiceResult();
    }
}
