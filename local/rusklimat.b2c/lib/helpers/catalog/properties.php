<?
namespace Rusklimat\B2c\Helpers\Catalog;

use \Bitrix\Iblock\PropertyTable;
use \Rusklimat\B2c\Config;
use \Bitrix\Main\Data\Cache;
use \Bitrix\Main\Context;
use \Bitrix\Iblock\ElementTable;

class Properties
{
	public static function getTitlesList($ID = 0)
	{
		GLOBAL $PROPERTIES_IBLOCK_ID;

		static $hints = [];
		static $sectionProps = [];

		$result = [];

		if($ID)
		{
			$cache = Cache::createInstance();

			if ($cache->initCache(7200, $ID, '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$element = ElementTable::getList([
					'filter' => [
						'=ID' => (int) $ID
					],
					'select' => ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'IBLOCK_SECTION.XML_ID'],
					'limit' => 1
				])->fetch();

				if($element['IBLOCK_SECTION_ID'] && $element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID'])
				{
					$arFilterProps = [];

					if(!isset($sectionProps[$element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']]))
					{
						$sectionProps[$element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']] = [];

						$rsProperties = \CIBlockElement::GetList(
							['SORT' => 'ASC'],
							["IBLOCK_ID" => $PROPERTIES_IBLOCK_ID, 'PROPERTY_TILE_CATS_XML' => $element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']],
							false,
							false,
							['ID', 'NAME', 'SORT', 'XML_ID', 'CODE']
						);

						while ($arProp = $rsProperties->Fetch())
						{
							$sectionProps[$element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']][] = $arProp;
						}
					}

					if(!empty($sectionProps[$element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']]))
					{
						foreach ($sectionProps[$element['IBLOCK_ELEMENT_IBLOCK_SECTION_XML_ID']] as $arProp)
						{
							$arFilterProps[] = 'PROPERTY_' . $arProp['CODE'];

							$result[$arProp['CODE']] = $arProp;

							if(!empty($hints[ToUpper($arProp['XML_ID'])]))
							{
								$result[$arProp['CODE']]['HINT'] = $hints[ToUpper($arProp['XML_ID'])];
							}
							else
							{
								$property = PropertyTable::getList([
									'filter' => [
										'ACTIVE' => 'Y',
										'IBLOCK_ID' => (int) $element['IBLOCK_ID'],
										'=XML_ID' => $arProp['XML_ID']
									],
									'select' => ['ID', 'XML_ID','HINT'],
									'limit' => 1
								])->fetch();

								if($property)
								{
									$hints[ToUpper($property['XML_ID'])] = $property['HINT'];
									$result[$arProp['CODE']]['HINT'] = $property['HINT'];
								}
							}
						}
					}

					if($result)
					{
						$res = \CIBlockElement::GetList(
							['SORT' => 'ASC'],
							["IBLOCK_ID" => $element['IBLOCK_ID'], 'ID' => $element['ID']],
							false,
							false,
							array_merge(['ID', 'CODE'], $arFilterProps)
						);

						while ($arElement = $res->Fetch())
						{
							foreach ($result as $k => $p)
							{
								if (!empty($arElement['PROPERTY_' . strtoupper($p['CODE']) . '_VALUE']) && ($arElement['PROPERTY_' . strtoupper($p['CODE']) . '_VALUE'] != "<>"))
								{
									$result[$k]['VALUE'] = $arElement['PROPERTY_' . strtoupper($p['CODE']) . '_VALUE'];
								}
								else
								{
									unset($result[$k]);
								}
							}
						}
					}
				}

				$cache->endDataCache($result);
			}
		}

		return $result;
	}
}