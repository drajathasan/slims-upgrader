<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-19 07:34:13
 * @modify date 2023-01-09 14:23:27
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

trait Guard
{
    /**
     * Get all dir content
     *
     * @param string $dir
     * @return void
     */
    private function getAllDirPath($dir)
    {
        $listOfDir = [];
        foreach (array_diff(scandir($dir), ['.','..','.git']) as $content) {
            $path = $dir . $content;
            if (is_dir($path)) {
                $listOfDir[] = $path;
                foreach ($this->getAllDirPath($path . DS) as $innerContent) 
                    $listOfDir[] = $innerContent;
            }
        }
    
        return $listOfDir;
    }

    /**
     * Check directory permission 
     * recursively
     *
     * @param string $currentDir
     * @return void
     */
    private function checkPermissions(string $currentDir)
    {
        $isNotWriteAble = [];
        foreach ($this->getAllDirPath($currentDir . DS) as $directory) {
            $firstIndex = explode(DS, trim(str_replace($currentDir, '', $directory), DS))[0];
            if (!is_writable($directory)) {
                if (!isset($isNotWriteAble[$firstIndex])) $isNotWriteAble[$firstIndex] = [];
                if (basename($directory) !== $firstIndex) $isNotWriteAble[$firstIndex][] = $directory;
            }
        }

        if (count($isNotWriteAble)) $this->setDirError($isNotWriteAble);
    }

    /**
     * Based on SLiMS Official installer
     *
     * @return void
     */
    private function checkExt()
    {
        include_once __DIR__ . '/SLiMS.inc.php';
        $slims = new \Install\SLiMS();
        $php_minimum_version = '7.4';

        $data = [
            'is_pass' => $slims->isPhpOk($php_minimum_version) &&
                $slims->databaseDriverType() &&
                $slims->phpExtensionCheck('bool'),
            'detail' => [
                'php' => [
                    'title' => ___('PHP Version'),
                    'status' => $slims->isPhpOk($php_minimum_version),
                    'version' => phpversion(),
                    'data' => str_replace('{phpversion}', '',___('Minimum PHP version to install SLiMS is {phpversion}. Please upgrade it first!'))
                ],
                'database' => [
                    'title' => ___('Database driver'),
                    'status' => $slims->databaseDriverType(),
                    'version' => $slims->databaseDriverType(),
                    'data' => ___('SLiMS required MYSQL for database management. Please install it first!')
                ],
                'phpextension' => [
                    'title' => ___('PHP Extension'),
                    'status' => $slims->phpExtensionCheck('bool'),
                    'version' => '*',
                    'data' => $slims->phpExtensionCheck()
                ],      
            ]
        ];

        if (!$data['is_pass'])
        {
            $message  = '<div class="w-full">';
            $message .= '<h2>' . ___('Galat') . '</h2>';
            $message .= '<ull>';
            foreach ($data['detail'] as $section => $detail) {
                if ($detail['status']) continue;
                extract($detail);
                $message .= <<<HTML
                <li>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{$title}</strong>
                        <p>{$data}</p>
                    </div>
                </li>
                HTML;
            }
            $message .= '</ul>';
            $message .= '</div>';

            throw new \Exception($message);
        }
            
    }

    /**
     * Display directory error
     *
     * @param array $error
     * @return void
     */
    private function setDirError($error)
    {
        $list = '';
        foreach ($error as $basePath => $listPath) {
          $list .= '<li>' . trim(SB . $basePath . ' ' . (count($listPath) ? ___(' dan direktori didalamnya.') : '')) . '</li>';
        }  

        $user = (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) ? (posix_getpwuid(posix_geteuid())['name']??'foo') : 'foo';
        list($paragraph, $example, $errorHeader) = [
            ___('Plugin ini membutuhkan izin untuk menulis file diseluruh folder yang ada di SLiMS<br>berikut direktori yang tidak dapat ditulis:'),
            ___('Solusi (contoh)'),
            ___('Galat')
        ];
        $message = <<<HTML
        <div>
            <h3>{$errorHeader}</h3>
            <p>{$paragraph}</p>
            <ul>{$list}</ul>
            <strong style="width: 100%; display: block">{$example}:</strong>
            <code>sudo chown {$user}:{$user} -R /var/www/html/template/</code>
        </div>
        HTML;
        throw new \Exception(preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''], $message));
    }
      
}