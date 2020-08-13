<?
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
	"sale", 
	'OnSaleComponentOrderProperties', 
	'componentOrderProperties'
);


function componentOrderProperties(&$arUserResult, $request, &$arParams, &$arResult)
{
	// получаем свойство Местоположения
    $propLocation = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arUserResult["PERSON_TYPE_ID"], "IS_LOCATION" => "Y"))->Fetch();

    // получаем индекс
    $propZip = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arUserResult["PERSON_TYPE_ID"], "IS_ZIP" => "Y"))->Fetch();

	$arUserResult['ORDER_PROP'][$propLocation["ID"]] = $_SESSION["CITYSELECTION_CITY"]["CODE"];
	$arUserResult['ORDER_PROP'][$propZip["ID"]] = $_SESSION["CITYSELECTION_CITY"]["ZIP"];

}