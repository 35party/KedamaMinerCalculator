<?php

switch ($_GET["server"]) {
  case "kedama":
    $serverurl = "https://stats.craft.moe/";
    break;
  case "nyaacat":
    $serverurl = "https://i.nyaa.cat/";
    break;
  default:
    echo "参数错误";
    exit();
}

//获取玩家UUID
$playerdata = file_get_contents("https://api.mojang.com/users/profiles/minecraft/" . $_GET["id"]);
if ($playerdata) {
  $playerid = (json_decode($playerdata)->id);
  //忽略i.nyaa.cat的证书错误
  $arrContextOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
  ];
  $json = file_get_contents($serverurl . "data/" . $playerid . "/stats.json", false, stream_context_create($arrContextOptions));
  if ($json) {
    $stats = json_decode($json,true);
    //统计信息
    $output = array(
      'playername' => $stats['data']['playername'],
      'playeruuid' => $stats['data']['uuid'],
      'playerban' => $stats['data']['banned'] ? '是':'否',
      'playertimestart' => date('Y-m-d H:i:s', $stats['data']['time_start'] / 1000),
      'playertimelast' => date('Y-m-d H:i:s', $stats['data']['time_last'] / 1000),
    );
    //挖掘数量
    $stone = $stats['stats']['minecraft:mined/minecraft:stone'];
    $coal = $stats['stats']['minecraft:mined/minecraft:coal_ore'];
    $iron = $stats['stats']['minecraft:mined/minecraft:iron_ore'];
    $gold = $stats['stats']['minecraft:mined/minecraft:gold_ore'];
    $redstone = $stats['stats']['minecraft:mined/minecraft:redstone_ore'];
    $emerald = $stats['stats']['minecraft:mined/minecraft:emerald_ore'];
    $diamond = $stats['stats']['minecraft:mined/minecraft:diamond_ore'];
    $quartz = $stats['stats']['minecraft:mined/minecraft:nether_quartz_ore'];
    $ancientdebris = $stats['stats']['minecraft:mined/minecraft:ancient_debris'];
    $all = $coal + $iron + $gold + $redstone + $emerald + $diamond + $quartz + $ancientdebris;
    $output['mined'] = array(
      'stone' => $stone,
      'coal' => $coal,
      'iron' => $iron,
      'gold' => $gold,
      'redstone' => $redstone,
      'emerald' => $emerald,
      'diamond' => $diamond,
      'quartz' => $quartz,
      'ancientdebris' => $ancientdebris,
    );
    //矿物比例
    $output['ratio'] = array(
      'diamondall' => round($diamond / $all * 100, 2)."%",
      'diamondcoal' => round($diamond / $coal * 100, 2)."%",
      'diamondiron' => round($diamond / $iron * 100, 2)."%",
      'diamondstone' => round($diamond / $stone * 100, 2)."%",
      'allstone' => round($all / $stone * 100, 2)."%",
    );
    echo json_encode($output);
  }
  else {
    echo "服务器数据中找不到该玩家";
    exit();
  }
}
else {  
  echo "Mojang数据库中找不到这个id";
  exit();
}

?>