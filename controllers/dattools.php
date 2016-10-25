<?php
namespace Application\Controller;

use \Concrete\Core\Controller\Controller;
use \Concrete\Core\Http\Request;
use Concrete\Core\User\User;
use Core;

class VersionObj {
    public $major = 0;
    public $minor = 0;
}

class Version {
    public function getVersion() {
        $v = new VersionObj();
        //TODO: Version compare
    }
}

class Dattools extends Controller {
    public function submitFormAjax() {
        //$bID = Request::getInstance()->get('bID');
        $bID = $_POST['bID'];
        $param = 'dattools_form_' . $bID . '_busy';

        $_SESSION[$param] = true;

        $success = false;
        $message = '';
        $errors = array();
        $redirectURL = '';
        $postData = '';

        foreach($_POST as $PITEM) {
            $postData .= $PITEM . '; ';
        }

        if ((empty($bID) || (intval($bID) != $bID))) {
            $message .= 'Form index error.';
        } else {
            $b = \Concrete\Core\Block\Block::getByID($bID);
            //$bc = new \Concrete\Block\Form\Controller($b);
            $bc = new AjaxFormController($b);
            //success redirect handle missed

            //version_compare
            if (str_contains(APP_VERSION, '5.7.5', true)) {
                try {
                    $bc->action_submit_form($bID);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            } else {
                try {
                    $bc->action_submit_form_legacy();
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }



            $invalidIP = $bc->get('invalidIP');
            if (!empty($invalidIP)) {
                $errors[] = $invalidIP;
            }

            $fieldErrors = $bc->get('errors');
            if (is_array($fieldErrors)) {
                $errors = array_merge($errors, $fieldErrors);
            }

            $success = empty($errors);
            $message = $success ? $bc->thankyouMsg : $bc->get('formResponse');
            $redirectURL = $success ? $redirectURL : '';
        }

        $json = \Loader::helper('json');
        $jsonData = array(
            'success' => $success,
            'msg' => addslashes($message),
            'errors' => array_values($errors),
            'redirect' => $redirectURL,
            'postData' => $postData
        );

        echo $json->encode($jsonData);
        return;
    }

    public function getScript() {
        ?>
        <script>
            (function() {
                $(document).bind(
                    'ready',
                    function() {
                        var _p = false;

                        $('*[id^=formblock]').each(function() {
                            var _f = $(this).find('form');
                            var _bID = $(this).attr('id').replace('formblock','');

                            _f.submit(function(e) {
                                e.preventDefault();

                                if ($(this).hasClass('busy')) return false;
                                $(this).addClass('busy');

                                var _params = 'bID=' + _bID;
                                _f.find('input, textarea, select').each(function() {
                                    _params += '&' + $(this).attr('name') + '=' + $(this).val();
                                });

                                var _fData = new FormData(jQuery(_f)[0]);
                                _fData.append("bID", _bID);

                                jDebug.log('params: ' + _params);

                                $.ajax({
                                    type: "POST",
                                    url: "<?=\URL::to('/controller/dattools/submit-form')?>",
                                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                                    data: _params,
                                    cache: false,
                                    processData: false,
                                    success: function(data) {
                                        showMsg(_f, data);
                                    },
                                    error: function(data) {
                                        showMsg(_f, data);
                                    }
                                });
                            });
                        });
                    }
                );

                function showMsg(_f, _msg) {
                    //update captcha (by simulating click action, cuz why not, it's the easiest way
                    _f.find('.ccm-captcha-image').click();
                    _f.find('.ccm-input-captcha').val('');
                    //remove busy tag
                    _f.removeClass('busy');

                    //remove all messages

                    var _data = jQuery.parseJSON(_msg),
                        _isError = !_data.success;

                    _f.find('div.alert').each(function() {
                        $(this).stop(true).animate({height: 0, opacity: 0}, function() {
                            $(this).remove();
                        });
                    });

                    var _class = (_isError) ? 'alert-danger' : 'alert-success';
                    var _div = '<div class="alert ' + _class + '">' + _data.msg;

                    if (_isError) {
                        for (i = 0; i < _data.errors.length; i++) {
                            _div += '<div class="error">' + _data.errors[i] + '</div>';
                        }
                    }


                    _div += '</div>';

                    /*_f.prepend(_div);*/
                    var _n = $(_div).prependTo(_f).css({opacity: 0});
                    var _h = _n.outerHeight();

                    _n.height(0).animate({
                        height: _h,
                        opacity: 1
                    }, 200);
                }
            })();
        </script>
        <?php
    }

}

class AjaxFormController extends \Concrete\Block\Form\Controller {
    /**
     * Users submits the completed survey.
     * This function is a pure copy of the original one from Form Controller
     * except it ends with return instead of exit, which gives us a chance to
     * get a response message right
     *
     * @param int $bID
     * @return bool
     * @throws Exception
     */
    public function action_submit_form($bID = null)
    {
        if ($this->bID != $bID) {
            return false;
        }

        $ip = \Core::make('helper/validation/ip');
        $this->view();

        if ($ip->isBanned()) {
            $this->set('invalidIP', $ip->getErrorMessage());

            return false;
        }

        $txt = \Core::make('helper/text');
        $db = \Database::connection();

        //question set id
        $qsID = intval($_POST['qsID']);
        if ($qsID == 0) {
            throw new Exception(t("Oops, something is wrong with the form you posted (it doesn't have a question set id)."));
        }

        //get all questions for this question set
        $rows = $db->GetArray("SELECT * FROM {$this->btQuestionsTablename} WHERE questionSetId=? AND bID=? order by position asc, msqID", array($qsID, intval($this->bID)));

        $errorDetails = array();

        // check captcha if activated
        if ($this->displayCaptcha) {
            $captcha = \Core::make('helper/validation/captcha');
            if (!$captcha->check()) {
                $errors['captcha'] = t("Incorrect captcha code");
                $_REQUEST['ccmCaptchaCode'] = '';
            }
        }

        //checked required fields
        foreach ($rows as $row) {
            if ($row['inputType'] == 'datetime') {
                if (!isset($datetime)) {
                    $datetime = \Core::make('helper/form/date_time');
                }
                $translated = $datetime->translate('Question'.$row['msqID']);
                if ($translated) {
                    $_POST['Question'.$row['msqID']] = $translated;
                }
            }
            if (intval($row['required']) == 1) {
                $notCompleted = 0;
                if ($row['inputType'] == 'email') {
                    if (!\Core::make('helper/validation/strings')->email($_POST['Question' . $row['msqID']])) {
                        $errors['emails'] = t('You must enter a valid email address.');
                        $errorDetails[$row['msqID']]['emails'] = $errors['emails'];
                    }
                }
                if ($row['inputType'] == 'checkboxlist') {
                    $answerFound = 0;
                    foreach ($_POST as $key => $val) {
                        if (strstr($key, 'Question'.$row['msqID'].'_') && strlen($val)) {
                            $answerFound = 1;
                        }
                    }
                    if (!$answerFound) {
                        $notCompleted = 1;
                    }
                } elseif ($row['inputType'] == 'fileupload') {
                    if (!isset($_FILES['Question'.$row['msqID']]) || !is_uploaded_file($_FILES['Question'.$row['msqID']]['tmp_name'])) {
                        $notCompleted = 1;
                    }
                } elseif (!strlen(trim($_POST['Question'.$row['msqID']]))) {
                    $notCompleted = 1;
                }
                if ($notCompleted) {
                    $errors['CompleteRequired'] = t("Complete required fields *");
                    $errorDetails[$row['msqID']]['CompleteRequired'] = $errors['CompleteRequired'];
                }
            }
        }

        //try importing the file if everything else went ok
        $tmpFileIds = array();
        if (!count($errors)) {
            foreach ($rows as $row) {
                if ($row['inputType'] != 'fileupload') {
                    continue;
                }
                $questionName = 'Question'.$row['msqID'];
                if (!intval($row['required']) &&
                    (
                        !isset($_FILES[$questionName]['tmp_name']) || !is_uploaded_file($_FILES[$questionName]['tmp_name'])
                    )
                ) {
                    continue;
                }
                $fi = new FileImporter();
                $resp = $fi->import($_FILES[$questionName]['tmp_name'], $_FILES[$questionName]['name']);
                if (!($resp instanceof Version)) {
                    switch ($resp) {
                        case \FileImporter::E_FILE_INVALID_EXTENSION:
                            $errors['fileupload'] = t('Invalid file extension.');
                            $errorDetails[$row['msqID']]['fileupload'] = $errors['fileupload'];
                            break;
                        case \FileImporter::E_FILE_INVALID:
                            $errors['fileupload'] = t('Invalid file.');
                            $errorDetails[$row['msqID']]['fileupload'] = $errors['fileupload'];
                            break;

                    }
                } else {
                    $tmpFileIds[intval($row['msqID'])] = $resp->getFileID();
                    if (intval($this->addFilesToSet)) {
                        $fs = new FileSet();
                        $fs = $fs->getByID($this->addFilesToSet);
                        if ($fs->getFileSetID()) {
                            $fs->addFileToSet($resp);
                        }
                    }
                }
            }
        }

        if (count($errors)) {
            $this->set('formResponse', t('Please correct the following errors:'));
            $this->set('errors', $errors);
            $this->set('errorDetails', $errorDetails);
        } else { //no form errors
            //save main survey record
            $u = new \User();
            $uID = 0;
            if ($u->isRegistered()) {
                $uID = $u->getUserID();
            }
            $q = "insert into {$this->btAnswerSetTablename} (questionSetId, uID) values (?,?)";
            $db->query($q, array($qsID, $uID));
            $answerSetID = $db->Insert_ID();
            $this->lastAnswerSetId = $answerSetID;

            $questionAnswerPairs = array();

            if (\Config::get('concrete.email.form_block.address') && strstr(Config::get('concrete.email.form_block.address'), '@')) {
                $formFormEmailAddress = Config::get('concrete.email.form_block.address');
            } else {
                $adminUserInfo = \UserInfo::getByID(USER_SUPER_ID);
                $formFormEmailAddress = $adminUserInfo->getUserEmail();
            }
            $replyToEmailAddress = $formFormEmailAddress;
            //loop through each question and get the answers
            foreach ($rows as $row) {
                //save each answer
                $answerDisplay = '';
                if ($row['inputType'] == 'checkboxlist') {
                    $answer = array();
                    $answerLong = "";
                    $keys = array_keys($_POST);
                    foreach ($keys as $key) {
                        if (strpos($key, 'Question'.$row['msqID'].'_') === 0) {
                            $answer[] = $txt->sanitize($_POST[$key]);
                        }
                    }
                } elseif ($row['inputType'] == 'text') {
                    $answerLong = $txt->sanitize($_POST['Question'.$row['msqID']]);
                    $answer = '';
                } elseif ($row['inputType'] == 'fileupload') {
                    $answerLong = "";
                    $answer = intval($tmpFileIds[intval($row['msqID'])]);
                    if ($answer > 0) {
                        $answerDisplay = \File::getByID($answer)->getVersion()->getDownloadURL();
                    } else {
                        $answerDisplay = t('No file specified');
                    }
                } elseif ($row['inputType'] == 'url') {
                    $answerLong = "";
                    $answer = $txt->sanitize($_POST['Question'.$row['msqID']]);
                } elseif ($row['inputType'] == 'email') {
                    $answerLong = "";
                    $answer = $txt->sanitize($_POST['Question'.$row['msqID']]);
                    if (!empty($row['options'])) {
                        $settings = unserialize($row['options']);
                        if (is_array($settings) && array_key_exists('send_notification_from', $settings) && $settings['send_notification_from'] == 1) {
                            $email = $txt->email($answer);
                            if (!empty($email)) {
                                $replyToEmailAddress = $email;
                            }
                        }
                    }
                } elseif ($row['inputType'] == 'telephone') {
                    $answerLong = "";
                    $answer = $txt->sanitize($_POST['Question'.$row['msqID']]);
                } else {
                    $answerLong = "";
                    $answer = $txt->sanitize($_POST['Question'.$row['msqID']]);
                }

                if (is_array($answer)) {
                    $answer = implode(',', $answer);
                }

                $questionAnswerPairs[$row['msqID']]['question'] = $row['question'];
                $questionAnswerPairs[$row['msqID']]['answer'] = $txt->sanitize($answer.$answerLong);
                $questionAnswerPairs[$row['msqID']]['answerDisplay'] = strlen($answerDisplay) ? $answerDisplay : $questionAnswerPairs[$row['msqID']]['answer'];

                $v = array($row['msqID'],$answerSetID,$answer,$answerLong);
                $q = "insert into {$this->btAnswersTablename} (msqID,asID,answer,answerLong) values (?,?,?,?)";
                $db->query($q, $v);
            }
            $foundSpam = false;

            $submittedData = '';
            foreach ($questionAnswerPairs as $questionAnswerPair) {
                $submittedData .= $questionAnswerPair['question']."\r\n".$questionAnswerPair['answer']."\r\n"."\r\n";
            }
            $antispam = \Core::make('helper/validation/antispam');
            if (!$antispam->check($submittedData, 'form_block')) {
                // found to be spam. We remove it
                $foundSpam = true;
                $q = "delete from {$this->btAnswerSetTablename} where asID = ?";
                $v = array($this->lastAnswerSetId);
                $db->Execute($q, $v);
                $db->Execute("delete from {$this->btAnswersTablename} where asID = ?", array($this->lastAnswerSetId));
            }

            if (intval($this->notifyMeOnSubmission) > 0 && !$foundSpam) {
                if (\Config::get('concrete.email.form_block.address') && strstr(\Config::get('concrete.email.form_block.address'), '@')) {
                    $formFormEmailAddress = Config::get('concrete.email.form_block.address');
                } else {
                    $adminUserInfo = \UserInfo::getByID(USER_SUPER_ID);
                    $formFormEmailAddress = $adminUserInfo->getUserEmail();
                }

                $mh = \Core::make('helper/mail');
                $mh->to($this->recipientEmail);
                $mh->from($formFormEmailAddress);
                $mh->replyto($replyToEmailAddress);
                $mh->addParameter('formName', $this->surveyName);
                $mh->addParameter('questionSetId', $this->questionSetId);
                $mh->addParameter('questionAnswerPairs', $questionAnswerPairs);
                $mh->load('block_form_submission');
                $mh->setSubject(t('%s Form Submission', $this->surveyName));
                //echo $mh->body.'<br>';
                @$mh->sendMail();
            }

            if (!$this->noSubmitFormRedirect) {
                /*if ($this->redirectCID > 0) {
                    $pg = \Page::getByID($this->redirectCID);
                    if (is_object($pg) && $pg->cID) {
                        $this->redirect($pg->getCollectionPath());
                    }
                }*/
                //$c = \Page::getCurrentPage();
                //header("Location: ".Core::make('helper/navigation')->getLinkToCollection($c, true)."?surveySuccess=1&qsid=".$this->questionSetId."#formblock".$this->bID);
                //exit;
            }

            return true;
        }
    }

    /**
     *
     * LEGACY CODE
     *
     * @return bool
     * @throws \Exception
     */
    function action_submit_form_legacy($allowPhoneOnce = false) {

        $ip = \Loader::helper('validation/ip');
        $this->view();

        if ($ip->isBanned()) {
            $this->set('invalidIP', $ip->getErrorMessage());
            return false;
        }

        $txt = \Loader::helper('text');
        $db = \Loader::db();

        //question set id
        $qsID=intval($_POST['qsID']);
        if($qsID==0)
            throw new \Exception(t("Oops, something is wrong with the form you posted (it doesn't have a question set id)."));

        //get all questions for this question set
        $rows=$db->GetArray("SELECT * FROM {$this->btQuestionsTablename} WHERE questionSetId=? AND bID=? order by position asc, msqID", array( $qsID, intval($this->bID)));

        // check captcha if activated
        if ($this->displayCaptcha) {
            $captcha = \Loader::helper('validation/captcha');
            if (!$captcha->check()) {
                $errors['captcha'] = t("Incorrect captcha code");
                $_REQUEST['ccmCaptchaCode']='';
            }
        }

        //checked required fields
        foreach($rows as $row){
            if ($row['inputType']=='datetime'){
                if (!isset($datetime)) {
                    $datetime = \Loader::helper("form/date_time");
                }
                $translated = $datetime->translate('Question'.$row['msqID']);
                if ($translated) {
                    $_POST['Question'.$row['msqID']] = $translated;
                }
            }
            if( intval($row['required'])==1 ){
                $notCompleted=0;
                if ($row['inputType'] == 'email') {
                    if (!\Loader::helper('validation/strings')->email($_POST['Question' . $row['msqID']])) {
                        $errors['emails'] = t('You must enter a valid email address.');
                    }
                }
                if($row['inputType']=='checkboxlist'){
                    $answerFound=0;
                    foreach($_POST as $key=>$val){
                        if( strstr($key,'Question'.$row['msqID'].'_') && strlen($val) ){
                            $answerFound=1;
                        }
                    }
                    if(!$answerFound) $notCompleted=1;
                }elseif($row['inputType']=='fileupload'){
                    if( !isset($_FILES['Question'.$row['msqID']]) || !is_uploaded_file($_FILES['Question'.$row['msqID']]['tmp_name']) )
                        $notCompleted=1;
                }elseif( !strlen(trim($_POST['Question'.$row['msqID']])) ){
                    $notCompleted=1;
                }
                if($notCompleted) $errors['CompleteRequired'] = t("Complete required fields *") ;
            }
        }

        //try importing the file if everything else went ok
        $tmpFileIds=array();
        if(!count($errors))	foreach($rows as $row){
            if( $row['inputType']!='fileupload' ) continue;
            $questionName='Question'.$row['msqID'];
            if	( !intval($row['required']) &&
                (
                    !isset($_FILES[$questionName]['tmp_name']) || !is_uploaded_file($_FILES[$questionName]['tmp_name'])
                )
            ){
                continue;
            }
            $fi = new FileImporter();
            $resp = $fi->import($_FILES[$questionName]['tmp_name'], $_FILES[$questionName]['name']);
            if (!($resp instanceof FileVersion)) {
                switch($resp) {
                    case FileImporter::E_FILE_INVALID_EXTENSION:
                        $errors['fileupload'] = t('Invalid file extension.');
                        break;
                    case FileImporter::E_FILE_INVALID:
                        $errors['fileupload'] = t('Invalid file.');
                        break;

                }
            }else{
                $tmpFileIds[intval($row['msqID'])] = $resp->getFileID();
                if(intval($this->addFilesToSet)) {

                    $fs = new FileSet();
                    $fs = $fs->getByID($this->addFilesToSet);
                    if($fs->getFileSetID()) {
                        $fs->addFileToSet($resp);
                    }
                }
            }
        }

        if(count($errors)){
            $this->set('formResponse', t('Please correct the following errors:') );
            $this->set('errors',$errors);
        }else{ //no form errors
            //save main survey record
            $u = new \User();
            $uID = 0;
            if ($u->isRegistered()) {
                $uID = $u->getUserID();
            }
            $q="insert into {$this->btAnswerSetTablename} (questionSetId, uID) values (?,?)";
            $db->query($q,array($qsID, $uID));
            $answerSetID=$db->Insert_ID();
            $this->lastAnswerSetId=$answerSetID;

            $questionAnswerPairs=array();

            if( strlen(FORM_BLOCK_SENDER_EMAIL)>1 && strstr(FORM_BLOCK_SENDER_EMAIL,'@') ){
                $formFormEmailAddress = FORM_BLOCK_SENDER_EMAIL;
            }else{
                $adminUserInfo=\UserInfo::getByID(USER_SUPER_ID);
                $formFormEmailAddress = $adminUserInfo->getUserEmail();
            }
            $replyToEmailAddress = $formFormEmailAddress;
            //loop through each question and get the answers
            foreach( $rows as $row ){
                //save each answer
                $answerDisplay = '';
                if($row['inputType']=='checkboxlist'){
                    $answer = Array();
                    $answerLong="";
                    $keys = array_keys($_POST);
                    foreach ($keys as $key){
                        if (strpos($key, 'Question'.$row['msqID'].'_') === 0){
                            $answer[]=$txt->sanitize($_POST[$key]);
                        }
                    }
                }elseif($row['inputType']=='text'){
                    $answerLong=$txt->sanitize($_POST['Question'.$row['msqID']]);
                    $answer='';
                }elseif($row['inputType']=='fileupload'){
                    $answerLong="";
                    $answer=intval( $tmpFileIds[intval($row['msqID'])] );
                    if($answer > 0) {
                        $answerDisplay = \File::getByID($answer)->getVersion()->getDownloadURL();
                    }
                    else {
                        $answerDisplay = t('No file specified');
                    }
                }elseif($row['inputType']=='url'){
                    $answerLong="";
                    $answer=$txt->sanitize($_POST['Question'.$row['msqID']]);
                }elseif($row['inputType']=='email'){
                    $answerLong="";
                    $answer=$txt->sanitize($_POST['Question'.$row['msqID']]);
                    if(!empty($row['options'])) {
                        $settings = unserialize($row['options']);
                        if(is_array($settings) && array_key_exists('send_notification_from', $settings) && $settings['send_notification_from'] == 1) {
                            $email = $txt->email($answer);
                            if(!empty($email)) {
                                $replyToEmailAddress = $email;
                            }
                        }
                    }
                }elseif($row['inputType']=='telephone'){
                    if ($allowPhoneOnce) {
                        //check if the phone number is already registered
                        $q = "select * from {$this->btAnswersTablename} where msqID=? and answer=?";
                        $v = array($row['msqID'], $_POST['Question'.$row['msqID']]);

                        $qr = $db->query($q, $v);
                        if (!empty($qr)) {
                            $errors['phoneRegistered'] .= t('This phone is already registered');
                            return false;
                        }
                    }

                    $answerLong="";
                    $answer=$txt->sanitize($_POST['Question'.$row['msqID']]);
                }else{
                    $answerLong="";
                    $answer=$txt->sanitize($_POST['Question'.$row['msqID']]);
                }

                if( is_array($answer) )
                    $answer=join(',',$answer);

                $questionAnswerPairs[$row['msqID']]['question']=$row['question'];
                $questionAnswerPairs[$row['msqID']]['answer']=$txt->sanitize( $answer.$answerLong );
                $questionAnswerPairs[$row['msqID']]['answerDisplay'] = strlen($answerDisplay) ? $answerDisplay : $questionAnswerPairs[$row['msqID']]['answer'];

                $v=array($row['msqID'],$answerSetID,$answer,$answerLong);
                $q="insert into {$this->btAnswersTablename} (msqID,asID,answer,answerLong) values (?,?,?,?)";
                $db->query($q,$v);
            }
            $foundSpam = false;

            $submittedData = '';
            foreach($questionAnswerPairs as $questionAnswerPair){
                $submittedData .= $questionAnswerPair['question']."\r\n".$questionAnswerPair['answer']."\r\n"."\r\n";
            }
            $antispam = \Loader::helper('validation/antispam'); //Here
            if (!$antispam->check($submittedData, 'form_block')) {
                // found to be spam. We remove it
                $foundSpam = true;
                $q="delete from {$this->btAnswerSetTablename} where asID = ?";
                $v = array($this->lastAnswerSetId);
                $db->Execute($q, $v);
                $db->Execute("delete from {$this->btAnswersTablename} where asID = ?", array($this->lastAnswerSetId));
            }

            if(intval($this->notifyMeOnSubmission)>0 && !$foundSpam){
                if( strlen(FORM_BLOCK_SENDER_EMAIL)>1 && strstr(FORM_BLOCK_SENDER_EMAIL,'@') ){
                    $formFormEmailAddress = FORM_BLOCK_SENDER_EMAIL;
                }else{
                    $adminUserInfo=\UserInfo::getByID(USER_SUPER_ID);
                    $formFormEmailAddress = $adminUserInfo->getUserEmail();
                }

                $mh = \Loader::helper('mail');
                $mh->to( $this->recipientEmail );
                $mh->from( $formFormEmailAddress );
                $mh->replyto( $replyToEmailAddress );
                $mh->addParameter('formName', $this->surveyName);
                $mh->addParameter('questionSetId', $this->questionSetId);
                $mh->addParameter('questionAnswerPairs', $questionAnswerPairs);
                $mh->load('block_form_submission');
                $mh->setSubject(t('%s Form Submission', $this->surveyName));
                //echo $mh->body.'<br>';
                @$mh->sendMail();
            }

            if (!$this->noSubmitFormRedirect) {
                /*if ($this->redirectCID > 0) {
                    $pg = \Page::getByID($this->redirectCID);
                    if (is_object($pg) && $pg->cID) {
                        $this->redirect($pg->getCollectionPath());
                    }
                }*/
                /*$c = Page::getCurrentPage();
                header("Location: ".Loader::helper('navigation')->getLinkToCollection($c, true)."?surveySuccess=1&qsid=".$this->questionSetId."#".$this->questionSetId);
                exit;*/
            }
        }
    }
}