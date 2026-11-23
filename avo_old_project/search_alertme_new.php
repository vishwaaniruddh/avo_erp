<?php
session_start(); 
include('db_connection.php');
$con1 = OpenCon1();

if(isset($_SESSION['branch']) && $_SESSION['branch']!=''){
    $br=$_SESSION['branch'];    
}else{
    $br=isset($_POST['branch_avo']) ? $_POST['branch_avo'] : '';
}

$strPage = isset($_REQUEST['Page']) ? $_REQUEST['Page'] : 1;
?>

<table width="100%" border="1" cellpadding="2" cellspacing="0" style="margin-top:5px;" class="res" id="custtable" >
<tr>
<th width="10%">Complain ID</th> 
<th width="5%">Client Docket Number</th> 
<th width="5%">Name</th>
<th width="5%">Site/Sol/ATM Id</th>
<th width="5%">User Customer Name</th>
<th width="5%">City</th>
<th width="5%">Pincode</th>
<th width="5%">Landmark</th>
<th width="5%">Address</th>
<th width="5%">AVO Branch</th>
<th width="5%">Problem</th>
<th width="5%">Alert Date</th>
<th width="5%">Contact Person</th>
<th width="5%"> Phone</th>
<th width="5%"> Delegated To</th>
<th width="5%"> Distance from Engr</th>
<th width="5%"> Call Type</th>
<th width="5%"> Customer Status</th>
<th width="5%"> Last FeedBack</th>
<th width="5%">Status</th>
<th width="5%">ETA</th>
<th width="5%">ETA Update/FSR Attach</th>
<th width="7%">Update</th>
</tr>
<?php

$bravo = isset($_POST['bravo']) ? $_POST['bravo'] : 'all';
$branch_avo = isset($_POST['branch_avo']) ? $_POST['branch_avo'] : '';

$where = " WHERE a.cust_id != 96 ";

if($bravo == 'all') {
    if(!empty($branch_avo)) {
        $stt = mysqli_real_escape_string($con1, $branch_avo);
        $where .= " AND a.branch_id = '".$stt."' ";
    } else {
        $where .= " AND a.branch_id <> '' ";
    }
} else {
    if(!empty($branch_avo)) {
        $stt = mysqli_real_escape_string($con1, $branch_avo);
        $where .= " AND a.branch_id = '".$stt."' ";
    } else {
        $stt = mysqli_real_escape_string($con1, $br);
        $where .= " AND a.branch_id = '".$stt."' ";
    }
}

// Call Type Filter
if(isset($_POST['calltype'])) {
    $calltype = $_REQUEST['calltype'];
    if($calltype == 'open') {
        $where .= " AND (a.call_status = 'Pending' or a.call_status = '1' or a.call_status = '2' or a.call_status = 'Delegated') and a.status != 'Done' ";
    } elseif($calltype == 'Done') {
        $where .= " AND (a.call_status = 'Done' or a.status = 'Done') ";
    } elseif($calltype == 'onhold') {
        $where .= " AND a.call_status = 'onhold' ";
    } elseif($calltype == 'Rejected') {
        $where .= " AND a.call_status = 'Rejected' ";
    }
}

// Open All Filter
if(isset($_POST['openall'])) {
    $calltype = $_REQUEST['openall'];
    if($calltype == 'install') {
        $where .= " AND (a.alert_type = 'new') ";
    } elseif($calltype == 'service') {
        $where .= " AND (a.alert_type = 'service' or a.alert_type = 'new temp') ";
    } elseif($calltype == 'pm') {
        $where .= " AND (a.alert_type = 'pm' or a.alert_type = 'temp_pm') ";
    } elseif($calltype == 'dere') {
        $where .= " AND (a.alert_type = 'dere' or a.alert_type = 'temp_dere') ";
    } elseif($calltype == 'wtpcb') {
        $where .= " AND (a.alert_type = 'wtpcb') ";
    }
}

