<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>広島県新型コロナウイルス陽性者数</title>
    <style>
    table {
        border-collapse: collapse;
        border: solid 1px black;
        /*表全体を線で囲う*/
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
    <!-- 広島県のデータベースから引用 -->
    <!-- 2021/07/28 作成          -->

    <?php
//更新日の取得
$target = "https://hiroshima.stopcovid19.jp";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $target);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$web_page = curl_exec($curl);
curl_close($curl);
$pattern = '/最終更新<\/span>(.*)<time datetime=\"(.*)" data-v-548e859e>/siU';
  if( preg_match_all($pattern, $web_page , $result) ){
    $last_updated = strtotime($result[2][0]);
    $str_last_updated=date('Y/m/d H時i分',strtotime($result[2][0]));
  }else{
    // エラーの時
    $last_updated = strtotime(date('Y/m/d'));
    $str_last_updated='';
  }

//感染状況の取得
  $pattern2 = '/<h4>感染状況<\/h4>(.*)<p\sdata-v-883a402c>(.*)<\/p>/siU';
    if( preg_match_all($pattern2, $web_page , $result2) ){
      $str_stage = $result2[2][0];
    }else{
      // エラーの時
      $str_stage='';
    }
?>


    <article>
        <h2>広島県新型コロナウイルス感染症 簡易まとめ</h2>
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
$csv = file_get_contents("https://www.pref.hiroshima.lg.jp/soshiki_file/brand/covid19/opendata/340006_hiroshima_covid19_01_patients.csv");
setlocale( LC_ALL, 'ja_JP' );
$lines = str_getcsv($csv, "\r\n");
if (preg_match('/No,/',$lines[0],$result)){
  //delimiter
  $delimiter = ",";
  $CSV_format = 'SJIS';
} else {
  //delimiter
  $delimiter = "\t";
  $CSV_format = 'SJIS';
}
foreach ($lines as $line) {
  $records[] = str_getcsv($line, $delimiter);
}
$cnt = count($lines); // 症例数は$cnt-1

$arry_column = array('No'=>0, 'examin'=>4, 'onset'=>5, 'center'=>6, 'living'=>7 ,'age'=>9);
// 0 No;
// 4 公表日;
// 5 発症日;
// 6 保健所
// 7 居住地
//9 年齢
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period = $cnt - 1; //トータルの患者数
if(empty($str_last_updated)){ //直近1週間の期間を設定
  $search_day1 = strtotime('-7 days');
} else {
  $search_day1 = strtotime(date('Y/m/d',$last_updated) . '-7 days');
}
for ($i = $cnt_total_all_period; $i>=1; $i--) {
  if ($records[$i][$arry_column['examin']]=='-') {
    //変なデータはスキップ
  } else if ($search_day1 > strtotime(str_replace('-','/',$records[$i][$arry_column['examin']]))) { //1週間前＋1日の日時まで来たら終了
    $cnt_total = $cnt_total_all_period - $i; //直近1週間の症例数を記録
    $second_index = $i; //その前の1週間の判定に使用
    break;
  } 
}
//2-1週間前のデータ
if ($records[$i][$arry_column['examin']]=='-') {
  //変なデータはスキップ
} else if(empty($str_last_updated)){ //2-1週間前の期間を設定
    $search_day2 = strtotime('-14 days');
  } else {
    $search_day2 = strtotime(date('Y/m/d',$last_updated) . '-14 days');
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
echo "<h2>広島県</h2>";
echo "<h3>" . $str_stage . "</h3>";
echo "<h3>一週間の陽性者数：" . $cnt_total . "人";
if(empty($str_last_updated)){
  echo "（" . date('n/j',strtotime('-7 days')) . "〜" . date('n/j',strtotime('-1 day')) . "）<br />";
} else {
  echo "（" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-7 days')) . "〜" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-1 day')) . "）<br />";
}
echo "10万人あたり" . sprintf('%.1f',$cnt_total/28.1) . "人, 先週比：" . (int)(($cnt_total / $cnt_total2) * 100) . "%</h3>";


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
        <a href="https://hiroshima.stopcovid19.jp"
            title="https://hiroshima.stopcovid19.jp">https://hiroshima.stopcovid19.jp</a><br />
        解析方法：
        <a href="https://github.com/poporacchi/ota-covid19-database" title="GitHub">GitHub</a>
    </article>


    <footer>
        <hr />
        <p>©️ 2021 大田記念病院感染管理室</p>
    </footer>

</body>

</html>