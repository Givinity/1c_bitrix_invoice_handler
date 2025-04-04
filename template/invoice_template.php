<?php
/**
 * @var array $params
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Форматирование суммы
function formatPrice($price)
{
    return number_format($price, 2, ',', ' ');
}

// Функция для перевода суммы прописью (для казахского тенге)
function num2str($num)
{
    $nul = 'ноль';
    $ten = array(
        array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
    );
    $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
    $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
    $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
    $unit = array(
        array('тиын' , 'тиын',  'тиын',     1),
        array('тенге',   'тенге',   'тенге',     0),
        array('тысяча',  'тысячи',  'тысяч',     1),
        array('миллион', 'миллиона', 'миллионов', 0),
        array('миллиард', 'миллиарда', 'миллиардов', 0),
    );

    list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub) > 0) {
        foreach (str_split($rub, 3) as $uk => $v) {
            if (!intval($v)) continue;
            $uk = sizeof($unit) - $uk - 1;
            $gender = $unit[$uk][3];
            list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2 > 1) # 20-99
                $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];
            else # 10-19 | 1-9
                $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];
            if ($uk > 1) $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
        }
    } else {
        $out[] = $nul;
    }
    $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // Добавляем 'рублей'
    $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]);
    return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
}

/**
 * Склонение словоформы
 */
function morph($n, $f1, $f2, $f5)
{
    $n = abs(intval($n)) % 100;
    if ($n > 10 && $n < 20) return $f5;
    $n = $n % 10;
    if ($n > 1 && $n < 5) return $f2;
    if ($n == 1) return $f1;
    return $f5;
}

// Получаем итоговую сумму с доставкой
$totalWithoutDelivery = 0;
foreach ($params['BASKET_ITEMS'] as $item) {
    $totalWithoutDelivery += $item['SUM'];
}
$totalSum = $totalWithoutDelivery + $params['DELIVERY_PRICE'];

// Информация о заказе
$orderNumber = 'УТ-' . $params['ORDER_ID'];
$orderDate = $params['DATE_BILL']->format('d F Y');
$validUntil = $params['DATE_BILL_UNTIL']->format('d.m.Y');

// Получаем название месяца на русском
$months = [
    'January' => 'января',
    'February' => 'февраля',
    'March' => 'марта',
    'April' => 'апреля',
    'May' => 'мая',
    'June' => 'июня',
    'July' => 'июля',
    'August' => 'августа',
    'September' => 'сентября',
    'October' => 'октября',
    'November' => 'ноября',
    'December' => 'декабря',
];

foreach ($months as $eng => $rus) {
    $orderDate = str_replace($eng, $rus, $orderDate);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Счет на оплату № <?= $orderNumber ?> от <?= $orderDate ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1;
            margin: 0px 0px;
            text-align: center;
        }

        ol, ul {
            list-style: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            margin-left: auto;
            margin-right: auto;
            border: 0.5px solid #000; /* Тонкая обводка для таблицы */
            width: 80%;
        }

        th, td {
            border: 0.5px solid #000; /* Тонкая обводка для ячеек */
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        h1, h2, h3, h4, h5, h6 {
            font-size: inherit;
            font-weight: normal;
        }

        .text_attention {
            font-size: 7pt;
            text-align: center;
            margin: 10px auto 0 auto;
            display: block;
        }

        .block_text {
            text-align: center;
            width: 260px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
        }

        .block_text p {
            margin:0;
        }

        .table_with_bank .text {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            width: 100%;
        }

        .block_with_product span {
            font-size: 12pt;
            font-weight: bold;
        }

        #bank td {
            padding: 1px 5px;
            border: 0.5px solid #000; /* Тонкая обводка */
        }

        #text_buyer {
            margin: 0 auto;
            width: 80%;
            border: none; /* Убираем обводку у этой таблицы */
        }

        #text_buyer td {
            padding: 10px 15px;
            border: none; /* Убираем границы у ячеек */
        }

        #product td {
            padding: 3px 5px;
            border: 0.5px solid #000; /* Тонкая обводка */
        }

        #product {
            margin: 0 auto;
            width: 80%;
        }

        #table_sum {
            margin-left: auto;
            margin-right: 50px;
            border: none; /* Убираем обводку */
        }

        #table_sum td {
            padding: 5px 10px;
            text-align: right;
            border: none; /* Убираем границы */
        }

        .full-width-line {
            width: 80%;
            height: 1px; /* Более тонкая линия */
            background-color: #000;
            margin: 10px auto;
        }

        .count_name {
            margin: 40px 0 0 90px;
            font-size: 7pt;
            text-align: left;
        }

        .warning {
            margin: 10px 0 0 90px;
            font-size: 8pt;
            text-align: left;
        }

        #table_sign td {
            padding: 5px 30px;
            border: none; /* Убираем границы */
        }

        #table_sign {
            margin: 20px 0 0 80px;
            text-align: left;
            border: none; /* Убираем обводку */
        }
    </style>
