<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рассылки");
?> 

<div class="bx-subscribe">
	<?$APPLICATION->IncludeComponent("bitrix:sender.subscribe", "", array(
		"SET_TITLE" => "N"
	));?>
</div>

 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>