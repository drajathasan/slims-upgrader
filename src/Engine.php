<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-17 22:14:43
 * @modify date 2023-01-07 19:10:59
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

use Exception;
use SLiMS\Json;
use SLiMS\Http\Client;

class Engine
{
    use Guard,Utils;
    
    /**
     * Default property
     *
     * @var mixed
     */
    private string $uri = '';
    private $client = null;
    private $cache = [];

    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $this->createConnection();
    }

    /**
     * Create http client instance
     *
     * @return void
     */
    private function createConnection()
    {
        $this->client = Client::init($this->uri);
    }

    /**
     * Get lastest update
     *
     * @param [type] $branch
     * @return array
     */
    public function getNewUpdate(string $branch):Array
    {
        $message = '';
        $compare = [];
        $lastVersion = SENAYAN_VERSION_TAG;

        try {
            // Check latest version
            $lastVersion = $this->checkLatestVersion($branch);

            // compare version
            $compare = $this->compareVersion($branch);
            if (!$compare['status']) throw new Exception($compare['message']);
            
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return [$lastVersion, $compare, $message];
    }

    /**
     * @param string $branch
     * @param string $from
     * @param string $to
     * @return void
     */
    public function doUpgrade(string $branch, string $from, string $to):void
    {
        // Hidden iframe element
        $this->turnOffVerbose();

        // Hide all SLiMS Menu
        $this->turnOffMenu();
        
        try {
            // PHP extension check
            $this->checkExt();
            
            // Writable scandir
            $this->checkPermissions(SB);

            // Check latest version
            $lastVersion = $this->checkLatestVersion($branch);
            
            // no limit
            set_time_limit(0);

            $this->progressMessage('<strong style="padding: 10px; color: black">'.___('Mengunduh data perubahan').'</strong></br>');
            // Get new diff file from github
            $this->downloadDiff($branch);

            // Parse diff file
            $new = $this->diffParse($branch, SB . 'files/cache/SLiMS.diff');

            // Download latest
            $this->downloadLatestPackages(($branch != 'develop' ? $lastVersion : 'develop'));
            $this->unzip(($branch != 'develop' ? $lastVersion : 'develop'));

            // Reset last connection
            Client::reset(); 
            $this->createConnection();

            // Reset precentation
            $this->setPercentProgress(0, 0);
            
            // total downloaded file
            $total = count($new);

            $this->progressMessage('<strong style="padding: 10px; color: black">' . ___('Mengunduh dan memasang berkas pembaharuan') . '</strong></br>');

            $this->cache = [];
            $rootPath = SB . 'files/cache/slims9_bulian-' . trim($lastVersion, 'v') . DS;
            foreach ($new as $index => $newUpdate) {
                // Make a cache
                $this->cache[$index] = [
                    'to' => $newUpdate['file'],
                    'raw' => $newUpdate['url'],
                    'changes_status' => $newUpdate['status'],
                    'download_status' => true,
                    'error_message' => ''
                ];

                // Migrating #1 schema
                // $download = Client::download($newUpdate['url']);
                
                // migrating data
                // $this->migrate($index, $from, $download, $newUpdate['file'], $newUpdate['status']);

                // Local migration
                $this->migrateFromLocal($index, trim($from, 'v'), $rootPath, $newUpdate['file'], $newUpdate['status']);

                // Show output
                $this->setPercentProgress(($index + 1), $total);
                $this->showMessage($index);

                if ($total == ($index + 1)) $this->outputWithFlush('<strong style="padding: 10px; color: green">' . ___('Selesai mendownload') . '</strong></br>');
            }
            $this->progressMessage('<strong style="padding: 10px; color: black">' . ___('Selesai memasang pembaharuan') . '</strong></br>');
            sleep(2);
            $this->progressMessage('<strong style="padding: 10px; color: black">' . ___('Memperbaharui basis data') . '</strong></br>');

            $this->upgradeDatabase($from);

            $this->progressMessage('<strong style="padding: 10px; color: black">' . ___('Selesai memperbaharui basis data') . '</strong></br>');
            $this->outputWithFlush('<strong style="padding: 10px; color: green">' . ___('Selesai memperbaharui basis data') . '</strong></br>');
            $this->progressMessage('<strong style="padding: 10px; color: green">' . ___('Selesai memperbaharui SLiMS, anda akan logout dalam 5 detik') . '</strong></br>');
            $this->delTree($rootPath);
            $this->logOut();
            
        } catch (\Exception $e) {
            $this->progressMessage('<strong style="padding: 10px; color: red">' . $e->getMessage() . '</strong></br>');
            generateTemplate('danger', ['message' => $e->getMessage()]);
        }
    }

    /**
     * this is not SLiMS environment
     * its just on if upgrade process is on
     *
     * @param string $env
     * @return void
     */
    public function setSystemEnv(string $env)
    {
        switch ($env) {
            case 'development':
              @error_reporting(-1);
              @ini_set('display_errors', true);
              break;
            case 'production':
              @ini_set('display_errors', false);
              @error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
              break;
            default:
              header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
              echo 'The application environment is not set correctly.';
              exit(1); // EXIT_ERROR
        }
    }

    /**
     * Migrating file
     *
     * @param integer $index
     * @param string $currentTag
     * @param object $download
     * @param string $destination
     * @param string $status
     * @return void
     */
    private function migrate(int $index, string $currentTag, object $download, string $destination, string $status)
    {
        try {
            // new or update
            if (in_array($status, ['modified','added']))
            {
                // be safe and make a backup
                $originalPath = SB . $destination;
                if (file_exists($originalPath)) copy($originalPath, $originalPath . $currentTag);
                
                if ($status == 'added' && !file_exists($path = dirname(SB . $destination))) $this->mkdir($path . DS);

                // save new file
                $download->to(SB . $destination);
            }

            // deleted
            if (preg_match('/(delete)|(remove)/i', $status)) unlink(SB . $destination);
            
        } catch (\Exception $e) {
            $this->cache[$index]['download_status'] = false;
            $this->cache[$index]['error_message'] = $e->getMessage();
        }
    }

    /**
     * Migrating file
     *
     * @param integer $index
     * @param string $currentTag
     * @param object $download
     * @param string $destination
     * @param string $status
     * @return void
     */
    private function migrateFromLocal(int $index, string $currentTag, string $rootPath, string $destination, string $status)
    {
        try {
            // new or update
            if (in_array($status, ['modified','added']))
            {
                // be safe and make a backup
                $originalPath = SB . $destination;
                if (file_exists($originalPath)) copy($originalPath, $originalPath . $currentTag);
                
                if ($status == 'added' && !file_exists($path = dirname(SB . $destination))) $this->mkdir($path . DS);

                // save new file
                copy($rootPath . $destination, SB . $destination);
            }

            // deleted
            if (preg_match('/(delete)|(remove)/i', $status)) unlink(SB . $destination);
            
        } catch (\Exception $e) {
            $this->cache[$index]['download_status'] = false;
            $this->cache[$index]['error_message'] = $e->getMessage();
        }
    }

    /**
     * Upgrade current database
     *
     * @param string $previousVersion
     * @return void
     */
    private function upgradeDatabase(string $previousVersion)
    {
        $this->outputWithFlush('<div style="color: lightblue"><strong>' . ___('Meningkatkan basis data') . '</strong></div>');
        
        include_once __DIR__ . '/SLiMS.inc.php';
        include_once __DIR__ . '/Upgrade.inc.php';
        $version = array_values(array_filter(require __DIR__ . '/Version.php', function($data) use($previousVersion) {
            if ($data['version'] == $previousVersion) return true;
        }))[0]['value']??'0';

        if ($version == 0) exit($this->outputWithFlush('<div style="padding: 10px; color: red">' . ___('Versi SLiMS anda tidak diketahui!') . '</div>'));

        $slims = new \Install\SLiMS();
        $slims->createConnection();
        $upgrade = \Install\Upgrade::init($slims)->from($version);
        if (count($upgrade) > 0) $this->outputWithFlush('<div style="padding: 10px; color: red">' . $upgrade . '</div>');
        
    }

    /**
     * Check latest version
     * from github
     *
     * @param string $branch
     * @return string
     */
    private function checkLatestVersion(string $branch):string
    {
        // Rolling release
        if ($branch === 'develop') return 'develop';

        // get from github api
        $latest = $this->client->get('/repos/slims/slims9_bulian/releases/latest');
        if ($latest->getStatusCode() != 200) throw new Exception('danger::' . $latest->getError()); // set error

        // set data
        $data = $latest->toArray();
        if (SENAYAN_VERSION_TAG === $data['tag_name']) throw new Exception('info::' . ___('SLiMS anda sudah paling baru'));
        
        // return latest release
        return $data['tag_name'];
    }

    /**
     * @param string $branch
     * @return array
     */
    private function compareVersion(string $branch):array
    {
        // Comparing data with current senayan tag and inputed branch
        $compare = $this->client->get('/repos/slims/slims9_bulian/compare/' . SENAYAN_VERSION_TAG . '...' . $branch);
        if ($compare->getStatusCode() != 200) return ['status' => false, 'message' => $compare->getError()];

        // back to outside
        return ['status' => true, 'data' => $compare->toArray()];
    }

    private function downloadDiff(string $branch)
    {
        Client::download('https://github.com/slims/slims9_bulian/compare/' . SENAYAN_VERSION_TAG . '...' . $branch . '.diff')
                ->withProgress(SB . 'files/cache/SLiMS.diff', function($totalSize, $currentSize){
                    $this->setPercentProgress($currentSize, $totalSize);
                });
    }

    private function downloadLatestPackages(string $branch)
    {
        $this->progressMessage('<strong style="padding: 10px; color: black">' . ___('Mengunduh paket versi terbaru, silahkan tunggu') . '</strong></br>');
        
        // Set url
        $url = 'https://github.com/slims/slims9_bulian/releases/download/' . $branch . '/slims9_bulian-' . str_replace('v', '', $branch) . '.zip';
        if ($branch == 'develop') $url = 'https://codeload.github.com/slims/slims9_bulian/zip/refs/heads/develop';
        
        Client::download($url)
                ->withProgress(SB . 'files/cache/' . $branch . '.zip', function($totalSize, $currentSize) {
                    $this->setPercentProgress($currentSize, $totalSize);
                });

        $this->setPercentProgress(0, 0);
    }

    public static function __callStatic($method, $arguments)
    {
        $static = new static('');
        
        if (method_exists($static, $method)) return $static->{$method}(...$arguments);
    }
}