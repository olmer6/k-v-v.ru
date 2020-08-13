<?
namespace Rusklimat\B2c\Agents\Sale;

use Bitrix\Main\Diag\Debug;

/**
 * Class SendOrdersToCrm
 * @package Rusklimat\B2c\Agents\Sale
 *
 * #27638, Агент отправляет в CRM заказы, которые не были отправлены
 */
class SendOrdersToCrm
{
	/**
	 * @return string
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function Execute()
	{
		GLOBAL $USER_FIELD_MANAGER;

		$rsOrders = \Bitrix\Sale\Internals\OrderTable::getList([
			'filter' => [
				'UF_ORDER_1C_SENDED' => false,
				'<DATE_INSERT' => (new \Bitrix\Main\Type\DateTime())->add('-10 MINUTE')
			],
			'select' => ['ID', 'UF_ORDER_1C_SENDED'],
			'order' => ['ID' => 'DESC'],
			'limit' => 10
		]);

		while($order = $rsOrders->fetch())
		{
			if(!isDev())
			{
				$sendToCrm = new \Rusklimat\B2c\Helpers\Sale\Order\SendToCrm($order['ID']);
				$sendToCrmResult = $sendToCrm->send();

				if($sendToCrmResult)
				{
					$USER_FIELD_MANAGER->Update('ORDER', $order['ID'], ['UF_ORDER_1C_SENDED' => 1]);

					Debug::writeToFile(date("d.m.Y H:i:s").' order.id:	'.$order['ID'], false, 'local/logs/agents.Sale.SendOrdersToCrm.log');
				}
				else
				{
					Debug::writeToFile(date("d.m.Y H:i:s").' order.id:	'.$order['ID'].'	error', false, 'local/logs/agents.Sale.SendOrdersToCrm.log');
				}
			}
		}

		return __CLASS__.'::'.__FUNCTION__.'();';
	}
}