<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
<title>岡山県新型コロナウイルス陽性者数</title>
<style>
table {
  border-collapse: collapse;
  border: solid 1px black;/*表全体を線で囲う*/
}
.base {
  display: flex;
  flex-direction: column;
}
.upper_base {
  display: flex;
  flex-direction: row;
  height: 680px;
}
.lower_base {
  display: flex;
  flex-direction: row;
}
.message {
  margin: 10px;
  padding: 5px;
  width: 400px;
  text-align: center;
  border: solid black medium;
}
</style>
</head>
<body>
<!-- 岡山県のデータベースから引用 -->
<!-- 2021/07/28 作成          -->

<?php
//更新日の取得
$target = "http://www.okayama-opendata.jp/opendata/ga130PreAction.action?resourceName=感染者詳細情報&keyTitle=d9c4776db7f09fff161953a2aaf03b80a9abad48&title=新型コロナウイルス感染症に関するデータ（岡山県）&isParam=1&resourceId=d021c012-297e-4ea9-bffa-cf55741884d1&licenseTitle=クリエイティブ・コモンズ+表示&datasetId=e6b3c1d2-2f1f-4735-b36e-e45d36d94761&checkFieldFormat=CSV";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $target);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$web_page = curl_exec($curl);
curl_close($curl);
$pattern = '/<th\sscope=\"row\">最終更新<\/th>(.*)<td>(.*)<\/td>/siU';
  if( preg_match_all($pattern, $web_page , $result) ){
    $str_date = $result[2][0];
    $str_date = str_replace('年','/',$str_date);
    $str_date = str_replace('月','/',$str_date);
    $str_date = str_replace('日','',$str_date);
    $last_updated = strtotime($str_date);
    $str_last_updated=$str_date;
  }else{
    // エラーの時
    $last_updated = strtotime(date('Y/m/d'));
    $str_last_updated='';
  }

//感染状況の取得
$target2 = "https://www.pref.okayama.jp/page/724270.html#01-kennaijoukyou";
$curl2 = curl_init();
curl_setopt($curl2, CURLOPT_URL, $target2);
curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
$web_page2 = curl_exec($curl2);
curl_close($curl2);
$pattern2 = '/<strong>総合的判断：(.*)<\/strong>/siU';
if( preg_match_all($pattern2, $web_page2 , $result2) ){
  $str_stage = $result2[1][0];
}else{
  // エラーの時
  $str_stage='';
}
?>


<article>
<h2>岡山県新型コロナウイルス感染症 簡易まとめ</h2>
<div style="text-align:right;">
  <?php
  if(empty($str_last_updated)){

  } else {
    echo "最終更新日時：" . $str_last_updated;
  }
   ?>
</div>

<div class="base">

<div class="lower_base">
<div>
<br />
<?php

//広島県のHPからCSVデータ取得
//CSVファイルがtab区切りかつSJISだったり、カンマ区切りかつUTF-8だったりするため、その判別が必要
$csv = file_get_contents("http://www.okayama-opendata.jp/ckan/dataset/e6b3c1d2-2f1f-4735-b36e-e45d36d94761/resource/d021c012-297e-4ea9-bffa-cf55741884d1/download/kansenshashousaijouhou.csv");
setlocale( LC_ALL, 'ja_JP' );
$lines = str_getcsv($csv, "\r\n");
if (preg_match('/330001,/',$lines[1],$result)){
  //delimiter
  $delimiter = ",";
  $CSV_format = 'SJIS';
} else {$
  //delimiter
  $delimiter = "\t";
  $CSV_format = 'SJIS';
}
foreach ($lines as $line) {
  $records[] = str_getcsv($line, $delimiter);
}
$cnt = count($lines); // 症例数は$cnt-1

