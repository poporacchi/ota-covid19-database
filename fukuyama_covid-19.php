<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
<title>福山市新型コロナウイルス陽性者数</title>
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
<!-- 福山市のデータベースから引用 -->
<!-- 2021/07/26 作成          -->

<?php
//更新日の取得
$target = "https://data.city.fukuyama.hiroshima.jp/dataset/covid19_information";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $target);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$web_page = curl_exec($curl);
curl_close($curl);
$pattern = '/<span class=\"automatic-local-datetime\" data-datetime=\"(.*)">/siU';
  if( preg_match_all($pattern, $web_page , $result) ){
    $last_updated = strtotime($result[1][0] . "+9 hours");
    $str_last_updated=date('Y/m/d H時i分',strtotime($result[1][0]));
  }else{
    // エラーの時
    $last_updated = strtotime(date('Y/m/d'));
    $str_last_updated='';
  }
?>
<article>
<h2>福山市新型コロナウイルス感染症 簡易まとめ</h2>
<div style="text-align:right;">
  <?php
  if(empty($str_last_updated)){

  } else {
    echo "最終更新日時：" . $str_last_updated;
  }
   ?>
</div>

<div class="base">
<div class="upper_base">
  <iframe width="560" height="500" src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/details-of-confirmed-cases?embed=true" frameborder="0"></iframe>
  <iframe width="560" height="660" src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/number-of-confirmed-cases?embed=true" frameborder="0"></iframe>
</div>

<div class="lower_base">
<div>
<br />
<?php

//福山市のHPからCSVデータ取得
//CSVファイルがtab区切りかつSJISだったり、カンマ区切りかつUTF-8だったりするため、その判別が必要
$csv = file_get_contents("https://data.city.fukuyama.hiroshima.jp/dataset/568687d8-6dc7-4a70-9101-98ff2dda5b28/resource/d0c5baf8-5061-484c-836a-994b322603d6/download/342076_fukuyama_covid19_04_patients.csv");
setlocale( LC_ALL, 'ja_JP' );
$lines = str_getcsv($csv, "\r\n");
if (preg_match('/No,/',$lines[0],$result)){
  //delimiterがカンマ区切りは文字コードはUTF-8と思われる。
  $delimiter = ",";
  $CSV_format = 'UTF-8';
} else {
  //delimiterがカンマ区切りでない（タブ区切り）は文字コードはSJISと思われる。
  $delimiter = "\t";
  $CSV_format = 'SJIS';
}
foreach ($lines as $line) {
  $records[] = str_getcsv($line, $delimiter);
}
$cnt = count($records); // 症例数は$cnt-1


$arry_column = [0, 5, 6, 7, 8 ,12 ,15];
// 0 No;
// 5 発症日;
// 6 確定日;
// 7 居住地
// 8 年齢
//12 症状
//15 コメント
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period = $cnt - 1; //トータルの患者数
for ($i = $cnt_total_all_period; $i>=1; $i--) {
  if ($CSV_format == 'SJIS') { //コメント行の取得
    $comment=mb_convert_encoding($records[$i][15], "utf-8", "SJIS");
  } else {
    $comment=$records[$i][15];
  }
  if(empty($str_last_updated)){ //直近1週間の期間を設定
    $str_search_day1 = strtotime('-7 days');
  } else {
    $str_search_day1 = strtotime(date('Y/m/d',$last_updated) . '-7 days');
  }
  if ($str_search_day1 > strtotime($records[$i][6])) { //1週間前＋1日の日時まで来たら終了
    $cnt_total = $cnt_total_all_period - $i; //直近1週間の症例数を記録
    $second_index = $i; //その前の1週間の判定に使用
    break;
  } else { //濃厚接触者の判定
    if ( preg_match('/濃厚接触者/', $comment, $matches) ) {
    } else if ( preg_match ('/の接触者/', $comment, $matches) ){

    } else if ( preg_match ('/他事例との関連調査中/', $comment, $matches) ){
      $cnt_unknown++;
    } else {
      $cnt_unknown++;
    }
  }
}
$unknown_rate = (int)(($cnt_unknown / $cnt_total) * 100); //経路不明の患者の割合を計算




