<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<?
if(CModule::IncludeModule("subscribe")){  

	$mailUser = $_POST["email"];
	$chekMail = filter_var($mailUser, FILTER_VALIDATE_EMAIL);

} 
?>
<br />

<?if(!empty($mailUser)&&$chekMail==true):?>

	<?// проверяем есть ли уже он в БД

	$subscription = CSubscription::GetByEmail($mailUser);

	if($subscription->ExtractFields("str_")&&$str_ACTIVE=="Y"):?>
		Электорнный адрес <?=$mailUser;?> уже есть в базе рассылок
	<?elseif($str_ACTIVE=="N"):?>
		Электорнный адрес <?=$mailUser;?> есть в базе рассылок, но он не активен
	<?else:?>
	<?
		//there must be at least one newsletter category
	$RUB_ID = array("52");

		$arFields = Array(
			"FORMAT" => "html",
			"EMAIL" => $mailUser,
			"ACTIVE" => "Y",
			"RUB_ID" => $RUB_ID
		);
		$subscr = new CSubscription;

		//can add without authorization
		$ID = $subscr->Add($arFields);
		if($ID>0)
			{CSubscription::Authorize($ID);
			echo "Адрес $mailUser добавлен в базу рассылок.";}
		else
			$strWarning .= "Error adding subscription: ".$subscr->LAST_ERROR."<br>";
	?>
	
	<?endif;?>

<?elseif(!empty($mailUser)&&$chekMail==false):?>

Вы ввели некорректную почту

<?else:?>

<p>Здесь вы можете подписаться на рассылки нашего сайта. Введите ваш e-mail и нажмите на кнопку Подписаться.</p>
<form method="POST" action="#SITE_DIR#personal/subscribe/subscr_new.php" style="border:1px solid #ccc;padding:5px;">
<p>Введите ваш email</p>
<input type="text" name="email" value="" />
<br /><br />
<input type="submit" value="Подписаться" style="padding:3px;">
</form>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>