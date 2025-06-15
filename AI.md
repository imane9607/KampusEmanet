
#### 1. "Kayıt sırasında şifreleri PHP ile güvenli bir şekilde nasıl hash'leyebilirim?"

Şifreleri veritabanında saklarken düz metin olarak (plaintext) tutmak, veri ihlali durumunda çok büyük bir güvenlik riski oluşturur. Kötü niyetli kişiler veritabanına eriştiğinde tüm kullanıcı şifrelerini ele geçirebilir. Bu nedenle şifreleri **hash'lemek** esastır. Hash'leme, geri döndürülemez (tek yönlü) bir matematiksel işlemle şifreyi sabit uzunlukta bir karakter dizisine dönüştürmektir.

**Neden Hash'leme?**
*   **Geri Döndürülemezlik:** Hash'lenmiş bir şifreden orijinal şifreyi elde etmek neredeyse imkansızdır.
*   **Güvenlik:** Veritabanı ihlali olsa bile, saldırganların eline geçenler hash'lenmiş şifreler olur, gerçek şifreler değil.
*   **Salting:** Her şifreye rastgele bir "salt" (tuz) eklenerek hash'lenmesi, aynı şifreye sahip iki kullanıcının farklı hash'lere sahip olmasını sağlar. Bu, "rainbow table" saldırılarını engeller.

**Güvenli Hash'leme Yöntemi: `password_hash()` ve `password_verify()`**

PHP, şifre hash'leme için özel olarak tasarlanmış, kullanımı kolay ve güvenli fonksiyonlar sunar: `password_hash()` ve `password_verify()`.

*   **`password_hash($password, PASSWORD_DEFAULT, $options)`:**
    *   `$password`: Kullanıcının girdiği düz metin şifre.
    *   `PASSWORD_DEFAULT`: PHP'nin varsayılan olarak önerdiği ve gelecekteki güncellemelerle en iyi algoritmayı (şu an için **bcrypt**) otomatik olarak seçecek bir sabittir. Bu, sizin sürekli olarak güvenlik standartlarını takip etmenizi gerektirmez.
    *   `$options`: (İsteğe bağlı) Algoritmanın maliyetini (cost) belirleyebilirsiniz. Daha yüksek maliyet, daha fazla işlem gücü gerektirir ve hash'leme süresini uzatır, bu da kaba kuvvet saldırılarını yavaşlatır.

`password_hash()` fonksiyonu, şifreyi otomatik olarak tuzlar (salt ekler) ve bcrypt algoritmasıyla hash'ler. Ortaya çıkan hash dizisi, şifreyi, kullanılan algoritmayı ve tuzu içerir.

*   **`password_verify($password, $hash)`:**
    *   `$password`: Giriş sırasında kullanıcının girdiği düz metin şifre.
    *   `$hash`: Veritabanından çekilen, hash'lenmiş şifre.

Bu fonksiyon, verilen düz metin şifreyi, veritabanındaki hash ile karşılaştırır. İçerideki tuz ve algoritma bilgisi sayesinde, aynı hash'leme sürecini tekrarlar ve iki hash'in eşleşip eşleşmediğini kontrol eder. Eşleşirse `true`, aksi takdirde `false` döndürür.

**MD5 veya SHA1 Neden Kullanılmamalı?**
Eskiden yaygın olarak kullanılan MD5 ve SHA1 gibi hash algoritmaları, günümüzde güvenlik açısından zayıf kabul edilmektedir. Çok hızlı hesaplanabilirler ve "collision" (çakışma) saldırılarına veya "rainbow table" saldırılarına karşı savunmasızdırlar. `password_hash()` ise yavaş olacak şekilde tasarlanmıştır ve güçlü, modern algoritmalar kullanır.

**Örnek Akış:**

1.  **Kayıt Formu (register.php):** Kullanıcı `şifre`sini girer.
2.  **PHP Sunucu Tarafı İşleme:**
    ```php
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
        $plainTextPassword = $_POST['password'];

        // Şifreyi güvenli bir şekilde hash'le
        $hashedPassword = password_hash($plainTextPassword, PASSWORD_DEFAULT);

        // $hashedPassword'ı veritabanına kaydet
        // Örnek: INSERT INTO users (username, password) VALUES ('kullanici_adi', '$hashedPassword');

        echo "Şifre başarıyla hash'lendi ve kaydedildi (örnek): " . $hashedPassword;
    }
    ?>
    <form method="post" action="">
        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Kaydol</button>
    </form>
    ```
3.  **Veritabanı (Giriş Tablosu):** Şifre sütununun `VARCHAR(255)` veya benzeri, hash'lenmiş şifreyi tutabilecek kadar uzun olduğundan emin olun (bcrypt hash'leri 60 karaktere kadar olabilir, ancak gelecekteki algoritmalar için 255 karakter önerilir).
4.  **Giriş İşlemi (login.php):**
    ```php
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $plainTextPassword = $_POST['password'];

        // Veritabanından kullanıcının hash'lenmiş şifresini çek
        // Örnek: SELECT password FROM users WHERE username = '$username';
        $storedHashedPassword = '$2y$10$EXAMPLE_HASH_FROM_DATABASE...'; // Bu değeri veritabanından aldığınızı varsayın

        // Girilen şifreyi veritabanındaki hash ile doğrula
        if (password_verify($plainTextPassword, $storedHashedPassword)) {
            echo "Giriş başarılı!";
            // Oturum başlatma, kullanıcıyı yönlendirme vb.
        } else {
            echo "Kullanıcı adı veya şifre yanlış.";
        }
    }
    ?>
    <form method="post" action="">
        <label for="username">Kullanıcı Adı:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Giriş Yap</button>
    </form>
    ```

Bu yaklaşım, şifrelerinizi güvenli bir şekilde yönetmenizi sağlar.

#### 2. "login.php dosyamı inceleyip SQL enjeksiyonuna karşı savunmasız olup olmadığını kontrol edebilir misin?"

SQL enjeksiyonu, kötü niyetli kullanıcıların SQL sorgularınıza kendi komutlarını ekleyerek veritabanınızı manipüle etmelerine izin veren yaygın ve tehlikeli bir güvenlik açığıdır. Genellikle kullanıcıdan alınan verilerin (kullanıcı adı, şifre, arama terimi vb.) doğrudan, **temizlenmeden** veya **parametreleştirilmeden** SQL sorgusuna eklenmesiyle oluşur.

**SQL Enjeksiyonu Nasıl Anlaşılır?**

Bir `login.php` dosyasında SQL enjeksiyonu olup olmadığını kontrol etmek için, kullanıcının girdiği verilerin (genellikle `$_POST` veya `$_GET` dizilerinden gelen veriler) SQL sorgusunda nasıl kullanıldığına bakılır.

**Savunmasız Örnek (Kesinlikle Kullanmayın!):**

```php
<?php
// ... veritabanı bağlantısı ($conn) varsayalım ...

$username = $_POST['username'];
$password = $_POST['password'];

// Kullanıcı girdisinin doğrudan sorguya eklendiği savunmasız bir sorgu
$sql = "SELECT id, username FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Giriş başarılı
} else {
    // Giriş başarısız
}
?>
```
Bu örnekteki kritik hata, `$username` ve `$password` değişkenlerinin doğrudan tırnak işaretleri içine alınarak SQL sorgusuna eklenmesidir.

**Saldırı Senaryosu (Savunmasız Örnek İçin):**

Eğer kötü niyetli bir kullanıcı `username` alanına şunu girerse:
`' OR '1'='1`

Ve `password` alanına herhangi bir şey girerse. SQL sorgusu şöyle olacaktır:

`SELECT id, username FROM users WHERE username = '' OR '1'='1' AND password = 'herhangi_bir_sifre'`

Buradaki `' OR '1'='1'` kısmı, `username = ''` yanlış olsa bile `OR '1'='1'` kısmı her zaman doğru olacağı için sorgunun `WHERE` koşulunu her zaman `TRUE` yapar ve saldırgan, şifreyi bilmese bile ilk kullanıcının hesabına (veya tüm kullanıcıları çekmeye) erişebilir. Veya `username` alanına `' OR 1=1 --` ( `--` sonrası yorum satırı olur) gibi ifadelerle şifre kontrolünü tamamen atlayabilir.

**SQL Enjeksiyonuna Karşı Korunma: Hazırlanmış İfadeler (Prepared Statements)**

SQL enjeksiyonuna karşı en etkili koruma yöntemi **Hazırlanmış İfadeler (Prepared Statements)** kullanmaktır. Bu yöntem, sorgu mantığını (ne yapılacağını) kullanıcı verisinden (hangi veriyle yapılacağını) ayırır.

**Nasıl Çalışır?**

1.  **Sorguyu Hazırla (Prepare):** SQL sorgusu bir şablon olarak veritabanı sunucusuna gönderilir. Bu şablonda, kullanıcıdan gelecek değerler için yer tutucular (`?` veya `:named_placeholder`) kullanılır.
2.  **Değerleri Bağla (Bind Parameters):** Kullanıcıdan gelen gerçek değerler daha sonra bu yer tutuculara güvenli bir şekilde bağlanır. Veritabanı sürücüsü, bu değerleri SQL komutları olarak değil, sadece veri olarak algılar ve bu sayede kötü niyetli komutlar çalıştırılamaz.
3.  **Çalıştır (Execute):** Hazırlanmış sorgu, bağlı değerlerle birlikte çalıştırılır.

PHP'de hazırlanmış ifadeleri kullanmak için iki ana uzantı vardır: **PDO** (PHP Data Objects) veya **MySQLi**. Her ikisi de bu özelliği destekler. PDO, farklı veritabanı sistemleri arasında geçiş yapmayı kolaylaştırdığı için genellikle tercih edilir.

**Güvenli Örnek (PDO ile):**

```php
<?php
// Veritabanı bağlantısı (PDO)
$dsn = 'mysql:host=localhost;dbname=veritabani_adi;charset=utf8mb4';
$username = 'kullanici';
$password = 'sifre';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Hata raporlamayı etkinleştir
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $submittedUsername = $_POST['username'];
    $submittedPassword = $_POST['password']; // Şifrenin hash'lenmiş halini kontrol edeceğiz

    // 1. Sorguyu hazırla (Yer tutucular ile)
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");

    // 2. Değerleri bağla
    $stmt->bindParam(':username', $submittedUsername);

    // 3. Çalıştır
    $stmt->execute();

    // Sonucu çek
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Kullanıcı bulundu, şimdi şifreyi doğrula (hash'lenmiş şifre varsayımıyla)
        if (password_verify($submittedPassword, $user['password'])) {
            echo "Giriş başarılı! Hoş geldiniz, " . htmlspecialchars($user['username']);
            // Oturum başlatma, kullanıcıyı yönlendirme
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // header("Location: dashboard.php");
            // exit();
        } else {
            echo "Kullanıcı adı veya şifre yanlış.";
        }
    } else {
        echo "Kullanıcı adı veya şifre yanlış.";
    }
}
?>

<!-- HTML Form for login -->
<form method="post" action="">
    <label for="username">Kullanıcı Adı:</label>
    <input type="text" id="username" name="username" required><br>
    <label for="password">Şifre:</label>
    <input type="password" id="password" name="password" required><br>
    <button type="submit">Giriş Yap</button>
</form>
```

Bu örnekte, `password` sütununda şifrenin hash'lenmiş hali saklandığı varsayılmıştır (bkz. ilk soru). `password_verify()` ile güvenlik kontrolü yapılır. Kullanıcı adı ve şifre gibi hassas bilgileri işlerken her zaman hazırlanmış ifadeler kullanmalısınız.

#### 3. "Kayıp eşya resimleri için PHP'de dosya yüklemeyi güvenli hale nasıl getirebilirim?"

Dosya yükleme, web uygulamalarındaki en büyük güvenlik açıklarından biridir. Saldırganlar, kötü amaçlı betikler (örneğin PHP kabuk betikleri) yükleyerek sunucunuz üzerinde kontrol sağlayabilir veya çok büyük dosyalar yükleyerek hizmeti engelleme (DoS) saldırısı yapabilirler. Güvenli dosya yükleme için aşağıdaki adımları izlemek önemlidir:

1.  **Doğru MIME Tipini ve Uzantıyı Doğrula:**
    *   **MIME Tipi Kontrolü (`$_FILES['file']['type']`):** Bu, tarayıcının bildirdiği dosya türüdür. Ancak kolayca taklit edilebilir, bu yüzden tek başına güvenli değildir.
    *   **Dosya Uzantısı Kontrolü:** Sadece izin verilen uzantılara (`.jpg`, `.png`, `.gif` vb.) izin verin. `pathinfo()` fonksiyonunu kullanın. Ancak bu da taklit edilebilir (örneğin `resim.php.jpg` gibi).
    *   **Sunucu Taraflı Gerçek Dosya Tipi Kontrolü:** En güvenilir yöntemdir.
        *   **`getimagesize()` (Resimler İçin):** Yüklenen dosya bir resimse, bu fonksiyon dosyanın boyutlarını ve MIME tipini döndürür. Eğer geçerli bir resim değilse `false` döner. Bu, bir PHP betiğinin `.jpg` uzantısıyla yüklenmesini engeller.
        *   **`finfo_file()` (Genel Dosyalar İçin):** PHP'nin Fileinfo uzantısı ile dosyanın gerçek MIME tipini kontrol eder.

2.  **Dosya Boyutunu Doğrula:**
    *   **PHP Ayarları:** `php.ini` dosyasındaki `upload_max_filesize` ve `post_max_size` limitlerini aşmamalıdır.
    *   **Uygulama İçi Kontrol:** `$_FILES['file']['size']` değerini kullanarak kendi belirlediğiniz maksimum boyutu aşmadığından emin olun. Çok büyük dosyaların sunucunuzu yormasını veya disk alanını doldurmasını engeller.

3.  **Benzersiz Dosya Adları Oluştur:**
    *   Kullanıcının yüklediği orijinal dosya adını doğrudan kullanmayın. Bu, isim çakışmalarına (bir kullanıcının dosyası diğerininkinin üzerine yazılır) ve yol geçişi (path traversal) saldırılarına (örneğin `../../../config.php` gibi) yol açabilir.
    *   Bunun yerine, benzersiz bir ad oluşturmak için `uniqid()`, `md5(microtime())` veya rastgele dizeler kullanın. Orijinal uzantıyı korumayı unutmayın.

