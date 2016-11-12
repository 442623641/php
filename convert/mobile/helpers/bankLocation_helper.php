<?php
function bankInfo($card,$bankList)
{
    $card_8 = substr($card, 0, 8);
    if (isset($bankList[$card_8])) {
        return $bankList[$card_8];
    }
    $card_6 = substr($card, 0, 6);
    if (isset($bankList[$card_6])) {
        return $bankList[$card_6];
    }
    $card_5 = substr($card, 0, 5);
    if (isset($bankList[$card_5])) {
        return $bankList[$card_5];
    }
    $card_4 = substr($card, 0, 4);
    if (isset($bankList[$card_4])) {
       return $bankList[$card_4];
    }
    return '该卡号信息暂未录入';
}