<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/theme.css" />
  <title>İlanı Düzenle – Kampüs Emanet</title>
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
  </style>
  <?php
    require_once 'includes/auth.php';
    require_once 'includes/items.php';
    Auth::requireLogin();
    $user = Auth::user();
    $id = (int)($_GET['id'] ?? ($_POST['item_id'] ?? 0));
    $item = Items::get($id);
    if(!$item || $item['user_id']!=$user['id']){ header('Location: listlostfound.php');exit; }
    // handle form submission
    if($_SERVER['REQUEST_METHOD']==='POST'){
      $uploadsDir='uploads/'; if(!is_dir($uploadsDir)) mkdir($uploadsDir);
      $title=trim($_POST['title'] ?? '');
      $desc =trim($_POST['description'] ?? '');
      $status=$_POST['status'] ?? 'lost';
      $date  =$_POST['date'] ?? date('Y-m-d');
      $location=$_POST['location'] ?? '';
      $category=trim($_POST['category'] ?? '');
      if(isset($_FILES['image']) && $_FILES['image']['error']==0){
          $name=uniqid('img_').'.'.pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
          $dest=$uploadsDir.$name;
          if(move_uploaded_file($_FILES['image']['tmp_name'],$dest)){
              Items::setImage($id,$dest);
          }
      }
      Items::update($id,$user['id'],$title,$desc,$category,$location,$status,$date);
      header('Location: dashboard.php?updated=1');exit;
    }
    $currentImage=Items::firstImage($item['id']);
  ?>
</head>
<body class="bg-slate-50 text-[var(--text-primary)]">
  <div class="relative flex min-h-screen flex-col">
    <?php include 'includes/header.php'; ?>

    <!-- Main -->
    <main class="flex flex-1 justify-center py-8 px-4 sm:px-6 lg:px-8">
      <div class="w-full max-w-xl space-y-8">
        <div class="text-center">
          <h1 class="text-3xl font-bold tracking-tight">İlanı Düzenle</h1>
          <p class="mt-2 text-sm text-[var(--text-secondary)]">Aşağıda eşyanın detaylarını güncelleyin.</p>
        </div>

        <form action="edit-item.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6 bg-[var(--card-background)] p-6 sm:p-8 rounded-xl shadow-lg">
          <input type="hidden" name="item_id" value="<?php echo $id; ?>">

          <div>
            <label class="form-label" for="file-upload">Eşya Resmi</label>
            <img id="imgPreview" src="<?= $currentImage? htmlspecialchars($currentImage):''; ?>" class="h-32 w-full object-cover rounded mb-2<?= $currentImage? '':' hidden'; ?>" />
            <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-[var(--border-color)] px-6 pt-10 pb-12 hover:border-[var(--primary-color)] transition-colors duration-200">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 48 48"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/></svg>
                <div class="flex text-sm text-[var(--text-secondary)]">
                  <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-[var(--primary-color)] hover:text-blue-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-[var(--primary-color)] focus-within:ring-offset-2">
                    <span>Dosya yükle</span>
                    <input id="file-upload" name="image" type="file" class="sr-only" accept="image/*" />
                  </label>
                  <p class="pl-1">veya sürükleyip bırakın</p>
                </div>
                <p class="text-xs text-gray-500">PNG, JPG, GIF (10MB'a kadar)</p>
              </div>
            </div>
          </div>

          <div>
            <label class="form-label" for="item-title">Başlık</label>
            <input type="text" id="item-title" name="title" class="form-input" value="<?php echo htmlspecialchars($item['title']); ?>" />
          </div>

          <fieldset>
            <legend class="form-label">Durum</legend>
            <div class="mt-1 grid grid-cols-1 gap-3 sm:grid-cols-2">
              <label class="radio-label" for="lost-item">
                <input type="radio" id="lost-item" name="status" value="lost" class="radio-input" <?php echo $item['status']==='lost'? 'checked':''; ?> />
                <span class="text-sm font-medium text-[var(--text-primary)]">Bu eşyayı kaybettim</span>
              </label>
              <label class="radio-label" for="found-item">
                <input type="radio" id="found-item" name="status" value="found" class="radio-input" <?php echo $item['status']==='found'? 'checked':''; ?> />
                <span class="text-sm font-medium text-[var(--text-primary)]">Bu eşyayı buldum</span>
              </label>
            </div>
          </fieldset>

          <div>
            <label class="form-label" for="category">Kategori</label>
            <select id="category" name="category" class="form-input">
              <?php
                $categories = ['electronics'=> 'Elektronik','keys'=>'Anahtarlar','books'=>'Kitaplar','clothing'=>'Giysiler','accessories'=>'Aksesuarlar','bags'=>'Çantalar','other'=>'Diğer'];
                foreach($categories as $val=>$label){
                  $sel = $item['category']===$val ? 'selected' : '';
                  echo "<option value=\"$val\" $sel>$label</option>";
                }
              ?>
            </select>
          </div>

          <div>
            <label class="form-label" for="description">Açıklama</label>
            <textarea id="item-description" name="description" rows="4" class="form-input"><?php echo htmlspecialchars($item['description']); ?></textarea>
          </div>

          <div>
            <label class="form-label" for="location">Kampüs Konumu</label>
            <?php $loc=$item['location']??''; ?>
            <input type="text" id="location" name="location" class="form-input" value="<?php echo htmlspecialchars($loc); ?>" />
          </div>

          <div>
            <label class="form-label" for="date-lost-found">Kaybedilme/Bulunma Tarihi</label>
            <input type="date" id="date-lost-found" name="date" class="form-input" value="<?php echo $item['date']; ?>" />
          </div>

          <div>
            <button type="submit" class="flex w-full justify-center rounded-lg bg-[var(--primary-color)] py-3 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 transition-colors duration-200">Değişiklikleri Kaydet</button>
          </div>
        </form>
      </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center text-sm text-[var(--text-secondary)] border-t border-[var(--border-color)] bg-white">
      &copy; 2024 Kampüs Emanet. Tüm hakları saklıdır.
    </footer>
  </div>
  <script>
    const fileInput=document.getElementById('file-upload');
    const preview=document.getElementById('imgPreview');
    if(fileInput){fileInput.addEventListener('change',e=>{const f=e.target.files[0]; if(f){preview.src=URL.createObjectURL(f); preview.classList.remove('hidden');}});} 
  </script>
</body>
</html>