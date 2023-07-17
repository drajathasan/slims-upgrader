<?php
use Drajathasan\SlimsUpgrader\Html;

if (!function_exists('selfUrl'))
{
    function selfUrl($query = [])
    {
        foreach($query as $key => $value) {
            if (isset($_GET[$key]) && $value == 'unset') unset($_GET[$key]);
            else if (isset($_GET[$key])) $_GET[$key] = $value;
        }

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
                $number = 1;
                $tr = '';
                foreach ($result['data']['commits'] as $detail) {
                    extract($detail['commit']);
                    extract($detail['author']);
                    $message = ucwords($message);
                    $date = \Carbon\Carbon::parse($author['date'])->locale('id')->isoFormat('dddd, Do MMMM YYYY');
                    $tr .= Html::tr(
                        Html::td($number, ['style' => 'width: 5px']) . 
                        Html::td(
                            Html::h6($message) . 
                            Html::div(
                                Html::span(___('Waktu pembaharuan : '), ['class' => 'text-muted']) . 
                                Html::strong($date),
                            ['class' => 'd-flex flex-row'])
                        )
                    );
                    $number++;
                }
                $table = Html::table(
                    Html::tbody($tr),
                ['class' => 'table my-2']);

                echo Html::form(
                    Html::h3('Terdapat pembaharuan di cabang ' . strip_tags($_GET['branch'])) . 
                    Html::input(['type' => 'hidden', 'name' => 'id', 'value' => $_GET['id']]) .
                    Html::input(['type' => 'hidden', 'name' => 'mod', 'value' => $_GET['mod']]) .
                    Html::input(['type' => 'hidden', 'name' => 'from', 'value' => SENAYAN_VERSION_TAG]) .
                    Html::input(['type' => 'hidden', 'name' => 'to', 'value' => $lastVersion]) .
                    Html::input(['type' => 'hidden', 'name' => 'branch', 'value' => $_GET['branch']]) .
                    Html::p(
                        'versi anda ' . Html::code(SENAYAN_VERSION_TAG) . ' akan diperbaharui ke ' . Html::code($lastVersion),
                    ['style' => 'font-size: 12pt']) . 
                    Html::div(
                        Html::strong('Peringatan', ['style' => 'font-size: 14pt']) .
                        Html::br() . 
                        Html::p('sebelum anda meningkatan versi SLiMS anda, pastikan anda sudah melakukan <i>backup</i> database dan source code SLiMS anda saat ini. Keberhasilan proses upgrade bergantung pada koneksi Internet dan stabilitas server anda.', ['style' => 'font-size: 12pt']),
                    ['class' => 'alert alert-warning']) . 
                    Html::input(['type' => 'submit', 'name' => 'upgrade', 'value' => 'Tingkatkan', 'class' => 'btn btn-primary']) . 
                    $table,
                ['action' => selfUrl(['upgrade' => 'yes', 'from' => SENAYAN_VERSION_TAG, 'to' => $lastVersion])]);
                break;
            
            default:
                echo Html::div(
                    Html::strong($message),
                ['class' => 'alert alert-' . $type]);
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

if (!function_exists('___'))
{
    function ___($message)
    {
        global $sysconf;
        include_once __DIR__ . '/translate.php';
        return isset($translate[$sysconf['default_lang']]) ? ($translate[$sysconf['default_lang']][$message]??$message) : $message;
    }
}