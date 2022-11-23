<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-23 12:53:03
 * @modify date 2022-11-23 16:24:45
 * @license GPLv3
 * @desc [description]
 */

namespace Drajathasan\SlimsUpgrader;

class Translate
{
    public static function getFile($dir, $exception = ['.','..','.git'])
    {
        $listOfDir = [];
        foreach (array_diff(scandir($dir), $exception) as $content) {
            $path = $dir . $content;
            if (is_dir($path)) {
                $listOfDir[] = $path;
                foreach (self::getFile($path . DS) as $innerContent) 
                    $listOfDir[] = $innerContent;
            }
            else
            {
                $listOfDir[] = $path;
            }
        }
    
        return $listOfDir;
    }

    public static function getTranslateString(array $files)
    {
        $matchString = [];
        foreach ($files as $file) {
            if (is_dir($file)) continue;
            if (preg_match_all('/(?<=\_\_\_\()(.*?)(?=\))/i', file_get_contents($file), $match));
            
            if (count($match)) 
            {
                $matchString[basename($file)] = array_unique($match[0]);
            }
        }

        return $matchString;
    }

    public static function makeTranslate(string $lang, array $matchStrings)
    {
        $path = __DIR__ . '/../lang/' . $lang . '.php';
        $prefix = '<?php' . PHP_EOL .
        '$translate[\'' . $lang . '\'] = [' . PHP_EOL;

        $currentContent = file_exists($path) ? file_get_contents($path) : '';
        $content = '';
        foreach ($matchStrings as $file => $detailString) {
            if (count($detailString) > 0 && !strpos($currentContent, $file)) $content .= "      // $file " . PHP_EOL;
            foreach ($detailString as $string) {
                if (!strpos($currentContent, $string) && trim($string) != '$message')
                {
                    $string = "'" . trim($string, '\'') . "'";
                    $content .= "      $string => $string," . PHP_EOL;   
                }
            }
        }
        $suffix = '];' . PHP_EOL;
        
        if (empty($currentContent)) 
        {
            file_put_contents($path, $prefix . trim($content, ',') . $suffix);
        }
        else
        {
            file_put_contents($path, str_replace($suffix,  trim($content, ',') . $suffix, $currentContent));
        }

    }
}