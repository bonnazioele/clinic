@extends('layouts.app')
@section('title','Secretary Dashboard')
@section('content')
<style>
  #serviceTabs .service-tab {
    white-space: nowrap;
    border: 1px solid #93c5fd !important;
    background-color: #eff6ff !important;
    color: #1e3a8a !important;
    font-weight: 600;
  }

  #serviceTabs .service-tab:hover,
  #serviceTabs .service-tab:focus {
    border-color: #60a5fa !important;
    background-color: #dbeafe !important;
    color: #1e3a8a !important;
  }

  #serviceTabs .service-tab.active,
  #serviceTabs .service-tab.active:hover,
  #serviceTabs .service-tab.active:focus {
    border-color: #1d4ed8 !important;
    background-color: #1d4ed8 !important;
    color: #ffffff !important;
  }

  #serviceTabs .service-tab .badge {
    background-color: #bfdbfe !important;
    color: #1e3a8a !important;
  }

  #serviceTabs .service-tab.active .badge {
    background-color: #dbeafe !important;
    color: #1d4ed8 !important;
  }

  .queue-lane-tab {
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

  .queue-lane-tab .queue-lane-doctor {
    font-weight: 600;
    color: #111827;
    line-height: 1.2;
  }

  .queue-lane-tab .queue-lane-service {
    font-size: 0.8rem;
    color: #4b5563;
    line-height: 1.2;
  }

  .queue-lane-tab:hover {
    background-color: #e2e8f0;
    border-color: #94a3b8;
    color: #111827;
  }

  .queue-lane-tab:hover .queue-lane-doctor,
  .queue-lane-tab:hover .queue-lane-service {
    color: #111827;
  }

  .queue-lane-tab.active {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }

  .queue-lane-tab.active .queue-lane-doctor,
  .queue-lane-tab.active .queue-lane-service {
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
      @if(($serviceTabs ?? collect())->count())
        <ul class="nav nav-tabs flex-wrap mb-4" id="serviceTabs" role="tablist">
          @foreach($serviceTabs as $serviceTab)
            <li class="nav-item me-2 mb-2" role="presentation">
              <button
                class="nav-link service-tab {{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'active' : '' }}"
                id="{{ $serviceTab['id'] }}-tab"
                data-bs-toggle="tab"
                data-bs-target="#{{ $serviceTab['id'] }}"
                type="button"
                role="tab"
                aria-controls="{{ $serviceTab['id'] }}"
                aria-selected="{{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'true' : 'false' }}">
                {{ $serviceTab['service_name'] }}
                <span class="badge bg-secondary ms-2">{{ $serviceTab['doctor_lanes']->count() }}</span>
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content" id="serviceTabsContent">
          @foreach($serviceTabs as $serviceTab)
            <div
              class="tab-pane fade {{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'show active' : '' }}"
              id="{{ $serviceTab['id'] }}"
              role="tabpanel"
              aria-labelledby="{{ $serviceTab['id'] }}-tab">

              <div class="bg-white border rounded-3 p-4 p-lg-4">
                @if($serviceTab['doctor_lanes']->isEmpty())
                  <div class="bg-light border rounded-3 p-4 text-center text-muted">
                    No doctors are currently assigned to this service.
                  </div>
                @else
                  <ul class="nav nav-pills flex-wrap mb-4" id="queueLaneTabs-{{ $serviceTab['service_id'] }}" role="tablist">
                    @foreach($serviceTab['doctor_lanes'] as $lane)
                      @php
                        $laneTabId = $lane['id'].'-svc-'.$serviceTab['service_id'];
                      @endphp
                      <li class="nav-item me-2 mb-2" role="presentation">
                        <button
                          class="nav-link queue-lane-tab {{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'active' : '' }}"
                          id="{{ $laneTabId }}-tab"
                          data-bs-toggle="tab"
                          data-bs-target="#{{ $laneTabId }}"
                          type="button"
                          role="tab"
                          aria-controls="{{ $laneTabId }}"
                          aria-selected="{{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'true' : 'false' }}">
                          <div class="queue-lane-doctor">{{ $lane['doctor_name'] }}</div>
                          <div class="queue-lane-service">{{ $lane['service_name'] ?: 'General consultation' }}</div>
                        </button>
                      </li>
                    @endforeach
                  </ul>

                  <div class="tab-content" id="queueLaneTabsContent-{{ $serviceTab['service_id'] }}">
                    @foreach($serviceTab['doctor_lanes'] as $lane)
                      @php
                        $nowServing = $lane['now_serving'];
                        $nextUp = $lane['next_up'];
                        $callNextEntry = $lane['call_next_entry'];
                        $noShowEntry = $lane['no_show_entry'];
                        $lanePaneId = $lane['id'].'-svc-'.$serviceTab['service_id'];
                      @endphp
                      <div
                        class="tab-pane fade {{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'show active' : '' }}"
                        id="{{ $lanePaneId }}"
                        role="tabpanel"
                        aria-labelledby="{{ $lanePaneId }}-tab">

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
                                <input type="hidden" name="lane" value="{{ $lane['id'] }}">
                                <input type="hidden" name="service_tab" value="{{ $serviceTab['id'] }}">
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
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="bg-light border rounded-3 p-4 text-center text-muted">
          No service tabs with assigned doctors are available yet for your assigned clinics.
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
