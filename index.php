<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");

?>
			
            <section class="promo clearfix">
            	<!-- <img src="image/promo.png" alt=""> -->
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
			
			
            <section class="content without-price">			  
			  	<?
				// товар дня
				$APPLICATION->IncludeFile($APPLICATION->GetCurDir()."/include/novelty.php", Array(), Array(
					"MODE"      => "php",                                           // будет редактировать в веб-редакторе
					"NAME"      => "Редактирование включаемой области раздела",      // текст всплывающей подсказки на иконке
					"TEMPLATE"  => "section_include_template.php"                    // имя шаблона для нового файла
					));
				?>	
			  
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.bestsellers",
	"supra_main",
	Array(
		"ACTION_VARIABLE" => "action",
		"ADDITIONAL_PICT_PROP_2" => "MORE_PHOTO",
		"ADDITIONAL_PICT_PROP_3" => "MORE_PHOTO",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BASKET_URL" => "/personal/basket.php",
		"BY" => "QUANTITY",
		"CACHE_TIME" => "86400",
		"CACHE_TYPE" => "A",
		"CART_PROPERTIES_2" => array("", ""),
		"CART_PROPERTIES_3" => array("", "CORNER", ""),
		"CART_PROPERTIES_4" => "",
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"DETAIL_URL" => "",
		"DISPLAY_COMPARE" => "N",
		"FILTER" => array("N", "P", "F"),
		"HIDE_NOT_AVAILABLE" => "N",
		"LABEL_PROP_2" => "-",
		"LABEL_PROP_3" => "SPECIALOFFER",
		"LINE_ELEMENT_COUNT" => "3",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_BTN_SUBSCRIBE" => "Подписаться",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"OFFER_TREE_PROPS_3" => array(),
		"OFFER_TREE_PROPS_4" => array(0=>"-",),
		"PAGE_ELEMENT_COUNT" => "6",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PERIOD" => "0",
		"PRICE_CODE" => array("BASE"),
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_SUBSCRIPTION" => "N",
		"PROPERTY_CODE_2" => array("", ""),
		"PROPERTY_CODE_3" => array("", "MANUFACTURER", "MATERIAL", ""),
		"PROPERTY_CODE_4" => array(0=>"COLOR",),
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_IMAGE" => "Y",
		"SHOW_NAME" => "Y",
		"SHOW_OLD_PRICE" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"SHOW_PRODUCTS_2" => "Y",
		"SHOW_PRODUCTS_3" => "Y",
		"TEMPLATE_THEME" => "red",
		"USE_PRODUCT_QUANTITY" => "N"
	)
);?>
				
				
            </section>
			
			<?$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"reviews",
				Array(
					"ACTIVE_DATE_FORMAT" => "d.m.Y",
					"ADD_SECTIONS_CHAIN" => "N",
					"AJAX_MODE" => "N",
					"AJAX_OPTION_ADDITIONAL" => "",
					"AJAX_OPTION_HISTORY" => "N",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"CACHE_FILTER" => "N",
					"CACHE_GROUPS" => "Y",
					"CACHE_TIME" => "36000000",
					"CACHE_TYPE" => "A",
					"CHECK_DATES" => "Y",
					"DETAIL_URL" => "",
					"DISPLAY_BOTTOM_PAGER" => "N",
					"DISPLAY_DATE" => "N",
					"DISPLAY_NAME" => "Y",
					"DISPLAY_PICTURE" => "Y",
					"DISPLAY_PREVIEW_TEXT" => "Y",
					"DISPLAY_TOP_PAGER" => "N",
					"FIELD_CODE" => array("ID", "CODE", "XML_ID", "NAME", "SORT", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
					"FILTER_NAME" => "",
					"HIDE_LINK_WHEN_NO_DETAIL" => "N",
					"IBLOCK_ID" => "#REVIEWS_IBLOCK_ID#",
					"IBLOCK_TYPE" => "content",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"INCLUDE_SUBSECTIONS" => "Y",
					"MESSAGE_404" => "",
					"NEWS_COUNT" => "2",
					"PAGER_BASE_LINK_ENABLE" => "N",
					"PAGER_DESC_NUMBERING" => "N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
					"PAGER_SHOW_ALL" => "N",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_TEMPLATE" => ".default",
					"PAGER_TITLE" => "Новости",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"PREVIEW_TRUNCATE_LEN" => "",
					"PROPERTY_CODE" => array("AUTHOR_CITY", "STARS", ""),
					"SET_BROWSER_TITLE" => "N",
					"SET_LAST_MODIFIED" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_STATUS_404" => "N",
					"SET_TITLE" => "N",
					"SHOW_404" => "N",
					"SORT_BY1" => "SORT",
					"SORT_BY2" => "ID",
					"SORT_ORDER1" => "DESC",
					"SORT_ORDER2" => "ASC",
                    "LINK_TO_MARKET" => "https://market.yandex.ru/"
				)
			);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>