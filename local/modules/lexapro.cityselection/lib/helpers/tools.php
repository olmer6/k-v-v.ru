<?
namespace LexaProCitySelection\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Cookie;

class Tools
{
	public static function setCookie($name, $value, $expires = null)
	{
		$context = Application::getInstance()->getContext();

		$cookie = new Cookie($name, $value, $expires);
		$cookie->setHttpOnly(false);

		$context->getResponse()->addCookie($cookie);
		$context->getResponse()->flush('');
	}

	public static function getCookie($name)
	{
		$result = htmlspecialcharsbx(Application::getInstance()->getContext()->getRequest()->getCookie($name));

		return $result;
	}
}