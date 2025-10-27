<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Todo</title>
  <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: rgba(0,0,0,0.06);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: "Segoe UI", sans-serif;
      margin: 0;
    }
    .modal-card {
      width: 520px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      padding: 24px;
      animation: fadeIn .25s ease;
    }
    @keyframes fadeIn { from {opacity:0; transform:translateY(6px)} to {opacity:1; transform:none} }
    .meta { color: #6c757d; font-size: .95rem; }
    .field-box { border: 1px solid #f0f0f0; padding: .6rem; border-radius: .5rem; background:#fbfbff; }
  </style>
</head>
<body>

<?php if (!isset($todo) || !$todo): ?>
  <div class="modal-card text-center">
    <h4 class="text-danger">Data Todo tidak ditemukan</h4>
    <p class="meta">ID yang diminta tidak ada atau sudah dihapus.</p>
    <div class="mt-3">
      <a href="index.php" class="btn btn-primary">Kembali</a>
    </div>
  </div>

<?php else: ?>
  <div class="modal-card">
    <h4 class="mb-3">📋 Detail Todo</h4>

    <div class="mb-3">
      <label class="form-label"><strong>Judul</strong></label>
      <div class="field-box"><?= htmlspecialchars($todo['title']) ?></div>
    </div>

    <div class="mb-3">
      <label class="form-label"><strong>Deskripsi</strong></label>
      <div class="field-box"><?= nl2br(htmlspecialchars($todo['description'] ?: 'Tidak ada deskripsi')) ?></div>
    </div>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div>
        <label class="form-label"><strong>Status</strong></label>
        <div>
          <?= ($todo['is_finished'] === 't' || $todo['is_finished'] == 1)
              ? '<span class="badge bg-success">Selesai</span>'
              : '<span class="badge bg-danger">Belum Selesai</span>' ?>
        </div>
      </div>

      <div class="text-end meta">
        <div><strong>Dibuat:</strong><br><?= htmlspecialchars($todo['created_at']) ?></div>
        <div class="mt-2"><strong>Diupdate:</strong><br><?= htmlspecialchars($todo['updated_at']) ?></div>
      </div>
    </div>

    <div class="d-flex justify-content-end">
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
<?php endif; ?>

</body>
</html>
