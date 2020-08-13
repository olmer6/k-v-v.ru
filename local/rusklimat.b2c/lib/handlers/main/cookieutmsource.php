<?php
namespace Rusklimat\B2c\Handlers\Main;

use \Rusklimat\B2c\Helpers;

/**
 * Class CookieUtmSource
 * @package Rusklimat\B2c\Handlers\Main
 */
class CookieUtmSource
{
	public static function Init()
	{
		AddEventHandler('main', 'OnPageStart', [__CLASS__, 'setCookie']);
	}

	public static function setCookie()
	{
		$request  = \Bitrix\Main\Context::getCurrent()->getRequest();

		if(!empty($request->get('utm_source')))
		{
			$oldConfig = self::getConfig(Helpers\Tools::getCookie('utm_source'));
			$newConfig = self::getConfig($request->get('utm_source'));

			Helpers\Tools::setCookie('utm_source', $request->get('utm_source'));
			Helpers\Tools::setCookie('utm_medium', $request->get('utm_medium'));
			Helpers\Tools::setCookie('utm_campaign', $request->get('utm_campaign'));

			if(!empty($oldConfig['addonCookies']) && $oldConfig['utm_source'] != $newConfig['utm_source'])
			{
				foreach($oldConfig['addonCookies'] as $addonOld)
				{
					Helpers\Tools::setCookie($oldConfig['utm_source'].'__'.$addonOld, '', -1);
				}
			}

			if(!empty($newConfig['addonCookies']))
			{
				foreach($newConfig['addonCookies'] as $addonNew)
				{
					Helpers\Tools::setCookie($newConfig['utm_source'].'__'.$addonNew, $request->get($addonNew));
				}
			}
		}
	}

	/**
	 * @param string $code
	 *
	 * @return array|mixed
	 */
	private static function getConfig($code = '')
	{
		$config = [];

		$config['default'] = [
			'utm_source' => $code,
			'lifetime' => 86400*30
		];

		/*$config['cityads'] = [
			'utm_source' => 'cityads',
			'lifetime' => 86400*30,
			'addonCookies' => [
				'click_id'
			]
		];*/

		if(!empty($config[$code]))
			$result = $config[$code];
		else
			$result = $config['default'];

		return $result;
	}
}