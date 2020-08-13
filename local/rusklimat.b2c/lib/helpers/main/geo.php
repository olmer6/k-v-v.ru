<?
namespace Rusklimat\B2c\Helpers\Main;

use \Bitrix\Main\Application;
use \Bitrix\Main\Context;
use \Bitrix\Main\Data\Cache;
use \Bitrix\Iblock\SectionTable;
use \Rusklimat\B2c\Config;
use \Rusklimat\B2c\Helpers;
use \Rusklimat\B2c\Helpers\Main\Tools;

/**
 * Всё что связано с геолокацией пользователя на сайте
 * На основе local/php_interface/rk/geo.php
 *
 * Class Geo
 * @package Rusklimat\B2c\Helpers\Main
 */
class Geo
{
	private static $instance;

	private $bushes = [];
	private $prices = [];

	private $capitals = [];
	private $default = [];
	private $redirectFolders = [];

	private $userLocation = null;

	private function __construct()
	{
		$this->setBushes();
		$this->setPrices();

		$this->setDefault();
		$this->setCapitals();

		$this->setUserLocation();
	}

	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getUserLocation()
	{
		return $this->userLocation;
	}

	public function getCapitals()
	{
		return $this->capitals;
	}

	public function getRedirectFolders()
	{
		return $this->redirectFolders;
	}

	public function getDefault()
	{
		return $this->default;
	}

	private function setUserLocation()
	{
		$request = Context::getCurrent()->getRequest();

		$cityCookie = $this->getUserLocationID();

		$arCookieCity = $this->getByID($cityCookie);

		if(is_numeric($request->get('location')))
		{
			$city = $request->get('location');
		}
		else
		{
			$uri = explode('/', $_SERVER['REQUEST_URI']);

			if($_SERVER['REQUEST_URI'] == '/')
			{
				if(!empty($arCookieCity))
				{
					if($arCookieCity['CITY_FOLDER'])
					{
						Tools::LocalRedirect($arCookieCity['CITY_FOLDER'], false, "301 Moved permanently", false);
					}
					else
					{
						$city = $arCookieCity['ID'];
					}
				}
			}
			elseif($_SERVER['SCRIPT_URL'] == '/katalog/')
			{
				$city = $this->default['ID'];
			}
			else
			{
				if(strripos($uri[1],'product-') === 0)
				{
					$city = $this->default['ID'];

					if($arCookieCity)
					{
						if(!empty($arCookieCity) && !$arCookieCity['CITY_FOLDER'])
							$city = $arCookieCity['ID'];
					}
				}
				elseif(!empty($uri[1]) && !empty($this->redirectFolders[$uri[1]]))
				{
					$city = $this->redirectFolders[$uri[1]];

					if($arCookieCity && $arCookieCity['CITY_FOLDER'] == $this->getByID($city)['CITY_FOLDER'])
					{
						$city = $arCookieCity['ID'];
					}
				}
				elseif(PAGE_TYPE === 'CATALOG')
				{
					if(!empty($arCookieCity))
					{
						if($arCookieCity['CITY_FOLDER'] && $arCookieCity['CITY_FOLDER'] != $uri[1])
						{
							if($this->redirectFolders[$uri[1]])
							{
								$city = $this->redirectFolders[$uri[1]];
							}
							elseif($uri[1])
							{
								if(self::isCatalogSection($uri[1]))
								{
									$city = $this->default['ID'];
								}
								else
								{
									$city = $arCookieCity['ID'];
								}
							}
						}
						else
						{
							$city = $arCookieCity['ID'];
						}
					}
				}
				elseif(!empty($arCookieCity))
				{
					$city = $arCookieCity['ID'];
				}
			}
		}

		if($city && !empty($this->getByID($city)))
		{
			$this->userLocation = $this->getByID($city);
		}

		# Если не смогли найти, ставим дефолтный город
		if(empty($this->userLocation) && Config\Geo::BASE_CITY)
		{
			$this->userLocation = $this->getByID($this->default['ID']);
		}

		if($this->userLocation)
		{
			Helpers\Tools::setCookie(Config\Geo::CITY_COOKIE_NAME, $this->userLocation['ID']);

			if($request->get('location'))
			{
				$redirect = Helpers\Main\Tools::GetCurPageParam('', ['location']);

				if($redirect)
				{
					Tools::LocalRedirect($redirect, false, "301 Moved permanently", false);
				}
			}
		}
	}

