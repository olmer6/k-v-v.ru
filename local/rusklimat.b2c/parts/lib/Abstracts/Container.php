<?php


namespace Rusklimat\B2c\Parts\Abstracts;


class Container
{
	protected $__error = null;
	
	public function isSuccess()
	{
		if (is_null($this->__error))
			return true;
		
		return false;
	}
	
	public function getError()
	{
		return $this->__error;
	}
	
	protected function addError($error)
	{
		if (!is_array($this->__error))
			$this->__error = [];
		
		$this->__error[] = $error;
	}
}