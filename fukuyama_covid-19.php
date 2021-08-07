<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>福山市新型コロナウイルス陽性者数</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
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
$pattern = '/<span\sclass=\"automatic-local-datetime\" data-datetime=\"(.*)">/siU';
  if( preg_match_all($pattern, $web_page , $result) ){
    $last_updated = strtotime($result[1][0]);
    $str_last_updated=date('Y/n/j H時i分',strtotime($result[1][0]));
  }else{
    // エラーの時
    $last_updated = strtotime(date('Y/n/j'));
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
                <iframe width="560" height="500"
                    src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/details-of-confirmed-cases?embed=true"
                    frameborder="0"></iframe>
                <iframe width="560" height="660"
                    src="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/cards/number-of-confirmed-cases?embed=true"
                    frameborder="0"></iframe>
            </div>
            <div class="lower_base">
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

$arry_column = array('No'=>0, 'onset'=>5, 'examin'=>6, 'living'=>7, 'age'=>8 ,'symptom'=>12 ,'comment'=>15);
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
if(empty($str_last_updated)){ //直近1週間の期間を設定
  $search_day1 = strtotime('-7 days');
} else {
  $search_day1 = strtotime(date('Y/m/d',$last_updated) . '-7 days');
}
//症状の累計
$arry_column_symptom = array('fever'=>0, 'cough'=>1, 'stuffy'=>2, 'nasal'=>3, 'throat'=>4 ,'headache'=>5, 'fatigue'=>6, 'diarrhea'=>7, 'muscle'=>8, 'arthralgia'=>9, 'nosymptom'=>10);
$arry_key_symptom = array_keys($arry_column_symptom); //キー名の配列

$cnt_symptom = array();
for ($i=0;$i<count($arry_key_symptom);$i++){
  $cnt_symptom[$arry_key_symptom[$i]]=0;
}
for ($i = $cnt_total_all_period; $i>=1; $i--) {
  if ($CSV_format == 'SJIS') { //コメント行の取得
    $comment=mb_convert_encoding($records[$i][$arry_column['comment']], "utf-8", "SJIS");
    $symptom=mb_convert_encoding($records[$i][$arry_column['symptom']], "utf-8", "SJIS");
  } else {
    $comment=$records[$i][$arry_column['comment']];
    $symptom=$records[$i][$arry_column['symptom']];
  }
  if ($search_day1 > strtotime($records[$i][$arry_column['examin']])) { //1週間前＋1日の日時まで来たら終了
    $cnt_total = $cnt_total_all_period - $i; //直近1週間の症例数を記録
    $second_index = $i; //その前の1週間の判定に使用
    break;
  } else { 
    
    if (preg_match('/熱/',$symptom,$result)){
      $cnt_symptom['fever']++;
    }
    if (preg_match('/咳/',$symptom,$result)){
      $cnt_symptom['cough']++;
    }
    if (preg_match('/息苦しさ/',$symptom,$result)){
      $cnt_symptom['stuffy']++;
    }
    if (preg_match('/呼吸苦/',$symptom,$result)){
      $cnt_symptom['stuffy']++;
    }
    if (preg_match('/鼻/',$symptom,$result)){
      $cnt_symptom['nasal']++;
    }
    if (preg_match('/咽頭痛/',$symptom,$result)){
      $cnt_symptom['throat']++;
    }
    if (preg_match('/頭痛/',$symptom,$result)){
      $cnt_symptom['headache']++;
    }
    if (preg_match('/倦怠感/',$symptom,$result)){
      $cnt_symptom['fatigue']++;
    }
    if (preg_match('/下痢/',$symptom,$result)){
      $cnt_symptom['diarrhea']++;
    }
    if (preg_match('/筋肉痛/',$symptom,$result)){
      $cnt_symptom['muscle']++;
    }
    if (preg_match('/関節/',$symptom,$result)){
      $cnt_symptom['arthralgia']++;
    }
    if (preg_match('/なし/',$symptom,$result)){
      $cnt_symptom['nosymptom']++;
    }
    //濃厚接触者の判定
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
if(empty($str_last_updated)){ //2-1週間前の期間を設定
  $search_day2 = strtotime('-14 days');
} else {
  $search_day2 = strtotime(date('Y/m/d',$last_updated) . '-14 days');
}
for ($i = $second_index; $i>=1; $i--) { //1週間前より前の患者のカウント
  if ($CSV_format == 'SJIS') { //コメント行の取得
    $comment=mb_convert_encoding($records[$i][$arry_column['comment']], "utf-8", "SJIS");
  } else {
    $comment=$records[$i][$arry_column['comment']];
  }
  if ($search_day2 > strtotime($records[$i][$arry_column['examin']])) { //2週間前＋1日の日時まで来たら終了
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
                    <h2><a href="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/">福山市</a></h2>
                    <?php

echo "<h3>福山市陽性者数：" . $cnt_total . "人/週";
if(empty($str_last_updated)){
  echo "（" . date('n/j',strtotime('-7 days')) . "〜" . date('n/j',strtotime('-1 day')) . "）<br />";
} else {
  echo "（" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-7 days')) . "〜" . date('n/j',strtotime(date('Y/m/d',$last_updated) . '-1 day')) . "）<br />";
}
echo "（うち経路不明：" . $cnt_unknown . "人, " . $unknown_rate. "%）<br />";
echo "10万人あたり" . sprintf('%.1f',$cnt_total/4.6) . "人, 先週比：" . (int)(($cnt_total / $cnt_total2) * 100) . "%</h3>";

//症状を降順に並び変える
echo "直近１週間の陽性者の症状（降順）<br />";
arsort($cnt_symptom);
foreach ($cnt_symptom as $key => $count){
  if ($count != 0) {
    if ($key=='fever') {
      $str_symptom = '発熱';
    } else if ($key=='headache') {
      $str_symptom = '頭痛';
    } else if ($key=='cough') {
      $str_symptom = '咳';
    } else if ($key=='throat') {
      $str_symptom = '咽頭痛';
    } else if ($key=='fatigue') {
      $str_symptom = '倦怠感';
    } else if ($key=='nasal') {
      $str_symptom = '鼻汁・鼻閉';
    } else if ($key=='nosymptom') {
      $str_symptom = '無症状';
    } else if ($key=='stuffy') {
      $str_symptom = '呼吸苦';
    } else if ($key=='muscle') {
      $str_symptom = '筋肉痛';
    } else if ($key=='arthralgia') {
      $str_symptom = '関節痛';
    } else if ($key=='diarrhea') {
      $str_symptom = '下痢';
    }  
    echo $str_symptom . " (" . sprintf('%.1f',$count/$cnt_total*100) . "%)<br />";
  }
}
echo "<br />";
if(empty($str_last_updated)){
  echo "市のデータは毎日夕方に更新されます。<br />";
} else {
  echo "更新日時：" . $str_last_updated;
}

?>
                </div>
                <?php
//広島県の更新日の取得
$target_hiroshima = "https://hiroshima.stopcovid19.jp";
$curl_hiroshima = curl_init();
curl_setopt($curl_hiroshima, CURLOPT_URL, $target_hiroshima);
curl_setopt($curl_hiroshima, CURLOPT_RETURNTRANSFER, true);
$web_page_hiroshima = curl_exec($curl_hiroshima);
curl_close($curl_hiroshima);
$pattern_hiroshima = '/最終更新<\/span>(.*)<time datetime=\"(.*)" data-v-548e859e>/siU';
  if( preg_match_all($pattern_hiroshima, $web_page_hiroshima , $result) ){
    $last_updated_hiroshima = strtotime($result[2][0]);
    $str_last_updated_hiroshima =date('Y/n/j H時i分',strtotime($result[2][0]));
  }else{
    // エラーの時
    $last_updated_hiroshima = strtotime(date('Y/n/j'));
    $str_last_updated_hiroshima='';
  }

//感染状況の取得
  $pattern_hiroshima2 = '/<h4>感染状況<\/h4>(.*)<p\sdata-v-883a402c>(.*)<\/p>/siU';
    if( preg_match_all($pattern_hiroshima2, $web_page_hiroshima , $result2) ){
      $str_stage_hiroshima = $result2[2][0];
    }else{
      // エラーの時
      $str_stage_hiroshima='';
    }

//広島県のHPからCSVデータ取得
$csv_hiroshima = file_get_contents("https://www.pref.hiroshima.lg.jp/soshiki_file/brand/covid19/opendata/340006_hiroshima_covid19_01_patients.csv");
setlocale( LC_ALL, 'ja_JP' );
$lines_hiroshima = str_getcsv($csv_hiroshima, "\r\n");
if (preg_match('/No,/',$lines_hiroshima[0],$result3)){
  //delimiter
  $delimiter_hiroshima = ",";
  $CSV_format_hiroshima = 'SJIS';
} else {
  //delimiter
  $delimiter_hiroshima = "\t";
  $CSV_format_hiroshima = 'SJIS';
}
foreach ($lines_hiroshima as $line2) {
  $records_hiroshima[] = str_getcsv($line2, $delimiter_hiroshima);
}
$cnt_hiroshima = count($lines_hiroshima); // 症例数は$cnt-1

$arry_column_hiroshima = array('No'=>0, 'examin'=>4, 'onset'=>5, 'center'=>6, 'living'=>7 ,'age'=>9);
// 0 No;
// 4 公表日;
// 5 発症日;
// 6 保健所
// 7 居住地
//9 年齢
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period_hiroshima = $cnt_hiroshima - 1; //トータルの患者数
$cnt_total_mihara = 0;
$cnt_total_onomichi = 0;
if(empty($str_last_updated_hiroshima)){ //直近1週間の期間を設定
  $search_day1_hiroshima = strtotime('-7 days');
} else {
  $search_day1_hiroshima = strtotime(date('Y/m/d',$last_updated_hiroshima) . '-7 days');
}
for ($i = $cnt_total_all_period_hiroshima; $i>=1; $i--) {
  if ($records_hiroshima[$i][$arry_column_hiroshima['examin']]=='-') {
    //変なデータはスキップ
  } else if ($search_day1_hiroshima> strtotime(str_replace('-','/',$records_hiroshima[$i][$arry_column_hiroshima['examin']]))) { //1週間前＋1日の日時まで来たら終了
    $cnt_total_hiroshima = $cnt_total_all_period_hiroshima - $i; //直近1週間の症例数を記録
    $second_index_hiroshima = $i; //その前の1週間の判定に使用
    break;
  } else {
    if ($CSV_format_hiroshima == 'SJIS') { //コメント行の取得
      $living_area=mb_convert_encoding($records_hiroshima[$i][$arry_column_hiroshima['living']], "utf-8", "SJIS");
    } else {
      $living_area=$records_hiroshima[$i][$arry_column_hiroshima['living']];
    }
    if($living_area=='三原市') {
      $cnt_total_mihara++;
    } else if($living_area=='尾道市') {
      $cnt_total_onomichi++;
    }
  }
}
//2-1週間前のデータ
if(empty($str_last_updated_hiroshima)){ //2-1週間前の期間を設定
    $search_day2_hiroshima = strtotime('-14 days');
  } else {
    $search_day2_hiroshima = strtotime(date('Y/m/d',$last_updated_hiroshima) . '-14 days');
  }
for ($i = $second_index_hiroshima; $i>=1; $i--) { //1週間前より前の患者のカウント
  if ($records_hiroshima[$i][$arry_column_hiroshima['examin']]=='-') {
    //変なデータはスキップ
  } else if ($search_day2_hiroshima > strtotime(str_replace('-','/',$records_hiroshima[$i][$arry_column_hiroshima['examin']]))) { //2週間前＋1日の日時まで来たら終了
    $cnt_total2_hiroshima = $second_index_hiroshima - $i; //2-1週間前の症例数を記録
    break;
  }
}
?>
                <div class="message">
                    <h2><a href="https://hiroshima.stopcovid19.jp">広島県</a></h2>

                    <?php
if (preg_match('/ステージ1/', $str_stage_hiroshima)) {
  $h3_id_hiroshima = 'blue';
} else if (preg_match('/ステージ2/', $str_stage_hiroshima)) {
  $h3_id_hiroshima = 'yellow';
} else if (preg_match('/ステージ3/', $str_stage_hiroshima)) {
  $h3_id_hiroshima = 'brown';
} else if (preg_match('/ステージ4/', $str_stage_hiroshima)) {
  $h3_id_hiroshima = 'pink';
}

echo "<h3 id=\"" . $h3_id_hiroshima . "\">" . $str_stage_hiroshima . "</h3>";
echo "<h3>広島県陽性者数：" . $cnt_total_hiroshima . "人/週";
if(empty($str_last_updated_hiroshima)){
  echo "（" . date('n/j',strtotime('-7 days')) . "〜" . date('n/j',strtotime('-1 day')) . "）<br />";
} else {
  echo "（" . date('n/j',strtotime(date('Y/m/d',$last_updated_hiroshima) . '-7 days')) . "〜" . date('n/j',strtotime(date('Y/m/d',$last_updated_hiroshima) . '-1 day')) . "）<br />";
}

echo "10万人あたり" . sprintf('%.1f',$cnt_total_hiroshima/28.1) . "人, 先週比：" . (int)(($cnt_total_hiroshima / $cnt_total2_hiroshima) * 100) . "%<br />";

if ($cnt_total_onomichi != 0) {
  echo "尾道市：" . $cnt_total_onomichi . "人/週（" . sprintf('%.1f', $cnt_total_onomichi/1.38) . "/10万人）<br />";
} 
if ($cnt_total_mihara != 0) {
  echo "三原市：" . $cnt_total_mihara . "人/週（" . sprintf('%.1f', $cnt_total_mihara/0.96) . "/10万人）<br />";
}
echo "</h3>";

if(empty($str_last_updated_hiroshima)){
  echo "広島県のデータは毎日午前中に更新されます。<br />";
} else {
  echo "更新日時：" . $str_last_updated_hiroshima . "<br />";
  echo "広島県のデータは福山市より遅れて更新されます。<br />";
  echo "感染状況のステージについては反映が１週間遅れることがあります。";
}

?>
                </div>

                <?php
//岡山県の更新日の取得
$target_okayama = "http://www.okayama-opendata.jp/opendata/ga130PreAction.action?resourceName=感染者詳細情報&keyTitle=d9c4776db7f09fff161953a2aaf03b80a9abad48&title=新型コロナウイルス感染症に関するデータ（岡山県）&isParam=1&resourceId=d021c012-297e-4ea9-bffa-cf55741884d1&licenseTitle=クリエイティブ・コモンズ+表示&datasetId=e6b3c1d2-2f1f-4735-b36e-e45d36d94761&checkFieldFormat=CSV";
$curl_okayama = curl_init();
curl_setopt($curl_okayama, CURLOPT_URL, $target_okayama);
curl_setopt($curl_okayama, CURLOPT_RETURNTRANSFER, true);
$web_page_okayama = curl_exec($curl_okayama);
curl_close($curl_okayama);
$pattern_okayama = '/<th\sscope=\"row\">最終更新<\/th>(.*)<td>(.*)<\/td>/siU';
  if( preg_match_all($pattern_okayama, $web_page_okayama , $result_okayama) ){
    $str_date = $result_okayama[2][0];
    $str_date = str_replace('年','/',$str_date);
    $str_date = str_replace('月','/',$str_date);
    $str_date = str_replace('日','',$str_date);
    $last_updated_okayama = strtotime($str_date);
    $str_last_updated_okayama=$str_date;
  }else{
    // エラーの時
    $last_updated_okayama = strtotime(date('Y/m/d'));
    $str_last_updated_okayama='';
  }

//感染状況の取得
$target_okayama = "https://www.pref.okayama.jp/page/724270.html#01-kennaijoukyou";
$curl_okayama = curl_init();
curl_setopt($curl_okayama, CURLOPT_URL, $target_okayama);
curl_setopt($curl_okayama, CURLOPT_RETURNTRANSFER, true);
$web_page_okayama = curl_exec($curl_okayama);
curl_close($curl_okayama);
$pattern_okayama = '/<strong>総合的判断：(.*)<\/strong>/siU';
if( preg_match_all($pattern_okayama, $web_page_okayama , $result_okayama2) ){
  $str_stage_okayama = $result_okayama2[1][0];
}else{
  // エラーの時
  $str_stage_okayama='';
}

//岡山県のHPからCSVデータ取得
$csv_okayama = file_get_contents("http://www.okayama-opendata.jp/ckan/dataset/e6b3c1d2-2f1f-4735-b36e-e45d36d94761/resource/d021c012-297e-4ea9-bffa-cf55741884d1/download/kansenshashousaijouhou.csv");
setlocale( LC_ALL, 'ja_JP' );
$lines_okayama = str_getcsv($csv_okayama, "\r\n");
if (preg_match('/330001,/',$lines_okayama[1],$result_okayama)){
  //delimiter
  $delimiter_okayama = ",";
  $CSV_format_okayama = 'SJIS';
} else {$
  //delimiter
  $delimiter_okayama = "\t";
  $CSV_format_okayama = 'SJIS';
}
foreach ($lines_okayama as $line) {
  $records_okayama[] = str_getcsv($line, $delimiter_okayama);
}
$cnt_okayama = count($lines_okayama); // 症例数は$cnt-1

$arry_column_okayama = array('examin'=>3, 'living'=>5 ,'age'=>6);
// 3 公表日
// 5 居住地
// 6 年齢
date_default_timezone_set('Asia/Tokyo');
//1週間のデータ
$cnt_total_all_period_okayama = $cnt_okayama - 1; //トータルの患者数
if(empty($str_last_updated_okayama)){ //直近1週間の期間を設定
  $search_day1_okayama = strtotime('-6 days');
} else {
  $search_day1_okayama = strtotime($str_last_updated_okayama . '-6 days');
}
for ($i = $cnt_total_all_period_okayama; $i>=1; $i--) {
  if ($search_day1_okayama > strtotime($records_okayama[$i][$arry_column_okayama['examin']])) { //1週間前＋1日の日時まで来たら終了
    $cnt_total_okayama = $cnt_total_all_period_okayama - $i; //直近1週間の症例数を記録
    $second_index_okayama = $i; //その前の1週間の判定に使用
    break;
  } 
}
//2-1週間前のデータ
if(empty($str_last_updated_okayama)){ //2-1週間前の期間を設定
    $search_day2_okayama = strtotime('-13 days');
  } else {
    $search_day2_okayama = strtotime($str_last_updated_okayama . '-13 days');
  }
for ($i = $second_index_okayama; $i>=1; $i--) { //1週間前より前の患者のカウント
  if ($search_day2_okayama > strtotime($records_okayama[$i][$arry_column_okayama['examin']])) { //2週間前＋1日の日時まで来たら終了
    $cnt_total2_okayama = $second_index_okayama - $i; //2-1週間前の症例数を記録
    break;
  }
}

?>
                <div class="message">
                    <h2><a href="https://www.pref.okayama.jp/page/724270.html#01-kennaijoukyou">岡山県</a></h2>

                    <?php

if (preg_match('/ステージ１/', $str_stage_okayama)) {
  $h3_id_okayama = 'blue';
  $str_stage_okayama = '[ステージ1] 医療提供体制に特段の支障がない段階';
} else if (preg_match('/ステージ２/', $str_stage_okayama)) {
  $h3_id_okayama = 'yellow';
  $str_stage_okayama = '[ステージ2] 感染者の漸増及び医療提供体制への負荷が蓄積する段階';
} else if (preg_match('/ステージ３/', $str_stage_okayama)) {
  $h3_id_okayama = 'brown';
  $str_stage_okayama = '[ステージ3] 感染者の急増及び医療提供体制における大きな支障の発生を避けるための対応が必要な段階 ';
} else if (preg_match('/ステージ４/', $str_stage_okayama)) {
  $h3_id_okayama = 'pink';
  $str_stage_okayama = '[ステージ4] 爆発的な感染拡大及び深刻な医療提供体制の機能不全を避けるための対応が必要な段階';
}

echo "<h3 id=\"" . $h3_id_okayama . "\">" . $str_stage_okayama . "</h3>";
echo "<h3>岡山県陽性者数：" . $cnt_total_okayama . "人/週";
if(empty($str_last_updated_okayama)){
  echo "（" . date('n/j',strtotime('-6 days')) . "〜" . date('n/j') . "）<br />";
} else {
  echo "（" . date('n/j',strtotime($str_last_updated_okayama . '-6 days')) . "〜" . date('n/j',$last_updated_okayama) . "）<br />";
}

echo "10万人あたり" . sprintf('%.1f',$cnt_total_okayama/19) . "人, 先週比：" . (int)(($cnt_total_okayama / $cnt_total2_okayama) * 100) . "%</h3>";
if(empty($str_last_updated_okayama)){
    echo "岡山のデータは毎日夕方に更新されます。<br />";
  } else {
    echo "更新日時：" . $str_last_updated_okayama ."<br/>";
  }
echo "感染状況のステージについては反映が１週間遅れることがあります。";
?>

                </div>
            </div>
            福山市の直近1ヶ月の陽性者リスト<br />

            <?php
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
    $examday=$records[$i][$arry_column['examin']];
    if (strtotime('-30 days') > strtotime($examday)) {

    } else {
      echo "<tr>";
      echo "<td>";
      if ($CSV_format == 'SJIS') {
        $comment=mb_convert_encoding($records[$i][$arry_column['comment']], "utf-8", "SJIS");
      } else {
        $comment=$records[$i][$arry_column['comment']];
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
        元データ：
        <a href="https://hiroshima.stopcovid19.jp">広島県</a>&nbsp;&nbsp;
        <a href="https://usecase.data.city.fukuyama.hiroshima.jp/covid-19/">福山市</a>&nbsp;&nbsp;
        <a href="https://www.pref.okayama.jp/page/724270.html#01-kennaijoukyou">岡山県</a><br />
        ソースコード：
        <a href="https://github.com/poporacchi/ota-covid19-database" title="GitHub">GitHub</a>
    </article>


    <footer>
        <hr />
        <p>&copy;&nbsp;2021&nbsp;大田記念病院感染管理室</p>
    </footer>

</body>

</html>