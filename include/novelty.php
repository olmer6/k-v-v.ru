<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<h2 class="maintitle">Наши новинки</h2>
<div class="">
	<div class="">
	<?
	$arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "DETAIL_PICTURE", "CATALOG_GROUP_1","PROPERTY_MINIMUM_PRICE");
	$arFilter = Array("IBLOCK_ID"=>2, "ACTIVE"=>"Y", "!=PROPERTY_NEWPRODUCT" => false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>5), $arSelect);
	while($ob = $res->GetNext())
	{
		if(!empty($ob["PROPERTY_MINIMUM_PRICE_VALUE"]))
			$minPrice = $ob["PROPERTY_MINIMUM_PRICE_VALUE"];
		elseif(!empty($ob["CATALOG_PRICE_1"]))
			$minPrice = $ob["CATALOG_PRICE_1"];
			
		?>
		<div class="col-md-20 col-xs-6 item-wrap">
			<div class="item">
				<a href="<?=$ob["DETAIL_PAGE_URL"];?>" class="item-img"><img src="<?=CFile::GetPath($ob["DETAIL_PICTURE"]);?>" class="img"></a>
				<a href="<?=$ob["DETAIL_PAGE_URL"];?>" class="item-descr"><?=$ob["NAME"];?></a>
				<div class="price action-price"><?=CurrencyFormat($minPrice ,"RUB")?></div>
			</div>
		</div>
		<?
	}
	?>
	</div>
</div>
<div class="more-link"><a href="/catalog/">Показать все товары	</a></div>