4.  **Dosyaları Web Kök Dizin Dışında Sakla:**
    *   Yüklenen dosyaları web sunucusunun doğrudan erişemediği bir dizinde saklamak en güvenli yoldur. Örneğin, `public_html` veya `www` dizininin dışında bir `uploads` klasörü.
    *   Eğer bu mümkün değilse, yükleme dizininin içinde `execute` (çalıştırma) izinlerini kapatın (Linux'ta `chmod -R 0644 uploads` gibi, ancak sunucu konfigürasyonuna göre değişebilir). Ayrıca `.htaccess` dosyası ile PHP betiklerinin çalışmasını engelleyebilirsiniz.

5.  **Hata Kontrolleri Yap:**
    *   `$_FILES['file']['error']` değerini kontrol edin (`UPLOAD_ERR_OK` olmalı).

6.  **Görsel İşlem (İsteğe Bağlı ama Önerilen):**
    *   Yüklenen resimlerin boyutlarını yeniden düzenleyin (resize) veya filigran ekleyin. Bu, resimlerin sunucuda aşırı yer kaplamasını engeller ve potansiyel olarak kötü amaçlı piksel verilerini temizleyebilir (çok nadir de olsa).

**Güvenli Dosya Yükleme Örneği:**

```php
<?php
// Resim yükleme için izin verilen uzantılar ve MIME tipleri
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['item_image'])) {
    $file = $_FILES['item_image'];

    // 1. Hata kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Dosya yüklenirken bir hata oluştu: " . $file['error'];
        exit;
    }

    // 2. Dosya boyutu kontrolü
    if ($file['size'] > $maxFileSize) {
        echo "Hata: Dosya boyutu " . ($maxFileSize / (1024 * 1024)) . "MB'tan büyük olamaz.";
        exit;
    }

    // 3. Uzantı ve MIME tipi kontrolü
    $fileInfo = pathinfo($file['name']);
    $fileExtension = strtolower($fileInfo['extension']);

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "Hata: Desteklenmeyen dosya uzantısı. Yalnızca JPG, JPEG, PNG, GIF kabul edilir.";
        exit;
    }

    // Gerçek MIME tipi kontrolü (resimler için getimagesize daha güvenlidir)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false || !in_array($imageInfo['mime'], $allowedMimeTypes)) {
        echo "Hata: Yüklenen dosya geçerli bir resim değil veya MIME tipi desteklenmiyor.";
        exit;
    }

    // 4. Benzersiz dosya adı oluşturma ve hedef dizin
    $uploadDir = 'uploads/'; // Bu dizin web kök dizini dışında olmalı veya özel izinleri olmalı
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Dizin yoksa oluştur
    }

    $uniqueFileName = uniqid('item_', true) . '.' . $fileExtension;
    $destinationPath = $uploadDir . $uniqueFileName;

    // 5. Dosyayı geçici konumdan kalıcı konuma taşı
    if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
        echo "Dosya başarıyla yüklendi: <a href='" . htmlspecialchars($destinationPath) . "'>" . htmlspecialchars($uniqueFileName) . "</a>";
        // Dosya yolunu veritabanına kaydet
    } else {
        echo "Dosya taşıma hatası.";
    }
} else {
    // Form gösterimi veya hata mesajı
}
?>

<form action="" method="post" enctype="multipart/form-data">
    <label for="item_image">Eşya Resmi:</label>
    <input type="file" id="item_image" name="item_image" accept="image/jpeg,image/png,image/gif" required><br>
    <button type="submit">Yükle</button>
</form>
```

Yukarıdaki `uploads/` dizini, uygulamanızın kök dizininde yer alabilir, ancak bu dizin için web sunucusu konfigürasyonunuzda özel kurallar (örneğin PHP dosyalarının yürütülmesini engellemek) tanımlamanız önemlidir.

#### 4. "dashboard.php sayfam neden veritabanındaki tüm eşyaları göstermiyor?"

`dashboard.php` sayfanızın veritabanındaki tüm eşyaları göstermemesinin birkaç yaygın nedeni olabilir. Bu tür sorunlar genellikle adım adım hata ayıklama (debugging) ile çözülür. İşte olası nedenler ve kontrol etmeniz gerekenler:

1.  **Veritabanı Bağlantı Sorunları:**
    *   **En Temel Sebep:** PHP dosyanız veritabanına doğru şekilde bağlanamıyor olabilir. Bağlantı bilgileri (ana bilgisayar, kullanıcı adı, şifre, veritabanı adı) yanlış olabilir veya veritabanı sunucusu çalışmıyor olabilir.
    *   **Kontrol:** Bağlantı kodunuzun hemen ardından bir hata kontrolü ekleyin.
        *   **PDO için:** `try-catch` bloğu kullanın ve `$e->getMessage()` ile hatayı görüntüleyin.
        *   **MySQLi için:** `mysqli_connect_error()` veya `$mysqli->connect_error` kullanın.
    *   **Örnek:**
        ```php
        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Hataları görmeyi sağlar
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage()); // Bağlantı hatası durumunda işlemi durdur ve mesajı göster
        }
        ```

2.  **SQL Sorgusu Hataları veya Sınırlamaları:**
    *   **Yanlış Sorgu:** `SELECT` sorgunuzda bir yazım hatası, yanlış tablo veya sütun adı olabilir.
    *   **`WHERE` Koşulu:** Sorgunuzda farkında olmadan bir `WHERE` koşulu olabilir (`WHERE user_id = :current_user_id` gibi) ve bu sadece belirli kullanıcılara ait eşyaları getiriyor olabilir. Veya bir filtreden (kategori, tarih) kalan eski bir koşul olabilir.
    *   **`LIMIT` Kısıtlaması:** Sorgunuzda bir `LIMIT` ifadesi olabilir (`LIMIT 10` gibi) ve bu da sadece ilk birkaç kaydı getirir.
    *   **Tablo/Sütun Adları:** Tablonuzun veya sütunlarınızın adları büyük/küçük harf duyarlı olabilir (özellikle Linux sunucularında) ve sorgunuzdaki adlarla eşleşmeyebilir.
    *   **Kontrol:** Sorgunuzu doğrudan bir veritabanı yönetim aracı (phpMyAdmin, DBeaver, MySQL Workbench) üzerinden çalıştırın. Beklediğiniz tüm eşyaların gelip gelmediğini görün. Eğer geliyorsa sorun PHP tarafında demektir.
    *   **Örnek Sorgu (Tüm eşyaları getirmesi gereken):**
        ```sql
        SELECT * FROM lost_items;
        ```
        Veya belirli sütunları almak için:
        ```sql
        SELECT id, title, description, category, reported_date FROM lost_items;
        ```

3.  **PHP Veri Çekme ve İşleme Hataları:**
    *   **Sorgu Çalışmıyor:** `$stmt->execute()` veya `$conn->query()` metodu başarısız olabilir.
    *   **Veri Çekme Döngüsü:** Verileri veritabanından çekerken kullandığınız döngü (örneğin `while ($row = $stmt->fetch(PDO::FETCH_ASSOC))`) düzgün çalışmıyor veya erken duruyor olabilir. Belki de `fetch()` fonksiyonunu yanlış kullanıyorsunuzdur veya hiç çağırmıyorsunuzdur.
    *   **Değişken Kapsamı:** Veritabanından gelen verileri tutan değişkenler (örneğin `$items = []`) yanlış bir kapsamda (scope) tanımlanmış olabilir ve HTML'e ulaşmıyor olabilir.
    *   **Hata Raporlama:** PHP hata raporlaması kapalı olabilir, bu da size sessizce oluşan hataları göstermez.
    *   **Kontrol:**
        *   Sorguyu çalıştırdıktan hemen sonra, `$stmt->rowCount()` (PDO) veya `$result->num_rows` (MySQLi) ile kaç kayıt döndüğünü kontrol edin. Eğer sıfır veya beklenenden azsa, sorun sorgudadır.
        *   Verileri çeken döngünün içine `var_dump($row);` veya `echo "<pre>"; print_r($row); echo "</pre>";` gibi ifadeler ekleyerek her bir satırın doğru şekilde okunup okunmadığını görün.
        *   PHP'nin en üstüne `error_reporting(E_ALL); ini_set('display_errors', 1);` ekleyerek tüm hataları görüntüleyin.

**Adım Adım Hata Ayıklama Yaklaşımı:**

1.  **Bağlantıyı Test Edin:** Sayfanın en başına sadece veritabanı bağlantısı kurup (veya bağlantı dosyasını dahil edip) hemen bir `if (!$conn) { die("Bağlantı hatası"); }` (veya PDO try/catch) ekleyip test edin. Eğer bir hata görüyorsanız, bağlantı bilgileriniz yanlış demektir.
2.  **Sorguyu Konsolda Çalıştırın:** PHP tarafında kullandığınız `SELECT` sorgusunu kopyalayın ve doğrudan phpMyAdmin, DBeaver, MySQL Workbench gibi bir araçta çalıştırın. Eğer orada tüm verileri görmüyorsanız, sorun SQL sorgunuzdadır.
3.  **PHP Tarafında Veri Çekmeyi Kontrol Edin:**
    ```php
    // Örnek PDO ile
    $sql = "SELECT * FROM lost_items";
    $stmt = $pdo->query($sql); // Basit SELECT için query kullanılabilir, ancak PDO::prepare önerilir

    if ($stmt) {
        $itemCount = $stmt->rowCount();
        echo "Veritabanından çekilen eşya sayısı: " . $itemCount . "<br>";

        // İlk birkaç eşyayı görmek için
        $counter = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC) && $counter < 5) {
            echo "Eşya ID: " . $row['id'] . ", Başlık: " . htmlspecialchars($row['title']) . "<br>";
            $counter++;
        }
        if ($itemCount > 0 && $counter == 0) {
            echo "Döngüye girilmedi veya veri çekilmedi. Problem döngüde olabilir.";
        }
    } else {
        echo "Sorgu çalıştırılamadı. Hata: " . implode(" ", $pdo->errorInfo()); // PDO hata bilgisi
    }
    ```
4.  **HTML Yapısını İnceleyin:** Tüm bu kontrollerden sonra hala görüntülenmiyorsa, verilerin HTML çıktısına doğru şekilde entegre edildiğinden emin olun. Bazen döngü dışına `</div>` gibi bir etiket yanlışlıkla yerleştirilebilir. Tarayıcınızın geliştirici araçlarını (F12) kullanarak HTML yapısını ve konsoldaki potansiyel JavaScript hatalarını kontrol edin.

Bu adımları izleyerek sorunun kaynağını genellikle kolayca tespit edebilirsiniz.

#### 5. "PHP dosyalarımda 'tanımsız indeks' veya 'tanımsız değişken' uyarılarını nasıl düzeltebilirim?"

"Undefined index" (tanımsız indeks) ve "Undefined variable" (tanımsız değişken) uyarıları, PHP'de çok sık karşılaşılan ancak genellikle kolayca düzeltilebilen hatalardır. Bu uyarılar, bir programın var olmayan bir dizi anahtarına veya tanımlanmamış bir değişkene erişmeye çalıştığında ortaya çıkar. PHP, bu durumlarda programın durmasını gerektirmese de, bu uyarılar potansiyel mantık hatalarını veya eksik kontrolleri işaret eder.

**1. "Undefined Index" Uyarısı:**

Bu uyarı genellikle `$_GET`, `$_POST`, `$_SESSION`, `$_FILES` gibi süperglobal dizilere veya kendi tanımladığınız dizilere yanlış bir anahtar (index) ile erişmeye çalıştığınızda meydana gelir. Yani, dizinin o anahtara sahip bir elemanı yoktur.

**Örnek Nedenler:**
*   Bir form gönderildiğinde, belirli bir alanın adının (`name` özniteliği) yanlış yazılması veya hiç gönderilmemesi, ancak PHP tarafında o alana erişilmeye çalışılması.
    *   HTML: `<input type="text" name="user_name">`
    *   PHP: `$_POST['username']` (Burada "username" yerine "user_name" olmalıydı.)
*   URL'de bir GET parametresi beklerken, o parametrenin URL'de olmaması.
    *   URL: `sayfa.php?id=123`
    *   PHP: `$_GET['product_id']` (Burada "product_id" yerine "id" olmalıydı.)
*   Oturum değişkenlerinin henüz ayarlanmamış olması.

**Çözümler:**

*   **`isset()` Fonksiyonunu Kullanma:** Bir değişkenin veya dizi anahtarının tanımlı olup olmadığını ve `NULL` olmadığını kontrol etmek için en yaygın ve önerilen yöntemdir.
    ```php
    // Örnek: POST verisi kontrolü
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    } else {
        $username = "Misafir"; // Varsayılan değer atama
    }

    // Veya daha kısa bir yöntem (ternary operatör):
    $username = isset($_POST['username']) ? $_POST['username'] : "Misafir";
    ```
*   **`empty()` Fonksiyonunu Kullanma:** Bir değişkenin veya dizi anahtarının boş olup olmadığını (yani `""`, `0`, `NULL`, `false`, boş dizi) kontrol eder. `isset()`'ten daha geniş bir kontrol sağlar. Eğer bir indeks veya değişken *tanımlı değilse* ve `empty()` ile kontrol edilirse, yine de "Undefined index/variable" uyarısı verir. Bu yüzden genellikle `isset()` ile birlikte veya `isset()` yerine kullanılır, ancak farklarını bilmek önemlidir.
    ```php
    // $_POST['email'] tanımlı DEĞİLSE veya BOŞ ise 'empty_email' olur
    $email = !empty($_POST['email']) ? $_POST['email'] : 'empty_email';
    // Not: Bu durumda eğer $_POST['email'] hiç yoksa yine de "Undefined index" uyarısı verir.
    // Doğrusu:
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $_POST['email'];
    } else {
        $email = 'empty_email';
    }
    ```
*   **Null Coalescing Operatörü (`??`) - PHP 7+:** Bu operatör, bir değişkenin veya dizi anahtarının tanımlı ve `NULL` olmadığından emin olmak için çok temiz bir yol sunar. Eğer sol taraf `NULL` veya tanımlı değilse, sağdaki değeri kullanır.
    ```php
    // $_POST['username'] tanımlı değilse veya NULL ise 'Misafir' değerini alır
    $username = $_POST['username'] ?? "Misafir";

    // $_GET['id'] tanımlı değilse veya NULL ise 0 değerini alır
    $id = $_GET['id'] ?? 0;
    ```
    Bu, çoğu "Undefined index" durumunu en zarif şekilde çözen yöntemdir.

**2. "Undefined Variable" Uyarısı:**

Bu uyarı, bir değişkeni kullanmaya çalıştığınızda henüz tanımlanmamış veya değer atanmamış olması durumunda ortaya çıkar.

**Örnek Nedenler:**
*   Bir `if` bloğu içinde bir değişken tanımlanmış, ancak `if` koşulu sağlanmadığında bu değişken hiç tanımlanmamış ve daha sonra kullanılmaya çalışılmış.
*   Yazım hatası (örneğin `$totalPrice` yerine `$totlePrice` kullanılması).
*   Fonksiyonlarda veya döngülerde değişken kapsamı (scope) sorunları.

**Çözümler:**

*   **Değişkenleri Başlangıçta Tanımlama/Başlatma:** Bir değişkeni kullanmadan önce mutlaka bir başlangıç değeriyle tanımlayın. Bu, özellikle koşullu bloklarda tanımlanabilecek değişkenler için önemlidir.
    ```php
    $message = ""; // Başlangıçta boş bir değerle tanımla

    if ($success) {
        $message = "İşlem başarılı!";
    } else {
        $message = "Bir hata oluştu.";
    }

    echo $message; // Artık $message her zaman tanımlı olacak
    ```
*   **`isset()` Kullanımı:** Bir değişkenin tanımlı olup olmadığını kontrol etmek için de kullanılabilir.
    ```php
    // $user_input değişkeni bir koşula bağlı olarak tanımlanıyor olabilir
    // if ($condition) { $user_input = "some value"; }
    
    if (isset($user_input)) {
        echo $user_input;
    } else {
        echo "Kullanıcı girdisi yok.";
    }
    ```
*   **Doğru Yazım Kontrolü:** Kodunuzu dikkatlice gözden geçirin ve değişken isimlerinde yazım hatası olup olmadığını kontrol edin.

**Genel İpuçları:**

*   **Hata Raporlama Ayarları:** Geliştirme ortamında PHP'nin tüm hataları göstermesini sağlayın. Bunu `php.ini` dosyasında `display_errors = On` ve `error_reporting = E_ALL` yaparak veya kodunuzun başına (sadece geliştirme sırasında!) ekleyerek yapabilirsiniz:
    ```php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ```
    Üretim ortamında `display_errors`'ı `Off` yapın ve hataları bir log dosyasına yazdırın (`log_errors = On`).
*   **Kodu Okunabilir Tutun:** Düzenli ve anlaşılır kod yazmak, bu tür hataları daha kolay fark etmenizi sağlar.

Bu yaklaşımlarla, PHP'de karşılaştığınız "Undefined index" ve "Undefined variable" uyarılarının büyük çoğunluğunu etkili bir şekilde düzeltebilirsiniz.

---

### 🧠 Veritabanı Tasarımı ve Sorgular

#### 6. "lost_found.sql dosyamda, eşyaları kategoriye ve tarihe göre getiren sorguyu nasıl optimize edebilirim?"

Veritabanı sorgularını optimize etmek, uygulamanızın performansını ve ölçeklenebilirliğini artırmak için kritik öneme sahiptir. `lost_items` tablosundaki eşyaları kategori ve tarihe göre filtreleyen veya sıralayan sorguların yavaş çalışmasının ana nedeni, veritabanının her sorguda tüm tabloyu taraması olabilir. Bu, büyük tablolar için çok maliyetlidir. Optimizasyon için anahtar kelime **indeksleme**dir.

**Optimizasyon Adımları:**

1.  **İndeksleme (Indexing):**
    *   **Neden Önemli?** İndeksler, veritabanının belirli sütunlardaki verilere daha hızlı erişmesini sağlar, tıpkı bir kitabın içindekiler dizini gibi. Bir sorgu `WHERE` veya `ORDER BY` koşulunda indekslenmiş bir sütunu kullandığında, veritabanı tüm tabloyu taramak yerine doğrudan ilgili verilere gidebilir.
    *   **Hangi Sütunlara İndeks Ekleme:**
        *   `WHERE` koşullarında sıkça kullanılan sütunlar: `category_id`, `found_date`, `location_id`, `status` (kayıp/bulundu) gibi.
        *   `ORDER BY` koşullarında kullanılan sütunlar: `found_date` (veya `reported_date`), `title` gibi.
        *   `JOIN` operasyonlarında kullanılan sütunlar (bir `lost_items` tablonuz varsa ve `categories` veya `users` tablolarıyla birleştiriyorsanız, birincil ve yabancı anahtarlara indeks eklemek önemlidir).
    *   **İndeks Oluşturma (SQL):**

        ```sql
        -- lost_items tablosunda kategori_id sütununa tekil bir indeks ekle
        CREATE INDEX idx_lost_items_category_id ON lost_items (category_id);

        -- lost_items tablosunda reported_date (veya found_date) sütununa tekil bir indeks ekle
        CREATE INDEX idx_lost_items_reported_date ON lost_items (reported_date);

        -- Hem kategori hem de tarihe göre filtreleme veya sıralama yapılıyorsa, bileşik indeks faydalı olabilir
        -- Bu indeks, sorgu hem category_id hem de reported_date'i birlikte kullandığında en verimli çalışır.
        CREATE INDEX idx_lost_items_category_date ON lost_items (category_id, reported_date);
        ```
    *   **Dikkat:** Çok fazla indeks, `INSERT`, `UPDATE` ve `DELETE` işlemlerini yavaşlatabilir çünkü her veri değişikliğinde indekslerin de güncellenmesi gerekir. Bu yüzden sadece sorgu performansı için gerçekten gerekli olan sütunlara indeks ekleyin.

2.  **`EXPLAIN` Kullanımı:**
    *   **Ne İşe Yarar?** Bir SQL sorgusunun nasıl çalıştırılacağını (yani veritabanı motorunun hangi indeksleri kullanacağını, hangi tabloları tarayacağını vb.) anlamak için `EXPLAIN` anahtar kelimesini kullanın.
    *   **Nasıl Kullanılır?** Sorgunuzun başına `EXPLAIN` yazın:
        ```sql
        EXPLAIN SELECT * FROM lost_items WHERE category_id = 5 AND reported_date > '2023-01-01' ORDER BY reported_date DESC;
        ```
    *   **Yorumlama:** `EXPLAIN` çıktısında `type` sütunu `ALL` (tam tablo taraması) yerine `ref`, `range` veya `const` gibi daha verimli bir değer gösteriyorsa, indeksleriniz etkili bir şekilde kullanılıyor demektir. `key` sütunu kullanılan indeksi gösterir.

3.  **Sadece Gerekli Sütunları Seçme:**
    *   `SELECT *` yerine sadece ihtiyacınız olan sütunları belirtmek, ağ üzerinden daha az veri transferi sağlar ve veritabanının daha az işlem yapmasına yardımcı olabilir.
    *   **Kötü:** `SELECT * FROM lost_items WHERE category_id = 5;`
    *   **İyi:** `SELECT id, title, description, image_url FROM lost_items WHERE category_id = 5;`

4.  **Doğru Veri Tipleri:**
    *   Sütunlar için doğru veri tiplerini kullanmak (örneğin tarih için `DATE` veya `DATETIME`, id'ler için `INT`, küçük metinler için `VARCHAR` yerine `TEXT` kullanmamak), veritabanı depolamasını ve sorgu performansını iyileştirebilir. Örneğin, `reported_date` sütununun `DATETIME` veya `TIMESTAMP` olması önemlidir.

**Örnek Optimize Edilmiş Sorgu ve Uygulama:**

Diyelim ki kullanıcı `kategori_id` 5 olan ve `2023-01-01` tarihinden sonra bildirilen eşyaları en yeniye göre sıralanmış şekilde görmek istiyor:

```php
<?php
// ... PDO veritabanı bağlantısı ($pdo) varsayalım ...

$categoryId = $_GET['category'] ?? null; // URL'den kategori al, yoksa NULL
$startDate = $_GET['start_date'] ?? null; // URL'den başlangıç tarihi al

$sql = "SELECT id, title, description, image_url, reported_date, category_id
        FROM lost_items
        WHERE 1=1"; // Her zaman doğru olan bir başlangıç koşulu

$params = []; // Hazırlanmış ifadeler için parametreler dizisi

if ($categoryId !== null) {
    $sql .= " AND category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

if ($startDate !== null) {
    $sql .= " AND reported_date >= :start_date";
    $params[':start_date'] = $startDate;
}

$sql .= " ORDER BY reported_date DESC"; // Her zaman tarihe göre azalan sırala

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Parametreleri doğrudan execute'a ver

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Eşyaları listele
    foreach ($items as $item) {
        echo "<p>ID: " . htmlspecialchars($item['id']) . ", Başlık: " . htmlspecialchars($item['title']) . ", Tarih: " . htmlspecialchars($item['reported_date']) . "</p>";
    }

} catch (PDOException $e) {
    echo "Sorgu hatası: " . $e->getMessage();
}
?>

<!-- Basit bir filtreleme formu -->
<form action="" method="get">
    <label for="category">Kategori:</label>
    <select name="category" id="category">
        <option value="">Tümü</option>
        <option value="1">Elektronik</option>
        <option value="5">Giysi</option>
        <!-- Diğer kategoriler -->
    </select><br>
    <label for="start_date">Başlangıç Tarihi:</label>
    <input type="date" name="start_date" id="start_date"><br>
    <button type="submit">Filtrele</button>
</form>
```

Bu örnekte, `WHERE 1=1` ile başlayıp koşulları dinamik olarak ekleyerek, kullanıcının seçimine göre sorguyu oluşturuyoruz. Hazırlanmış ifadeler, hem güvenlik hem de performans açısından önemlidir (veritabanı sorguyu bir kez derleyebilir). İndeksler doğru yerde olduğunda, veritabanı bu filtreleri ve sıralamayı çok daha hızlı gerçekleştirecektir.

#### 7. "MySQL'de kullanıcılarla onların paylaştığı eşyalar arasında ilişki nasıl kurulur?"

Kullanıcılar ve onların paylaştığı eşyalar arasındaki ilişki, tipik bir **bire-çok (one-to-many)** ilişkisidir. Bir kullanıcı birden fazla eşya paylaşabilir, ancak her eşya yalnızca bir kullanıcı tarafından paylaşılır (en azından bu senaryoda). Bu ilişkiyi veritabanında kurmak için **yabancı anahtar (Foreign Key)** kullanılır.

**Adım 1: Gerekli Tabloları Oluşturma**

Öncelikle, kullanıcıları ve eşyaları depolayacak iki ana tabloya ihtiyacımız var: `users` ve `lost_items` (veya `found_items` veya tek bir `items` tablosu).

**`users` Tablosu:**
Kullanıcı bilgilerini içerir. Birincil anahtarı (Primary Key) genellikle `id` sütunu olur.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Şifre hash'i için uzun olmalı
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`lost_items` Tablosu:**
Kayıp veya bulunan eşyaların bilgilerini içerir. Bu tabloda, eşyayı hangi kullanıcının paylaştığını belirlemek için bir sütuna ihtiyacımız var. Bu sütun, `users` tablosundaki `id` sütununu referans alacaktır. Buna **yabancı anahtar (Foreign Key)** denir.

```sql
CREATE TABLE lost_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Yabancı anahtar: Bu eşyayı paylaşan kullanıcının ID'si
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    location VARCHAR(100),
    item_status ENUM('lost', 'found') NOT NULL, -- 'lost' veya 'found'
    reported_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255),
    contact_info VARCHAR(255),

    -- Yabancı anahtar tanımı:
    -- 'user_id' sütunu, 'users' tablosundaki 'id' sütununa referans verir.
    -- ON DELETE CASCADE: Eğer bir kullanıcı silinirse, o kullanıcının tüm eşyaları da silinir.
    -- ON DELETE SET NULL: Eğer bir kullanıcı silinirse, o kullanıcının eşyalarının user_id'si NULL olur (user_id NULL olabilmeli).
    -- ON DELETE RESTRICT: Eğer eşyaları varsa, kullanıcı silinemez. (varsayılan)
    -- Genellikle bir kullanıcının eşyalarının kalması istenir, bu yüzden SET NULL veya RESTRICT daha uygun olabilir.
    CONSTRAINT fk_user_item
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE -- Kullanıcı silinirse eşyaları silinemez, ID'si değişirse güncellenir.
);
```

**`fk_user_item` Kısıtlaması:** Bu satır, `lost_items` tablosundaki `user_id` sütununun, `users` tablosundaki `id` sütunu ile ilişkilendirildiğini belirtir. Bu, veritabanı düzeyinde referans bütünlüğünü sağlar: `lost_items.user_id` değerinin her zaman `users.id` tablosunda var olan bir `id`'ye karşılık gelmesi gerekir.

**`ON DELETE` ve `ON UPDATE` Davranışları:**
*   `ON DELETE RESTRICT`: Varsayılan davranıştır. Bir kullanıcıya bağlı eşyalar varken o kullanıcıyı `users` tablosundan silmeye çalışırsanız, veritabanı buna izin vermez. Önce eşyaları silmeniz gerekir. Bu genellikle güvenli bir seçimdir.
*   `ON DELETE CASCADE`: Eğer bir kullanıcı silinirse, o kullanıcının `lost_items` tablosundaki tüm ilgili eşya kayıtları da otomatik olarak silinir. (Dikkatli kullanılmalı!)
*   `ON DELETE SET NULL`: Eğer bir kullanıcı silinirse, o kullanıcının `lost_items` tablosundaki eşyalarının `user_id` sütunu `NULL` olarak ayarlanır. Bu durumda `user_id` sütununun `NULL` değer kabul etmesi gerekir (`user_id INT NULL`).
*   `ON UPDATE CASCADE`: Eğer `users` tablosundaki bir kullanıcının `id`'si değişirse (ki bu genellikle tavsiye edilmez), `lost_items` tablosundaki ilgili `user_id`'ler de otomatik olarak güncellenir.

**Adım 2: İlişkili Verileri Sorgulama (PHP ve SQL)**

Bu ilişkiyi kurduktan sonra, bir kullanıcının paylaştığı tüm eşyaları veya bir eşyayı paylaşan kullanıcının bilgilerini kolayca sorgulayabilirsiniz.

**Örnek 1: Bir Kullanıcının Tüm Eşyalarını Bulma**

Belirli bir kullanıcının (örneğin ID'si 1 olan) tüm kayıp eşyalarını listelemek için:

```sql
SELECT *
FROM lost_items
WHERE user_id = 1;
```

**Örnek 2: Bir Eşyayı Paylaşan Kullanıcının Bilgileriyle Birlikte Çekme (`JOIN`)**

Bir eşyanın detaylarını gösterirken, o eşyayı bildiren kullanıcının kullanıcı adını veya iletişim bilgilerini de göstermek isteyebilirsiniz. Bunun için `JOIN` (birleştirme) işlemi kullanılır.

```sql
SELECT
    li.id,
    li.title,
    li.description,
    li.reported_date,
    u.username AS reporter_username, -- Kullanıcının kullanıcı adı
    u.email AS reporter_email       -- Kullanıcının e-posta adresi
FROM
    lost_items AS li
JOIN
    users AS u ON li.user_id = u.id
WHERE
    li.id = 123; -- Belirli bir eşyanın ID'si
```

Bu `JOIN` sorgusu, `lost_items` tablosundan `li` takma adı ve `users` tablosundan `u` takma adı kullanarak, `user_id` sütunları eşleşen satırları birleştirir. Böylece tek bir sorgu ile hem eşya hem de onu bildiren kullanıcı hakkında bilgi alabilirsiniz.

**PHP Uygulaması:**

```php
<?php
// ... PDO veritabanı bağlantısı ($pdo) varsayalım ...

// Örnek: Belirli bir kullanıcının (örneğin oturumdaki kullanıcının) eşyalarını listeleme
session_start();
$currentUserId = $_SESSION['user_id'] ?? null;

if ($currentUserId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE user_id = :user_id ORDER BY reported_date DESC");
        $stmt->bindParam(':user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        $userItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Paylaştığınız Eşyalar:</h2>";
        if (count($userItems) > 0) {
            foreach ($userItems as $item) {
                echo "<p><strong>" . htmlspecialchars($item['title']) . "</strong> - " . htmlspecialchars($item['reported_date']) . "</p>";
            }
        } else {
            echo "<p>Henüz hiçbir eşya paylaşmadınız.</p>";
        }

    } catch (PDOException $e) {
        echo "Eşyalar çekilirken hata oluştu: " . $e->getMessage();
    }
} else {
    echo "Giriş yapmalısınız.";
}
?>
```
Bu yapı, uygulamanızda kullanıcıların kendi ilanlarını yönetmelerine veya başkalarının ilanlarını kimin verdiğini görmenize olanak tanır.

#### 8. "Veritabanımda kayıp eşyalar için tam metin arama (full-text search) özelliğini nasıl ekleyebilirim?"

Geleneksel `LIKE '%kelime%'` sorguları, küçük tablolar için yeterli olsa da, büyük metin alanlarında veya büyük veri kümelerinde çok yavaş ve verimsiz hale gelirler. Ayrıca `LIKE` sorguları genellikle arama terimlerinin sıralamasını veya alaka düzeyini (relevance) göz önünde bulundurmaz. İşte bu noktada **tam metin arama (Full-Text Search)** devreye girer.

MySQL, MyISAM ve InnoDB depolama motorları için yerleşik tam metin arama yetenekleri sunar. InnoDB için tam metin arama desteği MySQL 5.6 ile gelmiştir ve modern uygulamalar için tercih edilmelidir.

**Adım 1: Tam Metin Dizini (FULLTEXT Index) Oluşturma**

Tam metin aramayı etkinleştirmek için, arama yapmak istediğiniz metin sütunlarına bir `FULLTEXT` dizini eklemeniz gerekir. Genellikle `title` (başlık) ve `description` (açıklama) gibi sütunlar buna adaydır.

**Örnek: `lost_items` tablosuna `FULLTEXT` dizini ekleme**

```sql
ALTER TABLE lost_items
ADD FULLTEXT INDEX idx_fulltext_items (title, description);

-- Alternatif olarak, tablo oluşturulurken de eklenebilir:
-- CREATE TABLE lost_items (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     title VARCHAR(100) NOT NULL,
--     description TEXT,
--     -- ... diğer sütunlar ...
--     FULLTEXT INDEX idx_fulltext_items (title, description)
-- );
```
*   `idx_fulltext_items`: İndeksin adıdır, isteğe bağlıdır.
*   `(title, description)`: Bu indeksin hangi sütunlar üzerinde oluşturulduğunu gösterir. Birden fazla sütun tanımlayabilirsiniz; MySQL bunları birleşik bir tam metin dizini olarak işler.

**Adım 2: `MATCH AGAINST` Operatörünü Kullanarak Arama Yapma**

`FULLTEXT` dizini oluşturulduktan sonra, arama yapmak için `MATCH() AGAINST()` sözdizimini kullanabilirsiniz. Bu, `WHERE` koşulunda kullanılır.

**Temel Sözdizimi:**

```sql
SELECT column1, column2, ..., MATCH(column_name(s)) AGAINST('search_query' [search_mode]) AS relevance
FROM table_name
WHERE MATCH(column_name(s)) AGAINST('search_query' [search_mode]);
```

**Arama Modları:**

1.  **`IN NATURAL LANGUAGE MODE` (Doğal Dil Modu - Varsayılan):**
    *   Bu mod, kullanıcıların doğal bir dille arama yapmasını sağlar (Google araması gibi).
    *   Sorguyu en alakalı sonuçlara göre sıralar.
    *   Stop kelimeleri (örneğin "bir", "ve", "de") genellikle göz ardı edilir.
    *   Minimum kelime uzunluğu vardır (varsayılan olarak 4 karakter).
    ```sql
    SELECT id, title, description,
           MATCH(title, description) AGAINST('anahtarlık telefon') AS score
    FROM lost_items
    WHERE MATCH(title, description) AGAINST('anahtarlık telefon')
    ORDER BY score DESC;
    ```
    Burada `score` sütunu, arama terimleriyle eşleşmenin alaka düzeyini gösteren bir değerdir; daha yüksek puan, daha alakalı demektir.

2.  **`IN BOOLEAN MODE` (Boolean Modu):**
    *   Daha ince arama kontrolü sağlar. Operatörler kullanılarak (örneğin `+` zorunlu kelime, `-` hariç tutulan kelime, `*` joker karakter) arama yapılabilir.
    *   Alaka düzeyine göre sıralama yapmaz (genellikle sıralamayı kendiniz `ORDER BY` ile belirlersiniz).
    *   Stop kelimelerini ve minimum kelime uzunluğunu göz ardı etmez.
    ```sql
    -- "anahtarlık" kelimesini içeren, ama "kırmızı" kelimesini içermeyen eşyaları bul
    SELECT id, title, description
    FROM lost_items
    WHERE MATCH(title, description) AGAINST('+anahtarlık -kırmızı' IN BOOLEAN MODE);

    -- "telefon" veya "şarj" kelimelerinden birini içeren eşyaları bul
    SELECT id, title, description
    FROM lost_items
    WHERE MATCH(title, description) AGAINST('telefon şarj' IN BOOLEAN MODE);
    ```

**PHP Uygulaması:**

```php
<?php
// ... PDO veritabanı bağlantısı ($pdo) varsayalım ...

$searchQuery = $_GET['q'] ?? ''; // Arama terimi
$searchQuery = trim($searchQuery); // Baştaki ve sondaki boşlukları temizle

if (!empty($searchQuery)) {
    try {
        // Doğal dil modu ile arama
        $sql = "SELECT id, title, description, image_url,
                       MATCH(title, description) AGAINST(:search_query IN NATURAL LANGUAGE MODE) AS relevance
                FROM lost_items
                WHERE MATCH(title, description) AGAINST(:search_query IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':search_query', $searchQuery);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Arama Sonuçları for '" . htmlspecialchars($searchQuery) . "':</h2>";
        if (count($results) > 0) {
            foreach ($results as $item) {
                echo "<p><strong>" . htmlspecialchars($item['title']) . "</strong> (Alaka Düzeyi: " . round($item['relevance'], 2) . ")<br>";
                echo htmlspecialchars(substr($item['description'], 0, 150)) . "...</p>"; // Açıklamadan bir kısmını göster
            }
        } else {
            echo "<p>Eşleşen sonuç bulunamadı.</p>";
        }

    } catch (PDOException $e) {
        echo "Arama hatası: " . $e->getMessage();
    }
}
?>

<form action="" method="get">
    <label for="q">Arama:</label>
    <input type="search" id="q" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Eşya başlığı veya açıklaması">
    <button type="submit">Ara</button>
</form>
```

**Dikkat Edilmesi Gerekenler:**

*   **InnoDB vs. MyISAM:** Modern MySQL versiyonlarında (5.6 ve sonrası), InnoDB depolama motoru tam metin aramayı destekler ve genellikle daha iyidir. Daha eski bir MySQL kullanıyorsanız MyISAM kullanmanız gerekebilir, ancak MyISAM genel olarak transaction ve referans bütünlüğü desteği olmadığı için önerilmez.
*   **Minimum Kelime Uzunluğu:** MySQL'in varsayılan minimum kelime uzunluğu `ft_min_word_len` ayarıdır (genellikle 4). Daha kısa kelimeleri aramak istiyorsanız bu ayarı `my.cnf` veya `my.ini` dosyanızda değiştirmeniz ve MySQL hizmetini yeniden başlatmanız gerekebilir.
*   **Stop Kelimeleri:** MySQL, bazı yaygın kelimeleri (stop words) tam metin dizininden hariç tutar. Kendi stop kelime listenizi tanımlayabilirsiniz.
*   **Gelişmiş Çözümler:** Çok büyük veri setleri veya daha karmaşık arama gereksinimleriniz varsa (örneğin fasetli arama, yazım düzeltme, eş anlamlılar), Elasticsearch veya Apache Solr gibi özel arama motorlarını düşünebilirsiniz. Bunlar MySQL'in yerleşik tam metin aramasından daha gelişmiş yetenekler sunar ancak ayrı bir kurulum ve yönetim gerektirirler.

Bu adımlarla, kayıp eşyalarınız için etkili bir tam metin arama özelliği ekleyebilirsiniz.

#### 9. "Kayıp eşya veritabanında tekrar eden kayıtları nasıl engelleyebilirim?"

Tekrar eden kayıtlar (duplicate records), veritabanının bütünlüğünü bozar, gereksiz veri depolamasına yol açar ve sorgu sonuçlarını yanıltıcı hale getirebilir. Kayıp eşya veritabanınızda tekrar eden kayıtları engellemek için hem veritabanı seviyesinde hem de uygulama seviyesinde önlemler alabilirsiniz.

**1. Veritabanı Seviyesinde Engelleme (Önerilen ve En Güvenilir Yöntem):**

Veritabanı seviyesindeki kısıtlamalar, veritabanına veri girilirken tutarlılığı garantileyen en güçlü yöntemdir.

*   **`UNIQUE` Kısıtlaması/İndeksi:**
    *   Belirli bir veya birden fazla sütunun birleşimi için benzersizlik sağlar. Eğer bu sütunların değerleri zaten veritabanında varsa, yeni bir kayıt eklemeye çalışırken hata (duplicate key error) alırsınız.
    *   **Neye göre "tekrar eden"?** Bu, uygulamanızın mantığına bağlıdır. "Tekrar eden bir kayıp eşya" ne anlama gelir?
        *   Aynı başlık ve aynı kategoriye sahip bir eşya mı?
        *   Aynı başlık, açıklama ve kullanıcı tarafından aynı gün bildirilen bir eşya mı?
        *   Aynı başlık ve konumda raporlanan bir eşya mı?

    **Örnek: `title`, `description` ve `user_id` birleşiminin benzersiz olmasını sağlama**
    Böylece aynı kullanıcı, aynı başlık ve açıklamayla iki kez aynı eşyayı bildiremez.

    ```sql
    -- Mevcut bir tabloya UNIQUE kısıtlama ekleme
    ALTER TABLE lost_items
    ADD CONSTRAINT UQ_LostItem_TitleDescUser UNIQUE (title, description, user_id);

    -- Tablo oluşturulurken UNIQUE kısıtlama ekleme
    CREATE TABLE lost_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        user_id INT NOT NULL,
        -- ... diğer sütunlar ...
        CONSTRAINT UQ_LostItem_TitleDescUser UNIQUE (title, description, user_id)
    );
    ```
    **Avantajı:** Veritabanı motoru tarafından zorlandığı için uygulamanızdaki bir hata bile olsa veri tutarsızlığı olmaz.
    **Dezavantajı:** Hata oluştuğunda (duplicate key), uygulamanızın bu hatayı yakalaması ve kullanıcıya dostça bir mesaj göstermesi gerekir.

*   **`PRIMARY KEY`:**
    *   Tablonun her satırını benzersiz şekilde tanımlayan bir veya daha fazla sütun kümesidir. Otomatik olarak bir `UNIQUE` kısıtlama uygular ve `NOT NULL` olmak zorundadır.
    *   Çoğu tabloda `id` sütunu `PRIMARY KEY`'dir ve her eşyanın kendi benzersiz kimliği olmasını sağlar. Ancak bu, eşyanın kendisinin (içeriğinin) benzersizliğini garanti etmez.

**2. Uygulama Seviyesinde Engelleme (Veritabanı Kısıtlamalarına Ek Olarak):**

Uygulama seviyesinde kontroller, kullanıcıya daha iyi bir deneyim sunabilir ve veritabanına bile ulaşmadan potansiyel yinelenmeleri eleyebilir. Ancak bu yöntem, tek başına kullanıldığında veritabanı bütünlüğünü %100 garanti etmez (örneğin, aynı anda birden fazla isteğin gelmesi durumunda yarış koşulları oluşabilir). Bu yüzden veritabanı kısıtlamaları ile birlikte kullanılmalıdır.

*   **`INSERT` Öncesi Kontrol (`SELECT` ile):**
    *   Kullanıcı yeni bir eşya bildirdiğinde, `INSERT` sorgusunu çalıştırmadan önce, benzer bir eşyanın zaten var olup olmadığını kontrol etmek için bir `SELECT` sorgusu çalıştırabilirsiniz.
    *   **Senaryo:** Kullanıcı "Kırmızı Cüzdan" diye bir eşya bildiriyor. Veritabanında, bu kullanıcının zaten "Kırmızı Cüzdan" adında bir eşya bildirip bildirmediğini kontrol edin.

    **PHP Örneği:**

    ```php
    <?php
    // ... PDO veritabanı bağlantısı ($pdo) varsayalım ...

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $userId = $_SESSION['user_id']; // Oturumdaki kullanıcı ID'si

        // 1. Önce veritabanında benzer bir kayıt olup olmadığını kontrol et
        $checkSql = "SELECT COUNT(*) FROM lost_items WHERE title = :title AND description = :description AND user_id = :user_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':title', $title);
        $checkStmt->bindParam(':description', $description);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn(); // Eşleşen kayıt sayısını al

        if ($count > 0) {
            echo "Bu başlık ve açıklama ile zaten bir eşya bildirdiniz. Lütfen farklı bir eşya bildirin veya mevcut ilanı düzenleyin.";
        } else {
            // Benzer bir kayıt yok, şimdi ekleme işlemini yapabiliriz
            $insertSql = "INSERT INTO lost_items (title, description, user_id, /* ... diğer sütunlar ... */) VALUES (:title, :description, :user_id, /* ... diğer değerler ... */)";
            $insertStmt = $pdo->prepare($insertSql);
            // ... parametreleri bağla ve execute et ...
            if ($insertStmt->execute()) {
                echo "Eşya başarıyla eklendi!";
            } else {
                echo "Eşya eklenirken bir hata oluştu.";
            }
        }
    }
    ?>
    ```
    **Avantajı:** Kullanıcıya daha anlamlı ve anında geri bildirim sağlar.
    **Dezavantajı:** Uygulama seviyesindeki kontrol ile veritabanı kaydı arasına çok kısa bir süre de olsa başka bir işlem girebilir ve yine de çift kayıt oluşabilir (ancak `UNIQUE` kısıtlama bu durumu son kertede engeller).

*   **`INSERT IGNORE` (MySQL Özelliği):**
    *   Eğer bir `UNIQUE` veya `PRIMARY KEY` ihlali olursa, `INSERT IGNORE` komutu hatayı yoksayar ve kayıt eklenmez. İşlem başarısız olur, ancak hata mesajı üretmez.
    *   **Kullanım:** `INSERT IGNORE INTO lost_items (...) VALUES (...)`
    *   **Dikkat:** Diğer hataları da sessizce yoksayabilir, bu yüzden dikkatli kullanılmalıdır.

*   **`ON DUPLICATE KEY UPDATE` (MySQL Özelliği):**
    *   Eğer eklemeye çalıştığınız kayıt bir `UNIQUE` veya `PRIMARY KEY` kısıtlamasını ihlal ediyorsa, bunun yerine mevcut kaydı günceller.
    *   **Kullanım:**
        ```sql
        INSERT INTO lost_items (id, title, description, user_id)
        VALUES (123, 'Kırmızı Cüzdan', 'İçinde kredi kartları vardı', 1)
        ON DUPLICATE KEY UPDATE
            description = VALUES(description), -- Yeni açıklamayla güncelle
            reported_date = CURRENT_TIMESTAMP; -- Güncelleme tarihini de yenile
        ```
    *   **Dikkat:** Bu, "tekrar eden kaydı engellemek"ten ziyade "tekrar eden kayıt varsa güncellemek" için kullanılır. İlanın zaten var olduğunu kabul edip üzerini yazmak istediğiniz durumlarda faydalıdır.

**Özetle:**

En güvenli ve önerilen yöntem, veritabanı seviyesinde uygun `UNIQUE` kısıtlamaları kullanmaktır. Bu, veri bütünlüğünü garantiler. Kullanıcı deneyimini iyileştirmek için, uygulama seviyesinde `INSERT` yapmadan önce bir `SELECT` ile kontrol ekleyebilirsiniz. Bu, hem hataları önler hem de kullanıcıya daha açıklayıcı mesajlar sunar.

---

### 🖥️ Kullanıcı Arayüzü (UI) / Kullanıcı Deneyimi (UX)

#### 10. "Bootstrap kullanarak kayıp/bulunan eşya listesini nasıl mobil uyumlu (responsive) hale getirebilirim?"

Mobil uyumluluk (responsive design), web sitenizin farklı ekran boyutlarına (masaüstü, tablet, telefon) otomatik olarak uyum sağlaması anlamına gelir. Bootstrap, bu konuda size büyük ölçüde yardımcı olan bir CSS çerçevesidir. Temel olarak **ızgara sistemi (grid system)** ve **mobil öncelikli (mobile-first)** yaklaşımıyla çalışır.

**Bootstrap'ın Temel Responsive Bileşenleri:**

1.  **Kapsayıcılar (Containers):** İçeriğin genişliğini sınırlamak ve ortalamak için kullanılır.
    *   `.container`: Sabit genişlikte, ekran boyutuna göre ayarlanır.
    *   `.container-fluid`: Her zaman %100 genişlikte.

2.  **Izgara Sistemi (Grid System):** Bootstrap'ın kalbidir. Sayfayı 12 sütuna böler ve bu sütunları farklı ekran boyutlarında nasıl dağıtacağınızı kontrol etmenizi sağlar.
    *   **Sınıf Önekleri:**
        *   `col-`: Ekstra küçük cihazlar (varsayılan)
        *   `col-sm-`: Küçük cihazlar (≥576px)
        *   `col-md-`: Orta boy cihazlar (≥768px)
        *   `col-lg-`: Geniş cihazlar (≥992px)
        *   `col-xl-`: Ekstra geniş cihazlar (≥1200px)
        *   `col-xxl-`: En geniş cihazlar (≥1400px)
    *   **Örnek Kullanım:**
        *   `col-12`: Tüm ekranlarda 12 sütunu kaplar (tam genişlik).
        *   `col-md-6`: Orta boy ekranlardan itibaren 6 sütun kaplar, daha küçük ekranlarda 12 sütun (tam genişlik) olur.
        *   `col-lg-4`: Geniş ekranlardan itibaren 4 sütun kaplar, orta boy ekranlarda 6, daha küçüklerde 12 olur.

3.  **Bootstrap Kartları (Cards):** Tek bir eşya bilgisini (resim, başlık, açıklama) düzenli ve çekici bir şekilde göstermek için idealdirler. Responsive davranışları grid sistemiyle harika çalışır.

4.  **Tablo Duyarlılığı (`.table-responsive`):** Eğer eşyaları bir HTML tablosu içinde gösteriyorsanız, küçük ekranlarda yatay kaydırma çubuğu ekleyerek tablonun taşmasını engeller.

**Kayıp/Bulunan Eşya Listesini Responsive Yapma Örneği:**

Diyelim ki her bir kayıp/bulunan eşyayı bir kart olarak göstermek istiyorsunuz.

**HTML Yapısı (PHP Döngüsü İçinde):**

```html
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıp/Bulunan Eşyalar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .item-card {
            margin-bottom: 20px; /* Kartlar arasında boşluk bırakır */
            height: 100%; /* Kartların aynı yükseklikte olmasını sağlar */
        }
        .item-card img {
            max-height: 200px; /* Resimlerin maksimum yüksekliğini sınırlar */
            object-fit: cover; /* Resimlerin kart içinde düzgün sığmasını sağlar */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Kayıp ve Bulunan Eşyalar</h1>

    <div class="row">
        <?php
        // Burası veritabanından çekilen eşyaları temsil eden bir örnek döngü
        // Gerçek uygulamada PDO/MySQLi ile veritabanından veri çekeceksiniz.
        $items = [
            ['id' => 1, 'title' => 'Kırmızı Cüzdan', 'description' => 'Küçük, deri, kırmızı cüzdan. Kimlikler vardı.', 'image_url' => 'https://via.placeholder.com/300x200/FF0000/FFFFFF?text=Cüzdan', 'status' => 'lost'],
            ['id' => 2, 'title' => 'iPhone 13', 'description' => 'Siyah renkli, kırık ekranlı iPhone 13.', 'image_url' => 'https://via.placeholder.com/300x200/000000/FFFFFF?text=Telefon', 'status' => 'found'],
            ['id' => 3, 'title' => 'Mavi Şemsiye', 'description' => 'Katlanabilir, otomatik, mavi şemsiye.', 'image_url' => 'https://via.placeholder.com/300x200/0000FF/FFFFFF?text=Şemsiye', 'status' => 'lost'],
            ['id' => 4, 'title' => 'Ders Kitabı', 'description' => 'Matematik ders kitabı, kapağında sarı bir kedi var.', 'image_url' => 'https://via.placeholder.com/300x200/800080/FFFFFF?text=Kitap', 'status' => 'found'],
            ['id' => 5, 'title' => 'Anahtarlık', 'description' => 'Üzerinde araba anahtarı ve bir şirin anahtarlık var.', 'image_url' => 'https://via.placeholder.com/300x200/FFA500/FFFFFF?text=Anahtarlık', 'status' => 'lost'],
        ];

        foreach ($items as $item) {
            $cardClass = ($item['status'] == 'lost') ? 'border-danger' : 'border-success'; // Kayıpsa kırmızı, bulunmuşsa yeşil kenarlık
        ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card item-card <?php echo $cardClass; ?>">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="card-text text-muted">Durum: <?php echo ($item['status'] == 'lost') ? 'Kayıp' : 'Bulundu'; ?></p>
                        <p class="card-text"><?php echo htmlspecialchars(substr($item['description'], 0, 70)); ?>...</p>
                        <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">Detayları Gör</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Bootstrap JS (Popper.js ile birlikte) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

**Açıklama:**

*   **`<div class="container mt-5">`**: İçeriği merkeze alır ve üstten biraz boşluk bırakır.
*   **`<div class="row">`**: Bootstrap grid sisteminde bir satır tanımlar. İçindeki sütunlar bu satır içinde yer alır.
*   **`<div class="col-12 col-sm-6 col-md-4 col-lg-3">`**: Bu kısım responsiveness'ın anahtarıdır.
    *   `col-12`: En küçük ekranlarda (telefonlar, varsayılan) her eşya kartı tüm satırı kaplar (12 sütun).
    *   `col-sm-6`: Küçük ekranlarda (tabletler gibi, ≥576px) her eşya kartı satırın yarısını kaplar (6 sütun), yani yan yana 2 kart görünür.
    *   `col-md-4`: Orta boy ekranlarda (küçük dizüstü bilgisayarlar, ≥768px) her eşya kartı satırın üçte birini kaplar (4 sütun), yani yan yana 3 kart görünür.
    *   `col-lg-3`: Geniş ekranlarda (masaüstü bilgisayarlar, ≥992px) her eşya kartı satırın dörtte birini kaplar (3 sütun), yani yan yana 4 kart görünür.
*   **`<div class="card item-card ...">`**: Her bir eşyayı görsel olarak düzenleyen Bootstrap kart bileşeni. `item-card` özel CSS'imiz için.
    *   `card-img-top`, `card-body`, `card-title`, `card-text`, `btn` gibi Bootstrap sınıfları, kartın içindeki içeriğin stilini düzenler.
*   **`border-danger` ve `border-success`**: Kayıp/bulunan durumuna göre kartın kenarlık rengini değiştiren Bootstrap sınıflarıdır.

Bu yapıyla, tarayıcı penceresinin boyutunu değiştirdiğinizde veya farklı cihazlarda görüntülediğinizde, eşya kartları otomatik olarak farklı sütun düzenlerine uyum sağlayacaktır. Bu, kullanıcılarınız için tutarlı ve erişilebilir bir deneyim sunar.

#### 11. "Giriş yaptıktan veya eşya ekledikten sonra sayfa yenilenmeden başarı/hata mesajları nasıl gösterilir?"

Sayfa yenilenmeden mesaj göstermek, modern web uygulamalarında kullanıcı deneyimini önemli ölçüde iyileştiren bir özelliktir. Bu genellikle **AJAX (Asynchronous JavaScript and XML)** kullanarak başarılır. AJAX, arka planda sunucuya istek göndermenizi, yanıtı almanızı ve sayfanın belirli bir bölümünü güncellemenizi sağlar, böylece tüm sayfanın yeniden yüklenmesine gerek kalmaz.

**İş Akışı:**

1.  **Form Gönderimi (JavaScript):** Kullanıcı bir formu (giriş veya eşya ekleme) gönderdiğinde, standart HTML form gönderimini engellersiniz. Bunun yerine, JavaScript (örneğin `fetch` API veya `XMLHttpRequest` kullanarak) form verilerini toplar ve sunucuya bir AJAX isteği gönderir.
2.  **PHP İşleme ve JSON Yanıtı:** PHP betiğiniz (örneğin `login.php` veya `create_item.php`), normalde yaptığı gibi verileri işler (doğrulama, veritabanı kaydı vb.). İşlem başarılı olsun veya olmasın, bir **JSON (JavaScript Object Notation)** yanıtı oluşturur. Bu yanıt genellikle bir durum (`status: 'success'` veya `'error'`) ve bir mesaj (`message: 'Giriş başarılı!'`) içerir.
3.  **JavaScript Yanıtı İşleme:** JavaScript, sunucudan gelen JSON yanıtını alır. Yanıtın içeriğine göre, HTML belgesindeki belirli bir DOM (Document Object Model) öğesini (örneğin bir `<div>` veya `<p>` etiketi) başarı veya hata mesajıyla günceller.
4.  **İsteğe Bağlı Ek Eylemler:**
    *   Başarılı girişten sonra, kullanıcıyı başka bir sayfaya yönlendirebilir (`window.location.href = 'dashboard.php';`).
    *   Eşya eklemeden sonra formu sıfırlayabilir veya eklenen eşyayı listeye ekleyebilir.

**Örnek: Eşya Ekleme Formu ile AJAX Mesajı**

**HTML Form (create_item.php):**

```html
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Eşya Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1>Yeni Kayıp/Bulunan Eşya Ekle</h1>

    <!-- Mesajları göstermek için bir div -->
    <div id="messageDisplay" class="mt-3"></div>

    <form id="itemForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Başlık:</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Açıklama:</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="item_image" class="form-label">Resim:</label>
            <input type="file" class="form-control" id="item_image" name="item_image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Eşya Ekle</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('itemForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Formun standart gönderimini engelle

    const form = e.target;
    const formData = new FormData(form); // Form verilerini al (dosya yüklemeleri için FormData gerekli)
    const messageDisplay = document.getElementById('messageDisplay');

    // Bootstrap alert sınıflarını temizle
    messageDisplay.className = 'mt-3';
    messageDisplay.innerHTML = ''; // Önceki mesajı temizle

    // Sunucuya AJAX isteği gönder
    fetch('process_item.php', { // Verileri işleyecek PHP dosyası
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Yanıtı JSON olarak ayrıştır
    .then(data => {
        if (data.status === 'success') {
            messageDisplay.classList.add('alert', 'alert-success');
            messageDisplay.textContent = data.message;
            form.reset(); // Formu sıfırla
            // Eklenen eşyayı listeye dinamik olarak ekleyebilirsin
        } else {
            messageDisplay.classList.add('alert', 'alert-danger');
            messageDisplay.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        messageDisplay.classList.add('alert', 'alert-danger');
        messageDisplay.textContent = 'Bir ağ hatası oluştu. Lütfen tekrar deneyin.';
    });
});
</script>

</body>
</html>
```

**PHP İşleme Dosyası (process_item.php):**

```php
<?php
// Bu dosya AJAX isteği tarafından çağrılır ve sadece JSON yanıtı döndürmelidir.
header('Content-Type: application/json'); // Yanıtın JSON olduğunu belirt

$response = ['status' => 'error', 'message' => 'Bir hata oluştu.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    // Resim yükleme ve diğer doğrulamaları burada yapın (bkz. Güvenli Dosya Yükleme sorusu)

    // Örnek doğrulama
    if (empty($title) || empty($description)) {
        $response['message'] = 'Başlık ve açıklama boş bırakılamaz.';
    } else {
        // Burada veritabanına kayıt işlemini yapın
        // Örneğin:
        // try {
        //     $stmt = $pdo->prepare("INSERT INTO lost_items (title, description, user_id) VALUES (:title, :description, :user_id)");
        //     $stmt->bindParam(':title', $title);
        //     $stmt->bindParam(':description', $description);
        //     $stmt->bindParam(':user_id', $_SESSION['user_id']); // Kullanıcı oturumu varsa
        //     $stmt->execute();
        //     $response['status'] = 'success';
        //     $response['message'] = 'Eşya başarıyla eklendi!';
        // } catch (PDOException $e) {
        //     $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
        // }

        // Basit bir başarılı senaryo (gerçek veritabanı işlemi olmadan)
        $response['status'] = 'success';
        $response['message'] = 'Eşya başarıyla eklendi (örnek)!';
        // Gerçekte burada DB ID'si, resim yolu gibi ek bilgiler de dönebilirsiniz.
    }
} else {
    $response['message'] = 'Geçersiz istek metodu.';
}

echo json_encode($response);
exit; // Sadece JSON yanıtı döndürülmeli, başka çıktı olmamalı
?>
```

**Açıklama:**

*   **HTML:**
    *   Forma bir `id` (`itemForm`) verdik.
    *   Mesajları göstermek için boş bir `div` (`id="messageDisplay"`) ekledik.
    *   `<form enctype="multipart/form-data">` özelliği, dosya yüklemeyi desteklemek için önemlidir.
*   **JavaScript:**
    *   `addEventListener('submit', ...)`: Form gönderildiğinde tetiklenir.
    *   `e.preventDefault()`: Tarayıcının varsayılan form gönderim davranışını (sayfa yenileme) durdurur.
    *   `new FormData(form)`: Formdaki tüm giriş alanlarının ve dosya yüklemelerinin verilerini otomatik olarak toplar. Bu, AJAX ile dosya yüklemek için gereklidir.
    *   `fetch('process_item.php', {...})`: Sunucuya `POST` isteği gönderir.
    *   `.then(response => response.json())`: Sunucudan gelen yanıtı JSON formatında ayrıştırmasını söyler.
    *   `.then(data => {...})`: Ayrıştırılmış JSON verilerini (`data`) alır ve işler. `data.status` ve `data.message` değerlerine göre mesaj div'ini günceller ve Bootstrap uyarı sınıflarını (`alert-success`, `alert-danger`) ekler.
    *   `.catch(error => {...})`: Ağ hataları veya JSON ayrıştırma hataları gibi durumlarda hatayı yakalar.
*   **PHP (`process_item.php`):**
    *   `header('Content-Type: application/json');`: Tarayıcıya bu yanıtın bir JSON nesnesi olduğunu bildirir.
    *   `$response = ['status' => 'error', 'message' => '...'];`: JSON yanıtının yapısı.
    *   `json_encode($response);`: PHP dizisini veya nesnesini bir JSON dizesine dönüştürür ve tarayıcıya gönderir.
    *   `exit;`: PHP betiğinin burada durmasını ve başka hiçbir HTML veya metin çıktısı vermemesini sağlar, bu AJAX yanıtları için kritik öneme sahiptir.

Bu yöntemle, kullanıcınız form doldurup gönderdiğinde, sayfa yenilenmeden doğrudan formun altında veya üstünde başarı/hata mesajını görecektir, bu da çok daha akıcı bir kullanıcı deneyimi sunar. Giriş formu için de benzer bir mantık uygulanabilir; başarılı girişte yönlendirme yapılırken, başarısız girişte mesaj gösterilir.

#### 12. "item-detail.php sayfasındaki eşya bilgilerini göstermek için basit bir modal pencere nasıl eklenir?"

Modal pencereler (pop-up'lar veya diyalog kutuları), kullanıcının mevcut sayfadan ayrılmadan ek bilgi görüntülemesini veya etkileşimde bulunmasını sağlayan bir UI öğesidir. Bootstrap, modal pencereleri kolayca oluşturmanıza ve yönetmenize olanak tanır.

`item-detail.php` sayfasında, kullanıcının bir eşyaya tıkladığında detayları bir modal içinde görmesini sağlamak için genellikle AJAX ve JavaScript kullanılır.

**İş Akışı:**

1.  **HTML Listesi:** Eşyaların listelendiği sayfada (örneğin `index.php` veya `dashboard.php`), her eşya için "Detayları Gör" butonu veya bağlantısı bulunur. Bu bağlantılar, tıklandığında modalı tetikleyecek ve hangi eşyanın detaylarının gösterileceğini belirten bir `data-id` veya benzeri bir öznitelik içerecektir.
2.  **Boş Modal Yapısı:** Sayfada (genellikle HTML'in en altında), içeriği dinamik olarak doldurulacak bir Bootstrap modalının temel HTML yapısı bulunur.
3.  **JavaScript Dinleyici:** "Detayları Gör" butonlarına tıklandığında tetiklenecek bir JavaScript olayı dinleyicisi eklenir. Bu dinleyici, tıklanan butonun `data-id`'sini alır.
4.  **AJAX İsteği:** JavaScript, alınan eşya ID'si ile sunucuya (örneğin `get_item_details.php` adlı yeni bir PHP dosyasına) bir AJAX isteği gönderir.
5.  **PHP Detayları Sağlama:** `get_item_details.php` dosyası, verilen eşya ID'si ile veritabanından eşyanın tüm detaylarını çeker ve bu detayları bir JSON yanıtı olarak geri gönderir.
6.  **Modalı Doldurma ve Gösterme:** JavaScript, sunucudan gelen JSON yanıtını alır. Modalın başlık, gövde ve altbilgi gibi ilgili kısımlarını bu verilerle doldurur ve ardından Bootstrap'ın JavaScript API'sini kullanarak modalı gösterir.

**Örnek Uygulama:**

**1. `index.php` (Eşya Listesi ve Modal Yapısı):**

```html
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıp ve Bulunan Eşyalar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Tüm Eşyalar</h1>

    <div class="row">
        <?php
        // Burası veritabanından çekilen eşyaları temsil eden bir örnek döngü
        // Gerçek uygulamada PDO/MySQLi ile veritabanından veri çekeceksiniz.
        // Eşya ID'lerinin gerçek DB ID'leri olduğunu varsayın.
        $items = [
            ['id' => 1, 'title' => 'Kırmızı Cüzdan', 'description' => 'Küçük, deri, kırmızı cüzdan.', 'image_url' => 'https://via.placeholder.com/150x100?text=Cüzdan'],
            ['id' => 2, 'title' => 'iPhone 13', 'description' => 'Siyah renkli, kırık ekranlı iPhone 13.', 'image_url' => 'https://via.placeholder.com/150x100?text=Telefon'],
            ['id' => 3, 'title' => 'Mavi Şemsiye', 'description' => 'Katlanabilir, otomatik, mavi şemsiye.', 'image_url' => 'https://via.placeholder.com/150x100?text=Şemsiye'],
        ];

        foreach ($items as $item) {
        ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</p>
                        <!-- Detay butonu: data-item-id ile eşya ID'sini taşır -->
                        <button type="button" class="btn btn-primary view-details-btn" data-item-id="<?php echo $item['id']; ?>">
                            Detayları Gör
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Bootstrap Modal Yapısı (sayfanın en altında olabilir) -->
<div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemDetailModalLabel">Eşya Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <!-- Eşya detayları buraya dinamik olarak yüklenecek -->
                <p>Yükleniyor...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Popper.js ile birlikte) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailButtons = document.querySelectorAll('.view-details-btn');
    const itemDetailModal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
    const modalBodyContent = document.getElementById('modalBodyContent');
    const modalTitle = document.getElementById('itemDetailModalLabel');

    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId; // Butonun data-item-id değerini al

            // Modalı açmadan önce içeriği sıfırla veya yükleniyor mesajı göster
            modalTitle.textContent = 'Eşya Detayları Yükleniyor...';
            modalBodyContent.innerHTML = '<p>Detaylar yükleniyor...</p>';
            itemDetailModal.show(); // Modalı göster

            // AJAX ile eşya detaylarını çek
            fetch(`get_item_details.php?id=${itemId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Modalı gelen verilerle doldur
                        modalTitle.textContent = data.item.title;
                        modalBodyContent.innerHTML = `
                            <img src="${data.item.image_url}" class="img-fluid mb-3" alt="${data.item.title}">
                            <p><strong>Açıklama:</strong> ${data.item.description}</p>
                            <p><strong>Kategori:</strong> ${data.item.category}</p>
                            <p><strong>Konum:</strong> ${data.item.location}</p>
                            <p><strong>Durum:</strong> ${data.item.item_status === 'lost' ? 'Kayıp' : 'Bulundu'}</p>
                            <p><strong>Bildirilme Tarihi:</strong> ${data.item.reported_date}</p>
                            <p><strong>İletişim:</strong> ${data.item.contact_info}</p>
                        `;
                    } else {
                        modalTitle.textContent = 'Hata';
                        modalBodyContent.innerHTML = `<p class="text-danger">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    modalTitle.textContent = 'Hata';
                    modalBodyContent.innerHTML = `<p class="text-danger">Detaylar yüklenemedi: ${error.message}</p>`;
                });
        });
    });
});
</script>
</body>
</html>
```

**2. `get_item_details.php` (Eşya Detaylarını JSON Olarak Döndüren PHP Dosyası):**

```php
<?php
// Bu dosya AJAX isteği tarafından çağrılır ve sadece JSON yanıtı döndürmelidir.
header('Content-Type: application/json');

