<?php
if(is_array($message_de_service) && count($message_de_service)) {
	?>
	<div style="background:yellow; color:red; padding:10px">
		<?php echo implode('<br />', $message_de_service); ?>
	</div>
	<?php
}
?>