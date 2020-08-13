<?php


namespace Rusklimat\B2c\Parts\CPA\GdeSlon;


use Kd\Rusklimat\Debugger;
use Kd\Rusklimat\Logger;
use Rusklimat\B2c\Parts\CPA\CpaAbstract;

class Handler extends CpaAbstract
{
	protected $webMasterId;
	
	public function getPartnerName()
	{
		return "gdeSlon";
	}
	
	public function checkGetParams()
	{
		$params = $this->getGetParams();
		
		// ?gsaid=0000&_gs_ref=17ffbcd85ff9ac22d16bd29d5a12e7a19a84f5fc&_gs_cttl=30&utm_campaign=gdeslon&utm_medium=cpa&utm_content=gdeslon&utm_source=gdeslon
		// https://www.rusklimat.ru/?gsaid=41435&_gs_ref=b8faf754a2b6beac1a17713529d9eb127b3695da&_gs_cttl=30&utm_content=b8faf754a2b6beac1a17713529d9eb127b3695da&utm_source=gdeslon&utm_medium=41435
		if (
			isset($params["gsaid"])
			&& $params["utm_source"] == "gdeslon"
			//			&& $params["utm_campaign"] == "gdeslon"
		)
		{
			$this->webMasterId = $params["gsaid"];
			$this->clickId = $params["_gs_ref"];
			$this->cookieLifeTime = $params["_gs_cttl"] * 24 * 3600;
			
			//			Logger::getInstance()->putLog([
			//				$params
			//			]);
			
			return true;
		}
		
		return false;
	}
	
	public function sendOrder($orderId, $arBasket, $arOrder)
	{
		$arProducts = [];
		
		foreach($arBasket as $arBasketItem)
		{
			$arProducts[] = [
				"article" => $arBasketItem["PRODUCT_ID"],
//				"article" => "001",
				"price" => number_format($arBasketItem['PRICE'], 2, '.', ''),
				"quantity" => $arBasketItem["QUANTITY"]
			];
		}
		
		$arProducts[] = [
			"article" => "001",
			"price" => $arOrder['PRICE']
		];
		
		$url = "https://". Config::ACCOINT_ID . ":" . Config::API_KEY ."@www.gdeslon.ru/api/operate/postbacks/";
		
		$data = [
			"root" => [
				"orders" => [
					"order" => [
						[
							"order_id" =>  $orderId,
							"token" =>  $this->clickId,
							"status" =>  "0",//0- потенциальный 3 — подтвержден
							"products" =>  [
								"product" => $arProducts
							]
						]
					]
				]
			]
		];
		
		$httpClient = new \Bitrix\Main\Web\HttpClient();
		$httpClient->setHeader("Content-Type", "application/json");
		$result = $httpClient->post($url, json_encode($data));
		
		//		Logger::getInstance()->putLog([
		//			$url,
		//			$data,
		//			json_encode($data),
		//			$result
		//		]);
	}
	
	
	public function getCommonScript()
	{
		return '<script async="true" type="text/javascript" src="https://www.gdeslon.ru/landing.js?mid='. Config::ACCOINT_ID .'"></script>';
	}
}