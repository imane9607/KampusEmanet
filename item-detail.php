<?php
require_once 'includes/auth.php';
require_once 'includes/items.php';
require_once 'includes/users.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = Items::get($id);
if(!$item){
  http_response_code(404);
  echo "<h2 class='text-center mt-20 text-2xl font-semibold'>Eşya bulunamadı</h2>";
  exit;
}
$user = Auth::user();
$poster = Users::get($item['user_id']);
$img = Items::firstImage($item['id']) ?: 'assets/img/placeholder.png';
?>
<!DOCTYPE html>
<html lang="tr"><head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<style type="text/tailwindcss">
      :root {
        --primary-color: #0c7ff2;
      }
      body {
        font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;
      }
    </style>
<title><?= htmlspecialchars($item['title']); ?> - Kampüs Emanet</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="stylesheet" href="assets/css/theme.css" />
</head>
<body class="bg-slate-50 text-slate-900">
<div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<?php include 'includes/header.php'; ?>
<main class="flex-1 px-4 sm:px-6 py-8 lg:py-12">
<div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-8 lg:gap-12">
<div class="aspect-[4/3] sm:aspect-[3/2] lg:aspect-auto bg-slate-200 rounded-lg overflow-hidden">
<div class="w-full h-full bg-center bg-no-repeat bg-cover" style="background-image:url('<?= htmlspecialchars($img); ?>');"></div>
</div>
<div class="flex flex-col">
<div class="flex items-center justify-between mb-3">
<h1 class="text-3xl sm:text-4xl font-bold tracking-tight"><?= htmlspecialchars($item['title']); ?></h1>
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $item['status']==='lost'?'bg-red-100 text-red-600':($item['status']==='found'?'bg-green-100 text-green-600':'bg-blue-100 text-blue-600'); ?>">
<?= ucfirst($item['status']); ?>
</span>
</div>
<p class="text-slate-600 text-base leading-relaxed mb-6">
<?= nl2br(htmlspecialchars($item['description'])); ?>
</p>
<div class="space-y-4 border-t border-slate-200 pt-6">
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center">
<p class="text-sm font-medium text-slate-500">Gönderen</p>
<p class="col-span-1 sm:col-span-2 text-sm text-slate-800"><?= htmlspecialchars($poster['name']); ?></p>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center border-t border-slate-200 pt-4">
<p class="text-sm font-medium text-slate-500">İletişim</p>
<p class="col-span-1 sm:col-span-2 text-sm text-slate-800">
  <a href="tel:<?= htmlspecialchars($poster['phone']); ?>" class="text-blue-600 hover:text-blue-800">
    <span class="material-icons-outlined text-sm">phone</span>
    <?= htmlspecialchars($poster['phone']); ?>
  </a>
  <br>
  <a href="mailto:<?= htmlspecialchars($poster['email']); ?>" class="text-blue-600 hover:text-blue-800">
    <span class="material-icons-outlined text-sm">email</span>
    <?= htmlspecialchars($poster['email']); ?>
  </a>
</p>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center border-t border-slate-200 pt-4">
<p class="text-sm font-medium text-slate-500">Kategori</p>
<?php 
  $catLabels=['electronics'=>'Elektronik','keys'=>'Anahtarlar','books'=>'Kitaplar','clothing'=>'Giysiler','accessories'=>'Aksesuarlar','bags'=>'Çantalar','other'=>'Diğer'];
  $catRaw = $item['category'] ?? '';
  $catText = $catLabels[$catRaw] ?? ($catRaw!=='' ? ucfirst($catRaw) : 'Not specified');
?>
<p class="col-span-1 sm:col-span-2 text-sm text-slate-800"><?= htmlspecialchars($catText); ?></p>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center border-t border-slate-200 pt-4">
<p class="text-sm font-medium text-slate-500">Konum</p>
<p class="col-span-1 sm:col-span-2 text-sm text-slate-800"><?= htmlspecialchars($item['location']); ?></p>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 items-center border-t border-slate-200 pt-4">
<p class="text-sm font-medium text-slate-500">Gönderim Tarihi</p>
<p class="col-span-1 sm:col-span-2 text-sm text-slate-800"><?= date('d M Y', strtotime($item['date'])) . ' ' . date('H:i', strtotime($item['date'])); ?></p>
</div>
</div>
<?php if($user && $user['id']==$item['user_id']): ?>
  <div class="mt-auto pt-8">
    <div class="flex flex-col sm:flex-row gap-3">
      <a href="edit-item.php?id=<?= $item['id']; ?>" class="flex-1 inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md h-11 px-5 bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-colors">
        <span class="material-icons-outlined text-xl">edit</span>
        İlanı Düzenle
      </a>
      <button class="flex-1 inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md h-11 px-5 bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2" onclick="return confirm('Bu ilanı silmek istiyor musunuz?');">
        <span class="material-icons-outlined text-xl">delete</span>
        İlanı Sil
      </button>
    </div>
  </div>
<?php endif; ?>
</div>
</div>
</div>
</main>
</div>
</div>

</body></html>