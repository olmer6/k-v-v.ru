<?
namespace Rusklimat\B2c\Helpers\Catalog\Components;

use Bitrix\Highloadblock as HL;
use \Bitrix\Main\Data\Cache;
use \Bitrix\Main\Context;
use Rusklimat\B2c\Helpers\Main\Geo;
use \Rusklimat\B2c\Config;

class Element
{
	/**
	 * @param $PRODUCT_XML_ID
	 * @param $CITY
	 * @param bool $PRICE
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getActions($PRODUCT_XML_ID, $CITY, $PRICE = false)
	{
		GLOBAL $IBLOCK_ACTIONS;

		$arActions = [];

		if($PRODUCT_XML_ID && $CITY)
		{
			$arFilter = [
				"IBLOCK_ID" => $IBLOCK_ACTIONS,
				'!PROPERTY_PIC_PRODUCT' => false,
				'=PROPERTY_geo_networks' => $CITY,
				"ACTIVE" => "Y",
				[
					"LOGIC" => "OR",
					['=PROPERTY_GOODS_XML' => $PRODUCT_XML_ID],
					["PROPERTY_ALL_GOODS" => "Y"]
				],
				"ACTIVE_DATE" => "Y",
				">PROPERTY_GOODS_PAGE_SORT" => "0",
				"!ID" => \CIBlockElement::SubQuery("ID", [
					"IBLOCK_ID" => $IBLOCK_ACTIONS,
					"ACTIVE" => "Y",
					"=PROPERTY_GOODS_EXCEPT_XML" => $PRODUCT_XML_ID,
				])
			];

			if(is_numeric($PRICE) && $PRICE > 0)
			{
				$arFilter[] = [
					'LOGIC' => 'OR',
					['<=PROPERTY_MIN_PRODUCT_PRICE' => $PRICE],
					['PROPERTY_MIN_PRODUCT_PRICE' => false]
				];
			}

			$arSelect = [
				'ID',
				'NAME',
				'XML_ID',
				'SORT',
				'PROPERTY_PIC_PRODUCT',
				'DETAIL_PAGE_URL'
			];

			$res = \CIBlockElement::GetList(
				["property_GOODS_PAGE_SORT" => "ASC"],
				$arFilter,
				false,
				false,
				$arSelect
			);

			while ($arElement = $res->GetNext())
			{
				$arElement['PROPERTY_PIC_PRODUCT_VALUE'] = \CFile::GetFileArray($arElement['PROPERTY_PIC_PRODUCT_VALUE']);
				
				// Alex: #56781
				$arElement["PROPERTY_PIC_PRODUCT_VALUE"]["SRC"] = \CFile::ResizeImageGet(
					$arElement["PROPERTY_PIC_PRODUCT_VALUE"]["ID"],
					array('width'=>296, 'height'=>130),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false
				)["src"];
				
				
				$arActions[$arElement['XML_ID']] = $arElement;
			}

			if($arActions)
			{
				$HlBlock = HL\HighloadBlockTable::getList(['filter' => ['=NAME' => 'HlRkActionsDisplacement']])->fetch();

				if($HlBlock)
				{
					$entityDataClass = HL\HighloadBlockTable::compileEntity($HlBlock)->getDataClass();

					$getList = $entityDataClass::getList([
						'filter' => [
							'=UF_GOODS' => $PRODUCT_XML_ID,
							'=UF_PRICEBUSH' => $CITY
						]
					]);

					while($row = $getList->fetch())
					{
						if(!empty($row['UF_ACTION']) && !empty($arActions[$row['UF_ACTION']]))
						{
							if(!empty($arActions[$row['UF_DISPLACEMENT']]))
							{
								unset($arActions[$row['UF_DISPLACEMENT']]);
							}
						}
					}
				}
			}
		}

		return $arActions;
	}

	public static function getLabelsProperty($props, $badProps)
	{
		$Labels = [];

		$obCache = new \CPHPCache();

		if ($obCache->InitCache(3600, serialize($props), "/"))
		{
			$Labels = $obCache->GetVars();
		}
		elseif ($obCache->StartDataCache())
		{
			$props_xml = [];

			foreach ($props as $k => $v)
			{
				if (in_array($v['CODE'], $badProps) || $v['SORT'] < 100 || in_array(substr($v['CODE'], 0, 4), ['pdf_', 'seo_']) || $v['USER_TYPE'] == 'HTML')
					continue;

				$props_xml[$v['XML_ID']] = $v;
			}

			$res = \CIBlockElement::GetList(
				['SORT'=>'ASC'],
				["IBLOCK_ID"=>43,'!PROPERTY_LABEL'=>false],
				false,
				false,
				['ID','NAME','XML_ID','SORT','PROPERTY_LABEL']
			);

			while($arElement = $res->Fetch())
			{
				if($props_xml[$arElement['XML_ID']]['VALUE'] != '')
				{
					$Labels[$arElement['PROPERTY_LABEL_VALUE']] = array('NAME'=>$arElement['NAME'],'VALUE'=>$props_xml[$arElement['XML_ID']]['VALUE']);
				}
			}

			$obCache->EndDataCache($Labels);
		}
		return $Labels;
	}
	
	/*
	* Получение определяющих характеристик для товара #30451
	* Alex: Важное замечание: функционал рассчитан на Максимум 2 выбранных свойства для раздела!!!!!
	* Если вдруг потребуется больше, arFilterProdLogicOR не подойдет
	*/
	public static function getFilterProps($sectionId = 0, $props = array())
	{
		if(empty($sectionId) || empty($props) || empty($props["series"]["VALUE"]))
			return false;
		
		// ID ИБ
		$catalogIblockId = \Rusklimat\B2c\Config\Catalog::ID;
		$arTranslitParams = array("replace_space"=>"-","replace_other"=>"-");
		
		// получаем ID-шники выбранных для раздела хар-ки
		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$catalogIblockId."_SECTION", $sectionId);
		$filterPropsIds = $arUserFields["UF_FILTER_PROP"]["VALUE"];
		$groupHidePropsIds = $arUserFields["UF_GROUP_HIDE_PROP"]["VALUE"];
		
