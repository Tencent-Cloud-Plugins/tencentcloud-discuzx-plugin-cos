<?php
/**
 *  由lxyxinyuli开发，提供给discuz!x用户使用的插件。<br>实现网站静态资源存储到腾讯云COS，有效降低本地存储负载，提升用户体验。
 * @package 腾讯云对象存储（COS）插件
 * @author cos
 * @version 1.0.2
 * @link https://github.com/Tencent-Cloud-Plugins/tencentcloud-discuzx-plugin-cos
 * @date 2022-12-13
 */
if (! defined('IN_DISCUZ') || ! defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$step = $_GET['step'];

loadcache('plugin');
$cache = $_G['cache']['plugin']['tencentcloud_discuzx_plugin_cos'];



if (! $cache['secretid'] || ! $cache['secretkey']) {
    cpmsg('请检查设置中腾讯云SecretId和腾讯云SecretKey是否填写正确', 'action=plugins&operation=config&do=' . $pluginid);
}
if (! $cache['region']) {
    cpmsg('请检查设置中地域是否填写正确', 'action=plugins&operation=config&do=' . $pluginid);
}
if (! $cache['bucket']) {
    cpmsg('请检查设置中存储桶名称是否填写正确', 'action=plugins&operation=config&do=' . $pluginid);
}

if ($step == 2) {
    $pertask = isset($_GET['pertask']) ? intval($_GET['pertask']) : 5;
    $current = isset($_GET['current']) && $_GET['current'] > 0 ? intval($_GET['current']) : 0;
    $tableid = isset($_GET['tableid']) ? intval($_GET['tableid']) : 0;
    $next = $current + $pertask;


    if (submitcheck('dealsubmit', 1) && ($_GET['formhash'] == $_G['formhash'])) {
        $url = 'action=plugins&operation=config&do=' . $pluginid . '&identifier=tencentcloud_discuzx_plugin_cos&pmod=tencentcos&tableid='.$tableid.'&step=2&dealsubmit=yes&current=' . $next . '&pertask=0' . $pertask . '&formhash=' . formhash();
        $processed = 0;
        $arr = C::t('#tencentcloud_discuzx_plugin_cos#forum_attachment_get')->table_traversal($tableid,$current, $pertask, 'asc');

        if ($arr) {
            require_once DISCUZ_ROOT.'source/plugin/tencentcloud_discuzx_plugin_cos/cos_sdk_calling.php';
            $attach = new cos_sdk_calling($cache);
        }
        foreach ($arr as $v) {
            $processed = 1;
            $attach->upload($v['attachment']);

        }
        if ($processed) {
            cpmsg('正在上传，请稍等',$url, 'loading');
        } else {
            $tableid=$tableid+1;
            if($tableid>9){
                cpmsg('已成功上传附件图片到COS');

            }else{
                $current=0;
                $next=5;
                $url = 'action=plugins&operation=config&do=' . $pluginid . '&identifier=tencentcloud_discuzx_plugin_cos&pmod=tencentcos&tableid='.$tableid.'&step=2&dealsubmit=yes&current=' . $current . '&pertask=' . $pertask . '&formhash=' . formhash();
                cpmsg('正在上传，请稍等', $url, 'loading');
            }
        }
    } else {
        $url = 'action=plugins&operation=config&do=' . $pluginid . '&identifier=tencentcloud_discuzx_plugin_cos&pmod=tencentcos&step=2&dealsubmit=yes&formhash=' . formhash();
        cpmsg('正在上传，请稍等', $url, 'loading');
    }
} else {
    cpmsg("点击开始后，请您稍等，插件会自动帮您将图片和附件上传到设置中您填写信息的对应存储桶内", 'action=plugins&operation=config&do=' . $pluginid . '&identifier=tencentcloud_discuzx_plugin_cos&pmod=tencentcos&step=2', 'button', '', FALSE);
}

?>