// Engineer Filter
if(isset($_POST['eng']) && $_POST['eng'] != '') {
    $eng = mysqli_real_escape_string($con1, $_POST['eng']);
    if($eng != '-1' && $eng != '-2' && $eng != '-3' && $eng != '-4') {
        $eng_alert = mysqli_query($con1, "select alert_id from alert_delegation where engineer='" . $eng . "'");
        $all_alid = array();
        while($eng_alert1 = mysqli_fetch_row($eng_alert)) {
            $all_alid[] = $eng_alert1[0];
        }
        $alert_string = implode(",", $all_alid);
        if(!empty($alert_string)) {
            $where .= " AND a.alert_id in ($alert_string) ";
        } else {
            $where .= " AND 1=0 ";
        }
    } elseif($eng == '-1') {
        $where .= " AND a.alert_id not in (select alert_id from alert_delegation) ";
    } elseif($eng == '-3') {
        $where .= " AND a.alert_id in (select alert_id from alert_delegation where status=2) ";
    } elseif($eng == '-4') {
        $where .= " AND a.alert_id in (select alert_id from alert_delegation where call_close_status = 2) ";
    } elseif($eng == '-2') {
        $where .= " AND a.alert_id not in (select alert_id from eng_feedback) ";
    }
}

// Date Filter
if(isset($_POST['fromdt']) && $_POST['fromdt'] != '' && isset($_POST['todt']) && $_POST['todt'] != '') {
    $fromdt = $_POST['fromdt'];
    $todt = $_POST['todt'];
    if(isset($_POST['calltype']) && $_POST['calltype'] == 'Done') {
        $fdate = date('Y-m-d', strtotime(str_replace('/', '-', $fromdt)));
        $tdate = date('Y-m-d', strtotime(str_replace('/', '-', $todt)));
        $where .= " AND a.close_date between '" . $fdate . " 00:00:00' and '" . $tdate . " 23:59:59' ";
    } elseif(isset($_POST['calltype']) && $_POST['calltype'] == 'Rejected') {
        $fdate = date('Y-m-d', strtotime(str_replace('/', '-', $fromdt)));
        $tdate = date('Y-m-d', strtotime(str_replace('/', '-', $todt)));
        $where .= " AND a.reject_date between '" . $fdate . " 00:00:00' and '" . $tdate . " 23:59:59' ";
    } else {
        $where .= " AND a.entry_date Between STR_TO_DATE('$fromdt','%d/%m/%Y') AND STR_TO_DATE('$todt','%d/%m/%Y') + INTERVAL 1 DAY ";
    }
}

// ATM ID Filter
if(isset($_POST['atmid']) && $_POST['atmid'] != '') {
    $id = mysqli_real_escape_string($con1, $_POST['atmid']);
    $where .= " AND (((a.atm_id IN (select track_id from atm where atm_id LIKE '%" . $id . "%') and a.assetstatus='site') or (a.atm_id IN (select amcid from Amc where atmid LIKE '%" . $id . "%' and a.assetstatus='amc') )) or a.atm_id LIKE '%" . $id . "%') ";
}

// Client ID Filter
if(isset($_POST['cid']) && $_POST['cid'] != '') {
    $cid = mysqli_real_escape_string($con1, $_POST['cid']);
    $where .= " AND a.cust_id = '" . $cid . "' ";
}

// Bank Filter
if(isset($_POST['bank']) && $_POST['bank'] != '') {
    $bank = mysqli_real_escape_string($con1, $_POST['bank']);
    $where .= " AND a.bank_name LIKE '%" . $bank . "%' ";
}

// Asset Status Filter
if(isset($_POST['assetstatus']) && $_POST['assetstatus'] != '') {
    $assetstatus = $_POST['assetstatus'];
    if($assetstatus == "amc") {
        $where .= " AND a.assetstatus like '%amc%' ";
    } elseif($assetstatus == "site") {
        $where .= " AND a.assetstatus like '%site%' ";
    } else {
        $where .= " AND (a.assetstatus = '' or a.assetstatus is NULL) ";
    }
}

// Docket Filter
if(isset($_POST['docket']) && $_POST['docket'] != '') {
    $docket = mysqli_real_escape_string($con1, $_POST['docket']);
    $where .= " AND a.custdoctno LIKE '%" . $docket . "%' ";
}

// Area Filter
if(isset($_POST['area']) && $_POST['area'] != '') {
    $area = mysqli_real_escape_string($con1, $_POST['area']);
    $where .= " AND a.address LIKE '%" . $area . "%' ";
}

