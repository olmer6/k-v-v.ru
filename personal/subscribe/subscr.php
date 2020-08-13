<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");?>
<link href="<?=SITE_TEMPLATE_PATH?>/informula_styles.css"  type="text/css" rel="stylesheet" />
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery-migrate-1.2.1.min.js"></script>
<style>
    body {
        padding: 10px 15px;
        margin: 0;
        border-top: 3px solid #ff5a00;
        overflow: hidden;
        position: relative;
    }
    form {margin: 0;}
	.results{
		margin: 280px 0px 0px 410px;
		position: absolute;
		text-align: center;
		width: 280px;
		font-family: Verdana;
	}
</style>
<script type="text/javascript">
	function call() {
		var msg   = $('#formx').serialize();
		$.ajax({
			type: 'POST',
			url: 'ajax_subscr.php',
			data: msg,
			success: function(data) {
				$('.results').html(data);
			},
			error:  function(xhr, str){
				alert('Возникла ошибка: ' + xhr.responseCode);
			}
		});
	}
</script>
<div class="results">
	<p style="font-size: 19px;margin: 12px 0px 1px 0px;color: #fff;">введите свой e-mail здесь:</p>
	<form method="POST" action="javascript:void(null);" onsubmit="call()" id="formx">
		<input style="height: 30px;text-indent: 5px;border-radius: 13px;box-shadow: 0 0 10px rgba(0,0,0,0.5);" type="email" size="30" class="e_mail" name="email" value="" />
		<input class="button" type="image" style="vertical-align: middle;" src="strelka.png"></p>
	</form>
</div>
<img src="#SITE_DIR#upload/subscribe/subscribe.jpg">