<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <title>Kampüs Emanet</title>
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="stylesheet" href="assets/css/theme.css" />
  <style type="text/tailwindcss">
    :root {
      --primary-color: #0c7ff2;
      --muted-foreground: #6b7280;
      --border-color: #e5e7eb;
      --card-background: #ffffff;
      --input-background: #f9fafb;
      --text-primary: #1f2937;
      --text-secondary: #4b5563;
    }
    body {
      font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;
    }
    .btn-primary {
      @apply bg-[var(--primary-color)] text-white hover:bg-blue-700 transition-colors duration-200;
    }
  </style>
</head>
<body class="bg-slate-50 text-[var(--text-primary)]">
  <?php
  require_once 'includes/auth.php';
  require_once 'includes/items.php';
  $user = Auth::user();
  $items = Items::getAll();
  ?>
  <div class="relative flex min-h-screen flex-col">
    <?php include 'includes/header.php'; ?>

    <!-- Hero -->
    <section class="relative flex flex-1 items-center justify-center bg-center bg-cover" style="background-image:url('assets/img.png');">
      <div class="absolute inset-0 bg-black/50"></div>
      <div class="relative z-10 max-w-3xl text-center px-4 sm:px-6 lg:px-8 py-24">
        <h1 class="text-white text-4xl sm:text-5xl font-extrabold tracking-tight mb-6">Eşyalarınızla Yeniden Buluşun</h1>
        <p class="text-white/90 text-lg mb-8">Kampüs genelindeki kayıp ve bulunan eşya hizmetimiz, öğrenciler ve personelin kaybettiği eşyalarını hızlı ve verimli bir şekilde geri bulmalarına yardımcı olur.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="#items-list" class="btn-primary rounded-lg px-6 py-3 text-base font-medium">Kayıp Eşya Bildir</a>
          <a href="#items-list" class="rounded-lg bg-white/90 text-[var(--primary-color)] px-6 py-3 text-base font-medium hover:bg-white transition-colors duration-200">Bir Eşya Buldum</a>
        </div>
      </div>
    </section>

    <!-- Items Listing -->
    <section id="items-list" class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg aria-hidden="true" class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" fill-rule="evenodd"></path></svg>
            </div>
            <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)] sm:text-sm pl-10 py-3 placeholder:text-gray-400" id="search" placeholder="Eşya ara..." type="search" oninput="filterItems()" />
          </div>
          <select id="statusFilter" onchange="filterItems()" class="w-full sm:w-48 rounded-lg border-gray-300 shadow-sm focus:border-[var(--primary-color)] focus:ring-[var(--primary-color)] py-3 px-4 text-sm">
            <option value="all">Tüm Durumlar</option>
            <option value="lost">Kayıp</option>
            <option value="found">Bulunan</option>
            <option value="reunited">Yeniden Buluşan</option>
          </select>
        </div>
      </div>
      <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($items as $it): $img = Items::firstImage($it['id']) ?: 'assets/img/placeholder.png'; ?>
          <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all hover:shadow-2xl flex flex-col" data-status="<?= $it['status']; ?>">
            <div class="relative">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['title']) ?>" class="w-full h-48 object-cover object-center" />
              <span class="absolute top-2 left-2 px-3 py-1.5 text-sm font-semibold rounded-full <?= $it['status']==='lost' ? 'bg-red-100 text-red-600' : ($it['status']==='found' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'); ?>"><?= strtoupper($it['status']) ?></span>
            </div>
            <div class="p-5 flex flex-col flex-grow">
              <h3 class="text-lg font-semibold text-[var(--text-primary)] mb-1 truncate"><?= htmlspecialchars($it['title']) ?></h3>
              <?php $desc=strip_tags($it['description']); if(strlen($desc)>90) $desc=substr($desc,0,87).'...'; ?>
              <p class="text-sm text-[var(--text-secondary)] mb-3"><?= htmlspecialchars($desc); ?></p>
              <div class="mt-auto flex justify-center">
                <a href="item-detail.php?id=<?= $it['id']; ?>" class="btn-primary px-4 py-2 text-sm rounded">View</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p id="noResults" class="hidden text-center text-[var(--text-secondary)] mt-8">Arama kriterinize uygun eşya bulunamadı.</p>
    </section>

    <!-- Features -->
    <section class="relative isolate overflow-hidden bg-gradient-to-b from-slate-50 to-white py-14 sm:py-20">
      <div class="absolute inset-0 -z-10 bg-[url('assets/shape.svg')] bg-no-repeat bg-top opacity-10 pointer-events-none"></div>
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
          <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[var(--text-primary)] mb-4">Neden Kampüs Emanet?</h2>
        </div>

        <div class="grid gap-12 md:grid-cols-3">
          <!-- Card -->
          <div class="group relative flex flex-col items-center rounded-3xl bg-white p-6 shadow-lg transition-transform hover:-translate-y-2 hover:shadow-2xl">
            <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-blue-50 text-[var(--primary-color)] group-hover:bg-[var(--primary-color)] group-hover:text-white transition-colors">
              <span class="material-icons-outlined text-4xl">search</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Kolay Arama</h3>
          </div>

          <!-- Card -->
          <div class="group relative flex flex-col items-center rounded-3xl bg-white p-6 shadow-lg transition-transform hover:-translate-y-2 hover:shadow-2xl">
            <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-blue-50 text-[var(--primary-color)] group-hover:bg-[var(--primary-color)] group-hover:text-white transition-colors">
              <span class="material-icons-outlined text-4xl">campaign</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Saniyelerde Bildir</h3>
          </div>

          <!-- Card -->
          <div class="group relative flex flex-col items-center rounded-3xl bg-white p-6 shadow-lg transition-transform hover:-translate-y-2 hover:shadow-2xl">
            <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-blue-50 text-[var(--primary-color)] group-hover:bg-[var(--primary-color)] group-hover:text-white transition-colors">
              <span class="material-icons-outlined text-4xl">handshake</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Topluluk Tabanlı</h3>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 text-center text-sm text-[var(--text-secondary)] border-t border-[var(--border-color)] bg-white">
      &copy; 2024 Kampüs Emanet. Tüm hakları saklıdır.
    </footer>
  </div>
  <script>
    (()=>{
      const header=document.getElementById('siteHeader');
      if(!header) return;
      let last=window.scrollY;
      window.addEventListener('scroll',()=>{
        const cur=window.scrollY;
        if(cur>last && cur>100){
          header.classList.add('-translate-y-full');
        }else{
          header.classList.remove('-translate-y-full');
        }
        last=cur;
      });
    })();
    function filterItems(){
      const term=document.getElementById('search').value.toLowerCase();
      const status=document.getElementById('statusFilter').value;
      const cards=document.querySelectorAll('#itemsGrid > div');
      let visibleCount=0;
      cards.forEach(card=>{
        const text=card.textContent.toLowerCase();
        const cardStatus=card.dataset.status;
        const matchText=text.includes(term);
        const matchStatus=(status==='all'||status===cardStatus);
        card.style.display=(matchText&&matchStatus)?'':'none';
        if(matchText&&matchStatus) visibleCount++;
      });
      document.getElementById('noResults').classList.toggle('hidden',visibleCount!==0);
    }
  </script>
</body>
</html>