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

	<?// ��������� ���� �� ��� �� � ��

	$subscription = CSubscription::GetByEmail($mailUser);

	if($subscription->ExtractFields("str_")&&$str_ACTIVE=="Y"):?>
		����������� ����� <?=$mailUser;?> ��� ���� � ���� ��������
	<?elseif($str_ACTIVE=="N"):?>
		����������� ����� <?=$mailUser;?> ���� � ���� ��������, �� �� �� �������
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
			echo "����� $mailUser �������� � ���� ��������.";}
		else
			$strWarning .= "Error adding subscription: ".$subscr->LAST_ERROR."<br>";
	?>
	
	<?endif;?>

<?elseif(!empty($mailUser)&&$chekMail==false):?>

�� ����� ������������ �����

<?else:?>

<p>����� �� ������ ����������� �� �������� ������ �����. ������� ��� e-mail � ������� �� ������ �����������.</p>
<form method="POST" action="#SITE_DIR#personal/subscribe/subscr_new.php" style="border:1px solid #ccc;padding:5px;">
<p>������� ��� email</p>
<input type="text" name="email" value="" />
<br /><br />
<input type="submit" value="�����������" style="padding:3px;">
</form>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>