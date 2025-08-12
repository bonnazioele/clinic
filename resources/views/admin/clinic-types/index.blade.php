@extends('admin.layouts.app')

@section('title', 'Clinic Types')

@section('content')
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1">
        <i class="bi bi-building me-2 text-primary"></i>Clinic Types
      </h2>
      <p class="text-muted mb-0">Manage different categories of healthcare facilities</p>
    </div>
    <a href="{{ route('admin.clinic-types.create') }}" class="btn btn-primary rounded-pill">
      <i class="bi bi-plus-circle me-2"></i> Add Clinic Type
    </a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      @if($clinicTypes->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr class="bg-light">
                <th class="border-0 px-4 py-3 fw-semibold">ID</th>
                <th class="border-0 px-4 py-3 fw-semibold">Type Name</th>
                <th class="border-0 px-4 py-3 fw-semibold">Description</th>
                <th class="border-0 px-4 py-3 fw-semibold">Clinics Count</th>
                <th class="border-0 px-4 py-3 fw-semibold">Created</th>
                <th class="border-0 px-4 py-3 fw-semibold">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($clinicTypes as $clinicType)
                <tr>
                  <td class="px-4 py-3">{{ $clinicType->id }}</td>
                  <td class="px-4 py-3">
                    <h6 class="fw-semibold text-dark mb-0">{{ $clinicType->type_name }}</h6>
                  </td>
                  <td class="px-4 py-3">
                    @if($clinicType->description)
                      {{ Str::limit($clinicType->description, 50) }}
                    @else
                      <span class="text-muted">No description</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <span class="badge bg-info rounded-pill">{{ $clinicType->clinics()->count() }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-muted">{{ $clinicType->created_at->format('M d, Y') }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.clinic-types.edit', $clinicType) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        @if($clinicType->clinics()->count() > 0)
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" disabled
                                    title="Cannot delete clinic type that is being used by {{ $clinicType->clinics()->count() }} clinic(s)">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteClinicTypeModal"
                                data-clinic-type-id="{{ $clinicType->id }}"
                                data-clinic-type-name="{{ $clinicType->type_name }}">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $clinicTypes->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="bi bi-building display-4 text-muted mb-3"></i>
          <h5 class="text-muted fw-semibold mb-2">No clinic types found</h5>
          <p class="text-muted mb-3">Start by adding your first clinic type.</p>
          <a href="{{ route('admin.clinic-types.create') }}" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-circle me-2"></i> Add First Clinic Type
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Delete Modal -->
  <x-delete-modal
    id="deleteClinicTypeModal"
    title="Delete Clinic Type"
    route=""
    itemName=""
    deleteText="Delete Clinic Type"
    cancelText="Cancel">
    This action cannot be undone. Are you sure you want to delete this clinic type?
  </x-delete-modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteClinicTypeModal');
    const deleteForm = deleteModal.querySelector('form');
    const modalTitle = deleteModal.querySelector('.modal-title');
    const modalBody = deleteModal.querySelector('.modal-body');

    // Handle delete button clicks
    document.querySelectorAll('[data-bs-target="#deleteClinicTypeModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const clinicTypeId = this.getAttribute('data-clinic-type-id');
            const clinicTypeName = this.getAttribute('data-clinic-type-name');
            const deleteUrl = "{{ route('admin.clinic-types.destroy', ':id') }}".replace(':id', clinicTypeId);

            // Update form action
            deleteForm.action = deleteUrl;

            // Update modal content
            modalBody.innerHTML = `This action cannot be undone. Are you sure you want to delete <strong>${clinicTypeName}</strong>?`;
        });
    });
});
</script>
@endpush