		// для фильтрации подходящих товаров
		$arFilterProd = array(
			"IBLOCK_ID" => $catalogIblockId,
			"PROPERTY_SERIES" => $props["series"]["VALUE"],
			"ACTIVE" => "Y",
			"SECTION_ID" => $sectionId
		);
		
		$arSelectProd = Array("ID", "NAME", "CODE");
		$arProps = array();
		
		// одно из значений должно совпадать со значением текущего эл-та, если 2 свойства
		$arFilterProdLogicOR = array(
			"LOGIC" => "OR",
		);
		
		// Определяющие хар-ки
		foreach($filterPropsIds as $p_id)
		{
			// к сожалению, перебираем по одному, т.к. ID гетлиста принимает только число
			$resProp = \CIBlockProperty::GetByID($p_id);
			if($arProp = $resProp->Fetch())
			{
				if(!empty($props[$arProp["CODE"]]["VALUE"])) // если у товара это св-во заполнено
				{
					$arProps[$arProp["CODE"]] = $props[$arProp["CODE"]]; // запомним св-во
					
					$arFilterProd["!PROPERTY_".strtoupper($arProp["CODE"])] = false; // свойство должно быть не пустым
					$arFilterProdLogicOR["PROPERTY_".strtoupper($arProp["CODE"])] = $props[$arProp["CODE"]]["VALUE"]; // для более 1 св-ва
					$arSelectProd[] = "PROPERTY_".strtoupper($arProp["CODE"]); // добавляем в выборку
				}
			}
		}
		
		// если св-в больше 1, проверим, что одна из хар-к совпадает с нашим товаром
		if(count($arProps) > 1)
			$arFilterProd[] = $arFilterProdLogicOR;
		
		// теперь для скрытых группировочных свойств #38902
		foreach($groupHidePropsIds as $ph_id)
		{
			// к сожалению, перебираем по одному, т.к. ID гетлиста принимает только число
			$resProp = \CIBlockProperty::GetByID($ph_id);
			if($arProp = $resProp->Fetch())
			{
				if(!empty($props[$arProp["CODE"]]["VALUE"])) // если у товара это св-во заполнено
				{
					$arFilterProd["PROPERTY_".strtoupper($arProp["CODE"])] = $props[$arProp["CODE"]]["VALUE"];
				}
			}
		}		

