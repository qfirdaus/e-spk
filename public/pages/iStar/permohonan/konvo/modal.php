<div class="modal fade sample-modal sample-modal--add" id="sampleAddModal" tabindex="-1" aria-hidden="true" aria-labelledby="sampleAddModalTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sampleAddModalTitle">
          <i class="ri-add-circle-line"></i> <?= h(tr('template_senarai_crud_modal_add_title', 'Add Record')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(tr('template_senarai_crud_btn_close', 'Close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form class="sample-form-shell" id="sampleAddForm">
          <div>
            <label for="sampleAddName" class="form-label"><?= h(tr('template_senarai_crud_field_name', 'Name')) ?></label>
            <input type="text" class="form-control" id="sampleAddName" value="">
          </div>
          <div>
            <label for="sampleAddDepartment" class="form-label"><?= h(tr('template_senarai_crud_field_department', 'Department')) ?></label>
            <input type="text" class="form-control" id="sampleAddDepartment" value="">
          </div>
          <div>
            <label for="sampleAddGroup" class="form-label"><?= h(tr('template_senarai_crud_field_group', 'Group')) ?></label>
            <input type="text" class="form-control" id="sampleAddGroup" value="">
          </div>
          <div>
            <label for="sampleAddAccess" class="form-label"><?= h(tr('template_senarai_crud_field_access', 'Access')) ?></label>
            <select class="form-select" id="sampleAddAccess">
              <option value="1"><?= h(tr('template_senarai_crud_access_allowed', 'Allowed')) ?></option>
              <option value="0"><?= h(tr('template_senarai_crud_access_blocked', 'Blocked')) ?></option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= h(tr('template_senarai_crud_btn_cancel', 'Cancel')) ?></button>
        <button type="button" class="btn btn-success" id="sampleAddSaveBtn"><?= h(tr('template_senarai_crud_btn_save', 'Save')) ?></button>
      </div>
    </div>
  </div>
</div>