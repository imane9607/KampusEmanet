<?php
require_once '../includes/auth.php';
require_once '../includes/users.php';
require_once '../includes/items.php';
require_once '../includes/db.php';
Auth::requireAdmin();

// Veritabanı bağlantısını başlat
$db = (new Database())->getConn();

$toast='';
$current = basename($_SERVER['PHP_SELF']);
$admin = Auth::user();
$adminName = $admin['name'] ?? 'Yönetici'; // İsim ayarlanmadıysa varsayılan olarak 'Yönetici'
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'id';
$dir = $_GET['dir'] ?? 'desc';
// İşlem yöneticileri

if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    Users::delete($id);
    $toast='Kullanıcı silindi';
}

$users=Users::getAll();
// arama filtresi
if($search !== ''){
    $users=array_filter($users,function($u) use($search){
        return stripos($u['name'],$search)!==false || stripos($u['email'],$search)!==false || (string)$u['id']===$search;
    });
}
// sırala
usort($users,function($a,$b) use($sort,$dir){
    $valA=$a[$sort] ?? $a['id'];
    $valB=$b[$sort] ?? $b['id'];
    if($sort==='name' || $sort==='email'){
        $cmp= strcasecmp($valA,$valB);
    } else {
        $cmp= $valA <=> $valB;
    }
    return $dir==='asc'? $cmp : -$cmp;
});

$perPage=10;
$page=max(1,(int)($_GET['p']??1));

$totalUsers=count($users);
$totalPages=max(1,ceil($totalUsers/$perPage));
$users=array_slice($users,($page-1)*$perPage,$perPage);

