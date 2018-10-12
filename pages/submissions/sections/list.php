<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Base58;
use \system\packages\duckietown\Duckietown;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;
use \system\templates\tableviewers\TableViewer;

$page_id = 'submissions';

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
			'align' => 'center',
			'translation' => 'ID',
			'editable' => false
		),
		'challenge_id' => array(
			'type' => 'text',
			'show' => true,
			'width' => 'md-1',
			'align' => 'center',
			'translation' => 'Challenge',
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
		'retire' => array(
			'type' => 'warning',
			'glyphicon' => 'trash',
			'tooltip' => 'Retire submission',
            'function' => array(
				'type' => '_toggle_modal',
				'class' => 'yes-no-modal',
				'text' => 'Retire',
				'API_resource' => 'submission',
				'API_action' => 'retire',
				'arguments' => [
					'submission_id'
				],
				'static_data' => [
					'question' => 'Are you sure you want to retire this submission?'
				]
			)
		)
	),
	'features' => array(
		// '_counter_column',
		'_actions_column'
	)
);


$res = AIDODashboard::getChallenges();
if( !$res['success'] ) Core::throwError( $res['data'] );
$challenges = $res['data'];


// TODO: remove
// $challenges = [
// 	['title' => 'A test of luck', 'challenge_id' => 2],
// 	['title' => 'aido1_test1', 'challenge_id' => 10],
// 	['title' => 'aido1_test2', 'challenge_id' => 11],
// 	['title' => 'aido1_test3', 'challenge_id' => 20]
// ];
// TODO: remove


//TODO: active
// filter challenges
// $tmp = [];
// foreach( $challenges as $ch ){
// 	if( in_array('aido1', $ch['tags']) )
// 		array_push($tmp, $ch);
// }
// $challenges = $tmp;
//TODO: active


// add challenges as features
foreach( $challenges as $ch ){
	$ch_key = sprintf('ch_%s', $ch['challenge_id']);
	$features[$ch_key] = [
		'type' => 'integer',
		'default' => 1
	];
}

// parse the arguments
TableViewer::parseFeatures( $features, $_GET );

// get challenges to show
$challenges_to_show = [];
foreach( $challenges as $ch ){
	$ch_key = sprintf('ch_%s', $ch['challenge_id']);
	if(booleanval($features[$ch_key]['value'])){
		array_push($challenges_to_show, $ch['challenge_id']);
	}
}
?>

<h4>Select the challenges:</h4>
<nav class="navbar navbar-default" id="aido_challenges_selector" role="navigation" style="margin-bottom:30px">
	<div class="container-fluid" style="padding-left:0; padding-right:0">
		<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
			<table style="width:100%; height:50px">
				<tr>
					<?php
					$col_width_perc = 100.0 / floatval(count($challenges));
					for( $i=0; $i < count($challenges); $i++ ){
						$ch = $challenges[$i];
						$is_last = $i == count($challenges)-1;
						$ch_key = sprintf('ch_%s', $ch['challenge_id']);
						$is_active = in_array( $ch['challenge_id'], $challenges_to_show );
						?>
						<td style="padding-left:14px">
							<input type="checkbox"
		                        data-toggle="toggle"
		                        data-onstyle="primary"
		                        data-class="fast"
		                        data-size="mini"
								data-challenge="<?php echo $ch['challenge_id'] ?>"
		                        name="<?php echo $ch['title'] ?>"
		                        <?php echo ($is_active)? 'checked' : '' ?>>
						</td>
						<td class="text-center" style="<?php echo !$is_last? 'border-right:1px solid lightgray;' : '';?> width:<?php echo $col_width_perc ?>%">
							<span style="float:left; padding-left:10px; font-weight:bold">(#<?php echo $ch['challenge_id'] ?>)</span>
							<?php echo $ch['title'] ?>
						</td>
						<?php
					}
					?>
				</tr>
			</table>
		</div>
	</div>
</nav>

<script type="text/javascript">
	var aido_challenges_urls = {
		<?php
		foreach( $challenges as $challenge ){
			echo sprintf(
				"'ch_%s' : '%s',",
				$challenge['challenge_id'],
				sprintf("%s%s%s",
					Configuration::$BASE,
					$page_id,
					toQueryString(
						array_keys($features),
						$features['_valid'], true, true,
						[sprintf('ch_%s',$challenge['challenge_id'])]
					)
				)
			);
		}
		?>
	};

	$('#aido_challenges_selector :input').change(function(){
		var chId = "ch_{0}".format( $(this).data('challenge') );
		var isChecked = $(this).is(':checked')? 1 : 0;
		// update results
		var url = "{0}{1}={2}".format(
			aido_challenges_urls[chId],
			chId,
			isChecked
		);
		window.location.href = url;
	});
</script>

<?php

// get submission for this challenge
$res = AIDODashboard::getUserSubmissions(
	null /*user_id*/,
	implode(',', $challenges_to_show),
	$features['tag']['value'],
	$features['page']['value'],
	$features['results']['value'],
	true, /* recent_first */
	$features['keywords']['value']
);
if( !$res['success'] ) Core::throwError( $res['data'] );
$submissions = $res['data'];
$total_submissions = $res['total'];
// convert `parameters`
foreach( $submissions as &$subm ){
	// label
	$subm['parameters'] = json_decode($subm['parameters'], true);
	$subm['label'] = strlen($subm['parameters']['user_label'])>0? $subm['parameters']['user_label'] : 'NO LABEL';
	// status
	$res = AIDO::getSubmissionsStatusStyle( $subm['status'] );
	$status_icon = $res['icon'];
	$status_color = $res['color'];
	$subm['status_html'] = sprintf(
		'<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
		$status_color, $status_icon,
		ucfirst($subm['status'])
	);
}
//
$num_submissions = count($submissions);

// prepare data for the table viewer
$res = array(
	'size' => $num_submissions,
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
