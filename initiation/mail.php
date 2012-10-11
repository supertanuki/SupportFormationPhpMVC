<?php
if( isset($_GET['subject'])
	&& isset($_GET['message'])
	&& isset($_GET['to'])
	) {
	if( mail($_GET['to'], $_GET['subject'], $_GET['message']) )
	{
		echo 'Ok !';
	} else {
		echo 'KO !';
	}
}