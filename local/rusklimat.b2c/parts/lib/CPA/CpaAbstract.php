<?php


namespace Rusklimat\B2c\Parts\CPA;

use Bitrix\Main\Application;
use Rusklimat\B2c\Helpers\Tools;

abstract class CpaAbstract
{
	protected $clickId;
	protected $cookieLifeTime = 30 * 24 * 3600;
	
	protected function getGetParams()
	{
		return $_GET;
	}
	
	public function processRequest()
	{
		if ($this->checkGetParams())
		{
			$this->saveCookieParams();
		}
	}
	
	protected function saveCookieParams()
	{
		//		echo "<xmp>";
		//		print_r([
		//			$this->getCookiePartnerName(),
		//			$this->getPartnerName(),
		//			$this->cookieLifeTime
		//		]);
		//		echo "</xmp>";
		//
		//		echo "<xmp>";
		//		print_r([
		//			$this->getCookieClickId(),
		//			$this->clickId,
		//			$this->cookieLifeTime
		//		]);
		//		echo "</xmp>";
		
		setcookie(
			$this->getCookiePartnerName(),
			$this->getPartnerName(),
			time() + $this->cookieLifeTime
		);
		$_COOKIE[$this->getCookiePartnerName()] = $this->getPartnerName();
		
		setcookie(
			$this->getCookieClickId(),
			$this->clickId,
			time() + $this->cookieLifeTime
		);
		$_COOKIE[$this->getCookiePartnerName()] = $this->getPartnerName();
	}
	
	public function getCookiePartnerName()
	{
		return "RUSKLIMAT_CPA_PARTNER";
	}
	
	public function getCookieClickId()
	{
		return "RUSKLIMAT_CPA_CLICK_ID";
	}
	
	abstract public function checkGetParams();
	abstract public function getPartnerName();
	
	
	public function onOrderCreate($orderId, $arBasket, $arOrder)
	{
		if ($this->checkCookie())
		{
			$this->sendOrder($orderId, $arBasket, $arOrder);
		}
	}
	
	protected function checkCookie()
	{
		
		//		echo "<xmp>";
		//		print_r([
		//			$_COOKIE[ $this->getCookiePartnerName() ],
		//			$_COOKIE[ $this->getCookieClickId() ]
		//		]);
		//		echo "</xmp>";
		
		
		if ($partnerName = $_COOKIE[ $this->getCookiePartnerName() ])
		{
			if ($partnerName == $this->getPartnerName())
			{
				$clickId = $_COOKIE[ $this->getCookieClickId() ];
				
				if (!empty($clickId))
				{
					$this->clickId = $clickId;
					return true;
				}
			}
		}
		
		return false;
	}
	
	protected abstract function sendOrder($orderId, $arBasket, $arOrder);
	
	public abstract function getCommonScript();
}