</head>
<body>
    <div class="text_attention">
        <div class="block_text">
        <p>Внимание! Счет действителен до <?= $validUntil ?>.</p>
        <p>Оплата данного счета означает согласие с условиями поставки товара.</p>
        <p>Уведомление об оплате обязательно, в противном случае не гарантируется
            наличие товара на складе.</p>
        <p>Товар отпускается по факту прихода денег
            на р/с</p>
        <p>Поставщика, самовывозом, при наличии доверенности
            и документов удостоверяющих личность.</p>
        </div>
    </div>

    <div class="table_with_bank" style="place-items: center;">
        <p class="text">Образец заполнения платежного поручения</p>
        <table id="bank" width="80%">
            <tr>
                <td><?= $params['BANK_INFO']['BANK_NAME'] ?><br><br>Банк получателя</td>
                <td>
                    БИК<br>Сч. №
                </td>
                <td><?= $params['BANK_INFO']['BIK'] ?></td>
            </tr>
            <tr>
                <td>ИИН/БИН | <?= $params['COMPANY_INFO']['INN'] ?></td>
                <td>Сч. № </td>
                <td><?= $params['BANK_INFO']['ACCOUNT_NUMBER'] ?></td>
            </tr>
            <tr>
                <td><?= $params['COMPANY_INFO']['NAME'] ?><br>Получатель</td>
                <td>КБе <br>Код наз. пл. <br>Код</td>
                <td><?= $params['BANK_INFO']['KBE'] ?> <br><?= $params['BANK_INFO']['CODE'] ?> <br><?= $params['BANK_INFO']['PAYMENT_PURPOSE_CODE'] ?></td>
            </tr>
            <tr>
                <td colspan="3">Оплата по заказу клиента №<?= $orderNumber ?> <br> <br>Назначение платежа</td>
            </tr>
        </table>
    </div>

    <br>
    <div class="block_with_product">
        <span style="margin: 0 10%;">Счет на оплату № <?= $orderNumber ?> от <?= $orderDate ?> г.</span>
        <div class="full-width-line"></div>
        <br><br>
        <table id="text_buyer">
            <tr>
                <td>Поставщик</td>
                <td><?= $params['COMPANY_INFO']['NAME'] ?>, БИН / ИИН <?= $params['COMPANY_INFO']['INN'] ?>, <?= $params['COMPANY_INFO']['ADDRESS'] ?>, тел.: <?= $params['COMPANY_INFO']['PHONE'] ?></td>
            </tr>
            <tr>
                <td>Покупатель:</td>
                <td><?= $params['BUYER_INFO']['COMPANY_NAME'] ? $params['BUYER_INFO']['COMPANY_NAME'] : 'Физическое лицо: ' . $params['BUYER_INFO']['FIO'] ?><?= $params['BUYER_INFO']['INN'] ? ', БИН / ИИН ' . $params['BUYER_INFO']['INN'] : '' ?>, <?= $params['BUYER_INFO']['ADDRESS'] ?><?= $params['BUYER_INFO']['PHONE'] ? ', тел.: ' . $params['BUYER_INFO']['PHONE'] : '' ?></td>
            </tr>
            <tr>
                <td>Договор:</td>
                <td><?= $orderNumber ?> от <?= $orderDate ?> г</td>
            </tr>
        </table>

        <table id="product">
            <tr>
                <td>№</td>
                <td>Артикул</td>
                <td>Товары (работы, услуги)</td>
                <td>Количество</td>
                <td>Цена</td>
                <td>Сумма</td>
            </tr>
            <?php foreach ($params['BASKET_ITEMS'] as $item): ?>
            <tr>
                <td><?= $item['NUMBER'] ?></td>
                <td><?= $item['ARTICLE'] ?></td>
                <td><?= $item['NAME'] ?></td>
                <td><?= $item['QUANTITY'] ?></td>
                <td><?= formatPrice($item['PRICE']) ?></td>
                <td><?= formatPrice($item['SUM']) ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if ($params['DELIVERY_PRICE'] > 0): ?>
            <tr>
                <td><?= count($params['BASKET_ITEMS']) + 1 ?></td>
                <td>-</td>
                <td>Доставка</td>
                <td>1</td>
                <td><?= formatPrice($params['DELIVERY_PRICE']) ?></td>
                <td><?= formatPrice($params['DELIVERY_PRICE']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="sum">
        <table id="table_sum">
            <tr>
                <td>Итого:</td>
                <td><?= formatPrice($totalSum) ?></td>
            </tr>
            <br>
            <tr>
                <td>Без налога (НДС):</td>
                <td>-</td>
            </tr>
        </table>
    </div>

    <div class="count_name">
        <p>Всего наименований <?= count($params['BASKET_ITEMS']) + ($params['DELIVERY_PRICE'] > 0 ? 1 : 0) ?>, на сумму <?= formatPrice($totalSum) ?> <?= $params['CURRENCY'] ?></p>
        <p style="font-weight: bold;"><?= num2str($totalSum) ?></p>
    </div>
    <div class="full-width-line"></div>
    <div class="warning">
        <p>ВНИМАНИЕ! Во избежание расхождений в платежном поручении просим ОБЯЗАТЕЛЬНО указывать НОМЕР СЧЕТА НА ОПЛАТУ!!!</p>
    </div>
    <div class="full-width-line"></div>
    <div class="signature">
        <table id="table_sign">
            <tr>
                <td>Менеджер</td>
                <td>_____________________________</td>
                <td><?= $params['COMPANY_INFO']['MANAGER'] ? $params['COMPANY_INFO']['MANAGER'] : '_____________________________' ?></td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size: 8pt;text-align: center;">подпись</td>
                <td style="font-size: 8pt;text-align: center;">расшифровка подписи</td>
            </tr>
        </table>
    </div>
</body>
</html>
