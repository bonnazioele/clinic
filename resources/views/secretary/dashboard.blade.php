@extends('layouts.app')
@section('title','Secretary Dashboard')
@section('content')
<style>
  #queueLaneTabs .queue-lane-tab {
    min-width: 210px;
    text-align: left;
    border: 1px solid #d8dee4;
    background-color: #f8fafc;
    color: #1f2937;
    padding: 0.6rem 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
  }

  #queueLaneTabs .queue-lane-tab .queue-lane-doctor {
    font-weight: 600;
    color: #111827;
    line-height: 1.2;
  }

  #queueLaneTabs .queue-lane-tab .queue-lane-service {
    font-size: 0.8rem;
    color: #4b5563;
    line-height: 1.2;
  }

  #queueLaneTabs .queue-lane-tab:hover {
    background-color: #e2e8f0;
    border-color: #94a3b8;
    color: #111827;
  }

  #queueLaneTabs .queue-lane-tab:hover .queue-lane-doctor,
  #queueLaneTabs .queue-lane-tab:hover .queue-lane-service {
    color: #111827;
  }

  #queueLaneTabs .queue-lane-tab.active {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }

  #queueLaneTabs .queue-lane-tab.active .queue-lane-doctor,
  #queueLaneTabs .queue-lane-tab.active .queue-lane-service {
    color: #ffffff;
  }
</style>
<div class="container py-4">

  <div class="row mb-3">
    <div class="col-12">
      <h5 class="mb-1">Welcome, {{ auth()->user()->name }}!</h5>
    </div>
  </div>

  <div class="row g-4 align-items-start mb-4">
    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">Total today</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($totalTodayCount ?? 0) }}</div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">Waiting</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($waitingCount ?? 0) }}</div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">Served</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($servedCount ?? 0) }}</div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">No show</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($noShowCount ?? 0) }}</div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">Rescheduled</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($rescheduledCount ?? 0) }}</div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="bg-white border rounded-3 p-3 h-100">
        <div class="small text-muted">Walk-in today</div>
        <div class="display-6 lh-1 mt-3">{{ number_format($walkInTodayCount ?? 0) }}</div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <section class="bg-white border rounded-3 p-4 p-lg-4">
        <div class="d-flex justify-content-start mb-3">
          <form method="GET" action="{{ route('secretary.dashboard') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except('sort_by', 'service_id') as $key => $value)
              <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="lane-sort" class="small text-muted mb-0">Sort by</label>
            <select id="lane-sort" name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="doctor" {{ ($laneSort ?? 'doctor') === 'doctor' ? 'selected' : '' }}>Doctor</option>
              <option value="service" {{ ($laneSort ?? 'doctor') === 'service' ? 'selected' : '' }}>Service</option>
            </select>

            @if(($laneSort ?? 'doctor') === 'service')
              <label for="lane-service" class="small text-muted mb-0">Service</label>
              <select id="lane-service" name="service_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0" {{ (int) ($selectedServiceId ?? 0) === 0 ? 'selected' : '' }}>All services</option>
                @foreach(($serviceOptions ?? collect()) as $serviceOption)
                  <option value="{{ $serviceOption->id }}" {{ (int) ($selectedServiceId ?? 0) === (int) $serviceOption->id ? 'selected' : '' }}>
                    {{ $serviceOption->name }}
                  </option>
                @endforeach
              </select>
            @endif
          </form>
        </div>

        @if(($doctorLanes ?? collect())->count())
          <ul class="nav nav-pills flex-nowrap overflow-auto mb-4" id="queueLaneTabs" role="tablist">
            @foreach($doctorLanes as $index => $lane)
              <li class="nav-item me-2 mb-2" role="presentation">
                <button
                  class="nav-link queue-lane-tab {{ $index === 0 ? 'active' : '' }}"
                  id="{{ $lane['id'] }}-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#{{ $lane['id'] }}"
                  type="button"
                  role="tab"
                  aria-controls="{{ $lane['id'] }}"
                  aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                  <div class="queue-lane-doctor">{{ $lane['doctor_name'] }}</div>
                  <div class="queue-lane-service">{{ $lane['service_name'] ?: 'General consultation' }}</div>
                </button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content" id="queueLaneTabsContent">
            @foreach($doctorLanes as $index => $lane)
              @php
                $nowServing = $lane['now_serving'];
                $nextUp = $lane['next_up'];
                $callNextEntry = $lane['call_next_entry'];
                $noShowEntry = $lane['no_show_entry'];
              @endphp
              <div
                class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                id="{{ $lane['id'] }}"
                role="tabpanel"
                aria-labelledby="{{ $lane['id'] }}-tab">

                <div class="border rounded-3 p-4">
                  <div class="mb-3">
                    <div class="h4 mb-0">{{ number_format($lane['queue_depth']) }}</div>
                    <div class="small text-muted">in active queue</div>
                  </div>

                  <div class="bg-light border rounded-3 p-3 mb-3">
                    <div class="small text-muted mb-1">Now serving</div>
                    @if($nowServing)
                      <div class="fs-5">{{ $nowServing->display_name }} <span class="text-muted">- #{{ $nowServing->queue_number }}</span></div>
                      <div class="small text-muted mt-1">
                        Called at {{ optional($nowServing->updated_at)->format('g:i A') ?? '-' }}
                        - {{ $nowServing->appointment_id ? 'Appointment' : 'Walk-in' }}
                      </div>
                    @else
                      <div class="text-muted">No patient is currently being served.</div>
                    @endif
                  </div>

                  <div class="mb-4">
                    <div class="small text-muted mb-2">Next up</div>
                    @if($nextUp->count())
                      <div class="vstack gap-2">
                        @foreach($nextUp as $entry)
                          <div class="d-flex justify-content-between align-items-center border rounded-3 p-2">
                            <div>#{{ $entry->queue_number }} {{ $entry->display_name }}</div>
                            <span class="badge text-bg-light border">{{ $entry->appointment_id ? 'Appt' : 'Walk-in' }}</span>
                          </div>
                        @endforeach
                      </div>
                    @else
                      <div class="text-muted">No queued patients waiting in this lane.</div>
                    @endif
                  </div>

                  @if(!$nowServing && $callNextEntry)
                    <div class="mb-3">
                      <form method="POST" action="{{ route('secretary.queue.call', [$lane['clinic_id'], $callNextEntry->id]) }}">
                        @csrf
                        <button class="btn btn-primary">Start</button>
                      </form>
                    </div>
                  @endif

                  <div class="d-flex flex-wrap gap-2">
                    @if($nowServing)
                      <form method="POST" action="{{ route('secretary.queue.done_next', [$lane['clinic_id'], $nowServing->id]) }}">
                        @csrf
                        <button class="btn btn-primary">Done and next</button>
                      </form>

                      <form
                        method="POST"
                        action="{{ route('secretary.queue.no_show', [$lane['clinic_id'], $noShowEntry->id]) }}"
                        data-confirm="Mark this patient as no-show?"
                        data-confirm-title="Mark As No-Show"
                        data-confirm-btn="Mark No-Show">
                        @csrf
                        <button class="btn btn-outline-secondary">Mark no-show</button>
                      </form>
                    @else
                      <button class="btn btn-outline-secondary" disabled>Mark no-show</button>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="bg-light border rounded-3 p-4 text-center text-muted">
            No doctor lanes are available yet for your assigned clinics.
          </div>
        @endif
      </section>
    </div>
  </div>
  </div>

</div>
@endsection
