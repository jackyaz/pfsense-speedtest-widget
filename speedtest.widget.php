<?php

/*
 * speedtest.widget.php
 *
 * Copyright (c) 2020 Alon Noy
 * Copyright (c) 2021 Rudie Shahinian
 *
 * Licensed under the GPL, Version 3.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("guiconfig.inc");
require_once("/usr/local/www/widgets/include/interfaces.inc");
function get_isp_list() {
    $ispsList = [];
    try {
        $getIsps = shell_exec("speedtest -L -f json");
        $jsonObj  = json_decode($getIsps);
        $ispsList = $jsonObj->{'servers'};
        if (is_null($jsonObj)) {
          throw ('Error');
        }
    } catch (Exception $e) {

    }
    return $ispsList;
}

$ifdescrs = get_configured_interface_with_descr();
$isps_list = get_isp_list();

if ($_POST['widgetkey'] && !$_REQUEST['ajax']) {
	set_customwidgettitle($user_settings);

	$validNames = array();

	foreach ($ifdescrs as $ifdescr => $ifname) {
		array_push($validNames, $ifdescr);
	}
	if (is_array($_POST['ifaces'])) {
		$user_settings['widgets'][$_POST['widgetkey']]['iffilter'] = implode(',', array_diff($validNames, $_POST['ifaces']));
	} else {
		$user_settings['widgets'][$_POST['widgetkey']]['iffilter'] = implode(',', $validNames);
	}

	if ($_POST['ispselect']) {
        $isp_selected_key = array_search($_POST['ispselect'], array_column($isps_list, 'id'));
		$user_settings['widgets'][$_POST['widgetkey']]['ispselected'] = json_encode($isps_list[$isp_selected_key]);
	} 
    save_widget_settings($_SESSION['Username'], $user_settings["widgets"], gettext("Updated speedtest widget settings via dashboard."));
	header("Location: /");
    exit(0);
}

// When this widget is included in the dashboard, $widgetkey is already defined before the widget is included.
// When the ajax call is made to refresh the interfaces table, 'widgetkey' comes in $_REQUEST.
if ($_REQUEST['widgetkey']) {
	$widgetkey = $_REQUEST['widgetkey'];
}

$isp_selected = $isps_list[0];
if($user_settings['widgets'][$widgetkey]['ispselected']) {
    $isp_selected = json_decode($user_settings['widgets'][$widgetkey]['ispselected']);
};
if (isset($_REQUEST['ajax']) && isset($_REQUEST['source_ip']) && isset($_REQUEST['isp_id']) && isset($_REQUEST['iface'])) {
    $st_cmd = 'speedtest -s ' . $_REQUEST['isp_id'] . ' -i ' . $_REQUEST['source_ip'] . ' -f json 2>&1';
    $speedtest = shell_exec($st_cmd);
    $results = '{"iface": "'.$_REQUEST['iface'].'", "cmd": "'.$st_cmd.'", "results": '.end(preg_split("/\n/",trim($speedtest))).'}';
   
    $config['widgets']['speedtest_results_'.$_REQUEST['isp_id']][$_REQUEST['iface']] = $results;
    write_config("Save speedtest results");
    echo $config['widgets']['speedtest_results_'.$_REQUEST['isp_id']][$_REQUEST['iface']] ;
    exit(0);
} else {
    $results = isset($config['widgets']['speedtest_results_'.$isp_selected->{'id'}]) ? $config['widgets']['speedtest_results_'.$isp_selected->{'id'}] : [];
?>

<?php if($isp_selected) { ?>
<div class="table-responsive" >
	<table class="table table-condensed">
        <tr>
            <td>Selected ISP: <?=$isp_selected->{'name'}?> (<?=$isp_selected->{'location'}?>)</td>
        </tr>
    </table>
</div>
<?php } ?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-condensed">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Interface</th>
				<th style="text-align: center;" >Ping</th>
				<th style="text-align: center;" >Download</th>
				<th style="text-align: center;" >Upload</th>
				<th>Date</th>
			</tr>
		</thead>
		<tbody id="<?=htmlspecialchars($widgetkey)?>-sttblbody">
		</tbody>
	</table>
</div>

</div><div id="<?=$widget_panel_footer_id?>" class="panel-footer collapse">
<form action="/widgets/widgets/speedtest.widget.php" method="post" class="form-horizontal">
	<?=gen_customwidgettitle_div($widgetconfig['title']); ?>

    <div class="panel panel-default col-sm-10">
		<div class="panel-body">
			<input type="hidden" name="widgetkey" value="<?=htmlspecialchars($widgetkey); ?>">
			<div class="table responsive">
				<table class="table table-striped table-hover table-condensed" id="isp_list_selection">
					<thead>
						<tr>
							<th>ISP</th>
							<th>Select</th>
						</tr>
					</thead>
					<tbody >
<?php
				foreach ($isps_list as $i => $isp):
?>
						<tr>
							<td><?=$isp->{'name'}?> (<?=$isp->{'location'}?>)</td>
							<td class="col-sm-2"><input id="ispselect_<?=$isp->{'id'}?>" name ="ispselect" value="<?=$isp->{'id'}?>" type="radio" <?=($isp->{'id'} === $isp_selected->{'id'} ? 'checked':'')?>></td>
						</tr>
<?php
				endforeach;
?>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>
    <div class="panel panel-default col-sm-10">
		<div class="panel-body">
			<div class="table responsive">
				<table class="table table-striped table-hover table-condensed">
					<thead>
						<tr>
							<th><?=gettext("Interface")?></th>
							<th><?=gettext("Show")?></th>
						</tr>
					</thead>
					<tbody>
<?php
				$skipinterfaces = explode(",", $user_settings['widgets'][$widgetkey]['iffilter']);

				foreach ($ifdescrs as $ifdescr => $ifname):
?>
						<tr>
							<td><?=$ifname?></td>
							<td class="col-sm-2"><input id="ifaces[]" name ="ifaces[]" value="<?=$ifdescr?>" type="checkbox" <?=(!in_array($ifdescr, $skipinterfaces) ? 'checked':'')?>></td>
						</tr>
<?php
				endforeach;
?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<button type="submit" class="btn btn-primary"><i class="fa fa-save icon-embed-btn"></i><?=gettext('Save')?></button>
		</div>
	</div>
</form>




<script type="text/javascript">

function add_st_row(iface, iface_name, iface_source_ip, results) {
    $('#<?=htmlspecialchars($widgetkey)?>-sttblbody:last-child').append('<tr id="sp_iface_'+iface+'"></tr>');
    $("#sp_iface_"+iface).append('<td class="speedtest-reload"><a href="#" class="fa fa-refresh"></a></td>')
    $("#sp_iface_"+iface).append('<td class="speedtest-iface">'+iface_name+'</td>');
    $("#sp_iface_"+iface).append('<td style="text-align: center;" class="speedtest-error" colspan="3"></td>');
    $("#sp_iface_"+iface+" .speedtest-error").hide();
    $("#sp_iface_"+iface).append('<td style="text-align: center;" class="speedtest-ping"></td>');
    $("#sp_iface_"+iface).append('<td style="text-align: center;" class="speedtest-download"></td>');
    $("#sp_iface_"+iface).append('<td style="text-align: center;" class="speedtest-upload"></td>');
    $("#sp_iface_"+iface).append('<td class="speedtest-ts"></td>');
    update_st_row(iface, results);
    $("#sp_iface_"+iface+" .speedtest-reload a").click(function() {
        clear_st_row(iface);
        $("#sp_iface_"+iface+" .speedtest-ping").html('<i class="fa fa-spinner fa-spin"></i>');
        $("#sp_iface_"+iface+" .speedtest-download").html('<i class="fa fa-spinner fa-spin"></i>');
        $("#sp_iface_"+iface+" .speedtest-upload").html('<i class="fa fa-spinner fa-spin"></i>');
        run_speedtest(iface, iface_source_ip);
        return false;
    });
    $("#sp_iface_"+iface).show();
}
function update_st_row(iface, results) {
    if(results) {
        if (results.results.error) {
            $("#sp_iface_"+iface+" .speedtest-error").html('<small class="text-warning"><i class="fa fa-exclamation-triangle fa-1x"></i> '+results.results.error+'</small>').show();
            $("#sp_iface_"+iface+" .speedtest-ping").empty().hide();
            $("#sp_iface_"+iface+" .speedtest-download").empty().hide();
            $("#sp_iface_"+iface+" .speedtest-upload").empty().hide();
            $("#sp_iface_"+iface+" .speedtest-ts").html('');
        } else {
            var date = new Date(results.results.timestamp).toLocaleString();
            if (results.results.type == 'result') {
                $("#sp_iface_"+iface+" .speedtest-error").empty().hide();
                $("#sp_iface_"+iface+" .speedtest-ping").html('<i class="fa fa-satellite-dish"></i> '+results.results.ping.latency.toFixed(2) + '<small> ms</small>').show();
                $("#sp_iface_"+iface+" .speedtest-download").html('<i class="fa fa-download"></i> '+(results.results.download.bandwidth / 100000).toFixed(2) + '<small> Mbps</small>').show();
                $("#sp_iface_"+iface+" .speedtest-upload").html('<i class="fa fa-upload"></i> '+(results.results.upload.bandwidth / 100000).toFixed(2) + '<small> Mbps</small>').show();
                $("#sp_iface_"+iface+" .speedtest-ts").html('<small>'+date+'</small>');
            } else {
                $("#sp_iface_"+iface+" .speedtest-error").html('<small class="text-warning"><i class="fa fa-exclamation-triangle fa-1x"></i> '+results.results.message+'</small>').show();
                $("#sp_iface_"+iface+" .speedtest-ping").empty().hide();
                $("#sp_iface_"+iface+" .speedtest-download").empty().hide();
                $("#sp_iface_"+iface+" .speedtest-upload").empty().hide();
                $("#sp_iface_"+iface+" .speedtest-ts").html('<small>'+date+'</small>');
            }
        }
    } 
}



function clear_st_row(iface) {
    $("#sp_iface_"+iface+" .speedtest-ts").empty();
    $("#sp_iface_"+iface+" .speedtest-ping").empty().show();
    $("#sp_iface_"+iface+" .speedtest-download").empty().show();
    $("#sp_iface_"+iface+" .speedtest-upload").empty().show();
    $("#sp_iface_"+iface+" .speedtest-error").empty().hide();
}

function run_speedtest(iface, iface_source_ip) {
    $("#sp_iface_"+iface+' td.speedtest-reload a').addClass("fa-spin");
    $("#sp_iface_"+iface+' td.speedtest-reload a').off();
    $.ajax({
        type: 'POST',
        url: "/widgets/widgets/speedtest.widget.php",
        dataType: 'json',
        data: {
            ajax: "ajax",
            isp_id: '<?=$isp_selected->{'id'}?>',
            source_ip: iface_source_ip,
            iface: iface,
        },
        success: function(data) {
            update_st_row(iface, data);
        },
        error: function(e) {
            clear_st_row(iface);
        },
        complete: function() {
            $("#sp_iface_"+iface+' td.speedtest-reload a').removeClass("fa-spin");
            $("#sp_iface_"+iface+' td.speedtest-reload a').on();
        }
    });

}

events.push(function() {
    <?php
    foreach ($ifdescrs as $ifdescr => $ifname){
        if (in_array($ifdescr, $skipinterfaces)) {
            continue;
        }
        $ifinfo = get_interface_info($ifdescr);
        ?>
        add_st_row('<?=$ifdescr?>', '<?=$ifname?>', '<?=htmlspecialchars($ifinfo['ipaddr'])?>' <?=($results[$ifdescr])?', '.$results[$ifdescr]:''?>);
    <?php
    }
    ?>
});
</script>
<?php } ?>
