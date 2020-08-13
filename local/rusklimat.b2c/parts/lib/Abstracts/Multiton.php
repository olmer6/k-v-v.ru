<?php

namespace Rusklimat\B2c\Parts\Abstracts;

abstract class Multiton
{
	/**
	 * @var array
	 */
	protected static $instances = array();
	
	
	/**
	 * ���������� ��������� ������, �� �������� ������
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
	 * ������� ��������� ������, �� �������� ������
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
	 * ���������� ��� ���������� ������
	 *
	 * @return string
	 */
	final protected static function getClassName()
	{
		return get_called_class();
	}
	
	/**
	 * ����������� ������
	 */
	protected function __construct()
	{
	}
	
	/**
	 * ������������ ���������
	 */
	final protected function __clone()
	{
	}
	
	/**
	 * ������������ ���������
	 */
	final protected function __sleep()
	{
	}
	
	/**
	 * �������������� ���������
	 */
	final protected function __wakeup()
	{
	}
}