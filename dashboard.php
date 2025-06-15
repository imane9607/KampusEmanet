<?php
require_once 'includes/auth.php';
require_once 'includes/items.php';
Auth::requireLogin();
$user = Auth::user();
$items = Items::getByUser($user['id']);
$total = Items::countAll();
$reunited = Items::countReunited();
?>
<?php include 'includes/header.php'; ?>
<html><head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Icons+Outlined" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/theme.css" />
<title>Kampüs Emanet - İlanlarım</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style type="text/tailwindcss">
    :root {
      --primary-color: #3B82F6;
      --primary-hover-color: #2563EB;
      --success-color: #10B981;
      --danger-color: #EF4444;
      --text-primary: #1F2937;
      --text-secondary: #6B7280;
      --surface-background:#F9FAFB;
      --surface-card:#FFFFFF;
      --surface-input:#F3F4F6;
      --border-color:#E5E7EB;
    }
    .active-tab {
      border-bottom-width: 3px;
      border-color: var(--primary-color);
      color: var(--primary-color);
      font-weight: 600;
      text-decoration: none;
    }
    .tab-inactive {
      border-bottom-width: 3px;
      border-color: transparent;
      color: var(--text-secondary);
      font-weight: 500;
      text-decoration: none;
    }
    .tab-inactive:hover {
      border-color: var(--border-color);
      color: var(--text-primary);
    }
    .stat-card {
      background-color: white;
      border: 1px solid var(--border-color);
      border-radius: 0.75rem;padding: 1.5rem;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
    }
    .item-card {
      background-color: white;
      border: 1px solid var(--border-color);
      border-radius: 0.75rem;padding: 1.5rem;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
    }
    .action-button {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.5rem;padding: 0.5rem 1rem;font-size: 0.875rem;font-weight: 500;
      transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
      text-decoration: none;
    }
    .button-primary {
      background-color: var(--primary-color);
      color: white;
    }
    .button-primary:hover {
      background-color: var(--primary-hover-color);
    }
    .button-secondary {
      background-color: var(--surface-input);
      color: var(--text-primary);
      border: 1px solid var(--border-color);
    }
    .button-secondary:hover {
      background-color: #E5E7EB;}
    .button-danger {
      background-color: transparent;
      color: var(--danger-color);
    }
    .button-danger:hover {
      background-color: #FEF2F2;}
    .notification-badge {
      background-color: var(--danger-color);
      color: white;
      font-size: 0.75rem;font-weight: 500;
      padding: 0.125rem 0.375rem;border-radius: 0.5rem;margin-left: 0.25rem;}
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1);
      z-index: 1;
      border-radius: 0.5rem;
      border: 1px solid var(--border-color);
      right: 0;
      margin-top: 0.25rem;
    }
    .dropdown-content a {
      color: var(--text-primary);
      padding: 0.75rem 1rem;
      text-decoration: none;
      display: block;
      font-size: 0.875rem;
    }
    .dropdown-content a:hover {
      background-color: var(--surface-input);
    }
    .dropdown:hover .dropdown-content {
      display: block;
    }
    .active-tab{background-color:white;color:var(--primary-color);font-weight:600;box-shadow:0 1px 2px rgba(0,0,0,0.05);transition:color .2s,background-color .2s;}
    .tab-inactive{color:var(--text-secondary);transition:color .2s;}
    .tab-inactive:hover{color:var(--primary-color);}
  </style>
</head>
<body class="bg-[var(--surface-background)]" style='font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;'>
<div class="relative flex min-h-screen flex-col overflow-x-hidden">
<div class="layout-container flex flex-col min-h-screen">
<main class="flex-1 px-4 sm:px-6 lg:px-10 xl:px-20 2xl:px-40 py-8">
<div class="mx-auto max-w-5xl">
<div class="flex items-center justify-between mb-6">
<h2 class="text-3xl font-bold text-[var(--text-primary)]">İlanlarım</h2>
<a href="create-item.php" class="action-button button-primary flex items-center gap-1"><span class="material-icons-outlined text-base">add</span> Yeni İlan</a>
</div>
<div class="mb-6">
<div class="inline-flex bg-gray-100 rounded-lg p-1 mb-6">
<a href="dashboard.php" class="active-tab rounded-md px-4 sm:px-6 py-2 text-sm tracking-wide">İlanlarım</a>
<a href="profile.php" class="tab-inactive rounded-md px-4 sm:px-6 py-2 text-sm tracking-wide">Profilim</a>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
<div class="stat-card flex flex-col gap-1">
<p class="text-base font-medium text-[var(--text-secondary)]">Aktif İlanlar</p>
<p class="text-3xl font-bold text-[var(--text-primary)]"><?php echo $total; ?></p>
</div>
<div class="stat-card flex flex-col gap-1">
<p class="text-base font-medium text-[var(--text-secondary)]">Başarıyla Bulunan Eşyalar</p>
<p class="text-3xl font-bold text-[var(--success-color)]"><?php echo $reunited; ?></p>
</div>
</div>
<!-- User Listings -->
<div class="mt-8">
<h3 class="text-xl font-semibold mb-4">İlanlarım (<?= count($items); ?>)</h3>
<?php if(!$items): ?>
<p class="text-sm text-[var(--text-secondary)]">Henüz hiçbir ilan eklememişsiniz.</p>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
<?php foreach($items as $it): $img=Items::firstImage($it['id']); ?>
<div class="item-card">
<?php if($img): ?><img src="<?= htmlspecialchars($img); ?>" class="h-40 w-full object-cover rounded-t-md mb-3" /><?php endif; ?>
<h4 class="text-lg font-bold mb-1"><?= htmlspecialchars($it['title']); ?></h4>
<p class="text-sm mb-2"><span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold capitalize <?= $it['status']==='lost'?'bg-red-100 text-red-600':($it['status']==='found'?'bg-green-100 text-green-600':'bg-blue-100 text-blue-600'); ?>"><?= htmlspecialchars($it['status']); ?></span></p>
<?php $catLabels=['electronics'=>'Elektronik','keys'=>'Anahtarlar','books'=>'Kitaplar','clothing'=>'Giysiler','accessories'=>'Aksesuarlar','bags'=>'Çantalar','other'=>'Diğer']; ?>
<p class="text-xs text-[var(--text-secondary)] mb-1">Kategori: <?= htmlspecialchars($catLabels[$it['category']] ?? ucfirst($it['category'])); ?></p>
<p class="text-xs text-[var(--text-secondary)] mb-4">Tarih: <?= htmlspecialchars($it['date']); ?></p>
<div class="flex gap-2">
<a href="edit-item.php?id=<?= $it['id']; ?>" class="action-button button-secondary">Düzenle</a>
<a href="profile.php?delete=<?= $it['id']; ?>" class="action-button button-danger" onclick="return confirm('Bu ilanı silmek istiyor musunuz?');">Sil</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</main>
<footer class="py-6 mt-12 text-center text-sm text-[var(--text-secondary)] border-t border-[var(--border-color)] bg-white">
        &copy; 2024 Kampüs Emanet. Tüm hakları saklıdır.
      </footer>
</div>
</div>

</body></html>