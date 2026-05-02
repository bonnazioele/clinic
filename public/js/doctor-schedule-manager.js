document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('scheduleForm');
  if (!form) {
    return;
  }

  const dayCheckboxes = Array.from(form.querySelectorAll('input[name="days[]"]'));
  const timeBlocksWrap = document.getElementById('timeBlocksWrap');
  const addTimeBlockBtn = document.getElementById('addTimeBlockBtn');
  const scheduleFormMessage = document.getElementById('scheduleFormMessage');
  const submitLabel = document.getElementById('submitLabel');
  const cancelEditBtn = document.getElementById('cancelEdit');
  const formMethod = document.getElementById('formMethod');
  const scheduleIdInput = document.getElementById('schedule_id');
  const dayOfWeekInput = document.getElementById('day_of_week');
  const serviceSelect = document.getElementById('service_id');
  const deleteModalEl = document.getElementById('deleteScheduleModal');
  const confirmDeleteBtn = document.getElementById('confirmDeleteScheduleBtn');
  const deleteModal = deleteModalEl && window.bootstrap ? new window.bootstrap.Modal(deleteModalEl) : null;
  const storeAction = window.doctorScheduleData?.storeAction || form.action;
  let pendingDeleteForm = null;

  function showMessage(message) {
    if (!scheduleFormMessage) return;
    scheduleFormMessage.textContent = message;
    scheduleFormMessage.classList.remove('d-none');
  }

  function hideMessage() {
    if (!scheduleFormMessage) return;
    scheduleFormMessage.textContent = '';
    scheduleFormMessage.classList.add('d-none');
  }

  function blockRows() {
    return Array.from(timeBlocksWrap.querySelectorAll('.time-block-row'));
  }

  function timeToMinutes(value) {
    if (!value) return null;
    const [hours, minutes] = value.split(':').map(Number);
    return (hours * 60) + minutes;
  }

  function formatTime(value) {
    if (!value) return '';
    const [hoursString, minutesString] = value.split(':');
    let hours = Number(hoursString);
    const minutes = minutesString;
    const suffix = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return `${hours}:${minutes} ${suffix}`;
  }

  function renumberBlocks() {
    blockRows().forEach((row, index) => {
      const startInput = row.querySelector('input[type="time"][name*="start_time"]');
      const endInput = row.querySelector('input[type="time"][name*="end_time"]');
      if (startInput) startInput.name = `time_blocks[${index}][start_time]`;
      if (endInput) endInput.name = `time_blocks[${index}][end_time]`;
    });
  }

  function updateBreakNotes() {
    const rows = blockRows();
    rows.forEach(row => {
      const note = row.querySelector('.break-note');
      if (note) {
        note.classList.add('d-none');
        note.textContent = '';
      }
    });

    rows.forEach((row, index) => {
      const currentEnd = row.querySelector('input[type="time"][name*="end_time"]')?.value;
      const nextRow = rows[index + 1];
      if (!currentEnd || !nextRow) return;

      const nextStart = nextRow.querySelector('input[type="time"][name*="start_time"]')?.value;
      if (!nextStart) return;

      const currentMinutes = timeToMinutes(currentEnd);
      const nextMinutes = timeToMinutes(nextStart);
      if (currentMinutes === null || nextMinutes === null || nextMinutes <= currentMinutes) {
        return;
      }

      const note = row.querySelector('.break-note');
      if (note) {
        note.textContent = `Break after this block: ${formatTime(currentEnd)} to ${formatTime(nextStart)}`;
        note.classList.remove('d-none');
      }
    });
  }

  function addTimeBlock(start = '', end = '') {
    const row = document.createElement('div');
    row.className = 'time-block-row';
    row.innerHTML = `
      <div>
        <span class="time-block-label">Start time</span>
        <input type="time" class="form-control" value="${start}" required>
      </div>
      <div class="d-flex align-items-end justify-content-center text-muted fw-semibold">to</div>
      <div>
        <span class="time-block-label">End time</span>
        <input type="time" class="form-control" value="${end}" required>
      </div>
      <div class="d-flex align-items-end justify-content-end">
        <button type="button" class="btn btn-outline-danger time-remove remove-time-block-btn" title="Remove block">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="break-note d-none"></div>
    `;

    row.querySelector('.remove-time-block-btn').addEventListener('click', () => {
      const rows = blockRows();
      if (rows.length <= 1) return;
      row.remove();
      renumberBlocks();
      updateBreakNotes();
    });

    timeBlocksWrap.appendChild(row);
    renumberBlocks();
    updateBreakNotes();
    return row;
  }

  function ensureAtLeastOneBlock() {
    if (blockRows().length === 0) {
      addTimeBlock();
    }
  }

  addTimeBlockBtn.addEventListener('click', () => {
    const row = addTimeBlock();
    row.querySelector('input[type="time"]')?.focus();
  });

  timeBlocksWrap.addEventListener('input', updateBreakNotes);

  function validateBlocks() {
    const seen = [];
    for (const row of blockRows()) {
      const startInput = row.querySelector('input[type="time"][name*="start_time"]');
      const endInput = row.querySelector('input[type="time"][name*="end_time"]');
      const start = startInput?.value;
      const end = endInput?.value;
      if (!start || !end) {
        showMessage('Each consultation block needs both a start and end time.');
        return false;
      }
      const startMinutes = timeToMinutes(start);
      const endMinutes = timeToMinutes(end);
      if (endMinutes <= startMinutes) {
        showMessage('Each consultation block must end after it starts.');
        return false;
      }
      for (const block of seen) {
        if (startMinutes < block.end && endMinutes > block.start) {
          showMessage('Time blocks cannot overlap.');
          return false;
        }
      }
      seen.push({ start: startMinutes, end: endMinutes });
    }
    hideMessage();
    return true;
  }

  function validateDaySelection() {
    const selectedDays = dayCheckboxes.filter(cb => cb.checked);
    if (!selectedDays.length) {
      showMessage('Select at least one day for your recurring schedule.');
      return false;
    }
    return true;
  }

  function validateServiceSelection() {
    if (!serviceSelect || serviceSelect.value) {
      return true;
    }
    showMessage('Please select a service.');
    return false;
  }

  form.addEventListener('submit', event => {
    if (!validateServiceSelection() || !validateDaySelection() || !validateBlocks()) {
      event.preventDefault();
    }
  });

  function resetForm() {
    form.reset();
    form.action = storeAction;
    formMethod.value = 'POST';
    scheduleIdInput.value = '';
    if (dayOfWeekInput) dayOfWeekInput.value = '';
    submitLabel.textContent = 'Save schedule';
    cancelEditBtn.classList.add('d-none');
    ensureAtLeastOneBlock();
    // Reset service selection
    if (serviceSelect) {
      serviceSelect.value = '';
    }
    hideMessage();
  }

  cancelEditBtn.addEventListener('click', resetForm);

  Array.from(document.querySelectorAll('.edit-btn')).forEach(button => {
    button.addEventListener('click', () => {
      const row = button.closest('tr');
      if (!row) return;

      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      hideMessage();

      const id = row.dataset.id;
      const serviceId = row.dataset.serviceId || '';
      const day = row.dataset.day;
      const startTime = row.dataset.startTime || '';
      const endTime = row.dataset.endTime || '';

      form.action = `${storeAction}/${id}`;
      formMethod.value = 'PUT';
      scheduleIdInput.value = id;
      if (dayOfWeekInput) dayOfWeekInput.value = day;
      submitLabel.textContent = 'Update schedule';
      cancelEditBtn.classList.remove('d-none');

      ensureAtLeastOneBlock();

      if (serviceSelect) {
        serviceSelect.value = serviceId;
      }

      dayCheckboxes.forEach(cb => { cb.checked = Number(cb.value) === Number(day); });

      const firstRow = blockRows()[0];
      if (firstRow) {
        firstRow.querySelector('input[type="time"][name*="start_time"]').value = startTime;
        firstRow.querySelector('input[type="time"][name*="end_time"]').value = endTime;
      }
      blockRows().slice(1).forEach(rowEl => rowEl.remove());
      renumberBlocks();
      updateBreakNotes();
    });
  });

  Array.from(document.querySelectorAll('.delete-schedule-btn')).forEach(button => {
    button.addEventListener('click', () => {
      pendingDeleteForm = button.closest('form.delete-schedule-form');
      deleteModal?.show();
    });
  });

  confirmDeleteBtn?.addEventListener('click', () => {
    pendingDeleteForm?.submit();
  });

  deleteModalEl?.addEventListener('hidden.bs.modal', () => {
    pendingDeleteForm = null;
  });

  ensureAtLeastOneBlock();
  updateBreakNotes();

  const calendarEl = document.getElementById('doctorScheduleCalendar');
  if (calendarEl && window.FullCalendar && window.doctorScheduleData) {
    const events = window.doctorScheduleData.schedules || [];

    const buildCalendarEvents = (rangeStart, rangeEnd) => {
      const rendered = [];

      const toDate = value => {
        if (!value) return null;
        const date = new Date(value + 'T00:00:00');
        return Number.isNaN(date.getTime()) ? null : date;
      };

      const formatDate = date => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      };

      events.forEach(schedule => {
        if (!schedule.is_active) return;

        const startAnchor = new Date(rangeStart.getTime());
        const dayDiff = (schedule.day_of_week - startAnchor.getDay() + 7) % 7;
        startAnchor.setDate(startAnchor.getDate() + dayDiff);

        for (let cursor = new Date(startAnchor); cursor <= rangeEnd; cursor.setDate(cursor.getDate() + 7)) {
          const date = toDate(formatDate(cursor));
          if (!date) continue;
          rendered.push({
            id: `schedule-${schedule.id}-${formatDate(date)}`,
            title: schedule.service,
            start: `${formatDate(date)}T${schedule.start_time}:00`,
            end: `${formatDate(date)}T${schedule.end_time}:00`,
            display: 'block',
            extendedProps: { scheduleId: schedule.id, clinic: schedule.service }
          });
        }
      });

      return rendered;
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
      },
      events(info, successCallback) {
        successCallback(buildCalendarEvents(info.start, info.end));
      },
      eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
    });

    calendar.render();
  }
});
