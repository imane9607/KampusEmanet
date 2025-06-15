<?php
require_once 'includes/auth.php';
require_once 'includes/items.php';
require_once 'includes/db.php';
Auth::requireLogin();
$user = Auth::user();
$msg = '';
// İlan silme işlemi
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    Items::delete($id, $user['id']);
    header('Location: profile.php');
    exit;
}
// Yeni ilan oluşturma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'lost';
    if ($title === '') {
        $msg = 'Başlık gereklidir';
    } elseif (!Items::create($user['id'], $title, $description, $status)) {
        $msg = 'İlan kaydedilirken hata oluştu';
    } else {
        header('Location: profile.php');
        exit;
    }
}
// --- Profil güncelleme / silme işlemleri ---
$pdo = (new Database())->getConn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        $genderInput = trim($_POST['gender'] ?? '');
        // Mevcut cinsiyet bilgisini koru (ayarlanmışsa)
        $gender = ($user['gender'] ?? '') !== '' ? $user['gender'] : $genderInput;
        if ($name === '' || $email === '') {
            $msg = 'Ad ve e-posta gereklidir.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Geçersiz e-posta adresi.';
        } else {
            // Mevcut kullanıcıyı hariç tutarak e-posta adresinin benzersizliğini kontrol et
            $dup = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
            $dup->execute([$email, $user['id']]);
            if ($dup->fetch()) {
                $msg = 'Bu e-posta adresi zaten kullanılıyor.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, phone=?, city=?, gender=? WHERE id=?');
                $stmt->execute([$name, $email, $phone, $city, $gender, $user['id']]);
                // Oturum verilerini güncelle
                $_SESSION['user']["name"] = $name;
                $user['name'] = $name;
                $user['email'] = $email;
                $user['phone']=$phone; 
                $user['city']=$city;
                $user['gender']=$gender;
                $msg = 'Profil başarıyla güncellendi.';
            }
        }
    } elseif ($action === 'delete_account') {
        // Referans bütünlüğü için önce kullanıcının ilanlarını sil
        $pdo->prepare('DELETE FROM items WHERE user_id=?')->execute([$user['id']]);
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$user['id']]);
        Auth::logout();
        header('Location: index.php');
        exit;
    } elseif($action==='update_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if(strlen($new)<6){
            $msg='Yeni şifre en az 6 karakter olmalıdır.';
        } elseif($new!==$confirm){
            $msg='Şifreler eşleşmiyor.';
        } else {
            $check=$pdo->prepare('SELECT password FROM users WHERE id=?');
            $check->execute([$user['id']]);
            $hash=$check->fetchColumn();
            if(!$hash||!password_verify($current,$hash)){
                $msg='Mevcut şifre yanlış.';
            } else {
                $newHash=password_hash($new,PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$newHash,$user['id']]);
                $msg='Şifre başarıyla güncellendi.';
            }
        }
    }
}

// Yeniden kullanıcı kaydını çek (e-posta dahil)
$stmt = $pdo->prepare('SELECT name,email,phone,city,gender FROM users WHERE id=? LIMIT 1');
$stmt->execute([$user['id']]);
$user = array_merge($user, $stmt->fetch());

$items = Items::getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/theme.css" />
  <title>Profilim – Kampüs Emanet</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <style type="text/tailwindcss">
    :root {
      --primary-color:#3B82F6;--border-color:#E5E7EB;
      --text-primary:#1F2937;--text-secondary:#6B7280;
      --surface-background:#F9FAFB;--surface-card:#FFFFFF;--surface-input:#F3F4F6;
    }
    body {font-family:"Plus Jakarta Sans","Noto Sans",sans-serif;}
    .card { @apply bg-[var(--surface-card)] border border-[var(--border-color)] rounded-xl shadow-sm p-6; }
    .btn { @apply inline-flex items-center justify-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors; }
    .btn-primary { @apply bg-[var(--primary-color)] text-white hover:bg-blue-600; }
    .btn-secondary { @apply bg-[var(--surface-input)] text-[var(--text-primary)] hover:bg-gray-200; }
    .btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
    .active-tab{ @apply bg-white text-[var(--primary-color)] font-semibold shadow transition-colors; }
    .tab-inactive{ @apply text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors; }
  </style>