$arry_column = array('examin'=>3, 'living'=>5 ,'age'=>6);
// 0 No;
// 3 公表日
// 5 居住地
// 6 年齢
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period = $cnt - 1; //トータルの患者数
if(empty($str_last_updated)){ //直近1週間の期間を設定
  $search_day1 = strtotime('-6 days');
} else {
  $search_day1 = strtotime(date('Y/m/d',$last_updated) . '-6 days');
}
for ($i = $cnt_total_all_period; $i>=1; $i--) {
  if ($search_day1 > strtotime(str_replace('-','/',$records[$i][$arry_column['examin']]))) { //1週間前＋1日の日時まで来たら終了
    $cnt_total = $cnt_total_all_period - $i; //直近1週間の症例数を記録
    $second_index = $i; //その前の1週間の判定に使用
    break;
  } 
}
//2-1週間前のデータ
if(empty($str_last_updated)){ //2-1週間前の期間を設定
    $search_day2 = strtotime('-13 days');
  } else {
    $search_day2 = strtotime(date('Y/m/d',$last_updated) . '-13 days');
  }
for ($i = $second_index; $i>=1; $i--) { //1週間前より前の患者のカウント
  if ($search_day2 > strtotime(str_replace('-','/',$records[$i][$arry_column['examin']]))) { //2週間前＋1日の日時まで来たら終了
    $cnt_total2 = $second_index - $i; //2-1週間前の症例数を記録
    break;
  }
}
?>
<div class="message">
<?php
echo "<h2>岡山県</h2>";
echo "<h3>" . $str_stage . "</h3>";
echo "<h3>一週間の陽性者数：" . $cnt_total . "人";
if(empty($str_last_updated)){
  echo "（" . date('n/j',strtotime('-6 days')) . "〜" . date('n/j') . "）<br />";
} else {
  echo "（" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-6 days')) . "〜" . date('n/j',strtotime($last_updated)) . "）<br />";
}

echo "（うち経路不明：" . $cnt_unknown . "人, " . $unknown_rate. "%）<br />";
echo "10万人あたり" . sprintf('%.1f',$cnt_total/19) . "人, 先週比：" . (int)(($cnt_total / $cnt_total2) * 100) . "%</h3>";
if(empty($str_last_updated)){
    echo "岡山のデータは毎日夕方に更新されます。<br />";
  } else {
    echo "最終更新日時：" . $str_last_updated;
  }

?>
</div>
<?php


echo "直近1ヶ月の陽性者リスト<br />";
//1ヶ月のリスト取得
echo "<table border=1>";
  echo "<tr>";
foreach ($arry_column as $col) {
  echo "<th>";
  if ($CSV_format == 'SJIS') { //行の先頭列の設定
    $th_label=mb_convert_encoding($records[0][$col], "utf-8", "SJIS");
  } else {
    $th_label=$records[0][$col];
  }
  echo $th_label;
  echo "</th>";
}
  echo "</tr>";

  for ($i = $cnt_total_all_period; $i>=1; $i--) {
    $examday=$records[$i][$arry_column['examin']];
    if (strtotime('-30 days') > strtotime($examday)) {

    } else {
      echo "<tr>";
      foreach ($arry_column as $col) {
        echo "<td>";
        if ($CSV_format == 'SJIS') {
          $td = mb_convert_encoding($records[$i][$col], "utf-8", "SJIS");
          if ($col == 4) {
            $td = str_replace('-','/',$td);
          } else if ($col == 5) {
            $td = str_replace('-','/',$td);
          }
        } else {
          $td = $records[$i][$col];
          if ($col == 4) {
            $td = str_replace('-','/',$td);
          } else if ($col == 5) {
            $td = str_replace('-','/',$td);
          }
        }
        echo $td;
        echo "</td>";
      }
      $arr_examday[] = $examday;
      echo "</tr>";
    }

  }

  echo "</table>";
  $arr_cnt_pt_by_day=array_count_values($arr_examday);
?>

</div>
</div>
</div>
  元データ： <br />
  <a href="https://www.pref.okayama.jp/page/724270.html#21-kanzya-syousai" title="https://www.pref.okayama.jp/page/724270.html#21-kanzya-syousai">https://www.pref.okayama.jp/page/724270.html#21-kanzya-syousai</a><br />
  解析方法：
  <a href="https://github.com/poporacchi/ota-covid19-database" title="GitHub">GitHub</a>
</article>


<footer>
  <hr />
  <p>©️ 2021 大田記念病院感染管理室</p>
</footer>

</body>
</html>
