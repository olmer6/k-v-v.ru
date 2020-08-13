<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;

$aMenuLinks = Array(
	Array(
		"О компании", 
		"/about/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Сервис", 
		"/support/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Контакты",
		"/contacts/",
		Array(),
		Array(),
		""
	),
	Array(
		"Доставка",
		"/delivery/moskva/",
		Array(),
		Array(),
		""
	),
	Array(
		"Акции",
		"/actions/",
		Array(),
		Array(),
		""
	),
	Array(
		"Обратная связь",
		"/feedback/",
		Array(),
		Array(),
		""
	)
);

if ($USER->IsAuthorized())
{
	$aMenuLinks[] = [
			"Личный кабинет", 
			"/personal/", 
			Array(), 
			Array(), 
			"" 
	];
}
?>