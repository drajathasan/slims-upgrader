<?php

if (!function_exists('selfUrl'))
{
    function selfUrl($query = [])
    {
        return $_SERVER['PHP_SELF'] .'?'. http_build_query(array_unique(array_merge($_GET, $query)));
    }
}

if (!function_exists('getLatestVersion'))
{
    function checkLatestVersion($client, $branch)
    {
        // Rolling release
        if ($branch === 'develop') return 'develop';

        // get from github api
        $latest = $client->get('/repos/slims/slims9_bulian/releases/latest');
        if ($latest->getStatusCode() != 200) throw new Exception('danger::' . $latest->getError()); // set error

        // set data
        $data = $latest->toArray();
        if (SENAYAN_VERSION_TAG === $data['tag_name']) throw new Exception('info::SLiMS anda sudah paling baru');
        
        // return latest release
        return $data['tag_name'];
    }
}

if (!function_exists('compareVersion'))
{
    function compareVersion($client, $branch)
    {
        // Comparing data with current senayan tag and inputed branch
        $compare = $client->get('/repos/slims/slims9_bulian/compare/'.SENAYAN_VERSION_TAG . '...' . $branch);
        if ($compare->getStatusCode() != 200) return ['status' => false, 'message' => $compare->getError()];

        // back to outside
        return ['status' => true, 'data' => $compare->toArray()];
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
                echo '<input type="hidden" name="id" value="' . simbio_security::xssFree($_GET['id']) . '"/>';
                echo '<input type="hidden" name="mod" value="' . simbio_security::xssFree($_GET['mod']) . '"/>';
                echo '<input type="hidden" name="from" value="' . simbio_security::xssFree(SENAYAN_VERSION_TAG) . '"/>';
                echo '<input type="hidden" name="to" value="' . simbio_security::xssFree($lastVersion) . '"/>';
                echo '<p style="font-size: 12pt">versi anda <code>'.SENAYAN_VERSION_TAG.'</code> akan di perbaharui ke <code>'.$lastVersion.'</code></p>';
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