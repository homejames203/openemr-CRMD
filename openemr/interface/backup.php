<?php
require_once("globals.php");
use ESign\Api;
$s=sqlStatement("select pid from patient_data order by lname");
$ar=array();
while($row=sqlFetchArray($s))
{
 $ar[]=$row['pid'];
}
if($_POST)
exec('bash -c "exec nohup setsid php backend_backup.php '.$_SESSION['site_id'].' 1 '.$_REQUEST['pdf_type'].' '.($_REQUEST['pdf_type']=='batch'?$_REQUEST['starting_patient']:$_REQUEST['patient']).' '.($_REQUEST['pdf_type']=='batch'?$_REQUEST['ending_patient']:0).' '.$_REQUEST['email'].' > /dev/null 2>&1 &"');
//shell_exec("php backend_backup.php ".$_SESSION['site_id']." 1 ".$_REQUEST['pdf_type']." ".$_REQUEST['patient']." ".$_REQUEST['email']);
?>
 <!DOCTYPE html>
 <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDF Export</title>
    <link href="../library/css/bootstrap-3-2-0.min.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="../library/js/jquery-1.9.1.min.js"></script>
    <script>
     $(function(){
      <?php if($_REQUEST['pdf_type']=='single'){ ?>
       $('#patient>option[value=<?php echo $_REQUEST['patient']; ?>]').prop('selected',true);
      <?php }elseif($_REQUEST['pdf_type']=='batch'){ ?>
       $('#starting_patient>option[value=<?php echo $_REQUEST['starting_patient']; ?>]').prop('selected',true);
       $('#ending_patient>option[value=<?php echo $_REQUEST['ending_patient']; ?>]').prop('selected',true);
      <?php } ?>
      single_or_batch();
     });
     function single_or_batch()
     {
      if($("[name='pdf_type']:checked").val()=='single')
      {
       $('.single').show();
       $('.batch').hide();
      }
      else if($("[name='pdf_type']:checked").val()=='batch')
      {
       $('.batch').show();
       $('.single').hide();
      }
     }
    </script>
  </head>
  <body style='overflow-x:hidden'>
        <div id="page-wrapper">
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            PDF Export
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form role="form" method='POST'>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="pdf_type" value="single" onclick='single_or_batch()' <?php echo ($_REQUEST['pdf_type']!='batch'?'checked':''); ?>>Single
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="pdf_type" value="batch" onclick='single_or_batch()' <?php echo ($_REQUEST['pdf_type']=='batch'?'checked':''); ?>>Batch
                                            </label>
                                        </div>
					<?php
					 $s=sqlStatement("select lname,fname,pid from patient_data order by lname");
					 $s1='';
					 $i=0;
					 while($row=sqlFetchArray($s))
					 {
					  $s1.="<option value='$i' >".$row['lname'].",".$row['fname']."</option>";
					  $i++;
					 }
					?>
                                        <div class="form-group single">
                                          <label>Patient</label>
                                          <select class="form-control" id='patient' name='patient'>
					   <?php echo $s1; ?>
					  </select>
                                        </div>
                                        <div class="form-group batch">
                                          <label>Starting Patient</label>
                                          <select id='starting_patient' name='starting_patient' class="form-control">
					   <?php echo $s1; ?>
					  </select>
                                        </div>
                                        <div class="form-group batch">
                                          <label>Ending Patient</label>
                                          <select id='ending_patient' name='ending_patient' class="form-control">
					   <?php echo $s1; ?>
					  </select>
                                        </div>
                                        <div class="form-group">
                                          <label>Email</label>
                                          <input type='text' class="form-control" name='email' value="<?php echo (isset($_REQUEST['email'])?$_REQUEST['email']:''); ?>">
                                        </div>
					<input type='hidden' value='demographics' name='include_demographics'>
					<input type='hidden' value='history' name='include_history'>
					<input type='hidden' value='employer' name='include_employer'>
					<input type='hidden' value='allergies' name='include_allergies'>
					<input type='hidden' value='medications' name='include_medications'>
					<input type='hidden' value='medical_problems' name='include_medical_problems'>
					<input type='hidden' value='immunizations' name='include_immunizations'>
					<input type='hidden' value='notes' name='include_notes'>
					<input type='hidden' value='transactions' name='include_transactions'>
					<input type='hidden' value='batchcom' name='include_batchcom'>
					<input type='hidden' value='insurance' name='include_insurance'>
					<input type='hidden' value='billing' name='include_billing'>
					<input type='hidden' value=1 name='pdf'>
                                        <button type="submit" class="btn btn-default">Export</button>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
  </body>
 </html>
