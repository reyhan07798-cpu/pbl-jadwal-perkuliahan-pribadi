/**
 * @fileoverview Manages the entire student schedule application UI and logic.
 * Handles course scheduling, calendar view, notes, and data synchronization with a server.
 * @version 3.0 - Final Version with Full Database Integration
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // --- STATE & CONSTANTS ---
    const state = {
        courses: [],
        schedule: {},
        notes: [],
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
    };

    const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    const DAY_MAP = { 0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu' };
    const MONTH_NAMES = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    // --- DOM ELEMENTS ---
    const elements = {
        navBtns: document.querySelectorAll('.nav-btn'),
        tabContents: document.querySelectorAll('.tab-content'),
        addCourseBtn: document.getElementById('add-course-btn'),
        searchCourseInput: document.getElementById('search-course'),
        courseList: document.getElementById('course-list'),
        scheduleTable: document.getElementById('schedule-table'),
        currentWeekDate: document.getElementById('current-week-date'),
        calendarGrid: document.getElementById('calendar-grid'),
        calendarMonthYear: document.getElementById('calendar-month-year'),
        prevMonthBtn: document.getElementById('prev-month-btn'),
        nextMonthBtn: document.getElementById('next-month-btn'),
        addNoteBtn: document.getElementById('add-note-btn'),
        notesGrid: document.getElementById('notes-grid'),
        modalContainer: document.getElementById('modal-container'),
        modalTitle: document.getElementById('modal-title'),
        modalBody: document.getElementById('modal-body'),
        modalClose: document.querySelector('.modal-close'),
        toastContainer: document.getElementById('toast-container'),
        datetimeDay: document.getElementById('datetime-day'),
        datetimeDate: document.getElementById('datetime-date'),
        datetimeTime: document.getElementById('datetime-time'),
    };

    // --- UTILITY FUNCTIONS ---
    const debounce = (func, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    };
    const showLoading = (button, originalText) => {
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...`;
    };
    const hideLoading = (button, originalText) => {
        button.disabled = false;
        button.innerHTML = originalText;
    };
    
    // --- API HANDLERS ---
    const api = {
        async fetchSchedule() {
            const response = await fetch('api/get_schedule.php');
            if (!response.ok) throw new Error('Gagal mengambil jadwal dari server.');
            return await response.json();
        },

        async saveCourseAndSchedule(courseData) {
            const response = await fetch('api/save_course_and_schedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(courseData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menyimpan data ke server.');
            return result;
        },

        async updateSchedule(updateData) {
            const response = await fetch('api/update_schedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updateData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memperbarui jadwal di server.');
            return result;
        }
    };

    // --- INITIALIZATION ---
    function init() {
        loadData();
        setupEventListeners();
        initializeUI();
        loadInitialData();
    }
    function initializeUI() {
        updateDateTime();
        setInterval(updateDateTime, 1000);
        addSyncButton();
        updateCurrentWeekDate();
    }
    async function loadInitialData() {
        try {
            await fetchAndRenderSchedule();
        } catch (error) {
            console.error(error.message);
            showToast(error.message, 'warning');
            renderAll();
        }
    }

    // --- DATA MANAGEMENT ---
    function loadData() {
        try {
            state.courses = JSON.parse(localStorage.getItem('courses')) || [];
            state.schedule = JSON.parse(localStorage.getItem('schedule')) || {};
            state.notes = JSON.parse(localStorage.getItem('notes')) || [];
        } catch (e) {
            console.error("Failed to load data from localStorage", e);
            showToast("Gagal memuat data lokal.", 'error');
            state.courses = []; state.schedule = {}; state.notes = [];
        }
    }
    function saveData() {
        localStorage.setItem('courses', JSON.stringify(state.courses));
        localStorage.setItem('schedule', JSON.stringify(state.schedule));
        localStorage.setItem('notes', JSON.stringify(state.notes));
    }
    async function fetchAndRenderSchedule() {
        const serverSchedule = await api.fetchSchedule();
        state.schedule = {};
        serverSchedule.forEach(item => {
            const key = `${item.day_of_week}|${item.start_time}`;
            state.schedule[key] = { id: item.course_id, hari: item.day_of_week, jamMulai: item.start_time };
            if (!state.courses.find(c => c.id === item.course_id)) {
                state.courses.push({
                    id: item.course_id, nama: item.course_name || `Course ${item.course_id}`,
                    sks: item.sks || 0, dosen: item.dosen || 'Unknown',
                    ruangan: item.room || '', hari: item.day_of_week, jamMulai: item.start_time
                });
            }
        });
        saveData();
        renderAll();
        showToast("Jadwal berhasil dimuat dari server!", 'success');
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        elements.navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));
        elements.addCourseBtn.addEventListener('click', showAddCourseModal);
        elements.searchCourseInput.addEventListener('input', debounce((e) => renderCourseList(e.target.value), 300));
        elements.scheduleTable.addEventListener('click', handleScheduleClick);
        elements.courseList.addEventListener('click', handleCourseListClick);
        elements.prevMonthBtn.addEventListener('click', () => changeMonth(-1));
        elements.nextMonthBtn.addEventListener('click', () => changeMonth(1));
        elements.calendarGrid.addEventListener('click', handleCalendarClick);
        elements.calendarGrid.addEventListener('dragstart', handleDragStart);
        elements.calendarGrid.addEventListener('dragover', handleDragOver);
        elements.calendarGrid.addEventListener('drop', handleDrop);
        elements.addNoteBtn.addEventListener('click', showAddNoteModal);
        elements.notesGrid.addEventListener('click', handleNotesClick);
        elements.modalClose.addEventListener('click', hideModal);
        elements.modalContainer.addEventListener('click', (e) => { if (e.target === elements.modalContainer) hideModal(); });
    }

    // --- RENDER FUNCTIONS ---
    function renderAll() { renderScheduleTable(); renderCourseList(); renderCalendar(); renderNotes(); }
    function renderScheduleTable() {
        const thead = `<tr>${DAYS.map(day => `<th>${day}</th>`).join('')}</tr>`;
        const tbody = `<tr>${DAYS.map(day => `<td data-day="${day}"></td>`).join('')}</tr>`;
        elements.scheduleTable.innerHTML = thead + tbody;
        Object.values(state.schedule).forEach(item => {
            const dayCell = elements.scheduleTable.querySelector(`[data-day="${item.hari}"]`);
            if (dayCell) {
                const course = state.courses.find(c => c.id === item.id);
                if (course) dayCell.innerHTML += createClassCard(course);
            }
        });
    }
    function renderCourseList(searchTerm = '') {
        const filteredCourses = state.courses.filter(course =>
            course.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.dosen.toLowerCase().includes(searchTerm.toLowerCase())
        );
        elements.courseList.innerHTML = filteredCourses.length > 0
            ? filteredCourses.map(course => createCourseListItem(course)).join('')
            : '<p class="text-center text-muted">Tidak ada mata kuliah.</p>';
    }
    function renderCalendar() {
        const firstDay = new Date(state.currentYear, state.currentMonth, 1).getDay();
        const daysInMonth = new Date(state.currentYear, state.currentMonth + 1, 0).getDate();
        let html = DAY_MAP.slice(0, 7).map(day => `<div class="calendar-day-header">${day}</div>`).join('');
        for (let i = 0; i < firstDay; i++) html += `<div class="calendar-day other-month"></div>`;
        for (let date = 1; date <= daysInMonth; date++) {
            const dateStr = `${state.currentYear}-${String(state.currentMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            const dayName = DAY_MAP[new Date(state.currentYear, state.currentMonth, date).getDay()];
            const isToday = date === new Date().getDate() && state.currentMonth === new Date().getMonth() && state.currentYear === new Date().getFullYear();
            html += `<div class="calendar-day ${isToday ? 'today' : ''}" data-date="${dateStr}" data-day-name="${dayName}">
                        <div class="calendar-day-number">${date}</div>
                        ${getCalendarEvents(dateStr, dayName)}
                    </div>`;
        }
        elements.calendarGrid.innerHTML = html;
        elements.calendarMonthYear.textContent = `${MONTH_NAMES[state.currentMonth]} ${state.currentYear}`;
    }
    function renderNotes() {
        const sortedNotes = [...state.notes].sort((a, b) => new Date(b.date) - new Date(a.date));
        elements.notesGrid.innerHTML = sortedNotes.length > 0
            ? sortedNotes.map(note => createNoteCard(note)).join('')
            : '<p class="text-center text-muted">Belum ada catatan.</p>';
    }

    // --- HANDLERS ---
    function handleNotesClick(e) {
        const noteCard = e.target.closest('.note-card'); if (!noteCard) return;
        const id = parseInt(noteCard.dataset.id);
        if (e.target.closest('.btn-danger')) deleteNote(id);
        else viewNoteInModal(id);
    }
    function handleScheduleClick(e) {
        const classCard = e.target.closest('.class-card');
        if (classCard) viewCourse(parseInt(classCard.dataset.id));
    }
    function handleCalendarClick(e) {
        const noteElement = e.target.closest('.calendar-note');
        if (noteElement) viewNoteInModal(parseInt(noteElement.dataset.id));
    }
    function handleDragStart(e) {
        if (e.target.classList.contains('calendar-event')) {
            e.dataTransfer.setData('text/plain', e.target.dataset.id);
        }
    }
    function handleDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; }

    // --- FUNGSI DRAG & DROP YANG SUDAH DIPERBAIKI ---
    async function handleDrop(e) {
        e.preventDefault(); e.stopPropagation();
        const courseId = parseInt(e.dataTransfer.getData('text/plain'));
        const targetDay = e.target.closest('.calendar-day'); if (!targetDay) return;
        const targetDate = targetDay.dataset.date;
        const targetDayName = DAY_MAP[new Date(targetDate).getDay()];
        if (targetDayName === 'Minggu' || targetDayName === 'Sabtu') {
            showToast('Tidak bisa menjadwalkan di akhir pekan.', 'error'); return;
        }
        const course = state.courses.find(c => c.id === courseId); if (!course) return;

        // Optimistically update UI
        course.hari = targetDayName;
        const oldKey = Object.keys(state.schedule).find(key => state.schedule[key].id === courseId);
        if (oldKey) delete state.schedule[oldKey];
        const newKey = `${targetDayName}|${course.jamMulai}`;
        state.schedule[newKey] = { id: course.id, hari: targetDayName, jamMulai: course.jamMulai };

        saveData(); // Simpan ke localStorage dulu
        renderAll();

        try {
            // Kirim perubahan ke server
            await api.updateSchedule({
                course_id: courseId,
                new_day_of_week: targetDayName,
                new_start_time: course.jamMulai
            });
            
            showToast(`Jadwal ${course.nama} dipindahkan ke ${targetDayName}`, 'success');

        } catch (error) {
            console.error('Error updating schedule:', error);
            showToast(error.message, 'error');
            // Jika gagal di server, muat ulang data dari server untuk mengembalikan ke kondisi semula
            fetchAndRenderSchedule();
        }
    }
    
    function handleCourseListClick(e) {
        const courseItem = e.target.closest('.course-list-item'); if (!courseItem) return;
        const id = parseInt(courseItem.dataset.id);
        if (e.target.closest('.btn-info')) editCourse(id);
        else if (e.target.closest('.btn-danger')) deleteCourse(id);
        else viewCourse(id);
    }

    async function handleAddCourse(e) {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        showLoading(submitBtn, originalText);

        const formData = new FormData(e.target);
        const courseData = {
            course_name: formData.get('course_name'), sks: formData.get('sks'), dosen: formData.get('dosen'),
            day_of_week: formData.get('hari'), start_time: formData.get('jamMulai'),
            end_time: formData.get('jamSelesai'), room: formData.get('ruangan'),
        };
        
        try {
            const result = await api.saveCourseAndSchedule(courseData);
            showToast(result.message, 'success');
            await fetchAndRenderSchedule();
            hideModal();
        } catch (error) {
            console.error('Error saving course:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
        }
    }

    // --- UI HELPERS ---
    function switchTab(tabName) {
        elements.navBtns.forEach(btn => btn.classList.remove('active'));
        elements.tabContents.forEach(content => content.classList.remove('active'));
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    function showModal(title, body) {
        elements.modalTitle.textContent = title;
        elements.modalBody.innerHTML = body;
        elements.modalContainer.style.display = 'flex';
    }
    function hideModal() { elements.modalContainer.style.display = 'none'; }
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        elements.toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }
    function updateDateTime() {
        const now = new Date();
        elements.datetimeDay.textContent = DAY_MAP[now.getDay()];
        elements.datetimeDate.textContent = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        elements.datetimeTime.textContent = now.toLocaleTimeString('id-ID');
    }
    function updateCurrentWeekDate() {
        elements.currentWeekDate.textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    function changeMonth(direction) {
        state.currentMonth += direction;
        if (state.currentMonth < 0) { state.currentMonth = 11; state.currentYear--; }
        if (state.currentMonth > 11) { state.currentMonth = 0; state.currentYear++; }
        renderCalendar();
    }

    // --- VIEW & MODAL FUNCTIONS ---
    function viewNoteInModal(id) {
        const note = state.notes.find(n => n.id === id); if (!note) return;
        const modalBodyHtml = `
            <form id="edit-note-form">
                <div class="form-group"><label>Judul</label><input type="text" class="form-control" id="edit-note-title" value="${note.title}" required></div>
                <div class="form-group"><label>Tanggal</label><input type="date" class="form-control" id="edit-note-date" value="${note.date}" required></div>
                <div class="form-group"><label>Isi Catatan</label><textarea class="form-control" id="edit-note-content" rows="10" required>${note.content}</textarea></div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="delete-note-btn">Hapus</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        `;
        showModal(note.title, modalBodyHtml);
        const editForm = document.getElementById('edit-note-form');
        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const noteIndex = state.notes.findIndex(n => n.id === id);
            if (noteIndex !== -1) {
                state.notes[noteIndex] = { ...state.notes[noteIndex], ...Object.fromEntries(new FormData(editForm)) };
                saveData(); renderNotes(); renderCalendar(); hideModal();
                showToast("Catatan berhasil diperbarui!", 'success');
            }
        });
        document.getElementById('delete-note-btn').addEventListener('click', () => {
            if (confirm(`Yakin ingin menghapus catatan "${note.title}"?`)) { deleteNote(id); hideModal(); }
        });
    }
    function showAddNoteModal() {
        const formHtml = `
            <form id="add-note-form">
                <div class="form-group"><label>Judul Catatan</label><input type="text" class="form-control" id="add-note-title" required></div>
                <div class="form-group"><label>Tanggal Catatan</label><input type="date" class="form-control" id="add-note-date" required></div>
                <div class="form-group"><label>Isi Catatan</label><textarea class="form-control" id="add-note-content" rows="10" required></textarea></div>
                <button type="submit" class="btn btn-primary w-100">Simpan Catatan</button>
            </form>
        `;
        showModal('Tambah Catatan Baru', formHtml);
        document.getElementById('add-note-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const newNote = { id: Date.now(), ...Object.fromEntries(new FormData(e.target)) };
            state.notes.unshift(newNote);
            saveData(); renderNotes(); renderCalendar(); hideModal();
            showToast("Catatan berhasil disimpan!", 'success');
        });
    }
    function deleteNote(id) {
        const note = state.notes.find(n => n.id === id);
        if (!confirm(`Yakin ingin menghapus catatan "${note.title}"?`)) return;
        state.notes = state.notes.filter(n => n.id !== id);
        saveData(); renderNotes(); renderCalendar();
        showToast("Catatan dihapus.", 'success');
    }
    function viewCourse(id) {
        const course = state.courses.find(c => c.id === id); if (!course) return;
        const schedule = Object.values(state.schedule).find(s => s.id === id);
        const modalBody = `
            <p><strong>SKS:</strong> ${course.sks}</p>
            <p><strong>Dosen:</strong> ${course.dosen}</p>
            <p><strong>Ruangan:</strong> ${course.ruangan}</p>
            <p><strong>Hari:</strong> ${schedule ? schedule.hari : '-'}</p>
            <p><strong>Jam:</strong> ${course.jamMulai} - ${course.jamSelesai || 'N/A'}</p>
        `;
        showModal(course.nama, modalBody);
    }
    function showAddCourseModal() {
        const formHtml = `
            <form id="course-form">
                <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" name="course_name" required></div>
                <div class="form-group"><label>SKS</label><input type="number" class="form-control" name="sks" required></div>
                <div class="form-group"><label>Dosen</label><input type="text" class="form-control" name="dosen" required></div>
                <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" name="ruangan" required></div>
                <div class="form-group"><label>Hari</label><select class="form-control" name="hari" required>${DAYS.map(d => `<option value="${d}">${d}</option>`).join('')}</select></div>
                <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" name="jamMulai" required></div>
                <div class="form-group"><label>Jam Selesai</label><input type="time" class="form-control" name="jamSelesai" required></div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        `;
        showModal('Tambah Mata Kuliah', formHtml);
        document.getElementById('course-form').addEventListener('submit', handleAddCourse);
    }
    function editCourse(id) { showToast("Fitur edit mata kuliah akan segera hadir.", 'info'); }
    function deleteCourse(id) { showToast("Fitur hapus mata kuliah akan segera hadir.", 'info'); }
    function addSyncButton() {
        const syncBtn = document.createElement('button');
        syncBtn.id = 'sync-btn';
        syncBtn.className = 'btn btn-secondary me-2';
        syncBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Sync';
        syncBtn.addEventListener('click', () => {
            fetchAndRenderSchedule().catch(error => { showToast(error.message, 'error'); });
        });
        const headerRight = document.querySelector('.header-right .user-info');
        headerRight.prepend(syncBtn);
    }

    // --- HTML TEMPLATES ---
    function createClassCard(course) {
        return `<div class="class-card" data-id="${course.id}" draggable="true"><strong>${course.nama}</strong><br><small>${course.jamMulai} | ${course.ruangan}</small></div>`;
    }
    function createCourseListItem(course) {
        return `
            <div class="course-list-item" data-id="${course.id}">
                <div class="course-item-content">
                    <strong>${course.nama}</strong> (${course.sks} SKS)<br>
                    <small>${course.hari}, ${course.jamMulai} | ${course.dosen}</small>
                </div>
                <div class="course-item-actions">
                    <button class="btn btn-sm btn-info" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    }
    function createNoteCard(note) {
        const preview = note.content.substring(0, 100);
        const formattedDate = new Date(note.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        return `
            <div class="note-card" data-id="${note.id}">
                <h5 class="note-card-title"><i class="bi bi-bookmark-fill"></i> ${note.title}</h5>
                <div class="note-card-date">${formattedDate}</div>
                <div class="note-card-preview">${preview}${preview.length >= 100 ? '...' : ''}</div>
                <div class="note-card-footer">
                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                </div>
            </div>
        `;
    }
    function getCalendarEvents(dateStr, dayName) {
        let html = '';
        Object.values(state.schedule).filter(s => s.hari === dayName).forEach(item => {
            const course = state.courses.find(c => c.id === item.id);
            if (course) {
                html += `<div class="calendar-event" draggable="true" data-id="${course.id}" title="${course.nama}">${course.jamMulai} ${course.nama}</div>`;
            }
        });
        state.notes.filter(n => n.date === dateStr).forEach(note => {
            html += `<div class="calendar-note" data-id="${note.id}" title="${note.title}"><i class="bi bi-journal-text"></i> ${note.title}</div>`;
        });
        return html;
    }

    // --- START THE APP ---
    init();
});