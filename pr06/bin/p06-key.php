<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$folder = dirname(__DIR__).'/var';
if (!is_dir($folder) && !mkdir($folder,0700,true)) throw new RuntimeException('var directory');
$path = $folder.'/jwt.key';
$old = umask(0077);
try {
    $file = @fopen($path,'x');
    if ($file === false) { fwrite(STDERR,"Ключ уже существует или файл недоступен; он не перезаписан.\n"); exit(1); }
    try { if (fwrite($file,random_bytes(32)) !== 32) throw new RuntimeException('Key write'); }
    finally { fclose($file); }
} finally { umask($old); }
echo "Ключ JWT создан вне public/. Значение не выводится.\n";
