<?php

echo "\n" . cliColor(str_repeat ('═', 2), 'N') . cliColor(' 錯誤 ', 'r') . cliColor(str_repeat ('═', 72), 'N') . "\n\n";
echo cliColor($text, 'W') . "\n";
echo "\n" . cliColor(str_repeat ('═', 80), 'N') . "\n\n";

if (!empty($contents['details'])) {
  foreach ($contents['details'] as $detail) {
    echo cliColor(' ◎ ', 'G') . $detail['title'] . cliColor('：', 'N') . cliColor($detail['content'], 'W') . "\n";
  }
  echo "\n";
}
if (!empty($contents['traces'])) {
  foreach ($contents['traces'] as $trace) {
    echo cliColor(' ※ ', 'P') . cliColor($trace['info'], 'W') . "\n   " . cliColor($trace['path'], 'N') . "\n\n";
  }
}
echo "\n";
