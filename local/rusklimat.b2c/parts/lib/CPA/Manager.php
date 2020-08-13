<?php


namespace Rusklimat\B2c\Parts\CPA;


use Kd\Rusklimat\Debugger;
use Rusklimat\B2c\Parts\Abstracts\Multiton;

class Manager extends Multiton
{
	protected $handlers;
	protected $objects;
	
	protected function __construct()
	{
		$sep = "\\";
		$namespace = __NAMESPACE__ . $sep;
		
		$this->handlers = [
			"GdeSlon",
		
		];
		
		foreach ($this->handlers as $handler)
		{
			$className = $sep . $namespace . $handler . $sep ."Handler";
			$this->objects[$handler] = new $className();
		}
	}
	
	/**
	 * разместить в хедере или в ините, чтобы на каждой странице проверка на параметры входа срабатывала
	 */
	public function onStart()
	{
		/** @var CpaAbstract $object */
		foreach ($this->objects as $object)
		{
			$object->processRequest();
		}
	}
	
	/**
	 * разместить в confirm.php для отправки заказа партнеру
	 *
	 * @param $orderId
	 * @param $arBasket
	 */
	public function onOrderCreate($orderId, $arBasket, $arOrder)
	{
		/** @var CpaAbstract $object */
		foreach ($this->objects as $object)
		{
			$object->onOrderCreate($orderId, $arBasket, $arOrder);
		}
	}
	
	public function addCommonScripts()
	{
		/** @var CpaAbstract $object */
		foreach ($this->objects as $object)
			$GLOBALS["APPLICATION"]->addHeadString($object->getCommonScript());
	}
	
}