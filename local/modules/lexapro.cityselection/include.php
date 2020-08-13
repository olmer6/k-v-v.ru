<?
namespace LexaProCitySelection;

use Bitrix\Main\Loader;

Loader::includeModule('catalog');
Loader::includeModule('sale');

$arClasses = [];

\CModule::AddAutoloadClasses('lexapro.cityselection', $arClasses);