<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if(!$USER->IsAuthorized()) {
    LocalRedirect('/auth/?login=yes');
};
$phone_text = $_POST[text];
$phone_text = str_replace('+7','',$phone_text);
$phone_text = preg_replace('~\D+~','',$phone_text);
if($phone_text != ""){
	$filter = Array("ACTIVE" => "Y");
	$db_props_1 = CUser::GetList(($by="date_register"),($order="desc"),$filter,array("SELECT" => array("UF_SMS_MESSAGE")));
	$is_filtered = $db_props_1->is_filtered;
	while($db_props_1->NavNext(true, "f_"))
	{
		//Обрабатываем мобильный телефон
		$mobile = $f_PERSONAL_MOBILE;
		$mobile = str_replace('+7','',$mobile);
		if($mobile{0} == 8 || $mobile{0} == 7)
			$mobile = substr($mobile, 1);
		$mobile = preg_replace('~\D+~','',$mobile);
		//Обрабатываем персональный телефон
		$phone = $f_PERSONAL_PHONE;
		$phone = str_replace('+7','',$phone);
		if($phone{0} == 8 || $phone{0} == 7)
			$phone = substr($phone, 1);
		$phone = preg_replace('~\D+~','',$phone);
		if($phone_text == $phone || $phone_text == $mobile)
		{
			if($f_UF_SMS_MESSAGE == 1)
			{
				$perem = 1;
				$user = new CUser;
				$fields = Array("UF_SMS_MESSAGE" => 0); 
				$user->Update($f_ID, $fields);
				$nphone = 1;
				unset($mobile);
				unset($phone);
				break;
			} else {
				$perem = 2;
				$nphone = 1;
				unset($mobile);
				unset($phone);
				break;
			}
		}
	}
	if($nphone != 1)
		$perem = 3;
} else {
	$perem = 4;
}
function telep($perem) {
	if($perem == 1)
	{
		echo iconv("utf-8","windows-1251",'<b>Вы отписали номер '.$_POST[text].' от SMS рассылки</b>');
	} elseif($perem == 2)
	{
		echo iconv("utf-8","windows-1251",'<b>Номер '.$_POST[text].' уже отписан от SMS рассылки</b>');
	} elseif($perem == 3)
	{
		echo iconv("utf-8","windows-1251",'<b style="color:red;">Номер '.$_POST[text].' не найден!<br/>Если Вам приходят SMS рассылки, просьба написать в <a href="#SITE_DIR#help/feedback.php">Обратную связь</a></b>');
	} elseif($perem == 4)
	{
		echo iconv("utf-8","windows-1251",'<b style="color:red;">Не правильно заполнено поле!</b>');
	}
}
?>
<?
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
<? echo iconv("utf-8", "windows-1251", "<h1>Отписаться от СМС рассылки</h1>");?>
<br/>
<script type="text/javascript" src="http://base.4cars.pro/jquery.maskedinput.min.js"></script>
<script type="text/javascript">
jQuery(function($){
   $("#phone").mask("+7 (999) 999-99-99");
});
</script>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
������� ����� ��������, ������� �� ������ �������� �� ��������: <input type=hidden name="qwe" value="586"><input name="text"  id="phone" type="tel" pattern="+7 ([0-9]{3}) [0-9]{3}-[0-9]{2}-[0-9]{2}" placeholder="������� ��� �������" value="">
<input type="submit">
</form>

<?
if ($_POST['qwe']==586)
{?>
    <p><?telep($perem);?></p>
<?}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>