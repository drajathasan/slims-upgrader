<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-19 07:34:13
 * @modify date 2022-11-20 16:59:46
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

trait Guard
{
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

    private function checkPermissions($currentDir)
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

    private function setDirError($error)
    {
        $list = '';
        foreach ($error as $basePath => $listPath) {
          $list .= '<li>' . trim(SB . $basePath . ' ' . (count($listPath) ? ' dan direktori didalamnya.' : '')) . '</li>';
        }  

        $user = (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) ? (posix_getpwuid(posix_geteuid())['name']??'foo') : 'foo';
        $message = <<<HTML
        <div>
            <h3>Galat</h3>
            <p>Plugin ini membutuhkan izin untuk menulis file diseluruh folder yang ada di SLiMS<br>berikut direktori yang tidak dapat ditulis:</p>
            <ul>{$list}</ul>
            <strong style="width: 100%; display: block">Solusi (contoh):</strong>
            <code>sudo chown {$user}:{$user} -R /var/www/html/template/</code>
        </div>
        HTML;
        throw new \Exception(preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''], $message));
    }
      
}