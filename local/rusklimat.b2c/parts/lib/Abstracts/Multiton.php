<?php

namespace Rusklimat\B2c\Parts\Abstracts;

abstract class Multiton
{
	/**
	 * @var array
	 */
	protected static $instances = array();
	
	
	/**
	 * Возвращает экземпляр класса, из которого вызван
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		$className = static::getClassName();
		if (!(self::$instances[$className] instanceof $className)) {
			self::$instances[$className] = new $className();
		}
		return self::$instances[$className];
	}
	
	/**
	 * Удаляет экземпляр класса, из которого вызван
	 *
	 * @return void
	 */
	public static function removeInstance()
	{
		$className = static::getClassName();
		if (array_key_exists($className, self::$instances)) {
			unset(self::$instances[$className]);
		}
	}
	
	/**
	 * Возвращает имя экземпляра класса
	 *
	 * @return string
	 */
	final protected static function getClassName()
	{
		return get_called_class();
	}
	
	/**
	 * Конструктор закрыт
	 */
	protected function __construct()
	{
	}
	
	/**
	 * Клонирование запрещено
	 */
	final protected function __clone()
	{
	}
	
	/**
	 * Сериализация запрещена
	 */
	final protected function __sleep()
	{
	}
	
	/**
	 * Десериализация запрещена
	 */
	final protected function __wakeup()
	{
	}
}