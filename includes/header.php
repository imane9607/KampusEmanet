<?php
// Tüm sayfalar için ortak üst bilgi. Auth::user() ile $user beklenir.
if (!isset($user)) {
    require_once __DIR__ . '/auth.php';
    $user = Auth::user();
}
?>
<header id="siteHeader" class="sticky top-0 z-10 flex items-center justify-between whitespace-nowrap border-b border-[var(--border-color)] bg-white px-4 py-3 md:py-4 shadow-sm transition-transform duration-300">
  <div class="flex items-center gap-3 text-[var(--text-primary)]">
    <img src="assets/logo.png" alt="Kampüs Emanet Logo" class="h-12 w-12 md:h-14 md:w-14 object-contain transform scale-125 md:scale-150" style="transform-origin:left center;" />
  </div>
  <nav class="hidden md:flex items-center gap-6">
    <a href="index.php#home" class="text-sm font-medium no-underline text-[var(--primary-color)]">Ana Sayfa</a>
    <a href="index.php#items-list" class="text-sm font-medium no-underline text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors">Eşyaları Görüntüle</a>
    <?php if ($user): ?>
      <a href="create-item.php" class="text-sm font-medium no-underline text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors">Eşya Kaydet</a>
    <?php endif; ?>
  </nav>
  <!-- Desktop Avatar Dropdown -->
  <div class="hidden md:flex items-center gap-4 relative">
    <?php if ($user): ?>
      <details class="relative group">
        <summary class="list-none cursor-pointer">
          <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 border-2 border-slate-200" style="background-image: url('assets/avatar.png');"></div>
        </summary>
        <div class="absolute right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg py-1">
          <a href="profile.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Profil</a>
          <a href="dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kontrol Paneli</a>
          <a href="logout.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Çıkış Yap</a>
        </div>
      </details>
    <?php else: ?>
      <a href="login.php" class="btn-primary rounded-lg px-4 py-2 text-sm font-semibold">Giriş Yap</a>
    <?php endif; ?>
  </div>
  <!-- Mobile combined menu -->
  <div class="md:hidden">
    <details class="relative group">
      <summary class="list-none cursor-pointer">
        <?php if($user): ?>
        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 border-2 border-slate-200" style="background-image:url('assets/avatar.png');"></div>
        <?php else: ?>
        <span class="material-icons-outlined text-3xl text-[var(--primary-color)]">menu</span>
        <?php endif; ?>
      </summary>
      <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-2 flex flex-col gap-1">
        <a href="index.php#home" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Ana Sayfa</a>
        <a href="index.php#items-list" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Eşyaları Görüntüle</a>
        <?php if($user): ?>
          <a href="create-item.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Eşya Kaydet</a>
          <div class="border-t border-slate-200 my-1"></div>
          <a href="profile.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Profile</a>
          <a href="dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Dashboard</a>
          <a href="logout.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Logout</a>
        <?php else: ?>
          <a href="login.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Giriş Yap</a>
        <?php endif; ?>
      </div>
    </details>
  </div>
</header>