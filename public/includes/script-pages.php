<script>
(function () {
  'use strict';

  const icaresDrafts = <?= json_encode($_SESSION['icares_form_drafts'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const config = {
    copyRateLimit: <?= (int)(PROFILE_CONFIG['COPY_RATE_LIMIT'] ?? 1000) ?>,
    toastDuration: <?= (int)(PROFILE_CONFIG['TOAST_DURATION'] ?? 1400) ?>
  };
  let lastCopyTime = 0;

  function toast(message, type) {
    let el = document.querySelector('.toast-lite');
    if (!el) {
      el = document.createElement('div');
      el.className = 'toast-lite';
      el.setAttribute('aria-live', 'polite');
      el.setAttribute('aria-atomic', 'true');
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.className = 'toast-lite toast-' + (type || 'info');
    el.classList.add('show');
    window.setTimeout(function () {
      el.classList.remove('show');
    }, config.toastDuration);
  }

  async function copyText(text) {
    if (!text) {
      toast(<?= json_encode(tr('profile_js_copy_empty', 'Tiada teks untuk disalin'), JSON_UNESCAPED_UNICODE) ?>, 'error');
      return;
    }
    const now = Date.now();
    if (now - lastCopyTime < config.copyRateLimit) {
      toast(<?= json_encode(tr('profile_js_copy_wait', 'Sila tunggu sebentar sebelum menyalin lagi'), JSON_UNESCAPED_UNICODE) ?>, 'warning');
      return;
    }
    lastCopyTime = now;

    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
      }
      toast(<?= json_encode(tr('profile_js_copied', 'Disalin'), JSON_UNESCAPED_UNICODE) ?>, 'success');
    } catch (error) {
      toast(<?= json_encode(tr('profile_js_copy_failed', 'Gagal menyalin teks'), JSON_UNESCAPED_UNICODE) ?>, 'error');
    }
  }

  function setFieldValue(field, value) {
    if (!field || field.disabled) return;
    const tag = field.tagName.toLowerCase();
    const type = (field.getAttribute('type') || '').toLowerCase();
    if (['file', 'password', 'submit', 'button', 'reset', 'hidden'].includes(type)) return;

    if (type === 'checkbox' || type === 'radio') {
      if (Array.isArray(value)) {
        field.checked = value.map(String).includes(String(field.value));
      } else {
        field.checked = String(field.value) === String(value) || String(value) === '1' || String(value).toLowerCase() === 'on';
      }
      return;
    }

    if (tag === 'select' && field.multiple && Array.isArray(value)) {
      Array.from(field.options).forEach(function (option) {
        option.selected = value.map(String).includes(String(option.value));
      });
      return;
    }

    field.value = Array.isArray(value) ? value.join(', ') : String(value ?? '');
  }

  function fieldNameSelector(name) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return '[name="' + window.CSS.escape(name) + '"]';
    }
    return '[name="' + String(name).replace(/\\/g, '\\\\').replace(/"/g, '\\"') + '"]';
  }

  function cssIdSelector(id) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return '#' + window.CSS.escape(id);
    }
    return '#' + String(id).replace(/([ !"#$%&'()*+,./:;<=>?@[\\\]^`{|}~])/g, '\\$1');
  }

  function tabHashFromForm(form) {
    const pane = form.closest('.tab-pane[id]');
    return pane && pane.id ? '#' + pane.id : (window.location.hash || '');
  }

  function shouldUseLocalSubmitFeedback() {
    const currentPath = String(window.location.pathname || '').replace(/\\/g, '/');
    return /\/pages\/(?:iStar|rekod-utama|iCareS)\//.test(currentPath);
  }

  function findRelatedSkeleton(form) {
    let sibling = form.previousElementSibling;
    while (sibling) {
      if (sibling.classList && sibling.classList.contains('skeleton-loader')) {
        return sibling;
      }
      sibling = sibling.previousElementSibling;
    }

    return form.parentElement ? form.parentElement.querySelector('.skeleton-loader') : null;
  }

  function resolveSubmitButton(form, submitEvent) {
    if (submitEvent && submitEvent.submitter instanceof HTMLElement) {
      return submitEvent.submitter;
    }

    return form.querySelector('button[type="submit"], input[type="submit"]');
  }

  function setLocalSubmitFeedback(form, submitEvent) {
    if (!shouldUseLocalSubmitFeedback()) return;
    if (form.dataset.localSubmitPending === '1') return;

    const method = String(form.getAttribute('method') || 'get').toLowerCase();
    const action = String(form.getAttribute('action') || '').trim();
    if (method !== 'post' || action === '' || action === '#') return;

    form.dataset.localSubmitPending = '1';
    form.setAttribute('aria-busy', 'true');

    const skeleton = findRelatedSkeleton(form);
    if (skeleton) {
      skeleton.style.display = 'block';
    }

    form.style.opacity = '0.55';
    form.style.pointerEvents = 'none';

    const submitButton = resolveSubmitButton(form, submitEvent);
    if (!submitButton) return;

    if (submitButton.tagName === 'BUTTON') {
      submitButton.dataset.originalHtml = submitButton.innerHTML;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' + <?= json_encode(tr('profile_js_processing', 'Sedang diproses...'), JSON_UNESCAPED_UNICODE) ?>;
    } else {
      submitButton.dataset.originalValue = submitButton.value;
      submitButton.value = <?= json_encode(tr('profile_js_processing', 'Sedang diproses...'), JSON_UNESCAPED_UNICODE) ?>;
    }

    submitButton.disabled = true;
  }

  function showTabFromHash() {
    const hash = window.location.hash || '';
    if (!hash || hash === '#') return;
    const paneId = decodeURIComponent(hash.slice(1));
    if (!paneId) return;

    const paneSelector = cssIdSelector(paneId);
    const trigger = document.querySelector('[data-bs-toggle="tab"][href="' + paneSelector + '"], [data-bs-toggle="tab"][data-bs-target="' + paneSelector + '"], [data-bs-toggle="pill"][href="' + paneSelector + '"], [data-bs-toggle="pill"][data-bs-target="' + paneSelector + '"]');
    if (!trigger) return;

    if (window.bootstrap && window.bootstrap.Tab) {
      window.bootstrap.Tab.getOrCreateInstance(trigger).show();
      return;
    }

    const nav = trigger.closest('.nav');
    if (nav) {
      nav.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]').forEach(function (item) {
        item.classList.toggle('active', item === trigger);
        item.setAttribute('aria-selected', item === trigger ? 'true' : 'false');
      });
    }
    document.querySelectorAll('.tab-pane').forEach(function (pane) {
      const active = pane.id === paneId;
      pane.classList.toggle('active', active);
      pane.classList.toggle('show', active);
    });
  }

  function bindTabHashSync() {
    document.querySelectorAll('[data-bs-toggle="tab"][href^="#"], [data-bs-toggle="tab"][data-bs-target^="#"], [data-bs-toggle="pill"][href^="#"], [data-bs-toggle="pill"][data-bs-target^="#"]').forEach(function (trigger) {
      trigger.addEventListener('shown.bs.tab', function (event) {
        const target = event.target.getAttribute('href') || event.target.getAttribute('data-bs-target') || '';
        if (!target || target === window.location.hash) return;
        window.history.replaceState(null, '', window.location.pathname + window.location.search + target);
      });

      trigger.addEventListener('click', function () {
        const target = trigger.getAttribute('href') || trigger.getAttribute('data-bs-target') || '';
        if (!target || target === window.location.hash) return;
        window.history.replaceState(null, '', window.location.pathname + window.location.search + target);
      });
    });
  }

  function applyIcaresDrafts() {
    if (!icaresDrafts || typeof icaresDrafts !== 'object') return;
    document.querySelectorAll('form').forEach(function (form) {
      if (!(form instanceof HTMLFormElement)) return;
      const marker = form.querySelector('input[name="icares_form"]');
      const formKey = marker ? marker.value : '';
      const draft = formKey ? icaresDrafts[formKey] : null;
      const values = draft && draft.values && typeof draft.values === 'object' ? draft.values : null;
      if (!values) return;

      Object.keys(values).forEach(function (name) {
        form.querySelectorAll(fieldNameSelector(name)).forEach(function (field) {
          setFieldValue(field, values[name]);
        });
      });
    });
  }

  document.addEventListener('click', function (event) {
    const button = event.target.closest('.btn-copy-staf, .btn-copy-email');
    if (!button) return;
    event.preventDefault();
    copyText(button.dataset.copyValue || '');
  });

  document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    setLocalSubmitFeedback(form, event);

    const action = form.getAttribute('action') || '';
    if (!/\/actions\/profile-update\.php(?:$|[?#])/.test(action)) return;

    let returnInput = form.querySelector('input[name="return_to"]');
    if (!returnInput) {
      returnInput = document.createElement('input');
      returnInput.type = 'hidden';
      returnInput.name = 'return_to';
      form.appendChild(returnInput);
    }
    returnInput.value = window.location.pathname + window.location.search + tabHashFromForm(form);

    if (window.csrfToken && !form.querySelector('input[name="csrf_token"]')) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = 'csrf_token';
      csrfInput.value = window.csrfToken;
      form.appendChild(csrfInput);
    }
  }, true);

  window.addEventListener('hashchange', showTabFromHash);

  function initPageEnhancements() {
    bindTabHashSync();
    showTabFromHash();
    applyIcaresDrafts();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageEnhancements);
  } else {
    initPageEnhancements();
  }
})();

