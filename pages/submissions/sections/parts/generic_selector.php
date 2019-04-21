<?php
use \system\classes\Configuration;

/*
This function takes a list of choices via the variable $choices in the following format:

  $choices = {
    [
      'id' => "Choice ID",
      'title' => "Choice Title",
      'active' => "Whether the choice is active"
    ],
    ...
  };

*/
function render_generic_selector($page_id, $sel_id, $choice_key, $choices, $choices_per_row, $features){
  if( intval($choices_per_row) < 1 )
    $choices_per_row = 2;
  ?>
  <nav class="navbar navbar-default" id="<?php echo $sel_id ?>_selector" role="navigation" style="margin-bottom:0px">
  	<div class="container-fluid" style="padding-left:0; padding-right:0">
  		<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
  			<table style="width:100%; height:50px; font-size:9pt">
  				<tr>
  					<?php
  					$num_choices = count($choices);
  					$col_width_perc = 100.0 / floatval($choices_per_row);
  					for($i=0; $i < $num_choices; $i++){
  						$ch = $choices[$i];
  						$is_last = $i == $num_choices-1;
  						$ch_key = sprintf('%s_%s', $choice_key, $ch['id']);
  						$is_active = $ch['active'];
  						?>
  						<td style="padding-left:14px; height:34px">
  							<input type="checkbox"
                  data-toggle="toggle"
                  data-onstyle="primary"
                  data-class="fast"
                  data-size="mini"
                  data-choice="<?php echo $ch['id'] ?>"
                  name="<?php echo $ch['title'] ?>"
                  <?php echo ($is_active)? 'checked' : '' ?> >
  						</td>
  						<td class="text-left mono text-bold" style="width:45px; padding:0 10px">
  							<?php echo $ch['id'] ?>
  						</td>
              <td class="text-align" style="border-right:1px solid lightgray; width:<?php echo $col_width_perc ?>%">
  							: &nbsp;&nbsp;<?php echo $ch['title'] ?>
  						</td>
  						<?php
  						if(($i+1) % $choices_per_row == 0){
  							echo '</tr><tr style="border-top:1px solid lightgray">';
  						}
  					}
  					// consume empty columns
  					$rows_needed = ceil(floatval($num_choices)/floatval($choices_per_row));
  					$empty_cols = $rows_needed*$choices_per_row - $num_choices;
  					for($i=0; $i < $empty_cols; $i++){
  						echo sprintf('<td></td><td style="border-right:1px solid lightgray; width:%s%%"></td>', $col_width_perc);
  					}
  					?>
  				</tr>
  			</table>
  		</div>
  	</div>
  </nav>

  <script type="text/javascript">
  	$('#<?php echo $sel_id ?>_selector :input').change(function(){
      showPleaseWait();
      var urls = {
    		<?php
    		foreach($choices as $choice){
    			echo sprintf(
    				"'%s_%s' : '%s',",
            $choice_key,
    				$choice['id'],
    				sprintf("%s%s%s",
    					Configuration::$BASE,
    					$page_id,
    					toQueryString(
    						array_keys($features),
    						$features['_valid'], true, true,
    						[sprintf('%s_%s', $choice_key, $choice['id'])]
    					)
    				)
    			);
    		}
    		?>
    	};
      // ---
  		var chId = "<?php echo $choice_key ?>_{0}".format($(this).data('choice'));
  		var isChecked = $(this).is(':checked')? 1 : 0;
  		// update results
  		var url = "{0}{1}".format(
  			urls[chId],
        // isChecked? '' : '{0}=0'.format(chId)
        '{0}={1}'.format(chId, isChecked)
  		);
  		window.location.href = url;
  	});
  </script>
  <?php
}
?>
