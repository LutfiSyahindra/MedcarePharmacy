<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="branchForm">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Kode</label>
                        <input id="code" class="form-control" name="code" type="text">
                        <div class="invalid-feedback" id="error-code"></div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Branch</label>
                        <input id="name" class="form-control" name="name" type="name">
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <input id="address" class="form-control" name="address" type="address">
                        <div class="invalid-feedback" id="error-address"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">No Hp</label>
                        <input id="phone" class="form-control" name="phone" type="phone">
                        <div class="invalid-feedback" id="error-phone"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" class="form-control" name="email" type="email">
                        <div class="invalid-feedback" id="error-email"></div>
                    </div>
                    <input id="branchId" class="form-control" name="branchId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="submitForm" class="btn btn-primary"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
