<?php
$project = $_GET['project'] ?? null;

$basePath = __DIR__ . '/projects/';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'mov', 'webm'];

if (!$project || !is_dir($basePath . $project)) {
    http_response_code(404);
    echo "<h1>Project not found</h1>";
    exit;
}

$dir = $basePath . $project . '/';
$webDir = 'projects/' . $project . '/';

$media = [];

foreach (scandir($dir) as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $path = $webDir . $file;
    if (in_array($ext, $allowed_extensions)) {
        $media[] = $path;
    }
}
sort($media);

$title = $project;
$description = '';
$credits = '';

function make_links_clickable($text) {
    return preg_replace(
        '~(https?://[^\s]+)~',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        htmlspecialchars($text)
    );
}

$metaFile = $dir . 'meta.json';
$infoFile = $dir . 'info.txt';

if (file_exists($metaFile)) {
    $meta = json_decode(file_get_contents($metaFile), true);
    $title = $meta['title'] ?? $title;
    $description = $meta['description'] ?? '';
    $credits = $meta['credits'] ?? '';
} elseif (file_exists($infoFile)) {
    $lines = file($infoFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with($line, 'Title:')) {
            $title = trim(substr($line, 6));
        } elseif (str_starts_with($line, 'Description:')) {
            $description = trim(substr($line, 12));
        } elseif (str_starts_with($line, 'Credits:')) {
            $credits = trim(substr($line, 8));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    body {
      font-family: sans-serif;
      font-size: 12px;
      line-height: 1.2;
      padding: 40px 20px;
      background: #fff;
      color: #111;
      max-width: 900px;
      margin: auto;
    }
    h1, p {
      font-size: 12px;
      line-height: 1.2;
      font-weight: normal;
      margin-bottom: 1em;
    }
    .gallery {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 30px;
    }
    .gallery img,
    .gallery video {
      display: block;
      margin: 0 auto;
      height: auto;
      width: auto;
      max-width: 100%;
      max-height: 100%;
    }
    .landscape {
      width: 800px !important;
      height: auto !important;
    }
    .portrait {
      height: 600px !important;
      width: auto !important;
    }
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($title) ?></h1>
  <?php if ($description): ?>
    <p><?= nl2br(make_links_clickable($description)) ?></p>
  <?php endif; ?>
  <?php if ($credits): ?>
    <p><strong>Credits:</strong> <?= htmlspecialchars($credits) ?></p>
  <?php endif; ?>

  <div class="gallery">
    <?php foreach ($media as $file): ?>
      <?php $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); ?>
      <?php if (in_array($ext, ['mp4', 'webm', 'mov'])): ?>
        <video src="<?= htmlspecialchars($file) ?>" autoplay loop muted playsinline class="landscape"
               onloadedmetadata="
                 this.classList.remove('landscape', 'portrait');
                 if (this.videoWidth > this.videoHeight) {
                   this.classList.add('landscape');
                 } else {
                   this.classList.add('portrait');
                 }
               "></video>
      <?php else: ?>
        <img src="<?= htmlspecialchars($file) ?>" alt="" class="landscape"
             onload="
               this.classList.remove('landscape', 'portrait');
               if (this.naturalWidth > this.naturalHeight) {
                 this.classList.add('landscape');
               } else {
                 this.classList.add('portrait');
               }
             ">
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</body>
</html>
