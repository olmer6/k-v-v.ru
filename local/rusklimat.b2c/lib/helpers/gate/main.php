<?
namespace Rusklimat\B2c\Helpers\Gate;

use \Bitrix\Main\Web\HttpClient;

/**
 * Class Main
 * @package Rusklimat\B2c\Helpers\Gate
 *
 * Класс для обращения к шлюзу вне модуля обмена
 */

class Main
{
	static $timeOut = 10;
	static $apiKeys = [
		'soap' => 'UngwNGM5Tjg0WXFjZlZ2N1Z3djlGdz09'
	];

	/**
	 * @param $url
	 * @param array $query
	 * @param array $params
	 *
	 * @return array|mixed
	 */
	public static function get($url, array $query, array $params = [])
	{
		$result = [];

		$cache = \Bitrix\Main\Data\Cache::createInstance();

		if ($cache->initCache((int) $params['cacheTime'], serialize(func_get_args()), '/'.\Bitrix\Main\Context::getCurrent()->getSite().'/'.__CLASS__.'/'.__FUNCTION__))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$apiKey = \COption::GetOptionString("rusklimat.exchange", "RK_EXCHANGE_MAIN_SERVER_API_KEY");

			$httpClient = new HttpClient();

			$httpClient->setHeader('Content-Type', 'application/json', true);
			$httpClient->setHeader('Authorization', ($params['apiKey']?self::$apiKeys[$params['apiKey']]:$apiKey), true);

			$httpClient->setTimeout(self::$timeOut);

			$httpClient->get('http://api.rusklimat.ru/'.$url.'?'.http_build_query($query));

			if($httpClient->getResult())
			{
				$result = json_decode($httpClient->getResult(), true);
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}