	private function getUserLocationID()
	{
		$result = (int) Helpers\Tools::getCookie(Config\Geo::CITY_COOKIE_NAME);

		if(!$result)
		{
			$result = $this->getByUserIP();
		}

		if(!$result && Config\Geo::BASE_CITY)
		{
			$result = $this->default['ID'];
		}

		return $result;
	}

	private function getByUserIP()
	{
		$result = false;

		$ipDataFileDir = Application::getDocumentRoot().'/local/modules/rusklimat.b2c/tools/ip_data/ip_data.php';

		if(file_exists($ipDataFileDir))
		{
			include_once($ipDataFileDir);

			if (!empty($_SERVER['HTTP_CLIENT_IP']))
				$IP = $_SERVER['HTTP_CLIENT_IP'];
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				$IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
				$IP = $_SERVER['REMOTE_ADDR'];

			$IP_INT = ip2long($IP);

			$CUR_DATA = false;

			if(!empty($cidr_optim))
			{
				foreach($cidr_optim as $data)
				{
					if ($data[0] <= $IP_INT && $data[1] >= $IP_INT)
					{
						$CUR_DATA = $data;
						break;
					}
				}

				if (!empty($CUR_DATA) && !empty($cities))
				{
					$CUR_DATA[4] = $cities[$CUR_DATA[4]];

					if(!empty($CUR_DATA[4][1]))
						$ipCityName = $CUR_DATA[4][1];
				}
			}

			if(!empty($ipCityName))
			{
				$arCity =  $this->getList(['=NAME' => $ipCityName], ['ID'])->Fetch();

				if(!$arCity)
					$arCity =  $this->getList(['=NAME' => $ipCityName.' Г'], ['ID'])->Fetch();

				if($arCity)
					$result = $arCity['ID'];
			}
		}

		return $result;
	}

	private function getList($arFilter = [], $arSelect = [])
	{
		$arFilter = array_merge($arFilter, [
			'IBLOCK_ID' => Config\Catalog::IBLOCK_GLOBUS,
			'ACTIVE' => 'Y',
			'!PROPERTY_REGION' => false
		]);

		if(!$arSelect)
		{
			$arSelect = [
				'ID',
				'CODE',
				'NAME',
				'XML_ID',
				'PROPERTY_REGION',
				'PROPERTY_NETWORK_NAME',
				'PROPERTY_NETWORK_DATA_CODE',
				'PROPERTY_NETWORK_DATA_SITE_ID',
				'PROPERTY_FILIAL1_CODE',
				'PROPERTY_FILIAL2_CODE',
				'PROPERTY_ORG_PHONE',
				'PROPERTY_CAPITAL',
				'PROPERTY_VISIBILITY_CHECK',
				'PROPERTY_PREPAY_CHECK',
				'PROPERTY_PREORDER_CHECK',
				'PROPERTY_EXPORT_YML_CHECK',
				'PROPERTY_WH',
				'PROPERTY_WH_ORDER',
				'PROPERTY_WH_GENERAL',
				'PROPERTY_WH_HASH',
				'PROPERTY_PREDOPLATA_REQUIRED',
				'PROPERTY_ORG_EMAIL',
				'PROPERTY_ORG_FULLNAME',
				'PROPERTY_TIMEWORKSTART',
				'PROPERTY_TIMEWORKEND',
				'PROPERTY_ORG_PHONEWORKSCHEDULE',
				'IBLOCK_SECTION_ID',
				'PROPERTY_SHIPPING_1',
				'PROPERTY_SHIPPING_2',
				'PROPERTY_SHIPPING_3',
				'PROPERTY_SAMOV_1',
				'PROPERTY_SAMOV_2',
				'PROPERTY_SAMOV_3',
				'PROPERTY_NAME_MP_ED',
				'PROPERTY_NAME_DP_ED',
				'PROPERTY_FLL_ID',
			];
		}

		$res = \CIBlockElement::GetList(
			[
				'PROPERTY_REGION' => 'ASC',
				'PROPERTY_CAPITAL' => 'DESC',
				'NAME' => 'ASC'
			],
			$arFilter,
			false,
			false,
			$arSelect
		);

		return $res;
	}

