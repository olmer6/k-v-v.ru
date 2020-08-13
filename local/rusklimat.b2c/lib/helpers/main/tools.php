<?
namespace Rusklimat\B2c\Helpers\Main;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Cookie;

class Tools
{
	/**
	 * @param string $strParam
	 * @param array $arParamKill
	 * @param null $get_index_page
	 *
	 * @return array|bool|null|\SplFixedArray|string|string[]
	 *
	 * Держим ссылки чистыми
	 */
	public static function GetCurPageParam($strParam = '', $arParamKill = [], $get_index_page = null)
	{
		GLOBAL $APPLICATION;

		foreach($_GET as $_get => $_val)
		{
			$_get = ToUpper($_get);

			if(in_array('PAGEN_*', $arParamKill) && substr($_get, 0, 6) == 'PAGEN_')
			{
				$arParamKill[] = $_get;
			}
			elseif(substr($_get, 0, 4) == 'UTM_')
			{
				$arParamKill[] = $_get;
			}
			elseif(in_array($_get,['CLEAR_CACHE']))
			{
				$arParamKill[] = $_get;
			}
		}

		$result = $APPLICATION->GetCurPageParam($strParam, $arParamKill, $get_index_page);

		return $result;
	}

	/**
	 * @param $url
	 * @param bool $skip_security_check
	 * @param string $status
	 * @param bool $cache
	 *
	 * Обёртка для редиректа с возможностью запрета кеширования
	 */
	public static function LocalRedirect($url, $skip_security_check=false, $status="302 Found", $cache = true)
	{
		if(!$cache)
		{
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");
		}

		\LocalRedirect($url, $skip_security_check, $status);
		exit;
	}

	/**
	 * @return bool
	 */
	public static function pageIsCategoryUmnyyDom()
	{
		static $cache;

		$result = false;

		if(isset($cache[$_SERVER['SCRIPT_URL']]))
		{
			$result = $cache[$_SERVER['SCRIPT_URL']];
		}
		else
		{
			$url = explode('/', $_SERVER['SCRIPT_URL']);

			if($url[1] == 'umnyy_dom' || $url[2] == 'umnyy_dom')
			{
				$result = true;
			}
			else
			{
				if(substr($url[1],0, 8) == 'product-')
				{
					$code = $url[1];
				}
				elseif(substr($url[2],0, 8) == 'product-')
				{
					$code = $url[2];
				}

				$element = \CIBlockElement::GetList(
					false,
					[
						'IBLOCK_ID' => 8,
						'SECTION_CODE' => 'umnyy_dom',
						'INCLUDE_SUBSECTIONS' => 'Y',
						'=CODE' => substr($code, 8)
					],
					false,
					false,
					['ID']
				)->Fetch();

				if(!empty($element))
					$result = true;
			}

			$cache[$_SERVER['SCRIPT_URL']] = $result;
		}

		return $result;
	}
}