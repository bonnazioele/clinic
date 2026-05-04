@php
  $authUser = auth()->user();

  $latestNotifications = $authUser
      ? $authUser->notifications()->latest()->take(15)->get()
      : collect();

  $unreadNotificationsCount = $authUser
      ? $authUser->unreadNotifications()->count()
      : 0;

  $markReadUrl = Route::has('notifications.markRead')
      ? route('notifications.markRead')
      : null;
@endphp

<style>
  .cliniq-notification-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    z-index: 999999;
  }

  .cliniq-notification-btn {
    position: relative;
    z-index: 999999;
  }

  .cliniq-notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    background: #ef4444;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    box-shadow: 0 8px 18px rgba(239, 68, 68, 0.28);
  }

  .cliniq-notification-panel {
    position: fixed;
    top: 82px;
    right: 24px;
    width: 370px;
    max-width: calc(100vw - 32px);
    background: rgba(255, 255, 255, 0.97);
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: 24px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    z-index: 999999;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px) scale(0.98);
    transform-origin: top right;
    transition: 0.18s ease;
  }

  .cliniq-notification-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
  }

  .cliniq-notification-header {
    padding: 18px 18px 14px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .cliniq-notification-header h6 {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
  }

  .cliniq-notification-header span {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
  }

  .cliniq-notification-list {
    max-height: 390px;
    overflow-y: auto;
    padding: 8px;
  }

  .cliniq-notification-item {
    display: flex;
    gap: 12px;
    padding: 13px;
    border-radius: 18px;
    text-decoration: none;
    color: inherit;
    transition: 0.18s ease;
    border: 1px solid transparent;
  }

  .cliniq-notification-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
  }

  .cliniq-notification-item.unread {
    background: #eff6ff;
    border-color: #bfdbfe;
  }

  .cliniq-notification-icon {
    width: 42px;
    height: 42px;
    border-radius: 15px;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 10px 22px rgba(13, 110, 253, 0.20);
  }

  .cliniq-notification-content {
    min-width: 0;
    flex: 1;
  }

  .cliniq-notification-title {
    margin: 0;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 900;
    line-height: 1.25;
  }

  .cliniq-notification-message {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .cliniq-notification-time {
    display: block;
    margin-top: 7px;
    color: #94a3b8;
    font-size: 0.72rem;
    font-weight: 800;
  }

  .cliniq-notification-empty {
    padding: 34px 24px;
    text-align: center;
  }

  .cliniq-notification-empty-icon {
    width: 58px;
    height: 58px;
    margin: 0 auto 12px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .cliniq-notification-empty h6 {
    margin: 0;
    color: #0f172a;
    font-weight: 900;
  }

  .cliniq-notification-empty p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 0.83rem;
    font-weight: 600;
  }

  .cliniq-notification-footer {
    padding: 12px;
    border-top: 1px solid #e2e8f0;
    background: rgba(248, 250, 252, 0.92);
  }

  .cliniq-show-all-notifications-btn {
    width: 100%;
    border: 0;
    border-radius: 16px;
    padding: 12px 14px;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 0.85rem;
    font-weight: 900;
    transition: 0.18s ease;
  }

  .cliniq-show-all-notifications-btn:hover {
    background: #dbeafe;
    color: #0b5ed7;
  }

  .cliniq-show-all-notifications-btn:disabled {
    opacity: 0.75;
    cursor: default;
  }

  @media (max-width: 576px) {
    .cliniq-notification-panel {
      top: 78px;
      right: 14px;
      width: calc(100vw - 28px);
    }
  }
</style>

<div class="cliniq-notification-wrap" data-cliniq-notification>
  <button
    type="button"
    class="navbar-icon-btn cliniq-notification-btn"
    aria-label="Notifications"
    data-cliniq-notification-toggle
    data-mark-read-url="{{ $markReadUrl }}"
  >
    <i class="bi bi-bell"></i>

    @if($unreadNotificationsCount > 0)
      <span class="cliniq-notification-badge" data-cliniq-notification-badge>
        {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
      </span>
    @endif
  </button>

  <div class="cliniq-notification-panel" data-cliniq-notification-panel>
    <div class="cliniq-notification-header">
      <div>
        <h6>Notifications</h6>
        <span>Latest updates</span>
      </div>

      @if($unreadNotificationsCount > 0)
        <span data-cliniq-unread-label>{{ $unreadNotificationsCount }} unread</span>
      @else
        <span data-cliniq-unread-label>All caught up</span>
      @endif
    </div>

    <div class="cliniq-notification-list">
      @forelse($latestNotifications as $index => $notification)
        @php
          $notificationData = $notification->data ?? [];

          $notificationTitle =
              $notificationData['title']
              ?? $notificationData['subject']
              ?? 'New notification';

          $notificationMessage =
              $notificationData['message']
              ?? $notificationData['body']
              ?? $notificationData['text']
              ?? 'You have a new update.';

          $notificationUrl =
              $notificationData['url']
              ?? $notificationData['link']
              ?? null;

          $notificationIcon =
              $notificationData['icon']
              ?? 'bi-info-circle';

          $isUnread = is_null($notification->read_at);

          $extraClass = $index >= 5 ? 'cliniq-extra-notification d-none' : '';
        @endphp

        @if($notificationUrl)
          <a href="{{ $notificationUrl }}"
             class="cliniq-notification-item {{ $isUnread ? 'unread' : '' }} {{ $extraClass }}">
            <span class="cliniq-notification-icon">
              <i class="bi {{ $notificationIcon }}"></i>
            </span>

            <span class="cliniq-notification-content">
              <p class="cliniq-notification-title">{{ $notificationTitle }}</p>
              <p class="cliniq-notification-message">{{ $notificationMessage }}</p>
              <span class="cliniq-notification-time">
                {{ optional($notification->created_at)->diffForHumans() }}
              </span>
            </span>
          </a>
        @else
          <div class="cliniq-notification-item {{ $isUnread ? 'unread' : '' }} {{ $extraClass }}">
            <span class="cliniq-notification-icon">
              <i class="bi {{ $notificationIcon }}"></i>
            </span>

            <span class="cliniq-notification-content">
              <p class="cliniq-notification-title">{{ $notificationTitle }}</p>
              <p class="cliniq-notification-message">{{ $notificationMessage }}</p>
              <span class="cliniq-notification-time">
                {{ optional($notification->created_at)->diffForHumans() }}
              </span>
            </span>
          </div>
        @endif
      @empty
        <div class="cliniq-notification-empty">
          <div class="cliniq-notification-empty-icon">
            <i class="bi bi-bell-slash"></i>
          </div>
          <h6>No notifications</h6>
          <p>Your latest updates will appear here.</p>
        </div>
      @endforelse
    </div>

    @if($latestNotifications->count() > 5)
      <div class="cliniq-notification-footer">
        <button type="button" class="cliniq-show-all-notifications-btn" data-expanded="false">
  Show all notifications
</button>
      </div>
    @endif
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.__cliniqNotificationBellLoaded) {
      return;
    }

    window.__cliniqNotificationBellLoaded = true;

    document.addEventListener('click', function (event) {
      
      const showAllBtn = event.target.closest('.cliniq-show-all-notifications-btn');

if (showAllBtn) {
  event.preventDefault();
  event.stopPropagation();

  const root = showAllBtn.closest('[data-cliniq-notification]');
  const list = root.querySelector('.cliniq-notification-list');
  const isExpanded = showAllBtn.dataset.expanded === 'true';

  if (isExpanded) {
    root.querySelectorAll('.cliniq-extra-notification').forEach(function (item) {
      item.classList.add('d-none');
    });

    if (list) {
      list.style.maxHeight = '390px';
      list.scrollTop = 0;
    }

    showAllBtn.textContent = 'Show all notifications';
    showAllBtn.dataset.expanded = 'false';

    return;
  }

  root.querySelectorAll('.cliniq-extra-notification').forEach(function (item) {
    item.classList.remove('d-none');
  });

  if (list) {
    list.style.maxHeight = '520px';
    list.scrollTop = 0;
  }

  showAllBtn.textContent = 'Show less';
  showAllBtn.dataset.expanded = 'true';

  return;
}

      const toggle = event.target.closest('[data-cliniq-notification-toggle]');
      const wrapper = event.target.closest('[data-cliniq-notification]');

      document.querySelectorAll('[data-cliniq-notification-panel]').forEach(function (panel) {
        const panelWrapper = panel.closest('[data-cliniq-notification]');

        if (!wrapper || wrapper !== panelWrapper) {
          panel.classList.remove('show');
        }
      });

      if (!toggle) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      const root = toggle.closest('[data-cliniq-notification]');
      const panel = root.querySelector('[data-cliniq-notification-panel]');
      const badge = root.querySelector('[data-cliniq-notification-badge]');
      const unreadLabel = root.querySelector('[data-cliniq-unread-label]');
      const markReadUrl = toggle.dataset.markReadUrl;

      panel.classList.toggle('show');

      if (panel.classList.contains('show') && markReadUrl) {
        fetch(markReadUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        })
        .then(function () {
          if (badge) {
            badge.remove();
          }

          if (unreadLabel) {
            unreadLabel.textContent = 'All caught up';
          }

          root.querySelectorAll('.cliniq-notification-item.unread').forEach(function (item) {
            item.classList.remove('unread');
          });
        })
        .catch(function () {
          // Dropdown should still work even if mark-as-read fails.
        });
      }
    });
  });
</script>