<?

if(!function_exists('pre'))
{
	function pre($value, $die = false, $bHtml = false)
	{
		if(is_bool($value))
			$value = 'bool: '.($value == true ? 'true' : 'false');

		$sReturn = print_r($value, true);

		$debug_backtrace = debug_backtrace();


		if (defined("STDIN")) /* php-cli */
		{
			//echo "\r\n==========\r\n".$sReturn."\r\n==========\r\n";
			if (substr(ltrim($sReturn), 0, 1) === "*")
			{
				$pos = strpos($sReturn, "*");

				echo "\r".str_repeat(" ", 40);
				echo "\r".substr($sReturn, 0, $pos).substr($sReturn, $pos+1);
			}
			else
			{
				echo "\r\n".$sReturn;
			}

			ob_flush();
		}
		else
		{
			if ($bHtml)
				$sReturn = htmlspecialchars($sReturn);

			echo "<pre data-source=\"".substr($debug_backtrace[1]["file"], strlen($_SERVER["DOCUMENT_ROOT"])).":".$debug_backtrace[1]["line"]."\" style=\"overflow:auto; color: #000; background-color: white; border: 1px solid #CCC; padding: 5px; font-size: 12px;\">".$sReturn."</pre>";
		}

		if($die)
		{
			ob_get_flush();
			die();
		}
	}
}

if(!function_exists('array_is_assoc'))
{
	function array_is_assoc($arr)
	{
		$i = 0;
		foreach($arr as $k=>$val)
		{
			if("".$k!="".$i)
				return true;
			$i++;
		}
		return false;
	}
}

if(!function_exists('isDev'))
{
	function isDev()
	{
		$result = false;

		if( strpos($_SERVER["PHP_SELF"], "DEV") !== false
			|| strpos($_SERVER["PWD"], "DEV") !== false
			|| $_SERVER["SERVER_NAME"] == "dev.rusklimat.ru"
		)
		{
			$result = true;
		}

		if($_GET['isDev'] == 'N')
			$result = false;

		return $result;
	}
}

if(!function_exists('myUcfirst'))
{
	function myUcfirst($str = '')
	{
		$result = '';

		if(!empty($str))
		{
			$result = ToLower(substr($str, 0, 1)).substr($str, 1);
		}

		return $result;
	}
}