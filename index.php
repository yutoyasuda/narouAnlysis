<?php
require_once("./phpQuery-onefile.php");
$context = stream_context_create(array('http' => array(
    'method' => 'GET',
    'header' => 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
  )));
$url = "";

//入力されたurlのデータ取得
if(isset($_POST["url"])){$url = $_POST["url"];
$source = file_get_contents($url,false,$context);
$source = mb_convert_encoding($source, "UTF-8", "auto");
$doc = phpQuery::newDocumentHTML($source)->find("p:gt(10)")->text();//本文取得
$doc = str_replace(array(" ", "　"), "", $doc);//空白を埋める
$doc = mb_substr($doc,0,9999,"UTF-8");//文字列カット
$strlen = mb_strlen($doc,'UTF-8');
$url_pre = phpQuery::newDocumentHTML($source)->find(".novel_bn:eq(0)")->find("a")->attr("href");//前話のurl取得
//総ポイント取得
$url_det = phpQuery::newDocumentHTML($source)->find("#novel_header")->find("#head_nav")->find("li:eq(1)")->find("a")->attr("href");//詳細のurl取得
$source_det = file_get_contents($url_det,false,$context);
$source_det = mb_convert_encoding($source_det, "UTF-8", "auto");
$point = phpQuery::newDocumentHTML($source_det)->find("#contents_main")->find("#noveltable2")->find("tr:eq(5)")->find("td")->text();
$point = preg_replace('/[^0-9]/', '', $point);
// //前話のデータ取得
while ($strlen <= 10000) {
  $url_pre = "https://ncode.syosetu.com".$url_pre;//url作成
  $source_pre = file_get_contents($url_pre,false,$context);
  $source_pre = mb_convert_encoding($source_pre, "UTF-8", "auto");
  $url_pre = phpQuery::newDocumentHTML($source_pre)->find(".novel_bn:eq(0)")->find("a")->attr("href");//前話のurl取得
  $doc_pre = phpQuery::newDocumentHTML($source_pre)->find("p:gt(10)")->text();
  $doc_pre = str_replace(array(" ", "　"), "", $doc_pre);
  $doc = $doc.$doc_pre;
  $strlen = mb_strlen($doc,'UTF-8');
}
$doc = mb_substr($doc,0,10000,"UTF-8");//文字列カット
$doc_array = str_split($doc,500);//文字列分割

//形態要素解析
$appid = "dj00aiZpPWlQbjZXbmpqd1JnSyZzPWNvbnN1bWVyc2VjcmV0Jng9NjY-";

function escapestring($str) {
    return htmlspecialchars($str, ENT_QUOTES);

}

if ($doc != "") {//分割分繰り返す
  $xml = "";
  $kei = 0;
  $keidou = 0;
  $kando = 0;
  $huku = 0;
  $rentai = 0;
  $setuzoku = 0;
  $settou = 0;
  $setubi = 0;
  $mei = 0;
  $dou = 0;
  $zyo = 0;
  $zyodo = 0;
  $tokushu = 0;
  $nazo = 0;
  foreach ($doc_array as $doc) {
    $url_y = "http://jlp.yahooapis.jp/MAService/V1/parse?appid=".$appid."&results=uniq";
    $url_y .= "&sentence=".urlencode($doc)."&response=pos";
    $xml  = simplexml_load_file($url_y);
    foreach ($xml->uniq_result->word_list->word as $cur) {
      switch ($cur->pos) {
        case '形容詞':
            $kei += (int)$cur->count;
          break;
        case '形容動詞':
            $keidou += (int)$cur->count;
          break;
        case '感動詞':
            $kando += (int)$cur->count;
          break;
        case '副詞':
            $huku += (int)$cur->count;
          break;
        case '連体詞':
            $rentai += (int)$cur->count;
          break;
        case '接続詞':
            $setuzoku += (int)$cur->count;
          break;
        case '接頭辞':
            $settou += (int)$cur->count;
          break;
        case '接尾辞':
            $setubi += (int)$cur->count;
          break;
        case '名詞':
            $mei += (int)$cur->count;
          break;
        case '動詞':
            $dou += (int)$cur->count;
          break;
        case '助詞':
            $zyo += (int)$cur->count;
          break;
        case '助動詞':
            $zyodo += (int)$cur->count;
          break;
        case '特殊':
            $tokushu += (int)$cur->count;
          break;
        default:
            $nazo ++;
          break;
      }
    }
  }
}
echo (int)$point." ".$mei." ".$dou." ".$kei." ".$keidou." ".$huku." ".$kando." ".$rentai." ".$setuzoku." ".$setubi." ".$settou." ".$zyo." ".$zyodo." ".$tokushu." ".$nazo;
}
?>
<html><head>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<title>テキスト解析デモ - 日本語形態素解析</title>
</head>
<body>
<form name="myForm" method="post" action="">
<input type="text" name="url">
<input type="submit" name="exec" value="解析">
</form>
</body></html>
