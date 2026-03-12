/**
 * Menu Access Management untuk kumpulan-pengguna.php
 * Handle modal akses menu dengan DataTable dan editor
 */

const MenuAccess = {
  // DOM elements
  modalEl: null,
  subEl: null,
  loadEl: null,
  errEl: null,
  cntEl: null,
  editModalEl: null,
  editErrorEl: null,
  
  // Translations
  T: null,

  attachMultiSelectToggle(selectEl) {
    if (!selectEl || selectEl.dataset.multiToggleBound === '1') return;
    selectEl.dataset.multiToggleBound = '1';
    selectEl.addEventListener('mousedown', function(e) {
      const opt = e.target && e.target.tagName === 'OPTION' ? e.target : null;
      if (!opt) return;
      e.preventDefault();
      opt.selected = !opt.selected;
      // Fire change so dependent menu list refreshes
      selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    });
  },
  
  // Helper untuk query edit modal
  $ME(sel) {
    return this.editModalEl ? this.editModalEl.querySelector(sel) : null;
  },
  
  init(translations) {
    this.T = translations;
    this.modalEl = document.getElementById('aksesMenuModal');
    this.subEl = document.getElementById('aksesMenuSub');
    this.loadEl = document.getElementById('menuLoading');
    this.errEl = document.getElementById('menuError');
    this.cntEl = document.getElementById('menuContent');
    this.editModalEl = document.getElementById('menuEditModal');
    this.editErrorEl = document.getElementById('menuEditError');

    const colorPicker = document.getElementById('gc_color_picker');
    const colorInput = document.getElementById('gc_color');
    if (colorPicker && colorInput) {
      const syncColor = (v) => { colorInput.value = (v || '').trim(); };
      syncColor(colorPicker.value || '#50a4c1');
      colorPicker.addEventListener('input', () => syncColor(colorPicker.value));
    }

    this.attachMultiSelectToggle(document.getElementById('gc_moduls'));
    this.attachMultiSelectToggle(document.getElementById('gc_menus'));
    
    // View menu button handler - removed to avoid conflict with main file handler
    // Handler is now in main file (kumpulan-pengguna.php)
    
    // Save button handler
    document.getElementById('menuEditSaveBtn')?.addEventListener('click', () => {
      this.handleSave();
    });
    // Group create modal save handler (global page)
    document.getElementById('groupCreateSaveBtn')?.addEventListener('click', async (e) => {
      e.preventDefault();
      const errEl = document.getElementById('groupCreateError');
      if (errEl) errEl.classList.add('d-none');
      const groupID = parseInt(document.getElementById('gc_groupID')?.value || '0', 10) || 0;
      const payload = {
        groupID,
        groupKod: (document.getElementById('gc_groupKod')?.value || '').trim(),
        groupName: (document.getElementById('gc_groupName')?.value || '').trim(),
        priority: parseInt(document.getElementById('gc_priority')?.value || '0', 10) || 0,
        mod: parseInt(document.getElementById('gc_mod')?.value || '0', 10) || 0,
        color: (document.getElementById('gc_color')?.value || '').trim(),
        modulAccess: Array.from(document.getElementById('gc_moduls')?.selectedOptions || []).map(o => o.value).filter(Boolean),
        menuAccess: Array.from(document.getElementById('gc_menus')?.selectedOptions || []).map(o => o.value).filter(Boolean)
      };
      if (!payload.groupKod || !payload.groupName) {
        if (errEl) { errEl.textContent = this.T.err_group_code_name_required || 'Sila isi Kod & Nama Kumpulan.'; errEl.classList.remove('d-none'); }
        return;
      }

      // Export to global so page scripts can call populateCreateModal()
      try { window.MenuAccess = MenuAccess; } catch (e) { /* ignore */ }
      try {
        const resp = await fetch(GroupUtils.apiUrl('group-create.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': GroupUtils.getCSRF() },
          body: JSON.stringify(payload)
        });
        const j = await resp.json();
        if (!j || j.error) throw new Error(j && j.message ? j.message : 'Gagal simpan');

        // Always reload after save (create/update) so table state/ordering stays correct.
        // This also guarantees latest module/menu access indicators are rendered from DB.
        location.reload();
        return;

        // Close modal and reset form
        const modalEl = document.getElementById('groupCreateModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
        document.getElementById('groupCreateForm')?.reset();
        if (document.getElementById('gc_groupID')) document.getElementById('gc_groupID').value = '';
        if (document.getElementById('gc_color_picker')) document.getElementById('gc_color_picker').value = '#50a4c1';
        if (document.getElementById('gc_color')) document.getElementById('gc_color').value = '#50a4c1';
        // clear selects
        try { document.getElementById('gc_moduls').innerHTML = ''; document.getElementById('gc_menus').innerHTML = ''; } catch (_) {}

      } catch (err) {
        if (errEl) { errEl.textContent = err.message || 'Ralat rangkaian'; errEl.classList.remove('d-none'); }
      }
    });

    // Edit group metadata (reuse create modal)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-edit-group-meta');
      if (!btn) return;
      e.preventDefault();

      const modalEl = document.getElementById('groupCreateModal');
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      const titleEl = document.getElementById('groupCreateTitle');
      if (titleEl) titleEl.innerHTML = '<i class="ri-pencil-line"></i> <span>' + (this.T.modal_group_edit_title || 'Edit Kumpulan') + '</span>';
      const saveTxt = document.getElementById('groupCreateSaveBtnText');
      if (saveTxt) saveTxt.textContent = this.T.btn_update || 'Kemaskini';

      const gid = btn.getAttribute('data-group-id') || '';
      const kod = btn.getAttribute('data-group-kod') || '';
      const nama = btn.getAttribute('data-group-nama') || '';
      const prio = btn.getAttribute('data-group-priority') || '0';
      const mod = btn.getAttribute('data-group-mod') || '0';
      const color = btn.getAttribute('data-group-color') || '#50a4c1';

      if (document.getElementById('gc_groupID')) document.getElementById('gc_groupID').value = gid;
      if (document.getElementById('gc_groupKod')) document.getElementById('gc_groupKod').value = kod;
      if (document.getElementById('gc_groupName')) document.getElementById('gc_groupName').value = nama;
      if (document.getElementById('gc_priority')) document.getElementById('gc_priority').value = prio;
      if (document.getElementById('gc_mod')) document.getElementById('gc_mod').value = mod;
      if (document.getElementById('gc_color_picker')) document.getElementById('gc_color_picker').value = color;
      if (document.getElementById('gc_color')) document.getElementById('gc_color').value = color;

      try {
        if (window.MenuAccess && typeof window.MenuAccess.populateCreateModal === 'function') {
          await window.MenuAccess.populateCreateModal();
        }
        // Prefill existing module/menu selections for edit mode.
        const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('group-perms-get.php', { groupID: gid }));
        const toList = (v) => Array.isArray(v) ? v.map(String) : String(v || '').split(',').map(s => s.trim()).filter(Boolean);
        const modulIDs = toList(j.modulIDs ?? j.f_modulAccess ?? []);
        const menuIDs = toList(j.menuIDs ?? j.f_menuAccess ?? []);
        const selMod = document.getElementById('gc_moduls');
        if (selMod) {
          Array.from(selMod.options).forEach(o => { o.selected = modulIDs.includes(String(o.value)); });
          await this.populateMenusForModules(menuIDs);
        }
      } catch (_) {}
      modal.show();
    });

    // Delete group handler
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-delete-group');
      if (!btn) return;
      e.preventDefault();

      const gid = parseInt(btn.getAttribute('data-group-id') || '0', 10) || 0;
      const gnam = btn.getAttribute('data-group-nama') || 'Kumpulan';
      if (gid <= 0) return;

      const ask = await Swal.fire({
        icon: 'warning',
        title: this.T.confirm_title || 'Pengesahan',
        text: (this.T.confirm_delete_group_text || 'Padam kumpulan "{name}"?').replace('{name}', gnam),
        showCancelButton: true,
        confirmButtonText: this.T.confirm_yes_delete || 'Ya, Padam',
        cancelButtonText: this.T.confirm_cancel || 'Batal',
      });
      if (!ask.isConfirmed) return;

      try {
        const resp = await fetch(GroupUtils.apiUrl('group-delete.php'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': GroupUtils.getCSRF(),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ groupID: gid })
        });
        const j = await resp.json();
        if (!resp.ok || !j || j.error) {
          const msg = (j && j.message) ? j.message : 'Gagal memadam kumpulan.';
          await Swal.fire({
            icon: 'error',
            title: 'Tidak Dibenarkan',
            text: msg,
          });
          return;
        }

        await Swal.fire({
          icon: 'success',
          title: 'Berjaya',
          text: 'Kumpulan berjaya dipadam.',
          timer: 1400,
          showConfirmButton: false,
        });
        location.reload();
      } catch (err) {
        await Swal.fire({
          icon: 'error',
          title: 'Tidak Dibenarkan',
          text: err && err.message ? err.message : 'Ralat rangkaian semasa memadam kumpulan.',
        });
      }
    });
    // Ensure modal UI matches current mode when shown
    if (this.editModalEl) {
      this.editModalEl.addEventListener('show.bs.modal', () => {
        const mode = this.editModalEl.dataset.mode || 'edit';
        this.updateEditModalUI(mode);
      });
    }
  },

  updateEditModalUI(mode) {
    try {
      const titleEl = document.getElementById('menuEditTitleText');
      const btnTextEl = document.getElementById('menuEditSaveBtnText');
      if (titleEl) {
        if (mode === 'create') titleEl.textContent = titleEl.dataset.titleCreate || 'Tambah Menu';
        else titleEl.textContent = titleEl.dataset.titleEdit || 'Kemaskini Menu';
      }
      if (btnTextEl) {
        if (mode === 'create') btnTextEl.textContent = 'Simpan';
        else btnTextEl.textContent = 'Kemaskini';
      }
    } catch (e) { /* silent */ }
  },
  
  showLoading() {
    this.loadEl?.classList.remove('d-none');
    this.errEl?.classList.add('d-none');
    this.cntEl?.classList.add('d-none');
    this.cntEl.innerHTML = '';
  },
  
  showError(msg) {
    this.loadEl?.classList.add('d-none');
    if (this.errEl) {
      this.errEl.textContent = msg || this.T.error_unknown;
      this.errEl.classList.remove('d-none');
    }
  },
  
  showContent(html) {
    this.loadEl?.classList.add('d-none');
    this.errEl?.classList.add('d-none');
    if (this.cntEl) {
      if (html != null) {
        // Use safe assign to avoid executing injected scripts or on* handlers.
        try {
          if (window.DOMPurify && typeof DOMPurify.sanitize === 'function') {
            this.cntEl.innerHTML = DOMPurify.sanitize(html);
          } else {
            const doc = new DOMParser().parseFromString('<div>' + html + '</div>', 'text/html');
            doc.querySelectorAll('script').forEach(s => s.remove());
            doc.querySelectorAll('*').forEach(n => {
              Array.from(n.attributes).forEach(a => {
                if (/^on/i.test(a.name)) n.removeAttribute(a.name);
                if ((a.name === 'src' || a.name === 'href') && /^javascript:/i.test(a.value)) n.removeAttribute(a.name);
              });
            });
            this.cntEl.innerHTML = doc.body.firstChild ? doc.body.firstChild.innerHTML : '';
          }
        } catch (e) {
          this.cntEl.innerHTML = html;
        }
      }
      this.cntEl.classList.remove('d-none');
    }
  },
  
  // Parse menu helper
  parseMenu(me) {
    const id = parseInt(me.id ?? me.f_menuID, 10);
    const modulID = parseInt(me.modulID ?? me.f_modulID, 10);
    const hasFlag = Object.prototype.hasOwnProperty.call(me, 'flag') ||
                    Object.prototype.hasOwnProperty.call(me, 'f_flag') ||
                    Object.prototype.hasOwnProperty.call(me, 'active') ||
                    Object.prototype.hasOwnProperty.call(me, 'is_active');
    const rawFlag = hasFlag ? (me.flag ?? me.f_flag ?? me.active ?? me.is_active) : 1;
    const __asOn = (v) => v === 1 || v === '1' || v === true || v === 'true' || v === 'on';
    
    return {
      id,
      modulID,
      name: String(me.nama || me.menuName || me.kod || '-'),
      path: String(me.path || me.f_path || ''),
      flag: __asOn(rawFlag) ? 1 : 0
    };
  },
  
  // Fetch all menus
  async fetchAllMenusStrict() {
    try {
      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-list.php', { all: 1 }));
      const raw = Array.isArray(j?.menus) ? j.menus : (Array.isArray(j?.data) ? j.data : []);
      if (raw.length) {
        return raw.map(m => this.parseMenu(m)).filter(x => Number.isInteger(x.id) && Number.isInteger(x.modulID));
      }
    } catch (_) { /* fallback */ }
    
    // Fallback: loop setiap modul
    let modulIDs = [];
    try {
      const ml = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('modul-list.php'));
      const arr = Array.isArray(ml?.moduls) ? ml.moduls : (Array.isArray(ml) ? ml : []);
      modulIDs = arr.map(m => parseInt(m.id ?? m.f_modulID, 10)).filter(Number.isInteger);
    } catch (_) {}
    
    const all = [];
    for (const mid of modulIDs) {
      try {
        const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-list.php', { modulID: mid }));
        const raw = Array.isArray(j?.menus) ? j.menus : (Array.isArray(j?.data) ? j.data : j);
        (raw || []).forEach(r => all.push(this.parseMenu(r)));
      } catch (_) {}
    }
    return all.filter(x => Number.isInteger(x.id) && Number.isInteger(x.modulID));
  },

  // Populate module/menu selects in the group create modal
  async populateCreateModal() {
    try {
      // populate modules
      const errEl = document.getElementById('groupCreateError');
      if (errEl) errEl.classList.add('d-none');
      const selMod = document.getElementById('gc_moduls');
      if (selMod) {
        selMod.innerHTML = '<option value="">Memuat…</option>';
      }

      let ml;
      const url = GroupUtils.apiUrl('modul-list.php');
      // small timeout wrapper so a hung request surfaces to user
      const fetchWithTimeout = (p, ms = 7000) => Promise.race([
        p,
        new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), ms))
      ]);
      try {
        ml = await fetchWithTimeout(GroupUtils.fetchJSONSafe(url), 7000);
      } catch (e) {
        console.error('modul-list fetch failed', e, url);
        if (errEl) { errEl.textContent = 'Gagal memuat modul dari: ' + url + ' — ' + (e.message || 'Ralat'); errEl.classList.remove('d-none'); }
        if (selMod) selMod.innerHTML = '';
        return;
      }

      const arr = Array.isArray(ml?.moduls) ? ml.moduls : (Array.isArray(ml) ? ml : []);
      if (!arr.length) {
        if (errEl) { errEl.textContent = 'Tiada modul ditemui.'; errEl.classList.remove('d-none'); }
        if (selMod) selMod.innerHTML = '';
        return;
      }

      if (selMod) {
        selMod.innerHTML = '';
        arr.forEach(function(m) {
          var idVal = (m.id !== undefined && m.id !== null) ? m.id : (m.f_modulID || '');
          var id = String(idVal);
          var name = String(m.nama || m.modulName || id);
          var opt = document.createElement('option');
          opt.value = id; opt.textContent = name; selMod.appendChild(opt);
        });
        // attach change listener to populate menus on selection
        try {
          if (typeof selMod.removeEventListener === 'function') selMod.removeEventListener('change', MenuAccess.populateMenusForModules);
        } catch (e) { /* ignore */ }
        selMod.addEventListener('change', function() { MenuAccess.populateMenusForModules().catch(function(){/*ignore*/}); });
        this.attachMultiSelectToggle(selMod);
      }
      // initial populate menus (none selected → empty)
      await MenuAccess.populateMenusForModules();
    } catch (e) {
      // ignore populate errors
      console.warn('populateCreateModal error', e);
    }
  },

  async populateMenusForModules(preselectedMenuIds = []) {
    try {
      const selMod = document.getElementById('gc_moduls');
      const selMenu = document.getElementById('gc_menus');
      if (!selMenu) return;
      selMenu.innerHTML = '';
      this.attachMultiSelectToggle(selMenu);
      const selected = Array.from(selMod?.selectedOptions || []).map(o => o.value).filter(Boolean);
      if (!selected.length) return;
      const seen = new Set();
      for (const mid of selected) {
        try {
          const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-list.php', { modulID: mid }));
          const raw = Array.isArray(j?.menus) ? j.menus : (Array.isArray(j?.data) ? j.data : (Array.isArray(j) ? j : []));
          for (const m of raw) {
            const id = String(m.id ?? m.f_menuID ?? m.f_menuID);
            if (!id || seen.has(id)) continue;
            seen.add(id);
            const name = String(m.nama || m.menuName || m.f_path || ('Menu ' + id));
            const opt = document.createElement('option'); opt.value = id; opt.textContent = name; selMenu.appendChild(opt);
          }
        } catch (e) { /* ignore per-module errors */ }
      }
      if (preselectedMenuIds && preselectedMenuIds.length) {
        const selectedSet = new Set(preselectedMenuIds.map(String));
        Array.from(selMenu.options).forEach(o => { o.selected = selectedSet.has(String(o.value)); });
      }
    } catch (e) {
      console.warn('populateMenusForModules error', e);
    }
  },
  
  buildMenuTable(rows) {
    const html =
      '<table class="table table-striped table-bordered align-middle w-100" id="menuDT">' +
      '<thead class="table-light"><tr>' +
      '<th style="width:260px">' + GroupUtils.esc(this.T.field_modul || 'Modul') + '</th>' +
      '<th>' + GroupUtils.esc(this.T.col_menu || 'Menu') + '</th>' +
      '<th class="text-center col-status" style="width:200px">' + GroupUtils.esc(this.T.col_status || 'Status') + '</th>' +
      '<th class="text-center col-actions" style="width:170px">' + GroupUtils.esc(this.T.col_actions || 'Tindakan') + '</th>' +
      '</tr></thead><tbody></tbody>' +
      '</table>';

    this.showContent(html);

    const tbody = this.cntEl.querySelector('#menuDT tbody');
    if (rows.length) {
      tbody.innerHTML = rows.map(r => {
        const onId = 'flag_on_' + GroupUtils.esc(r.menuID);
        const offId = 'flag_off_' + GroupUtils.esc(r.menuID);
        const isOn = (parseInt(r.flag, 10) === 1);
        return '' +
          '<tr data-modul-id="' + GroupUtils.esc(r.modulID) + '" data-menu-id="' + GroupUtils.esc(r.menuID) + '">' +
          '<td>' + GroupUtils.esc(r.modulName) + '</td>' +
          '<td><div class="fw-semibold">' + GroupUtils.esc(r.menuName) + '</div>' + (r.path ? '<div class="menu-path">' + GroupUtils.esc(r.path) + '</div>' : '') + '</td>' +
          '<td class="text-center col-status">' +
          '<input type="radio" class="btn-check menu-flag" name="flag-' + GroupUtils.esc(r.menuID) + '" id="' + onId + '" value="1" ' + (isOn ? 'checked' : '') + '>' +
          '<label class="btn btn-outline-success btn-sm me-1" for="' + onId + '"><i class="ri-toggle-line"></i> ' + GroupUtils.esc(this.T.status_on || 'ON') + '</label>' +
          '<input type="radio" class="btn-check menu-flag" name="flag-' + GroupUtils.esc(r.menuID) + '" id="' + offId + '" value="0" ' + (!isOn ? 'checked' : '') + '>' +
          '<label class="btn btn-outline-secondary btn-sm" for="' + offId + '"><i class="ri-toggle-fill"></i> ' + GroupUtils.esc(this.T.status_off || 'OFF') + '</label>' +
          '</td>' +
          '<td class="text-center col-actions">' +
          '<button class="btn btn-sm btn-outline-secondary btn-edit-menu" title="' + GroupUtils.esc(this.T.edit || 'Edit') + '"><i class="ri-pencil-line"></i></button> ' +
          '<button class="btn btn-sm btn-outline-danger btn-del-menu" title="Padam"><i class="ri-delete-bin-line"></i></button>' +
          '</td>' +
          '</tr>';
      }).join('');
    } else {
      tbody.innerHTML = '';
    }

    const dt = GroupState.getMenuDataTable();
    if (dt) {
      try { dt.destroy(); } catch (e) {}
      GroupState.setMenuDataTable(null);
    }

    if (GroupUtils.hasDataTable()) {
      const table = jQuery('#menuDT').DataTable({
        pageLength: 5,
        lengthChange: false,
        ordering: true,
        order: [[0, 'asc'], [1, 'asc']],
        columnDefs: [
          { targets: 2, orderable: false, searchable: false, className: 'text-center' },
          { targets: 3, orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: 'frt' + '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
        language: {
          search: "",
          searchPlaceholder: "Cari…",
          emptyTable: this.T.no_records || 'Tiada rekod'
        },
        initComplete: () => {
          const $w = jQuery('#menuDT_wrapper');
          const $filter = $w.find('div.dataTables_filter');
          $filter.find('label').contents().filter(function () { return this.nodeType === 3; }).remove();
          const $input = $filter.find('input').addClass('form-control').attr('placeholder', 'Cari…');

          const $bar = jQuery('<div class="dt-topbar"></div>');
          const $left = jQuery('<div class="left"></div>');
          const $right = jQuery('<div class="right"></div>');

          // Add Menu button removed from modal (use page-level button instead)
          $right.append($input);
          $bar.append($left).append($right);

          $w.prepend($bar);
          $filter.remove();

          // (removed) modal-level add menu handler
        }
      });
      GroupState.setMenuDataTable(table);
    }

    // Event handlers
    jQuery('#menuDT').off('click', '.btn-edit-menu').on('click', '.btn-edit-menu', (e) => {
      e.preventDefault();
      const tr = e.currentTarget.closest('tr');
      if (!tr) return;
      this.openEditMenu(tr.getAttribute('data-menu-id'));
    });
    
    jQuery('#menuDT').off('click', '.btn-del-menu').on('click', '.btn-del-menu', (e) => {
      e.preventDefault();
      const tr = e.currentTarget.closest('tr');
      if (!tr) return;
      this.deleteMenu(tr.getAttribute('data-menu-id'), tr);
    });

    jQuery('#menuDT').off('change', '.menu-flag').on('change', '.menu-flag', async function () {
      const input = this;
      const tr = input.closest('tr');
      if (!tr) return;
      const menuId = tr.getAttribute('data-menu-id');
      const flagVal = input.value === '1' ? 1 : 0;
      try {
        const resp = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-flag-toggle.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': GroupUtils.getCSRF() },
          body: JSON.stringify({ menuID: menuId, flag: flagVal })
        });
        if (!resp || resp.error) throw new Error((resp && resp.message) || 'Gagal kemas kini status.');
      } catch (e) {
        const name = 'flag-' + menuId;
        MenuAccess.cntEl.querySelectorAll('input[name="' + name + '"]').forEach(el => {
          if (el !== input) el.checked = !input.checked;
        });
        MenuAccess.showError(e.message || 'Ralat rangkaian');
        setTimeout(() => { MenuAccess.errEl.classList.add('d-none'); }, 2500);
      }
    });
  },
  
  async openMenuEditor(groupID) {
    this.showLoading();
    try {
      // Get modul map
      let modulMap = {};
      try {
        const ml = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('modul-list.php'));
        const arr = Array.isArray(ml?.moduls) ? ml.moduls : (Array.isArray(ml) ? ml : []);
        modulMap = Object.fromEntries(
          arr.map(m => {
            const id = parseInt(m.id ?? m.f_modulID, 10);
            const nm = String(m.nama || m.modulName || ('Modul ' + (m.id || m.f_modulID)));
            return [id, nm];
          })
        );
      } catch (_) {}

      const allMenus = await this.fetchAllMenusStrict();
      const rows = allMenus.map(m => ({
        modulID: m.modulID,
        modulName: modulMap[m.modulID] || ('Modul ' + m.modulID),
        menuID: m.id,
        menuName: m.name,
        path: m.path,
        flag: m.flag
      }));

      rows.sort((a, b) => (a.modulID - b.modulID) || String(a.menuName).localeCompare(String(b.menuName)));
      this.buildMenuTable(rows);
    } catch (e) {
      this.showError(e.message || this.T.error_network);
    }
  },
  
  handleAddMenu() {
    const modal = GroupUtils.getModal(this.editModalEl);
    if (!modal) return;

    const gidFromCtx = GroupState.getMenuGroupID();
    const gidFromHidden = (() => {
      const el = document.getElementById('em_groupID');
      const v = el ? parseInt((el.value || '0'), 10) : 0;
      return Number.isFinite(v) && v > 0 ? v : null;
    })();
    const gidFromBtn = (() => {
      const btn = GroupState.getLastMenuBtn() || document.querySelector('.view-menu[data-group-id]');
      const v = btn ? parseInt(btn.getAttribute('data-group-id') || '0', 10) : 0;
      return Number.isFinite(v) && v > 0 ? v : null;
    })();

    const resolvedGroupID = gidFromCtx || gidFromHidden || gidFromBtn || null;

      if (!resolvedGroupID) {
        if (window.Swal && typeof Swal.fire === 'function') {
          Swal.fire({
            icon: 'warning',
            title: this.T.info_title || 'Makluman',
            text: this.T.info_select_group_first || 'Sila pilih kumpulan dahulu melalui butang Akses Menu.',
            confirmButtonText: 'OK'
          });
        } else {
          alert(this.T.info_select_group_first || 'Sila pilih kumpulan dahulu melalui butang Akses Menu.');
        }
        return;
      }
    GroupState.setMenuGroupID(resolvedGroupID);

    const hidEl = document.getElementById('em_groupID');
    const infoEl = document.getElementById('em_groupInfo');
    if (hidEl) hidEl.value = String(resolvedGroupID);
    if (infoEl) {
      const src = GroupState.getLastMenuBtn() || document.querySelector('.view-menu[data-group-kod]');
      const gkod = src?.getAttribute('data-group-kod') || '';
      const gnam = src?.getAttribute('data-group-nama') || '';
      infoEl.textContent = (gkod + (gnam ? ' — ' + gnam : '')).trim();
    }

    this.editErrorEl.classList.add('d-none');
    this.$ME('#em_menuID').value = '';
    this.$ME('#em_path').value = '';
    this.$ME('#em_name_ms').value = '';
    this.$ME('#em_name_en').value = '';
    this.$ME('#em_flag_on').checked = true;

    this.populateModuls(null).then(() => {
      this.editModalEl.dataset.mode = 'create';
      this.updateEditModalUI('create');
      modal.show();
    });
  },
  
  async openEditMenu(menuID) {
    const modal = GroupUtils.getModal(this.editModalEl);
    if (!modal) return;
    this.editErrorEl.classList.add('d-none');
    try {
      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-get.php', { menuID }));
      if (!j || j.error) {
        this.showError((j && j.message) || this.T.error_get_menu);
        return;
      }
      this.$ME('#em_menuID').value = j.menu.f_menuID;
      this.$ME('#em_path').value = j.menu.f_path || '';
      this.$ME('#em_name_ms').value = j.menu.f_menuName_ms || '';
      this.$ME('#em_name_en').value = j.menu.f_menuName_en || '';
      (parseInt(j.menu.f_flag ?? 0, 10) === 1 ? (this.$ME('#em_flag_on').checked = true) : (this.$ME('#em_flag_off').checked = true));
      await this.populateModuls(j.menu.f_modulID);
      this.editModalEl.dataset.mode = 'edit';
      this.updateEditModalUI('edit');
      modal.show();
    } catch (e) {
      this.showError(e.message || this.T.error_network);
    }
  },
  
  async handleSave() {
    const modal = GroupUtils.getModal(this.editModalEl);
    const mode = this.editModalEl.dataset.mode || 'edit';
    this.editErrorEl.classList.add('d-none');

    const gidFromCtx = GroupState.getMenuGroupID();
    const gidFromHidden = Number.parseInt(document.getElementById('em_groupID')?.value || '0', 10) || 0;
    const gidFromBtn = (() => {
      const btn = GroupState.getLastMenuBtn() || document.querySelector('.view-menu[data-group-id]');
      const v = btn ? parseInt(btn.getAttribute('data-group-id') || '0', 10) : 0;
      return Number.isFinite(v) && v > 0 ? v : 0;
    })();
    const groupID = gidFromCtx || gidFromHidden || gidFromBtn || 0;

    const payload = {
      groupID,
      menuID: parseInt((this.$ME('#em_menuID')?.value || '0'), 10),
      modulID: parseInt((this.$ME('#em_modulID')?.value || '0'), 10),
      path: (this.$ME('#em_path')?.value || '').trim(),
      name_ms: this.$ME('#em_name_ms')?.value || '',
      name_en: this.$ME('#em_name_en')?.value || '',
      flag: this.$ME('#em_flag_on')?.checked ? 1 : 0,
      position: document.getElementById('em_position')?.value || 'bottom'
    };

    if (!payload.path) {
      this.editErrorEl.textContent = this.T.err_path_required;
      this.editErrorEl.classList.remove('d-none');
      return;
    }
    if (payload.modulID <= 0) {
      this.editErrorEl.textContent = this.T.err_modul_required;
      this.editErrorEl.classList.remove('d-none');
      return;
    }
    if (!groupID) {
      this.editErrorEl.textContent = 'Sila pilih Kumpulan, Modul dan isi Path.';
      this.editErrorEl.classList.remove('d-none');
      return;
    }

    try {
      const target = (mode === 'create') ? 'menu-create.php' : 'menu-save.php';
      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl(target, { groupID }), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': GroupUtils.getCSRF() },
        body: JSON.stringify(payload)
      });

      if (!j || j.error) {
        this.editErrorEl.textContent = (j && j.message) || (mode === 'create' ? this.T.err_add_menu : this.T.err_save_menu);
        this.editErrorEl.classList.remove('d-none');
        return;
      }
      if (modal) modal.hide();
      this.openMenuEditor(groupID);
    } catch (e) {
      this.editErrorEl.textContent = e.message || this.T.error_network;
      this.editErrorEl.classList.remove('d-none');
    }
  },
  
  async deleteMenu(menuID, tr) {
    const menuName = (() => {
      try {
        if (!tr) return '';
        const el = tr.querySelector('td:nth-child(2) .fw-semibold');
        return (el?.textContent || '').trim();
      } catch (_) { return ''; }
    })();
    const prettyName = menuName || `ID ${menuID}`;

    async function askConfirm() {
      if (window.Swal && typeof Swal.fire === 'function') {
        const res = await Swal.fire({
          icon: 'warning',
          title: `Padam menu "${GroupUtils.esc(prettyName)}"?`,
          html: `
            <div class="text-start">
              <p class="mb-2">Menu <strong>${GroupUtils.esc(prettyName)}</strong> akan <u>dipadam</u>.</p>
              <ul class="mb-0">
                <li>Menu ini juga akan dibersihkan daripada <em>semua kumpulan</em> yang rujuk ID ini.</li>
                <li>Tindakan ini tidak boleh diundur.</li>
              </ul>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Ya, padam',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          focusCancel: true
        });
        return res.isConfirmed;
      }
      if (window.swal && typeof window.swal === 'function') {
        return await new Promise(resolve => {
          window.swal({
            title: `Padam menu "${prettyName}"?`,
            text: 'Menu ini juga akan dibersihkan daripada semua kumpulan.',
            icon: 'warning',
            buttons: ['Batal', 'Ya, padam'],
            dangerMode: true
          }).then(val => resolve(!!val));
        });
      }
      return confirm(`Padam menu "${prettyName}"? Menu juga akan dibersihkan daripada semua kumpulan.`);
    }

    const ok = await askConfirm();
    if (!ok) return;

    let delBtn;
    if (tr) {
      delBtn = tr.querySelector('.btn-del-menu');
      if (delBtn) delBtn.disabled = true;
    }

    try {
      const payload = {
        menuID: Number(menuID),
        groupID: Number(GroupState.getMenuGroupID() || 0),
        hard: 1
      };

      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-delete.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': GroupUtils.getCSRF(),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      if (!j || j.error) throw new Error((j && j.message) || 'Gagal memadam.');

      const dt = GroupState.getMenuDataTable();
      if (window.jQuery && dt && tr) {
        dt.row(jQuery(tr)).remove().draw(false);
      } else if (tr && tr.parentNode) {
        tr.parentNode.removeChild(tr);
      } else {
        this.openMenuEditor(GroupState.getMenuGroupID());
      }

      if (window.Swal && Swal.fire) {
        Swal.fire({
          icon: 'success',
          title: 'Dipadam',
          text: `Menu "${prettyName}" dibersihkan dari semua kumpulan.`,
          timer: 1400,
          showConfirmButton: false
        });
      }
    } catch (e) {
      if (window.Swal && Swal.fire) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: e.message || 'Ralat rangkaian',
          confirmButtonText: 'OK'
        });
      } else {
        alert(e.message || 'Ralat rangkaian');
      }
    } finally {
      if (delBtn) delBtn.disabled = false;
    }
  },
  
  async populateModuls(selected) {
    const sel = this.$ME('#em_modulID');
    if (!sel) return;
    sel.innerHTML = '<option value="">' + GroupUtils.esc(this.T.loading_modules || 'Memuatkan modul...') + '</option>';
    let options = [];
    try {
      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('modul-list.php'));
      const arr = Array.isArray(j?.moduls) ? j.moduls : (Array.isArray(j) ? j : []);
      options = arr.map(m => ({
        id: parseInt(m.id ?? m.f_modulID, 10),
        nama: String(m.nama || m.modulName || ('Modul ' + (m.id || m.f_modulID)))
      })).filter(x => Number.isInteger(x.id) && x.id > 0);
    } catch (_) {}
    sel.innerHTML = '';
    options.forEach(m => {
      const opt = document.createElement('option');
      opt.value = String(m.id);
      opt.textContent = m.nama;
      if (selected && parseInt(selected, 10) === m.id) opt.selected = true;
      sel.appendChild(opt);
    });
  },
  
  openMenuFromBtn(btn) {
    const el = document.getElementById('aksesMenuModal');
    const gid = btn.getAttribute('data-group-id');
    const gkod = btn.getAttribute('data-group-kod') || '';
    const gnam = btn.getAttribute('data-group-nama') || '';

    GroupState.setMenuGroupID(gid);
    if (this.subEl) this.subEl.textContent = gkod + (gnam ? ' — ' + gnam : '');

    const hidEl = document.getElementById('em_groupID');
    const infoEl = document.getElementById('em_groupInfo');
    if (hidEl) hidEl.value = String(GroupState.getMenuGroupID() || '');
    if (infoEl) infoEl.textContent = (gkod + (gnam ? ' — ' + gnam : '')).trim();

    if (window.bootstrap?.Modal && el) {
      GroupUtils.ensureInBody(el);
      window.bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, focus: true, keyboard: true }).show();
    } else if (el) {
      el.style.display = 'block';
      el.classList.add('show');
      el.removeAttribute('aria-hidden');
      el.setAttribute('aria-modal', 'true');
      el.setAttribute('role', 'dialog');
    } else {
      console.error('aksesMenuModal tidak ditemui');
      return;
    }

    this.openMenuEditor(gid);
  }
};

window.MenuAccess = MenuAccess;
