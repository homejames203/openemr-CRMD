<?php
/*
 * The page shown when the user requests to see this form. Allows the user to edit form contents, and save. has a button for printing the saved form contents.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');
/* note that we cannot include options_listadd.inc here, as it generates code before the <html> tag */

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_vaginitis_soap';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Vaginitis Complaints';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'vaginitis_soap';

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
 die($form_name.': Access Denied.');
}
/* perform a squad check for pages touching patients, if we're in 'athletic team' mode */
if ($GLOBALS['athletic_team']!='false') {
  $tmp = getPatientData($pid, 'squad');
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}

if ($thisauth != 'write' && $thisauth != 'addonly')
  die($form_name.': Adding is not authorized.');
/* Use the formFetch function from api.inc to load the saved record */
$xyzzy = formFetch($table_name, $_GET['id']);

/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'vaginitis_complaints' => 
   array( 'field_id' => 'vaginitis_complaints',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Vaginitis_Complaints' ),
 'other' => 
   array( 'field_id' => 'other',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' ),
 'duration' => 
   array( 'field_id' => 'duration',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'objective_exam' => 
   array( 'field_id' => 'objective_exam',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Vaginitis_Exam' ),
 'vaginitis' => 
   array( 'field_id' => 'vaginitis',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Vaginitis_Diagnosis' ),
 'plan_medications' => 
   array( 'field_id' => 'plan_medications',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Vaginitis_Medications' ),
 'plan_behavior_modification' => 
   array( 'field_id' => 'plan_behavior_modification',
          'data_type' => '3',
          'fld_length' => '40',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' ),
 'plan_cultures' => 
   array( 'field_id' => 'plan_cultures',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Vaginitis_Cultures' ),
 'plan_other' => 
   array( 'field_id' => 'plan_other',
          'data_type' => '3',
          'fld_length' => '40',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' )
 );
$submiturl = $GLOBALS['rootdir'].'/forms/'.$form_folder.'/save.php?mode=update&amp;return=encounter&amp;id='.$_GET['id'];
if ($_GET['mode']) {
 if ($_GET['mode']=='noencounter') {
 $submiturl = $GLOBALS['rootdir'].'/forms/'.$form_folder.'/save.php?mode=new&amp;return=show&amp;id='.$_GET['id'];
 $returnurl = 'show.php';
 }
}
else
{
 $returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
}


/* define check field functions. used for translating from fields to html viewable strings */

function chkdata_Txt(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<!-- declare this document as being encoded in UTF-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" ></meta>

<!-- supporting javascript code -->
<!-- for dialog -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<!-- For jquery, required by the save, discard, and print buttons. -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>



<script type="text/javascript">
// this line is to assist the calendar text boxes
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

<!-- FIXME: this needs to detect access method, and construct a URL appropriately! -->
function PrintForm() {
    newwin = window.open("<?php echo $rootdir.'/forms/'.$form_folder.'/print.php?id='.$_GET['id']; ?>","print_<?php echo $form_name; ?>");
}

</script>
<title><?php echo htmlspecialchars('View '.$form_name); ?></title>

</head>
<body class="body_top">

<div id="title">
<a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
<span class="title"><?php htmlspecialchars(xl($form_name,'e')); ?></span>
<span class="back">(<?php xl('Back','e'); ?>)</span>
</a>
</div>

<form method="post" action="<?php echo $submiturl; ?>" id="<?php echo $form_folder; ?>"> 

<!-- Save/Cancel buttons -->
<div id="top_buttons" class="top_buttons">
<fieldset class="top_buttons">
<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>" />
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>" />
<input type="button" class="print" value="<?php xl('Print','e'); ?>" />
</fieldset>
</div><!-- end top_buttons -->

<!-- container for the main body of the form -->
<div id="form_container">
<fieldset>

<!-- display the form's manual based fields -->
<table border='0' cellpadding='0' width='100%'>
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_1' value='1' data-section="subjective" checked="checked" />Subjective</td></tr><tr><td><div id="subjective" class='section'><table>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Vaginitis Complaints','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vaginitis_complaints'], $xyzzy['vaginitis_complaints']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Other','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other'], $xyzzy['other']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Duration','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['duration'], $xyzzy['duration']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section subjective -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_2' value='1' data-section="objective" checked="checked" />Objective</td></tr><tr><td><div id="objective" class='section'><table>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Exam','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['objective_exam'], $xyzzy['objective_exam']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section objective -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_3' value='1' data-section="assessment" checked="checked" />Assessment</td></tr><tr><td><div id="assessment" class='section'><table>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Vginitis','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vaginitis'], $xyzzy['vaginitis']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section assessment -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_4' value='1' data-section="plan" checked="checked" />Plan</td></tr><tr><td><div id="plan" class='section'><table>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Medications','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['plan_medications'], $xyzzy['plan_medications']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Behavior Modification','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['plan_behavior_modification'], $xyzzy['plan_behavior_modification']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Cultures','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['plan_cultures'], $xyzzy['plan_cultures']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Other','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['plan_other'], $xyzzy['plan_other']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section plan -->
</table>

</fieldset>
</div> <!-- end form_container -->

<!-- Save/Cancel buttons -->
<div id="bottom_buttons" class="button_bar">
<fieldset>
<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>" />
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>" />
<input type="button" class="print" value="<?php xl('Print','e'); ?>" />
</fieldset>
</div><!-- end bottom_buttons -->
</form>
<script type="text/javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.forms["<?php echo $form_folder; ?>"].submit(); });
    $(".dontsave").click(function() { location.href='<?php echo $returnurl; ?>'; });
    $(".print").click(function() { PrintForm(); });
    
    $(".sectionlabel input").click( function() {
    	var section = $(this).attr("data-section");
		if ( $(this).attr('checked' ) ) {
			$("#"+section).show();
		} else {
			$("#"+section).hide();
		}
    });

    $(".sectionlabel input").attr( 'checked', 'checked' );
    $(".section").show();
});
</script>
</body>
</html>

