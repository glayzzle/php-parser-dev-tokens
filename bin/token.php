<?php
/**
 * Copyright (C) 2017 Glayzzle (BSD3 License)
 * @authors https://github.com/glayzzle/php-parser/graphs/contributors
 * @url http://glayzzle.com
 */
ini_set('memory_limit', '1024M');

// clean up old generated source
$root = realpath(__DIR__ . '/..') . '/';
$target = [
  'src',
  'framework/beaba',
  'framework/cakephp',
  'framework/CodeIgniter',
  'framework/drupal',
  'framework/evernote-cloud-sdk-php',
  'framework/htmlpurifier',
  'framework/laravel',
  'framework/magento1',
  'framework/magento2',
  'framework/opencart',
  'framework/php-langspec',
  'framework/symfony',
  'framework/tcpdf',
  'framework/wordpress',
  'framework/yii2',
  'framework/yii1',
  'framework/zf2',
];

if (in_array('--path', $argv)) {
  $path = $argv[array_search('--path', $argv) + 1];
  $target = [$path];
  exec('rm -rf ' . $root . 'php/' . $path);
  mkdir($root . 'php/' . $path, 0777, true);
} else {
  exec('rm -rf ' . $root . 'php');
  mkdir($root . 'php', 0777, true);
}


function buildTokenFile($filename, $target) {
  $tokens = token_get_all(
    file_get_contents($filename)
  );
  $last = count($tokens) - 1;
  $buffer = '[';
  foreach($tokens as $p => $t) {
    if (is_array($t)) {
      $t[0] = token_name($t[0]);
      $t[1] = utf8_encode($t[1]);
    }
    $buffer .= json_encode( $t );
    if ($t[0] === 'T_HALT_COMPILER') break; // last token to test
    if ($p !== $last) {
      $buffer .= ',';
    }
  }
  $buffer .= ']';
  $folder = dirname($target);
  if (!is_dir($folder)) mkdir($folder, 0777, true);
  file_put_contents($target, gzcompress($buffer, 9));
}

echo "Generating files\n";
foreach($target as $path) {
  $i = 0;
  $Directory = new RecursiveDirectoryIterator($root . $path);
  $Iterator = new RecursiveIteratorIterator($Directory);
  $Regex = new RegexIterator($Iterator, '/^.+\.(php|phtml|req|inc|php5)$/i', RecursiveRegexIterator::GET_MATCH);
  $filesCount = iterator_count($Regex);
  echo "\n=> $path (" . $filesCount. ") : ";
  if ( $filesCount > 0 ) {
    $percent = round($filesCount / 60);
    if ($percent < 1) $percent = 1;
    foreach($Regex as $item) {
      if (++$i % $percent === 0) echo '#';
      $filename = substr($item[0], strlen($root));
      buildTokenFile($item[0], $root . 'php/' . $filename . '.token');
    }
  }
}

echo "\n\nScan finished\n\n";
