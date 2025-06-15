<?php
require_once 'includes/auth.php';
require_once 'includes/items.php';
Auth::requireLogin();
$user = Auth::user();
$msg = '';
$uploadsDir='uploads/';
if(!is_dir($uploadsDir)) mkdir($uploadsDir);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'lost';
    $date   = $_POST['date-lost-found'] ?? date('Y-m-d');
    $location = $_POST['location'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $imagePath=null;
    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $name=uniqid('img_').'.'.pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
        $dest=$uploadsDir.$name;
        if(move_uploaded_file($_FILES['image']['tmp_name'],$dest)){
            $imagePath=$dest;
        }
    }
    if ($title === '') {
        $msg = 'Başlık zorunludur';
    } else {
        $itemId=Items::create($user['id'], $title, $description,$category,$location, $status,$date,$imagePath);
        if($itemId){
            header('Location: listlostfound.php?lang=tr');exit;
        } else { $msg='Eşya kaydedilirken hata oluştu'; }
    }
}
?>
<html><head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/theme.css" />
<title>Kampüs Emanet - Eşya Kaydet</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style type="text/tailwindcss">
      :root {
        --primary-color: #0c7ff2;
        --muted-foreground: #6b7280;--border-color: #e5e7eb;--card-background: #ffffff;--input-background: #f9fafb;--text-primary: #1f2937;--text-secondary: #4b5563;}
      body {
        font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;
      }
      .form-input {
        @apply w-full rounded-lg border border-[var(--border-color)] bg-[var(--input-background)] p-3 text-sm text-[var(--text-primary)] placeholder:text-[var(--muted-foreground)] focus:border-[var(--primary-color)] focus:ring-1 focus:ring-[var(--primary-color)] transition-colors duration-200;
      }
      .form-label {
        @apply mb-1.5 text-sm font-medium text-[var(--text-primary)];
      }
      .radio-label {
        @apply flex items-center gap-3 rounded-lg border border-[var(--border-color)] p-4 cursor-pointer hover:border-[var(--primary-color)] transition-colors duration-200;
      }
      .radio-input {
        @apply h-5 w-5 appearance-none rounded-full border-2 border-[var(--border-color)] checked:border-[var(--primary-color)] checked:bg-[var(--primary-color)] checked:ring-2 checked:ring-offset-2 checked:ring-[var(--primary-color)] focus:outline-none focus:ring-0 focus:ring-offset-0;
      }
       .radio-input:checked::after {
        content: '';
        display: block;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        background-color: white;
        position: relative;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
      }
      .date-picker-button {
        @apply h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-700;
      }
      .date-picker-day {
        @apply h-10 w-full flex items-center justify-center rounded-full text-sm;
      }
      .date-picker-day-selected {
        @apply bg-[var(--primary-color)] text-white;
      }
      .date-picker-day-today {
        @apply border border-[var(--primary-color)] text-[var(--primary-color)];
      }
       .date-picker-day-name {
        @apply text-xs font-semibold text-gray-500 flex h-10 w-full items-center justify-center;
       }
    </style>
</head>
<body class="bg-slate-50 text-[var(--text-primary)]">
<div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden" style="--radio-dot-svg: url('data:image/svg+xml,%3csvg viewBox=%270 0 16 16%27 fill%3D%27%23ffffff%27 xmlns%3D%27http://www.w3.org/2000/svg%27%3e%3ccircle cx%3D%278%27 cy%3D%278%27 r%3D%273%27/%3e%3c/svg%3e'); --select-button-svg: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2724px%27 height=%2724px%27 fill%3D%27rgb(73,115,156)%27 viewBox%3D%270 0 256 256%27%3e%3cpath d%3D%27M181.66,170.34a8,8,0,0,1,0,11.32l-48,48a8,8,0,0,1-11.32,0l-48-48a8,8,0,0,1,11.32-11.32L128,212.69l42.34-42.35A8,8,0,0,1,181.66,170.34Zm-96-84.68L128,43.31l42.34,42.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,85.66Z%27%3e%3c/path%3e%3c/svg%3e');">
<div class="flex h-full grow flex-col">
<?php include 'includes/header.php'; ?>
<main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
<div class="w-full max-w-xl space-y-8">
<div>
      <h1 class="text-3xl font-bold tracking-tight text-center text-[var(--text-primary)]">Eşya Kaydet</h1>
      <p class="mt-2 text-center text-sm text-[var(--text-secondary)]">Kaybedilen veya bulunan bir eşyayı listelemek için aşağıdaki formu doldurun.</p>
</div>
<?php if($msg): ?><p class="text-red-600 text-sm text-center"><?php echo $msg; ?></p><?php endif; ?>
<form action="create-item.php" method="POST" enctype="multipart/form-data" class="space-y-6 bg-[var(--card-background)] p-6 sm:p-8 rounded-xl shadow-lg">
<div>
<label class="form-label" for="file-upload">Eşya Resmi</label>
<img id="imgPreview" class="h-32 w-full object-cover rounded mb-2 hidden" />
<div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-[var(--border-color)] px-6 pt-10 pb-12 hover:border-[var(--primary-color)] transition-colors duration-200">
<div class="space-y-1 text-center">
<svg aria-hidden="true" class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
<path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</svg>
<div class="flex text-sm text-[var(--text-secondary)]">
<label class="relative cursor-pointer rounded-md font-medium text-[var(--primary-color)] hover:text-blue-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-[var(--primary-color)] focus-within:ring-offset-2" for="file-upload">
<span>Dosya yükle</span>
<input class="sr-only" id="file-upload" name="image" type="file" accept="image/*"/>
</label>
<p class="pl-1">veya sürükleyip bırakın</p>
</div>
<p class="text-xs text-gray-500">PNG, JPG, GIF (10MB'a kadar)</p>
</div>
</div>
</div>
<div>
<label class="form-label" for="item-title">Başlık</label>
<input class="form-input" id="item-title" name="title" placeholder="Örnek: Siyah iPhone 13 Pro" type="text"/>
</div>
<fieldset>
<legend class="form-label">Durum</legend>
<div class="mt-1 grid grid-cols-1 gap-3 sm:grid-cols-2">
<label class="radio-label" for="lost-item">
<input checked="" class="radio-input" id="lost-item" name="status" type="radio" value="lost"/>
<span class="text-sm font-medium text-[var(--text-primary)]">Bu eşyayı kaybettim</span>
</label>
<label class="radio-label" for="found-item">
<input class="radio-input" id="found-item" name="status" type="radio" value="found"/>
<span class="text-sm font-medium text-[var(--text-primary)]">Bu eşyayı buldum</span>
</label>
</div>
</fieldset>
<div>
<label class="form-label" for="category">Kategori</label>
<select class="form-input appearance-none bg-[image:--select-button-svg] bg-no-repeat bg-[center_right_1rem]" id="category" name="category">
<option>Kategori seçin</option>
<option value="electronics">Elektronik</option>
<option value="keys">Anahtarlar</option>
<option value="books">Kitaplar</option>
<option value="clothing">Giysiler</option>
<option value="accessories">Aksesuarlar</option>
<option value="other">Diğer</option>
</select>
</div>
<div>
<label class="form-label" for="description">Açıklama</label>
<textarea class="form-input" id="description" name="description" placeholder="Eşyanın detaylı bir açıklaması, ayırt edici özellikler dahil." rows="4"></textarea>
</div>
<div>
<label class="form-label" for="location">Kampüs Konumu</label>
<input class="form-input" id="location" name="location" placeholder="Örnek: Kütüphane, 2. kat, çalışma odalarının yanındaki" type="text"/>
</div>
<div>
<label class="form-label" for="date-lost-found">Kaybedilme/Bulunma Tarihi</label>
<input class="form-input" id="date-lost-found" name="date-lost-found" type="date"/>
</div>
<div>
<button class="flex w-full justify-center rounded-lg bg-[var(--primary-color)] py-3 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 transition-colors duration-200" type="submit">
                  İlanı Gönder
                </button>
</div>
</form>
</div>
</main>
<footer class="py-8 text-center text-sm text-[var(--text-secondary)] border-t border-[var(--border-color)]">
          &copy; 2024 Kampüs Emanet. Tüm hakları saklıdır.
        </footer>
</div>
</div>

</body>
<script>
const fileInput=document.getElementById('file-upload');
const preview=document.getElementById('imgPreview');
if(fileInput){fileInput.addEventListener('change',e=>{const f=e.target.files[0]; if(f){preview.src=URL.createObjectURL(f); preview.classList.remove('hidden');}});} 
</script>
</html>