<?php
/**
 * Controller for affiliate routes
 *
 * @author Jason Engelbrecht, Max Lee, & Olivia Ringhiser
 * Date: 11/18/2019
 */
global $f3;
global $db;

$f3->route('GET|POST /affiliate_review/@applicantId/@hashcode', function ($f3, $params) {
    $hashedId = str_replace('-', '/', $params['hashcode']);
    if (!password_verify($params['applicantId'], $hashedId)) {
        $f3->reroute('/home');
    }

    $f3->set('page_title', 'Application Approve/Deny');

    global $db;

    if (!empty($_POST)) {
        $db->insertAffiliateNotes($params['applicantId'], $_POST['affiliateNotes'], $_POST['membershipExpiration']);

        if ($_POST['saveNotes'] != 'save') {
            $db->updateApplicantStatus($_POST['newStatus'], $params['applicantId']);

            //reroute to thank you message
            $f3->reroute('/affiliate_confirmation');
        }
    }

    $applicant = $db->getApplicant($params['applicantId']);

    //if app is already approve/deny, no need to show it
    if ($applicant['app_status'] != 1) {
        $f3->reroute('/application_reviewed');
    }

    //get app id
    $app_id = $params['applicantId'];

    //get app type
    $app_type = $applicant['app_type'];

    //pull data based on app type and id
    $longAnswers = $db->getLongAnswer($app_id, $app_type);
    $refName = $db->getRefName($app_type);
    $routing = $refName['ref_name'];

    //set to hive
    $f3->set('longAnswers', $longAnswers);

    $f3->set('applicant', $applicant);
    $f3->set('affiliate', $db->getAffiliateName($applicant['affiliate']));
    $f3->set('reviewIncludes', "views/affiliate/long_answers/$routing/affiliateReviewIncludes.html");

    $view = new Template();
    echo $view->render('views/affiliate/affiliateReview.html');
});

$f3->route('GET|POST /affiliate_confirmation', function ($f3) {
    $f3->set('page_title', 'Thank you!');

    $view = new Template();
    echo $view->render('views/affiliate/thankyouConfirmation.html');
});

$f3->route('GET /application_reviewed', function ($f3) {
    $f3->set('page_title', 'Already reviewed');

    $view = new Template();
    echo $view->render('views/affiliate/alreadyReviewed.html');
});

//affiliates
$f3->route('GET|POST /affiliates', function ($f3) {

    //get all affiliates
    global $db;
    $f3->set('Affiliates', $db->getAffiliates());

    //delete affiliate
    if (isset($_POST['deleteAffiliate'])) {
        $affiliateId = $_POST['deleteId'];

        $db->deleteAffiliate($affiliateId);
        $f3->reroute('/affiliates');
    }

    //update affiliate

    if (isset($_POST['updateAffiliate'])) {
        $name = $_POST['editAffiliate'];
        $email = $_POST['editAffiliateEmail'];
        $affiliateId = $_POST['id'];
        $db->updateAffiliate($name, $email, $affiliateId);
        $f3->reroute('/affiliates');
    }


    //add affiliate

    if (isset($_POST['addAffiliateSave'])) {
        $name = $_POST['addAffiliate'];
        $email = $_POST['addEmail'];
        $db->addAffiliate($name, $email);
        $f3->reroute('/affiliates');
    }

    $view = new Template();
    echo $view->render('views/portal/other/affiliates.html');
});

$f3->route('GET|POST /affiliate_to_dos', function ($f3) {

    //TODO: add post variables and SQL function

    $view = new Template();
    echo $view->render('views/affiliate/affiliateToDos.html');
});