	public function getByID($id)
	{
		$city = [];

		if($id)
		{
			$cache = Cache::createInstance();

			if ($cache->initCache(3600, $id, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
			{
				$city = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$city = $this->getList(['ID' => (int) $id])->Fetch();

				$city = $this->getCityArray($city);

				$cache->endDataCache($city);
			}
		}

		return $city;
	}

	public function getByXmlID($XmlId)
	{
		$city = [];

		if($XmlId)
		{
			$cache = Cache::createInstance();

			if ($cache->initCache(3600, $XmlId, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
			{
				$city = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$city = $this->getList(['XML_ID' => $XmlId])->Fetch();

				$city = $this->getCityArray($city);

				$cache->endDataCache($city);
			}
		}

		return $city;
	}

	private function setDefault()
	{
		$cache = Cache::createInstance();

		if ($cache->initCache(3600, Config\Geo::BASE_CITY, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
		{
			$this->default = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$arDefaultCity = $this->getList(['XML_ID' => Config\Geo::BASE_CITY])->Fetch();

			if($arDefaultCity)
			{
				$arDefaultCity['DEFAULT'] = true;

				$this->default = $arDefaultCity;
			}

			$cache->endDataCache($this->default);
		}
	}

	private function setCapitals()
	{
		$cache = Cache::createInstance();

		if ($cache->initCache(3600, 1, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
		{
			$cacheValues = $cache->getVars();

			$this->capitals = $cacheValues[0];
			$this->redirectFolders = $cacheValues[1];
		}
		elseif ($cache->startDataCache())
		{
			$rsCapitalsCities =  $this->getList(
				['PROPERTY_CAPITAL' => 1],
				['ID', 'CODE', 'PROPERTY_REGION']
			);

			while($city = $rsCapitalsCities->Fetch())
			{
				$this->capitals[$city['PROPERTY_REGION_VALUE']] = $city['CODE'];
				$this->redirectFolders[ToLower($city['CODE'])] = $city['ID'];
			}

			$cache->endDataCache([$this->capitals, $this->redirectFolders]);
		}
	}

	private function getCityArray($arFields = [])
	{
		$city = [];

		if($arFields)
		{
			$city = [
				'ID' => $arFields['ID'],
				'XML_ID' => $arFields['XML_ID'],
				'NAME' => $arFields['NAME'],
				'REGION' => $arFields['PROPERTY_REGION_VALUE'],
				'REGION_ID' => $arFields['IBLOCK_SECTION_ID'],
				'IS_CAPITAL' => $arFields['PROPERTY_CAPITAL_VALUE'],
				'PRICEBUSH_ID' => $arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE'],
				'CITY_NAME' => $arFields['PROPERTY_NETWORK_NAME_VALUE'],
				'CITY_CODE' => $arFields['PROPERTY_NETWORK_DATA_CODE_VALUE'],
				'CITY_CODE1' => $arFields['PROPERTY_FILIAL1_CODE_VALUE'],
				'CITY_CODE2' => $arFields['PROPERTY_FILIAL2_CODE_VALUE'],
				'CITY_WH_LOCAL' => $arFields['PROPERTY_WH_VALUE'],
				'CITY_WH_REMOTE' => $arFields['PROPERTY_WH_ORDER_VALUE'],
				'CITY_WH_GENERAL' => $arFields['PROPERTY_WH_GENERAL_VALUE'],
				'CITY_WH_FRC' => 688,
				'PREPAYMENT' => $arFields['PROPERTY_PREDOPLATA_REQUIRED_VALUE'],
				'WH_HASH' => $arFields['PROPERTY_WH_HASH_VALUE'],
				'PRICE_ID' => $this->prices[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE']]['ID']?$this->prices[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE']]['ID']:null,
				'PRICE_NODISCOUNT_ID' => $this->prices[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE'].'_NODISCOUNT']['ID']?$this->prices[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE'].'_NODISCOUNT']['ID']:null,
				'PHONE' => $arFields['PROPERTY_ORG_PHONE_VALUE'],
				'EMAIL' => $arFields['PROPERTY_ORG_EMAIL_VALUE'],
				'ORGNAME' => $arFields['PROPERTY_ORG_FULLNAME_VALUE'],
				'CODE' => $arFields['CODE'],
				'VISIBILITY_CHECK' => $arFields['PROPERTY_VISIBILITY_CHECK_VALUE'],
				'PREPAY_CHECK' => $arFields['PROPERTY_PREPAY_CHECK_VALUE'],
				'PREORDER_CHECK' => $arFields['PROPERTY_PREORDER_CHECK_VALUE'],
				'EXPORT_YML_CHECK' => $arFields['PROPERTY_EXPORT_YML_CHECK_VALUE'],
				'TIMEWORKSTART' => $arFields['PROPERTY_TIMEWORKSTART_VALUE']?date("H:i", strtotime($arFields['PROPERTY_TIMEWORKSTART_VALUE'])):null,
				'TIMEWORKEND' => $arFields['PROPERTY_TIMEWORKEND_VALUE']?date("H:i", strtotime($arFields['PROPERTY_TIMEWORKEND_VALUE'])):null,
				'ORG_PHONEWORKSCHEDULE' => $arFields['PROPERTY_ORG_PHONEWORKSCHEDULE_VALUE'],
				'SHIPPING_FLL_DAY' => $arFields['PROPERTY_SHIPPING_1_VALUE'],
				'SHIPPING_RRC_DAY' => $arFields['PROPERTY_SHIPPING_2_VALUE'],
				'SHIPPING_FRC_DAY' => $arFields['PROPERTY_SHIPPING_3_VALUE'],
				'PICKUP_FLL_DAY' => $arFields['PROPERTY_SAMOV_1_VALUE'],
				'PICKUP_RRC_DAY' => $arFields['PROPERTY_SAMOV_2_VALUE'],
				'PICKUP_FRC_DAY' => $arFields['PROPERTY_SAMOV_3_VALUE'],
				'NAME_MP_ED' => $arFields['PROPERTY_NAME_MP_ED_VALUE'],
				'NAME_DP_ED' => $arFields['PROPERTY_NAME_DP_ED_VALUE'],
				'FLL_ID' => $arFields['PROPERTY_FLL_ID_VALUE'],
				'CITY_FOLDER' => null,
				'PHONE_NUM' => null,
				'BUSH' => !empty($this->bushes[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE']]) ? $this->bushes[$arFields['PROPERTY_NETWORK_DATA_SITE_ID_VALUE']] : null,
			];

			if(empty($city['PHONE']))
				$city['PHONE'] = Config\Contacts::DEFAULT_PHONE;

			if($city['PHONE'])
			{
				$city['PHONE_NUM'] = preg_replace('/[^\d]+/ui', '', $city['PHONE']);

				if($city['PHONE_NUM']{0} == '8')
					$city['PHONE_NUM']="+7".substr($city['PHONE_NUM'],1,strlen($city['PHONE_NUM'])-1);
			}

			$city['CITY_FOLDER'] = $this->getLocationFolder($city);
		}

		return $city;
	}

	private function setBushes()
	{
		if(Config\Catalog::IBLOCK_BUSH)
		{
			$cache = Cache::createInstance();

			if ($cache->initCache(3600, 1, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
			{
				$cacheValues = $cache->getVars();

				$this->bushes = $cacheValues[0];
				$this->redirectFolders = $cacheValues[1];
			}
			elseif ($cache->startDataCache())
			{
				$res = \CIBlockElement::GetList(
					[],
					[
						'IBLOCK_ID' => Config\Catalog::IBLOCK_BUSH,
						'ACTIVE' => 'Y',
					],
					false,
					false,
					[
						'ID','NAME','CODE','XML_ID','PROPERTY_CITY'
					]
				);

				while($arFields = $res->Fetch())
				{
					$this->bushes[$arFields['XML_ID']] = $arFields;

					if($arFields['PROPERTY_CITY_VALUE'])
						$this->redirectFolders[(empty($arFields['CODE'])?'moscow':$arFields['CODE'])] = $arFields['PROPERTY_CITY_VALUE'];
					
					$arBaseCity_xmlid = \CIBlockElement::GetById($arFields['PROPERTY_CITY_VALUE'])->Fetch()["XML_ID"]; // получаем xml_id базовый город для куста

					$resStock = \CIBlockElement::GetList(["ID"=>"DESC"],["IBLOCK_ID" => 38, "PROPERTY_PICKUPPOINT_GLOBUSRK_ID" => $arBaseCity_xmlid, "ACTIVE"=>"Y"], false, false, ["ID", "NAME","PROPERTY_FULLNAME"]);

					while($obStock = $resStock->Fetch())
					{
						if(strlen($obStock['PROPERTY_FULLNAME_VALUE']) > 2)
						{
							$arStock = $obStock;
						}
					}

					$this->bushes[$arFields['XML_ID']]["STOCK_ID"] = $arStock["ID"];
				}

				$cache->endDataCache([$this->bushes, $this->redirectFolders]);
			}
		}
	}

	private function setPrices()
	{
		$cache = Cache::createInstance();

		if ($cache->initCache(3600, 1, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
		{
			$this->prices = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$rsPriceType = \CCatalogGroup::GetList(['SORT' => 'ASC'], []);

			while ($arPriceType = $rsPriceType->Fetch())
			{
				$this->prices[$arPriceType['NAME']] = $arPriceType;
			}

			$cache->endDataCache($this->prices);
		}
	}

	private function getLocationFolder($city = [])
	{
		$result = '';

		if($city)
		{
			if(!isset($this->bushes[$city['PRICEBUSH_ID']]['REGION']))
			{
				$this->bushes[$city['PRICEBUSH_ID']]['REGION'] = $this->getCityRegion($this->bushes[$city['PRICEBUSH_ID']]['PROPERTY_CITY_VALUE']);
			}

			if($city['REGION_ID'] == 21752)
			{
				/* Московская область */

				$result = '';
			}
			else
			{
				if($this->bushes[$city['PRICEBUSH_ID']]['REGION'] == $city['REGION'])
				{
					$result = $this->bushes[$city['PRICEBUSH_ID']]['CODE'];
				}
				elseif(!empty($this->capitals[$city['REGION']]))
				{
					$result = $this->capitals[$city['REGION']];
				}
				else
				{
					$result = $city['BUSH']['CODE'];
				}
			}
		}

		return $result;
	}

	private function getCityRegion($id = 0)
	{
		$result = null;

		if($id)
		{
			$city = $this->getList(['ID' => $id], ['PROPERTY_REGION'])->Fetch();

			if($city)
			{
				$result = $city['PROPERTY_REGION_VALUE'];
			}
		}

		return $result;
	}

	public function getAllLocations()
	{
		$result = [];

		if(Config\Catalog::IBLOCK_GLOBUS)
		{
			$res =  $this->getList([]);

			while($arFields = $res->Fetch())
			{
				$city = $this->getCityArray($arFields);

				$result[$arFields['PROPERTY_REGION_VALUE']][$arFields['ID']] = $city;
			}
		}

		return $result;
	}

	public static function isCatalogSection($code = '')
	{
		$result = false;

		if(!empty($code))
		{
			$cache = Cache::createInstance();

			if ($cache->initCache(7200, $code, str_replace('\\', '/', '/'.__CLASS__.'/'.__FUNCTION__)))
			{
				$result = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				$section = SectionTable::getList([
					'filter' => [
						'IBLOCK_ID' => Config\Catalog::ID,
						'=CODE' => $code,
						'DEPTH_LEVEL' => 1
					],
					'select' => ['ID'],
					'limit' => 1,
					'cache' => ['ttl' => 360000]
				])->fetch();

				if($section)
					$result = $section['ID'];

				$cache->endDataCache($result);
			}
		}

		return $result;
	}
}