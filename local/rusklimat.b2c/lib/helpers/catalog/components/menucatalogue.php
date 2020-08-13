<?
namespace Rusklimat\B2c\Helpers\Catalog\Components;

use \Bitrix\Main\Context;
use \Bitrix\Main\Data\Cache;
use Rusklimat\B2c\Helpers\Catalog\Product;
use Rusklimat\B2c\internals\Catalog\ProductsAvailablesTable;

class MenuCatalogue
{
	static $sectionsAllow = [];

	/**
	 * @param int $ID
	 * @param array $arSectionHaveCheckActive
	 *
	 * @return array
	 */
	public static function getCatalogSection($ID = 0, $arSectionHaveCheckActive = [])
	{
		global $CACHE_MANAGER;

		static $result = [];

		if(empty($result) && $ID && !empty($arSectionHaveCheckActive))
		{
			$cache = Cache::createInstance();

			$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

			if ($cache->initCache(3600*0, $ID, $cacheDir))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$rsSections = \CIBlockSection::GetList(
					[
						'LEFT_MARGIN' => 'ASC'
					],
					[
						'IBLOCK_ID' => $ID,
						'ID' => $arSectionHaveCheckActive,
						'ACTIVE' => 'Y',
						'GLOBAL_ACTIVE' => 'Y'
					],
					false,
					[
						'ID',
						'SECTION_PAGE_URL'
					]
				);

				while ($arSection = $rsSections->GetNext())
				{
					$result[$arSection['ID']] = $arSection;
				}

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag('iblock_id_'.$ID);
				$CACHE_MANAGER->EndTagCache();

				$cache->endDataCache($result);
			}
		}

		return $result;
	}

	/**
	 * @param array $IDS
	 * @param array $arParams
	 *
	 * @return array
	 *
	 * @deprecated
	 */
	public static function getCatalogCnt($IDS = [], $arParams = [])
	{
		global $CACHE_MANAGER;

		$result = [];

		if($IDS)
		{
			$cache = Cache::createInstance();

			$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

			if ($cache->initCache(3600, serialize($IDS), $cacheDir))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$arFilter = [
					"IBLOCK_ID" => $arParams['IBLOCK_ID_LINK'],
					"SECTION_ID" => $IDS,
					"INCLUDE_SUBSECTIONS" => "Y"
				];

				if (!empty($arParams['FILTER']))
					$arFilter = array_merge($arFilter, $arParams['FILTER']);

				$res = \CIBlockElement::GetList(
					[],
					$arFilter,
					['IBLOCK_SECTION_ID'],
					false,
					["ID", "IBLOCK_SECTION_ID"]
				);

				while($arFields = $res->Fetch())
				{
					$result[$arFields['IBLOCK_SECTION_ID']] = $arFields['CNT'];
				}

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag('iblock_id_'.$arParams['IBLOCK_ID_LINK']);
				$CACHE_MANAGER->EndTagCache();

				$cache->endDataCache($result);
			}
		}

		return $result;
	}

	/**
	 * @param array $sections
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 *
	 * Возвращает ID разделов, в которых есть активные товары
	 *
	 * @TODO - всеравно надо будет как-то оптимизировать
	 */
	public static function getActiveSectionsById($sections = [])
	{
		global $CACHE_MANAGER, $arGeo;

		static $result = [];

		if($sections && empty($result))
		{
			$cache = Cache::createInstance();

			$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

			$cacheKey = [
				$arGeo['CUR_CITY']['CITY_WH_GENERAL'],
				$arGeo['CUR_CITY']['PREORDER_CHECK'],
				$arGeo['CUR_CITY']['PREPAY_CHECK'],
				$sections
			];

			$cacheKey = serialize($cacheKey);

			if ($cache->initCache(3600*0, $cacheKey, $cacheDir))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$rsSections = \Bitrix\Iblock\SectionTable::getList([
					'filter' => [
						'IBLOCK_ID' => 8,
						'ID' => $sections,
						'=ACTIVE' => 'Y',
						'=GLOBAL_ACTIVE' => 'Y',
					],
					'select' => [
						'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'
					],
					'order' => [
						'LEFT_MARGIN' => 'ASC'
					]
				]);

				while($section = $rsSections->fetch())
				{
					if(in_array($section['ID'], self::$sectionsAllow))
					{
						$result[] = $section['ID'];
					}
					elseif(self::checkActiveSection($section))
					{
						if($section['IBLOCK_SECTION_ID'])
							self::$sectionsAllow[$section['IBLOCK_SECTION_ID']] = $section['IBLOCK_SECTION_ID'];

						self::$sectionsAllow[$section['ID']] = $section['ID'];

						$result[] = $section['ID'];
					}
				}

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag('iblock_id_8');
				$CACHE_MANAGER->EndTagCache();

				$cache->endDataCache($result);
			}
		}

		return $result;
	}

	/**
	 * @param $section
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkActiveSection($section)
	{
		GLOBAL $arGeo;

		$result = false;

		$ids = \Rusklimat\B2c\Helpers\Catalog\Sections::GetAllChilds($section, 8, 0);

		if($ids)
		{
			$rsStatus = ProductsAvailablesTable::getList([
				'filter' => [
					'CITY_ID' => $arGeo['CUR_CITY']['ID'],
					'=STATUS' => [1,2],
					'=ELEMENT.IBLOCK_SECTION_ID' => $ids
				],
				'select' => [
					'ELEMENT.IBLOCK_SECTION_ID'
				],
				'group' => [
					'ELEMENT.IBLOCK_SECTION_ID'
				]
			]);

			while($st = $rsStatus->fetch())
			{
				$st['IBLOCK_SECTION_ID'] = $st['RUSKLIMAT_B2C_INTERNALS_CATALOG_PRODUCTS_AVAILABLES_ELEMENT_IBLOCK_SECTION_ID'];
				self::$sectionsAllow[$st['IBLOCK_SECTION_ID']] = $st['IBLOCK_SECTION_ID'];

				$result = true;
			}
		}

		return $result;
	}
}