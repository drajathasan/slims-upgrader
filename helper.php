<?php

if (!function_exists('selfUrl'))
{
    function selfUrl($query = [])
    {
        return $_SERVER['PHP_SELF'] .'?'. http_build_query(array_unique(array_merge($_GET, $query)));
    }
}

if (!function_exists('generateTemplate'))
{
    function generateTemplate($type, $data = [])
    {
        extract($data);
        switch ($type) {
            case 'newUpdate':
                echo '<form action="' . selfUrl(['upgrade' => 'yes', 'from' => SENAYAN_VERSION_TAG, 'to' => $lastVersion]) . '">';
                echo '<h3>Terdapat pembaharuan di cabang ' . strip_tags($_GET['branch']) . '</h3>';
                echo '<input type="hidden" name="id" value="' . xssFree($_GET['id']) . '"/>';
                echo '<input type="hidden" name="mod" value="' . xssFree($_GET['mod']) . '"/>';
                echo '<input type="hidden" name="from" value="' . xssFree(SENAYAN_VERSION_TAG) . '"/>';
                echo '<input type="hidden" name="to" value="' . xssFree($lastVersion) . '"/>';
                echo '<input type="hidden" name="branch" value="' . xssFree($_GET['branch']) . '"/>';
                echo '<p style="font-size: 12pt">versi anda <code>'.SENAYAN_VERSION_TAG.'</code> akan diperbaharui ke <code>'.$lastVersion.'</code></p>';
                echo '<input type="submit" name="upgrade" class="btn btn-primary" value="Tingkatkan"/>';
                echo '<table class="table my-2">';
                echo '<tbody>';
                $number = 1;
                foreach ($result['data']['commits'] as $detail) {
                    extract($detail['commit']);
                    extract($detail['author']);
                    $message = ucwords($message);
                    $date = \Carbon\Carbon::parse($author['date'])->locale('id')->isoFormat('dddd, Do MMMM YYYY');
                    echo <<<HTML
                    <tr>
                        <td style="width: 5px">{$number}</td>
                        <td>
                            <h6>{$message}</h6>
                            <div class="d-flex flex-row">
                                <span class="text-muted">Waktu pembaharuan : </span>
                                <strong>{$date}</strong>
                            </div>
                        </td>
                    </tr>
                    HTML;
                    $number++;
                }
                echo '</tbody>';
                echo '</table>';
                echo '<button class="btn btn-primary">Tingkatkan</button>';
                echo '</form>';
                break;
            
            default:
                echo <<<HTML
                    <div class="alert alert-{$type}">
                        <strong>{$message}</strong>
                    </div>
                HTML;
                break;
        }
    }
}

if (!function_exists('xssFree'))
{
    function xssFree($str_char)
    {
        return str_replace(['\'', '"'], '', strip_tags($str_char));
    }
}