<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;
use \system\packages\aido_dashboard\AIDODashboard;


//Core::getUserLogged('username')

$res = AIDODashboard::getUserSubmissionsStats();
if( !$res['success'] ) Core::throwError( $res['data'] );
$stats = $res['data'];

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
                                <span style="margin-left:-20px">AI-DO ends in</span>
	                        </h4>
	                    </td>
	                </tr>
					<tr style="border-bottom:1px solid lightgray">
	                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
	                        <h4 style="padding:30px 0; font-weight:normal; font-size:45px">
                                <?php echo $stats['total_submissions'] ?>
	                        </h4>
	                    </td>
	                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
                            <div class="text-left" style="padding:10px 0; font-weight:normal; font-size:17px">
								<table style="width:100%">
									<?php
									foreach(AIDO::getSubmissionsStatusList() as $status){
										$res = AIDO::getSubmissionsStatusStyle( $status );
										$status_icon = $res['icon'];
										$status_color = $res['color'];
										?>
										<tr>
											<td class="col-md-2 text-left">
												<?php echo $stats['submissions_status'][$status] ?>
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
								$score = 'score1';
								$challenge = '2';
								if( !isset($stats['best_submission_per_challenge'][$score][$challenge]) ){
									echo '<strong>None</strong>';
								}else{
									$best_subm = $stats['best_submission_per_challenge'][$score][$challenge];
									?>
									<p class="text-center">
										<a href="<?php echo Configuration::$BASE ?>submissions/<?php echo $best_subm['submission_id'] ?>" target="_self">Submission #<?php echo $best_subm['submission_id'] ?></a>
									</p>
									<p class="text-center">
										Score:<br/><br/>
										<span style="font-size: 25pt">
											<?php echo sprintf("%.2f", $best_subm['score']) ?>
										</span>
									</p>
									<?php
								}
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


<?php

// echoArray(
// 	AIDO::callChallengesAPI( 'GET', 'submissions', null, [
// 		'challenge_id' => '2,3',
// 		'status' => 'success',
// 		'keywords' => '',
// 		'sort_by' => 'date',
// 		'sort_order' => 'ASC',
// 		'page' => 3,
// 		'results' => 1
// 	])
// );

// echoArray(
// 	AIDO::callChallengesAPI( 'GET', 'submission', '16' )
// );

// echoArray(
// 	AIDO::callChallengesAPI( 'GET', 'info' )
// );

?>




<script type="text/javascript">

	var leftTime = moment([2018, 11, 7]).startOf('day').diff(moment(), 'seconds');
	var duration = moment.duration(leftTime, 'seconds');

	function update_time(){
		if (duration.asSeconds() <= 0) {
			window.location.reload(true);
		}
		//Otherwise
		duration = moment.duration(duration.asSeconds() - 1, 'seconds');
		$('#aido_time_remaining').html(
			"{0} months<br/>{1} days<br/>{2} hours<br/>{3} minutes<br/>{4} seconds".format(
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
