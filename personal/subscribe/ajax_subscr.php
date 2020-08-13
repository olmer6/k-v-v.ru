<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");?>
<?if(CModule::IncludeModule("subscribe")){
	$mailUser = $_POST["email"];
	$chekMail = filter_var($mailUser, FILTER_VALIDATE_EMAIL);
}?>

<?if($mailUser == '') echo '<p style="color: #fff;">Вы не ввели почту</p>';
if(!empty($mailUser)&&$chekMail==true):?>
	<?$subscription = CSubscription::GetByEmail($mailUser);
	if($subscription->ExtractFields("str_")&&$str_ACTIVE=="Y"):?>
		<p style="color: #fff;">Электорнный адрес <?=$mailUser;?> уже есть в базе рассылок</p>
	<?elseif($str_ACTIVE=="N"):?>
		<p style="color: #fff;">Электорнный адрес <?=$mailUser;?> есть в базе рассылок, но он не активен</p>
	<?else:?>
	<?$RUB_ID = array("52");
	$arFields = Array(
		"FORMAT" => "html",
		"EMAIL" => $mailUser,
		"ACTIVE" => "Y",
		"RUB_ID" => $RUB_ID
	);
	$subscr = new CSubscription;
	$ID = $subscr->Add($arFields);
	if($ID>0) 
	{
		CSubscription::Authorize($ID);
		echo '<p style="color: #fff;">Адрес '.$mailUser.' добавлен в базу рассылок.';
	} else $strWarning .= "Error adding subscription: ".$subscr->LAST_ERROR."<br>";?>
<?endif;?>
<?elseif(!empty($mailUser)&&$chekMail==false):?>
<p style="color: #fff;">Вы ввели некорректную почту</p>
<?endif;?>