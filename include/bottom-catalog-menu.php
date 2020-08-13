<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="col-md-2 col-xs-4 links">
	<ul>
	<?
	// выборка только активных разделов из инфоблока $IBLOCK_ID, в которых есть элементы 
	// со значением свойства SRC, начинающееся с https://
	$i = 1;
	$arFilter = Array('IBLOCK_ID'=>2, 'GLOBAL_ACTIVE'=>'Y', "DEPTH_LEVEL" => 1);
	$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);
	while($ar_result = $db_list->GetNext())
	{
		?>
		<li><a href="<?=$ar_result["SECTION_PAGE_URL"]?>"><?=$ar_result["NAME"]?></a></li>
		<?
		if($i % 4 == 0)
			echo '</ul></div><div class="col-md-2 col-xs-4 links"><ul>';
		$i++;
	}

	?>
	</ul>
</div>