		if(!empty($arProps) && count($arProps) <= 2)
		{
			$resPropd = \CIBlockElement::GetList(Array(), $arFilterProd, false, false, $arSelectProd);
			
			while($obProp = $resPropd->Fetch())
			{
				foreach($arProps as &$prop)
				{
					$propUpperName = "PROPERTY_".strtoupper($prop["CODE"])."_VALUE";
					
					$prop_value = \Cutil::translit($obProp[$propUpperName], "ru", $arTranslitParams);
					
					$prop["VALUES_TREE_VAL"][$obProp[$propUpperName]] = $obProp["CODE"];
					ksort($prop["VALUES_TREE_VAL"], SORT_NUMERIC); // в идеале вынесте после цикла
				}
			}
			
			$result = array(
				"PROPS" => $arProps
			);

			return $result;
		}
		else
			return false;
	}

	/**
	 * @param $productID
	 * @param string $cityXmlID
	 *
	 * @return array
	 *
	 * Отзывы для карточки товара
	 */
	public static function getReviews($productID, $cityXmlID = '')
	{
		$Reviews = [
			'REVIEWS' => [],
			'GEO_REVIEWS' => [],
			'REVIEWS_CNT' => 0,
			'TOTAL_RATE' => 0
		];

		if($cityXmlID)
		{
			$arCity = Geo::getInstance()->getByXmlID($cityXmlID);
		}

		$cache = Cache::createInstance();

		$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

		if ($cache->initCache(3600, $productID.'r'.($arCity['REGION_ID']?$arCity['REGION_ID']:''), $cacheDir))
		{
			$Reviews = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$arGeoFilter = [];

			if(!empty($arCity['REGION_ID']))
			{
				$rsCities = \Bitrix\Iblock\ElementTable::getList([
					'filter' => [
						'IBLOCK_ID' => Config\Catalog::IBLOCK_GLOBUS,
						'IBLOCK_SECTION_ID' => $arCity['REGION_ID']
					],
					'select' => ['XML_ID']
				]);

				while($city = $rsCities->fetch())
				{
					$arGeoFilter[] = $city['XML_ID'];
				}
			}

			$res = \CIBlockElement::GetList(
				[
					'PROPERTY_grade' => 'DESC',
					'PROPERTY_date' => 'DESC'
				],
				[
					"IBLOCK_ID" => 13,
					"PROPERTY_ITEM_LINK" => $productID,
					"ACTIVE" => "Y",
					"PROPERTY_yandex_id" => false
				],
				false,
				false
			);

			while($ob = $res->GetNextElement())
			{
				$fields = $ob->GetFields();
				$props = $ob->GetProperties();
				$value = [];

				foreach($props as $key=>$prop)
				{
					$value[$key] = $prop['VALUE'];
				}

				$Reviews['REVIEWS'][$fields['ID']] = $value;
				$Reviews['TOTAL_RATE'] += intval($value['grade'])+3;

				if($arGeoFilter)
				{
					if($value['CITY_XML_ID'])
					{
						if(in_array($value['CITY_XML_ID'], $arGeoFilter))
						{
							$Reviews['GEO_REVIEWS'][$fields['ID']] = $value;
						}
					}
					elseif(!empty($value['city']))
					{
						if($value['city'] == $arCity['NAME'])
						{
							$Reviews['GEO_REVIEWS'][$fields['ID']] = $value;
						}
					}
				}
			}

			if (!empty($Reviews['REVIEWS']))
			{
				$Reviews['TOTAL_RATE'] = round($Reviews['TOTAL_RATE'] / count($Reviews['REVIEWS']));
			}

			$cache->endDataCache($Reviews);
		}

		$Reviews['REVIEWS_CNT'] = count($Reviews['REVIEWS']);

		return $Reviews;
	}

	public static function closeHtmlTagsDescription($str = '')
	{
		$str = mb_convert_encoding($str, 'HTML-ENTITIES', "WINDOWS-1251");

		$doc = new \DOMDocument();
		$doc->loadHTML($str);
		$str = $doc->saveHTML();

		$doc2 = new \DOMDocument();
		$doc2->loadHTML($str);
		$body = $doc2->getElementsByTagName('body');

		$str = '';

		foreach($body as $item)
		{
			/** @var \DOMElement $item */
			$str .= $doc2->saveHTML($item);
		}

		$str = str_replace(['<body>', '</body>'], '', $str);

		return $str;
	}
}