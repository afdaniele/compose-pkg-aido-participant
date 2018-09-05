<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;
use \system\templates\tableviewers\TableViewer;

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
	'tag' => array(
		'type' => 'text',
		'default' => null,
		'translation' => 'Status',
		'values' => AIDO::getSubmissionsStatusList()
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
		'submission_id' => array(
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
		'date_submitted' => array(
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
		),
		'status' => array(
			'type' => 'text',
			'show' => false,
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
				'arguments' => [
					'submission_id'
				],
				'static_data' => [
					'modal-mode' => 'edit'
				]
			)
		),
        'separator' => array(
            'type' => 'separator'
        ),
		'retract' => array(
			'type' => 'warning',
			'glyphicon' => 'trash',
			'tooltip' => 'Retract submission',
            'function' => array(
				'type' => '_toggle_modal',
				'class' => 'yes-no-modal',
				'text' => 'Retract',
				'API_resource' => 'submission',
				'API_action' => 'delete',
				'arguments' => [
					'submission_id'
				],
				'static_data' => [
					'question' => 'Are you sure you want to retract this submission?'
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

<?php
// parse the arguments
\system\templates\tableviewers\TableViewer::parseFeatures( $features, $_GET );

$res = AIDODashboard::getUserSubmissions( null/*user_id*/, $features['tag']['value'], $features['page']['value'], $features['results']['value'] );
if( !$res['success'] ) Core::throwError( $res['data'] );

$total_submissions = count($res['data']);
$submissions = $res['data'];

foreach( $submissions as &$submission ){
	$res = AIDO::getSubmissionsStatusStyle( $submission['status'] );
	// convert status
	$status_icon = $res['icon'];
	$status_color = $res['color'];
	$submission['status_html'] = sprintf(
		'<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
		$status_color, $status_icon,
		ucfirst($submission['status'])
	);
	// add label
	if( array_key_exists('user-label', $submission['parameters']) && strlen($submission['parameters']['user-label']) > 0 ){
		$submission['label'] = $submission['parameters']['user-label'];
	}else{
		$submission['label'] = 'NO LABEL';
	}
}

// prepare data for the table viewer
$res = array(
	'size' => sizeof( $submissions ),
	'total' => $total_submissions,
	'data' => $submissions
);

// <== Here is the Magic Call!
TableViewer::generateTableViewer( Configuration::$PAGE, $res, $features, $table );
?>


<script type="text/javascript">

	var args = "<?php echo base64_encode(toQueryString( array_keys($features), $_GET )) ?>";

	function _submission_info( target ){
		var record = $(target).data('record');
		// open page here
		var url = "<?php echo sprintf('%s%s/{0}{1}{2}', Configuration::$BASE, 'submissions') ?>".format( record.submission_id, args.length>0? '?lst=' : '', args );
		location.href = url;
	}

</script>
