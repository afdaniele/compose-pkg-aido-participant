<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;


// get submission details
$submission_id = Configuration::$ACTION;
$res = AIDODashboard::getSubmission( $submission_id );
if( !$res['success'] ) Core::throwError( $res['data'] );

$submission = $res['data'];
$submission['parameters'] = json_decode($submission['parameters'], true)
?>

<p style="margin-top:-20px; margin-bottom:40px">
    <?php
    $lst_args = isset($_GET['lst'])? base64_decode($_GET['lst']) : '';
    ?>
    <a href="<?php echo sprintf('%s%s%s%s', Configuration::$BASE, 'submissions', strlen($lst_args)>0? '?':'', $lst_args) ?>">&larr; Back to the list</a>
</p>


<!-- TAB: Misc -->
<table style="width:100%">
    <tr>
        <td class="col-md-3" style="padding-left:0">
            <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
                <div class="container-fluid" style="padding-left:0; padding-right:0">

                    <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

                        <div class="col-md-12 text-center" style="padding:0">

                            <table style="width:100%">
                                <tr style="border-bottom: 1px solid lightgray;">
                                    <td>
                                        <h4 style="padding:0 12px">
                                            <i class="fa fa-clock-o" aria-hidden="true" style="float:left"></i>&nbsp;
                                            <span style="margin-left:-20px">Submitted (GMT)</span>
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px">
                                        <h4 style="font-weight:normal">
                                            <?php
                                            echo $submission['date_submitted'];
                                            ?>
                                        </h4>
                                    </td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </div>
            </nav>
        </td>
        <!--  -->
        <td class="col-md-3">
            <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
                <div class="container-fluid" style="padding-left:0; padding-right:0">

                    <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

                        <div class="col-md-12 text-center" style="padding:0">

                            <table style="width:100%">
                                <tr style="border-bottom: 1px solid lightgray;">
                                    <td>
                                        <h4 style="padding:0 12px">
                                            <i class="fa fa-filter" aria-hidden="true" style="float:left"></i>&nbsp;
                                            <span style="margin-left:-20px">Status</span>
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px">
                                        <h4 style="font-weight:normal">
                                            <?php
                                            $res = AIDO::getSubmissionsStatusStyle( $submission['status'] );
                                        	$status_icon = $res['icon'];
                                        	$status_color = $res['color'];
                                        	echo sprintf(
                                                '<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
                                                $status_color, $status_icon,
                                                ucfirst($submission['status'])
                                            );
                                            ?>
                                        </h4>
                                    </td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </div>
            </nav>
        </td>
        <!--  -->
        <td class="col-md-6" style="padding-right:0">
            <nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
                <div class="container-fluid" style="padding-left:0; padding-right:0">

                    <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

                        <div class="col-md-12 text-center" style="padding:0">

                            <table style="width:100%">
                                <tr style="border-bottom: 1px solid lightgray;">
                                    <td>
                                        <h4 style="padding:0 12px">
                                            <i class="fa fa-tag" aria-hidden="true" style="float:left"></i>&nbsp;
                                            <span style="margin-left:-20px">Label</span>
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px">
                                        <h4 style="font-weight:normal">
                                            &ldquo;<?php echo $submission['parameters']['user_label']; ?>&rdquo;
                                        </h4>
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



<!-- TAB: Submissions Image -->
<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center" style="padding:0">

                <table style="width:100%">
                    <tr style="border-bottom: 1px solid lightgray;">
                        <td>
                            <h4 style="padding:0 12px">
                                <i class="fa fa-folder-open" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Submission Images</span>
                            </h4>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px">
                            <table class="table table-striped" style="margin:22px 0; font-size:12pt;">
                                <tr>
                                    <td class="col-md-1">#</td>
                                    <td class="col-md-3">Repository</td>
                                    <td class="col-md-3">Tag</td>
                                    <td class="col-md-5">Actions</td>
                                </tr>
                                <?php
                                $i = 1;
                                foreach( [ $submission['parameters']['hash'] ] as $hash ){
                                    $hash_parts = explode(':', $hash);
                                    ?>
                                    <tr>
                                        <td><?php echo $i ?></td>
                                        <td><?php echo $hash_parts[0] ?></td>
                                        <td><?php echo $hash_parts[1] ?></td>
                                        <td>
                                            <a role="button" class="btn btn-info btn-sm" href="<?php echo sprintf('https://hub.docker.com/r/%s/', $hash_parts[0]); ?>" target="_blank">
                                                Open in Docker Hub
                                            </a>
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



<!-- TAB: Jobs -->
<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center" style="padding:0">

                <table style="width:100%">
                    <tr style="border-bottom: 1px solid lightgray;">
                        <td>
                            <h4 style="padding:0 12px">
                                <i class="fa fa-tasks" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Jobs</span>
                            </h4>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px">
                            <table class="table table-striped" style="margin:22px 0; font-size:12pt;">
                                <tr>
                                    <td class="col-md-1">#</td>
                                    <td class="col-md-1">Job ID</td>
                                    <td class="col-md-2">Evaluator ID</td>
                                    <td class="col-md-3">Completed (GMT)</td>
                                    <td class="col-md-3">Result</td>
                                    <td class="col-md-2">Score</td>
                                </tr>
                                <?php
                                $i = 1;
                                foreach( $submission['jobs'] as $job ){
                                    ?>
                                    <tr>
                                        <td><?php echo $i ?></td>
                                        <td><?php echo $job['job_id'] ?></td>
                                        <td><?php echo $job['evaluator_id'] ?></td>
                                        <td><?php echo $job['date_completed'] ?></td>
                                        <td>
                                            <?php
                                            $res = AIDO::getSubmissionsStatusStyle( $job['status'] );
                                        	$status_icon = $res['icon'];
                                        	$status_color = $res['color'];
                                        	echo sprintf(
                                                '<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>',
                                                $status_color, $status_icon,
                                                ucfirst($job['status'])
                                            );
                                            ?>
                                        </td>
                                        <td><?php echo sprintf('%.4f', $job['stats']['scores']['score1']) ?></td>
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



<!-- TAB: Data -->
<!-- TODO -->
<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center" style="padding:0">

                <table style="width:100%">
                    <tr style="border-bottom: 1px solid lightgray;">
                        <td>
                            <h4 style="padding:0 12px">
                                <i class="fa fa-database" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Data</span>
                            </h4>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 20px">
                            <h4 style="font-weight:normal">
                                <strong>TODO:</strong> Here we will show a list of logs that the participant can download and use to check what went wrong
                            </h4>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</nav>
