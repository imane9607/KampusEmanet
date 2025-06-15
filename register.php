<?php
require_once 'includes/auth.php';
$error='';
$success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['full_name']??'');
  $email=trim($_POST['campus_email']??'');
  $password=$_POST['password']??'';
  $confirm=$_POST['confirm_password']??'';
  $phone=trim($_POST['phone']??'');
  $city=trim($_POST['city']??'');
  $gender=trim($_POST['gender']??'');
  if($password!==$confirm){
    $error='Şifreler eşleşmiyor';
  } else {
    $res=Auth::register($name,$email,$password,$phone,$city,$gender);
    if($res===true){
      $success=true;
    } else { $error=$res; }
  }
}
?>
<html><head>
<meta charset="utf-8"/>
<link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
<link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&amp;family=Plus+Jakarta+Sans%3Awght%40400%3B500%3B700%3B800" onload="this.rel='stylesheet'" rel="stylesheet"/>
<title>Kampüs Emanet - Hesap Oluştur</title>
<link href="data:image/x-icon;base64," rel="icon" type="image/x-icon"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="stylesheet" href="assets/css/theme.css" />
<style type="text/tailwindcss">
      :root {
        --brand-primary: #0c7ff2;
        --brand-primary-light: #e6f2fe;
        --text-primary: #0d141c;
        --text-secondary: #49739c;
        --border-color: #cedbe8;
        --background-light: #f8fafc;
        --background-white: #ffffff;
      }
      body {
        font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;
      }
      .form-input {
        @apply flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[var(--text-primary)] focus:outline-0 border border-[var(--border-color)] bg-[var(--background-light)] focus:border-[var(--brand-primary)] h-12 placeholder:text-[var(--text-secondary)] p-3 text-sm font-normal leading-normal ring-1 ring-transparent focus:ring-[var(--brand-primary)] focus:shadow-sm;
      }
      .form-label {
        @apply text-[var(--text-primary)] text-sm font-medium leading-normal pb-1.5;
      }
      .primary-button {
        @apply flex min-w-[84px] w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-[var(--brand-primary)] text-white text-base font-semibold leading-normal tracking-[0.01em] hover:bg-opacity-90 transition-colors duration-200;
      }
    </style>
</head>
<body class="bg-[var(--background-light)]">
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
<div class="relative flex min-h-screen flex-col group/design-root">
<div class="flex flex-1 items-center justify-center p-4 sm:p-6 lg:p-8">
<div class="w-full max-w-md space-y-8">
<div class="text-center">
<div class="inline-flex items-center justify-center gap-3 mb-6">
<img src="assets/logo.png" alt="Kampüs Emanet" class="w-12 h-12" />
</svg>
<h1 class="text-[var(--text-primary)] text-2xl font-bold tracking-tight">Kampüs Emanet</h1>
</div>
<h2 class="text-[var(--text-primary)] text-3xl font-bold tracking-tight">Hesabınızı Oluşturun</h2>
<p class="mt-2 text-sm text-[var(--text-secondary)]">
              Kayıp eşyaları bulmak ve bildirmek için kampüs topluluğumuza katılın.
            </p>
</div>
<div class="bg-[var(--background-white)] p-6 sm:p-8 shadow-lg rounded-xl space-y-6">
<form class="space-y-6" method="POST" action="register.php">
<div>
<label class="form-label" for="full-name">Ad Soyad</label>
<input class="form-input" id="full-name" name="full_name" placeholder="Tam adınızı girin" type="text"/>
</div>
<div>
<label class="form-label" for="campus-email">Kampüs E-postası</label>
<input class="form-input" id="campus-email" name="campus_email" placeholder="Kampüs e-posta adresinizi girin" type="email"/>
</div>
<div>
<label class="form-label" for="phone">Telefon</label>
<input class="form-input" id="phone" name="phone" placeholder="Telefon numaranızı girin" type="text"/>
</div>
<div>
<label class="form-label" for="city">Şehir</label>
<input class="form-input" id="city" name="city" placeholder="Yaşadığınız şehir" type="text"/>
</div>
<div>
<label class="form-label" for="gender">Cinsiyet</label>
<select id="gender" name="gender" class="form-input">
  <option value="">Belirtmek istemiyorum</option>
  <option value="male">Erkek</option>
  <option value="female">Kadın</option>
</select>
</div>
<div>
<label class="form-label" for="password">Şifre</label>
<input class="form-input" id="password" name="password" placeholder="Bir şifre oluşturun" type="password"/>
</div>
<div>
<label class="form-label" for="confirm-password">Şifreyi Onayla</label>
<input class="form-input" id="confirm-password" name="confirm_password" placeholder="Şifrenizi onaylayın" type="password"/>
</div>
<div>
<button class="primary-button mt-2" type="submit">
<span class="truncate">Kaydol</span>
</button>
</div>
<?php if($error): ?>
  <script>document.addEventListener('DOMContentLoaded',()=>showToast(<?php echo json_encode($error); ?>,'error'));</script>
<?php elseif($success): ?>
  <script>document.addEventListener('DOMContentLoaded',()=>showToast('Hesap oluşturuldu! Artık giriş yapabilirsiniz.','success'));</script>
<?php endif; ?>
</form>
<p class="text-center text-sm text-[var(--text-secondary)]">
              Zaten bir hesabınız var mı?
              <a class="font-semibold text-[var(--brand-primary)] hover:text-opacity-80 transition-colors duration-200" href="login.php">Giriş Yap</a>
</p>
</div>
</div>
</div>
</div>

</body></html>