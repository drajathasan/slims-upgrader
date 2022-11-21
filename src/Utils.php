<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-19 17:37:00
 * @modify date 2022-11-21 15:11:51
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

trait Utils
{
    public function diffParser(string $branch, string $filePath)
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
                ($this->cache[$index]['download_status'] ? 'Sukses mengupdate : ' . $this->cache[$index]['to'] : 'Gagal : ' . $this->cache[$index]['error_message']) . 
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
        $percent = $currentStep == 0 && $totalStep == 0 ? 0 : round(($currentStep / $totalStep) * 100);
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

    public function logOut()
    {
        $url = AWB . 'logout.php';
        echo <<<HTML
        <script>setTimeout(() => top.location.href = '{$url}', 5000)</script>
        HTML;
    }
}