<?php
interface dibs_fw_helpers_interface {
    function dibsflex_helper_dbquery_write($sQuery);
    function dibsflex_helper_dbquery_read($sQuery);
    function dibsflex_helper_dbquery_read_single($mResult, $sName);
    function dibsflex_helper_cmsurl($sLink);
    function dibsflex_helper_getconfig($sVar);
    function dibsflex_helper_getdbprefix();
    function dibsflex_helper_getReturnURLs($sURL);
    function dibsflex_helper_getOrderObj($mOrderInfo, $bResponse = FALSE);
    function dibsflex_helper_getAddressObj($mOrderInfo);
    function dibsflex_helper_getShippingObj($mOrderInfo);
    function dibsflex_helper_getItemsObj($mOrderInfo);
    function dibsflex_helper_redirect($sURL);
    function dibsflex_helper_getlang($sKey);
    function dibsflex_helper_cgiButtonsClass();
    function dibsflex_helper_modVersion();
}
?>