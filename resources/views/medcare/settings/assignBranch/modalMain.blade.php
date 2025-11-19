<div class="modal fade" id="assignBranchModal" tabindex="-1" aria-labelledby="assignBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="assignBranchModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="assignBranchForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih User</label>
                        <select name="user_id[]" id="userSelect" class="js-example-basic-multiple form-select" multiple="multiple"
                            data-width="100%"></select>
                    </div>
                    <input id="branchId" class="form-control" name="branchId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="assignBranch" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
