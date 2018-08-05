<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once __DIR__.'/../../utils/utils.php';

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDOParticipant;

$res = AIDOParticipant::getUserSubmissionsStats( Core::getUserLogged('username') );
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
	                <tr style="border-bottom:1px solid lightgray">
	                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
	                        <h4>
	                            <p style="border-bottom: 1px solid lightgray">
	                                <i class="fa fa-history" aria-hidden="true" style="float:left"></i>&nbsp;
	                                <span style="margin-left:-20px">Your submissions</span>
	                            </p>
	                            <p style="padding:30px 0; font-weight:normal; height:120px; font-size:45px">
	                                <?php echo $stats['total'] ?>
	                            </p>
	                        </h4>
	                    </td>
	                    <td class="col-md-3 text-center" style="border-right:1px solid lightgray">
	                        <h4>
	                            <p style="border-bottom: 1px solid lightgray">
	                                <i class="fa fa-line-chart" aria-hidden="true" style="float:left"></i>&nbsp;
	                                <span style="margin-left:-20px">Your statistics</span>
	                            </p>
	                            <div class="text-left" style="padding:10px 0; font-weight:normal; height:120px; font-size:17px">
									<?php
									foreach(['Queued', 'Running', 'Finished', 'Failed'] as $status){
										?>
										<p style="margin-bottom:8px">
										<?php
										$res = statusRender( $status );
										$status_icon = $res['icon'];
										$status_color = $res['color'];
										echo sprintf(
											'%s &nbsp; <span style="color:lightgray">|</span> &nbsp; <span style="color:%s"><i class="fa fa-%s" aria-hidden="true"></i>&nbsp; %s</span><br/>',
											$stats[$status], $status_color, $status_icon, $status
										);
										?>
										</p>
										<?php
									}
									?>
	                            </div>
	                        </h4>
	                    </td>
						<td class="col-md-3 text-center" style="border-right:1px solid lightgray">
	                        <h4>
	                            <p style="border-bottom: 1px solid lightgray">
	                                <i class="fa fa-trophy" aria-hidden="true" style="float:left"></i>&nbsp;
	                                <span style="margin-left:-16px">Your best submission</span>
	                            </p>
	                            <p style="padding:50px 0; font-weight:normal; height:120px">
									<strong>TODO</strong>
	                            </p>
	                        </h4>
	                    </td>
						<td class="col-md-3 text-center" style="border-right:1px solid lightgray">
	                        <h4>
	                            <p style="border-bottom: 1px solid lightgray">
	                                <i class="fa fa-clock-o" aria-hidden="true" style="float:left"></i>&nbsp;
	                                <span style="margin-left:-20px">AI-DO ends in</span>
	                            </p>
	                            <p style="padding:20px 0; font-weight:normal; height:120px; padding-top:10px">
									<span id="aido_time_remaining"></span>
	                            </p>
	                        </h4>
	                    </td>
	                </tr>
	            </table>
	        </div>
	    </div>
	</nav>

</div>

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
