<?php
require_once '../includes/auth.php';
require_once '../includes/items.php';
require_once '../includes/db.php';
Auth::requireAdmin();

// Veritabanı bağlantısını başlat
$db = (new Database())->getConn();

// Yönetici kullanıcı bilgilerini al
$admin = Auth::user();
$adminName = $admin['name'] ?? 'Yönetici';

// Türkçe tarih formatı için yerel ayarı ayarla
if (strpos(setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'turkish', 'tr'), 'tr') === false) {
    // error_log("Türkçe yerel ayarı kullanılamıyor.");
}

// İstatistikler
$totalListings = Items::countAll();
$lostListings = Items::countByStatus('lost');
$foundListings = Items::countByStatus('found');
$resolvedListings = Items::countReunited();
$activeListings = $lostListings + $foundListings; // aktif = çözümlenmemiş

$totalUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();

$latest = array_slice(Items::getAll(), 0, 5);

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/theme.css" />
<title>Kampüs Emanet - Yönetici Kontrol Paneli</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/> <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style type="text/tailwindcss">
      :root {
        --primary-color:#111827;      /* Kenar Çubuğu Arka Planı */
        --secondary-color:#F3F4F6;    /* Sayfa Arka Planı */
        --primary-text-color:#111827; /* Açık Arka Planlardaki Ana Metin */
        --secondary-text-color:#6B7280;/* Açık Arka Planlardaki Soluk Metin */
        --text-primary:#FFFFFF;       /* Kenar Çubuğu Metin Rengi */
        --accent-color:#3B82F6;       /* Aktif Bağlantılar vb. için Vurgu Rengi */
        --border-color:#E5E7EB;       /* Kenarlık Rengi */
        --destructive-color:#EF4444;  /* Yıkıcı Renk */
        --destructive-hover-color:#DC2626; /* Yıkıcı Üzerine Gelme Rengi */
       }
      .nav-link-active {
        color: var(--accent-color) !important;
        font-weight: 600 !important;
      }
      #sidebarOverlay.hidden{display:none!important;opacity:0!important;}

      @media (max-width: 640px) {
        .table thead {
          display: none;
        }
        .table tr {
          display: block;
          margin-bottom: 1rem;
          border: 1px solid var(--border-color);
          border-radius: .5rem;
          box-shadow: 0 1px 2px rgba(0,0,0,.05);
          overflow: hidden;
        }
        .table td {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
          padding: .75rem 1rem;
          border: 0;
        }
        .table td::before {
          content: attr(data-label);
          font-weight: 600;
          color: var(--secondary-text-color);
          margin-right: 1rem;
        }
        .table tbody tr + tr td {
          border-top: 1px solid var(--border-color);
        }
      }
