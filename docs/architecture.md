# CliniQ MVC map (one page)

This diagram maps key user flows from Routes → Controllers → Models → Views. It’s sized for a single-page print (landscape recommended).

```mermaid
flowchart LR
  %% Grouping
  subgraph R[Routes]
    R_pub_welcome[GET / welcome]
    R_patient_appts[Patient /appointments]
    R_patient_queue[Patient /queue/status]
    R_secretary_services[Sec /secretary/services]
    R_secretary_queue[Sec /secretary/clinics/:clinic/queue]
    R_doctor_queue[Doc /doctor/queue]
    R_admin_clinics[Admin /admin/clinics]
  end

  subgraph C[Controllers]
    C_dash[DashboardController]
    C_appt_public[AppointmentController]
    C_queue_public[QueueController]
    C_sec_appt[Secretary\\AppointmentController]
    C_sec_queue[Secretary\\QueueController]
    C_sec_services[Secretary\\ClinicServiceController]
    C_doc_queue[Doctor\\QueueController]
    C_admin_clinic[Admin\\ClinicController]
    C_admin_service[Admin\\ServiceController]
  end

  subgraph M[Models]
    M_user[User]
    M_clinic[Clinic]
    M_service[Service]
    M_appt[Appointment\n(status: scheduled/completed/cancelled/no_show)]
    M_queue[QueueEntry\n(status: waiting/called/served/cancelled/no_show)]
    M_schedule[DoctorSchedule]
  end

  subgraph V[Views]
    V_layout[layouts/app.blade.php\n(+ Echo/Pusher init)]
    V_patient_appts[appointments/*.blade.php]
    V_patient_queue[queue/status.blade.php]
    V_secretary_services[secretary/services/index.blade.php]
    V_secretary_appts[secretary/appointments/*.blade.php]
    V_secretary_queue[secretary/queue/index.blade.php]
    V_admin_clinics[admin/clinics/*.blade.php]
    V_doctor_queue[doctor/queue/index]
  end

  %% Routes → Controllers
  R_pub_welcome --> C_dash
  R_patient_appts --> C_appt_public
  R_patient_queue --> C_queue_public
  R_secretary_services --> C_sec_services
  R_secretary_queue --> C_sec_queue
  R_doctor_queue --> C_doc_queue
  R_admin_clinics --> C_admin_clinic

  %% Controllers ↔ Models
  C_dash --> M_user
  C_appt_public <--> M_appt
  C_appt_public --> M_clinic
  C_appt_public --> M_service
  C_queue_public <--> M_queue
  C_sec_appt <--> M_appt
  C_sec_services <--> M_service
  C_sec_services <--> M_clinic
  C_sec_queue <--> M_queue
  C_doc_queue <--> M_queue
  C_admin_clinic <--> M_clinic
  C_admin_service <--> M_service
  C_sec_appt --> M_user
  C_doc_queue --> M_appt

  %% Controllers → Views (returned responses)
  C_appt_public --> V_patient_appts
  C_queue_public --> V_patient_queue
  C_sec_services --> V_secretary_services
  C_sec_appt --> V_secretary_appts
  C_sec_queue --> V_secretary_queue
  C_admin_clinic --> V_admin_clinics
  C_doc_queue --> V_doctor_queue

  %% Shared layout
  V_layout -. wraps .- V_patient_appts
  V_layout -. wraps .- V_patient_queue
  V_layout -. wraps .- V_secretary_services
  V_layout -. wraps .- V_secretary_appts
  V_layout -. wraps .- V_secretary_queue
  V_layout -. wraps .- V_admin_clinics
  V_layout -. wraps .- V_doctor_queue
```

## File index (exact paths)
- Routes: `routes/web.php`
- Controllers:
  - Public: `app/Http/Controllers/AppointmentController.php`, `QueueController.php`, `DashboardController.php`
  - Secretary: `app/Http/Controllers/Secretary/AppointmentController.php`, `Secretary/ClinicServiceController.php`, `Secretary/QueueController.php`
  - Doctor: `app/Http/Controllers/Doctor/QueueController.php`, `Doctor/ScheduleController.php`
  - Admin: `app/Http/Controllers/Admin/ClinicController.php`, `Admin/ServiceController.php`, `Admin/DashboardController.php`
- Models: `app/Models/Appointment.php`, `QueueEntry.php`, `Clinic.php`, `Service.php`, `User.php`, `DoctorSchedule.php`
- Views:
  - Patient: `resources/views/appointments/*.blade.php`, `resources/views/queue/status.blade.php`
  - Secretary: `resources/views/secretary/services/index.blade.php`, `resources/views/secretary/appointments/*.blade.php`, `resources/views/secretary/queue/index.blade.php`
  - Doctor: `resources/views/doctor/*`
  - Admin: `resources/views/admin/*`
  - Layout: `resources/views/layouts/app.blade.php`

## Notes
- Real-time: `layouts/app.blade.php` initializes Echo + Pusher and subscribes to private notification channels (e.g., `user.notifications.{id}`).
- Status presentation: `Appointment` and `QueueEntry` expose `status_label` and `status_badge_class` so views render “No Show” (not `no_show`).
- Business rules: service detach/delete blocks only if appointments are in active use (ignores `cancelled`, `no_show`).