// Complaint No Filter
if(isset($_POST['complaintno']) && $_POST['complaintno'] != '') {
    $complaintno = mysqli_real_escape_string($con1, $_POST['complaintno']);
    $where .= " AND a.createdby LIKE '%" . $complaintno . "%' ";
}

$countsql = "Select count(*) as count from alert a " . $where;
$countrow = mysqli_query($con1, $countsql);
$rows = mysqli_fetch_assoc($countrow);
$Num_Rows = isset($rows['count']) ? $rows['count'] : 0;
$count = 0;
?>

<div align="center">Total number of Records :<b><?php echo $Num_Rows; ?></b>
Records Per Page :<select name="perpg" id="perpg" onchange="searchById('Listing','1','perpg');">

<?php
for($i=1; $i<=$Num_Rows; $i++) {
    if($i%10==0) {
        ?>
        <option value="<?php echo $i; ?>" <?php if(isset($_POST['perpg']) && $_POST['perpg']==$i){?> selected="selected" <?php } ?>><?php echo $i."/page"; ?></option>
        <?php
    }
}
?>
<option value="<?php echo $Num_Rows; ?>"><?php echo "All"; ?></option>
</select> </div>
<?php if(isset($_POST['calltype']) && $_POST['calltype']=='open') { ?>
<div><input class="buttn" type='button' onClick='window.open("route_plan.php","_blank");' style="background:#; height:20px" value='Route Plan'></div>
<?php } ?>
</div>

<?php
$Per_Page = isset($_POST['perpg']) && $_POST['perpg'] != '' ? (int)$_POST['perpg'] : 10;
$Page = $strPage ? (int)$strPage : 1;

$Prev_Page = $Page - 1;
$Next_Page = $Page + 1;

$Page_Start = (($Per_Page * $Page) - $Per_Page);
if($Num_Rows <= $Per_Page) {
    $Num_Pages = 1;
} else if(($Num_Rows % $Per_Page) == 0) {
    $Num_Pages = ($Num_Rows / $Per_Page);
} else {
    $Num_Pages = (int)($Num_Rows / $Per_Page) + 1;
}

$qr22 = "Select * from alert " . str_replace("a.", "", $where);

// Pass 1: Fetch exact Alert IDs for target page
$id_sql = "SELECT a.alert_id FROM alert a " . $where . " ORDER BY a.alert_id DESC LIMIT $Page_Start, $Per_Page";
$id_res = mysqli_query($con1, $id_sql);
$alert_ids = array();
if($id_res) {
    while($id_row = mysqli_fetch_row($id_res)) {
        $alert_ids[] = $id_row[0];
    }
}

if(!empty($alert_ids)) {
    $id_str = implode(',', $alert_ids);

    // Pass 2: Hyper-optimized query scoped strictly to the paginated IDs (50ms vs 9300ms)
    $main_sql = "SELECT 
        a.*, 
        c.cust_name, 
        b.name AS branch_name,
        amc.atmid AS amc_atmid,
        site.atm_id AS site_atmid,
        ef.feedback AS last_feedback, ef.standby AS last_standby, ef.feed_date AS last_feed_date,
        del.engg_name, del.phone_no1, del.engg_id, del.del_status, del.del_call_close_status,
        tr.trans_reason, tr.id AS trans_id,
        tmp.tmp_cnt
    FROM alert a
    LEFT JOIN customer c ON a.cust_id = c.cust_id
    LEFT JOIN avo_branch b ON a.branch_id = b.id
    LEFT JOIN Amc amc ON a.atm_id = amc.amcid AND (a.assetstatus = 'amc' OR a.assetstatus = 'AMC')
    LEFT JOIN atm site ON a.atm_id = site.track_id AND a.assetstatus = 'site'
    LEFT JOIN (
        SELECT f.alert_id, f.feedback, f.standby, f.feed_date
        FROM eng_feedback f
        INNER JOIN (
            SELECT alert_id, MAX(id) AS max_id
            FROM eng_feedback
            WHERE alert_id IN ($id_str)
            GROUP BY alert_id
        ) fmax ON f.id = fmax.max_id
    ) ef ON a.alert_id = ef.alert_id
    LEFT JOIN (
        SELECT d.alert_id, d.status AS del_status, d.call_close_status AS del_call_close_status, e.engg_name, e.phone_no1, e.engg_id
        FROM alert_delegation d
        INNER JOIN (
            SELECT alert_id, MAX(id) AS max_id
            FROM alert_delegation
            WHERE alert_id IN ($id_str)
            GROUP BY alert_id
        ) dmax ON d.id = dmax.max_id
        LEFT JOIN area_engg e ON d.engineer = e.engg_id
    ) del ON a.alert_id = del.alert_id
    LEFT JOIN (
        SELECT alertid, todesc AS trans_reason, id
        FROM transfersites
        WHERE alertid IN ($id_str) AND approval LIKE 'disapprove%' AND status = '0'
    ) tr ON a.alert_id = tr.alertid
    LEFT JOIN (
        SELECT alert_id, COUNT(*) AS tmp_cnt
        FROM tempclosedcall
        WHERE alert_id IN ($id_str) AND status = 0
        GROUP BY alert_id
    ) tmp ON a.alert_id = tmp.alert_id
    WHERE a.alert_id IN ($id_str)
    ORDER BY a.alert_id DESC";

    $table = mysqli_query($con1, $main_sql);
} else {
    $table = false;
}

