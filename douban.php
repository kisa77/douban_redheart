<?php
/**
 * 下载豆瓣红心歌曲
 *  created:2016-11-04 12:04:05
 *  creator:kisa77
 */

$username = urlencode('*****@163.com');
$password = '';
echo 'login douban.fm...' . PHP_EOL;
exec("curl -sL https://accounts.douban.com/j/popup/login/basic -d 'source=fm&referer=https%3A%2F%2Fdouban.fm%2Fmine%2Fhearts&ck=&name={$username}&password={$password}&captcha_solution=&captcha_id=' -c cookie", $out);
sleep(1);
$loginRs = json_decode($out[0], true);
if ($loginRs['status'] == 'success' && $loginRs['message'] == 'success') {
    echo "login success..." . PHP_EOL;
} else {
    die("login fail!" . PHP_EOL);
}
echo 'download redheart songs list...' . PHP_EOL;
exec("curl -sL 'https://douban.fm/j/v2/redheart/basic?updated_time=2015-01-01+00%3A00%3A00' -b cookie -c cookie ", $output);
sleep(1);
$data = json_decode($output[0], true);
$count = count($data['songs']);
if ($count > 0) {
    echo "downlaod list success.. {$count} songs total..".PHP_EOL;
} else {
    die('download list fail!'.PHP_EOL);
}
$result = array();
foreach ($data['songs'] as $v) {
    $result[] = $v['sid'];
}
//print_R($result);
$songStr = urlencode(join('|', $result));

// 从cookie中获取ck
$d = file('cookie');
$ck = '';
foreach ($d as $row) {
    $row = str_replace("\n", '', $row);
    $cols = explode("\tck\t", $row);
    if (array_key_exists('1', $cols) && $cols[1]) {
        $ck = $cols[1];
    }
}
exec("curl -s -b cookie  https://douban.fm/j/v2/redheart/songs  -d 'sids={$songStr}&kbps=192&ck={$ck}'", $outpu2);
sleep(1);
$songs = json_decode($outpu2[0], true);
$c = 0;
foreach ($songs as $s) {
    try{
        $ext = $s['file_ext'] ? $s['file_ext'] : 'mp3';
        $title = $s['artist'] . '-' . $s['title'] . '.' . $ext;
        if (array_key_exists('url', $s) && !$s['url']) {
            echo "download   {$title} ...  fail ! {$c}/{$count}" . PHP_EOL;
            continue;
        }
        exec("curl {$s['url']} -s -o \"{$title}\"");
        usleep(200000);
        if (file_exists($title)) {
            $c++;
        }
        echo "download   {$title} ...  success ! {$c}/{$count}" . PHP_EOL;
    } catch(Exception $e) {
    }
}
