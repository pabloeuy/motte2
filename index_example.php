<?php
session_start();
$service = mteCtr::get()->getService();
$module  = mteCtr::get()->getModule();
$method  = mteCtr::get()->getMethod();

// logout
if ($module == MODULE_DEFAULT && $method == 'logout') {
    // force logout
    tools::killSession();

    //redirect to index and exit
} elseif ($module == MODULE_DEFAULT && $method == 'login') {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //validate user and reply with mteCtr::get()->getResponse()
    } else {
        //echo login html
    }

} else {
    // access control
    $userOk = tools::getSessionVar('userOk') != '';

    // requested service
    switch ($service) {
        case SRV_HTML:
            if ($userOk) {
                //echo common html
            } else {
                // force login
                tools::killSession();

                //redirect to index and exit
            }
            break;
        case SRV_AJAX_XML:
            if ($userOk) {
                mteCtr::get()->ajax($module, $method);
            } else {
                //set status error (session expired)
            }
            echo mteCtr::get()->getResponse()->getXml();
            break;
        case SRV_AJAX_JSON:
            if ($userOk) {
                mteCtr::get()->ajax($module, $method);
                echo mteCtr::get()->getResponse()->getJSon();
            }
            break;
        case SRV_AJAX_HTML:
            if ($userOk) {
                echo mteCtr::get()->execute($module, $method);
            } else {
                //set status error (session expired)
            }
            break;
        case SRV_UPLOADIFY:
            echo mteCtr::get()->execute($module, $method);
            break;
    }
}
