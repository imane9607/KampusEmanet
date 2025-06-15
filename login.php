<?php
require_once 'includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['campus-email'] ?? '');
    $password = $_POST['password'] ?? '';
    $res = Auth::login($email, $password);
    if ($res === true) {
        $user = Auth::user();
        if(($user['role'] ?? '') === 'admin'){
            header('Location: admin/dashboard.php');
        } else {
            header('Location: profile.php');
        }
        exit;
    }
    $error = $res;
}
?>
<html><head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B600%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<title>Kampüs Emanet - Giriş Yap</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="stylesheet" href="assets/css/theme.css" />
<style type="text/tailwindcss">
      :root {
        --primary-color: #0c7ff2;
        --secondary-color: #6b7280;--background-color: #f9fafb;--card-background-color: #ffffff;
        --border-color: #d1d5db;--text-primary: #1f2937;
        --text-secondary: #4b5563;
        --placeholder-color: #9ca3af;
      }
      body {
        font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;
      }
      .form-input {
        border-color: var(--border-color);
        background-color: var(--card-background-color);}
      .form-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(12, 127, 242, 0.2);}
    </style>
</head>
<body class="bg-[var(--background-color)]">
  <!-- Bildirim Kutusu -->
  <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
  <style>
    @keyframes slide-in{from{opacity:0;transform:translateX(20px);}to{opacity:1;transform:translateX(0);}}
    .animate-slide-in{animation:slide-in .3s ease-out;}
  </style>
  <script>
    function bildirimGoster(msg,type='info'){
      const renkler={bilgi:'bg-blue-600',basarili:'bg-green-600',hata:'bg-red-600'};
      const toast=document.createElement('div');
      toast.className=`text-white px-4 py-3 rounded-lg shadow-lg ${renkler[type]||renkler.bilgi} animate-slide-in transition-opacity duration-500`;
      toast.textContent=msg;
      document.getElementById('toastContainer').appendChild(toast);
      setTimeout(()=>{toast.classList.add('opacity-0');setTimeout(()=>toast.remove(),500);},4000);
    }
  </script>
<div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden">
<div class="flex h-full grow flex-col items-center justify-center p-6 lg:p-8">
<div class="w-full max-w-md bg-[var(--card-background-color)] shadow-xl rounded-xl p-8 md:p-10">
<div class="flex flex-col items-center mb-8">
<div class="bg-white p-3 rounded-full mb-4 shadow-sm">
<img src="assets/logo.png" alt="Kampüs Emanet" class="w-12 h-12" />
<path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
</div>
<h1 class="text-[var(--text-primary)] text-3xl font-bold tracking-tight">Hoş Geldiniz!</h1>
<p class="text-[var(--text-secondary)] mt-2 text-center">Kayıp ve Bulunan eşyalar sistemine giriş yapın.</p>
</div>
<form class="space-y-6" method="POST">
<div>
<label class="block text-sm font-medium leading-6 text-[var(--text-primary)] pb-1.5" for="campus-email">Kampüs E-posta Adresi</label>
<input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[var(--text-primary)] focus:outline-none focus:ring-0 h-12 placeholder:text-[var(--placeholder-color)] p-3 text-base font-normal leading-normal" id="campus-email" name="campus-email" placeholder="Örnek: student@university.edu" type="email"/>
</div>
<div>
<label class="block text-sm font-medium leading-6 text-[var(--text-primary)] pb-1.5" for="password">Şifre</label>
<input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[var(--text-primary)] focus:outline-none focus:ring-0 h-12 placeholder:text-[var(--placeholder-color)] p-3 text-base font-normal leading-normal" id="password" name="password" placeholder="Şifrenizi girin" type="password"/>
</div>
<div>
<button class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-6 bg-[var(--primary-color)] text-white text-base font-semibold leading-normal tracking-wide hover:bg-opacity-90 transition-colors duration-150 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--primary-color)]" type="submit">
                Giriş Yap
              </button>

              <!-- Default Credentials Notification -->
              <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center space-x-3">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">Varsayılan Giriş Bilgileri</h3>
                    <ul class="mt-2 text-sm text-gray-600 space-y-1">
                      <li>
                        <span class="font-medium">Yönetici:</span>
                        admin@kampus.com / 123456
                      </li>
                      <li>
                        <span class="font-medium">Kullanıcı:</span>
                        user@kampus.com / 123456
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
<?php if($error): ?>
  <?php /* show toast via JS instead of inline alert */ ?>
  <script>document.addEventListener('DOMContentLoaded',()=>showToast(<?php echo json_encode($error); ?>,'error'));</script>
<?php endif; ?>
</div>
</form>
<div class="mt-6 text-center">
<a href="register.php" class="text-sm font-medium text-[var(--primary-color)] hover:text-opacity-80 hover:underline">
                Şifremi Unuttum?
              </a>
</div>
<p class="mt-8 text-center text-xs text-[var(--text-secondary)]">
                Hesabınız yok mu?
                <a href="register.php" class="font-semibold text-[var(--primary-color)] hover:text-opacity-80 hover:underline">Kaydol</a>
              </p>
</div>
</div>
</div>

</body></html>