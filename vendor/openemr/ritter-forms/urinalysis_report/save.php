<?php
/* this page is intended to be the 'action=' target of a form object.
 * it is called to save the contents of the form into the database
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for ??? */
require_once($GLOBALS['srcdir'].'/forms.inc');
/* for formDataCore() */
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_urinalysis_report';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Urinalysis Report';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'urinalysis_report';

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

/* an array of all of the fields' names and their types. */
$field_names = array('collection_date' => 'date','test_date' => 'date','physician' => 'provider','testers_initials' => 'textfield','exam_color' => 'checkbox_combo_list','exam_appearance' => 'checkbox_combo_list','chemical_exam_specific_gravity' => 'radio_list','chemical_exam_ph' => 'radio_list','chemical_exam_leukocytes' => 'radio_list','chemical_exam_nitrate' => 'radio_list','chemical_exam_protein' => 'radio_list','chemical_exam_glucose' => 'radio_list','chemical_exam_ketones' => 'radio_list','chemical_exam_urobilinogen' => 'radio_list','chemical_exam_bilirubin' => 'radio_list','chemical_exam_blood' => 'radio_list','chemical_exam_hemoglobin' => 'radio_list','comments' => 'textarea');
/* an array of the lists the fields may draw on. */
$lists = array('exam_color' => 'Urinalysis_Physical_Exam_Color', 'exam_appearance' => 'Urinalysis_Physical_Exam_Appear', 'chemical_exam_specific_gravity' => 'Urinalysis_Chem_Exam_Spec_Grav', 'chemical_exam_ph' => 'Urinalysis_Chem_Exam_Ph', 'chemical_exam_leukocytes' => 'Urinalysis_Chem_Exam_Leukocytes', 'chemical_exam_nitrate' => 'Urinalysis_Chem_Exam_Nitrate', 'chemical_exam_protein' => 'Urinalysis_Chem_Exam_Protein', 'chemical_exam_glucose' => 'Urinalysis_Chem_Exam_Glucose', 'chemical_exam_ketones' => 'Urinalysis_Chem_Exam_Ketones', 'chemical_exam_urobilinogen' => 'Urinalysis_Chem_Exam_Urobilinog', 'chemical_exam_bilirubin' => 'Urinalysis_Chem_Exam_Bilirubin', 'chemical_exam_blood' => 'Urinalysis_Chem_Exam_Blood', 'chemical_exam_hemoglobin' => 'Urinalysis_Chem_Exam_Hemoglobin');

/* get each field from $_POST[], storing them into $field_names associated with their names. */
foreach($field_names as $key=>$val)
{
    $pos = '';
    $neg = '';
    if ($val == 'textbox' || $val == 'textarea' || $val == 'provider' || $val == 'textfield')
    {
            $field_names[$key]=$_POST['form_'.$key];
    }
    if ($val == 'date')
    {
        $field_names[$key]=$_POST[$key];
    }
	if (($val == 'radio_list' ))
    {
        $field_names[$key]='';
        if (isset($_POST['form_'.$key]) && $_POST['form_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
        	$field_names[$key] = $_POST['form_'.$key];
        }
    }
    if (($val == 'checkbox_list' ))
    {
        $field_names[$key]='';
        if (isset($_POST['form_'.$key]) && $_POST['form_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
            $lres=sqlStatement("select * from list_options where list_id = '".$lists[$key]."' ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres))
            {
                if (is_array($_POST['form_'.$key]))
                    {
                        if ($_POST['form_'.$key][$lrow[option_id]])
                        {
                            if ($field_names[$key] != '')
                              $field_names[$key]=$field_names[$key].'|';
	                    $field_names[$key] = $field_names[$key].$lrow[option_id];
                        }
                    }
            }
        }
    }
    if (($val == 'checkbox_combo_list'))
    {
        $field_names[$key]='';
        if (isset($_POST['check_'.$key]) && $_POST['check_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
            $lres=sqlStatement("select * from list_options where list_id = '".$lists[$key]."' ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres))
            {
                if (is_array($_POST['check_'.$key]))
                {
                    if ($_POST['check_'.$key][$lrow[option_id]])
                    {
                        if ($field_names[$key] != '')
                          $field_names[$key]=$field_names[$key].'|';
                        $field_names[$key] = $field_names[$key].$lrow[option_id].":xx".$_POST['form_'.$key][$lrow[option_id]];
                    }
                }
            }
        }
    }
    if (($val == 'dropdown_list'))
    {
        $field_names[$key]='';
        if (isset($_POST['form_'.$key]) && $_POST['form_'.$key] != 'none' ) /* if the form submitted some entries selected in that field */
        {
            $lres=sqlStatement("select * from list_options where list_id = '".$lists[$key]."' ORDER BY seq, title");
            while ($lrow = sqlFetchArray($lres))
            {
                if ($_POST['form_'.$key] == $lrow[option_id])
                {
                    $field_names[$key]=$lrow[option_id];
                    break;
                }
            }
        }
    }
}

/* at this point, field_names[] contains an array of name->value pairs of the fields we expected from the form. */

/* escape form data for entry to the database. */
foreach ($field_names as $k => $var) {
  $field_names[$k] = formDataCore($var);
}

if ($encounter == '') $encounter = date('Ymd');

if ($_GET['mode'] == 'new') {
    /* NOTE - for customization you can replace $_POST with your own array
     * of key=>value pairs where 'key' is the table field name and
     * 'value' is whatever it should be set to
     * ex)   $newrecord['parent_sig'] = $_POST['sig'];
     *       $newid = formSubmit($table_name, $newrecord, $_GET['id'], $userauthorized);
     */

    /* make sure we're at the beginning of the array */
    reset($field_names);

    /* save the data into the form's encounter-based table */
    $newid = formSubmit($table_name, $field_names, $_GET['id'], $userauthorized);
    /* link this form into the encounter. */
    addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
}

elseif ($_GET['mode'] == 'update') {
    /* make sure we're at the beginning of the array */
    reset($field_names);

    /* update the data in the form's table */
    $success = formUpdate($table_name, $field_names, $_GET['id'], $userauthorized);
    /* sqlInsert('update '.$table_name." set pid = {".$_SESSION['pid']."},groupname='".$_SESSION['authProvider']."',user='".$_SESSION['authUser']."',authorized=$userauthorized,activity=1,date = NOW(), where id=$id"); */
}


$_SESSION['encounter'] = $encounter;

formHeader('Redirecting....');
/* defaults to the encounters page. */
formJump();

formFooter();
?>

