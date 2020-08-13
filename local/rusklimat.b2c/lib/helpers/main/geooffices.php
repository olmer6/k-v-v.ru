<?
namespace Rusklimat\B2c\Helpers\Main;

use \Bitrix\Main\Context;
use \Bitrix\Main\Data\Cache;

class GeoOffices
{
	/**
	 * @return array
	 *
	 * Возвращает офиссы по текущей геолокации
	 * Используется для вывода на карте яндекс и в списках
	 */
	public static function getByGeo()
	{
		GLOBAL $arGeo, $CACHE_MANAGER;

		$result = [];

		if(!empty($arGeo['CUR_CITY']['FLL_ID']))
		{
			$cache = Cache::createInstance();

			$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;

			if ($cache->initCache(3600, $arGeo['CUR_CITY']['FLL_ID'], $cacheDir))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$region = \CIBlockElement::GetList(
					[],
					[
						'IBLOCK_ID' => 38,
						'ACTIVE' => 'Y',
						'XML_ID' => $arGeo['CUR_CITY']['FLL_ID']
					],
					false,
					['nTopCount' => 1],
					['ID', 'NAME', 'PROPERTY_PICKUPPOINT_GLOBUSRK_ID']
				)->Fetch();

				if(!empty($region['PROPERTY_PICKUPPOINT_GLOBUSRK_ID_VALUE']))
				{
					$rsElements = \CIBlockElement::GetList(
						[],
						[
							'IBLOCK_ID' => 38,
							'ACTIVE' => 'Y',
							'PROPERTY_PICKUPPOINT_GLOBUSRK_ID' => $region['PROPERTY_PICKUPPOINT_GLOBUSRK_ID_VALUE']
						],
						false,
						false,
						['ID', 'NAME', 'PROPERTY_PICKUPPOINT_GLOBUSRK_ID', 'PROPERTY_FULLNAME', 'PROPERTY_PLACE', 'PROPERTY_PICKUPPOINT_ADRESS', 'PROPERTY_PHONE']
					);

					while($element = $rsElements->Fetch())
					{
						if($element['PROPERTY_PLACE_VALUE'] && strlen($element['PROPERTY_FULLNAME_VALUE']) > 2)
						{
							$result[$element['ID']] = $element;
						}
					}
				}

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag('iblock_id_38');
				$CACHE_MANAGER->EndTagCache();

				$cache->endDataCache($result);
			}
		}

		return $result;
	}
}