//2-1週間前のデータ
for ($i = $second_index; $i>=1; $i--) { //1週間前より前の患者のカウント
  if ($CSV_format == 'SJIS') { //コメント行の取得
    $comment=mb_convert_encoding($records[$i][15], "utf-8", "SJIS");
  } else {
    $comment=$records[$i][15];
  }
  if(empty($str_last_updated)){ //2-1週間前の期間を設定
    $str_search_day2 = strtotime('-14 days');
  } else {
    $str_search_day2 = strtotime(date('Y/m/d',$last_updated) . '-14 days');
  }
  if ($str_search_day2 > strtotime($records[$i][6])) { //2週間前＋1日の日時まで来たら終了
    $cnt_total2 = $second_index - $i; //2-1週間前の症例数を記録
    break;
  } else { //濃厚接触者の判定
    if ( preg_match('/濃厚接触者/', $comment, $matches) ) {

    } else if ( preg_match ('/の接触者/', $comment, $matches) ){

    } else if ( preg_match ('/他事例との関連調査中/', $comment, $matches) ){
      
    } else {

    }
  }
}
?>
<div class="message">
<?php
echo "<h3>一週間の陽性者数：" . $cnt_total . "人";
if(empty($str_last_updated)){
  echo "（" . date('n/j',strtotime('-7 days')) . "〜" . date('n/j',strtotime('-1 day')) . "）<br />";
} else {
  echo "（" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-7 days')) . "〜" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-1 day')) . "）<br />";
}

echo "（うち経路不明：" . $cnt_unknown . "人, " . $unknown_rate. "%）<br />";
echo "10万人あたり" . sprintf('%.1f',$cnt_total/4.6) . "人, 先週比：" . (int)(($cnt_total / $cnt_total2) * 100) . "%</h3>";
if(empty($str_last_updated)){
  echo "市のデータは毎日夕方に更新されます。<br />";
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
  echo "<th>経路不明</th>";
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
    $examday=$records[$i][6];
    if (strtotime('-30 days') > strtotime($examday)) {

    } else {
      echo "<tr>";
      echo "<td>";
      if ($CSV_format == 'SJIS') {
        $comment=mb_convert_encoding($records[$i][15], "utf-8", "SJIS");
      } else {
        $comment=$records[$i][15];
      }
      // 濃厚接触者の判定
      if ( preg_match('/濃厚接触者/', $comment, $matches) ) {
      } else if ( preg_match ('/の接触者/', $comment, $matches) ){

      } else if ( preg_match ('/他事例との関連調査中/', $comment, $matches) ){
        if ( preg_match('/県外往来等あり/', $comment, $matches) ) {
          echo "△"; // 経路不明
        } else {
          echo "○"; // 経路不明
        }

      } else {
        echo "○"; // 経路不明
      }
      echo "</td>";
      foreach ($arry_column as $col) {
        echo "<td>";
        if ($CSV_format == 'SJIS') {
          $td = mb_convert_encoding($records[$i][$col], "utf-8", "SJIS");
        } else {
          $td = $records[$i][$col];
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
  <a href="https://data.city.fukuyama.hiroshima.jp/dataset/covid19_patients/resource/d0c5baf8-5061-484c-836a-994b322603d6" title="https://data.city.fukuyama.hiroshima.jp/dataset/covid19_patients/resource/d0c5baf8-5061-484c-836a-994b322603d6">https://data.city.fukuyama.hiroshima.jp/dataset/covid19_patients/resource/d0c5baf8-5061-484c-836a-994b322603d6</a>

</article>


<footer>
  <hr />
  <p>©️ 2021 大田記念病院感染管理室</p>
</footer>

</body>
</html>
