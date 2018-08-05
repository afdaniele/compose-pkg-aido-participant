<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';
require_once __DIR__.'/../../../utils/utils.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDOParticipant;


// get submission details
$submission_id = Configuration::$ACTION;
$res = AIDOParticipant::getSubmission( Core::getUserLogged('username'), $submission_id );
if( !$res['success'] ) Core::throwError( $res['data'] );

$submission = $res['data'];
?>

<p style="margin-top:-20px; margin-bottom:40px">
    <?php
    $lst_args = isset($_GET['lst'])? base64_decode($_GET['lst']) : '';
    ?>
    <a href="<?php echo sprintf('%s%s%s%s', Configuration::$BASE, 'submissions', strlen($lst_args)>0? '?':'', $lst_args) ?>">&larr; Back to the list</a>
</p>


<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <table style="width:100%">
                <tr style="border-bottom:1px solid lightgray">
                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
                        <h4>
                            <p style="border-bottom: 1px solid lightgray">
                                <i class="fa fa-clock-o" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Submitted (GMT)</span>
                            </p>
                            <p style="padding:20px 0; font-weight:normal">
                                <?php
                                echo $submission['datetime'];
                                ?>
                            </p>
                        </h4>
                    </td>
                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
                        <h4>
                            <p style="border-bottom: 1px solid lightgray">
                                <i class="fa fa-filter" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Status</span>
                            </p>
                            <p style="padding:20px 0; font-weight:normal">
                                <?php
                                $res = statusRender( $submission['status'] );
                            	$status_icon = $res['icon'];
                            	$status_color = $res['color'];
                            	echo sprintf('<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span>', $status_color, $status_icon, $submission['status']);
                                ?>
                            </p>
                        </h4>
                    </td>
                    <td class="col-md-6 text-center">
                        <h4>
                            <p style="border-bottom: 1px solid lightgray">
                                <i class="fa fa-tag" aria-hidden="true" style="float:left"></i>&nbsp;
                                <span style="margin-left:-20px">Label</span>
                            </p>
                            <p class="text-left" style="padding:20px 0; font-weight:normal">
                                &ldquo;<?php echo $submission['label']; ?>&rdquo;
                            </p>
                        </h4>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</nav>


<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center">
                <h4>
                    <p style="border-bottom: 1px solid lightgray">
                        <i class="fa fa-folder-open" aria-hidden="true" style="float:left"></i>&nbsp;
                        <span style="margin-left:-20px">Content</span>
                    </p>
                    <p style="padding:20px 0; font-weight:normal">
                        <strong>TODO:</strong> Here we will show which Docker containers will be running
                    </p>
                </h4>
            </div>

        </div>
    </div>
</nav>


<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center">
                <h4>
                    <p style="border-bottom: 1px solid lightgray">
                        <i class="fa fa-bar-chart-o" aria-hidden="true" style="float:left"></i>&nbsp;
                        <span style="margin-left:-20px">Results</span>
                    </p>
                    <p style="padding:20px 0; font-weight:normal">
                        <strong>TODO:</strong> Here we will show results (visible only when the status is either Finished or Failed)
                    </p>
                </h4>
            </div>

        </div>
    </div>
</nav>


<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

            <div class="col-md-12 text-center">
                <h4>
                    <p style="border-bottom: 1px solid lightgray">
                        <i class="fa fa-database" aria-hidden="true" style="float:left"></i>&nbsp;
                        <span style="margin-left:-20px">Data</span>
                    </p>
                    <p style="padding:20px 0; font-weight:normal">
                        <strong>TODO:</strong> Here we will show a list of logs that the participant can download and use to check what went wrong
                    </p>
                </h4>
            </div>

        </div>
    </div>
</nav>
