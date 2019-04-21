<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;


// get submission details
$submission_id = Configuration::$ACTION;
$res = AIDODashboard::getSubmission($submission_id);
if(!$res['success'])
  Core::throwError($res['data']);

$submission = $res['data'];
$submission['parameters'] = json_decode($submission['parameters'], true);
?>

<p style="margin-top:-10px; margin-bottom:40px">
  <?php
  $lst_args = isset($_GET['lst'])? base64_decode($_GET['lst']) : '';
  ?>
  <a href="<?php
    echo sprintf(
      '%s%s%s%s',
      Configuration::$BASE,
      'submissions',
      strlen($lst_args)>0? '?':'',
      $lst_args
    )
    ?>">
    &larr; Back to the list
  </a>
</p>

<?php

function _datetime_fcn($dt){
  return str_replace('T', ' ', $dt);
}

function _status_fcn($status){
  $res = AIDO::getSubmissionsStatusStyle($status);
  $status_icon = $res['icon'];
  $status_color = $res['color'];
  return sprintf(
    '<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
    $status_color,
    $status_icon,
    ucfirst($status)
  );
}

function _bool_fcn($val){
  return Formatter::format($val, Formatter::BOOLEAN);
}

function _num_jobs_fcn($jobs){
  return count($jobs);
}
?>


<table style="width:100%">
  <tr style="vertical-align: top">
    <td class="col-md-5" style="padding: 0; padding-right: 10px">

      <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
        <div class="container-fluid" style="padding-left:0; padding-right:0">
          <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
            <div class="col-md-12 text-left" style="padding:0">

              <table style="width:100%">
                <tr style="border-bottom: 1px solid lightgray;">
                  <td>
                    <h4 style="padding:0 12px">
                      <i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;Information
                    </h4>
                  </td>
                </tr>
                <tr>
                  <td style="padding:4px 14px">
                    <?php
                    $_show = [
                      'submission_id' => [
                        'title' => 'Submission ID',
                        'fcn' => null
                      ],
                      'challenge_id' => [
                        'title' => 'Challenge ID',
                        'fcn' => null
                      ],
                      'challenge_name' => [
                        'title' => 'Challenge',
                        'fcn' => null
                      ],
                      'status' => [
                        'title' => 'Status',
                        'fcn' => _status_fcn
                      ],
                      'complete' => [
                        'title' => 'Complete',
                        'fcn' => _bool_fcn
                      ],
                      'date_submitted' => [
                        'title' => 'Submission time (GMT)',
                        'fcn' => _datetime_fcn
                      ],
                      'last_status_change' => [
                        'title' => 'Last update (GMT)',
                        'fcn' => _datetime_fcn
                      ],
                      'jobs' => [
                        'title' => '# Jobs',
                        'fcn' => _num_jobs_fcn
                      ]
                    ];

                    foreach ($_show as $key => $value) {
                      $v = is_null($value['fcn'])? $submission[$key] : $value['fcn']($submission[$key]);
                      ?>
                      <h5 style="font-weight:normal">
                        <strong>
                          <?php echo $value['title'] ?>
                        </strong>: <?php echo $v ?>
                      </h5>
                      <?php
                    }
                    ?>
                  </td>
                </tr>
              </table>

            </div>
          </div>
        </div>
      </nav>

    </td>
    <!--  -->
    <td class="col-md-7" style="padding: 0; padding-left: 10px">

      <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
        <div class="container-fluid" style="padding-left:0; padding-right:0">
          <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
            <div class="col-md-12 text-left" style="padding:0">

              <table style="width:100%">
                <tr style="border-bottom: 1px solid lightgray;">
                  <td>
                    <h4 style="padding:0 12px">
                      <i class="fa fa-list-ol" aria-hidden="true"></i>&nbsp;Results
                    </h4>
                  </td>
                </tr>
                <tr>
                  <td style="padding:4px 14px">
                    <div style="overflow-y: scroll; height:220px;">
                      <?php
                      foreach ($submission['jobs'] as &$job) {
                        foreach ($job['stats']['scores'] as $key => &$score) {
                          if (is_array($score))
                            continue;
                          // ---
                          ?>
                          <h5 style="font-weight:normal">
                            <span class="mono text-bold"><?php echo $key ?></span>: <?php echo sprintf("%.3f", $score) ?>
                          </h5>
                          <?php
                        }
                      }
                      ?>
                    </div>
                  </td>
                </tr>
              </table>

            </div>
          </div>
        </div>
      </nav>

    </td>
  </tr>

  <tr>
    <td colspan="2">

      <!-- TAB: Jobs -->
      <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
        <div class="container-fluid" style="padding-left:0; padding-right:0">
          <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
            <div class="col-md-12 text-left" style="padding:0">

              <table style="width:100%">
                <tr style="border-bottom: 1px solid lightgray;">
                  <td>
                    <h4 style="padding:0 12px">
                      <i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;Jobs
                    </h4>
                  </td>
                </tr>
                <tr>
                  <td style="padding:4px 14px">
                    <table class="table table-striped text-center" style="margin:22px 0; font-size:12pt;">
                      <tr>
                        <td class="col-md-2">Job ID</td>
                        <td class="col-md-2">Evaluator ID</td>
                        <td class="col-md-3">Started (GMT)</td>
                        <td class="col-md-3">Completed (GMT)</td>
                        <td class="col-md-2">Result</td>
                      </tr>
                      <?php
                      $i = 1;
                      foreach($submission['jobs'] as $job){
                        ?>
                        <tr>
                          <td>
                            <?php
                            $url = AIDODashboard::linkToExternalResource('jobs', $job['job_id']);
                            echo sprintf(
                              '<a href="%s" target="_blank">%s&nbsp;%s</a>',
                              $url,
                              $job['job_id'],
                              '<i class="fa fa-external-link" aria-hidden="true" style="color:lightgray"></i>'
                            );
                            ?>
                          </td>
                          <td>
                            <?php
                            $url = AIDODashboard::linkToExternalResource('evaluators', $job['evaluator_id']);
                            echo sprintf(
                              '<a href="%s" target="_blank">%s&nbsp;%s</a>',
                              $url,
                              $job['evaluator_id'],
                              '<i class="fa fa-external-link" aria-hidden="true" style="color:lightgray"></i>'
                            );
                            ?>
                          </td>
                          <td><?php echo str_replace('T', ' ', $job['date_started']) ?></td>
                          <td><?php echo str_replace('T', ' ', $job['date_completed']) ?></td>
                          <td>
                            <?php
                            $res = AIDO::getSubmissionsStatusStyle($job['status']);
                            $status_icon = $res['icon'];
                            $status_color = $res['color'];
                            echo sprintf(
                              '<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
                              $status_color, $status_icon,
                              ucfirst($job['status'])
                            );
                            ?>
                          </td>
                        </tr>
                        <?php
                        $i++;
                      }
                      ?>
                    </table>
                  </td>
                </tr>
              </table>
            </div>

          </div>
        </div>
      </nav>

    </td>
  </tr>
</table>
