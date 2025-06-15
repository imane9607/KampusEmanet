<?php
require_once '../includes/auth.php';
require_once '../includes/items.php';

Auth::requireAdmin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = Items::get($id);
if (!$item) {
    http_response_code(404);
    exit('Item not found');
}
$statuses = array_filter(array_unique(Items::getStatuses()),fn($s)=>trim($s)!=='');
$defaultStatuses = ['kayıp','found','geri verildi'];
$statuses = array_values(array_unique(array_merge($statuses,$defaultStatuses)));
$categories = Items::getCategories();
if(empty($categories)){
    $categories = ['elektronik','kişisel_öge','çanta','anahtar','aksesuar','kitap','giyim','belge','diğer'];
}

$toast = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? '';
    $date = $_POST['date'] ?? '';

    if ($title !== '' && $status !== '' && $category !== '' && $location!=='') {
        Items::adminUpdate($id, $title, $description, $category, $location, $status, $date);
        header('Location: manage-listings.php?updated=1');
        exit;
    } else {
        $toast = 'Lütfen zorunlu alanları doldurun';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta name="description" content="Kampus Emanet - Kayıp ve bulunan eşyalar için kayıt düzenleme">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kampüs Emanet - Kayıt Düzenle</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="../assets/css/theme.css" />
</head>
<body class="bg-gray-100">
<div class="max-w-3xl mx-auto py-12 px-4 lg:px-0">
    <h1 class="text-2xl font-bold mb-4">Kampus Emanet - Edit Item</h1>
    <?php if($toast):?><p class="mb-4 text-red-600"><?= htmlspecialchars($toast) ?></p><?php endif;?>
    <form method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow">
        <div>
            <label class="block text-sm font-medium mb-1">Başlık</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($item['title']) ?>" required />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Açıklama</label>
            <textarea name="description" rows="4" class="w-full border rounded px-3 py-2" required><?= htmlspecialchars($item['description']) ?></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Location</label>
                <input type="text" name="location" class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($item['location']) ?>" required />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2" required>
                    <?php foreach($statuses as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>" <?= $item['status']===$st?'selected':''; ?>><?= ucwords(str_replace('_', ' ', $st)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category" class="w-full border rounded px-3 py-2" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $item['category']===$cat?'selected':''; ?>><?= ucwords(str_replace('_', ' ', $cat), 'tr') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tarih</label>
            <input type="date" name="date" class="w-full border rounded px-3 py-2" value="<?= strftime('%Y-%m-%d', strtotime($item['date'])) ?>" />
        </div>
        <div class="flex justify-end gap-4">
            <a href="manage-listings.php" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">İptal</a>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Güncelle</button>
        </div>
    </form>
</div>
</body>
</html>
