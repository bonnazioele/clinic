@extends('layouts.app')

@section('title', 'Manage Appointments')

@section('content')
<div class="container py-4">
  @include('secretary.appointments._pane')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  initializeViewToggle();
});

function initializeViewToggle() {
  const tableView = document.getElementById('tableView');
  const cardView = document.getElementById('cardView');
  const tableViewContent = document.getElementById('tableViewContent');
  const cardViewContent = document.getElementById('cardViewContent');

  tableView.addEventListener('click', () => {
    tableViewContent.style.display = 'block';
    cardViewContent.style.display = 'none';
    tableView.classList.add('active');
    cardView.classList.remove('active');
  });

  cardView.addEventListener('click', () => {
    cardViewContent.style.display = 'block';
    tableViewContent.style.display = 'none';
    cardView.classList.add('active');
    tableView.classList.remove('active');
  });

  // Set default active state
  tableView.classList.add('active');
}

function exportAppointments() {
  // This would typically make an AJAX call to export appointments
  alert('Export functionality would be implemented here. This could export to CSV, PDF, or Excel format.');
}
</script>
@endpush
