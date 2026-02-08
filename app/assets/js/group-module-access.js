/**
 * Module Access Management untuk kumpulan-pengguna.php
 * Handle modal akses modul dengan reorder functionality
 */

const ModuleAccess = {
  // DOM elements
  modalEl: null,
  subEl: null,
  loadEl: null,
  errEl: null,
  cntEl: null,
  searchEl: null,
  
  // Translations
  T: null,
  
  init(translations) {
    this.T = translations;
    this.modalEl = document.getElementById('aksesModal');
    this.subEl = document.getElementById('aksesModalSub');
    this.loadEl = document.getElementById('aksesLoading');
    this.errEl = document.getElementById('aksesError');
    this.cntEl = document.getElementById('aksesContent');
    this.searchEl = document.getElementById('aksesSearch');
    
    // Auto-refresh menu bila modal ditutup jika ada perubahan order
    this.modalEl?.addEventListener('hidden.bs.modal', () => {
      if (!GroupState.isMenuOrderDirty()) return;
      GroupState.setMenuOrderDirty(false);
      MenuRefresh.refreshMainMenu().catch(console.warn);
    });
    
    // Search functionality
    if (this.searchEl) {
      this.searchEl.addEventListener('input', () => {
        const q = (this.searchEl.value || '').toLowerCase().trim();
        const acc = this.cntEl.querySelector('#modulAccordion');
        if (!acc) return;
        acc.querySelectorAll('.accordion-item').forEach(it => {
          const txt = it.textContent.toLowerCase();
          it.style.display = (q === '' || txt.indexOf(q) !== -1) ? '' : 'none';
        });
      });
    }
    
    // Reorder buttons (global delegation)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('#aksesContent .btn-move-up, #aksesContent .btn-move-down');
      if (!btn) return;
      e.preventDefault();
      await this.handleReorder(btn);
    }, { capture: true });
    
    // View access button handler - delegated to main file
  },
  
  showLoading() {
    this.loadEl?.classList.remove('d-none');
    this.errEl?.classList.add('d-none');
    this.cntEl?.classList.add('d-none');
    this.cntEl.innerHTML = '';
    if (this.searchEl) this.searchEl.value = '';
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
      this.cntEl.innerHTML = html;
      this.cntEl.classList.remove('d-none');
    }
  },
  
  renderAccess(data) {
    const modules = Array.isArray(data.modules) ? data.modules : [];
    const totals = data.totals || {};
    const modulCt = totals.modulCt ?? modules.length;
    const menuCt = totals.menuCt ?? (modules.reduce((n, m) => n + (m.menus ? m.menus.length : 0), 0));

    let html = '';
    html += '<div class="d-flex justify-content-between align-items-center mb-2">';
    html += '<div><span class="badge bg-primary-subtle text-primary modul-badge">' + modulCt + ' ' + GroupUtils.esc(this.T.label_module) + '</span> ';
    html += '<span class="badge bg-success-subtle text-success modul-badge">' + menuCt + ' ' + GroupUtils.esc(this.T.label_menu) + '</span></div>';
    html += '</div>';

    if (!modules.length) return html + '<div class="text-muted">' + GroupUtils.esc(this.T.no_records) + '</div>';

    html += '<div class="accordion" id="modulAccordion">';
    modules.forEach((m, i) => {
      const mid = 'mod' + i;
      const menus = Array.isArray(m.menus) ? m.menus : [];
      html += '<div class="accordion-item">';
      html += '<h2 class="accordion-header" id="h_' + mid + '">';
      html += '<button class="accordion-button collapsed acc-toggle" type="button" data-target="#c_' + mid + '" aria-expanded="false" aria-controls="c_' + mid + '">';
      html += '<div class="d-flex flex-column w-100">';
      html += '<div><strong>' + GroupUtils.esc(m.nama || m.modulName || this.T.modul_fallback) + '</strong> <span class="badge bg-secondary-subtle text-secondary modul-badge ms-2">' + menus.length + ' ' + GroupUtils.esc(this.T.label_menu) + '</span></div>';
      html += '</div>';
      html += '</button>';
      html += '</h2>';
      html += '<div id="c_' + mid + '" class="accordion-collapse collapse" aria-labelledby="h_' + mid + '">';
      html += '<div class="accordion-body" data-modul-id="' + GroupUtils.esc(m.id || m.f_modulID || '') + '">';
      if (!menus.length) {
        html += '<div class="text-muted small">' + GroupUtils.esc(this.T.no_records) + '</div>';
      } else {
        html += '<div class="row fw-semibold text-body-secondary mb-2"><div class="col">' + GroupUtils.esc(this.T.col_menu) + '</div><div class="col-auto">' + GroupUtils.esc(this.T.col_reorder) + '</div></div>';
        menus.forEach(me => {
          const id = me.id ?? me.f_menuID;
          html += '<div class="menu-row" data-menu-id="' + GroupUtils.esc(id) + '">';
          html += '<div>';
          html += '<div>' + GroupUtils.esc(me.nama || me.menuName || me.kod || '-') + '</div>';
          if (me.path || me.f_path) html += '<div class="menu-path">' + GroupUtils.esc(me.path || me.f_path) + '</div>';
          html += '</div>';
          html += '<div class="btn-group reorder-group" role="group" aria-label="Reorder">';
          html += '<button type="button" class="btn btn-outline-primary btn-sm btn-move-up" title="' + GroupUtils.esc(this.T.move_up) + '"><i class="ri-arrow-up-line"></i></button>';
          html += '<button type="button" class="btn btn-outline-primary btn-sm btn-move-down" title="' + GroupUtils.esc(this.T.move_down) + '"><i class="ri-arrow-down-line"></i></button>';
          html += '</div>';
          html += '</div>';
        });
      }
      html += '</div>';
      html += '</div>';
      html += '</div>';
    });
    html += '</div>';
    return html;
  },
  
  wireAccordionToggles(container) {
    const root = container || this.cntEl;
    if (!root) return;
    root.querySelectorAll('.accordion-collapse').forEach(el => {
      const M = window.bootstrap && bootstrap.Collapse ? bootstrap.Collapse : null;
      if (!M) return;
      M.getOrCreateInstance(el, { toggle: false });
    });
    root.querySelectorAll('.acc-toggle').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const sel = this.getAttribute('data-target');
        const panel = root.querySelector(sel);
        if (!panel) return;
        const M = window.bootstrap && bootstrap.Collapse ? bootstrap.Collapse : null;
        if (!M) return;
        const inst = M.getOrCreateInstance(panel, { toggle: false });
        if (panel.classList.contains('show')) {
          inst.hide();
          this.classList.add('collapsed');
          this.setAttribute('aria-expanded', 'false');
        } else {
          inst.show();
          this.classList.remove('collapsed');
          this.setAttribute('aria-expanded', 'true');
        }
      });
    });
  },
  
  refreshReorderButtons(bodyEl) {
    const rows = Array.from(bodyEl.querySelectorAll('.menu-row'));
    rows.forEach((r, idx) => {
      const up = r.querySelector('.btn-move-up');
      const dn = r.querySelector('.btn-move-down');
      if (up) up.disabled = (idx === 0);
      if (dn) dn.disabled = (idx === rows.length - 1);
    });
  },
  
  async handleReorder(btn) {
    const row = btn.closest('.menu-row');
    const body = btn.closest('.accordion-body');
    if (!row || !body) return;

    if (btn.classList.contains('btn-move-up')) {
      let prev = row.previousElementSibling;
      while (prev && !prev.classList.contains('menu-row')) prev = prev.previousElementSibling;
      if (!prev) return;
      row.parentNode.insertBefore(row, prev);
      await this.saveSwap(body, row, prev, () => {
        prev.parentNode.insertBefore(prev, row);
      });
    } else {
      let next = row.nextElementSibling;
      while (next && !next.classList.contains('menu-row')) next = next.nextElementSibling;
      if (!next) return;
      next.parentNode.insertBefore(next, row);
      await this.saveSwap(body, row, next, () => {
        row.parentNode.insertBefore(row, next);
      });
    }
  },
  
  async saveSwap(bodyEl, rowA, rowB, revert) {
    const modulID = bodyEl.getAttribute('data-modul-id');
    const aID = rowA.getAttribute('data-menu-id');
    const bID = rowB.getAttribute('data-menu-id');
    rowA.classList.add('saving');
    rowB.classList.add('saving');
    try {
      const resp = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('menu-swap.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': GroupUtils.getCSRF() },
        body: JSON.stringify({ modulID, aID, bID })
      });
      if (!resp || resp.error) {
        if (typeof revert === 'function') revert();
        this.showError((resp && resp.message) || this.T.error_reorder);
        setTimeout(() => this.errEl?.classList.add('d-none'), 2500);
        return;
      }
      this.refreshReorderButtons(bodyEl);
      GroupState.setMenuOrderDirty(true);
      await MenuRefresh.refreshMainMenu();
    } catch (e) {
      if (typeof revert === 'function') revert();
      this.showError(e.message || this.T.error_network);
      setTimeout(() => this.errEl?.classList.add('d-none'), 2500);
    } finally {
      rowA.classList.remove('saving');
      rowB.classList.remove('saving');
    }
  },
  
  async openAccess(btn) {
    const modal = GroupUtils.getModal(this.modalEl);
    if (!modal) return;
    const gid = btn.getAttribute('data-group-id');
    const gkod = btn.getAttribute('data-group-kod') || '';
    const gnam = btn.getAttribute('data-group-nama') || '';
    if (this.subEl) this.subEl.textContent = gkod + (gnam ? ' — ' + gnam : '');
    this.showLoading();
    modal.show();
    try {
      const j = await GroupUtils.fetchJSONSafe(GroupUtils.apiUrl('group-access.php', { groupID: gid }));
      if (!j || j.error) {
        this.showError((j && j.message) || this.T.error_load_access);
        return;
      }
      this.showContent(this.renderAccess(j));
      this.wireAccordionToggles(this.cntEl);
      this.cntEl.querySelectorAll('.accordion-body[data-modul-id]').forEach(body => {
        this.refreshReorderButtons(body);
      });
    } catch (e) {
      this.showError(e.message || this.T.error_network);
    }
  }
};

window.ModuleAccess = ModuleAccess;

