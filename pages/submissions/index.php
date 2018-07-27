<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\packages\aido_participant\AIDOParticipant;

// Define Constants

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
		'placeholder' => 'e.g., Completed'
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
			'width' => 'md-3',
			'align' => 'left',
			'translation' => 'Label',
			'editable' => false
		),
		'datetime' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-3',
			'align' => 'center',
			'translation' => 'Submitted (GMT)',
			'editable' => false
		),
		'status' => array(
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
				'API_resource' => 'aido_submission',
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
	AIDOParticipant::createSubmission( Core::getUserLogged('username'), 'fixed bug, now should work', 'something' );

	// parse the arguments
	\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );

	$submissions = AIDOParticipant::getUserSubmissions( Core::getUserLogged('username') );

	$tmp = [];
	foreach( $submissions as $submission ){
		$datetime = date_create_from_format(\DateTime::W3C, $submission['datetime']);
		$submission['datetime'] = $datetime->format("Y-m-d H:i:s");
		array_push( $tmp, $submission );
	}
	$submissions = $tmp;

	// filter based on keywords (if needed)
	if( $features['keywords']['value'] != null ){
		$tmp = array();
		foreach( $submissions as $submission ){
			if( strpos($submission['label'], $features['keywords']['value']) !== false ){
				array_push($tmp, $user);
			}
		}
		$submissions = $tmp;
	}

	// compute total number of users for pagination purposes
	$total_submissions = sizeof( $submissions );

	// take the slice corresponding to the selected page
	$submissions = array_slice(
		$submissions,
		($features['page']['value']-1)*$features['results']['value'],
		$features['results']['value']
	);

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

	function _edit_user( target ){
		var userid = $(target).data('userid');
		//TODO: open editor modal here
		alert( 'Not implemented yet!' );
	}

</script>
