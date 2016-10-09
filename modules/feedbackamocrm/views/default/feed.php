<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\View;
?>
<?php $form = ActiveForm::begin([
	'id' => 'my-form-id',
	'action' => Url::toRoute('default/send'),
	'enableAjaxValidation' => true,
	'validationUrl' => Url::toRoute('default/validate'),
	]); ?>
<div class='form-box'>	
	<div id="mess"></div>

<div class='field-box'><?= $form->field($model, 'name',[
'options'=>['id'=>'','class'=>'']]) ?></div>
	
<div class='field-box'><?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
		'mask' => '+7(999)-999-9999',
		'clientOptions'=>[
		'removeMaskOnSubmit' => true,
		]
		]) ?></div>

<div class='field-box'><?= $form->field($model, 'theme') ?></div>

<div class='field-box'><?= $form->field($model, 'message')->textArea(['rows' => '6']) ?></div>

		<div class="form-group">
			<?= Html::submitButton('Отправить', ['class' => 'sky big']) ?>
		</div>

		<?php ActiveForm::end(); ?>
</div>
		<?php $this->registerJs('
			$(document).on("beforeSubmit", "#my-form-id", function () {

				$.ajax({
					type: "POST",
					async: true,
					url: "'. Url::toRoute('default/send') .'",
					data: $("#my-form-id").serializeArray(),
					dataType: "json"						
				})
		.done(function(json) {
			$("#mess").html(\'<div class="alert alert-success" role="alert">'.\Yii::$app->session->getFlash('success').'</div>\');
		})
		.fail(function(json) {
			$("#mess").html(\'<div class="alert alert-danger" role="alert">'.\Yii::$app->session->getFlash('danger').'</div>\');
		});
		return false; 
	});


		', View::POS_END, 'my-options');

		?>

