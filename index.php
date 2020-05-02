<?php
# Using CURL for speed
function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1'); # Required so SSL doesn't fail with "error:1414D172".
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Gecko/20100101 Firefox/74.0');
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


$channels_tvi = array(
    'tvi' => 'https://video-auth2.iol.pt/live_tvi/live_tvi/playlist.m3u8?wmsAuthSign=',
    'tvi24' => 'https://video-auth6.iol.pt/live_tvi24/live_tvi24/playlist.m3u8?wmsAuthSign=',
    'tvi_reality' => 'https://video-auth5.iol.pt/live_tvi_direct/live_tvi_direct/playlist.m3u8?wmsAuthSign=',
    'tvi_internacional' => 'https://video-auth8.iol.pt/live_tvi_internacional/live_tvi_internacional/playlist.m3u8?wmsAuthSign=',
    'tvi_ficcao' => 'https://video-auth6.iol.pt/live_tvi_ficcao/live_tvi_ficcao/playlist.m3u8?wmsAuthSign='
);
$channels_euronews = array(
    'euronews_en' => 'https://www.euronews.com/api/watchlive.json',
    'euronews_fr' => 'https://fr.euronews.com/api/watchlive.json',
    'euronews_de' => 'https://de.euronews.com/api/watchlive.json',
    'euronews_it' => 'https://it.euronews.com/api/watchlive.json',
    'euronews_es' => 'https://es.euronews.com/api/watchlive.json',
    'euronews_pt' => 'https://pt.euronews.com/api/watchlive.json',
    'euronews_ru' => 'https://ru.euronews.com/api/watchlive.json',
    'euronews_gr' => 'https://gr.euronews.com/api/watchlive.json',
    'euronews_hu' => 'https://hu.euronews.com/api/watchlive.json',
# 'euronews_al' => 'https://al.euronews.com/api/watchlive.json' // There is an Albanian stream but the API isn't providing the link here.
);
$channels_nowtv = array(
    'nowtv_zhibotai' => '331',
    'nowtv_xinwentai' => '332'
);
$channels_ftv = array(
    'minshi_hd' => 'FTV',
    'minshi_diyitai' => 'FMTV',
    'minshi_taiwantai' => 'FTTV',
    'minshi_xinwentai' => 'FTVNews',
    'minshi_yingjutai' => 'FTVDrama',
    'minshi_zongyitai' => 'FTVVariety',
    'minshi_lvyoutai' => 'FTVTravel'
);


$channel = $_GET['channel'];

if (array_key_exists($channel, $channels_tvi)) {
    # Get Token
    $token = file_get_contents_curl('https://services.iol.pt/matrix?userId=');
    $m3u8 = $channels_tvi[$channel] . $token . "=";
    header('Location: ' . $m3u8);
    die;
} elseif (array_key_exists($channel, $channels_euronews)) {
    $stream_info_json = file_get_contents_curl($channels_euronews[$channel]);
    $stream_info = json_decode($stream_info_json , true);
    $stream_info_url = 'https:' . $stream_info['url'];
    $m3u8_json = file_get_contents_curl($stream_info_url);
    $m3u8 = json_decode($m3u8_json, true);
    header('Location: ' . $m3u8['primary']);
    die;
} elseif (array_key_exists($channel, $channels_nowtv)) {
    $stream_info_json = file_get_contents_curl('https://d1jithvltpp1l1.cloudfront.net/getLiveURL?channelno=' . $channels_nowtv[$channel] . '&mode=prod&audioCode=&format=HLS');
    $stream_info = json_decode($stream_info_json, true);
    $m3u8 = $stream_info['asset']['hls']['adaptive']['0'];
    header('Location: ' . $m3u8);
    die;
} elseif (array_key_exists($channel, $channels_ftv)) {
    $stream_info = file_get_contents_curl('https://app.4gtv.tv/Data/GetChannelURL_Mozai.ashx?ChannelNamecallback=channelname&Type=LIVE&ChannelName=' . $channels_ftv[$channel]);
    $stream_info_json = $stream_info = json_decode(substr($stream_info, 12, -1), true);
    $m3u8 = $stream_info_json['VideoURL'];
    header('Location: ' . $m3u8);
    die;
} else {
die("Channel not provided or doesn't exist.");
}
?>
