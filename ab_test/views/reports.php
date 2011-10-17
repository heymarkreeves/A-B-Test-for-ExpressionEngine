<div>
	<? if ($results == 'none') {
	?>
	<h3>No tests</h3>
	<? } else { ?>
	<a href="<?=$reset_link?>">Reset All</a>
	<? foreach ($tests as $test)
	{ ?>
		<h3 style="padding-top: 12px;"><?=$test['test_name']?>, Displays: <?=$test['hits']?> [<a href="<?=$test['reset_link']?>">Reset</a>]</h3>
		<? foreach ($test['test_cases'] as $case)
		{ 
			if ($case['hits']!=0)
				{
				?>
				<h4><?=$case['case_name']?>, Displays: <?=$case['hits']?></h4>
				<ul>
					<? foreach ($case['actions'] as $action)
					{ ?>
						<? if ($action['action']!='' && $action['hits']!=0)
						{ ?><li>Hits: <?=$action['hits']?>, <?=$action['action']?></li><? } ?>
					<? } ?>
				</ul>
				<hr/>
			<? }
		} ?>
	<? }
	} ?>
</div>