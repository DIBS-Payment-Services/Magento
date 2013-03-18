<?php
interface dibs_pw_helpers_interface {
    function helper_dibs_db_write($sQuery);
    function helper_dibs_db_read_single($mResult, $sName);
    function helper_dibs_tools_conf($sVar);
    function helper_dibs_tools_prefix();
    function helper_dibs_tools_lang($sKey);
    function helper_dibs_tools_url($sLink);
    function helper_dibs_obj_order($mOrderInfo, $bResponse = FALSE);
    function helper_dibs_obj_items($mOrderInfo);
    function helper_dibs_obj_ship($mOrderInfo);
    function helper_dibs_obj_addr($mOrderInfo);
    function helper_dibs_obj_urls($mOrderInfo = null);
    function helper_dibs_obj_etc($mOrderInfo);
}
?>