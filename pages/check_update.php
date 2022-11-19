<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-16 08:24:23
 * @modify date 2022-11-19 07:32:47
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

// Ignited engine
$engine = new Drajathasan\SlimsUpgrader\Engine('https://api.github.com');

if (isset($_GET['branch']) && isset($_GET['check']))
{
    /**
     * Get new feature
     */
    $branch = simbio_security::xssFree($_GET['branch']);
    list($lastVersion, $compare, $message) = $engine->getNewUpdate($branch);
    
    ob_start();
    
    if (!empty($message)) list($type, $message) = explode('::', $message);
    
    /**
     * Generate output based on type
     */
    generateTemplate($type??'newUpdate', ['message' => $message, 'result' => $compare, 'lastVersion' => $lastVersion]);

    $content = ob_get_clean();

    // parse to template
    include SB . 'admin/admin_template/notemplate_page_tpl.php';
    exit;
}

if (isset($_GET['upgrade']))
{
    // Start with black board verbose area
    echo '<div id="verbose" style="font: 14px Menlo, Monaco, Consolas, monospace;background-color: #18171B; height: 500px;padding: 20px;overflow-y: auto;">';
    if (empty($_GET['from']) || empty($_GET['to']))
    {
        ob_start();
        generateTemplate('danger', ['message' => 'Permintaan tidak valid']);
        $content = ob_get_clean();
        include SB . 'admin/admin_template/notemplate_page_tpl.php';
        exit;
    }
    else
    {
        $engine->setSystemEnv('development');
        $engine->doUpgrade($_GET['branch'], $_GET['from'], $_GET['to']);
    }
    echo '</div>';
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
        <div id="simpleDetail" class="d-none">
            <span id="ProgressStatus"></span>
            <div class="progress my-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <button id="showDetail" class="btn btn-secondary btn-sm">Lihat proses</button>
        </div>
    </div>
  </div>
</div>
<iframe id="blackBoard" name="resultIframe" class="d-none" style="height: 100vh"></iframe>
<script>
    $('#upgrade').submit(function(){
        $('iframe[name="resultIframe"]').removeClass('d-none');
    });
    $('.showFlush').click(function(){
        $('iframe[name="resultIframe"]').removeClass('d-none');
    });
    $('#showDetail').click(function(){
        $(this).html('Tutup detail')
        $(this).addClass('show')

        let iframe = $('#blackBoard')
        
        if (iframe.hasClass('d-none'))
        {
            iframe.removeClass('d-none')
        }
        else
        {
            iframe.addClass('d-none')
        }
    });
</script>