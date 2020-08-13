<?
namespace Rusklimat\B2c\Helpers\Catalog;

use \Rusklimat\B2c\Config;

class Product
{
	/**
	 * @param $id
	 * @param array $props
	 * @param bool $whGeneral - склад
	 * @param bool $geoPreOrderCheck
	 * @param bool $geoPrePayCheck
	 * @param int $whAmount - если знаем остаток на складе, то передаем его
	 *
	 * @return array
	 *
	 * Alex: Согласно новым требованиям "сообщить о поступлении" ставится только когда: нет на складе + не стоит под заказ + стоит предоплата
	 * сделаем всю логику по наличию тут, а не в template
	 * Изначально ставим все доступными для покупки
	 *
	 * RK_AVAILABLE
	 * 		= 0 нет в наличи
	 * 		= 1 в наличи / под заказа
	 *
	 * TODO: необходимо учитывать это в смартфильтре local/templates/rusklimat/components/bitrix/catalog/catalog/section.php !!!
	 */
	public static function getProductStatus($id, $props = [], $whGeneral = false, $geoPreOrderCheck = false, $geoPrePayCheck =  false, $whAmount = null)
	{
		$result = [
			'RK_AVAILABLE' => 1,
			'RK_STATUS' => 'AVAILABLE',
			'DISCONTINUED' => 'N',
			'PREORDER' => 'N',
			'PREPAY' => 'N',
			'RK_AMOUNT' => 0,
			'error' => false
		];

		#Костыль для умного дома
		if(in_array($id, self::getAlwaysAvailable()))
		{
			return $result;
		}

		if(!isset($props['PREORDER_CHECK']) || !isset($props['PREPAY_CHECK']) || !isset($props['IMPORT_NSI_ACTIVE']))
		{
			$result['error'] = true;
		}

		if(in_array($geoPreOrderCheck, $props['PREORDER_CHECK']['VALUE']))
			$result['PREORDER'] = "Y";

		if(in_array($geoPrePayCheck, $props['PREPAY_CHECK']['VALUE']))
			$result['PREPAY'] = "Y";

		if(is_numeric($whAmount))
		{
			$result['RK_AMOUNT'] = $whAmount;
		}
		else
		{
			if($whGeneral)
			{
				$amounts = \getStoreProduct($id, [$whGeneral]);

				if(!empty($amounts))
					$result['RK_AMOUNT'] = $amounts[$whGeneral];
			}
		}

		if($result['RK_AMOUNT'] == 0 && $result['PREORDER'] == "N" && $result['PREPAY'] == "Y")
		{
			$result["RK_AVAILABLE"] = 0;
			$result["RK_STATUS"] = '';
		}

		if($result["RK_AVAILABLE"])
		{
			if($result['RK_AMOUNT'] == 0)
			{
				if( ($result['PREORDER'] == "N" && $result['PREPAY'] == "N") || ($result['PREORDER'] == "Y" && $result['PREPAY'] == "Y") )
				{
					$result["RK_STATUS"] = "PREORDER";
				}
			}
		}

		if(is_numeric($props['IMPORT_NSI_ACTIVE']['VALUE'] ) && $props['IMPORT_NSI_ACTIVE']['VALUE'] == 0 && $result['RK_AMOUNT'] == 0)
		{
			$result['DISCONTINUED'] = 'Y';
		}


		return $result;
	}

	/**
	 * @return array
	 *
	 * #66052
	 *
	 * Товары которые всегда доступны
	 */
	public static function getAlwaysAvailable()
	{
		$result = [
			6983713, 6981658, 6981653, 6981651, 6981646, 6981642, 6981576, 6981645, 6981639, 6981643,
			6981572, 6983711, 6981635, 6981640, 6981641, 6984659, 6981657, 6981575, 6981656, 6981644,
			6981636, 6984660, 6981573, 6981647, 6981648, 6983710, 6981652, 6996052, 6981655, 6981654,
			6981571, 6981574, 6981649, 6981659, 6981637, 6981638, 6981650,

			7034441, 7034442, 7034448, 7034449, 7034450, 7034446
		];

		return $result;
	}
}