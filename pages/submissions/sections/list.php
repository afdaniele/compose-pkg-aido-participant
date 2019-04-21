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

$page_id = Configuration::$PAGE;


$groups = [
  ['id' => 'LF', 'title' => '🚗 - Lane following'],
  ['id' => 'LFV', 'title' => '🚗🚗 - Lane following + Vehicles'],
  ['id' => 'LFVI', 'title' => '🚗🚗🚦 - Lane following + Vehicles + Intersections'],
  ['id' => 'AMOD', 'title' => '🛎 - Autonomous Mobility on Demand']
];
$fields = [
  ['id' => 'val', 'title' => '🏋 - Validation'],
  ['id' => 'tst', 'title' => '🥇 - Testing']
];
$environments = [
  ['id' => 'sim', 'title' => '👾 - Simulation'],
  ['id' => 'rob', 'title' => '🏎 - Robotarium']
];

$groups_list = ['LF', 'LFV', 'LFVI', 'aido2-amod'];
$fields_list = ['ml-validation', 'ml-testing'];
$environments_list = ['simulation', 'robotarium'];

$keys_and_choices = [
  'ch' => &$groups,
  'ml' => &$fields,
  'env' => &$environments
];

$key_to_tag = [
  'ch_LF' => 'LF',
  'ch_LFV' => 'LFV',
  'ch_LFVI' => 'LFVI',
  'ch_AMOD' => 'aido2-amod',
  'ml_val' => 'ml-validation',
  'ml_tst' => 'ml-testing',
  'env_sim' => 'simulation',
  'env_rob' => 'robotarium'
];

$tag_to_icon = [
  'LF' => '🚗',
  'LFV' => '🚗🚗',
  'LFVI' => '🚗🚗🚦',
  'aido2-amod' => '🛎',
  'ml-validation' => '🏋',
  'ml-testing' => '🥇',
  'simulation' => '👾',
  'robotarium' => '🏎'
];
function tag_to_icon_fcn($tag){
  global $tag_to_icon;
  return $tag_to_icon[$tag];
}

$short_tags = [
  'LF' => 'LF',
  'LFV' => 'LFV',
  'LFVI' => 'LFVI',
  'aido2-amod' => 'AMOD',
  'ml-validation' => 'val',
  'ml-testing' => 'tst',
  'simulation' => 'sim',
  'robotarium' => 'rob'
];
function short_tag_fcn($tag){
  global $short_tags;
  return $short_tags[$tag];
}

$active_tags = [
  'visible',
  AIDO::getChallengesAPIversion(),
  sprintf('aido%s', AIDO::getAIDOversion()),
  sprintf('aido%s-embodied', AIDO::getAIDOversion())
];

$disabled_tags = [
  sprintf('aido%s-testing', AIDO::getAIDOversion())
];

$ch_features = [];
foreach($key_to_tag as $key => $_){
  $ch_features[$key] = [
    'type' => 'integer',
		'default' => 2
  ];
}
// parse the arguments
TableViewer::parseFeatures($ch_features, $_GET);

// fuse given keys with memory
$last_selection = isset($_SESSION['_AIDO_CHALLENGES_LAST_SELECTION'])?
  $_SESSION['_AIDO_CHALLENGES_LAST_SELECTION'] : [];

$ch_features['_valid'] = array_merge($last_selection, $ch_features['_valid']);

$_SESSION['_AIDO_CHALLENGES_LAST_SELECTION'] = $ch_features['_valid'];

// show/hide challenges based on preferences
foreach($keys_and_choices as $key => &$choices){
  foreach($choices as &$choice){
    $choice_key = sprintf('%s_%s', $key, $choice['id']);
    $is_active = !array_key_exists($choice_key, $ch_features['_valid']) || boolval($ch_features['_valid'][$choice_key]);
    $choice['active'] = boolval($is_active);
    if($is_active){
      array_push($active_tags, $key_to_tag[$choice_key]);
    }
  }
}

// get all challenges
$res = AIDODashboard::getChallenges();
if(!$res['success'])
  Core::throwError($res['data']);
$challenges = $res['data'];

// keep only the challenges selected
function filter_challenges(&$challenges, $active_tags, $disabled_tags){
  global $key_to_tag;
  if(!is_array($active_tags))
    $active_tags = [$active_tags];
  $all_tags = array_values($key_to_tag);
  $disabled_tags = array_merge($disabled_tags, array_diff($all_tags, $active_tags));
  return array_filter(
    $challenges,
    function($ch) use($disabled_tags){
      return count(array_intersect($disabled_tags, $ch['tags'])) == 0;
    }
  );
}//filter_challenges

