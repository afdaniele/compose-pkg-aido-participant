<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;
use \system\classes\enum\StringType;

$page_title = sprintf('AIDO%s - Submissions', AIDO::getAIDOversion());
$page_section = 'list';

// parse arguments
if( strlen(Configuration::$ACTION) > 0 && StringType::isValid(Configuration::$ACTION, StringType::NUMERIC) ){
	$page_title = sprintf('AIDO%s - Submission <span style="float:right">Submission ID: <span class="mono">%s</span></span>', AIDO::getAIDOversion(), Configuration::$ACTION);
	$page_section = 'submission';
}

?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:10px">

		<tr>
			<td style="width:100%">
				<h3><?php echo $page_title ?></h3>
			</td>
		</tr>

	</table>

	<?php
	// load section
	require_once __DIR__.'/sections/'.$page_section.'.php';
	?>

</div>