</head>
<body class="bg-[var(--surface-background)] text-[var(--text-primary)]">
<?php include 'includes/header.php'; ?>
<main class="max-w-5xl mx-auto p-6 space-y-8">
  <h2 class="text-3xl font-bold mb-6">Merhaba, <?= htmlspecialchars($user['name']); ?></h2>
  <?php if ($msg): ?>
    <p class="text-red-600 text-sm mb-4"><?= htmlspecialchars($msg); ?></p>
  <?php endif; ?>
  <!-- Kişisel Bilgiler -->
  <div class="inline-flex bg-[var(--surface-input)] rounded-lg p-1 mb-6">
     <a href="dashboard.php" class="tab-inactive rounded-md px-4 sm:px-6 py-2 text-sm tracking-wide">İlanlarım</a>
     <a href="profile.php" class="active-tab rounded-md px-4 sm:px-6 py-2 text-sm tracking-wide">Profilim</a>
  </div>
  <div class="card">
     <h3 class="text-lg font-semibold mb-4">Profil Bilgilerim</h3>
    <form method="post" class="space-y-4 max-w-md">
      <input type="hidden" name="action" value="update_profile" />
      <div>
         <label class="block text-sm font-medium mb-1">Ad Soyad</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">E-posta</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">Telefon</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">Şehir</label>
        <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? ''); ?>" class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">Cinsiyet</label>
         <?php if(($user['gender']??'')!==''): ?>
           <input type="text" value="<?= htmlspecialchars(ucfirst($user['gender']==''?'Belirtmek istemiyorum':$user['gender'])); ?>" disabled class="w-full rounded-md border border-[var(--border-color)] p-2 bg-gray-100" />
           <p class="text-xs text-[var(--text-secondary)] mt-1">Cinsiyet değiştirilemez.</p>
           <input type="hidden" name="gender" value="<?= htmlspecialchars($user['gender']); ?>" />
         <?php else: ?>
           <select name="gender" class="w-full rounded-md border border-[var(--border-color)] p-2">
             <option value="" <?= ($user['gender']??'')==''?'selected':''; ?>>Belirtmek istemiyorum</option>
             <option value="male">Erkek</option>
             <option value="female">Kadın</option>
           </select>
         <?php endif; ?>
      </div>
       <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
    </form>
    <hr class="my-6" />
     <form method="post" onsubmit="return confirm('Bu işlem hesabınızı ve ilanlarınızı kalıcı olarak silecektir. Devam etmek istiyor musunuz?');">
       <input type="hidden" name="action" value="delete_account" />
       <button type="submit" class="btn btn-danger">Hesabı Sil</button>
    </form>
    <!-- Şifre Güncelleme -->
     <h3 class="text-lg font-semibold mt-10 mb-4">Şifreyi Değiştir</h3>
    <form method="post" class="space-y-4 max-w-md">
      <input type="hidden" name="action" value="update_password" />
      <div>
         <label class="block text-sm font-medium mb-1">Mevcut Şifre</label>
         <input type="password" name="current_password" required class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">Yeni Şifre</label>
         <input type="password" name="new_password" required class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
      <div>
         <label class="block text-sm font-medium mb-1">Yeni Şifreyi Onayla</label>
         <input type="password" name="confirm_password" required class="w-full rounded-md border border-[var(--border-color)] p-2" />
      </div>
       <button type="submit" class="btn btn-primary">Şifreyi Güncelle</button>
    </form>
  </div>
</main>
<footer class="text-center text-sm text-[var(--text-secondary)] py-6">&copy; <?= date('Y'); ?> Campus Lost &amp; Found</footer>
</body>
</html>