$challenges = filter_challenges($challenges, $active_tags, $disabled_tags);

// get challenges IDs, Tags, and create map {id -> tag}
$challenges_ids = array_map(
  function($ch){ return $ch['challenge_id']; },
  $challenges
);
$challenges_tags = array_map(
  function($ch) use($groups_list, $fields_list, $environments_list){
    return [
      array_intersect($ch['tags'], $groups_list),
      array_intersect($ch['tags'], $fields_list),
      array_intersect($ch['tags'], $environments_list)
    ];
  },
  $challenges
);
$challenges_tags = array_combine($challenges_ids, $challenges_tags);

// define table features
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
    'tags' => array(
      'type' => 'text',
      'show' => true,
      'width' => 'md-2',
      'align' => 'center',
      'translation' => 'Tags',
      'editable' => false
    ),
    'user_label' => array(
      'type' => 'text',
      'show' => true,
      'width' => 'md-3',
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

// parse the arguments
TableViewer::parseFeatures($features, $_GET);

?>

<?php
include_once __DIR__.'/parts/generic_selector.php';
?>
<table style="width:100%; margin-bottom:30px">
  <tr>
    <td style="width:46%">
      <h5>Challenge group:</h5>
      <?php
      render_generic_selector($page_id, 'aido_group', 'ch', $groups, 1, $ch_features);
      ?>
    </td>
    <td style="width:2%; border-right: 1px solid lightgrey"></td>
    <td style="width:2%"></td>
    <td style="width:23%; vertical-align:top;">
      <h5 class="text-left">Field:</h5>
      <?php
      render_generic_selector($page_id, 'aido_field', 'ml', $fields, 1, $ch_features);
      ?>
    </td>
    <td style="width:4%"></td>
    <td style="width:23%; vertical-align:top;">
      <h5 class="text-right">Environment:</h5>
      <?php
      render_generic_selector($page_id, 'aido_env', 'env', $environments, 1, $ch_features);
      ?>
    </td>
    <td>
  </tr>
</table>



<?php

// $res = AIDO::callChallengesAPI(
//   'GET',
//   'submissions-list',
//   null/*action*/,
//   $data,
//   []/*headers*/,
//   null
// );
//
//
// echoArray($res);
//
// return;


$challenges_to_query = (count($challenges_ids) == 0)? [999999] : $challenges_ids;

// $challenges_to_query = reset($challenges_to_query);

// get submission for this challenge
$res = AIDODashboard::getUserSubmissions(
	null /*user_id*/,
	$challenges_to_query,
	$features['tag']['value'],
	$features['page']['value'],
	$features['results']['value'],
	true, /* recent_first */
	$features['keywords']['value']
);

// echoArray($res);
// return;

if(!$res['success'])
  Core::throwError($res['data']);

// return;

$submissions = $res['data'];
$total_submissions = $res['total'];
// convert `parameters`
foreach($submissions as &$subm){
  // label
  $subm['user_label'] = ($subm['user_label'] == 'null')? '(empty)' : $subm['user_label'];
	// status
  $subm_status = (strlen($subm['status']) > 0)? $subm['status'] : 'submitted';
	$res = AIDO::getSubmissionsStatusStyle($subm_status);
	$status_icon = $res['icon'];
	$status_color = $res['color'];
	$subm['status_html'] = sprintf(
		'<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
		$status_color, $status_icon,
		ucfirst($subm_status)
	);
  // tags
  $subm['tags'] = implode(
    ', ',
    array_map(
      short_tag_fcn,
      array_merge(...$challenges_tags[$subm['challenge_id']])
    )
  );
  $subm['tags'] = sprintf(
    '<span class="mono">%s</span>',
    (strlen($subm['tags']) > 0)? $subm['tags'] : '(none)'
  );
  // datetime
  $subm['date_submitted'] = str_replace('T', ' ', $subm['date_submitted']);
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
TableViewer::generateTableViewer(Configuration::$PAGE, $res, $features, $table);
?>


<script type="text/javascript">

	var args = "<?php echo base64_encode(toQueryString(array_keys($features), $_GET)) ?>";

	function _submission_info(target){
		var record = $(target).data('record');
		// open page here
		var url = "<?php echo sprintf('%s%s/{0}{1}{2}', Configuration::$BASE, 'submissions') ?>".format(record.submission_id, args.length>0? '?lst=' : '', args);
		location.href = url;
	}

</script>
