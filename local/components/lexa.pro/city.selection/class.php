<?php
// Author: Alex
// www.lexa.pro
// 22.02.2019

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class LexaProCitySelection extends \CBitrixComponent
{

    public function executeComponent()
    {
        // значения по умолчанию
        if(empty($_SESSION["CITYSELECTION_CITY"]["NAME"]) || empty($_SESSION["CITYSELECTION_CITY"]["CODE"]) || empty($_SESSION["CITYSELECTION_CITY"]["ZIP"]))
        {
            $_SESSION["CITYSELECTION_CITY"]["NAME"] = "Москва";
            $_SESSION["CITYSELECTION_CITY"]["CODE"] = "0000073738";
            $_SESSION["CITYSELECTION_CITY"]["ZIP"] = "101000";
        }

        $this->includeComponentTemplate();
    }
}