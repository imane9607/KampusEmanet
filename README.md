# Kampüs Emanet

![Kampüs Emanet Logo](assets/logo.png)

## Proje URL
[https://kampus-emanet.com](http://95.130.171.20/~st21360859223/index.php#home)

## Proje Açıklaması
Kampüs Emanet, kampüs içinde kayıp ve bulunan eşyaların yönetimi için geliştirilmiş bir web uygulamasıdır. Sistemin amacı, öğrenciler ve personelin kayıp eşyalarını kolayca bildirebilmesi ve bulunan eşyaların sahiplerine ulaşmasını sağlamaktır.

## Proje Gereksinimleri

### Teknik Gereksinimler
- **Backend**: Yalın PHP (frameworks kullanılmadı)
- **Frontend**: Tailwind CSS
- **Veritabanı**: MySQL
- **Güvenlik**:
  - Şifre hashleme (password_hash)
  - Session tabanlı oturum yönetimi
  - XSS koruması
  - SQL injection koruması

### Özellikler
- Kullanıcı kaydı ve oturum yönetimi
- Kayıp/bulunan eşya kaydı
- Eşya listesi görüntüleme
- Eşya düzenleme
- Eşya silme
- Admin paneli
- Kullanıcı yönetimi

### Uyumluluk
- Responsive tasarım
- Mobil uyumlu arayüz
- Türkçe dil desteği
- SEO uyumlu URL'ler

### Veritabanı
- 4 MySQL tablosu
- Güvenli şifre depolama
- Veri bütünlüğü kontrolü
- İndeksleme optimizasyonu
- MariaDB uyumlu

### Geliştirme Ortamı
- XAMPP veya benzeri web sunucusu
- PHP 8.0+ sürümü
- MySQL 5.7+ sürümü
- Modern tarayıcılar

## Proje Açıklaması
Kampüs Kayıp Eşya Yönetim Sistemi, kampüs içinde kayıp ve bulunan eşyaların yönetimi için geliştirilmiş bir web uygulamasıdır. Sistemin amacı, öğrenciler ve personelin kayıp eşyalarını kolayca bildirebilmesi ve bulunan eşyaların sahiplerine ulaşmasını sağlamaktır.

## Özellikler

### Kullanıcı Özellikleri
- Kullanıcı kayıt ve giriş
- Şifreli oturum yönetimi
- Kayıp eşya bildirimi
- Bulunan eşya bildirimi
- Eşya takibi ve yönetimi
- Profil bilgileri düzenleme

### Yönetici Özellikleri
- Kullanıcı yönetimi
- İlan yönetimi
- İstatistikler ve raporlar
- Sistem ayarları

## Teknolojiler

- Backend: PHP (Yalnızca core PHP, framework kullanılmadan)
- Frontend: HTML5, CSS3, JavaScript
- CSS Framework: Tailwind CSS
- Veritabanı: MySQL

## Kurulum

1. XAMPP veya benzeri bir web sunucusu kurun
2. Veritabanı ayarlarını `includes/db.php` dosyasında yapın
3. Proje dosyalarını web sunucusunun root dizinine yükleyin
4. Tarayıcıdan uygulamaya erişin

## Default Login Credentials

### Admin Account
- Email: admin@kampus.com
- Password: 123456

### User Account
- Email: user@kampus.com
- Password: 123456

**Note: These are default credentials for development purposes. Please change them in production.**

## Veritabanı

Veritabanı şeması `lost_found.sql` dosyasında bulunur. Bu dosyayı MySQL sunucusuna import ederek veritabanı oluşturabilirsiniz.

Veritabanı adı: `lost_found`

## Tablo Yapısı
- `users`: Kullanıcı bilgileri
- `items`: Kayıp/bulunan eşya bilgileri
- `claims`: Eşya talep bilgileri
- `categories`: Eşya kategorileri
- `statuses`: Eşya durumları

## Güvenlik Özellikleri

- Şifre hashleme (password_hash kullanılıyor)
- Session tabanlı oturum yönetimi
- XSS koruması
- SQL injection koruması
- Input validation

## Ekran Görüntüleri

![Ana Sayfa](home.png)
)
![İlan Detay](screenshots/item-detail.png)
![Kullanıcı Profili](screenshots/profile.png)

## Video Demo

[![Video Demo](https://img.youtube.com/vi/VIDEO_ID/0.jpg)](https://youtube.com/watch?v=VIDEO_ID)

## Güvenlik Uyarısı

⚠️ GitHub'a yüklediğinizde hassas bilgileri (hosting şifreleri, API anahtarları vb.) gizlemeyi veya kaldırmayı unutmayın!

- Veritabanı şifreleri
- Hosting erişim bilgileri
- API anahtarları
- Özel yapılandırma dosyaları

Bu bilgileri `.gitignore` dosyasına ekleyin veya deploy edilmeden önce kaldırın.

## Lisans

MIT License
