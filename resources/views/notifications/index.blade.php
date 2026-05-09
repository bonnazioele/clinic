@extends('layouts.app')

@section('title','Notifications')

@section('content')
<style>
  .notifications-page {
    width: 96%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 1rem 0 2rem;
  }

  .notifications-hero,
  .notifications-panel {
    border: 1px solid rgba(226, 232, 240, 0.96);
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .notifications-hero {
    padding: 1.35rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    margin-bottom: 1rem;
  }

  .notifications-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .notifications-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .notifications-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.5rem;
  }

  .notifications-title {
    margin: 0;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .notifications-subtitle {
    margin: 0.3rem 0 0;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 650;
  }

  .notifications-hero .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .notifications-list {
    display: grid;
    gap: 0.75rem;
    padding: 1rem;
  }

  .notification-item {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 0.9rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    background: #ffffff;
  }

  .notification-item.unread {
    border-color: #bfdbfe;
    background: #eff6ff;
  }

  .notification-dot {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    color: #0d6efd;
    background: #eff6ff;
    flex: 0 0 40px;
  }

  .notification-message {
    color: #0f172a;
    font-weight: 850;
  }

  .notification-time {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 650;
  }

  .notifications-empty {
    padding: 2.5rem 1rem;
    text-align: center;
    color: #64748b;
    font-weight: 650;
  }
</style>

<div class="notifications-page">
  <section class="notifications-hero">
    <div class="notifications-hero-row">
      <div class="notifications-title-wrap">
        <span class="notifications-icon">
          <i class="bi bi-bell"></i>
        </span>

        <div>
          <h1 class="notifications-title h3">Notifications</h1>
          <p class="notifications-subtitle">Review appointment, queue, and clinic updates.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('notifications.markRead') }}">
        @csrf
        <button class="btn btn-light text-primary" data-loading-text="Updating...">
          <i class="bi bi-check2-all me-1"></i>
          Mark all as read
        </button>
      </form>
    </div>
  </section>

  <section class="notifications-panel">
    @if($all->isEmpty())
      <div class="notifications-empty">
        <i class="bi bi-bell-slash display-5 d-block mb-3"></i>
        No notifications.
      </div>
    @else
      <div class="notifications-list">
        @foreach($all as $note)
          <article class="notification-item {{ $note->read_at ? '' : 'unread' }}">
            <span class="notification-dot">
              <i class="bi {{ $note->read_at ? 'bi-bell' : 'bi-bell-fill' }}"></i>
            </span>

            <div>
              <div class="notification-message">{{ $note->data['message'] }}</div>
              <div class="notification-time">{{ $note->created_at->diffForHumans() }}</div>
            </div>
          </article>
        @endforeach
      </div>

      <div class="px-3 pb-3">
        {{ $all->links() }}
      </div>
    @endif
  </section>
</div>
@endsection
