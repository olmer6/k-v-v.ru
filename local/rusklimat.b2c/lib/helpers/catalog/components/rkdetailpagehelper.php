<?
namespace Rusklimat\B2c\Helpers\Catalog\Components;

use \Rusklimat\B2c\Config;
use Bitrix\Main\Loader;

/**
 * Class rkDetailPageHelper
 */
class rkDetailPageHelper
{
	public static function getTechProperties($nIblockID = 0)
	{
		$result = [];

		if($nIblockID)
		{
			$res = \CIBlockElement::GetList(
				Array(),
				Array("IBLOCK_ID" => 42, "ID" => 58150, "ACTIVE" => "Y"),
				false,
				Array("nPageSize" => 1),
				Array("ID", "NAME", "PROPERTY_PROPS")
			);

			$arProps = array();

			while ($ob = $res->GetNextElement())
			{
				$arFields = $ob->GetFields();
				$arProps = $arFields["PROPERTY_PROPS_VALUE"];
			}

			if($arProps)
			{
				$propSort = array_flip($arProps);

				$properties = \Bitrix\Iblock\PropertyTable::getList([
					'filter' => [
						'=IBLOCK_ID' => $nIblockID,
						'XML_ID' => $arProps
					],
					'select' => ['ID', 'XML_ID', 'CODE', 'NAME', 'HINT'],
					'order' => ["SORT" => "asc", "NAME" => "asc"]
				]);

				while ($prop_fields = $properties->fetch())
				{
					$result["PROPERTY_" . strtoupper($prop_fields["CODE"])] = [
						"ID" => $prop_fields["ID"],
						"NAME" => $prop_fields["NAME"],
						"DIMENSION" => $prop_fields["HINT"],
						"SORT" => (int)$propSort[$prop_fields['XML_ID']]
					];
				}

				uasort($result, function ($a, $b) {
					if($a['SORT'] == $b['SORT'])
					{
						return 0;
					}
					return ($a['SORT'] < $b['SORT']) ? -1 : 1;
				});
			}
		}

		return $result;
	}

