<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'CliniQ')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    :root {
      --cliniq-blue: #0d6efd;
      --cliniq-blue-2: #178bff;
      --cliniq-bg: #f6faff;
      --cliniq-text: #0f172a;
      --cliniq-muted: #64748b;
      --cliniq-card: #ffffff;
      --cliniq-border: #e2e8f0;
      --cliniq-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
      --cliniq-radius: 24px;
    }

    * {
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.13), transparent 25%),
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 22%),
        linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
      color: var(--cliniq-text);
    }

    a {
      text-decoration: none;
    }

    .patient-page {
      min-height: 100vh;
    }

    .patient-main {
      width: 100%;
      padding: 22px 22px 36px;
    }

    .dashboard-container,
    .patient-content-container {
      width: 100%;
      max-width: 1540px;
      margin: 0 auto;
    }

    .page-card,
    .dash-card {
      background: rgba(255, 255, 255, 0.94);
      border: 1px solid rgba(226, 232, 240, 0.96);
      border-radius: var(--cliniq-radius);
      box-shadow: var(--cliniq-shadow);
      padding: 24px;
    }

    .page-card {
      margin-top: 16px;
    }

    .welcome-card,
    .welcome-panel,
    .welcome-banner {
      border-radius: 24px;
      padding: 30px 34px;
      color: #ffffff;
      background:
        radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.15), transparent 18%),
        linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
      overflow: hidden;
      position: relative;
      margin-bottom: 24px;
    }

    .welcome-card h1,
    .welcome-panel h1,
    .welcome-banner h1 {
      font-size: clamp(26px, 3vw, 36px);
      font-weight: 900;
      letter-spacing: -0.04em;
      margin: 0 0 8px;
    }

    .welcome-card p,
    .welcome-panel p,
    .welcome-banner p {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
      opacity: 0.95;
    }

    .stat-card {
      min-height: 170px;
      border-radius: 20px;
      padding: 24px;
      color: #ffffff;
      position: relative;
      overflow: hidden;
      box-shadow: var(--cliniq-shadow);
      border: 0;
    }

    .stat-card::after {
      content: "";
      position: absolute;
      right: -25px;
      bottom: -25px;
      width: 130px;
      height: 130px;
      border-radius: 40px;
      background: rgba(255, 255, 255, 0.13);
      transform: rotate(3deg);
    }

    .stat-blue {
      background: linear-gradient(135deg, #0866f2, #2993ff);
    }

    .stat-green {
      background: linear-gradient(135deg, #087b3d, #2bbf6a);
    }

    .stat-yellow {
      background: linear-gradient(135deg, #ffd85a, #ffc107);
      color: #162033;
    }

    .stat-cyan {
      background: linear-gradient(135deg, #a9efff, #d9f8ff);
      color: #123047;
    }

    .stat-content {
      display: flex;
      align-items: flex-start;
      gap: 18px;
      position: relative;
      z-index: 2;
    }

    .stat-icon {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, 0.18);
      font-size: 30px;
      flex: 0 0 auto;
    }

    .stat-value {
      font-size: 38px;
      font-weight: 900;
      line-height: 1;
      margin-bottom: 8px;
      letter-spacing: -0.04em;
    }

    .stat-title {
      font-size: 16px;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .stat-sub {
      opacity: 0.92;
      margin: 0;
      font-weight: 500;
    }

    .dash-card-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 20px;
    }

    .dash-card-title h2,
    .dash-card-title h3 {
      font-size: 24px;
      font-weight: 900;
      letter-spacing: -0.04em;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .title-icon {
      color: var(--cliniq-blue);
    }

    .appointment-row {
      display: grid;
      grid-template-columns: 86px 1fr auto auto;
      gap: 22px;
      align-items: center;
      padding: 20px 8px;
      border-top: 1px solid var(--cliniq-border);
    }

    .appointment-row:first-child {
      border-top: 0;
    }

    .date-box {
      width: 78px;
      height: 92px;
      border-radius: 16px;
      background: linear-gradient(180deg, var(--cliniq-blue), #238dff);
      color: #ffffff;
      display: grid;
      place-items: center;
      text-align: center;
      box-shadow: 0 12px 24px rgba(13, 110, 253, 0.25);
    }

    .date-box .month {
      font-weight: 900;
      font-size: 15px;
    }

    .date-box .day {
      font-weight: 900;
      font-size: 28px;
      line-height: 1;
    }

    .date-box .weekday {
      font-size: 14px;
    }

    .appointment-name {
      font-size: 18px;
      font-weight: 900;
      margin-bottom: 8px;
    }

    .appointment-meta {
      color: #536178;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 6px;
      font-weight: 500;
    }

    .status-pill {
      border-radius: 999px;
      padding: 8px 14px;
      background: #eef5ff;
      color: var(--cliniq-blue);
      font-weight: 700;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: fit-content;
    }

    .empty-box {
      text-align: center;
      padding: 50px 20px;
      color: var(--cliniq-muted);
    }

    .empty-box i {
      font-size: 58px;
      display: block;
      margin-bottom: 14px;
      color: #64748b;
    }

    .info-card {
      background: linear-gradient(135deg, #e8fbff, #f5ffff);
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin: 18px 0 24px;
    }

    .activity-list {
      display: flex;
      flex-direction: column;
    }

    .activity-item {
      display: flex;
      gap: 14px;
      padding: 15px 0;
      border-bottom: 1px solid var(--cliniq-border);
    }

    .activity-item:last-child {
      border-bottom: 0;
    }

    .activity-icon {
      color: var(--cliniq-blue);
      font-size: 20px;
      margin-top: 2px;
      flex: 0 0 auto;
    }

    .dashboard-pair-card {
      height: 100%;
      min-height: 642px;
      display: flex;
      flex-direction: column;
    }

    .dashboard-pair-card .dash-card-title {
      margin-bottom: 0;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--cliniq-border);
    }

    .recent-activity-card .activity-list,
    .past-appointments-card .past-appointments-list {
      flex: 1;
    }

    .recent-activity-card .activity-item {
      padding: 18px 0;
      min-height: 78px;
    }

    .past-appointments-card .appointment-row {
      grid-template-columns: 86px 1fr auto;
      gap: 22px;
      padding: 20px 8px;
      margin-bottom: 0 !important;
      min-height: 124px;
    }

    .past-appointments-card .status-pill {
      justify-self: end;
      align-self: center;
      background: #eef5ff;
      padding: 8px 14px;
    }

    .past-appointments-card .history-link-wrap {
      margin-top: auto;
      padding-top: 14px;
      text-align: center;
    }

    .compact .stat-card.mini,
    .stat-card.mini {
      padding: 16px;
      min-height: 110px;
    }

    .compact .stat-value,
    .stat-card.mini .stat-value {
      font-size: 24px;
      margin-bottom: 3px;
    }

    .dash-card.compact {
      padding: 18px;
    }

    .appointment-row.compact {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-top: 1px solid var(--cliniq-border);
    }

    .date-mini {
      font-size: 12px;
      font-weight: 700;
      min-width: 55px;
    }

    .back-top {
      position: fixed;
      right: 28px;
      bottom: 26px;
      width: 54px;
      height: 54px;
      border-radius: 999px;
      border: 0;
      background: linear-gradient(135deg, var(--cliniq-blue), var(--cliniq-blue-2));
      color: #ffffff;
      box-shadow: 0 14px 28px rgba(13, 110, 253, 0.35);
      z-index: 100;
      display: grid;
      place-items: center;
    }

    @media (max-width: 992px) {
      .patient-main {
        padding: 18px 14px 28px;
      }

      .appointment-row {
        grid-template-columns: 70px 1fr;
      }

      .appointment-row .status-pill,
      .appointment-row .btn {
        grid-column: 2;
        width: fit-content;
      }

      .dashboard-pair-card {
        min-height: auto;
      }

      .past-appointments-card .appointment-row {
        grid-template-columns: 70px 1fr;
      }

      .past-appointments-card .status-pill {
        grid-column: 2;
        justify-self: start;
      }
    }

    @media (max-width: 768px) {
      .patient-main {
        padding: 14px 10px 24px;
      }

      .page-card,
      .dash-card {
        border-radius: 22px;
        padding: 18px;
      }

      .welcome-card,
      .welcome-panel,
      .welcome-banner {
        padding: 24px 20px;
        border-radius: 22px;
      }

      .stat-card {
        min-height: 140px;
        padding: 20px;
      }

      .stat-icon {
        width: 54px;
        height: 54px;
        font-size: 25px;
      }

      .stat-value {
        font-size: 30px;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }

      .back-top {
        right: 16px;
        bottom: 18px;
        width: 48px;
        height: 48px;
      }
    }
  </style>

  @stack('styles')
</head>
<body>
  <div class="patient-page">
    @include('partials.patient-navbar')
    @include('partials.patient-sidebar')

    <main class="patient-main">
      @yield('content')
    </main>
  </div>

  <button type="button" class="back-top" onclick="window.scrollTo({top:0, behavior:'smooth'})" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
