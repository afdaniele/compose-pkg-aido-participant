<?php

function statusRender( $status ){
    // convert status
	$icon = '';
	$color = '';
	switch( $status ){
		case 'Queued':
			$icon = 'clock-o';
			$color = 'black';
			break;
		case 'Running':
			$icon = 'car';
			$color = '#337ab7';
			break;
		case 'Finished':
			$icon = 'check';
			$color = 'green';
			break;
		case 'Failed':
			$icon = 'exclamation-circle';
			$color = 'red';
			break;
		default: break;
	}
    return ['icon' => $icon, 'color' => $color];
}//statusRender

?>
