<?
namespace Rusklimat\B2c\Helpers\Sale\Order;

use Bitrix\Sale\Internals\OrderTable;

class Captcha
{
	/**
	 * @return bool
	 *
	 * Проверяем, если с последнего заказа прошло меньше 5 минут, то говорим, что необходима капч
	 */
	public static function isRequired()
	{
		GLOBAL $USER;

		$result = false;

		$maxTime = 60*30;

		/* Проверка по последнему заказу с этого IP */

		$rsEvent = \CEventLog::GetList(
			['ID' => 'DESC'],
			[
				'SEVERITY' => 'INFO',
				'AUDIT_TYPE_ID' => 'ORDER_CREATED',
				'MODULE_ID' => 'rusklimat.b2c',
				'REMOTE_ADDR' => $_SERVER["REMOTE_ADDR"],
			],
			['nTopCount' => 1]
		)->Fetch();

		if(!empty($rsEvent['TIMESTAMP_X']))
		{
			$time = time()-strtotime($rsEvent['TIMESTAMP_X']);

			if($time < $maxTime)
				$result = true;
		}

		/* Блокировка по агенту */

		$arBlockAgent = [
			'accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
		];

		if(!empty($arBlockAgent) && in_array(ToLower($_SERVER["HTTP_USER_AGENT"]), $arBlockAgent))
			$result = true;

		/* Блокировка по ip */

		$arBlockIp = [];

		if(!empty($arBlockIp) && in_array(ToLower($_SERVER["REMOTE_ADDR"]), $arBlockIp))
			$result = true;

		/* По пользователю */

		if($USER->IsAuthorized())
		{
			$arLastOrder = OrderTable::getList([
				'filter' => [
					'=USER_ID' => $USER->GetID()
				],
				'order' => ['ID' => 'DESC'],
				'limit' => 1
			])->fetch();

			if(!empty($arLastOrder))
			{
				$time = time()-strtotime($arLastOrder['DATE_INSERT']->getTimestamp());

				if($time < $maxTime)
					$result = true;
			}
		}

		return $result;
	}
}