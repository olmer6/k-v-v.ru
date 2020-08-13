<?
namespace Rusklimat\B2c\Helpers\Main;

use \Rusklimat\B2c\Helpers\Main\Geo;
use \Rusklimat\B2c\Helpers\Main\Tools;

class GeoRedirects
{
	/**
	 * @param string $dir
	 *
	 *  Делает редирект на региональную странику, если это необходимо
	 *  Пример: Rusklimat\B2c\Helpers\Main\GeoRedirects::byDir('/internet-shop/how-to-buy/');
	 */
	public static function byDir($dir = '')
	{
		if($dir)
		{
			$geo = Geo::getInstance()->getUserLocation();

			if($geo['CITY_FOLDER'] && $_SERVER['SCRIPT_URL'] == $dir)
			{
				$redirect = '/'.$geo['CITY_FOLDER'].$_SERVER['REQUEST_URI'];
			}

			if(!empty($redirect) && $_SERVER['REQUEST_URI'] != $redirect)
			{
				Tools::LocalRedirect($redirect, false, "301 Moved permanently", false);
				exit;
			}
		}
	}

	/**
	 * Импользуется в событие OnPageStart
	 */
	public static function bySubDomain()
	{
		GLOBAL $GEO_DOMAINS_IBLOCK_ID;

		$redirect = null;

		$url = $_SERVER['SCRIPT_URI'].''.$_SERVER['QUERY_STRING'];

		$arUrl = parse_url($url);

		$domainOrigin = $arUrl['host'];
		$domainNotWww = str_replace(['www.', '2014.'], '', $domainOrigin);

		$arDomain = explode('.', $domainNotWww);

		if(count($arDomain) == 2)
		{
			if($arDomain[0] != 'rusklimat')
			{
				$cityRedirect = \CIBlockElement::GetList(
					[],
					[
						'IBLOCK_ID' => $GEO_DOMAINS_IBLOCK_ID,
						[
							'LOGIC' => 'OR',
							['NAME' => $domainNotWww],
							['NAME' => 'www.'.$domainNotWww]
						]
					],
					false,
					['nTopCount' => 1],
					['ID', 'NAME', 'PROPERTY_CITY']
				)->Fetch();

				if($cityRedirect['PROPERTY_CITY_VALUE'])
				{
					$folder = Geo::getInstance()->getByID($cityRedirect['PROPERTY_CITY_VALUE'])['CITY_FOLDER'];
				}
			}
		}

		if(count($arDomain) == 3 && !in_array($arDomain[0], ['dev', 'prepub']))
		{
			if($arDomain[1] == 'rusklimat')
			{
				$city = Geo::getInstance()->getRedirectFolders()[$arDomain[0]];

				if($city)
				{
					$folder = Geo::getInstance()->getByID($city)['CITY_FOLDER'];
				}

				if(!empty($folder))
				{
					$cityRedirect = \CIBlockElement::GetList(
						[],
						[
							'IBLOCK_ID' => $GEO_DOMAINS_IBLOCK_ID,
							'NAME' => $arUrl['host']
						],
						false,
						['nTopCount' => 1],
						['ID', 'NAME', 'PROPERTY_CITY']
					)->Fetch();

					if($cityRedirect['PROPERTY_CITY_VALUE'])
					{
						$folder = Geo::getInstance()->getByID($cityRedirect['PROPERTY_CITY_VALUE'])['CITY_FOLDER'];
					}
				}
			}
		}

		if(!empty($folder))
		{
			$arUrl['host'] = 'www.rusklimat.ru';

			$arPatch = explode('/',$arUrl['path']);

			if(Geo::getInstance()->getRedirectFolders()[$arPatch[1]])
				unset($arPatch[1]);

			$arUrl['path'] = implode('/', $arPatch);

			if($arPatch[1] != $folder)
			{
				$arUrl['path'] = $folder.($arUrl['path'][0] != '/'?'/':'').$arUrl['path'];
			}

			$redirect = 'https://'.$arUrl['host'].'/'.$arUrl['path'].($arUrl['query']?'?'.$arUrl['query']:'');
		}

		if($redirect && $redirect != $url )
		{
			Tools::LocalRedirect($redirect, false, "301 Moved permanently", false);
		}
	}
}