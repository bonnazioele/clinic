@extends($profileLayout ?? 'layouts.app')
@section('content')
  <main class="doctor-layout-content">
    @yield('doctor-content')
  </main>

  <style>
    .doctor-layout-content {
      width: 100%;
      min-height: calc(100vh - 96px);
      padding: 24px 28px 36px;
      background:
        radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 28%),
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
        #f6f9fc;
    }

    @media (max-width: 768px) {
      .doctor-layout-content {
        padding: 18px 12px 28px;
      }
    }
  </style>
@endsection