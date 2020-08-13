<?
namespace Rusklimat\B2c\Helpers\Catalog;

use \Bitrix\Iblock\PropertyTable;
use \Rusklimat\B2c\Config;
use \Bitrix\Main\Data\Cache;
use \Bitrix\Main\Context;
use \Bitrix\Iblock\ElementTable;

class Sections
{
	public static function GetAllChilds($ID = 0, $IBLOCK_ID = 0, $cacheTime = 3600)
	{
		global $CACHE_MANAGER;

		static $resultCache = [];

		$result = [];

		if(!empty($ID) && !empty($IBLOCK_ID))
		{
			if(is_array($ID))
			{
				if(array_key_exists('LEFT_MARGIN', $ID) && array_key_exists('RIGHT_MARGIN', $ID))
				{
					$section = $ID;
					$ID = $section['ID'];
				}
			}

			if(isset($resultCache[$ID]))
			{
				$result = $resultCache[$ID];
			}
			else
			{
				$cache = Cache::createInstance();

				$cacheDir = '/'.Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__;
				$cacheKey = $ID;

				if ($cache->initCache($cacheTime, $cacheKey, $cacheDir))
				{
					$result = $cache->getVars();
				}
				elseif ($cache->startDataCache())
				{
					if(empty($section) && $ID)
					{
						$section = \Bitrix\Iblock\SectionTable::getList([
							'filter' => [
								'ID' => $ID,
								'=ACTIVE' => 'Y',
								'=GLOBAL_ACTIVE' => 'Y',
							],
							'select' => [
								'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'
							]
						])->fetch();
					}
					
					if(!empty($section))
					{
						$rsSections = \Bitrix\Iblock\SectionTable::getList([
							'filter' => [
								'IBLOCK_ID' => $section['IBLOCK_ID'],
								'>=LEFT_MARGIN' => $section['LEFT_MARGIN'],
								'<=RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
								'=ACTIVE' => 'Y',
								'=GLOBAL_ACTIVE' => 'Y',
							],
							'select' => [
								'ID',
							]
						]);

						while($childSection = $rsSections->fetch())
						{
							$result[] = $childSection['ID'];
						}
					}

					$CACHE_MANAGER->StartTagCache($cacheDir);
					$CACHE_MANAGER->RegisterTag('iblock_id_'.$IBLOCK_ID);
					$CACHE_MANAGER->EndTagCache();

					$cache->endDataCache($result);

					$resultCache[$ID] = $result;
				}
			}
		}

		return $result;
	}
}