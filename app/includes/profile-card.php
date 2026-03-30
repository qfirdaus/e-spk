            <div class="profile-hero">
              <div class="d-flex align-items-center gap-3 flex-wrap position-relative">
                <div class="position-relative">
                  <img src="<?= h($avatarUrl) ?>"
                      alt="<?= h(tr('profile_avatar_alt','Avatar pengguna')) ?>"
                      class="avatar"
                      onerror="this.onerror=null;this.src='<?= h(base_url('assets/images/no-image.jpg')) ?>';">
                  <span class="status-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"
                        title="<?= h($isActive ? tr('profile_status_active','Aktif') : tr('profile_status_inactive','Tidak Aktif')) ?>"></span>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="display-name fs-4 mb-0">
                      <?= h($namaPenuh !== '' ? $namaPenuh : '—') ?>
                    </span>
                  </div>

                  <div class="subline mt-1">
                    <?php if ($jawGred !== ''): ?>
                      <span class="chip">
                        <i class="ri-briefcase-2-line"></i><?= h($jawGred) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($jabatan !== ''): ?>
                      <span class="chip">
                        <i class="ri-building-2-line"></i><?= h($jabatan) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="quick-actions d-flex align-items-center gap-2 ms-auto">
                  <?php if ($stafID !== ''): 
                          if($_SESSION['auth_type'] === 'student'):  
                            $label_button = h(tr('profile_btn_copy_no_staf','Salin No. Matrik'));
                          else: 
                            $label_button = h(tr('profile_btn_copy_no_matrik','Salin No. Staf'));
                          endif;
                  ?>
                    <button class="btn btn-sm btn-copy-staf" 
                            type="button"
                            aria-label="<?= $label_button ?>"
                            data-copy-value="<?= h($stafID) ?>">
                      <i class="ri-file-copy-2-line me-1" aria-hidden="true"></i>
                      <?= $label_button ?>
                    </button>
                  <?php endif; ?>

                  <?php if ($emel !== ''): ?>
                    <button class="btn btn-sm btn-copy-email" 
                            type="button"
                            aria-label="<?= h(tr('profile_btn_copy_email','Salin Emel')) ?>"
                            data-copy-value="<?= h($emel) ?>">
                      <i class="ri-clipboard-line me-1" aria-hidden="true"></i>
                      <?= h(tr('profile_btn_copy_email','Salin Emel')) ?>
                    </button>
                  <?php endif; ?>
                  
                  <!-- refresh button removed (redundant near copy buttons) -->
                </div>
              </div>
            </div>