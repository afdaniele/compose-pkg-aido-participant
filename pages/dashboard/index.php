<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;


//Core::getUserLogged('username')

$res = AIDODashboard::getUserInfo();
if( !$res['success'] )
Core::throwError( $res['data'] );
$stats = $res['data']['stats'];
?>


<div style="width:100%; margin:auto">

  <table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

    <tr>
      <td style="width:100%">
        <h2>Dashboard</h2>
      </td>
    </tr>

  </table>


  <nav class="navbar navbar-default" role="navigation" style="margin-bottom:60px">
    <div class="container-fluid" style="padding-left:0; padding-right:0">

      <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

        <table style="width:100%">
          <tr>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="border-bottom: 1px solid lightgray">
                <i class="fa fa-history" aria-hidden="true" style="float:left"></i>&nbsp;
                <span style="margin-left:-20px">Your submissions</span>
              </h4>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="border-bottom: 1px solid lightgray">
                <i class="fa fa-line-chart" aria-hidden="true" style="float:left"></i>&nbsp;
                <span style="margin-left:-20px">Your statistics</span>
              </h4>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="border-bottom: 1px solid lightgray">
                <i class="fa fa-trophy" aria-hidden="true" style="float:left"></i>&nbsp;
                <span style="margin-left:-16px">Your best submission</span>
              </h4>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="border-bottom: 1px solid lightgray">
                <i class="fa fa-clock-o" aria-hidden="true" style="float:left"></i>&nbsp;
                <span style="margin-left:-20px">AI-DO 2 ends in</span>
              </h4>
            </td>
          </tr>
          <tr style="border-bottom:1px solid lightgray">
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="padding:20px 0; font-weight:normal; font-size:45px">
                <?php echo $stats['total_submissions'] ?><br/>
              </h4>
              <a class="btn btn-default" href="<?php echo Core::getURL('submissions') ?>" role="button">Go to submissions list</a>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <div class="text-left" style="padding:10px 0; font-weight:normal; font-size:17px">
                <table style="width:100%">
                  <?php
                  $status_list = array_merge(['submitted'], AIDO::getSubmissionsStatusList());
                  foreach($status_list as $status){
                    $res = AIDO::getSubmissionsStatusStyle( $status );
                    $status_icon = $res['icon'];
                    $status_color = $res['color'];

                    $status_q = ($status == 'submitted')? 'null' : $status;
                    $num_subm = array_key_exists($status_q, $stats['submissions_status'])? $stats['submissions_status'][$status_q] : 0;
                    ?>
                    <tr>
                      <td class="col-md-2 text-left">
                        <?php echo $num_subm ?>
                      </td>
                      <td class="text-center">
                        <span style="color:lightgray">|</span>
                      </td>
                      <td class="text-center">
                        <?php
                        echo sprintf( '<span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i></span>', $status_color, $status_icon );
                        ?>
                      </td>
                      <td class="col-md-7 text-left">
                        <?php
                        echo ucfirst($status);
                        ?>
                      </td>
                    </tr>
                    <?php
                  }
                  ?>
                </table>
              </div>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="padding:10px 0; font-weight:normal">
                <?php
                // $score = 'score1';
                // $challenge = '2';
                // if( !isset($stats['best_submission_per_challenge'][$score][$challenge]) ){
                //   echo '<strong>None</strong>';
                // }else{
                //   $best_subm = $stats['best_submission_per_challenge'][$score][$challenge];
                //   ?>
                //   <!-- <p class="text-center">
                //     <a href="<?php echo Configuration::$BASE ?>submissions/<?php echo $best_subm['submission_id'] ?>" target="_self">Submission #<?php echo $best_subm['submission_id'] ?></a>
                //   </p>
                //   <p class="text-center">
                //     Score:<br/><br/>
                //     <span style="font-size: 25pt">
                //       <?php echo sprintf("%.2f", $best_subm['score']) ?>
                //     </span>
                //   </p> -->
                //   <?php
                // }
                ?>
              </h4>
            </td>
            <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
              <h4 style="padding:20px 0; font-weight:normal; padding-top:10px">
                <span id="aido_time_remaining" style="line-height:24px"></span>
              </h4>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </nav>

</div>



<script type="text/javascript">

var leftTime = moment([2019, 5, 15]).startOf('day').diff(moment(), 'seconds');
var duration = moment.duration(leftTime, 'seconds');

function update_time(){
  if (duration.asSeconds() <= 0) {
    window.location.reload(true);
  }
  //Otherwise
  duration = moment.duration(duration.asSeconds() - 1, 'seconds');
  $('#aido_time_remaining').html(
    "{0} month(s)<br/>{1} day(s)<br/>{2} hour(s)<br/>{3} minute(s)<br/>{4} second(s)".format(
      duration.months(),
      duration.days(),
      duration.hours(),
      duration.minutes(),
      duration.seconds()
    )
  );
}//update_time

$(document).ready(function(){
  update_time();
  setInterval(update_time, 1000);
});
</script>
