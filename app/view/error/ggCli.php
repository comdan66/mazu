<?php

echo "\n" . cc(str_repeat ('═', 2), 'N') . cc(' 錯誤 ', 'r') . cc(str_repeat ('═', 72), 'N') . "\n\n";

if ($text !== null) {
  echo cc($text, 'W') . "\n";
  echo "\n" . cc(str_repeat ('═', 80), 'N') . "\n\n";
}

if (!empty($contents['msgs'])) {
  foreach ($contents['msgs'] as $msg) {
    echo cc(' ➤ ', 'R') . cc($msg, 'W') . "\n";
  }
  echo "\n";
}
if (!empty($contents['details'])) {
  foreach ($contents['details'] as $detail) {
    echo cc(' ➤ ', 'R') . $detail['title'] . cc('：', 'N') . cc($detail['content'], 'W') . "\n";
  }
  echo "\n";
}
if (!empty($contents['traces'])) {
  foreach ($contents['traces'] as $trace) {
    echo cc(' ※ ', 'P') . cc($trace['info'], 'W') . "\n   " . cc($trace['path'], 'N') . "\n\n";
  }
}
echo "\n";