// Veritabanı bağlantısı
$dsn = 'mysql:host=localhost;dbname=veritabani_adi;charset=utf8mb4';
$username = 'kullanici';
$password = 'sifre';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı bağlantı hatası.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Geçersiz istek.'];

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    $sql = "SELECT title, description, category, location, item_status, reported_date, image_url, contact_info
            FROM lost_items
            WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $response = ['status' => 'success', 'item' => $item];
        } else {
            $response = ['status' => 'error', 'message' => 'Eşya bulunamadı.'];
        }
    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Sorgu hatası: ' . $e->getMessage()];
    }
}

echo json_encode($response);
exit;
?>
```

**Açıklama:**

*   **HTML (index.php):**
    *   Her "Detayları Gör" butonu, ilgili eşyanın ID'sini `data-item-id` özniteliği aracılığıyla taşır. Bu, JavaScript'in hangi eşyanın detaylarının istendiğini bilmesini sağlar.
    *   Boş modal yapısı, `id="itemDetailModal"` ile tanımlanır. İçindeki `id="modalBodyContent"` ve `id="itemDetailModalLabel"` alanları dinamik olarak güncellenecektir. `modal-dialog-centered` modalları ortalar.
*   **JavaScript (index.php):**
    *   Tüm "Detayları Gör" butonlarını seçer ve her birine `click` olay dinleyicisi ekler.
    *   Tıklanan butonun `data-item-id`'sini alır.
    *   Modalı manuel olarak açmak için `new bootstrap.Modal(...)` kullanılır ve `itemDetailModal.show()` ile gösterilir.
    *   `fetch` API kullanarak `get_item_details.php`'ye AJAX isteği gönderir ve eşya ID'sini URL parametresi olarak gönderir.
    *   PHP'den gelen JSON yanıtını alır (`response.json()`).
    *   Yanıtı işler: Eğer `status: 'success'` ise, modalın başlığını ve gövdesini gelen eşya verileriyle doldurur. Hata durumunda ise bir hata mesajı gösterir. `img-fluid` gibi Bootstrap sınıfları resmin modal içinde responsive olmasını sağlar.
*   **PHP (get_item_details.php):**
    *   URL'den `id` parametresini alır.
    *   Bu ID'ye sahip eşyanın bilgilerini veritabanından çeker (güvenliğiniz için hazırlanmış ifadeler kullanıldığından emin olun).
    *   Eşya bulunursa, `status: 'success'` ve `item` verileri içeren bir JSON yanıtı döndürür. Aksi takdirde bir hata mesajı döndürür.
    *   `exit;` ile başka hiçbir çıktının gönderilmemesini sağlar.

Bu yapı, kullanıcıların bir eşyanın detaylarını görmek için yeni bir sayfaya yönlendirilmek yerine hızlıca bir pop-up pencerede görmelerini sağlar, bu da kullanıcı deneyimini önemli ölçüde iyileştirir.

#### 13. "index.php sayfasına tarih, kategori veya konuma göre sıralama ve filtreleme nasıl eklenir?"

Kayıp/bulunan eşyalar listesini kullanıcıların tercih ettiği kriterlere göre sıralama ve filtreleme yeteneği, kullanıcı deneyimini zenginleştirir. Bu özellik, genellikle URL parametreleri (`$_GET`) ve dinamik olarak oluşturulan SQL sorguları kullanılarak gerçekleştirilir.

**İş Akışı:**

1.  **HTML Form (Filtreleme ve Sıralama Kontrolleri):**
    *   `index.php` üzerinde, kullanıcının filtreleme ve sıralama tercihlerini seçebileceği bir form oluşturulur. Bu form genellikle `GET` metoduyla gönderilir, böylece filtreler URL'de görünür ve paylaşılabilir olur.
    *   Dropdown menüler (`<select>`) kategoriler veya sıralama seçenekleri için idealdir.
    *   Metin giriş alanları (`<input type="date">`, `<input type="text">`) konum veya tarih aralığı için kullanılabilir.
2.  **PHP Dinamik Sorgu Oluşturma:**
    *   `index.php` dosyası, gelen `$_GET` parametrelerini okur.
    *   Bu parametrelere göre bir SQL sorgusu dinamik olarak oluşturulur. Sorguya `WHERE` koşulları (filtreleme için) ve `ORDER BY` ifadesi (sıralama için) eklenir.
    *   **SQL Enjeksiyonuna Karşı Koruma:** Kullanıcıdan gelen her parametre, hazırlanmış ifadeler (prepared statements) kullanılarak sorguya güvenli bir şekilde dahil edilmelidir.

**Örnek Uygulama:**

**1. `index.php` (Filtreleme Formu ve Dinamik Listeleme):**

```php
<?php
// Veritabanı bağlantısı (PDO)
$dsn = 'mysql:host=localhost;dbname=veritabani_adi;charset=utf8mb4';
$username = 'kullanici';
$password = 'sifre';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Filtreleme ve Sıralama Parametrelerini Al
$filterCategory = $_GET['category'] ?? ''; // Kategori filtresi
$filterLocation = $_GET['location'] ?? ''; // Konum filtresi
$filterStatus = $_GET['status'] ?? ''; // Durum filtresi (kayıp/bulundu)
$sortBy = $_GET['sort_by'] ?? 'reported_date_desc'; // Varsayılan sıralama

