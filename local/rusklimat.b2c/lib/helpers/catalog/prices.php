<?
namespace Rusklimat\B2c\Helpers\Catalog;

class Prices
{
	/**
	 * @param $price
	 * @param string $currency
	 * @param bool $useTemplate
	 *
	 * @return mixed|null|string|string[]
	 */
	public static function Format($price, $currency = 'RUB', $useTemplate = false)
	{
		$result = \CCurrencyLang::CurrencyFormat($price, $currency, $useTemplate);

		$result = str_replace(' ', '&nbsp;', $result);

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getTypesList()
	{
		$result = [];

		$cache = \Bitrix\Main\Data\Cache::createInstance();

		if ($cache->initCache(3600, 1, '/'.\Bitrix\Main\Context::getCurrent()->getSite().'/f/'.__CLASS__.'/'.__FUNCTION__))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$dbPriceType = \CCatalogGroup::GetList(
				["SORT" => "ASC"],
				[]
			);

			while ($arPriceType = $dbPriceType->Fetch())
			{
				$result[$arPriceType['NAME']] = $arPriceType;
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}