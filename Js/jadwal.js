/**
 * @fileoverview Manages the entire student schedule application UI and logic.
 * Handles course scheduling, calendar view, notes, and data synchronization with a server.
 * @version 4.0 - Lengkap dengan fitur Edit dan Hapus
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // --- KONFIGURASI & STATE ---
    const API_BASE_URL = './api/'; // Path ke folder API Anda

    const state = {
        courses: [],
        schedules: [], // Menggunakan schedules yang sudah digabung dari server
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

    // --- API HANDLERS ---
    const api = {
        async fetchSchedule() {
            const response = await fetch(`${API_BASE_URL}get_schedule.php`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Gagal mengambil jadwal dari server.');
            }
            return await response.json();
        },
        async saveCourseAndSchedule(courseData) {
            const response = await fetch(`${API_BASE_URL}save_course_and_schedule.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(courseData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menyimpan data ke server.');
            return result;
        },
        async deleteSchedule(courseId) {
            const response = await fetch(`${API_BASE_URL}delete_schedule.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: courseId }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menghapus jadwal.');
            return result;
        },
        async getCourseById(courseId) {
            const response = await fetch(`${API_BASE_URL}get_course.php?id=${courseId}`);
            if (!response.ok) throw new Error('Gagal mengambil data mata kuliah.');
            return await response.json();
        },
        async updateCourseAndSchedule(courseData) {
            const response = await fetch(`${API_BASE_URL}update_course_and_schedule.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(courseData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memperbarui data.');
            return result;
        }
    };

    // --- INITIALIZATION ---
    function init() {
        setupEventListeners();
        initializeUI();
        loadData(); // Memuat data dari server saat pertama kali
    }

    function initializeUI() {
        updateDateTime();
        setInterval(updateDateTime, 1000);
        updateCurrentWeekDate();
    }

    // --- DATA MANAGEMENT ---
    async function loadData() {
        try {
            state.schedules = await api.fetchSchedule();
            const uniqueCourses = new Map();
            state.schedules.forEach(schedule => {
                if (!uniqueCourses.has(schedule.course_id)) {
                    uniqueCourses.set(schedule.course_id, {
                        id: schedule.course_id,
                        nama: schedule.course_name,
                        sks: schedule.sks,
                        dosen: schedule.dosen,
                        ruangan: schedule.room,
                    });
                }
            });
            state.courses = Array.from(uniqueCourses.values());
            state.notes = [];
            renderAll();
            showToast("Jadwal berhasil dimuat dari server!", 'success');
        } catch (error) {
            console.error("Failed to load data:", error);
            showToast(error.message, 'error');
            renderAll();
        }
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
        elements.addNoteBtn.addEventListener('click', () => showToast("Fitur catatan akan segera hadir.", 'info'));
        elements.notesGrid.addEventListener('click', () => showToast("Fitur catatan akan segera hadir.", 'info'));
        elements.modalClose.addEventListener('click', hideModal);
        elements.modalContainer.addEventListener('click', (e) => { if (e.target === elements.modalContainer) hideModal(); });
    }

    // --- RENDER FUNCTIONS ---
    function renderAll() { renderScheduleTable(); renderCourseList(); renderCalendar(); renderNotes(); }
    
    function renderScheduleTable() {
        const thead = `<tr>${DAYS.map(day => `<th>${day}</th>`).join('')}</tr>`;
        const tbody = `<tr>${DAYS.map(day => `<td data-day="${day}"></td>`).join('')}</tr>`;
        elements.scheduleTable.innerHTML = thead + tbody;

        state.schedules.forEach(item => {
            const dayCell = elements.scheduleTable.querySelector(`[data-day="${item.day_of_week}"]`);
            if (dayCell) {
                const course = state.courses.find(c => c.id === item.course_id);
                if (course) {
                    dayCell.innerHTML += createClassCard(course, item);
                }
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
        elements.notesGrid.innerHTML = '<p class="text-center text-muted">Fitur catatan belum tersedia.</p>';
    }

    // --- HANDLERS ---
    function handleScheduleClick(e) {
        const classCard = e.target.closest('.class-card');
        if (!classCard) return;

        const courseId = parseInt(classCard.dataset.id);
        if (e.target.closest('.btn-edit')) {
            handleEditSchedule(courseId);
        } else if (e.target.closest('.btn-delete')) {
            handleDeleteSchedule(courseId);
        } else {
            viewCourse(courseId);
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
            await loadData();
            hideModal();
        } catch (error) {
            console.error('Error saving course:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
        }
    }

    // --- HANDLER UNTUK EDIT & HAPUS ---
    async function handleEditSchedule(courseId) {
        try {
            const courseData = await api.getCourseById(courseId);
            
            const formHtml = `
                <form id="edit-course-form">
                    <input type="hidden" name="course_id" value="${courseData.id}">
                    <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" name="course_name" value="${courseData.course_name}" required></div>
                    <div class="form-group"><label>SKS</label><input type="number" class="form-control" name="sks" value="${courseData.sks}" required></div>
                    <div class="form-group"><label>Dosen</label><input type="text" class="form-control" name="dosen" value="${courseData.dosen}" required></div>
                    <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" name="room" value="${courseData.room}" required></div>
                    <div class="form-group"><label>Hari</label><select class="form-control" name="hari" required>${DAYS.map(d => `<option value="${d}" ${courseData.day_of_week === d ? 'selected' : ''}>${d}</option>`).join('')}</select></div>
                    <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" name="jamMulai" value="${courseData.start_time}" required></div>
                    <div class="form-group"><label>Jam Selesai</label><input type="time" class="form-control" name="jamSelesai" value="${courseData.end_time}" required></div>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </form>
            `;
            showModal(`Edit Mata Kuliah: ${courseData.course_name}`, formHtml);
            
            document.getElementById('edit-course-form').addEventListener('submit', handleUpdateSchedule);

        } catch (error) {
            showToast(error.message, 'error');
        }
    }

    async function handleUpdateSchedule(e) {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        showLoading(submitBtn, originalText);

        const formData = new FormData(e.target);
        const courseData = {
            course_id: formData.get('course_id'),
            course_name: formData.get('course_name'), sks: formData.get('sks'), dosen: formData.get('dosen'),
            day_of_week: formData.get('hari'), start_time: formData.get('jamMulai'),
            end_time: formData.get('jamSelesai'), room: formData.get('ruangan'),
        };

        try {
            const result = await api.updateCourseAndSchedule(courseData);
            showToast(result.message, 'success');
            await loadData();
            hideModal();
        } catch (error) {
            console.error('Error updating course:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
        }
    }

    async function handleDeleteSchedule(courseId) {
        const course = state.courses.find(c => c.id === courseId);
        if (!course) return;

        if (!confirm(`Yakin ingin menghapus mata kuliah "${course.nama}"?`)) return;

        try {
            const result = await api.deleteSchedule(courseId);
            showToast(result.message, 'success');
            await loadData();
        } catch (error) {
            console.error('Error deleting course:', error);
            showToast(error.message, 'error');
        }
    }


    // --- UI HELPERS ---
    const debounce = (func, delay) => { let timeoutId; return (...args) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(this, args), delay); }; };
    const showLoading = (button, originalText) => { button.disabled = true; button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...`; };
    const hideLoading = (button, originalText) => { button.disabled = false; button.innerHTML = originalText; };

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
    function viewCourse(id) {
        const course = state.courses.find(c => c.id === id); if (!course) return;
        const schedule = state.schedules.find(s => s.course_id === id);
        const modalBody = `
            <p><strong>SKS:</strong> ${course.sks}</p>
            <p><strong>Dosen:</strong> ${course.dosen}</p>
            <p><strong>Ruangan:</strong> ${course.ruangan}</p>
            <p><strong>Hari:</strong> ${schedule ? schedule.day_of_week : '-'}</p>
            <p><strong>Jam:</strong> ${schedule ? schedule.start_time : 'N/A'} - ${schedule ? schedule.end_time : 'N/A'}</p>
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
    function editCourse(id) { showToast("Fitur edit dari daftar akan segera hadir.", 'info'); }
    function deleteCourse(id) { showToast("Fitur hapus dari daftar akan segera hadir.", 'info'); }

    // --- HTML TEMPLATES ---
    function createClassCard(course, scheduleItem) {
        return `
            <div class="class-card" data-id="${course.id}">
                <div class="class-card-content">
                    <strong>${course.nama}</strong><br>
                    <small>${scheduleItem.start_time} - ${scheduleItem.end_time} | ${course.ruangan}</small>
                </div>
                <div class="class-card-actions">
                    <button class="btn btn-sm btn-edit" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    }
    function createCourseListItem(course) {
        return `
            <div class="course-list-item" data-id="${course.id}">
                <div class="course-item-content">
                    <strong>${course.nama}</strong> (${course.sks} SKS)<br>
                    <small>${course.dosen}</small>
                </div>
                <div class="course-item-actions">
                    <button class="btn btn-sm btn-info" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    }
    function getCalendarEvents(dateStr, dayName) {
        let html = '';
        state.schedules.filter(s => s.day_of_week === dayName).forEach(item => {
            const course = state.courses.find(c => c.id === item.course_id);
            if (course) {
                html += `<div class="calendar-event" title="${course.nama}">${item.start_time} ${course.nama}</div>`;
            }
        });
        return html;
    }

    // --- START THE APP ---
    init();
});