function showLoading(messageKey = 'processing') {
  const message = msg_load[messageKey] || msg_load.processing; 
  
  hideLoading();
  if (window.AppLoader && typeof window.AppLoader.show === 'function') {
    window.__userListLoaderToken = window.AppLoader.show(message);
    return;
  }

  if (window.IQSLoader && typeof window.IQSLoader.show === 'function') {
    window.__userListLoaderToken = window.IQSLoader.show(message);
  }
}

function hideLoading() {
  if (!window.__userListLoaderToken) {
    return;
  }
  if (window.AppLoader && typeof window.AppLoader.hide === 'function') {
    window.AppLoader.hide(window.__userListLoaderToken);
  } else if (window.IQSLoader && typeof window.IQSLoader.hide === 'function') {
    window.IQSLoader.hide(window.__userListLoaderToken);
  }
  window.__userListLoaderToken = null;
}

function resolveLoadMessage(messageKey) {
    if (typeof msg_load !== 'undefined' && msg_load && msg_load[messageKey]) {
        return msg_load[messageKey];
    }

    if (typeof msg_load !== 'undefined' && msg_load && msg_load.processing) {
        return msg_load.processing;
    }

    return 'Sedang diproses...';
}

function renderInlineLoader(message) {
    return `
        <div class="konvo-inline-loader" role="status" aria-live="polite">
            <div class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></div>
            <span>${message}</span>
        </div>
    `;
}

