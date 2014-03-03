<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('ROOT_PATH', getenv('DOCUMENT_ROOT'));

require_once ROOT_PATH.'/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('iblock');

if (getenv('REQUEST_METHOD') == 'POST' && getenv('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
	// Clear html
	$_POST = array_map('strip_tags', $_POST);
	$_POST = array_map('trim', $_POST);
	$errors = array();
	
	$validEmail = filter_var($_POST['recall_email'], FILTER_VALIDATE_EMAIL);
	
	if ($_POST['recall_name'] == '') {
		$errors['recall_name'] = 'Введите Ваше имя';
	}
	if (!$validEmail) {
			$errors['recall_email'] = 'Некорректный E-mail';
		}
	if ($_POST['recall_lang'] == '') {
		$errors['recall_lang'] = 'Введите языковую пару';
	}
	if ($_FILES['file']['size'] > 2000000) {
        $errors['file'] = 'Размер файла большой';
    }
	if (empty($errors)) {
		$arEventFields = array(
			'recall_name'	 => $_POST['recall_name'],
			'recall_phone'	 => $_POST['recall_phone'],
			'recall_email'	 => $_POST['recall_email'],
			'recall_service'	 => $_POST['recall_service'],
			'recall_lang'	 => $_POST['recall_lang'],
			'recall_comment'	 => $_POST['recall_comment'],
		);
		
		CEvent::SendImmediate('RECALL', SITE_ID, $arEventFields);
		$el = new CIBlockElement;
		$arLoadProductArray = array(
			'IBLOCK_ID'     	=> 	9,
			'NAME'				=>	$_POST['recall_name'],
			'PREVIEW_TEXT'		=>	sprintf('Телефон: %s<br>E-mail: %s<br>Какая услуга требуется: %s<br>Языковая пара: %s<br>Коментации: %s', $_POST['recall_phone'], $_POST['recall_email'], $_POST['recall_service'], $_POST['recall_lang'], $_POST['recall_comment']), 
			'PREVIEW_TEXT_TYPE'	=> 'html',
			'PROPERTY_VALUES' => array(
				'LOAD_FILE' => Array("n0" => CFile::MakeFileArray($_FILES['file']['tmp_name']))
			),
		);

		$el->Add($arLoadProductArray);
		echo 'success';
		exit;
	}

	$_POST = array_map('htmlspecialchars', $_POST);
}
?>

<?if(getenv('HTTP_X_REQUESTED_WITH') != 'XMLHttpRequest'):?><div class="order_form"><?endif;?>

	<form id="recall_form" enctype="multipart/form-data" action="" method="POST">
		<span>Контактное лицо:</span>
		<input type="text" id="recall_name" name="recall_name" class="required_field <?if(isset($errors['recall_name'])):?>error_input empty_field<?endif;?>" value="<?if(array_key_exists('recall_name', $_POST)):?><?=$_POST['recall_name']?><?endif;?>">
		<span>Телефон:</span>
		<input type="text" id="recall_phone" name="recall_phone" value="<?if(array_key_exists('recall_phone', $_POST)):?><?=$_POST['recall_phone']?><?endif;?>">
		<span>E-mail:</span>
		<input type="text" id="recall_email" name="recall_email" class="required_field <?if(isset($errors['recall_email'])):?>error_input empty_field<?endif;?>" value="<?if(array_key_exists('recall_email', $_POST)):?><?=$_POST['recall_email']?><?endif;?>">
		<span>Какая услуга требуется:</span>
		<input type="text" id="recall_service" name="recall_service" value="<?if(array_key_exists('recall_service', $_POST)):?><?=$_POST['recall_service']?><?endif;?>">
		<span>Языковая пара:</span>
		<input type="text" id="recall_lang" name="recall_lang" class="required_field <?if(isset($errors['recall_lang'])):?>error_input empty_field<?endif;?>" value="<?if(array_key_exists('recall_lang', $_POST)):?><?=$_POST['recall_lang']?><?endif;?>">
		<span>Комментарии:</span>
		<textarea id="recall_comment" name="recall_comment" value="<?if(array_key_exists('recall_comment', $_POST)):?><?=$_POST['recall_comment']?><?endif;?>"></textarea>
		<div id="succes" class="succes"><?=$errors['file']?></div>
		<div class="upload_file">
			<span>Прикрепить файлы:</span>
			<a href="#" id="chose_file" class="file_btn">Выберите файл</a>
			<input id="recall_upload" class="file_upl" type="file" name="file">
		</div>
		<div class="submit_block">
			<input class="sumbit_btn" id="recall_submit" type="submit" name="uploadSubmitter1" value="Отправить заказ">
		</div>
	</form>
<?if(getenv('HTTP_X_REQUESTED_WITH') != 'XMLHttpRequest'):?></div><?endif;?>
<?if(getenv('HTTP_X_REQUESTED_WITH') != 'XMLHttpRequest'):?>
<script>
	$("#recall_phone").mask("+7 (999) 999-9999");
	$('#chose_file').click(function(){
		$('#recall_upload').click();
		return false;
	});
	$(document).on("click", "#recall_submit", function(){
		$("#recall_form").submit();
	});
	$("html").on('submit', '#recall_form', function() {
			var ok=true;
		$('#recall_form').find('.required_field').each(function(){
			if($(this).val()!=""){
				$(this).removeClass('empty_field');
				$(this).removeClass('error_input');
			}else{
				$(this).addClass('error_input');
				$(this).addClass('empty_field');
				ok=false;
				$('#succes').css({'color':'red'}).html("Ошибка");
			}
		});
		if(ok==true){
			$('#succes').css({'color':'green'}).html("Отправка...");
			$('#recall_form').ajaxForm({
				success: function(data){
					$('#succes').css({'color':'green'}).html("Заказ успешно отправлен.");
				}
			});
		}
		return false;
	});

</script>
<?endif;?>