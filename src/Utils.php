<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-19 17:37:00
 * @modify date 2023-01-04 21:27:12
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

trait Utils
{
    /**
     * method to get detail of changed files
     * based on git diff
     *
     * @param string $branch
     * @param string $filePath
     * @return void
     */
    public function diffParse(string $branch, string $filePath)
    {
        $fn = fopen($filePath,"r");
        $result = [];
        while(! feof($fn))  {
            $string = fgets($fn);
            if (substr($string, 0,10) == 'diff --git') 
            {
                $diffPath = trim(str_replace('diff --git', '', $string));
                $newFilePosition = strpos($diffPath, ' b/') + 3;
                $file = substr($diffPath, $newFilePosition);
                $status = fgets($fn);
                $result[] = [
                    'file' => $file,
                    'url' => 'https://raw.githubusercontent.com/slims/slims9_bulian/' . $branch . '/' . $file,
                    'status' => preg_match('/deleted/i', $status) ? 'deleted' : (preg_match('/new/i', $status) ? 'added' : 'modified')
                ];
            }
            ob_flush();
            flush();
        }
        return $result;
    }
    /**
     * Make directory
     *
     * @param string $path
     * @return void
     */
    private function mkdir(string $path)
    {
        mkdir($path, 0777, true);
    }

    /**
     * Show output message
     *
     * @param [type] $index
     * @return void
     */
    private function showMessage($index)
    {
        $message = '<strong style="padding: 10px; color: ' . 
                ($this->cache[$index]['download_status'] ? 'green' : 'red') . '">' . 
                ($this->cache[$index]['download_status'] ? ___('Sukses mengupdate : ') . $this->cache[$index]['to'] : ___('Gagal : ') . $this->cache[$index]['error_message']) . 
            '</strong></br>';

        $js = <<<HTML
        <script>
            if (parent.$('#simpleDetail').hasClass('d-none'))
            {
                parent.$('#simpleDetail').removeClass('d-none')
            }
        </script>
        HTML;
        $this->outputWithFlush($message . $js);
    }

    /**
     * set message outside verbose area
     *
     * @param string $stepMessage
     * @return void
     */
    private function progressMessage($stepMessage)
    {
        $message = <<<HTML
        <script>
            if (parent.$('#simpleDetail').hasClass('d-none'))
            {
                parent.$('#simpleDetail').removeClass('d-none')
            }
            parent.document.getElementById('ProgressStatus').innerHTML = `{$stepMessage}`;
        </script>
        HTML;

        $this->outputWithFlush($message);
    }

    /**
     * Flush output process
     *
     * @param string $message
     * @return void
     */
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
            }, 500);
        </script>
        HTML;
        ob_flush();
        flush();
    }

    /**
     * Set progress precentation
     *
     * @param integer $currentStep
     * @param integer $totalStep
     * @return void
     */
    public function setPercentProgress(int $currentStep, int $totalStep)
    {
        $percent = $currentStep == 0 || $totalStep == 0 ? 0 : round(($currentStep / $totalStep) * 100);
        echo <<<HTML
        <script>
            var progressBar = parent.document.querySelector('.progress-bar');
            progressBar.setAttribute('style', 'width: {$percent}%');
            progressBar.innerHTML = '{$percent}%';
        </script>
        HTML;
        ob_flush();
        flush();
        
    }

    /**
     * Switching iframe visibility
     *
     * @param boolean $status
     * @return void
     */
    public function turnOffVerbose(bool $status = true)
    {
        if ($status)
        {
            echo <<<HTML
            <script>
                parent.$('input[name="check"]').attr('disabled', 'true');
                parent.$('iframe[name="resultIframe"]').addClass('d-none');
            </script>
            HTML;
            ob_flush();
            flush();
        }
    }

    /**
     * Hide all SLiMS Menu
     *
     * @return void
     */
    public function turnOffMenu()
    {
        echo <<<HTML
        <script>
            parent.$('#mainContent').attr('style', 'display: block; margin-left: 0px !important');
            parent.$('#sidepan').remove();
            parent.$('#header').remove();
        </script>
        HTML;
        ob_flush();
        flush();
    }

    /**
     * Redirect current page 
     * into opac
     *
     * @return value
     */
    public function logOut()
    {
        $url = AWB . 'logout.php';
        echo <<<HTML
        <script>setTimeout(() => top.location.href = '{$url}', 5000)</script>
        HTML;
    }

    /**
     * Extract zip file
     *
     * @param string $branch
     * @return void
     */
    public function unzip(string $branch)
    {
        $path = SB . 'files/cache/' . $branch . '.zip';
        $zip = new \ZipArchive;
        
        $errorMessage = [
            1 => 'Multi-disk zip archives not supported',
            'Renaming temporary file failed',
            'Closing zip archive failed',
            'Seek error',
            'Read error',
            'Write error',
            'CRC error',
            'Containing zip archive was closed',
            'No such file',
            'File already exists',
            'Can\'t open file',
            'Failure to create temporary file',
            'Zlib error',
            'Malloc failure',
            'Entry has been changed',
            'Compression method not supported',
            'Premature EOF',
            'Invalid argument',
            'Not a zip archive',
            'Internal error',
            'Zip archive inconsistent',
            'Can\'t remove file',
            'Entry has been deleted'
        ];

        $process = $zip->open($path);
        if ($process !== TRUE) throw new \Exception(___('Gagal mengesktrak file') . ' : ' . ($errorMessage[$process]??'An unknown error has occurred') . " {$path}");
        
        $zip->extractTo(SB . 'files' . DS . 'cache' . DS);
        $zip->close();
        @unlink($path);
    }

    /**
     * remove extracted folder
     *
     * @param string $dir
     * @return void
     */
    private function delTree($dir) {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        return rmdir($dir);
    } 
}