function setSectionLoading(container, messageKey = 'loading') {
    if (!container) {
        return;
    }

    container.innerHTML = renderInlineLoader(resolveLoadMessage(messageKey));
}

function setButtonBusy(button, isBusy, messageKey = 'processing') {
    const btn = button && button.jquery ? button : jQuery(button);

    if (!btn.length) {
        return;
    }

    if (isBusy) {
        if (!btn.data('original-html')) {
            btn.data('original-html', btn.html());
        }

        btn.prop('disabled', true);
        btn.addClass('is-busy');
        btn.html(`
            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
            <span>${resolveLoadMessage(messageKey)}</span>
        `);
        return;
    }

    const originalHtml = btn.data('original-html');
    if (originalHtml) {
        btn.html(originalHtml);
        btn.removeData('original-html');
    }

    btn.prop('disabled', false);
    btn.removeClass('is-busy');
}

function showToast(message, type = 'success') {

    let bgClass = 'bg-success';

    if (type === 'error') {
        bgClass = 'bg-danger';
    }

    const toast = `

        <div class="toast align-items-center text-white ${bgClass} border-0 show mb-2">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast">
                </button>
            </div>
        </div>

    `;

    jQuery('.toast-lite').append(toast);

    setTimeout(() => {
        jQuery('.toast-lite .toast').first().remove();
    }, 2500);

}
</script>
