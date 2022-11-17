<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-17 22:14:43
 * @modify date 2022-11-18 01:18:15
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

use SLiMS\Json;
use SLiMS\Http\Client;

class Engine
{
    private string $uri = '';
    private $client = null;
    private $cache = [];

    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $this->createConnection();
    }

    private function createConnection()
    {
        $this->client = Client::init($this->uri);
    }

    public function getNewUpdate($branch)
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

    public function doUpgrade($branch, $from, $to)
    {
        try {
            // Check latest version
            $lastVersion = $this->checkLatestVersion($branch);

            // Get new file
            $new = $this->compareVersion($branch);

            set_time_limit(0);
            // Reset last connection
            Client::reset(); $this->createConnection();

            // total downloaded file
            $total = count($new['data']['files']);

            $this->cache = [];
            foreach ($new['data']['files'] as $index => $newUpdate) {
                // Make a cache
                $this->cache[$index] = [
                    'to' => $newUpdate['filename'],
                    'raw' => $newUpdate['raw_url'],
                    'changes_status' => $newUpdate['status'],
                    'download_status' => true,
                    'error_message' => ''
                ];

                $download = Client::download($newUpdate['raw_url']);
                
                // migrating data
                $this->migrate($index, $from, $download, $newUpdate['filename'], $newUpdate['status']);

                // Show output
                $this->showMessage($index);

                if ($total == ($index + 1)) $this->outputWithFlush('<strong style="padding: 10px; color: green">Selesai mendownload</strong></br>');
            }

            $this->upgradeDatabase($from);

            $this->outputWithFlush('<strong style="padding: 10px; color: green">Selesai memperbaharui basis data</strong></br>');
            
        } catch (\Exception $e) {
            generateTemplate('danger', ['message' => $e->getMessage()]);
        }
    }

    private function migrate($index, $currentTag, $download, $destination, $status)
    {
        try {
            // new or update
            if (in_array($status, ['modified','added']))
            {
                // be safe and make a backup
                copy(SB . $destination, SB . $destination . $currentTag);
                
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

    private function upgradeDatabase(string $previousVersion)
    {
        $this->outputWithFlush('<div style="color: lightblue"><strong>Meningkatkan basis data</strong></div>');
        
        include __DIR__ . '/SLiMS.inc.php';
        include __DIR__ . '/Upgrade.inc.php';
        $version = array_values(array_filter(require __DIR__ . '/Version.php', function($data) use($previousVersion) {
            if ($data['version'] == $previousVersion) return true;
        }))[0]['value']??'0';

        if ($version == 0) exit($this->outputWithFlush('<div style="padding: 10px; color: red">Versi SLiMS anda tidak diketahui!</div>'));

        $slims = new \SLiMS();
        $upgrade = \Install\Upgrade::init($slims)->from($version);
        if (count($upgrade) > 0) $this->outputWithFlush('<div style="padding: 10px; color: red">' . $upgrade . '</div>');
        
    }

    private function mkdir(string $path)
    {
        mkdir($path, 0777, true);
    }

    private function showMessage($index)
    {
        $message = '<strong style="padding: 10px; color: ' . 
                ($this->cache[$index]['download_status'] ? 'green' : 'red') . '">' . 
                ($this->cache[$index]['download_status'] ? 'Sukses mengupdate : ' . $this->cache[$index]['to'] : 'Gagal : ' . $this->cache[$index]['error_message']) . 
            '</strong></br>';
        $this->outputWithFlush($message);
    }

    private function outputWithFlush(string $message = '')
    {
        echo $message;
        echo <<<HTML
        <script>
            setTimeout(() => {
                scroll({
                    top: document.body.scrollHeight,
                    behavior: "smooth"
                }); 
            }, 2000);
        </script>
        HTML;
        ob_flush();
        flush();
    }

    private function checkLatestVersion($branch)
    {
        // Rolling release
        if ($branch === 'develop') return 'develop';

        // get from github api
        $latest = $this->client->get('/repos/slims/slims9_bulian/releases/latest');
        if ($latest->getStatusCode() != 200) throw new Exception('danger::' . $latest->getError()); // set error

        // set data
        $data = $latest->toArray();
        if (SENAYAN_VERSION_TAG === $data['tag_name']) throw new Exception('info::SLiMS anda sudah paling baru');
        
        // return latest release
        return $data['tag_name'];
    }

    private function compareVersion($branch)
    {
        // Comparing data with current senayan tag and inputed branch
        $compare = $this->client->get('/repos/slims/slims9_bulian/compare/'.SENAYAN_VERSION_TAG . '...' . $branch);
        if ($compare->getStatusCode() != 200) return ['status' => false, 'message' => $compare->getError()];

        // back to outside
        return ['status' => true, 'data' => $compare->toArray()];
    }
}