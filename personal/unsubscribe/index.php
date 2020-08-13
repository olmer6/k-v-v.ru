<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(iconv("utf-8","windows-1251","АДРЕНАЛИН.RU - все для туризма, рыбалки, квадроциклы, лодки ПВХ, лодочные моторы, GPS навигаторы, эхолоты, рации."));
?>
<style>
.button-l {
		display:block;
		margin:0 10px;
		padding:5px 10px;
		float:left;
		background-color:#FF5A00;
		color:white;
		text-decoration:none;
		border-radius:3px;
		border:1px solid #B64000;
}
	.button-l:hover {
		background-color:#fd6f22;
}
	.button-l:first-child {

		margin:0 10px 0 300px;
}
</style>
<div>
	<br />
<?$APPLICATION->IncludeComponent(
	"",//"accesser:subscribe.unsubscribe",
	"",
Array(
      "ASD_MAIL_ID" => $_REQUEST["mid"],
      "ASD_MAIL_MD5" => $_REQUEST["mhash"]
),false
);?>

</div>
<?if(CModule::IncludeModule("subscribe")){  

    $test_mail = CSubscription::GetList(Array("ID"=>"ASC"), Array("ID"=>$_GET["ID"]), false);
    while($test_mail1 = $test_mail->Fetch())
    {
        $mailUser = $test_mail1["EMAIL"];
        $chekMail = filter_var($mailUser, FILTER_VALIDATE_EMAIL);
    }
} 
?>
<br />

    <?if(!empty($mailUser)&&$chekMail==true):?>

        <?// проверяем есть ли уже он в БД

        $subscription = CSubscription::GetByEmail($mailUser);
        $RUB_ID = null;
            $arFields = Array(
                "FORMAT" => "html",
                //"EMAIL" => $mailUser,
                "ACTIVE" => "N",
                "RUB_ID" => $RUB_ID
            );
            $subscr1 = new CSubscription;
        $ID = $subscr1->Update($_GET["ID"],$arFields);
        if($ID>0){
            CSubscription::Authorize($ID);
			echo iconv("utf-8","windows-1251","Адрес $mailUser отписан от рассылки.");
        }
		else
			$strWarning .= "Error adding subscription: ".$subscr->LAST_ERROR."<br>";
		/*$subscr = new CSubscription;

		//can add without authorization
		$ID = $subscr->Add($arFields);
		if($ID>0)
			{CSubscription::Authorize($ID);
			echo iconv("utf-8","windows-1251","Адрес $mailUser отписан от рассылки.");}
		else
			$strWarning .= "Error adding subscription: ".$subscr->LAST_ERROR."<br>";*/
	?>

	<?endif;?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>