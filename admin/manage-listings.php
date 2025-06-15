<?php
require_once '../includes/auth.php';
require_once '../includes/items.php';
Auth::requireAdmin();
$admin = Auth::user();
$adminName = $admin['name'] ?? 'Yönetici'; // İsim ayarlanmadıysa varsayılan olarak 'Yönetici'
$current = basename($_SERVER['PHP_SELF']);
$toast='';

// işlemleri yönet
if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    Items::adminDelete($id);
    $toast='Kayıt silindi';
}

$search = $_GET['search'] ?? '';

$userFilter=isset($_GET['user'])? (int)$_GET['user'] : null;

// Filtreleri uygula
$items = array_filter(Items::getAll(), function($it) use($search, $userFilter) {
    if ($search && stripos($it['title'], $search) === false) return false;
    if ($userFilter && $it['user_id'] !== $userFilter) return false;
    return true;
});

?>

<html><head>
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Kampüs Emanet</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="stylesheet" href="../assets/css/theme.css" />
<style type="text/tailwindcss">
      :root {
        --primary-color:#111827;--secondary-color:#F3F4F6;--primary-text-color:#111827;--secondary-text-color:#6B7280;--text-primary:#FFFFFF;--accent-color:#3B82F6;--border-color:#E5E7EB;--destructive-color:#EF4444;--destructive-hover-color:#DC2626;
       }
      .nav-link-active {
        color: var(--accent-color) !important;
        font-weight: 600 !important;
      }
      .table th {
        background-color: var(--light-gray);
        color: #111827;
        font-weight: 600;padding: 1rem 1.5rem;}
      .table td {
        padding: 1rem 1.5rem;border-bottom-width: 1px;
        border-color: var(--medium-gray);
      }
      .table tbody tr:last-child td {
        border-bottom-width: 0px;
      }
      .filter-button {
        background-color: white;
        border: 1px solid var(--medium-gray);
        color: #111827;
        transition: background-color 0.2s, color 0.2s;
      }
      .filter-button:hover {
         /* açılır menü açıkken metni koyu tut */
         background-color: var(--accent-color);
         color: #ffffff;
       }
      /* Üst öğe renginden bağımsız olarak seçenek metnini koyu tutmaya zorla */
      .filter-button option { color:#111827; }

      .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;font-size: 0.75rem;font-weight: 500;
      }
      .status-lost {
        background-color: #fee2e2;color: #b91c1c;} /* Kayıp durumu için */
      .status-found {
        background-color: #dcfce7;color: #166534;} /* Bulundu durumu için */
      .category-badge {
         padding: 0.25rem 0.75rem;
        border-radius: 9999px;font-size: 0.75rem;font-weight: 500;
        background-color: var(--light-gray);
        color: #111827;
      }
    </style>
</head>
<body class="bg-[var(--secondary-color)]">
    <!-- Kenar çubuğu açma/kapama düğmesi -->
    <button id="sidebarToggle" class="fixed top-4 left-4 z-50 p-2 rounded-md bg-white text-[var(--primary-color)] shadow-lg focus:outline-none md:hidden">
      <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
    </button>
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
<?php if($toast): ?>
<script>document.addEventListener('DOMContentLoaded',()=>{const t=document.createElement('div');t.textContent=<?php echo json_encode($toast); ?>;t.className='bg-green-600 text-white px-4 py-2 rounded shadow animate-slide-in';document.getElementById('toastContainer').appendChild(t);setTimeout(()=>{t.classList.add('opacity-0');setTimeout(()=>t.remove(),500);},3500);});</script>
<?php endif; ?>
<!-- Küçük ekranlar için katman -->
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
      <a class="sidebar-link <?php echo $current==='dashboard.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="dashboard.php"><span class="material-icons-outlined text-gray-400">dashboard</span>Kontrol Paneli</a>
      <a class="sidebar-link <?php echo $current==='manage-users.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-users.php"><span class="material-icons-outlined text-gray-400">group</span>Kullanıcıları Yönet</a>
      <a class="sidebar-link <?php echo $current==='manage-listings.php'?'active':'';?> flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="manage-listings.php"><span class="material-icons-outlined text-gray-400">list_alt</span>İlanları Yönet</a>
    </nav>
    <div>
      <a class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg hover:text-white transition-colors" href="../logout.php"><span class="material-icons-outlined text-gray-400">logout</span>Çıkış Yap</a>
    </div>
  </div>
  <!-- İçerik Alanı -->
  <div id="contentArea" class="flex-1 ml-0 md:ml-64 flex flex-col transition-all duration-300">
    <main class="flex-1 px-8 py-10">

      <div class="layout-content-container mx-auto flex max-w-7xl flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
           <h1 class="text-gray-800 text-3xl font-bold leading-tight">İlan Yönetimi</h1>
           <div class="flex items-center gap-4">
             <div class="relative">
               <input 
                 type="text" 
                 name="search" 
                 value="<?php echo htmlspecialchars($search); ?>"
                 placeholder="Başlıkla ara..."
                 class="form-input w-64"
                 oninput="this.form.submit()"
               >
             </div>
           </div>
         </div>

         <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm @container">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">İlan Başlığı</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Bildiren</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Durum</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">Gönderim Tarihi</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-900">İşlemler</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            <?php foreach($items as $item): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="İlan Başlığı">
                  <div class="flex items-center">
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($item['title']); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Bildiren">
                  <?php echo htmlspecialchars($item['reporter']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap" data-label="Kategori">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <?php echo htmlspecialchars($item['category']); ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap" data-label="Durum">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    <?php 
                      $status = htmlspecialchars($item['status']);
                      if ($status === 'lost') echo 'bg-red-100 text-red-800';
                      elseif ($status === 'found') echo 'bg-green-100 text-green-800';
                      else echo 'bg-yellow-100 text-yellow-800'; // Varsayılan durum için (örn: reunited)
                    ?>">
                    <?php 
                      echo str_replace(['lost', 'found', 'reunited'], ['Kayıp', 'Bulunan', 'Sahibine Ulaştı'], $status);
                    ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Gönderim Tarihi">
                  <?php echo date('d.m.Y', strtotime($item['created_at'])); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="İşlemler">
                  <div class="flex items-center space-x-2">
                    <a href="edit-item.php?id=<?php echo $item['id']; ?>" 
                       class="inline-flex items-center px-2 py-1 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Düzenle
                    </a>
                    <a href="../item-detail.php?id=<?php echo $item['id']; ?>" 
                       class="inline-flex items-center px-2 py-1 border border-transparent rounded-md text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      İncele
                    </a>
                    <button onclick="if(confirm('Bu ilanı silmek istediğinizden emin misiniz?')) location='?delete=<?php echo $item['id']; ?>'" 
                            class="inline-flex items-center px-2 py-1 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Sil
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <style>
          /* Seçim kutusu stilleri */
          select {
            @apply w-full rounded-lg border border-[var(--border-color)] bg-[var(--input-background)] p-3 text-sm text-[var(--text-primary)] placeholder:text-[var(--muted-foreground)] focus:border-[var(--primary-color)] focus:ring-1 focus:ring-[var(--primary-color)] transition-colors duration-200;
          }
          .form-input {
            @apply w-full rounded-lg border border-[var(--border-color)] bg-[var(--input-background)] p-3 text-sm text-[var(--text-primary)] placeholder:text-[var(--muted-foreground)] focus:border-[var(--primary-color)] focus:ring-1 focus:ring-[var(--primary-color)] transition-colors duration-200;
          }

          /* Arama girişindeki metnin koyu kalmasını sağla */
          input[type="text"] {
            color: #111827 !important;
          }

          /* Yer tutucu metnin koyu olmasını sağla */
          input[type="text"]::placeholder {
            color: #6b7280;
          }

          select:hover {
            border-color: var(--accent-color);
            color: inherit; /* Metni koyu tut */
          }

          
          /* Seçenek metnini koyu tutmaya zorla */
          select option {
            color: #111827;
          }

          /* Kapsayıcı küçüldükçe sütunları aşamalı olarak gizle */
          @container(max-width:120px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-120{display: none;}}
          @container(max-width:240px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-240{display: none;}}
          @container(max-width:360px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-360{display: none;}}
          @container(max-width:480px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-480{display: none;}}
          @container(max-width:600px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-600{display: none;}}
          @container(max-width:720px){.table-2d3a4fd6-96c3-4da0-8ac8-09e8455b3dd2-column-720{display: none;}}

          /* Tablo satırları için mobil öncelikli kart stili */
          @media (max-width: 640px) {
            /* Kenar çubuğu katmanı aktif değilken gizli olduğundan emin ol */
            #sidebarOverlay.hidden{display:none!important;opacity:0!important;}

            /* Yatay taşmayı önle */
            .table tr{width:100%;}
            .table td,.table select{max-width:100%;}

            /* Filtreleri tam genişlik yap */
            .layout-content-container form .filter-button{width:100%;}
            /* Yatay kaydırmayı engelle */
            html,body{overflow-x:hidden;}
            .overflow-x-auto{overflow-x:visible;}
            .table{width:100%;}

            .layout-content-container form{width:100%;}
            .layout-content-container form>div{flex:1 1 100%;}
            /* Kenar çubuğu gizliyken içeriğin ekranı doldurduğundan emin ol */
            #contentArea{margin-left:0!important;width:100%!important;}


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
              content: attr(data-label); /* data-label'ı kullan */
              font-weight: 600;
              color: var(--secondary-text-color);
              margin-right: 1rem;
            }
            .table tbody tr + tr td {
              border-top: 1px solid var(--border-color);
            }
          }
        </style>
      </div>
    </div>
  </div>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
// Duyarlı kenar çubuğu mantığı
 document.addEventListener('DOMContentLoaded',function(){
   const sidebar=document.getElementById('sidebar');
   const content=document.getElementById('contentArea');
   const toggle=document.getElementById('sidebarToggle');

    // Katman öğesi
    const overlay = document.getElementById('sidebarOverlay');

   function openSidebar(){
     sidebar.classList.remove('-translate-x-full');
     overlay.classList.remove('hidden');
     document.body.classList.add('overflow-hidden'); // Kaydırmayı engelle
   }
   function closeSidebar(){
     sidebar.classList.add('-translate-x-full');
     overlay.classList.add('hidden');
     document.body.classList.remove('overflow-hidden'); // Kaydırmaya izin ver
   }

   if(toggle){
     toggle.addEventListener('click',()=>{
       if(sidebar.classList.contains('-translate-x-full')){
         openSidebar();
       }else{
         closeSidebar();
       }
     });
   }

   // Katmana tıklandığında kapat
   overlay.addEventListener('click',closeSidebar);

   // Küçük ekranlarda bir bağlantı seçildikten sonra kapat
   sidebar.querySelectorAll('a').forEach(link=>{
     link.addEventListener('click',()=>{
       if(window.innerWidth<768){ // md breakpoint
         closeSidebar();
       }
     });
   });
 });

 
</script>

</div>

</body>
</html>