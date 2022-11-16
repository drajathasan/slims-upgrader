<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-16 08:24:23
 * @modify date 2022-11-16 15:52:51
 * @license GPLv3
 * @desc [description]
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

use SLiMS\Json;
use SLiMS\Http\Client;

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require __DIR__ . '/../vendor/autoload.php';

if (isset($_GET['branch']) && isset($_GET['check']))
{
    $branch = simbio_security::xssFree($_GET['branch']);
    $client = Client::init('https://api.github.com');
    $message = '';
    $compare = [];
    $lastVersion = SENAYAN_VERSION_TAG;

    try {
        // Check latest version
        $lastVersion = checkLatestVersion($client, $branch);

        // compare version
        $compare = compareVersion($client, $branch);
        if (!$compare['status']) throw new Exception($compare['message']);
        
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
    
    ob_start();
    
    if (!empty($message)) list($type, $message) = explode('::', $message);
    
    generateTemplate($type??'newUpdate', ['message' => $message, 'result' => $compare, 'lastVersion' => $lastVersion]);

    $content = ob_get_clean();
    include SB . 'admin/admin_template/notemplate_page_tpl.php';
    exit;
}

if (isset($_GET['upgrade']))
{
    ob_start();
    if (empty($_GET['from']) || empty($_GET['to']))
    {
        generateTemplate('danger', ['message' => 'Permintaan tidak valid']);
    }

    $content = ob_get_clean();
    include SB . 'admin/admin_template/notemplate_page_tpl.php';
    exit;
}

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Check Update'); ?></h2>
    </div>
    <div class="sub_section">
        <form id="upgrade" action="<?= selfUrl() ?>" target="resultIframe" id="search" method="get" class="form-inline">
            <input type="hidden" name="id" value="<?= simbio_security::xssFree($_GET['id']) ?>"/>
            <input type="hidden" name="mod" value="<?= simbio_security::xssFree($_GET['mod']) ?>"/>
            <?php echo __('From'); ?>&nbsp;&nbsp;&nbsp;
            <select name="branch" class="form-control">
                <option value="master"><?= __('Stable') ?></option>
                <option value="develop"><?= __('Development') ?></option>
            </select>
            <input class="btn btn-primary" type="submit" name="check" value="<?= __('Check') ?>"/>
        </form>
    </div>
  </div>
</div>
<div id="pleaseWait" class="alert alert-info d-none">
    <strong>Tunggu sebentar...</strong>
</div>
<iframe name="resultIframe" style="height: 100vh"></iframe>
<script>
    $('#upgrade').submit(function(){
        toastr.info('Tunggu', 'Proses sedang berjalan');
        console.log($('iframe[name="resultIframe"]'));
    })
</script>