function page_link($p,$search,$sort,$dir){
  $params=['p'=>$p];
  if($search!=='') $params['q']=$search;
  if($sort) $params['sort']=$sort;
  if($dir) $params['dir']=$dir;
  return '?'.http_build_query($params);
}
?>
<!DOCTYPE html>
<html><head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/theme.css" />
<title>Kampüs Emanet</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style type="text/tailwindcss">
      :root {--primary-color:#111827;--secondary-color:#F3F4F6;--primary-text-color:#111827;--secondary-text-color:#6B7280;--text-primary:#FFFFFF;--accent-color:#3B82F6;--border-color:#E5E7EB;--destructive-color:#EF4444;--destructive-hover-color:#DC2626;}
      body{font-family:"Plus Jakarta Sans","Noto Sans",sans-serif;}
      .sidebar-link:hover{background-color:#374151;}
      .sidebar-link.active{background-color:var(--accent-color);color:var(--text-primary);font-weight:500;}
      .sidebar-link.active .material-icons-outlined,.sidebar-link.active .material-icons{color:var(--text-primary);}
      .admin-nav-link {
        @apply text-[var(--primary-text-color)] text-sm font-medium leading-normal hover:text-indigo-600 transition-colors;
      }
      .admin-nav-link-active {
        @apply text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1;
      }
      .table-header {
        @apply px-3 sm:px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-100;
      }
      .table-cell {
        @apply px-3 sm:px-6 py-4 text-sm text-gray-800 border-b border-[var(--border-color)];
      }
      .table-cell-primary {
        @apply px-3 sm:px-6 py-4 text-sm font-medium text-gray-800 border-b border-[var(--border-color)];
      }
      .action-link {
        @apply text-indigo-600 hover:text-indigo-800 font-medium transition-colors;
      }
      .destructive-button {
        @apply text-white bg-[var(--destructive-color)] hover:bg-[var(--destructive-hover-color)] font-medium rounded-md text-xs sm:text-sm px-2 sm:px-3 py-1 sm:py-1.5 text-center transition-colors;
      }
      
      /* Mobil tablo duyarlılığı */
      @media (max-width: 640px) {
        .mobile-card {
          @apply block bg-white rounded-lg shadow mb-4 p-4;
        }
        .mobile-hide {
          @apply hidden;
        }
        .mobile-stack {
          @apply flex flex-col space-y-2;
        }
      }
    </style>
</head>
<body class="bg-[var(--secondary-color)]" >
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Mobil için kenar çubuğu açma/kapama düğmesi -->
<button id="sidebarToggle" class="fixed top-4 left-4 z-50 p-2 rounded-md bg-white text-[var(--primary-color)] shadow-lg focus:outline-none lg:hidden">
  <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path id="hamburgerPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
  </svg>
</button>

<!-- Mobil için katman -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

<div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden">
  <div class="flex h-full grow">
    <!-- Kenar Çubuğu -->
    <div id="sidebar" class="fixed z-50 flex h-screen w-64 flex-col bg-[var(--primary-color)] text-[var(--text-primary)] p-6 shadow-lg transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
         <div class="flex flex-col items-center mb-8">
           <img src="../assets/avatar.png" alt="Yönetici Avatarı" class="h-16 w-16 mb-3 rounded-full border-2 border-white">
           <h1 class="text-xl font-semibold text-center"><?php echo htmlspecialchars($adminName); ?></h1>
           <p class="text-sm text-gray-400">Yönetici Paneli</p>
         </div>
      <nav class="flex flex-col gap-3 flex-grow">
        <a class="sidebar-link <?php echo $current==='dashboard.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="dashboard.php">
          <span class="material-icons-outlined text-gray-400">dashboard</span>Kontrol Paneli
        </a>
        <a class="sidebar-link <?php echo $current==='manage-users.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-users.php">
          <span class="material-icons-outlined text-gray-400">group</span>Kullanıcıları Yönet
        </a>
        <a class="sidebar-link <?php echo $current==='manage-listings.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-listings.php">
          <span class="material-icons-outlined text-gray-400">list_alt</span>İlanları Yönet
        </a>
      </nav>
      <div>
        <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="../logout.php">
          <span class="material-icons-outlined text-gray-400">logout</span>Çıkış Yap
        </a>
      </div>
    </div>

    <!-- Ana İçerik -->
    <div class="flex-1 lg:ml-64 min-h-screen">
      <main class="px-4 sm:px-6 lg:px-8 py-6 lg:py-10">
        <?php if($toast): ?>
        <script>document.addEventListener('DOMContentLoaded',()=>showToast(<?php echo json_encode($toast); ?>));</script>
        <?php endif; ?>
        
        <!-- Başlık Bölümü -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4 mb-6 bg-white shadow rounded-lg mt-16 lg:mt-0">
          <h2 class="text-[var(--primary-text-color)] text-xl sm:text-2xl font-semibold leading-tight">Kullanıcı Yönetimi</h2>
          <form method="get" class="relative w-full sm:w-auto sm:max-w-xs">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1111.472 3.304l4.112 4.112a1 1 0 01-1.414 1.414l-4.112-4.112A6 6 0 012 8z" clip-rule="evenodd" />
              </svg>
            </div>
            <input name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-input w-full pl-10 pr-3 py-2.5 text-sm text-gray-800 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-500 bg-white" placeholder="Kullanıcıları ara..." type="search"/>
          </form>
        </div>

        <!-- Masaüstü Tablo -->
        <div class="hidden sm:block bg-white shadow-lg rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="table-header text-gray-500" scope="col"><?php 
                  function sort_link($label,$field,$sort,$dir,$search){
                    $newDir= ($sort==$field && $dir==='asc')? 'desc':'asc';
                    $icon=$sort==$field? ($dir==='asc'?'↑':'↓') : '';
                    $url='?sort='.$field.'&dir='.$newDir.'&q='.urlencode($search);
                    return '<a href="'.$url.'" class="flex items-center gap-1 hover:text-indigo-600">'.$label.'<span>'.$icon.'</span></a>';
                  }
                  echo sort_link('Kullanıcı ID','id',$sort,$dir,$search); ?></th>
                  <th class="table-header text-gray-500" scope="col">Avatar</th>
                  <th class="table-header text-gray-500" scope="col"><?php echo sort_link('İsim','name',$sort,$dir,$search); ?></th>
                  <th class="table-header text-gray-500 hidden md:table-cell" scope="col"><?php echo sort_link('E-posta','email',$sort,$dir,$search); ?></th>
                  <th class="table-header text-gray-500 hidden lg:table-cell" scope="col"><?php echo sort_link('Katılım Tarihi','created_at',$sort,$dir,$search); ?></th>
                  <th class="table-header text-center text-gray-500" scope="col">İşlemler</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($users as $u): ?>
                <tr class="hover:bg-gray-50">
                  <td class="table-cell text-gray-800">#<?php echo $u['id']; ?></td>
                  <td class="table-cell">
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-8 mx-auto" style="background-image:url('../assets/avatar.png');"></div>
                  </td>
                  <td class="table-cell-primary text-gray-800">
                    <button type="button" class="user-detail text-indigo-600 hover:underline font-medium" 
                        data-id="<?php echo $u['id']; ?>" 
                        data-name="<?php echo htmlspecialchars($u['name']); ?>" 
                        data-email="<?php echo htmlspecialchars($u['email']); ?>" 
                        data-role="<?php echo htmlspecialchars($u['role']??'user'); ?>" 
                        data-phone="<?php echo htmlspecialchars($u['phone']??''); ?>" 
                        data-city="<?php echo htmlspecialchars($u['city']??''); ?>" 
                        data-gender="<?php echo htmlspecialchars($u['gender']??''); ?>" 
                        data-date="<?php echo date('Y-m-d', strtotime($u['created_at'])); ?>" 
                        data-status="<?php echo $u['status']??'active'; ?>" 
                        data-lists="<?php echo Items::countByUser($u['id']); ?>"
                    ><?php echo htmlspecialchars($u['name']); ?></button>
                  </td>
                  <td class="table-cell text-gray-800 hidden md:table-cell"><?php echo htmlspecialchars($u['email']); ?></td>
                  <td class="table-cell text-gray-800 hidden lg:table-cell"><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                  <td class="table-cell text-center">
                    <div class="flex items-center justify-center space-x-2">
                      <button onclick="if(confirm('Kullanıcıyı silmek istediğinizden emin misiniz?')) location='?delete=<?php echo $u['id']; ?>'" class="destructive-button">Sil</button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobil Kartlar -->
        <div class="sm:hidden space-y-4">
          <?php foreach($users as $u): ?>
          <div class="mobile-card border border-gray-200">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center space-x-3">
                <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" style="background-image:url('../assets/avatar.png');"></div>
                <div>
                  <button type="button" class="user-detail text-indigo-600 hover:underline font-medium text-left" 
                      data-id="<?php echo $u['id']; ?>" 
                      data-name="<?php echo htmlspecialchars($u['name']); ?>" 
                      data-email="<?php echo htmlspecialchars($u['email']); ?>" 
                      data-role="<?php echo htmlspecialchars($u['role']??'user'); ?>" 
                      data-phone="<?php echo htmlspecialchars($u['phone']??''); ?>" 
                      data-city="<?php echo htmlspecialchars($u['city']??''); ?>" 
                      data-gender="<?php echo htmlspecialchars($u['gender']??''); ?>" 
                      data-date="<?php echo date('Y-m-d', strtotime($u['created_at'])); ?>" 
                      data-status="<?php echo $u['status']??'active'; ?>" 
                      data-lists="<?php echo Items::countByUser($u['id']); ?>"
                  ><?php echo htmlspecialchars($u['name']); ?></button>
                  <p class="text-sm text-gray-500">#<?php echo $u['id']; ?></p>
                </div>
              </div>
              <button onclick="if(confirm('Kullanıcıyı silmek istediğinizden emin misiniz?')) location='?delete=<?php echo $u['id']; ?>'" class="destructive-button">Sil</button>
            </div>
            <div class="mobile-stack text-sm text-gray-600">
              <p><span class="font-medium">E-posta:</span> <?php echo htmlspecialchars($u['email']); ?></p>
              <p><span class="font-medium">Katıldı:</span> <?php echo date('M j, Y', strtotime($u['created_at'])); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Sayfalandırma -->
        <?php if($totalPages>1): ?>
        <div class="mt-6 flex justify-center">
          <nav aria-label="Sayfalandırma" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
            <?php $prev=$page-1; ?>
            <a class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium <?php echo $prev<1?'text-gray-300 cursor-not-allowed':'text-gray-500 hover:bg-gray-50'; ?>" href="<?php echo $prev>=1?page_link($prev,$search,$sort,$dir):'#'; ?>">
              <span class="sr-only">Önceki</span>
              <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path>
              </svg>
            </a>
            <?php
            $range=2; // mevcut sayfa etrafındaki sayfalar
            $start=max(1,$page-$range);
            $end=min($totalPages,$page+$range);
            if($start>1){
              echo '<a class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium" href="'.page_link(1,$search,$sort,$dir).'">1</a>'; 
              if($start>2) echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"> ... </span>'; 
            }
            for($i=$start;$i<=$end;$i++){
              $active=$i==$page;
              echo '<a '.($active?'aria-current="page"':'').' class="'.($active?'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 ':'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 ').'relative inline-flex items-center px-4 py-2 border text-sm font-medium" href="'.page_link($i,$search,$sort,$dir).'"> '.$i.' </a>'; 
            }
            if($end<$totalPages){
              if($end<$totalPages-1) echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"> ... </span>'; 
              echo '<a class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium" href="'.page_link($totalPages,$search,$sort,$dir).'"> '.$totalPages.' </a>'; 
            }
            $next=$page+1;
            ?>
            <a class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium <?php echo $next>$totalPages?'text-gray-300 cursor-not-allowed':'text-gray-500 hover:bg-gray-50'; ?>" href="<?php echo $next<=$totalPages?page_link($next,$search,$sort,$dir):'#'; ?>">
              <span class="sr-only">Sonraki</span>
              <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" fill-rule="evenodd"></path>
              </svg>
            </a>
          </nav>
        </div>
        <?php endif; ?>
      </main>
    </div>
  </div>
</div>

<!-- Kullanıcı detay modalı -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative max-h-screen overflow-y-auto">
    <button id="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 p-1">
      <span class="material-icons">close</span>
    </button>
    <h3 class="text-xl font-semibold mb-4 pr-8">Kullanıcı Detayları</h3>
    <div class="space-y-3 text-sm text-gray-700">
      <div class="grid grid-cols-1 gap-2">
        <p><span class="font-medium text-gray-900">ID:</span> <span id="ud-id" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">İsim:</span> <span id="ud-name" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">E-posta:</span> <span id="ud-email" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Rol:</span> <span id="ud-role" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Telefon:</span> <span id="ud-phone" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Şehir:</span> <span id="ud-city" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Cinsiyet:</span> <span id="ud-gender" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">İlan Sayısı:</span> <span id="ud-lists" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Durum:</span> <span id="ud-status" class="text-gray-600"></span></p>
        <p><span class="font-medium text-gray-900">Oluşturulma Tarihi:</span> <span id="ud-date" class="text-gray-600"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
// Kenar çubuğu açma/kapama işlevselliği
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const toggleIcon = document.getElementById('toggleIcon');
const hamburgerPath = document.getElementById('hamburgerPath');

function openSidebar() {
  sidebar.classList.remove('-translate-x-full');
  overlay.classList.remove('hidden');
  hamburgerPath.setAttribute('d', 'M6 18L18 6M6 6l12 12'); // Kapat (X) ikonu
}

function closeSidebar() {
  sidebar.classList.add('-translate-x-full');
  overlay.classList.add('hidden');
  hamburgerPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16'); // Hamburger ikonu
}

sidebarToggle.addEventListener('click', function() {
  if (sidebar.classList.contains('-translate-x-full')) {
    openSidebar();
  } else {
    closeSidebar();
  }
});

// Katmana tıklandığında kenar çubuğunu kapat
overlay.addEventListener('click', closeSidebar);

// Mobilde dışarıya tıklandığında kenar çubuğunu kapat
document.addEventListener('click', function(e) {
  if (window.innerWidth < 1024) { // Sadece mobil/tablet üzerinde (lg breakpoint)
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
      closeSidebar();
    }
  }
});

// Pencere yeniden boyutlandırmayı yönet
window.addEventListener('resize', function() {
  if (window.innerWidth >= 1024) { // Masaüstü
    sidebar.classList.remove('-translate-x-full'); // Kenar çubuğunu göster
    overlay.classList.add('hidden'); // Katmanı gizle
    hamburgerPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16'); // Hamburger ikonuna sıfırla (gerekirse)
  } else { // Mobil/tablet
    if (!overlay.classList.contains('hidden')) {
      // Kenar çubuğu mobilde açık, açık tut (veya ikon durumunu koru)
      sidebar.classList.remove('-translate-x-full');
    } else {
      // Kenar çubuğu mobilde kapalı
      sidebar.classList.add('-translate-x-full');
      hamburgerPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16'); // Hamburger ikonuna sıfırla
    }
  }
});

// Kullanıcı detay modalı işlevselliği
document.querySelectorAll('.user-detail').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('ud-id').textContent = '#' + btn.dataset.id;
    document.getElementById('ud-name').textContent = btn.dataset.name;
    document.getElementById('ud-email').textContent = btn.dataset.email;
    document.getElementById('ud-role').textContent = btn.dataset.role;
    document.getElementById('ud-phone').textContent = btn.dataset.phone || 'Belirtilmemiş';
    document.getElementById('ud-city').textContent = btn.dataset.city || 'Belirtilmemiş';
    document.getElementById('ud-gender').textContent = btn.dataset.gender || 'Belirtilmemiş';
    document.getElementById('ud-lists').textContent = btn.dataset.lists;
    document.getElementById('ud-status').textContent = btn.dataset.status;
    document.getElementById('ud-date').textContent = btn.dataset.date;
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModal').classList.add('flex');
  });
});

document.getElementById('closeModal').addEventListener('click', () => {
  const modal = document.getElementById('userModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
});

// Modal dışına tıklandığında kapat
document.getElementById('userModal').addEventListener('click', e => {
  if (e.target === e.currentTarget) { // Sadece modalın kendisine (içeriğine değil) tıklandığında
    e.currentTarget.classList.add('hidden');
    e.currentTarget.classList.remove('flex');
  }
});

// Bildirim (toast) fonksiyonu
function showToast(msg) {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.textContent = msg;
  toast.className = 'bg-green-600 text-white px-4 py-2 rounded shadow-lg transform transition-all duration-300 translate-x-full';
  container.appendChild(toast);
  
  // Animasyonu tetikle
  setTimeout(() => {
    toast.classList.remove('translate-x-full');
  }, 100);
  
  // Otomatik gizle
  setTimeout(() => {
    toast.classList.add('translate-x-full'); // Tekrar sağa kaydırarak gizle
    setTimeout(() => toast.remove(), 300); // DOM'dan kaldır
  }, 3500);
}
</script>
</body></html>