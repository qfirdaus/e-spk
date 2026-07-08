<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusUniversitiController.php';

$controller = new MaklumatKursusUniversitiController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo "Ralat: " . $controller->getErrorMessage();
}

?>
<div class="icares-address-content">
    <div class="tab-pane show active">
      <div class="icares-address-panel-header">
        <h5><?= h(tr('carian', 'Carian')) ?></h5>
      </div>

      <form id="form-maklumat-plo" method="POST" action="">
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div class="col-md-6 gx-4">
                
                  <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('peringkat_pengajian','Peringkat Pengajian')) ?></label>
                    <div class="col-sm-8">
                      <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                          <option value="" <?= (($_SESSION["pengajiankursus"] ?? '') === '') ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                          <option value="Asasi" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                          <option value="Diploma" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                          <option value="Sarjana Muda" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                      </select>                    
                    </div>                 
                  </div>   

                  <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('sesi','Sesi')) ?></label>
                    <div class="col-sm-8"> 
                      <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi_kursus" id="selectSesi_kursus">
                          <option value="" <?= empty($_SESSION["sesikursus"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                          
                          <?php foreach ($data['list_sesi'] as $sesi): ?>
                          <option value="<?= h($sesi['f005term']) ?>" <?= ($sesi['f005term'] === ($_SESSION["sesikursus"] ?? '')) ? 'selected' : '' ?> >
                              <?= h($sesi['f005term']) ?> - <?= h($sesi['semester']) ?>
                          </option>
                          <?php endforeach; ?>
                      </select>                    
                    </div>                 
                  </div>                                      

              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
</div> 
<br><br>
<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_kursus_universiti','Senarai Kursus Universiti')) ?></h5>

    <div class="list-actions" style="float: right; margin-bottom:10px;">
        <?php 
            $selectedTerm = $data['selected_term_detail'] ?? [];

            $sesiID = $selectedTerm['f005term'] ?? '';
            $semester = $selectedTerm['semester'] ?? '';
        ?>

        <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                data-bs-toggle="modal" data-bs-target="#tambah" 
                data-bs-container="body"
                data-sesiid="<?= h($sesiID) ?>"
                data-sesi="<?= h($semester) ?>"
                title="<?= h(tr('tambah_kursus_universiti', 'Tambah Kursus Universiti')) ?>">
            <i class="ri-add-line"></i>
        </button>     
    </div>    
  </div>
  <div class="table-responsive dt-standard">
    <table id="dataPLODT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('COL-KOD-KURSUS', 'Kod Kursus')) ?></th>
        <th class="small w-15"><?= h(tr('COL-NAMA-KURSUS', 'Nama Kursus')) ?></th>
        <th class="small w-12"><?= h(tr('COL-KATEGORI-KURSUS', 'Kategori Kursus')) ?></th>
        <th class="small w-12"><?= h(tr('COL-PENYELARAS', 'Penyelaras')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataSubjectRegistered = $data['list_subject_registered'] ?? [];

        // Semak jika array kosong
        if (empty($list_dataSubjectRegistered)): 
      ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataSubjectRegistered as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr>
            <td class="col-bil text-center"><?= $i + 1 ?></td>         
            <td><?= h($row['kod_kursus'] ?? '') ?></td>
            <td><?= h($row['subjekbm'] ?? '') ?></td>
            <td>
                <form action="" method="POST">
                    <input name="txtIdKursus" type="hidden" value="<?= h($row['id_kursus'] ?? '') ?>"/>
                    <input name="txtKodKursus" type="hidden" value="<?= h($row['kod_kursus'] ?? '') ?>"/>
                    
                    <select class="form-select form-select-sm" name="selectKategoriKursus" onchange="this.form.submit()">
                        <option value="0" <?= empty($row['kategori_kursus']) ? 'selected' : '' ?>>
                            - <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -
                        </option>
                        
                        <option value="Teras" <?= (($row['kategori_kursus'] ?? '') === 'Teras') ? 'selected' : '' ?>>
                            Teras
                        </option>
                        
                        <option value="Elektif" <?= (($row['kategori_kursus'] ?? '') === 'Elektif') ? 'selected' : '' ?>>
                            Elektif
                        </option>
                    </select>
                </form>          
            </td>

            <td>
              <form action="" method="POST" id="form_<?= h($row['id_kursus']) ?>">
                  <div class="row g-2 align-items-center">
                      <input name="txtIdKursus" type="hidden" value="<?= h($row['id_kursus'] ?? '') ?>"/>
                      <input name="txtKodKursus" type="hidden" value="<?= h($row['kod_kursus'] ?? '') ?>"/>
                      
                      <div class="col-sm-10">
                          <select class="form-select form-select-sm select2 select-penyelaras-dropdown" 
                                  id="select_<?= h($row['id_kursus']) ?>" 
                                  name="selectPenyelaras"
                                  onchange="hantarBorangPenyelaras(this)">
                              
                              <option value="0" <?= empty($row['penyelaras_kursus']) ? 'selected' : '' ?>>
                                  - <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -
                              </option>
                              
                              <?php if (!empty($row['penyelaras_kursus'])): ?>
                                  <option value="<?= h($row['penyelaras_kursus']) ?>" selected>
                                      <?= h($row['penyelaras_nama'] ?? '') ?> - <?= h($row['penyelaras_kursus']) ?>
                                  </option>
                              <?php endif; ?>

                              <?php 
                              if (!empty($row['senarai_pensyarah'])):
                                  foreach ($row['senarai_pensyarah'] as $ps): 
                                      if (trim($ps['nopekerja']) === trim($row['penyelaras_kursus'] ?? '')) continue;
                              ?>
                                  <option value="<?= h($ps['nopekerja']) ?>">
                                      <?= h($ps['gelar_nama'] ?? '') ?> - <?= h($ps['nopekerja']) ?>
                                  </option>
                              <?php 
                                  endforeach;
                              endif; 
                              ?>
                          </select>
                      </div>
                      
                      <div class="col-sm-2"> 
                          <button class="btn btn-sm btn-outline-secondary" 
                                  type="button" 
                                  onclick="resetPenyelaras('<?= h($row['id_kursus']) ?>')"
                                  title="Reset">
                              <i class="ri-repeat-line"></i>
                          </button>
                      </div>
                  </div>
              </form>       
            </td>       
        </tr>
      <?php 
            endforeach; 
        endif; 
      ?>
    </tbody>
    </table>
  </div>
</div>

<?php if (isset($_SESSION['flash_alert'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '<?= $_SESSION['flash_alert']['icon'] ?>',
            title: '<?= $_SESSION['flash_alert']['title'] ?>',
            text: '<?= $_SESSION['flash_alert']['message'] ?>',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
<?php 
    // PENTING: Hapuskan session selepas digunakan supaya alert tak keluar lagi bila page di-refresh
    unset($_SESSION['flash_alert']); 
endif; 
?>