/**
 * @fileoverview Manages entire student schedule application UI and logic.
 * Handles course scheduling, calendar view, notes with dates, and data synchronization with a server.
 * @version 9.0 - Final Fix for Edit Sequence & Room Handling
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // --- KONFIGURASI & STATE GLOBAL ---
    const API_BASE_URL = '../Mahasiswa/api/'; // Sesuaikan path jika perlu

    const state = {
        courses: [],
        schedules: [],
        notes: [],
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
    };

    const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    const DAY_MAP = { 0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu' };
    const MONTH_NAMES = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    // --- ELEMEN-ELEMEN DOM ---
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
        // Elemen untuk Notes
        addNoteBtn: document.getElementById('add-note-btn'),
        notesGrid: document.getElementById('notes-grid'),
        // Elemen Modal
        modalContainer: document.getElementById('modal-container'),
        modalTitle: document.getElementById('modal-title'),
        modalBody: document.getElementById('modal-body'),
        modalClose: document.querySelector('.modal-close'),
        // Elemen Lainnya
        toastContainer: document.getElementById('toast-container'),
        datetimeDay: document.getElementById('datetime-day'),
        datetimeDate: document.getElementById('datetime-date'),
        datetimeTime: document.getElementById('datetime-time'),
        // Tombol Export PDF
        exportPdfBtn: document.getElementById('export-pdf-btn'),
    };

    // --- API HANDLERS ---
    const api = {
        async fetchSchedule() {
            const cacheBuster = new Date().getTime();
            const response = await fetch(`${API_BASE_URL}get_schedule.php?_=${cacheBuster}`, { cache: "no-store" });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
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

        async getCourseById(courseId) {
            const response = await fetch(`${API_BASE_URL}get_course.php?id=${courseId}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Gagal mengambil data mata kuliah.');
            }
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
        
        // --- API untuk Notes ---
        async fetchNotes() {
            const cacheBuster = new Date().getTime();
            const response = await fetch(`${API_BASE_URL}get_notes.php?_=${cacheBuster}`, { cache: "no-store" });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Gagal mengambil catatan.');
            }
            return await response.json();
        },
        async addNote(noteData) {
            const response = await fetch(`${API_BASE_URL}add_note.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(noteData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menambah catatan.');
            return result;
        },
        async updateNote(noteData) {
            const response = await fetch(`${API_BASE_URL}edit_note.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(noteData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal memperbarui catatan.');
            return result;
        },
        async deleteNote(noteId) {
            const response = await fetch(`${API_BASE_URL}delete_note.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: noteId }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Gagal menghapus catatan.');
            return result;
        }
    };

    function init() {
        setupEventListeners();
        initializeUI();
        loadData();
    }

    function initializeUI() {
        updateDateTime();
        setInterval(updateDateTime, 1000);
        updateCurrentWeekDate();
    }

    async function loadData() {
        try {
            const [schedulesData, notesData] = await Promise.all([
                api.fetchSchedule(),
                api.fetchNotes()
            ]);

            state.schedules = schedulesData;
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
            state.notes = notesData;

            renderAll();
        } catch (error) {
            console.error("Failed to load data:", error);
            showToast(error.message, 'error');
            renderAll();
        }
    }

    function setupEventListeners() {
        elements.navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));
        elements.addCourseBtn.addEventListener('click', showAddCourseModal);
        elements.searchCourseInput.addEventListener('input', debounce((e) => renderCourseList(e.target.value), 300));
        
        elements.scheduleTable.addEventListener('click', handleScheduleClick);
        elements.courseList.addEventListener('click', handleCourseListClick);
        
        elements.prevMonthBtn.addEventListener('click', () => changeMonth(-1));
        elements.nextMonthBtn.addEventListener('click', () => changeMonth(1));
        
        elements.addNoteBtn.addEventListener('click', showAddNoteModal);
        elements.notesGrid.addEventListener('click', handleNotesClick);

        elements.calendarGrid.addEventListener('click', handleCalendarEventClick);

        elements.modalClose.addEventListener('click', hideModal);
        elements.modalContainer.addEventListener('click', (e) => { if (e.target === elements.modalContainer) hideModal(); });
        
        elements.exportPdfBtn.addEventListener('click', handleExportPdf);
    }

    function renderAll() {
        renderScheduleTable();
        renderCourseList();
        renderCalendar();
        renderNotes();
    }
    
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
        
        let html = Object.values(DAY_MAP).map(day => `<div class="calendar-day-header">${day}</div>`).join('');

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
        if (state.notes.length === 0) {
            elements.notesGrid.innerHTML = '<p class="text-center text-muted">Belum ada catatan. Tambah catatan baru!</p>';
            return;
        }
        elements.notesGrid.innerHTML = state.notes.map(note => createNoteCard(note)).join('');
    }

    function handleScheduleClick(e) {
        const classCard = e.target.closest('.class-card');
        if (classCard) {
            viewCourse(parseInt(classCard.dataset.id));
        }
    }

    function handleCourseListClick(e) {
        const courseItem = e.target.closest('.course-list-item');
        if (!courseItem) return;

        const courseId = parseInt(courseItem.dataset.id);
        if (isNaN(courseId)) {
            console.error("ID tidak valid:", courseItem.dataset.id);
            showToast("Terjadi kesalahan: ID mata kuliah tidak valid.", 'error');
            return;
        }

        if (e.target.closest('.btn-info')) {
            handleEditSchedule(courseId);
        } else if (e.target.closest('.btn-danger')) {
            handleDeleteSchedule(courseId);
        } else {
            viewCourse(courseId);
        }
    }

    function handleCalendarEventClick(e) {
        const eventEl = e.target.closest('.calendar-event');
        const noteEl = e.target.closest('.calendar-note');

        if (eventEl) {
            const courseId = parseInt(eventEl.dataset.courseId);
            const dayEl = eventEl.closest('.calendar-day');
            const dateStr = dayEl.dataset.date;

            const course = state.courses.find(c => c.id === courseId);
            const schedule = state.schedules.find(s => s.course_id === courseId && s.day_of_week === dayEl.dataset.dayName);
            
            if (!course || !schedule) {
                showToast('Detail jadwal tidak ditemukan.', 'error');
                return;
            }

            const notesForTheDay = state.notes.filter(note => note.note_date === dateStr);

            let modalBody = `
                <div class="schedule-detail">
                    <h4>${course.nama}</h4>
                    <p><strong>Dosen:</strong> ${course.dosen}</p>
                    <p><strong>SKS:</strong> ${course.sks}</p>
                    <p><strong>Ruangan:</strong> ${course.ruangan}</p>
                    <p><strong>Hari:</strong> ${schedule.day_of_week}</p>
                    <p><strong>Waktu:</strong> ${schedule.start_time} - ${schedule.end_time}</p>
                </div>
            `;

            if (notesForTheDay.length > 0) {
                modalBody += `
                    <hr>
                    <h4>Catatan pada Tanggal Ini</h4>
                    <div class="notes-list-in-modal">
                        ${notesForTheDay.map(note => `
                            <div class="note-snippet" data-note-id="${note.id}">
                                <strong>${note.title}</strong>
                                <p>${note.content.substring(0, 80)}${note.content.length > 80 ? '...' : ''}</p>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                modalBody += `<hr><p><em>Tidak ada catatan untuk tanggal ${new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}.</em></p>`;
            }

            showModal(`Detail Jadwal`, modalBody);

            const modalNotesContainer = elements.modalBody.querySelector('.notes-list-in-modal');
            if (modalNotesContainer) {
                modalNotesContainer.addEventListener('click', (e) => {
                    const snippet = e.target.closest('.note-snippet');
                    if (snippet) {
                        viewNote(parseInt(snippet.dataset.noteId));
                    }
                });
            }
        } else if (noteEl) {
            const noteId = parseInt(noteEl.dataset.noteId);
            viewNote(noteId);
        }
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
            end_time: formData.get('jamSelesai'), room: formData.get('room'),
        };
        
        try {
            const result = await api.saveCourseAndSchedule(courseData);
            showToast(result.message, 'success');
            await loadData();
        } catch (error) {
            console.error('Error saving course:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
            hideModal();
        }
    }

    async function handleEditSchedule(courseId) {
        try {
            // PERBAIKAN PENTING: Selalu ambil FRESH data dari database
            const courseData = await api.getCourseById(courseId);
            
            // Escape HTML untuk mencegah error jika ada tanda kutip di nama ruangan
            const safeRoom = (courseData.room || '').replace(/"/g, '&quot;');
            
            const formHtml = `
                <form id="edit-course-form">
                    <input type="hidden" name="course_id" value="${courseData.id}">
                    <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" name="course_name" value="${courseData.course_name}" required></div>
                    <div class="form-group"><label>SKS</label><input type="number" class="form-control" name="sks" value="${courseData.sks}" required></div>
                    <div class="form-group"><label>Dosen</label><input type="text" class="form-control" name="dosen" value="${courseData.dosen}" required></div>
                    <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" name="room" value="${safeRoom}" required></div>
                    <div class="form-group"><label>Hari</label><select class="form-control" name="hari" required>${DAYS.map(d => `<option value="${d}" ${courseData.day_of_week === d ? 'selected' : ''}>${d}</option>`).join('')}</select></div>
                    <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" name="jamMulai" value="${courseData.start_time || ''}" required></div>
                    <div class="form-group"><label>Jam Selesai</label><input type="time" class="form-control" name="jamSelesai" value="${courseData.end_time || ''}" required></div>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </form>
            `;
            showModal(`Edit Mata Kuliah: ${courseData.course_name}`, formHtml);
            
            // PERBAIKAN PENTING: Bersihkan event lama sebelum pasang baru
            const form = document.getElementById('edit-course-form');
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            document.getElementById('edit-course-form').addEventListener('submit', handleUpdateSchedule);
        } catch (error) {
            console.error('Error di handleEditSchedule:', error);
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
            id: parseInt(formData.get('course_id')),
            course_name: formData.get('course_name'), 
            sks: formData.get('sks'), 
            dosen: formData.get('dosen'),
            day_of_week: formData.get('hari'), 
            start_time: formData.get('jamMulai'),
            end_time: formData.get('jamSelesai'), 
            room: formData.get('room'), 
        };

        // Debugging
        console.log("Data yang dikirim:", courseData);

        try {
            const result = await api.updateCourseAndSchedule(courseData);
            showToast(result.message, 'success');
            
            // PERBAIKAN KRUSIAL: Refresh data dari database agar edit kedua berhasil
            await loadData(); 
            
        } catch (error) {
            console.error('Error updating course:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
            hideModal();
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

    function handleNotesClick(e) {
        const noteCard = e.target.closest('.note-card');
        if (!noteCard) return;
        const noteId = parseInt(noteCard.dataset.id);

        if (e.target.closest('.btn-delete-note')) {
            if (confirm('Yakin ingin menghapus catatan ini?')) {
                handleDeleteNote(noteId);
            }
        } else if (e.target.closest('.btn-edit-note')) {
            handleEditNote(noteId);
        } else {
            viewNote(noteId);
        }
    }

    function showAddNoteModal() {
        const today = new Date().toISOString().split('T')[0];
        const formHtml = `
            <form id="note-form">
                <div class="form-group"><label>Judul Catatan</label><input type="text" class="form-control" name="note_title" required></div>
                <div class="form-group"><label>Tanggal Catatan</label><input type="date" class="form-control" name="note_date" value="${today}" required></div>
                <div class="form-group"><label>Isi Catatan</label><textarea class="form-control" name="note_content" rows="8" required></textarea></div>
                <button type="submit" class="btn btn-primary">Simpan Catatan</button>
            </form>
        `;
        showModal('Tambah Catatan Baru', formHtml);
        document.getElementById('note-form').addEventListener('submit', handleAddNote);
    }

    async function handleAddNote(e) {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        showLoading(submitBtn, originalText);

        const formData = new FormData(e.target);
        const noteData = {
            title: formData.get('note_title'),
            content: formData.get('note_content'),
            note_date: formData.get('note_date'),
        };
        
        try {
            const result = await api.addNote(noteData);
            showToast(result.message, 'success');
            await loadData();
        } catch (error) {
            console.error('Error saving note:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
            hideModal();
        }
    }

    async function handleEditNote(noteId) {
        const note = state.notes.find(n => n.id === noteId);
        if (!note) return;

        const formHtml = `
            <form id="edit-note-form">
                <input type="hidden" name="note_id" value="${note.id}">
                <div class="form-group"><label>Judul Catatan</label><input type="text" class="form-control" name="note_title" value="${note.title}" required></div>
                <div class="form-group"><label>Tanggal Catatan</label><input type="date" class="form-control" name="note_date" value="${note.note_date}" required></div>
                <div class="form-group"><label>Isi Catatan</label><textarea class="form-control" name="note_content" rows="8" required>${note.content}</textarea></div>
                <button type="submit" class="btn btn-primary">Perbarui Catatan</button>
            </form>
        `;
        showModal(`Edit Catatan: ${note.title}`, formHtml);
        
        // Fix duplicate listener
        const form = document.getElementById('edit-note-form');
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);
        
        document.getElementById('edit-note-form').addEventListener('submit', handleUpdateNote);
    }

    async function handleUpdateNote(e) {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        showLoading(submitBtn, originalText);

        const formData = new FormData(e.target);
        const noteData = {
            id: parseInt(formData.get('note_id')),
            title: formData.get('note_title'),
            content: formData.get('note_content'),
            note_date: formData.get('note_date'),
        };
        
        try {
            const result = await api.updateNote(noteData);
            showToast(result.message, 'success');
            await loadData();
        } catch (error) {
            console.error('Error updating note:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(submitBtn, originalText);
            hideModal();
        }
    }

    async function handleDeleteNote(noteId) {
        try {
            const result = await api.deleteNote(noteId);
            showToast(result.message, 'success');
            await loadData();
        } catch (error) {
            console.error('Error deleting note:', error);
            showToast(error.message, 'error');
        }
    }

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

    function viewCourse(id) {
        const course = state.courses.find(c => c.id === id);
        if (!course) return;
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

    function viewNote(noteId) {
        const note = state.notes.find(n => n.id === noteId);
        if (!note) return;
        const modalBody = `<p>${note.content.replace(/\n/g, '<br>')}</p>`;
        showModal(note.title, modalBody);
    }

    function showAddCourseModal() {
        const formHtml = `
            <form id="course-form">
                <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" name="course_name" required></div>
                <div class="form-group"><label>SKS</label><input type="number" class="form-control" name="sks" required></div>
                <div class="form-group"><label>Dosen</label><input type="text" class="form-control" name="dosen" required></div>
                <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" name="room" required></div>
                <div class="form-group"><label>Hari</label><select class="form-control" name="hari" required>${DAYS.map(d => `<option value="${d}">${d}</option>`).join('')}</select></div>
                <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" name="jamMulai" required></div>
                <div class="form-group"><label>Jam Selesai</label><input type="time" class="form-control" name="jamSelesai" required></div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        `;
        showModal('Tambah Mata Kuliah', formHtml);
        
        // Fix duplicate listener
        const form = document.getElementById('course-form');
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        document.getElementById('course-form').addEventListener('submit', handleAddCourse);
    }

    function createClassCard(course, scheduleItem) {
        return `
            <div class="class-card" data-id="${course.id}">
                <div class="class-card-content">
                    <strong>${course.nama}</strong><br>
                    <small>${scheduleItem.start_time} - ${scheduleItem.end_time} | ${course.ruangan}</small>
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
    function createNoteCard(note) {
        return `
            <div class="note-card" data-id="${note.id}">
                <div class="note-card-header">
                    <h5>${note.title}</h5>
                    <div>
                        <button class="btn btn-sm btn-info btn-edit-note" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete-note" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="note-card-body">
                    <p>${note.content.substring(0, 100)}${note.content.length > 100 ? '...' : ''}</p>
                </div>
                <div class="note-card-footer">
                    <small>${new Date(note.updated_at).toLocaleString('id-ID')}</small>
                </div>
            </div>
        `;
    }
    
    function getCalendarEvents(dateStr, dayName) {
        let html = '';
        state.schedules.filter(s => s.day_of_week === dayName).forEach(item => {
            const course = state.courses.find(c => c.id === item.course_id);
            if (course) {
                html += `<div class="calendar-event" data-course-id="${course.id}" title="${course.nama}">${item.start_time} ${course.nama}</div>`;
            }
        });

        state.notes.filter(note => note.note_date === dateStr).forEach(note => {
            html += `<div class="calendar-note" data-note-id="${note.id}" title="Catatan: ${note.title}">${note.title}</div>`;
        });

        return html;
    }

    async function handleExportPdf() {
        const originalText = elements.exportPdfBtn.innerHTML;
        showLoading(elements.exportPdfBtn, originalText);

        const exportData = {
            courses: state.courses,
            schedules: state.schedules
        };

        try {
            const response = await fetch(`${API_BASE_URL}export_schedule_pdf.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(exportData)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || 'Gagal membuat PDF.');
            }

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `jadwal_perkuliahan_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            document.body.removeChild(a);

            showToast('PDF berhasil diunduh!', 'success');

        } catch (error) {
            console.error('Export PDF Error:', error);
            showToast(error.message, 'error');
        } finally {
            hideLoading(elements.exportPdfBtn, originalText);
        }
    }

    // --- START THE APP ---
    init();

});