</style>
</head>
<body class="bg-[var(--secondary-color)]">
    <!-- Kenar Çubuğu Açma/Kapatma Düğmesi -->
    <button id="sidebarToggle" class="fixed top-4 left-4 z-50 p-2 rounded-md bg-white text-[var(--primary-color)] shadow-lg focus:outline-none md:hidden">
      <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
    </button>

    <!-- Küçük Ekranlar İçin Katman -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>
    
    <div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden">
      <!-- Kenar Çubuğu -->
      <div id="sidebar" class="fixed z-50 flex h-screen w-64 flex-col bg-[var(--primary-color)] text-[var(--text-primary)] p-6 shadow-lg transform transition-transform duration-300 -translate-x-full md:translate-x-0">
        <div class="flex flex-col items-center mb-8">
          <img src="../assets/avatar.png" alt="Avatar" class="h-16 w-16 mb-3 rounded-full border-2 border-white" />
          <h1 class="text-xl font-semibold"><?php echo htmlspecialchars($adminName); ?></h1>
          <p class="text-sm text-gray-400">Yönetici Paneli</p>
        </div>
        <nav class="flex flex-col gap-3 flex-grow">
          <a class="sidebar-link <?php echo $current==='dashboard.php'?'nav-link-active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="dashboard.php">
            <span class="material-icons-outlined text-gray-400">dashboard</span>
            <span>Kontrol Paneli</span>
          </a>
          <a class="sidebar-link <?php echo $current==='manage-users.php'?'nav-link-active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-users.php">
            <span class="material-icons-outlined text-gray-400">group</span>
            <span>Kullanıcıları Yönet</span>
          </a>
          <a class="sidebar-link <?php echo $current==='manage-listings.php'?'nav-link-active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-listings.php">
            <span class="material-icons-outlined text-gray-400">list_alt</span>
            <span>Eşyaları Yönet</span>
          </a>
        </nav>
        <div>
          <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="../logout.php">
            <span class="material-icons-outlined text-gray-400">logout</span>
            <span>Çıkış Yap</span>
          </a>
        </div>
      </div>

      <!-- İçerik Alanı -->
      <div id="contentArea" class="flex-1 ml-0 md:ml-64 flex flex-col transition-all duration-300">
        <!-- Kontrol Paneli Ana İçeriği --> 
          <div class="main-content p-4 md:p-8">
            <!-- Kontrol Paneli Başlığı -->
            <div class="mb-6 md:mb-8">
               <h1 class="dashboard-title text-[var(--primary-text-color)] text-2xl md:text-3xl font-bold">Kontrol Paneli</h1>
            </div>
            
            <!-- İstatistik Izgarası -->
            <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
              <!-- Toplam Eşya Kartı -->
              <div class="stats-card bg-white p-4 md:p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                  <p class="text-gray-500 text-sm font-medium mb-1">Toplam Eşya</p>
                  <p class="number text-[var(--primary-text-color)] text-3xl md:text-4xl font-bold"><?php echo $totalListings; ?></p>
                </div>
                <div class="mt-4 h-2 bg-gray-200 rounded-full">
                  <div class="h-2 bg-[var(--accent-color)] rounded-full" style="width: 100%;"></div>
                </div>
              </div>
              
              <!-- Aktif Eşya Kartı -->
              <div class="stats-card bg-white p-4 md:p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                  <p class="text-gray-500 text-sm font-medium mb-1">Aktif Eşya</p>
                  <p class="number text-[var(--primary-text-color)] text-3xl md:text-4xl font-bold"><?php echo $activeListings; ?></p>
                </div>
                <div class="mt-4 h-2 bg-gray-200 rounded-full">
                  <div class="h-2 bg-green-500 rounded-full" style="width: <?php echo $totalListings > 0 ? ($activeListings / $totalListings * 100) : 0; ?>%;"></div>
                </div>
              </div>
              
              <!-- Çözümlenmiş Eşya Kartı -->
              <div class="stats-card bg-white p-4 md:p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                  <p class="text-gray-500 text-sm font-medium mb-1">Çözümlenmiş Eşya</p>
                  <p class="number text-[var(--primary-text-color)] text-3xl md:text-4xl font-bold"><?php echo $resolvedListings; ?></p>
                </div>
                <div class="mt-4 h-2 bg-gray-200 rounded-full">
                  <div class="h-2 bg-blue-500 rounded-full" style="width: <?php echo $totalListings > 0 ? ($resolvedListings / $totalListings * 100) : 0; ?>%;"></div>
                </div>
              </div>
              
              <!-- Toplam Kullanıcı Kartı -->
              <div class="stats-card bg-white p-4 md:p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                  <p class="text-gray-500 text-sm font-medium mb-1">Toplam Kullanıcı</p>
                  <p class="number text-[var(--primary-text-color)] text-3xl md:text-4xl font-bold"><?php echo $totalUsers; ?></p>
                </div>
                <div class="mt-4 h-2 bg-gray-200 rounded-full">
                  <div class="h-2 bg-purple-500 rounded-full" style="width: 100%;"></div>
                </div>
              </div>
            </div>
            
            <!-- Son Kayıtlar Bölümü -->
            <div>
              <h2 class="section-title text-[var(--primary-text-color)] text-xl md:text-2xl font-semibold mb-4 md:mb-6">Son Kayıtlar</h2>
              <div class="bg-white shadow-md rounded-xl overflow-hidden @container">
                <div class="table-container overflow-x-auto">
                  <table class="w-full min-w-full">
                    <thead class="bg-gray-100">
                      <tr>
                        <th class="table-column-item px-3 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eşya</th>
                        <th class="table-column-description px-3 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
                        <th class="table-column-status px-3 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="table-column-date px-3 md:px-6 py-3 md:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bildirim Tarihi</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <?php if (empty($latest)): ?>
                        <tr>
                          <td colspan="4" class="px-3 md:px-6 py-8 text-center text-gray-500">
                            Son eşya bulunamadı
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach($latest as $item): ?>
                          <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="table-column-item px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-[var(--primary-text-color)]">
                              <?php echo htmlspecialchars($item['title']); ?>
                            </td>
                            <td class="table-column-description px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500 max-w-[200px] md:max-w-[250px]">
                              <div class="truncate" title="<?php echo htmlspecialchars($item['description']); ?>">
                                <?php echo htmlspecialchars(substr($item['description'], 0, 60)); ?>
                                <?php if (strlen($item['description']) > 60): ?>...<?php endif; ?>
                              </div>
                            </td>
                            <td class="table-column-status px-3 md:px-6 py-3 md:py-4">
                              <?php if($item['status'] === 'reunited'): ?>
                                <span class="status-resolved px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Çözüldü</span>
                              <?php elseif($item['status'] === 'lost'): ?>
                                <span class="status-lost px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Kayıp</span>
                              <?php elseif($item['status'] === 'found'): ?>
                                <span class="status-found px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Bulunan</span>
                              <?php else: ?>
                                <span class="status-active px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="table-column-date px-3 md:px-6 py-3 md:py-4 text-sm text-gray-500">
                              <?php echo strftime('%e %b %Y', strtotime($item['created_at'])); // Örn: 5 Oca 2024 ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </main>
        <!-- Orijinal Kontrol Paneli Ana İçeriği BURADA BİTİYOR -->
      </div>
    </div>

  <!-- JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      const sidebar=document.getElementById('sidebar');
      const content=document.getElementById('contentArea'); 
      const toggle=document.getElementById('sidebarToggle');
      const overlay = document.getElementById('sidebarOverlay');

      function openSidebar(){
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); 
      }
      function closeSidebar(){
        if (!sidebar || !overlay) return;
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden'); 
      }

      if(toggle){
        toggle.addEventListener('click',(e)=>{
          e.stopPropagation();
          if (sidebar && sidebar.classList.contains('-translate-x-full')){
            openSidebar();
          } else {
            closeSidebar();
          }
        });
      }

      if(overlay){
          overlay.addEventListener('click',closeSidebar);
      }
      
      if(sidebar){
        sidebar.querySelectorAll('a').forEach(link=>{
           link.addEventListener('click',()=>{
             setTimeout(() => {
                 if(window.innerWidth<768){
                     closeSidebar();
                 }
             }, 50);
           });
        });
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 768) {
            closeSidebar();
        }
      });

       window.addEventListener('resize', function() {
         if (window.innerWidth >= 768) { 
           if(overlay) overlay.classList.add('hidden');
           document.body.classList.remove('overflow-hidden');
         }
      });
    });
  </script>
</body>
</html>