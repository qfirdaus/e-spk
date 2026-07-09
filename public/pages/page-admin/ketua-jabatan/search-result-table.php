<?php 
//data array from model KetuaJabatan -> searchStaf
$stafList = $response['data'] ?? []; 
?>

<?php if ($response['status'] === 'success' && !empty($stafList)): ?>
    <table class='table table-bordered table-striped table-hover'>
        <thead>
            <tr>
                <th>No.Staf</th>
                <th>Nama</th> 
                <th style='width:50px'>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stafList as $row): ?>
                <tr>
                    <td align='center'><?= htmlspecialchars($row["nopekerja"] ?? '') ?></td>
                    <td align='left'><?= strtoupper(htmlspecialchars($row["nama_staf"] ?? '')) ?></td>
                    <td>
                        <center>
                            <button class='btn btn-sm btn-outline-info rounded-3' type='button' name='btnTambah' id='btnTambah' 
                                    data-bs-toggle='modal' data-bs-target='#tambah' 
                                    data-nostaf="<?= htmlspecialchars($row["nopekerja"] ?? '') ?>" 
                                    data-nokp="<?= htmlspecialchars($row["nokp"] ?? '') ?>" 
                                    data-nama="<?= htmlspecialchars($row["nama_staf"] ?? '') ?>" 
                                    data-kdjbtnhakiki="<?= htmlspecialchars($row["kdjbtnhakiki"] ?? '') ?>" 
                                    data-jabatan="<?= htmlspecialchars($row["jabatanhakiki"] ?? '') ?>" 
                                    data-notel="<?= htmlspecialchars($row["telefon_pej"] ?? '') ?>" 
                                    data-emel="<?= htmlspecialchars($row["email"] ?? '') ?>" 
                                    title='Tambah Pengguna'>
                                <i class="ri-add-fill"></i>
                            </button>
                        </center>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-warning text-center">
        <?= htmlspecialchars($response['message'] ?? 'Tiada rekod ditemui.') ?>
    </div>
<?php endif; ?>