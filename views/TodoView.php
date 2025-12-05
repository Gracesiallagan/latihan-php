<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Todo List PostgreSQL</title>
  <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <style>
       /* 🌈 Warna dasar & font */
    body {
      background: linear-gradient(135deg, #eef3ff, #f9faff);
      min-height: 100vh;
      font-family: "Poppins", "Segoe UI", sans-serif;
      color: #333;
    }

    /* 🔹 Navbar */
    .navbar {
      background: white !important;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .navbar-brand {
      font-weight: 600;
      color: #5a52e0 !important;
      font-size: 1.3rem;
    }

    /* 📦 Card utama */
    .card-custom {
      background: white;
      border-radius: 1.2rem;
      box-shadow: 0 6px 25px rgba(0,0,0,0.07);
      padding: 2rem;
      animation: fadeIn .3s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: none;}
    }

    /* 📋 Tabel todo */
    table {
      font-size: 0.95rem;
    }
    thead {
      background: linear-gradient(90deg, #5a52e0, #7870ff);
      color: white;
    }
    .table-hover tbody tr:hover {
      background: #f6f7ff;
    }
    .table td, .table th {
      vertical-align: middle;
      padding: 0.75rem 1rem;
    }

    /* 🔘 Tombol */
    .btn {
      border-radius: 10px;
      padding: 0.45rem 0.9rem;
      font-weight: 500;
      transition: 0.2s ease;
    }
    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .btn-success {
      background: linear-gradient(135deg, #43b581, #37a372);
      border: none;
    }
    .btn-warning {
      background: #ffcc66;
      border: none;
      color: #000;
    }
    .btn-danger {
      background: #ff6b6b;
      border: none;
    }
    .btn-info {
      background: #5a52e0;
      border: none;
    }

    /* 🔍 Filter bar & search */
    .filter-bar a {
      border-radius: 8px;
      font-size: 0.85rem;
    }
    .filter-bar .active {
      background: #5a52e0;
      color: white !important;
      border-color: #5a52e0;
    }
    .form-control {
      border-radius: 8px;
    }

    /* 💬 Modal tampilan */
    .modal-content {
      border-radius: 1rem;
    }

    /* 🪄 Hover baris */
    tbody tr {
      cursor: grab;
      transition: 0.2s;
    }
    tbody tr:active {
      cursor: grabbing;
    }

    /* Alert message */
    .alert-custom {
      border-radius: 10px;
      animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
      from {opacity: 0; transform: translateY(-10px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>
<nav class="navbar navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">Todo App PostgreSQL</a>
  </div>
</nav>

<div class="container">
  <div class="card card-custom p-4">
    
    <!-- PERBAIKAN: Pesan notifikasi -->
    <?php if (isset($_GET['msg'])): ?>
      <?php if ($_GET['msg'] === 'duplicate'): ?>
        <div class="alert alert-warning alert-dismissible fade show alert-custom" role="alert">
          <strong>Peringatan!</strong> Judul todo sudah ada. Gunakan judul yang berbeda.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($_GET['msg'] === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show alert-custom" role="alert">
          <strong>Berhasil!</strong> Todo berhasil ditambahkan.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($_GET['msg'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-custom" role="alert">
          <strong>Error!</strong> Terjadi kesalahan saat menyimpan data.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Daftar Todo</h4>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTodo">+ Tambah Todo</button>
    </div>

    <!-- PERBAIKAN: Filter dan Pencarian dengan parameter yang benar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="filter-bar">
        <a href="index.php?page=index&filter=all<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"
           class="btn btn-outline-dark btn-sm <?= ($_GET['filter'] ?? 'all') === 'all' ? 'active' : '' ?>">
           Semua
        </a>

        <a href="index.php?page=index&filter=pending<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"
           class="btn btn-outline-danger btn-sm <?= ($_GET['filter'] ?? '') === 'pending' ? 'active' : '' ?>">
           Belum
        </a>

        <a href="index.php?page=index&filter=done<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"
           class="btn btn-outline-success btn-sm <?= ($_GET['filter'] ?? '') === 'done' ? 'active' : '' ?>">
           Selesai
        </a>
      </div>

      <!-- PERBAIKAN: Form search mempertahankan filter -->
      <form method="get" class="d-flex">
        <input type="hidden" name="page" value="index">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
        <input type="text" name="search" class="form-control me-2" placeholder="Cari " value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="min-width: 250px;">
        <button class="btn btn-primary" type="submit">Cari</button>
        <?php if (!empty($_GET['search'])): ?>
          <a href="index.php?page=index&filter=<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>" class="btn btn-secondary ms-2">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Info filter aktif -->
    <?php 
    $filterText = 'Semua Todo';
    if (($_GET['filter'] ?? 'all') === 'done') $filterText = 'Todo Selesai';
    if (($_GET['filter'] ?? 'all') === 'pending') $filterText = 'Todo Belum Selesai';
    ?>
    <div class="mb-2">
      <small class="text-muted">
        Menampilkan: <strong><?= $filterText ?></strong>
        <?php if (!empty($_GET['search'])): ?>
          | Pencarian: <strong>"<?= htmlspecialchars($_GET['search']) ?>"</strong>
        <?php endif; ?>
        | Total: <strong><?= count($todos ?? []) ?></strong> todo
      </small>
    </div>

    <!-- Tabel Todo -->
    <table class="table table-hover" id="todoTable">
      <thead class="table-primary">
        <tr>
          <th>Judul</th>
          <th>Status</th>
          <th style="width:30%">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($todos)): foreach ($todos as $todo): ?>
        <tr data-id="<?= $todo['id'] ?>">
          <td><?= htmlspecialchars($todo['title']) ?></td>
          <td>
            <?= ($todo['is_finished'] === 't' || $todo['is_finished'] == 1)
              ? '<span class="badge bg-success">Selesai</span>'
              : '<span class="badge bg-danger">Belum</span>' ?>
          </td>
          <td>
            <button class="btn btn-info btn-sm text-white"
              onclick="showDetail(
                `<?= htmlspecialchars($todo['title'], ENT_QUOTES) ?>`,
                `<?= nl2br(htmlspecialchars($todo['description'] ?? '', ENT_QUOTES)) ?>`,
                `<?= ($todo['is_finished'] === 't' || $todo['is_finished'] == 1) ? 'Selesai' : 'Belum Selesai' ?>`
              )">
              Detail
            </button>
            <button class="btn btn-warning btn-sm btn-edit"
              data-id="<?= $todo['id'] ?>"
              data-title="<?= htmlspecialchars($todo['title'], ENT_QUOTES) ?>"
              data-desc="<?= htmlspecialchars($todo['description'] ?? '', ENT_QUOTES) ?>"
              data-done="<?= ($todo['is_finished'] === 't' || $todo['is_finished'] == 1) ? 1 : 0 ?>">
              Ubah
            </button>
            <a href="?page=delete&id=<?= $todo['id'] ?>" class="btn btn-danger btn-sm"
              onclick="return confirm('Yakin ingin menghapus todo ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="3" class="text-center text-muted">
          <?php if (!empty($_GET['search'])): ?>
            Tidak ada hasil untuk pencarian "<?= htmlspecialchars($_GET['search']) ?>"
          <?php else: ?>
            Belum ada todo
          <?php endif; ?>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH -->


<div class="modal fade" id="addTodo">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?page=create" id="addForm">
        <div class="modal-header"><h5 class="modal-title">Tambah Todo</h5></div>
        <div class="modal-body">
          <label>Judul <span class="text-danger">*</span></label>
          <input name="title" id="addTitle" class="form-control" required maxlength="255">
          <small class="text-muted">Judul harus unik</small>
          
          <label class="mt-2">Deskripsi</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editTodo">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm">
        <input type="hidden" name="id" id="editId">
        <div class="modal-header"><h5 class="modal-title">Edit Todo</h5></div>
        <div class="modal-body">
          <label>Judul <span class="text-danger">*</span></label>
          <input name="title" id="editTitle" class="form-control" required maxlength="255">
          <small class="text-muted">Judul harus unik</small>
          
          <label class="mt-2">Deskripsi</label>
          <textarea name="description" id="editDesc" class="form-control" rows="3"></textarea>
          
          <label class="mt-2">Status</label>
          <select name="is_finished" id="editStatus" class="form-select">
            <option value="0">Belum</option>
            <option value="1">Selesai</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal fade" id="detailTodo">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Detail Todo</h5></div>
      <div class="modal-body">
        <p><strong>Judul:</strong> <span id="detailTitle"></span></p>
        <p><strong>Deskripsi:</strong></p>
        <div class="border rounded p-2 bg-light" id="detailDesc"></div>
        <p class="mt-3"><strong>Status:</strong> <span id="detailStatus" class="badge"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script>
const editModal = new bootstrap.Modal(document.getElementById('editTodo'));
const detailModal = new bootstrap.Modal(document.getElementById('detailTodo'));
const addModal = new bootstrap.Modal(document.getElementById('addTodo'));

// Auto hide alert setelah 5 detik
setTimeout(() => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

// === TOMBOL EDIT ===
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('editId').value = btn.dataset.id;
    document.getElementById('editTitle').value = btn.dataset.title;
    document.getElementById('editDesc').value = btn.dataset.desc;
    document.getElementById('editStatus').value = btn.dataset.done;
    editModal.show();
  });
});

// === SUBMIT EDIT FORM ===
document.getElementById('editForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Menyimpan...';
  
  const id = document.getElementById('editId').value;
  const title = document.getElementById('editTitle').value.trim();
  const description = document.getElementById('editDesc').value.trim();
  const is_finished = document.getElementById('editStatus').value;

  try {
    const res = await fetch('?page=update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json', 
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({id, title, description, is_finished})
    });
    
    const data = await res.json();

    if (data.success) {
      const todo = data.todo;
      const row = document.querySelector(`tr[data-id="${id}"]`);
      
      if (row) {
        // Update isi tabel secara realtime
        row.children[0].textContent = todo.title;
        row.children[1].innerHTML = (todo.is_finished === 't' || todo.is_finished == 1)
          ? '<span class="badge bg-success">Selesai</span>'
          : '<span class="badge bg-danger">Belum</span>';
        
        // Update data di tombol ubah
        const editBtn = row.querySelector('.btn-edit');
        editBtn.dataset.title = todo.title;
        editBtn.dataset.desc = todo.description ?? '';
        editBtn.dataset.done = (todo.is_finished === 't' || todo.is_finished == 1) ? 1 : 0;
        
        // Tambahkan efek visual
        row.style.background = '#d4edda';
        setTimeout(() => {
          row.style.background = '';
        }, 1500);
      }
      
      editModal.hide();
      
      // Tampilkan notifikasi sukses
      showNotification('Todo berhasil diperbarui!', 'success');
      
    } else {
      alert(data.message || 'Gagal memperbarui todo!');
    }
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan koneksi!');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
});

// === DETAIL MODAL ===
function showDetail(title, desc, status){
  document.getElementById('detailTitle').textContent = title;
  document.getElementById('detailDesc').innerHTML = desc || '<i class="text-muted">Tidak ada deskripsi</i>';
  const statusEl = document.getElementById('detailStatus');
  statusEl.textContent = status;
  statusEl.className = 'badge ' + (status === 'Selesai' ? 'bg-success' : 'bg-danger');
  detailModal.show();
}

// === SORTABLE / DRAG ===
new Sortable(document.querySelector('#todoTable tbody'), {
  animation: 150,
  onEnd: function(){
    let order = [];
    document.querySelectorAll('#todoTable tbody tr').forEach(tr => {
      const id = tr.dataset.id;
      if (id) order.push(id);
    });
    
    fetch('?page=reorder', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({order})
    }).then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          showNotification('Urutan berhasil disimpan!', 'info');
        }
      });
  }
});

// Fungsi helper untuk notifikasi
function showNotification(message, type = 'success') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
  alertDiv.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  const container = document.querySelector('.card-custom');
  container.insertBefore(alertDiv, container.firstChild);
  
  setTimeout(() => {
    const bsAlert = new bootstrap.Alert(alertDiv);
    bsAlert.close();
  }, 3000);
}

// Validasi form tambah sebelum submit
document.getElementById('addForm').addEventListener('submit', function(e) {
  const title = document.getElementById('addTitle').value.trim();
  if (title === '') {
    e.preventDefault();
    alert('Judul tidak boleh kosong!');
  }
});
</script>
</body>
</html>