// Temel SQL sorgusu
$sql = "SELECT id, title, description, category, location, item_status, reported_date, image_url
        FROM lost_items
        WHERE 1=1"; // Her zaman doğru olan bir başlangıç koşulu

$params = []; // Hazırlanmış ifadeler için parametreler

// Kategori filtresi ekle
if (!empty($filterCategory)) {
    $sql .= " AND category = :category";
    $params[':category'] = $filterCategory;
}

// Konum filtresi ekle (LIKE ile kısmi eşleşme)
if (!empty($filterLocation)) {
    $sql .= " AND location LIKE :location";
    $params[':location'] = '%' . $filterLocation . '%';
}

// Durum filtresi ekle
if (!empty($filterStatus) && ($filterStatus == 'lost' || $filterStatus == 'found')) {
    $sql .= " AND item_status = :status";
    $params[':status'] = $filterStatus;
}

// Sıralama koşulu ekle
switch ($sortBy) {
    case 'reported_date_asc':
        $sql .= " ORDER BY reported_date ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'category_asc':
        $sql .= " ORDER BY category ASC";
        break;
    default: // reported_date_desc
        $sql .= " ORDER BY reported_date DESC";
        break;
}

$items = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Eşyalar yüklenirken bir hata oluştu: " . $e->getMessage() . "</div>";
}

