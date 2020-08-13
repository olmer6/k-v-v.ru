<?
namespace Rusklimat\B2c\Helpers\Catalog\Components;

use \Bitrix\Main\Context;
use \Bitrix\Main\Data\Cache;

class Section
{
	public static function getParents($arSection = [])
	{
		global $CACHE_MANAGER;

		$result = [];

		if($arSection['ID'])
		{
			$cache = Cache::createInstance();

			$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

			if ($cache->initCache(3600, $arSection['ID'], $cacheDir))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$res = \CIBlockSection::GetList(
					[],
					[
						"IBLOCK_ID" => $arSection['IBLOCK_ID'],
						'>LEFT_MARGIN' => $arSection['LEFT_MARGIN'],
						'<RIGHT_MARGIN' => $arSection['RIGHT_MARGIN'],
						'>DEPTH_LEVEL' => $arSection['DEPTH_LEVEL']
					],
					false,
					[
						"ID", "XML_ID", "IBLOCK_SECTION_ID", "DEPTH_LEVEL","NAME","SECTION_PAGE_URL" , "DEPTH_LEVEL"
					]
				);

				while($arFields = $res->GetNext())
				{
					$result['IDS'][] = $arFields['ID'];
					$result['XML_IDs'][] = $arFields['XML_ID'];

					$result['SECTIONS'][$arFields['ID']]['NAME'] = $arFields['NAME'];
					$result['SECTIONS'][$arFields['ID']]['DETAIL_PAGE_URL'] = $arFields['SECTION_PAGE_URL'];
				}

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag('iblock_id_'.$arSection['IBLOCK_ID']);
				$CACHE_MANAGER->EndTagCache();

				$cache->endDataCache($result);
			}
		}

		return $result;
	}

	public static function getProductsIDByItemsArray($items = [])
	{
		$result = [];

		if(!empty($items))
		{
			foreach($items as $item)
			{
				$result[] = $item['ID'];
			}
		}

		return $result;
	}

	public static function getProductsXmlIDByItemsArray($items = [])
	{
		$result = [];

		if(!empty($items))
		{
			foreach($items as $item)
			{
				$result[] = $item['XML_ID'];
			}
		}

		return $result;
	}
}