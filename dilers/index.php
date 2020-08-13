<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Дилеры");
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.register",
	"diler",
	Array(
		"AUTH" => "Y",
		"REQUIRED_FIELDS" => array("EMAIL","NAME","WORK_COMPANY"),
		"SET_TITLE" => "Y",
		"SHOW_FIELDS" => array("EMAIL","NAME","SECOND_NAME","LAST_NAME","PERSONAL_PHOTO","PERSONAL_MOBILE","WORK_COMPANY","WORK_POSITION","WORK_WWW","WORK_PHONE","WORK_CITY"),
		"SUCCESS_PAGE" => "",
		"USER_PROPERTY" => array("UF_MANAGER","UF_OPT_ROZN","UF_TYPE_GOODS"),
		"USER_PROPERTY_NAME" => "",
		"USE_BACKURL" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>