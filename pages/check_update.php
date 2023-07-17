<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-16 08:24:23
 * @modify date 2023-07-17 16:58:30
 * @license GPLv3
 * @desc [description]
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

use SLiMS\Json;
use SLiMS\Http\Client;
use Drajathasan\SlimsUpgrader\Html;
use GuzzleHttp\Client as GClient;

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

if (isset($_GET['check_internet'])) {
    try {
        $client = new GClient;
        $request = $client->request('GET', 'https://raw.githubusercontent.com/drajathasan/slims-upgrader-manifest/main/detail.json', [
            'connect_timeout' => 10,
            'headers' => ['User-Agent' => $_SERVER['HTTP_USER_AGENT']]
        ]);
    
        if ($request->getStatusCode() == '200'): ?>
            <h3 class="font-weight-bold text-muted">Selamat Data di Peningkat! 👋</h3>
            <p style="font-size: 12pt">Sudah siap untuk mendapatkan fitur baru yang keren?</p>
            <div class="d-flex flex-row">
                <a href="<?= selfUrl(['page' => 'selectVersion', 'check_internet' => 'unset']) ?>" class="upgraderLink notAJAX btn btn-primary">Hayuk, Gaskeun</a>&nbsp;
                <a href="<?= AWB ?>" class="btn btn-outline-secondary notAJAX">Ntar dulu ah</a>
            </div>
        <?php endif;
    } catch (Exception $e) {
        ?>
            <h3 class="font-weight-bold text-muted">Yah, internet nya mati 😔</h3>
            <p style="font-size: 12pt">meningkatankan versi SLiMS membutuhkan akses internet,</p>
            <div class="d-flex flex-row">
                <a href="<?= selfUrl(['page' => 'welcome']) ?>" class="upgraderLink btn btn-outline-secondary notAJAX">Coba lagi</a>
            </div>
        <?php
    }
    exit;
}

if (isset($_GET['branch']) && isset($_GET['check']))
{
    /**
     * Get new feature
     */
    $branch = xssFree($_GET['branch']);
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
    echo '<div id="verbose" style="font: 14px Menlo, Monaco, Consolas, monospace;background-color: #18171B; min-height: 500px;padding: 20px;">';
    if (empty($_GET['from']) || empty($_GET['to']))
    {
        ob_start();
        generateTemplate('danger', ['message' => ___('Permintaan tidak valid')]);
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

if (isset($_GET['page'])) {
    $page = basename($_GET['page']);
    if ($page != 'welcome') $_SESSION['page'] = $page;
    include __DIR__ . '/sections/' . $page . '.php';
    exit;
}
?>
<section class="w-100 d-flex">
    <div class="col-2 d-flex justify-content-center align-items-end" style="height: 100vh; background: #2ec27e; /* fallback for old browsers */">
        <a href="<?= selfUrl(['page' => $_SESSION['page']??'welcome']) ?>" class="upgraderLink d-flex justify-content-center notAJAX">
            <svg width="65" height="65" class="mb-5" version="1.1" viewBox="0 0 66.146 66.146" xmlns="http://www.w3.org/2000/svg">
                <path d="m33.073 0a33.073 33.073 0 0 0-33.073 33.073 33.073 33.073 0 0 0 33.073 33.073 33.073 33.073 0 0 0 33.073-33.073 33.073 33.073 0 0 0-33.073-33.073zm-0.01183 10.659c0.30737 0.0042 0.62283 0.08733 0.89784 0.2534l17.439 10.296c0.03219 0 0.03219 0.03327 0.06473 0.03327 0.42061 0.29893 0.71125 0.76382 0.7438 1.2953v20.759h5.29e-4c0 0.06639 0.03237 0.09995-0.03237 0.16635v0.13262c-0.03237 0.46501-0.29128 0.93025-0.67954 1.2292-0.09706 0.06638-0.19408 0.13263-0.29123 0.1659l-17.439 10.33c-0.22648 0.09968-0.45305 0.16635-0.7119 0.16635-0.25883 0-0.51777-0.06632-0.7766-0.19916l-0.09711-0.06659-17.439-10.33c0-0.03314-0.03236-0.03328-0.06472-0.06654-0.42061-0.29894-0.71189-0.76383-0.74425-1.2953v-20.926c0.03235-0.53145 0.29128-0.99637 0.67953-1.3285l0.12944-0.06654 17.471-10.33c0.24266-0.14945 0.54216-0.2243 0.84953-0.22014zm-10.944 7.7264-6.7297 3.9861c-0.09706 0.06644-0.16179 0.16612-0.16179 0.29898v20.793c0 0.13286 0.06474 0.23257 0.19415 0.29898l6.6974 3.9856 10.968-6.51 10.968 6.51 6.665-3.9523s0.03219-3e-6 0.06473-0.03327c0.09703-0.06642 0.16179-0.19929 0.16179-0.29898v-20.826c-0.03237-0.13287-0.0971-0.23257-0.19415-0.29898h-0.03237l-6.665-3.9528-10.968 6.5105zm-0.71189 1.1959 23.36 13.85v13.087l-23.36-13.818z" fill="#fff" stroke-width="1.5927"/>
            </svg>
            <div class="d-flex flex-column text-white ml-2">
                <h3 class="font-weight-bold">Upgrader</h3>
                <h6>v2.0.0</h6>
            </div>
        </a>
    </div>
    <div id="upgraderContent" class="col-10" style="height: 100vh">
        <?php include __DIR__ . DS . 'sections' . DS . ($_SESSION['page']??'welcome') . '.php'; ?>
    </div>
</section>
<script>
    $('#sidepan').remove()
    $('#header').remove()
    $('#help').remove()
    $('#footer').remove()
    $('#mainContent').on('click', '.upgraderLink', function(e){
        e.preventDefault()
        let href = $(this).attr('href')
        $('#upgraderContent').simbioAJAX(href)
    });
    $('#mainContent').attr('style', 'margin-left: 0px !important')
</script>