	public static function getPropVals($arResult = [])
	{
		$result = [];
		
		$obCache = new \CPHPCache();

		if ($obCache->InitCache(3600, md5(serialize($arResult)), "/"))
		{
			$result = $obCache->GetVars();
		}
		elseif ($obCache->StartDataCache())
		{
			$arCatalogProperties = [];
			$arPropsCode = [];
			$arPropNames = [];

			// Alex: ссылки на псевдо разделы #51270
			$arPseudoPropVals = rkDetailPageHelper::getPseudoPropVals($arResult["ID"], $arResult["IBLOCK_ID"]);

			$rsProperties = \CIBlockProperty::GetList(
				[],
				["ACTIVE" => "Y", "IBLOCK_ID" => $arResult['IBLOCK_ID']]
			);

			while ($arProp = $rsProperties->GetNext())
			{
				if($arProp['XML_ID'])
				{
					$arCatalogProperties[ToUpper($arProp['XML_ID'])] = $arProp;
					$arAllPropsCode[$arProp["CODE"]] = $arProp["ID"];
				}
			}

			if($arResult['IS_AJAX_DESCRIPTION'] == false)
			{
				//Загружаем характеристики по группам
				foreach ($arResult['PROPS_GROUPS'] as $prop_group)
				{
					if($prop_group["SORT"] >= 10 && ($prop_group["SORT"] < 70) && $prop_group['PROPERTY_PROPS_VALUE'])
					{
						foreach ($prop_group['PROPERTY_PROPS_VALUE'] as $p)
						{
							if($p && !empty($arCatalogProperties[ToUpper($p)]))
							{
								$prop_fields = $arCatalogProperties[ToUpper($p)];

								if($prop_fields)
								{
									$arPropsCode[] = $prop_fields["CODE"];

									$arPropNames[$prop_fields["CODE"]] = array(
										"ID" => $prop_fields["ID"],
										"NAME" => $prop_fields["NAME"],
										"HINT" => $prop_fields["HINT"],
										"XML_ID" => $prop_fields["XML_ID"],
									);
								}
							}
						}

						if($arPropsCode)
						{
							foreach ($arPropsCode as $code)
							{
								if(!empty($arResult['PROPERTIES'][$code]))
								{
									$arProp = $arResult['PROPERTIES'][$code];

									if($arProp['VALUE'])
									{
										$result[$arPropNames[$code]["XML_ID"]] = array(
											"ID" => $arPropNames[$code]["ID"],
											"NAME" => $arPropNames[$code]["NAME"],
											"DISPLAY_VALUE" => $arProp['VALUE'],
											"VALUE" => $arProp['VALUE'],
											"DIMENSION" => $arPropNames[$code]["DIMENSION"],
											"HINT" => $arPropNames[$code]["HINT"],
										);
										
										// если есть подходящее значение у псевдоразделов, добавим URL для ссылки #51270
										$idPropPseudoCode   = $arAllPropsCode[ $code ];
										$idPropPseudoCode_n = $arAllPropsCode[ $code . "_n" ];								
										if(
											!empty($arPseudoPropVals[ $idPropPseudoCode ]) &&
											strtoupper($arPseudoPropVals[ $idPropPseudoCode]["VALUE"]) == strtoupper($arProp['VALUE'])
										)
										{
											$result[ $arPropNames[$code]["XML_ID"] ]["URL"] = $arPseudoPropVals[ $idPropPseudoCode ]["SECTION_PAGE_URL"];
										}
										elseif(
											!empty($arPseudoPropVals[ $idPropPseudoCode_n ]) &&
											strtoupper($arPseudoPropVals[ $idPropPseudoCode_n]["VALUE"]) == strtoupper($arProp['VALUE'])
										)
										{
											$result[ $arPropNames[$code]["XML_ID"] ]["URL"] = $arPseudoPropVals[ $idPropPseudoCode_n ]["SECTION_PAGE_URL"];
										}
									}
								}
							}
						}
					}
				}
			}
			$obCache->EndDataCache($result);
		}
		return $result;
	}

	public static function getPseudoPropVals($element_id, $iblock_id)
	{
		Loader::includeModule("kokoc.pseudosection");
		
		$arPropsVals = array();

		// Alex: получаем IDшники всех привязанных разделов элемента
		$resAllSeciton = \CIBlockElement::GetElementGroups($element_id, true);
		while ($obAllSeciton = $resAllSeciton->Fetch())
			$arIdAllSection[] = $obAllSeciton["ID"];

			// получаем по псевдоразделам: ID свойства привязки, значение свойства привязки и URL адрес раздела

		$arFilterPseudo = Array('IBLOCK_ID' => $iblock_id, "ID" => $arIdAllSection, '!UF_PSEUDO_SECTION' => false, '>ELEMENT_CNT' => 0);
		$arSelectPseudo = array("ID", "NAME", "UF_PSEUDO_SECTION", "SECTION_PAGE_URL");
		$db_PseudoSection = \CIBlockSection::GetList(Array(), $arFilterPseudo, true, $arSelectPseudo);
		while ($resPseudoSection = $db_PseudoSection->GetNext())
		{
			$pseudoSection = unserialize(htmlspecialchars_decode($resPseudoSection['UF_PSEUDO_SECTION']));

			// обработка правил свойства Псевдоразделов
			$obCond = new \CCCatalogCondTree();
			$boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
			$conditions = $obCond->Parse($pseudoSection['rule']);
			$propVal = $obCond->getIdPropAndVal($conditions);
			if(!empty($propVal[1]))
			{
				$arPropsVals[$propVal[0]] = array
				(
					"VALUE" => $propVal[1],
					"SECTION_PAGE_URL" => $resPseudoSection["SECTION_PAGE_URL"]
				);
			}
		}
		return $arPropsVals;
	}
}
?>