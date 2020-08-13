<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>404 ошибка</title>
    <?$APPLICATION->ShowHead();?>
	<link href="/local/templates/supra_2017/css/main.css" type="text/css"   data-template-style="true"  rel="stylesheet">
	<link href="/local/templates/supra_2017/template_styles.css" type="text/css"   data-template-style="true"  rel="stylesheet">
    <link href="/bitrix/css/main/bootstrap.min.css" type="text/css"  rel="stylesheet" />
    <link href="/bitrix/css/main/font-awesome.min.css" type="text/css"  rel="stylesheet" />
</head>
<body>
	<div class="main-404">
		<div class="main-404__content">
			<div class="search-404">
				
				<div class="text-input-404">
					<p class="text-404-white"><span>404</span> ошибка, такой страницы не существует</p>
					<p  class="text-404-black">Воспользуйтесь поиском, чтобы перейти к нужным страницам</p>
					<?$APPLICATION->IncludeComponent(
							"bitrix:search.title", 
							"top_supra", 
							array(
								"NUM_CATEGORIES" => "1",
								"TOP_COUNT" => "5",
								"CHECK_DATES" => "N",
								"SHOW_OTHERS" => "N",
								"PAGE" => SITE_DIR."search/",
								"CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
								"CATEGORY_0" => array(
									0 => "iblock_catalog",
								),
								"CATEGORY_0_iblock_catalog" => array(
									0 => "all",
								),
								"CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
								"SHOW_INPUT" => "Y",
								"INPUT_ID" => "title-search-input",
								"CONTAINER_ID" => "search",
								"PRICE_CODE" => array(
									0 => "BASE",
								),
								"SHOW_PREVIEW" => "Y",
								"PREVIEW_WIDTH" => "75",
								"PREVIEW_HEIGHT" => "75",
								"CONVERT_CURRENCY" => "Y",
								"COMPONENT_TEMPLATE" => "top_supra",
								"ORDER" => "date",
								"USE_LANGUAGE_GUESS" => "Y",
								"PRICE_VAT_INCLUDE" => "Y",
								"PREVIEW_TRUNCATE_LEN" => "",
								"CURRENCY_ID" => "RUB"
							),
							false
						);?>
					</div>



			</div>

			<div class="bottom-404">
				<section class="promo clearfix promo-404 col-md-9">
					<?$APPLICATION->IncludeComponent(
						"bitrix:advertising.banner",
						"main_supra",
						array(
							"ANIMATION_DURATION" => "500",
							"ARROW_NAV" => "1",
							"BS_ARROW_NAV" => "Y",
							"BS_BULLET_NAV" => "Y",
							"BS_CYCLING" => "N",
							"BS_EFFECT" => "fade",
							"BS_HIDE_FOR_PHONES" => "N",
							"BS_HIDE_FOR_TABLETS" => "N",
							"BS_KEYBOARD" => "Y",
							"BS_WRAP" => "Y",
							"BULLET_NAV" => "2",
							"CACHE_TIME" => "0",
							"CACHE_TYPE" => "A",
							"CYCLING" => "N",
							"DEFAULT_TEMPLATE" => "-",
							"EFFECTS" => "",
							"HEIGHT" => "300",
							"KEYBOARD" => "N",
							"NOINDEX" => "N",
							"QUANTITY" => "1",
							"SCALE" => "N",
							"TYPE" => "MAIN_SUPRA",
							"WRAP" => "1",
							"COMPONENT_TEMPLATE" => ".default"
						),
						false
					);?>
				</section>
				
				
				<div class="list-group col-md-3">
				  <?
				// товар дня
				$APPLICATION->IncludeFile($APPLICATION->GetCurDir()."/include/novelty-404.php", Array(), Array(
					"MODE"      => "php",                                           // будет редактировать в веб-редакторе
					"NAME"      => "Редактирование включаемой области раздела",      // текст всплывающей подсказки на иконке
					"TEMPLATE"  => "section_include_template.php"                    // имя шаблона для нового файла
					));
				?>
                  </div>
				
				
			</div>
		</div>



	<div>	
</body>
</html>

<?//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>