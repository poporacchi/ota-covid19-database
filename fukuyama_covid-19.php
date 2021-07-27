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
<h2>福山市新型コロナウイルス感染症 簡易まとめ</h2>
<article>


<div class="base">
<div class="upper_base">
  <iframe width="560" height="500" src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/details-of-confirmed-cases?embed=true" frameborder="0"></iframe>
  <iframe width="560" height="660" src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/number-of-confirmed-cases?embed=true" frameborder="0"></iframe>
</div>

<div class="lower_base">
<div>
<br />
<?php
$csv = file_get_contents("https://data.city.fukuyama.hiroshima.jp/dataset/568687d8-6dc7-4a70-9101-98ff2dda5b28/resource/d0c5baf8-5061-484c-836a-994b322603d6/download/342076_fukuyama_covid19_01_patients.csv");
setlocale( LC_ALL, 'ja_JP' );
//$records[] = str_getcsv($csv,',','"');
$lines = str_getcsv($csv, "\r\n");
foreach ($lines as $line) {
  $records[] = str_getcsv($line);
}
$cnt = count($records); // 症例数は$cnt-2

$arry_column = [0, 5, 6, 7, 8 ,12 ,15];
// 0 No;
// 5 発症日;
// 6 確定日;
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period = $cnt - 2;
for ($i = $cnt_total_all_period; $i>=1; $i--) {
  //echo "i: " . $i . ", No: " . $records[$i][0] . ", " . $records[$i][6] . ", " . $records[$i][15] . "<br />";
  if (strtotime('-8 days') > strtotime($records[$i][6])) {
    $cnt_total = $cnt_total_all_period - $i; //直近1週間の症例数を記録
    $end_index = $i-1;
    break;
  } else {
    if ( preg_match('/濃厚接触者/', $records[$i][15], $matches) ) {
    } else if ( preg_match ('/の接触者/', $records[$i][15], $matches) ){

    } else if ( preg_match ('/他事例との関連調査中/', $records[$i][15], $matches) ){
      $cnt_unknown++;
    } else {
      $cnt_unknown++;
    }
  }
}
$unknown_rate = (int)(($cnt_unknown / $cnt_total) * 100);




//1週間のデータ(-14 to -8)
for ($i = $end_index; $i>=1; $i--) {
  //echo "i: " . $i . ", No: " . $records[$i][0] . ", " . $records[$i][6] . ", " . $records[$i][15];
  if (strtotime('-14 days') > strtotime($records[$i][6])) {
    $cnt_total2 = $end_index - $i;
    break;
  } else {
    if ( preg_match('/濃厚接触者/', $records[$i][15], $matches) ) {

    } else if ( preg_match ('/の接触者/', $records[$i][15], $matches) ){

    } else if ( preg_match ('/他事例との関連調査中/', $records[$i][15], $matches) ){
      $cnt_unknown2++;
    } else {
      $cnt_unknown2++;
    }
  }
}
$unknown_rate = (int)(($cnt_unknown2 / $cnt_total2) * 100);

?>
<div class="message">
<?php
echo "<h3>一週間の陽性者数：" . $cnt_total . "人";
echo "（" . date('n/j',strtotime('-7 days')) . "〜" . date('n/j',strtotime('-1 day')) . "）<br />";
echo "（うち経路不明：" . $cnt_unknown . "人, " . $unknown_rate. "%）<br />";
echo "10万人あたり" . sprintf('%.1f',$cnt_total/4.6) . "人, 先週比：" . (int)(($cnt_total / $cnt_total2) * 100) . "%</h3>";
echo "市のデータは毎日夕方に更新されます。<br />";
?>
</div>
<?php


echo "直近1ヶ月の陽性者リスト<br />";
//1ヶ月のリスト取得
echo "<table border=1>";
  echo "<tr>";
  echo "<td>経路不明</td>";
foreach ($arry_column as $col) {
  echo "<td>";
  echo $records[0][$col];
  echo "</td>";
}
  echo "</tr>";

  for ($i = $cnt-2; $i>=1; $i--) {

    if (strtotime('-30 days') > strtotime($records[$i][6])) {

    } else {
      echo "<tr>";
      echo "<td>";
      if ( preg_match('/濃厚接触者/', $records[$i][15], $matches) ) {
      } else if ( preg_match ('/の接触者/', $records[$i][15], $matches) ){

      } else if ( preg_match ('/他事例との関連調査中/', $records[$i][15], $matches) ){
        if ( preg_match('/県外往来等あり/', $records[$i][15], $matches) ) {
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
        echo $records[$i][$col];
        echo "</td>";
      }
      $arr_examday[] = $records[$i][6];
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
