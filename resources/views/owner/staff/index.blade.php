@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<style>
  .owner-staff-page {
    width: 96%;
    max-width: 1380px;
    margin: 0 auto;
    padding: 1rem 0 2rem;
  }

  .staff-hero {
    border-radius: 30px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
  }

  .staff-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .staff-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .staff-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .staff-title {
    margin: 0;
    font-size: clamp(1.7rem, 3vw, 2.4rem);
    font-weight: 950;
    letter-spacing: -0.045em;
  }

  .staff-subtitle {
    margin: 0.35rem 0 0;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 650;
  }

  .staff-clinic-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border-radius: 999px;
    padding: 0.6rem 0.85rem;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-weight: 900;
  }

  .staff-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .staff-panel {
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .staff-panel-head {
    padding: 1rem 1.15rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: #0f172a;
    font-weight: 950;
  }

  .staff-panel-head span {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    color: #0d6efd;
    background: #eff6ff;
  }

  .staff-panel-body {
    padding: 1.15rem;
  }

  .staff-panel .form-select {
    min-height: 44px;
    border-radius: 14px;
    border-color: #dbe3ef;
    background-color: #f8fafc;
    font-weight: 650;
    box-shadow: none;
  }

  .staff-panel .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .staff-list {
    display: grid;
    gap: 0.65rem;
    margin-top: 1rem;
  }

  .staff-list-item {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    background: #ffffff;
  }

  .staff-person {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: #0f172a;
    font-weight: 850;
  }

  .staff-avatar {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    color: #0d6efd;
    background: #eff6ff;
  }

  .staff-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 18px;
    padding: 1rem;
    color: #64748b;
    font-weight: 650;
    text-align: center;
  }

  @media (max-width: 900px) {
    .staff-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="owner-staff-page">
  <section class="staff-hero">
    <div class="staff-hero-row">
      <div class="staff-title-wrap">
        <span class="staff-title-icon">
          <i class="bi bi-people"></i>
        </span>

        <div>
          <h1 class="staff-title">Staff Management</h1>
          <p class="staff-subtitle">Attach or detach doctors and secretaries assigned to your clinic.</p>
        </div>
      </div>

      <span class="staff-clinic-pill">
        <i class="bi bi-hospital"></i>
        {{ $clinic->name }}
      </span>
    </div>
  </section>

  <div class="staff-grid">
    <section class="staff-panel">
      <div class="staff-panel-head">
        <span><i class="bi bi-stethoscope"></i></span>
        Doctors
      </div>

      <div class="staff-panel-body">
        <form class="row g-2" method="POST" action="{{ route('owner.staff.attach') }}">
          @csrf
          <input type="hidden" name="role" value="doctor">
          <div class="col-8 col-sm-9">
            <select name="user_id" class="form-select">
              @foreach($doctorCandidates as $candidate)
                <option value="{{ $candidate->id }}">Dr. {{ $candidate->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-4 col-sm-3 d-grid">
            <button class="btn btn-primary" data-loading-text="Attaching...">Attach</button>
          </div>
        </form>

        <div class="staff-list">
          @forelse($doctors as $doc)
            <div class="staff-list-item">
              <div class="staff-person">
                <span class="staff-avatar"><i class="bi bi-person-badge"></i></span>
                <span>Dr. {{ $doc->name }}</span>
              </div>

              <form method="POST" action="{{ route('owner.staff.detach') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="role" value="doctor">
                <input type="hidden" name="user_id" value="{{ $doc->id }}">
                <button class="btn btn-sm btn-outline-danger" data-loading-text="Detaching...">
                  <i class="bi bi-x"></i>
                  Detach
                </button>
              </form>
            </div>
          @empty
            <div class="staff-empty">No doctors attached.</div>
          @endforelse
        </div>
      </div>
    </section>

    <section class="staff-panel">
      <div class="staff-panel-head">
        <span><i class="bi bi-clipboard2-pulse"></i></span>
        Secretaries
      </div>

      <div class="staff-panel-body">
        <form class="row g-2" method="POST" action="{{ route('owner.staff.attach') }}">
          @csrf
          <input type="hidden" name="role" value="secretary">
          <div class="col-8 col-sm-9">
            <select name="user_id" class="form-select">
              @foreach($secretaryCandidates as $candidate)
                <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-4 col-sm-3 d-grid">
            <button class="btn btn-primary" data-loading-text="Attaching...">Attach</button>
          </div>
        </form>

        <div class="staff-list">
          @forelse($secretaries as $sec)
            <div class="staff-list-item">
              <div class="staff-person">
                <span class="staff-avatar"><i class="bi bi-person-workspace"></i></span>
                <span>{{ $sec->name }}</span>
              </div>

              <form method="POST" action="{{ route('owner.staff.detach') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="role" value="secretary">
                <input type="hidden" name="user_id" value="{{ $sec->id }}">
                <button class="btn btn-sm btn-outline-danger" data-loading-text="Detaching...">
                  <i class="bi bi-x"></i>
                  Detach
                </button>
              </form>
            </div>
          @empty
            <div class="staff-empty">No secretaries attached.</div>
          @endforelse
        </div>
      </div>
    </section>
  </div>
</div>
@endsection
