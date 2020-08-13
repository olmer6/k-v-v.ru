<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Обратная связь");
?><?$APPLICATION->IncludeComponent("bitrix:form", "feedback", Array(
	"AJAX_MODE" => "N",	// Включить режим AJAX
		"AJAX_OPTION_ADDITIONAL" => "",	// Дополнительный идентификатор
		"AJAX_OPTION_HISTORY" => "N",	// Включить эмуляцию навигации браузера
		"AJAX_OPTION_JUMP" => "N",	// Включить прокрутку к началу компонента
		"AJAX_OPTION_STYLE" => "Y",	// Включить подгрузку стилей
		"CACHE_TIME" => "3600",	// Время кеширования (сек.)
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CHAIN_ITEM_LINK" => "",	// Ссылка на дополнительном пункте в навигационной цепочке
		"CHAIN_ITEM_TEXT" => "",	// Название дополнительного пункта в навигационной цепочке
		"EDIT_ADDITIONAL" => "N",	// Выводить на редактирование дополнительные поля
		"EDIT_STATUS" => "Y",	// Выводить форму смены статуса
		"IGNORE_CUSTOM_TEMPLATE" => "N",	// Игнорировать свой шаблон
		"NOT_SHOW_FILTER" => array(	// Коды полей, которые нельзя показывать в фильтре
			0 => "",
			1 => "",
		),
		"NOT_SHOW_TABLE" => array(	// Коды полей, которые нельзя показывать в таблице
			0 => "",
			1 => "",
		),
		"RESULT_ID" => $_REQUEST[RESULT_ID],	// ID результата
		"SEF_MODE" => "N",	// Включить поддержку ЧПУ
		"SHOW_ADDITIONAL" => "N",	// Показать дополнительные поля веб-формы
		"SHOW_ANSWER_VALUE" => "N",	// Показать значение параметра ANSWER_VALUE
		"SHOW_EDIT_PAGE" => "Y",	// Показывать страницу редактирования результата
		"SHOW_LIST_PAGE" => "Y",	// Показывать страницу со списком результатов
		"SHOW_STATUS" => "Y",	// Показать текущий статус результата
		"SHOW_VIEW_PAGE" => "Y",	// Показывать страницу просмотра результата
		"START_PAGE" => "new",	// Начальная страница
		"SUCCESS_URL" => "",	// Страница с сообщением об успешной отправке
		"USE_EXTENDED_ERRORS" => "N",	// Использовать расширенный вывод сообщений об ошибках
		"VARIABLE_ALIASES" => array(
			"action" => "action",
		),
		"WEB_FORM_ID" => "3",	// ID веб-формы
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>