// Kategorileri veritabanından çekmek daha iyi bir pratik olabilir.
$allCategories = ['Elektronik', 'Giysi', 'Kitap', 'Anahtar', 'Cüzdan', 'Diğer'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıp ve Bulunan Eşyalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .item-card {
            margin-bottom: 20px;
            height: 100%;
        }
        .item-card img {
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Kayıp ve Bulunan Eşyalar</h1>

    <!-- Filtreleme ve Sıralama Formu -->
    <form class="row mb-4 g-3 align-items-end" method="get" action="">
        <div class="col-md-3">
            <label for="category" class="form-label">Kategori:</label>
            <select class="form-select" id="category" name="category">
                <option value="">Tüm Kategoriler</option>
                <?php foreach ($allCategories as $cat) { ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($filterCategory == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="location" class="form-label">Konumda Ara:</label>
            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($filterLocation); ?>" placeholder="Konum girin">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Durum:</label>
            <select class="form-select" id="status" name="status">
                <option value="">Tümü</option>
                <option value="lost" <?php echo ($filterStatus == 'lost') ? 'selected' : ''; ?>>Kayıp</option>
                <option value="found" <?php echo ($filterStatus == 'found') ? 'selected' : ''; ?>>Bulundu</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort_by" class="form-label">Sırala:</label>
            <select class="form-select" id="sort_by" name="sort_by">
                <option value="reported_date_desc" <?php echo ($sortBy == 'reported_date_desc') ? 'selected' : ''; ?>>Tarih (Yeniye Göre)</option>
                <option value="reported_date_asc" <?php echo ($sortBy == 'reported_date_asc') ? 'selected' : ''; ?>>Tarih (Eskiye Göre)</option>
                <option value="title_asc" <?php echo ($sortBy == 'title_asc') ? 'selected' : ''; ?>>Başlık (A-Z)</option>
                <option value="title_desc" <?php echo ($sortBy == 'title_desc') ? 'selected' : ''; ?>>Başlık (Z-A)</option>
                <option value="category_asc" <?php echo ($sortBy == 'category_asc') ? 'selected' : ''; ?>>Kategori</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrele & Sırala</button>
        </div>
    </form>

    <div class="row">
        <?php if (count($items) > 0) {
            foreach ($items as $item) { ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card item-card <?php echo ($item['item_status'] == 'lost') ? 'border-danger' : 'border-success'; ?>">
                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/300x200?text=Resim+Yok'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted">Durum: <?php echo ($item['item_status'] == 'lost') ? 'Kayıp' : 'Bulundu'; ?></p>
                            <p class="card-text"><small class="text-muted">Kategori: <?php echo htmlspecialchars($item['category']); ?></small></p>
                            <p class="card-text"><small class="text-muted">Konum: <?php echo htmlspecialchars($item['location']); ?></small></p>
                            <p class="card-text"><small class="text-muted">Bildirildi: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($item['reported_date']))); ?></small></p>
                            <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm mt-2">Detayları Gör</a>
                        </div>
                    </div>
                </div>
            <?php }
        } else { ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Belirtilen kriterlere uygun eşya bulunamadı.
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

**Açıklama:**

*   **HTML Formu:**
    *   `method="get"`: Form gönderildiğinde seçilen filtre ve sıralama seçenekleri URL'ye eklenir (örneğin `index.php?category=Elektronik&sort_by=title_asc`). Bu, kullanıcıların filtreli listeleri favorilerine eklemesine veya paylaşmasına olanak tanır.
    *   `<select>` elementlerinin `value` öznitelikleri, PHP'de kontrol edilecek değerlerdir.
    *   `selected` özniteliği, PHP tarafından önceki seçimin korunmasını sağlar, böylece kullanıcı filtreleme yaptıktan sonra sayfadaki seçimleri hatırlanır.
*   **PHP Dinamik Sorgu Oluşturma:**
    *   `$_GET` ile kullanıcı tarafından seçilen filtre ve sıralama değerleri alınır.
    *   `$sql = "SELECT ... WHERE 1=1";`: `WHERE 1=1` ifadesi, SQL sorgusuna daha sonra kolayca `AND` koşulları eklememizi sağlayan bir hiledir, çünkü `WHERE` kelimesini her zaman ilk koşuldan önce kullanmamız gerekmez.
    *   `if (!empty($filterCategory)) { ... }`: Her filtre parametresi için, eğer boş değilse, `AND` ile sorguya ilgili `WHERE` koşulu eklenir.
    *   `$params[]`: Hazırlanmış ifadeler için parametreleri bir dizide toplamak, sorguyu esnek ve güvenli hale getirir. `LIKE` operatörü kullanılırken joker karakterler (`%`) parametre değerine dahil edilir, ancak parametre bağlama işlemi SQL enjeksiyonunu engeller.
    *   `switch ($sortBy) { ... }`: Sıralama seçimine göre `ORDER BY` ifadesi dinamik olarak belirlenir.
    *   `$pdo->prepare($sql); $stmt->execute($params);`: Güvenli veri çekimi için hazırlanmış ifadeler kullanılır.

Bu yapı, kullanıcılarınıza eşya listesini kendi ihtiyaçlarına göre özelleştirme gücü verir, bu da web sitenizin kullanışlılığını ve kullanıcı memnuniyetini artırır.

---

### 🔐 Güvenlik İyileştirmeleri

#### 14. "PHP'de oturum süresi dolma veya otomatik çıkış (auto-logout) nasıl uygulanır?"

Oturum süresi dolma (session timeout) ve otomatik çıkış (auto-logout), web uygulamalarının güvenliği için kritik öneme sahiptir. Bir kullanıcı web sitesinde uzun süre etkileşimde bulunmazsa, oturumunun otomatik olarak kapatılması, yetkisiz erişimi veya oturum kaçırma saldırılarını önlemeye yardımcı olur.

PHP'de oturum süresi dolmasını yönetmenin iki ana yolu vardır:

1.  **PHP Yapılandırması (php.ini):**
    *   `session.gc_maxlifetime`: Bu ayar, oturum verilerinin sunucuda ne kadar süreyle saklanacağını saniye cinsinden belirler. Çöp toplama (garbage collection) süreci, bu süreyi aşan oturum dosyalarını siler.
    *   `session.cookie_lifetime`: Bu ayar, oturum kimliği çerezinin tarayıcıda ne kadar süreyle kalacağını saniye cinsinden belirler. Varsayılan 0, tarayıcı kapatıldığında çerezin silineceği anlamına gelir.
    *   **Dezavantajları:** Bu ayarlar sunucu genelidir ve tüm uygulamaları etkiler. Ayrıca, `session.gc_maxlifetime` çöp toplamanın hemen çalışacağını garanti etmez; sunucunun rastgele aralıklarla çalıştırdığı bir işlemdir.

2.  **Uygulama Seviyesinde Özel Oturum Yönetimi (Önerilen ve En Esnek Yöntem):**
    Bu yöntem, her kullanıcı isteğinde oturum süresini kontrol ederek daha güvenilir ve anında bir otomatik çıkış sağlar.

**Uygulama Adımları:**

1.  **`session_start()`:** Her PHP sayfasının başında `session_start()` çağrısı yapılmalıdır.
2.  **`$_SESSION['last_activity']` Kaydetme:** Kullanıcı her sayfa yüklediğinde veya önemli bir işlem yaptığında (örneğin form gönderme), `$_SESSION['last_activity']` adlı bir oturum değişkenine o anki zaman damgasını (timestamp) kaydedersiniz.
3.  **Zaman Damgası Kontrolü:** Her sayfa yüklemesinde, mevcut zaman ile `$_SESSION['last_activity']` arasındaki farkı kontrol edersiniz. Eğer bu fark belirli bir süreyi (örneğin 30 dakika) aşarsa, kullanıcı oturumunu sonlandırırsınız (otomatik çıkış).
4.  **Oturumu Yok Etme:** Oturum süresi dolduğunda veya kullanıcı çıkış yaptığında, `session_unset()`, `session_destroy()` ve oturum çerezini silmek (isteğe bağlı ama iyi bir pratik) ile oturumu tamamen sonlandırırsınız.

**Örnek Uygulama (`check_session.php` veya bir başlangıç dosyası):**

Genellikle bu kontrolü, uygulamanızın her sayfasında `include` edeceğiniz merkezi bir dosyada yaparsınız.

```php
<?php
// Bu kodu her kullanıcı sayfası girişinde (login.php hariç) include etmelisiniz.
// Örneğin:
// include_once 'config.php'; // Veritabanı bağlantısı vb.
// include_once 'session_manager.php'; // Bu dosya

session_start(); // Oturumu başlat

// Oturum zaman aşımı süresi (saniye cinsinden, örneğin 30 dakika = 1800 saniye)
$session_timeout = 1800; // 30 dakika

// 1. Kullanıcı giriş yapmış mı kontrol et
if (isset($_SESSION['user_id'])) { // Kullanıcı ID'si oturumda mevcutsa giriş yapılmıştır
    // 2. Son aktivite zaman damgasını kontrol et
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
        // Oturum süresi doldu, oturumu sonlandır
        session_unset();     // Tüm oturum değişkenlerini kaldır
        session_destroy();   // Oturumu yok et
        setcookie(session_name(), '', time() - 3600, '/'); // Oturum çerezini sil (isteğe bağlı)

        // Kullanıcıyı giriş sayfasına yönlendir ve bir mesaj göster
        header("Location: login.php?message=session_expired");
        exit();
    }

    // 3. Her geçerli istekte son aktivite zamanını güncelle
    $_SESSION['last_activity'] = time();

} else {
    // Kullanıcı giriş yapmamışsa ve protected bir sayfadaysa yönlendir
    // Örneğin, dashboard.php sayfasında bu kontrol varsa:
    $current_page = basename($_SERVER['PHP_SELF']);
    $public_pages = ['login.php', 'register.php', 'index.php']; // Genişletilebilir

    if (!in_array($current_page, $public_pages)) {
        header("Location: login.php?message=not_logged_in");
        exit();
    }
}

// Çıkış (Logout) İşlemi:
// logout.php gibi bir sayfada:
/*
<?php
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/'); // Oturum çerezini sil
header("Location: login.php?message=logged_out");
exit();
?>
*/
?>
```

**Bu Yaklaşımın Avantajları:**

*   **Kesin Kontrol:** Tam olarak ne zaman oturumun kapanacağını siz belirlersiniz.
*   **Anında Etki:** Süre dolduğunda hemen devreye girer, PHP'nin çöp toplama mekanizmasının çalışmasını beklemez.
*   **Kullanıcı Bazında Esneklik:** İsterseniz farklı kullanıcı rolleri için farklı oturum süreleri bile belirleyebilirsiniz (örneğin, yöneticiler için daha kısa bir süre).
*   **Güvenlik:** Kullanıcı aktif değilse, oturum kimliğinin kötü niyetli kişilerin eline geçme süresini sınırlar.

**Ek İpuçları:**

*   **HTTPS Kullanımı:** Oturum kimliklerinin ağ üzerinden güvenli bir şekilde iletilmesini sağlamak için her zaman HTTPS kullanın.
*   **`session.use_strict_mode = 1`:** `php.ini`'de bu ayarı etkinleştirerek bilinmeyen oturum kimliklerinin kabul edilmesini engelleyin.
*   **`session.cookie_httponly = 1`:** Bu, JavaScript'in oturum çerezine erişmesini engeller ve XSS (Cross-Site Scripting) saldırılarıyla oturum kaçırma riskini azaltır.
*   **`session.cookie_secure = 1`:** Sadece HTTPS bağlantıları üzerinden çerezin gönderilmesini sağlar.

Bu yöntemlerle, uygulamanızdaki oturum güvenliğini önemli ölçüde artırabilirsiniz.

#### 15. "create-item.php dosyasında yüklenen dosyaların türünü ve boyutunu nasıl doğrularım?"

Dosya yüklemelerinde tür ve boyut doğrulaması, web güvenliği ve uygulama kararlılığı için hayati öneme sahiptir. Bu doğrulamalar hem kötü niyetli dosyaların yüklenmesini engeller hem de sunucu kaynaklarının aşırı kullanımını önler. Önceki "Dosya yüklemeyi güvenli hale getirme" sorusunda da bahsedildiği gibi, bu kontroller kritik adımlardır.

**1. Dosya Türü Doğrulama (En Kritik Kısım):**

Bir dosyanın türünü doğrularken, sadece dosya uzantısına güvenmek yeterli değildir. Kullanıcılar kolayca bir PHP dosyasının uzantısını `.jpg` olarak değiştirebilirler. Bu nedenle hem uzantıyı hem de dosyanın gerçek MIME tipini (içeriğini) kontrol etmelisiniz.

*   **a. Dosya Uzantısı Kontrolü:**
    *   Kullanıcının yüklediği dosyanın uzantısını alıp, sadece izin verilen uzantılar listesinde olup olmadığını kontrol edin.
    *   `pathinfo()` ve `strtolower()` fonksiyonları bu iş için kullanışlıdır.
    ```php
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileInfo = pathinfo($_FILES['item_image']['name']);
    $fileExtension = strtolower($fileInfo['extension']);

    if (!in_array($fileExtension, $allowedExtensions)) {
        // Hata: Geçersiz dosya uzantısı
        echo "Hata: Yalnızca JPG, JPEG, PNG, GIF resimleri yükleyebilirsiniz.";
        exit;
    }
    ```

*   **b. Gerçek MIME Tipi Kontrolü (Sunucu Taraflı ve Daha Güvenli):**
    *   **Resimler İçin (`getimagesize()`):** Yüklenen dosya bir resimse, `getimagesize()` fonksiyonu dosyanın boyutlarını ve MIME tipini döndürür. Eğer dosya geçerli bir resim değilse `false` döner. Bu, bir PHP betiği `.jpg` uzantısıyla yüklenmiş olsa bile, betik olmadığını doğrular.
    ```php
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $tempFilePath = $_FILES['item_image']['tmp_name']; // Geçici dosya yolu

    $imageInfo = getimagesize($tempFilePath);

    if ($imageInfo === false) {
        // Hata: Dosya geçerli bir resim değil (belki bozuk veya farklı bir dosya türü)
        echo "Hata: Yüklenen dosya geçerli bir resim formatında değil.";
        exit;
    }

    if (!in_array($imageInfo['mime'], $allowedMimeTypes)) {
        // Hata: MIME tipi izin verilenler listesinde değil
        echo "Hata: Resmin MIME tipi desteklenmiyor. Yalnızca JPG, JPEG, PNG, GIF kabul edilir.";
        exit;
    }
    ```
    *   **Genel Dosyalar İçin (`finfo_file()` - PHP Fileinfo Uzantısı):** Eğer resim dışındaki dosyaları (PDF, DOC vb.) yüklemeniz gerekiyorsa, `finfo_file()` fonksiyonu dosyanın içeriğine bakarak gerçek MIME tipini tespit eder. Bu uzantının PHP'de etkinleştirilmiş olması gerekir (`extension=fileinfo` in `php.ini`).
    ```php
    // $allowedMimeTypes'a 'application/pdf', 'application/msword' vb. eklenebilir
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tempFilePath);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        // Hata: MIME tipi izin verilenler listesinde değil
        echo "Hata: Yüklenen dosya tipi desteklenmiyor.";
        exit;
    }
    ```

**2. Dosya Boyutu Doğrulama:**

*   **PHP'nin Dahili Limitleri:** PHP'nin kendi `php.ini` ayarlarında dosya yükleme limitleri vardır:
    *   `upload_max_filesize`: Tek bir dosyanın maksimum boyutu.
    *   `post_max_size`: Bir POST isteğinin toplam maksimum boyutu (tüm form verisi ve dosyalar dahil).
    *   Bu limitler aşılırsa, `$_FILES['item_image']['error']` genellikle `UPLOAD_ERR_INI_SIZE` veya `UPLOAD_ERR_FORM_SIZE` değerini alır.
*   **Uygulama İçi Özel Limitler:** Kendi belirlediğiniz bir maksimum boyutu da uygulayabilirsiniz. Bu, `$_FILES['item_image']['size']` değerini kontrol ederek yapılır. Boyut bayt cinsinden gelir.
    ```php
    $maxFileSize = 5 * 1024 * 1024; // 5 MB (5 megabayt)

    if ($_FILES['item_image']['size'] > $maxFileSize) {
        // Hata: Dosya boyutu çok büyük
        echo "Hata: Dosya boyutu " . ($maxFileSize / (1024 * 1024)) . "MB'tan büyük olamaz.";
        exit;
    }
    ```

**Güvenli Dosya Yükleme Kontrolleri (Özet ve Örnek Kod):**

```php
<?php
// PHP'nin varsayılan upload hatalarını kontrol et
if ($_FILES['item_image']['error'] !== UPLOAD_ERR_OK) {
    switch ($_FILES['item_image']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            echo "Hata: Yüklenen dosya boyutu çok büyük (PHP limitini aştı).";
            break;
        case UPLOAD_ERR_PARTIAL:
            echo "Hata: Dosya kısmen yüklendi.";
            break;
        case UPLOAD_ERR_NO_FILE:
            echo "Hata: Hiç dosya yüklenmedi.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            echo "Hata: Geçici klasör eksik.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            echo "Hata: Diske yazılamadı.";
            break;
        case UPLOAD_ERR_EXTENSION:
            echo "Hata: Bir PHP uzantısı dosya yüklemesini durdurdu.";
            break;
        default:
            echo "Bilinmeyen dosya yükleme hatası.";
            break;
    }
    exit;
}

// 1. Uygulama içi maksimum dosya boyutu kontrolü
$maxFileSize = 5 * 1024 * 1024; // 5 MB
if ($_FILES['item_image']['size'] > $maxFileSize) {
    echo "Hata: Dosya boyutu " . ($maxFileSize / (1024 * 1024)) . "MB'tan büyük olamaz.";
    exit;
}

// 2. Dosya Uzantısı Kontrolü
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$fileInfo = pathinfo($_FILES['item_image']['name']);
$fileExtension = strtolower($fileInfo['extension']);

if (!in_array($fileExtension, $allowedExtensions)) {
    echo "Hata: Desteklenmeyen dosya uzantısı. Yalnızca JPG, JPEG, PNG, GIF kabul edilir.";
    exit;
}

// 3. Gerçek MIME Tipi Kontrolü (Resimler için)
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$tempFilePath = $_FILES['item_image']['tmp_name'];

$imageInfo = getimagesize($tempFilePath); // Resim değilse veya bozuksa false döner

if ($imageInfo === false || !in_array($imageInfo['mime'], $allowedMimeTypes)) {
    echo "Hata: Yüklenen dosya geçerli bir resim değil veya MIME tipi desteklenmiyor.";
    exit;
}

// Tüm kontroller başarılı, dosyayı güvenli bir yere taşıyabiliriz.
// Örneğin, benzersiz bir adla 'uploads/' dizinine:
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$uniqueFileName = uniqid('img_', true) . '.' . $fileExtension;
$destinationPath = $uploadDir . $uniqueFileName;

if (move_uploaded_file($tempFilePath, $destinationPath)) {
    echo "Dosya başarıyla yüklendi: " . htmlspecialchars($destinationPath);
    // Veritabanına dosya yolunu kaydet
} else {
    echo "Dosya taşıma hatası.";
}

?>
<form action="" method="post" enctype="multipart/form-data">
    <label for="item_image">Eşya Resmi:</label>
    <input type="file" id="item_image" name="item_image" accept="image/jpeg,image/png,image/gif" required><br>
    <button type="submit">Yükle</button>
</form>
```

Bu kapsamlı kontrolleri uygulayarak, uygulamanızın güvenliğini ve kararlılığını artırabilir ve kullanıcılarınızın yalnızca beklenen ve güvenli dosyaları yüklemesini sağlayabilirsiniz.

#### 16. "Yönetici (admin) sayfalarını oturum kontrolüyle nasıl koruyabilirim?"

Yönetici (admin) sayfalarını oturum kontrolüyle korumak, uygulamanızın güvenliği için temel bir adımdır. Sadece yetkili kullanıcıların (yani admin rolüne sahip kullanıcıların) bu sayfalara erişebildiğinden emin olmalısınız.

**Temel İş Akışı:**

1.  **Giriş Anında Rol Atama:** Kullanıcı giriş yaptığında, veritabanından kullanıcının rolünü (örneğin 'admin', 'user') veya yetki seviyesini çekersiniz. Bu rol bilgisini oturum değişkenine (`$_SESSION`) kaydedersiniz.
2.  **Oturum Kontrolü Fonksiyonu/Dosyası:** Korumak istediğiniz her admin sayfasının (veya bu sayfaların başında `include` ettiğiniz bir dosyanın) başında, kullanıcının oturum açmış olup olmadığını **VE** admin rolüne sahip olup olmadığını kontrol eden bir kod bloğu bulunur.
3.  **Yönlendirme:** Eğer kullanıcı oturum açmamışsa veya admin rolüne sahip değilse, onu giriş sayfasına veya yetkisiz erişim sayfasına yönlendirirsiniz.

**Adım Adım Uygulama:**

**1. Veritabanı (Kullanıcı Rolü):**

`users` tablonuza bir `role` sütunu ekleyin. Bu genellikle `ENUM` (örneğin `'admin', 'user'`) veya `VARCHAR` olabilir.

```sql
ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user';
-- Mevcut bir kullanıcıyı admin yapmak için:
-- UPDATE users SET role = 'admin' WHERE username = 'yonetici_kullaniciadi';
```

**2. Giriş İşlemi (`login.php`):**

Kullanıcı başarıyla giriş yaptığında, `password_verify` kontrolünden sonra, kullanıcının rolünü veritabanından çekip oturumda saklayın.

```php
<?php
session_start(); // Oturumu başlat
// ... veritabanı bağlantısı ($pdo) varsayalım ...

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $submittedUsername = $_POST['username'];
    $submittedPassword = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $submittedUsername);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($submittedPassword, $user['password'])) {
        // Giriş başarılı
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role']; // Kullanıcı rolünü oturuma kaydet!

        // Kullanıcının rolüne göre yönlendirme yapabilirsiniz
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php"); // Yöneticileri admin paneline yönlendir
        } else {
            header("Location: dashboard.php"); // Normal kullanıcıları kendi panellerine yönlendir
        }
        exit();
    } else {
        echo "Kullanıcı adı veya şifre yanlış.";
    }
}
?>
<!-- HTML login formu -->
```

**3. Yönetici Sayfaları İçin Kontrol Mekanizması (`admin_check.php`):**

Bu dosyayı, korumak istediğiniz her admin sayfasının en başına `include` edeceksiniz.

```php
<?php
// admin_check.php

session_start(); // Her zaman oturumu başlat

// Kullanıcı giriş yapmış mı ve admin rolüne sahip mi kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null ||
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    
    // Yönlendirilecek sayfa ve mesaj
    header("Location: login.php?message=unauthorized_access");
    exit(); // Yönlendirmeden sonra betiğin çalışmasını durdur
}

// Buraya gelindiyse, kullanıcı admin rolüne sahip ve oturum açmış demektir.
// Sayfa içeriği yüklenebilir.
?>
```

**4. Yönetici Sayfası (`admin_dashboard.php` veya başka bir admin sayfası):**

Korumak istediğiniz admin sayfalarının en üstüne `admin_check.php` dosyasını dahil edin.

```php
<?php
include_once 'admin_check.php'; // Yönetici kontrol dosyasını dahil et

// Bu noktadan sonra sadece adminler bu sayfaya erişebilir.
// Yönetici paneli içeriği burada yer alır.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Yönetici Paneline Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Burada kayıp/bulunan eşyaları yönetebilir, kullanıcıları denetleyebilir ve raporları görüntüleyebilirsiniz.</p>
        <a href="logout.php" class="btn btn-danger">Çıkış Yap</a>
    </div>
</body>
</html>
```

**Ek Güvenlik İpuçları:**

*   **HTTPS:** Her zaman SSL/TLS (HTTPS) kullanın. Bu, oturum kimliklerinin ve diğer hassas verilerin şifresiz gönderilmesini engeller.
*   **Oturum Sabitleri:** Oturum kimliğinin (SID) URL'de değil, çerezlerde gönderildiğinden emin olun (`session.use_trans_sid = 0` ve `session.use_cookies = 1` in `php.ini`).
*   **`httponly` ve `secure` Çerez Bayrakları:** Oturum çerezini daha güvenli hale getirin.
    *   `session.cookie_httponly = 1`: JavaScript'in çereze erişmesini engeller, XSS saldırılarına karşı korur.
    *   `session.cookie_secure = 1`: Çerezin sadece HTTPS üzerinden gönderilmesini sağlar.
*   **Oturum Yenileme:** Kullanıcı önemli bir yetki yükseltmesi yaptığında (örneğin admin girişi), `session_regenerate_id(true);` kullanarak yeni bir oturum kimliği oluşturmak, oturum sabitleme saldırılarına karşı korur.
*   **Sıfır Güven (Zero Trust) İlkesi:** Her zaman, her girişi ve her yetkiyi doğrulayın. Kullanıcının bir kez admin olarak işaretlenmesi, sonsuza dek admin olacağı anlamına gelmez. Yetki kontrolünü her istekte tekrarlayın.

Bu adımları izleyerek, yönetici sayfalarınızı güçlü bir oturum kontrolüyle koruyabilirsiniz.

---

### 🚀 Özellik Geliştirmeleri

#### 17. "Bir kayıp eşya bulunduğunda kullanıcıya e-posta bildirimi göndermek için sistem nasıl kurulur?"

Bir kayıp eşya bulunduğunda veya benzer bir eşya ilan edildiğinde ilgili kullanıcıya e-posta bildirimi göndermek, kullanıcı deneyimini büyük ölçüde artıran ve uygulamanızın değerini yükselten bir özelliktir. Bu sistemin kurulumu genellikle şu adımları içerir:

1.  **E-posta İçeriği ve Şablonlama:** Gönderilecek e-postanın yapısını ve içeriğini belirleyin (başlık, mesaj, ilgili eşyanın detayları, iletişim bilgileri vb.). HTML e-postalar daha çekicidir.
2.  **E-posta Gönderme Kütüphanesi Seçimi:** PHP'nin yerleşik `mail()` fonksiyonu basit durumlar için yeterli olabilir, ancak SMTP kimlik doğrulaması, HTML e-postalar, ekler ve hata yönetimi gibi gelişmiş özellikler için güvenilir bir kütüphane (örneğin **PHPMailer** veya **Symfony Mailer**) kullanmak şiddetle tavsiye edilir. Harici bir SMTP hizmeti (SendGrid, Mailgun, AWS SES, Gmail SMTP vb.) kullanmak, e-postalarınızın spam klasörüne düşmesini engeller ve gönderme limitlerinizi artırır.
3.  **Veritabanı Sorgusu (Eşleşen İlanları Bulma):** Bir "bulunan" eşya kaydedildiğinde, bu eşyayla eşleşen (örneğin aynı kategori, benzer açıklama veya konum) "kayıp" ilanlarını veritabanında ararsınız. Aynı şekilde, bir "kayıp" eşya kaydedildiğinde, daha önce bildirilmiş "bulunan" eşyalar arasında bir eşleşme arayabilirsiniz.
4.  **Kullanıcı Bilgilerini Çekme:** Eşleşen kayıp/bulunan ilanın sahibinin e-posta adresini ve diğer iletişim bilgilerini veritabanından çekersiniz.
5.  **E-posta Gönderme Mantığı:** E-posta gönderme işlemini tetikleyen PHP kodunu yazarsınız.

**Örnek Uygulama (PHPMailer ile):**

**Adım 1: PHPMailer'ı Kurun**

Composer kullanıyorsanız:
`composer require phpmailer/phpmailer`

Composer kullanmıyorsanız, PHPMailer'ın dosyalarını projenize dahil edin.

**Adım 2: E-posta Gönderme Fonksiyonu Oluşturun**

`email_sender.php` gibi bir dosya oluşturun:

```php
<?php
// email_sender.php
require 'vendor/autoload.php'; // Composer ile kurulduysa
// require 'path/to/PHPMailer/src/PHPMailer.php'; // Manual kurulum için
// require 'path/to/PHPMailer/src/SMTP.php';
// require 'path/to/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendNotificationEmail($recipientEmail, $recipientName, $itemTitle, $foundItemDetails, $contactInfo) {
    $mail = new PHPMailer(true); // Hatalar için true parametresi

    try {
        // SMTP Ayarları (Gmail SMTP örneği)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Veya başka bir SMTP sunucusu
        $mail->SMTPAuth   = true;
        $mail->Username   = 'senin_eposta@gmail.com'; // Gönderen e-posta adresiniz
        $mail->Password   = 'senin_app_sifren';     // Google App Şifresi (uygulama şifresi)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS için ENCRYPTION_STARTTLS
        $mail->Port       = 587; // TLS için 587, SSL için 465

        // Gönderen ve Alıcı Bilgileri
        $mail->setFrom('senin_eposta@gmail.com', 'Kayıp Eşya Bildirim Sistemi');
        $mail->addAddress($recipientEmail, $recipientName);

        // İçerik
        $mail->isHTML(true); // HTML formatında e-posta göndermek için ayarla
        $mail->CharSet = 'UTF-8'; // Türkçe karakterler için

        $mail->Subject = 'Kayıp Esyaniz Icin Bir Guncelleme: ' . $itemTitle;
        $mail->Body    = "
            <html>
            <head>
                <title>Kayıp Eşyanız Hakkında Bilgi</title>
            </head>
            <body>
                <p>Merhaba <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                <p>Bildirmiş olduğunuz <strong>'" . htmlspecialchars($itemTitle) . "'</strong> adlı eşyanızla ilgili bir gelişme olabilir.</p>
                <p>Bir kullanıcı, aşağıdaki bilgilere sahip bir eşya bulduğunu bildirdi:</p>
                <ul>
                    <li><strong>Bulunan Eşya Başlığı:</strong> " . htmlspecialchars($foundItemDetails['title']) . "</li>
                    <li><strong>Açıklama:</strong> " . nl2br(htmlspecialchars($foundItemDetails['description'])) . "</li>
                    <li><strong>Bulunan Konum:</strong> " . htmlspecialchars($foundItemDetails['location']) . "</li>
                    <li><strong>Bulunma Tarihi:</strong> " . htmlspecialchars($foundItemDetails['reported_date']) . "</li>
                </ul>
                <p>Bu eşyanın size ait olabileceğini düşünüyorsanız, bulan kişiyle aşağıdaki bilgiler aracılığıyla iletişime geçebilirsiniz:</p>
                <p><strong>İletişim Bilgisi:</strong> " . nl2br(htmlspecialchars($contactInfo)) . "</p>
                <p>Umarız eşyanıza kavuşursunuz.</p>
                <p>Saygılarımızla,</p>
                <p>Kayıp Eşya Bildirim Ekibi</p>
            </body>
            </html>
        ";
        $mail->AltBody = "Merhaba " . $recipientName . ",\n\nBildirmiş olduğunuz '" . $itemTitle . "' adlı eşyanızla ilgili bir gelişme olabilir.\nBir kullanıcı, aşağıdaki bilgilere sahip bir eşya bulduğunu bildirdi:\n\nBulunan Eşya Başlığı: " . $foundItemDetails['title'] . "\nAçıklama: " . $foundItemDetails['description'] . "\nBulunan Konum: " . $foundItemDetails['location'] . "\nBulunma Tarihi: " . $foundItemDetails['reported_date'] . "\n\nBu eşyanın size ait olabileceğini düşünüyorsanız, bulan kişiyle aşağıdaki bilgiler aracılığıyla iletişime geçebilirsiniz:\n\nİletişim Bilgisi: " . $contactInfo . "\n\nUmarız eşyanıza kavuşursunuz.\n\nSaygılarımızla,\nKayıp Eşya Bildirim Ekibi";


        $mail->send();
        return true; // E-posta başarıyla gönderildi
    } catch (Exception $e) {
        error_log("E-posta gönderim hatası: {$mail->ErrorInfo}"); // Hatayı logla
        return false; // E-posta gönderilemedi
    }
}
?>
```
**Not:** Gmail SMTP kullanırken, Google hesabınızda "Uygulama Şifreleri" (App Passwords) oluşturmanız gerekebilir. Doğrudan Gmail şifrenizi kullanmak genellikle tavsiye edilmez ve Google bunu kısıtlamış olabilir.

**Adım 3: Eşya Kaydedildiğinde Bildirimi Tetikleme (`process_item.php` veya `find_item_logic.php`):**

Bir kullanıcı yeni bir "bulunan" eşya kaydettiğinde (veya bir "kayıp" eşyanın durumu "bulundu" olarak güncellendiğinde), eşleşen "kayıp" eşya ilanlarını arayın ve sahiplerine bildirim gönderin.

```php
<?php
// ... PDO veritabanı bağlantısı ($pdo) varsayalım ...
include_once 'email_sender.php'; // E-posta gönderme fonksiyonunu dahil et

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Yeni kaydedilen eşya bilgilerini al (burada varsayımsal olarak gelen POST verileri)
    $newItemTitle = $_POST['title'] ?? '';
    $newItemDescription = $_POST['description'] ?? '';
    $newItemCategory = $_POST['category'] ?? '';
    $newItemLocation = $_POST['location'] ?? '';
    $newItemContactInfo = $_POST['contact_info'] ?? '';
    $newItemStatus = $_POST['item_status'] ?? 'found'; // Örneğin, 'found' olarak işaretlendi

    // ... Veritabanına yeni eşyayı kaydetme işlemi ...
    // try { /* INSERT INTO lost_items ... */ } catch ...
    // Eşyanın veritabanına başarıyla kaydedildiğini varsayalım.
    $newItemId = $pdo->lastInsertId(); // Yeni eklenen eşyanın ID'si

    // 2. Eşleşen "kayıp" eşyaları ara
    // Basit bir eşleşme mantığı: aynı kategori ve başlıkta benzer kelimeler
    $searchKeywords = implode(' OR ', array_map(function($word) {
        return "description LIKE '%" . $word . "%'";
    }, explode(' ', $newItemDescription))); // Açıklamadan kelimeleri ayır ve LIKE sorgusu hazırla

    $sql = "SELECT li.id, li.title, li.description, li.location, li.reported_date, li.contact_info,
                   u.email AS user_email, u.username AS user_name
            FROM lost_items AS li
            JOIN users AS u ON li.user_id = u.id
            WHERE li.item_status = 'lost'
            AND li.category = :category
            AND (li.title LIKE :title_like OR li.description LIKE :description_like)"; // Daha gelişmiş eşleşme için tam metin arama kullanılabilir.

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':category', $newItemCategory);
        $stmt->bindValue(':title_like', '%' . $newItemTitle . '%');
        $stmt->bindValue(':description_like', '%' . $newItemDescription . '%');
        $stmt->execute();
        $matchingLostItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Eşleşen her kayıp eşya sahibine bildirim gönder
        if (count($matchingLostItems) > 0) {
            $foundItemDetailsForEmail = [
                'title' => $newItemTitle,
                'description' => $newItemDescription,
                'location' => $newItemLocation,
                'reported_date' => date('d.m.Y H:i') // Yeni eklenen eşyanın tarihi
            ];

            foreach ($matchingLostItems as $lostItem) {
                $emailSent = sendNotificationEmail(
                    $lostItem['user_email'],
                    $lostItem['user_name'],
                    $lostItem['title'],
                    $foundItemDetailsForEmail,
                    $newItemContactInfo // Bulan kişinin iletişim bilgisi
                );

                if ($emailSent) {
                    echo "Bildirim e-postası başarıyla gönderildi: " . htmlspecialchars($lostItem['user_email']) . "<br>";
                } else {
                    echo "Bildirim e-postası gönderilemedi: " . htmlspecialchars($lostItem['user_email']) . "<br>";
                }
            }
        } else {
            echo "Eşleşen kayıp eşya bulunamadı, bildirim gönderilmedi.";
        }

    } catch (PDOException $e) {
        echo "Eşleşen eşya arama hatası: " . $e->getMessage();
    }
}
?>
```

**Dikkat Edilmesi Gerekenler:**

*   **Eşleşme Mantığı:** Yukarıdaki örnekte basit bir `LIKE` sorgusu kullanıldı. Gerçek bir uygulamada, daha sofistike bir eşleşme algoritmasına ihtiyacınız olabilir:
    *   **Tam Metin Arama:** MySQL'in `FULLTEXT` indeksleri ve `MATCH AGAINST` (bkz. Soru 8) bu tür aramalar için çok daha verimlidir.
    *   **Benzerlik Algoritmaları:** Levenshtein mesafesi veya Jaccard benzerliği gibi algoritmalarla başlık ve açıklama metinlerinin ne kadar benzer olduğunu hesaplayabilirsiniz.
    *   **Konum Bazlı Eşleşme:** Eğer konum bilgisi koordinatlar içeriyorsa, belirli bir mesafe içindeki ilanları arayabilirsiniz.
*   **Arka Plan İşlemleri (Cron Jobs/Queues):** Çok sayıda e-posta göndermeniz gerekiyorsa, e-posta gönderme işlemini web isteği sırasında yapmak yerine bir arka plan işine (cron job veya message queue ile çalışan bir worker) devretmek daha iyidir. Bu, kullanıcının isteğinin hemen yanıtlanmasını sağlar ve e-posta gönderme işlemi uzun sürerse web uygulamanızın yavaşlamasını engeller.
*   **Hata Yönetimi ve Loglama:** E-posta gönderiminde hatalar oluşabilir (geçersiz e-posta adresi, SMTP sunucusu sorunu vb.). Bu hataları yakalayın ve loglayın.
*   **Kullanıcı Tercihleri:** Kullanıcılara e-posta bildirimlerini açma/kapama seçeneği sunmak iyi bir uygulamadır.
*   **Bildirim Sıklığı:** Kullanıcıları çok fazla e-postayla boğmamaya dikkat edin.

Bu adımlarla, kullanıcılarınıza otomatik e-posta bildirimleri gönderebilir ve kayıp eşyaların sahipleriyle tekrar buluşmalarına yardımcı olabilirsiniz.

#### 18. "Tüm ilanlar arasında çalışan bir arama çubuğu nasıl eklenir?"

Web sitenizdeki ilanlar arasında tam metin arama çubuğu eklemek, kullanıcıların istedikleri eşyaları hızla bulmalarını sağlayan temel bir özelliktir. Bu özellik, genellikle kullanıcının girdiği bir anahtar kelimeye göre veritabanında arama yaparak çalışır.

**İş Akışı:**

1.  **HTML Form (Arama Çubuğu):** Sayfanızın üst kısmına veya ana gezinti menüsüne bir `<form>` etiketi içinde bir arama metin kutusu (`<input type="search">`) ve bir gönder düğmesi (`<button type="submit">`) eklenir. Formun `method`'u genellikle `GET` olur, böylece arama terimi URL'de görünür ve sonuçlar paylaşılabilir.
2.  **PHP Sunucu Taraflı İşleme:**
    *   Sayfa yüklendiğinde, PHP `$_GET` süperglobalinden arama terimini alır.
    *   Eğer arama terimi varsa, bu terimi kullanarak veritabanında bir `SELECT` sorgusu oluşturulur.
    *   **SQL Enjeksiyonuna Karşı Güvenlik:** Arama teriminin doğrudan SQL sorgusuna eklenmemesi, mutlaka hazırlanmış ifadeler (prepared statements) kullanılması esastır.
    *   Arama için `LIKE` operatörü veya daha gelişmiş aramalar için `FULLTEXT` indeksleri ve `MATCH AGAINST` (bkz. Soru 8) kullanılabilir.
3.  **Arama Sonuçlarını Görüntüleme:** Veritabanından gelen eşleşen sonuçlar, normal ilan listesi formatında (örneğin Bootstrap kartları veya tablo) sayfada görüntülenir.

**Örnek Uygulama:**

**1. `index.php` (Arama Formu ve Sonuçları):**

```php
<?php
// Veritabanı bağlantısı (PDO)
$dsn = 'mysql:host=localhost;dbname=veritabani_adi;charset=utf8mb4';
$username = 'kullanici';
$password = 'sifre';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$searchQuery = $_GET['q'] ?? ''; // URL'den arama terimini al

$sql = "SELECT id, title, description, category, location, item_status, reported_date, image_url
        FROM lost_items";
$params = [];

if (!empty($searchQuery)) {
    // Arama terimi varsa WHERE koşulu ekle
    $sql .= " WHERE title LIKE :search_title OR description LIKE :search_description";
    $params[':search_title'] = '%' . $searchQuery . '%';
    $params[':search_description'] = '%' . $searchQuery . '%';

    // Eğer tam metin arama (FULLTEXT index) kullanıyorsanız, yukarıdaki LIKE yerine:
    // $sql .= " WHERE MATCH(title, description) AGAINST(:search_query IN NATURAL LANGUAGE MODE)";
    // $params[':search_query'] = $searchQuery;
    // $sql .= " ORDER BY MATCH(title, description) AGAINST(:search_query IN NATURAL LANGUAGE MODE) DESC";
}

$sql .= " ORDER BY reported_date DESC"; // Her zaman tarihe göre sırala

$items = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Eşyalar yüklenirken bir hata oluştu: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıp ve Bulunan Eşyalar - Arama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .item-card {
            margin-bottom: 20px;
            height: 100%;
        }
        .item-card img {
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Kayıp ve Bulunan Eşyalar</h1>

    <!-- Arama Çubuğu Formu -->
    <form class="row mb-4 g-3" method="get" action="">
        <div class="col-md-10">
            <input type="search" class="form-control form-control-lg" id="search_query" name="q"
                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                   placeholder="Eşya başlığı, açıklaması veya anahtar kelime ile ara...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-lg w-100">Ara</button>
        </div>
    </form>

    <?php if (!empty($searchQuery)) { ?>
        <h2 class="mb-4">"<?php echo htmlspecialchars($searchQuery); ?>" için Arama Sonuçları</h2>
    <?php } ?>

    <div class="row">
        <?php if (count($items) > 0) {
            foreach ($items as $item) { ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card item-card <?php echo ($item['item_status'] == 'lost') ? 'border-danger' : 'border-success'; ?>">
                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/300x200?text=Resim+Yok'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted">Durum: <?php echo ($item['item_status'] == 'lost') ? 'Kayıp' : 'Bulundu'; ?></p>
                            <p class="card-text"><small class="text-muted">Kategori: <?php echo htmlspecialchars($item['category']); ?></small></p>
                            <p class="card-text"><small class="text-muted">Konum: <?php echo htmlspecialchars($item['location']); ?></small></p>
                            <p class="card-text"><small class="text-muted">Bildirildi: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($item['reported_date']))); ?></small></p>
                            <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm mt-2">Detayları Gör</a>
                        </div>
                    </div>
                </div>
            <?php }
        } else { ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <?php echo !empty($searchQuery) ? 'Aradığınız kritere uygun eşya bulunamadı.' : 'Henüz hiç eşya kaydedilmemiş.'; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

**Açıklama:**

*   **HTML Formu:**
    *   `<input type="search" ... name="q">`: HTML5'in arama kutusu tipini kullanır ve `name="q"` ile PHP'de `$_GET['q']` olarak erişilebilir olmasını sağlar.
    *   `value="<?php echo htmlspecialchars($searchQuery); ?>"`: Kullanıcının son arama terimini hatırlamasını sağlar, bu da iyi bir kullanıcı deneyimidir. `htmlspecialchars` ile XSS saldırılarına karşı korunur.
    *   `method="get"`: Formu GET isteği olarak gönderir, böylece arama terimi URL'de görünür.
*   **PHP İşleme:**
    *   `$searchQuery = $_GET['q'] ?? '';`: URL'den gelen `q` parametresini alır, yoksa boş bir dize atar.
    *   `if (!empty($searchQuery))`: Eğer arama terimi boş değilse, SQL sorgusuna bir `WHERE` koşulu eklenir.
    *   `WHERE title LIKE :search_title OR description LIKE :search_description`: Arama teriminin hem `title` hem de `description` sütunlarında aranmasını sağlar. `%` joker karakteri ile kısmi eşleşmeler bulunur.
    *   `$params[':search_title'] = '%' . $searchQuery . '%';`: Parametrenin başına ve sonuna `%` eklenir. Bu şekilde parametreler güvenli bir şekilde bağlanır.
    *   **Tam Metin Arama Yorum Satırı:** Daha gelişmiş ve performanslı bir arama için `FULLTEXT` indeksleri ve `MATCH AGAINST` kullanımına dair yorum satırları bırakılmıştır. (MySQL'de `lost_items` tablosunda `title` ve `description` sütunlarında `FULLTEXT` indeksinizin olması gerekir.)
    *   `$stmt = $pdo->prepare($sql); $stmt->execute($params);`: Her zaman hazırlanmış ifadeler kullanarak SQL enjeksiyonunu önleyin.
    *   Arama sonuçları boşsa, kullanıcıya ilgili bir mesaj gösterilir.

Bu yapıyla, kullanıcılarınız web sitenizdeki tüm kayıp/bulunan eşya ilanları arasında hızlı ve etkili bir şekilde arama yapabilir.

#### 19. "Admin kullanıcıları için tüm kayıp eşyaların indirilebilir bir PDF'sini nasıl oluşturabilirim?"

Tüm kayıp eşyaların indirilebilir bir PDF raporunu oluşturmak, yönetici kullanıcılar için çok kullanışlı bir özelliktir. Bu, genellikle sunucu tarafında bir PDF oluşturma kütüphanesi kullanarak yapılır. PHP için popüler PDF kütüphaneleri şunlardır:

1.  **TCPDF:** Çok güçlü ve esnek bir kütüphanedir. Sıfırdan PDF oluşturma, HTML/CSS'i PDF'e dönüştürme, barkodlar, resimler vb. gibi geniş bir özellik yelpazesine sahiptir. Öğrenme eğrisi biraz dik olabilir, ancak tam kontrol sağlar.
2.  **Dompdf:** HTML ve CSS'yi doğrudan PDF'e dönüştürmeyi amaçlar. Eğer verilerinizi HTML/CSS ile kolayca formatlayabiliyorsanız, Dompdf genellikle daha hızlı bir geliştirme süreci sunar.

Bu cevapta, HTML'den PDF oluşturan ve genellikle daha kolay entegre edilebilen **Dompdf**'i kullanma adımlarını açıklayacağım.

**İş Akışı:**

1.  **Gerekli Kütüphaneleri Kurma:** Dompdf kütüphanesini projenize Composer ile veya manuel olarak ekleyin.
2.  **Veritabanından Verileri Çekme:** PDF'e eklenecek tüm kayıp eşya bilgilerini veritabanından çekin.
3.  **HTML Şablonu Oluşturma:** Çektiğiniz verileri içeren bir HTML dizesi (veya PHP ile dinamik olarak oluşturulan bir HTML yapısı) hazırlayın. Bu HTML, PDF belgesinin içeriği olacaktır. HTML ve temel CSS ile tablolama, başlıklar, paragraflar kullanabilirsiniz.
4.  **PDF Oluşturma ve Ayarları:** Dompdf'i kullanarak bu HTML'yi bir PDF'e dönüştürün. Sayfa boyutu (A4), yönlendirme (dikey/yatay) gibi ayarları yapılandırın.
5.  **Dosyayı Gönderme:** Oluşturulan PDF'i tarayıcıya "indirme" olarak gönderin veya sunucuda bir dosyaya kaydedin.

**Örnek Uygulama (Dompdf ile):**

**Adım 1: Dompdf'i Kurun**

Composer kullanıyorsanız (önerilir):
`composer require dompdf/dompdf`

**Adım 2: PHP Dosyası Oluşturma (`generate_lost_items_pdf.php`):**

Bu dosya bir admin sayfasından çağrılabilir veya doğrudan bir bağlantı ile erişilebilir olmalıdır (tabii ki yönetici kontrolüyle korunmalı!).

```php
<?php
// Admin kontrolü (Soru 16'daki admin_check.php dosyasını dahil edebilirsiniz)
include_once 'admin_check.php'; // Bu dosya sizi admin değilseniz yönlendirecektir.

require 'vendor/autoload.php'; // Composer ile kurulduysa

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Veritabanı Bağlantısı
$dsn = 'mysql:host=localhost;dbname=veritabani_adi;charset=utf8mb4';
$username = 'kullanici';
$password = 'sifre';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// 2. Tüm kayıp eşyaları veritabanından çek
$sql = "SELECT li.title, li.description, li.category, li.location, li.reported_date,
               u.username AS reporter_username
        FROM lost_items AS li
        JOIN users AS u ON li.user_id = u.id
        WHERE li.item_status = 'lost'
        ORDER BY li.reported_date DESC";

$items = [];
try {
    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Eşyalar çekilirken hata oluştu: " . $e->getMessage());
}

// 3. HTML içeriğini oluştur
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kayıp Eşya Raporu</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; color: #333; }
        .footer { text-align: center; font-size: 8pt; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Kayıp Eşya Raporu</h1>
    <p>Oluşturulma Tarihi: ' . date('d.m.Y H:i:s') . '</p>

    <table>
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Kategori</th>
                <th>Konum</th>
                <th>Bildirim Tarihi</th>
                <th>Bildiren Kullanıcı</th>
            </tr>
        </thead>
        <tbody>';

if (count($items) > 0) {
    foreach ($items as $item) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($item['title']) . '</td>
                <td>' . htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : '') . '</td>
                <td>' . htmlspecialchars($item['category']) . '</td>
                <td>' . htmlspecialchars($item['location']) . '</td>
                <td>' . htmlspecialchars(date('d.m.Y H:i', strtotime($item['reported_date']))) . '</td>
                <td>' . htmlspecialchars($item['reporter_username']) . '</td>
            </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align: center;">Kayıp eşya bulunamadı.</td></tr>';
}

$html .= '
        </tbody>
    </table>
    <div class="footer">Bu rapor, sistemden otomatik olarak oluşturulmuştur.</div>
</body>
</html>';

// 4. Dompdf Ayarları ve Oluşturma
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // Türkçe karakterler için font ayarı
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Harici resimler için (eğer kullanılıyorsa)

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// PDF boyutunu ve yönünü belirle
$dompdf->setPaper('A4', 'portrait'); // A4 boyutunda, dikey (portrait)

// PDF'i render et
$dompdf->render();

// 5. PDF'i tarayıcıya gönder (indirme olarak)
$filename = "kayip_esya_raporu_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]); // "Attachment" => true indirme sağlar, false tarayıcıda açar

exit;
?>
```
**Not:** Dompdf'in Türkçe karakterleri doğru görüntülemesi için genellikle `defaultFont` ayarını yapmanız gerekir. `DejaVu Sans` gibi yaygın bir Unicode fontu kullanabilirsiniz. Bu fontun Dompdf'in font dizininde veya sisteminizde yüklü olması gerekebilir.

**3. Admin Panelinde Bağlantı:**

Admin panelinizde, bu PDF'i oluşturan sayfaya bir bağlantı ekleyin:

```html
<!-- admin_dashboard.php içinde -->
<p>
    <a href="generate_lost_items_pdf.php" class="btn btn-info" target="_blank">
        <i class="fas fa-file-pdf"></i> Kayıp Eşyaları PDF Olarak İndir
    </a>
</p>
```
`target="_blank"` kullanmak, PDF'in yeni bir sekmede açılmasını veya indirilmesini sağlar, böylece admin paneli sayfanız açık kalır.

**Dikkat Edilmesi Gerekenler:**

*   **Performans:** Çok sayıda kayıt varsa, PDF oluşturma işlemi biraz zaman alabilir. Bu durumu kullanıcıya belirtmeyi düşünebilirsiniz. Çok büyük raporlar için PDF oluşturma işlemini bir arka plan işine atmak daha iyi olabilir.
*   **Bellek Kullanımı:** Özellikle çok büyük resimler içeren veya çok sayfalı PDF'ler oluştururken bellek sınırlarına dikkat edin. `memory_limit` ayarını yükseltmeniz gerekebilir.
*   **Güvenlik:** `generate_lost_items_pdf.php` dosyasının sadece yetkili adminler tarafından erişilebilir olduğundan kesinlikle emin olun (yukarıdaki `admin_check.php` dahil edilmelidir).
*   **Fontlar:** Türkçe karakter sorunlarını aşmak için Dompdf'in fontlarını doğru şekilde yapılandırdığınızdan emin olun. Dompdf'in kendi dokümantasyonunda fontların nasıl yükleneceği ve kullanılacağı ayrıntılı olarak açıklanmıştır.

Bu adımlarla, yöneticileriniz için tüm kayıp eşyaların kolayca indirilebilir bir PDF raporunu sunabilirsiniz.