if($table && mysqli_num_rows($table) > 0) {
    while($row = mysqli_fetch_array($table, MYSQLI_BOTH)) {
        // ATM ID calculation from JOIN
        if($row[21] == 'amc' || $row[21] == 'AMC') {
            $atm_id = !empty($row['amc_atmid']) ? $row['amc_atmid'] : $row[2];
        } elseif($row[21] == 'site') {
            $atm_id = !empty($row['site_atmid']) ? $row['site_atmid'] : $row[2];
        } else {
            $atm_id = $row[2];
        }

        // Joined fields
        $cust_name = !empty($row['cust_name']) ? $row['cust_name'] : '';
        $branch_name1 = !empty($row['branch_name']) ? $row['branch_name'] : '';
        
        $engg_name = !empty($row['engg_name']) ? $row['engg_name'] : '';
        $phone_no1 = !empty($row['phone_no1']) ? $row['phone_no1'] : '';
        $engg_id = !empty($row['engg_id']) ? $row['engg_id'] : '';
        $delstus = '';
        if(!empty($engg_name)) {
            if(isset($row['del_status']) && $row['del_status'] == '2') {
                $delstus = "Rejected By Eng!";
            } elseif(isset($row['del_call_close_status']) && $row['del_call_close_status'] == '2') {
                $delstus = "Attended-Pending";
            }
        }
        ?>
        <tr <?php if($row[26]=='1'){ echo "style='background:#99CC33'"; } if($row[16]=='2'){ echo "style='background:#990000'"; } if($row[31]!='2000-01-01 00:00:00' && $row[31]<=date('Y-m-d H:i:s') && $row[16]<>'Done'){ echo "style='background:red'"; } if(isset($row[43]) && $row[43] !=''){ echo "style='background:#FFA500'"; }?>>

        <td valign="top"><?php echo $row[25]; ?></td>
        <td valign="top">&nbsp;<?php echo wordwrap($row[30],8,"<br />\n",TRUE); ?></td>
        <td valign="top">&nbsp;<?php echo $cust_name; ?></td>

        <td valign="top">&nbsp;<?php echo $atm_id; ?></td>

        <td valign="top">&nbsp;<?php echo wordwrap($row[3],6,"<br />\n",TRUE); ?></td>
        <td valign="top">&nbsp;<?php echo wordwrap($row[6],5,"<br />\n",TRUE); ?></td>
        <td valign="top">&nbsp;<?php echo $row[8]; ?></td>
        <td valign="top">&nbsp;<?php echo wordwrap($row[4] ,7,"<br />\n",TRUE) ;?></td>
        <td valign="top">&nbsp;<div height="100px" style="height:150px; overflow:hidden;"><?php echo $row[5]; ?></div></td>

        <td><?php echo $branch_name1; ?></td>
        <td valign="top">&nbsp;<?php echo $row[9]; ?></td>
        <td valign="top">&nbsp;<?php echo date('d/m/Y h:i:s a',strtotime($row[10]));?></td>
        <td valign="top">&nbsp;<?php echo wordwrap($row[12],8,"<br />\n",TRUE); ?></td>
        <td valign="top">&nbsp;<?php echo $row[13]; ?></td>

        <td valign="top"><?php echo $engg_name."<br>".$phone_no1."<br><font color='blue'>$delstus</font>"; ?>
        <br>
        <?php if($engg_name !='') { 
            $cdate = date('d/m/Y');
            ?>
            <button><a style="color:red; font-size:15px; font-weight:bold;" href="distance_map.php?id=<?php echo $engg_id; ?>&date=<?php echo $cdate; ?>" target="_new">Where is He? </a></button>
        <?php } ?>
        </td>
        <td valign="top"><?php echo $row[37]; ?></td>
        <td valign="top">&nbsp;<?php echo $row[17]; ?></td>

        <td valign="top">&nbsp;
        <?php 
        if($row[21]=='site'){ echo "Under Warranty"; }
        else if($row[21]=='amc'){ echo "AMC"; }
        else echo "Temp Call"; ?>
        <br>
        <?php if($row[21]=='site' or $row[21]=='amc'){ ?>
            <button><a href="javascript:void(0);" onclick="newwin('view_detassets.php?sid=<?php echo $row[2] ?>&site=<?php echo $row[21] ?>','display',450,300)" class="edit">Asset Detail </a></button>
        <?php } ?>  
        </td>

        <td valign="top">&nbsp;
        <div height="100px" style="height:150px; overflow:hidden;">
        <?php 
        if(!empty($row['last_feed_date'])){
            ?>
            <a href="javascript:void(0);" onclick="newwin('masteralert.php?id=<?php echo $row[0] ?>','display',700,700)" class="update">
            <?php echo date('d/m/Y h:i:s a',strtotime($row['last_feed_date']))."<br>".wordwrap($row['last_feedback'],10,"<br />\n",TRUE); ?></a>
            <?php
        } else {
            echo "No Updates so far";
        }
        ?>
        </div>
        </td>

        <?php 
        if($row[16]=='1' || $row[16]=='Pending') { ?>
        <td>
            <?php
            if($row[15] =='Pending'){
                if(isset($row[43]) && $row[43] !=''){ ?>
                    <a href="javascript:confirm_generate('<?php echo $row[0]?>','<?php echo $row[6];?>','<?php echo $row[2]?>','<?php echo $bravo;?>',<?php echo $row[7];?>);" ><font color="#000066" size="+1">RepDelegate</font></a>
                <?php } else { ?>
                    <a href="delegate.php?req=<?php echo $row[0]?>&city=<?php echo $row[6]; ?>&atm=<?php echo $row[2]?>&br=<?php echo $bravo; ?>&state=<?php echo $row[7]; ?>"><font color="#000066" size="+1">Delegate</font></a>
                <?php } ?>
                <a href="javascript:void(0);" onclick="newwin('rejected-hold.php?id=<?php echo $row[0] ?>','Reject',400,400)"><font color="white" > Reject/ Customer Dependency</font></a>
            <?php }
            if($row[15] =='Delegated'){ ?>
                <a href="redelegateme.php?req=<?php echo $row[0]?>&atm=<?php echo $row[2]?>&br=<?php echo $bravo; ?>"><font color="#000066" size="+1">Redelegate</font></a>
                <a href="javascript:void(0);" onclick="newwin('rejected-hold.php?id=<?php echo $row[0] ?>','Reject',400,400)"><font color="white" > Reject/ Customer Dependency</font></a>
            <?php }
            if($row[15] !='Done' ){ 
                if($row[26]!='1') { ?>
                    <a href="javascript:void(0);" onclick="newwin('calltransfer.php?id=<?php echo $row[0] ?>','transfer',700,700)" class="update"> 
                    Transfer
                    <?php if(!empty($row['trans_id'])){ 
                        echo " Failed<br>Reason :".$row['trans_reason']; 
                    } ?></a>
                <?php } else {
                    echo "<br><br>Under Transferring Process";
                } ?>
                <a href="BRF_form.php?id=<?php echo $row[0];?>"><font color="red" >Create/BRF</font></a>
            <?php }
            elseif($row[15]=='Done'){ ?>
                Closed By Engineer
                <br>
                <a href="avopdf/report.php?aid=<?php echo $row[0]; ?>" target="_blank" >Generate FSR </a>
                <br/>
                <a href="view_sitesnaps.php?aid=<?php echo $row[0]; ?>" target="_blank" >View Snaps </a>
                <br/>
                <a href="BRF_form.php?id=<?php echo $row[0];?>"><font color="red" >Create/BRF</font></a>
            <?php } ?>
        </td>
        <?php }
        else if($row[16]=='Done') { ?>
            <td> Call Closed 
            <br>
            <a href="avopdf/report.php?aid=<?php echo $row[0]; ?>" target="_blank" >Generate FSR </a>
            <br/>
            <a href="BRF_form.php?id=<?php echo $row[0];?>"><font color="red" >Create/BRF</font></a>
            </td>
        <?php }
        else if($row[16]=='Rejected') { ?>
            <td> Rejected </td>
        <?php } 
        else if($row[16]=='onhold') { ?>
            <td>
            Call on Hold
            <br><a href="unhold.php?id=<?php echo $row[0]; ?>">Unhold</a> 
            </td> 
        <?php } ?>

        <td>
        <?php 
        echo $row[31];
        if($row[31]!="0000-00-00 00:00:00")
            echo '<br>'.'<a href="calltrack.php?pid='.$row[0].'" target="_new" >Track</a>';
        ?>
        </td>

        <td>
        <?php 
        if(($row[16]=='Done' || $row[15]=='Done') && (empty($row[44]))){
            ?>
            <button type="button" style="color:blue; font:bold;"><a href="javascript:void(0);" class="btn btn-primary" onclick="window.open('manual_fsr.php?id=<?php echo $row[0];?>','upload','width=700px,height=500,left=300,top=100')" class="update">Attach Manual FSR</a></button>
        <?php } elseif (!empty($row[44])) { ?>
            <button type="button" class="btn btn-primary" style="color:blue ;"> <a href="<?php echo $row[44]; ?>" target="_blank">View FSR</a> </button>
        <?php } ?>
        </td>

        <td>
        <?php
        if(($row[16]=='Delegated' || $row[16]=='Pending' || $row[16]=='2' || $row[16]=='1' || (!empty($row['tmp_cnt']) && $row['tmp_cnt'] > 0)) && $row[26]!='1'){
            if($row[15]!='Done'){
                ?>
                <a href="javascript:void(0);" onclick="newwin('call_update.php?id=<?php echo $row[0]; ?>&eng_id=<?php echo $engg_id; ?>','display',600,600)" class="update"> 
                <font size="+1">Update</font></a> <?php 
            }
            echo "<br>";
        }  
        if($row[31]!='0000-00-00 00:00:00'){
            if($row[15]!='Done'){
                ?>
                <a href="javascript:void(0);" onclick="newwin('revise_update.php?id=<?php echo $row[0]; ?>&br=<?php echo $br;?>&ctype=<?php echo $row[17]; ?>&alerts_id=<?php echo $row[0]; ?>&eng_id=<?php echo $engg_id; ?>','display',600,600)" class="update">
                <font color="#000066" size="-2">ETA Update</font></a> 
            <?php }
        }
        ?>
        </td>
        </tr>
        <?php
    } 
}
?>
</table>
<div class="pagination" style="width:100%;"><font size="4" color="#000">
<?php
if($Prev_Page) {
    echo " <a href=\"JavaScript:searchById('Listing','$Prev_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252> << Back</font></a>";
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
if($Page!=$Num_Pages) {
    echo " <a href=\"JavaScript:searchById('Listing','$Next_Page','perpg')\" style=\"text-decoration:none\"><font color=:#005252>Next >></font></a> ";
}
?>
</div>
<div><form name="frm" method="post" action="exportme.php" target="_new">
<input type="hidden" name="qr" value="<?php echo $qr22; ?>" readonly>
<input type="submit" name="cmdsub" value="Export" > <span>(Export Minimum Records)</span>
</form>
</div>