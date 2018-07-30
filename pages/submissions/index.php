<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\packages\aido\AIDO;

// define features
$features = array(
	'page' => array(
		'type' => 'integer',
		'default' => 1,
		'values' => null,
		'minvalue' => 1,
		'maxvalue' => PHP_INT_MAX
	),
	'results' => array(
		'type' => 'integer',
		'default' => 10,
		'values' => null,
		'minvalue' => 1,
		'maxvalue' => PHP_INT_MAX
	),
	'keywords' => array(
		'type' => 'text',
		'default' => null,
		'placeholder' => 'e.g., my submission'
	)
);

$table = array(
	'style' => 'table-striped table-hover',
	'layout' => array(
		'id' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-1',
			'align' => 'left',
			'translation' => 'ID',
			'editable' => false
		),
		'label' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-4',
			'align' => 'left',
			'translation' => 'Label',
			'editable' => false
		),
		'datetime' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
			'translation' => 'Submitted (GMT)',
			'editable' => false
		),
		'status_html' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-2',
			'align' => 'center',
			'translation' => 'Status',
			'editable' => false
		)
	),
	'actions' => array(
		'_width' => 'md-2',
		'info' => array(
			'type' => 'default',
			'text' => 'Open',
			'glyphicon' => 'open',
			'tooltip' => 'Open submission',
            'function' => array(
				'type' => 'custom',
				'custom_html' => 'onclick="_submission_info(this)"',
				'arguments' => array('id')
			)
		),
        'separator' => array(
            'type' => 'separator'
        ),
		'delete' => array(
			'type' => 'warning',
			'glyphicon' => 'trash',
			'tooltip' => 'Delete submission',
            'function' => array(
				'type' => '_toggle_modal',
				'class' => 'yes-no-modal',
				'text' => 'Delete',
				'API_resource' => 'submission',
				'API_action' => 'delete',
				'arguments' => [
					'id'
				],
				'static_data' => [
					'question' => 'Are you sure you want to delete this submission?'
				]
			)
		)
	),
	'features' => array(
		'_counter_column',
		'_actions_column'
	)
);
?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Submissions</h2>
			</td>
		</tr>

	</table>

	<?php
	// parse the arguments
	\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );

	$res = AIDO::getUserSubmissions( Core::getUserLogged('username'), null, $features['page']['value'], $features['results']['value'] );
	if( !$res['success'] ) Core::throwError( $res['data'] );
	$total_submissions = $res['data']['total'];
	$submissions = $res['data']['page_data'];

	foreach( $submissions as &$submission ){
		// convert status
		$status_icon = '';
		$status_color = '';
		switch( $submission['status'] ){
			case 'Queued':
				$status_icon = 'clock-o';
				$status_color = 'black';
				break;
			case 'Running':
				$status_icon = 'car';
				$status_color = '#337ab7';
				break;
			case 'Finished':
				$status_icon = 'check';
				$status_color = 'green';
				break;
			case 'Failed':
				$status_icon = 'exclamation-circle';
				$status_color = 'red';
				break;
			default: break;
		}
		$submission['status_html'] = sprintf('<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>', $status_color, $status_icon, $submission['status']);
	}

	// prepare data for the table viewer
	$res = array(
		'size' => sizeof( $submissions ),
		'total' => $total_submissions,
		'data' => $submissions
	);

	// <== Here is the Magic Call!
	\system\templates\tableviewers\TableViewer::generateTableViewer( \system\classes\Configuration::$PAGE, $res, $features, $table );
	?>

</div>


<script type="text/javascript">

	function _edit_submission( target ){
		var userid = $(target).data('id');
		//TODO: open editor modal here
		alert( 'Not implemented yet!' );
	}

</script>
