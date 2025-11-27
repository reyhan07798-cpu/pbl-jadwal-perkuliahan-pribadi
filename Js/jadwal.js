document.addEventListener('DOMContentLoaded', () => {
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
        // Navigation
        navBtns: document.querySelectorAll('.nav-btn'),
        tabContents: document.querySelectorAll('.tab-content'),

        // Schedule
        addCourseBtn: document.getElementById('add-course-btn'),
        searchCourseInput: document.getElementById('search-course'),
        courseList: document.getElementById('course-list'), // Pastikan ini ada
        scheduleTable: document.getElementById('schedule-table'),
        currentWeekDate: document.getElementById('current-week-date'),

        // Calendar
        calendarGrid: document.getElementById('calendar-grid'),
        calendarMonthYear: document.getElementById('calendar-month-year'),
        prevMonthBtn: document.getElementById('prev-month-btn'),
        nextMonthBtn: document.getElementById('next-month-btn'),

        // Notes
        noteForm: document.getElementById('note-form'),
        noteTitle: document.getElementById('note-title'),
        noteContent: document.getElementById('note-content'),
        noteDate: document.getElementById('note-date'),
        notesGrid: document.getElementById('notes-grid'),

        // Modals & Toasts
        modalContainer: document.getElementById('modal-container'),
        modalTitle: document.getElementById('modal-title'),
        modalBody: document.getElementById('modal-body'),
        modalClose: document.querySelector('.modal-close'),
        toastContainer: document.getElementById('toast-container'),

        // DateTime
        datetimeDay: document.getElementById('datetime-day'),
        datetimeDate: document.getElementById('datetime-date'),
        datetimeTime: document.getElementById('datetime-time'),
    };

    // --- INITIALIZATION ---
    function init() {
        loadData();
        setupEventListeners();
        updateDateTime();
        setInterval(updateDateTime, 1000);
        renderAll();
    }

    // --- DATA MANAGEMENT ---
    function loadData() {
        try {
            state.courses = JSON.parse(localStorage.getItem('courses')) || [];
            state.schedule = JSON.parse(localStorage.getItem('schedule')) || {};
            state.notes = JSON.parse(localStorage.getItem('notes')) || [];
        } catch (e) {
            console.error("Failed to load data from localStorage", e);
            showToast("Gagal memuat data.", 'error');
        }
    }

    function saveData() {
        localStorage.setItem('courses', JSON.stringify(state.courses));
        localStorage.setItem('schedule', JSON.stringify(state.schedule));
        localStorage.setItem('notes', JSON.stringify(state.notes));
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        // Navigation
        elements.navBtns.forEach(btn => {
            btn.addEventListener('click', () => switchTab(btn.dataset.tab));
        });

        // Schedule
        elements.addCourseBtn.addEventListener('click', () => showAddCourseModal());
        elements.searchCourseInput.addEventListener('input', (e) => renderCourseList(e.target.value));
        elements.scheduleTable.addEventListener('click', handleScheduleClick);

        // --- BARU: Event Listener untuk Daftar Mata Kuliah ---
        elements.courseList.addEventListener('click', handleCourseListClick);

        // Calendar
        elements.prevMonthBtn.addEventListener('click', () => changeMonth(-1));
        elements.nextMonthBtn.addEventListener('click', () => changeMonth(1));
        elements.calendarGrid.addEventListener('click', handleCalendarClick);
        elements.calendarGrid.addEventListener('dragstart', handleDragStart);
        elements.calendarGrid.addEventListener('dragover', handleDragOver);
        elements.calendarGrid.addEventListener('drop', handleDrop);

        // Notes
        elements.noteForm.addEventListener('submit', handleNoteSubmit);
        elements.notesGrid.addEventListener('click', handleNotesClick);

        // Modals
        elements.modalClose.addEventListener('click', hideModal);
        elements.modalContainer.addEventListener('click', (e) => {
            if (e.target === elements.modalContainer) hideModal();
        });
    }

    // --- RENDER FUNCTIONS ---
    function renderAll() {
        renderScheduleTable();
        renderCourseList();
        renderCalendar();
        renderNotes();
        updateCurrentWeekDate();
    }

    function renderScheduleTable() {
        const thead = `<tr>${DAYS.map(day => `<th>${day}</th>`).join('')}</tr>`;
        const tbody = `<tr>${DAYS.map(day => `<td data-day="${day}"></td>`).join('')}</tr>`;
        elements.scheduleTable.innerHTML = thead + tbody;

        Object.values(state.schedule).forEach(item => {
            const dayCell = elements.scheduleTable.querySelector(`[data-day="${item.hari}"]`);
            if (dayCell) {
                const course = state.courses.find(c => c.id === item.id);
                if (course) {
                    dayCell.innerHTML += createClassCard(course);
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
        
        let html = '';
        for (let i = 0; i < 7; i++) html += `<div class="calendar-day-header">${DAY_MAP[i]}</div>`;
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
    // --- BARU: Handler untuk klik di Daftar Mata Kuliah ---
    function handleCourseListClick(e) {
        const courseItem = e.target.closest('.course-list-item');
        if (!courseItem) return;
        const id = parseInt(courseItem.dataset.id);
        
        if (e.target.closest('.btn-info')) { // Jika tombol Edit diklik
            editCourse(id);
        } else if (e.target.closest('.btn-danger')) { // Jika tombol Hapus diklik
            deleteCourse(id);
        } else {
            viewCourse(id); // Jika bagian lain diklik, lihat detail
        }
    }

    function handleNoteSubmit(e) {
        e.preventDefault();
        const newNote = {
            id: Date.now(),
            title: elements.noteTitle.value,
            content: elements.noteContent.value,
            date: elements.noteDate.value,
        };
        state.notes.unshift(newNote);
        saveData();
        renderNotes();
        renderCalendar();
        elements.noteForm.reset();
        showToast("Catatan berhasil disimpan!", 'success');
    }

    function handleNotesClick(e) {
        const noteCard = e.target.closest('.note-card');
        if (!noteCard) return;
        const id = parseInt(noteCard.dataset.id);
        if (e.target.closest('.btn-info')) {
            viewNote(id);
        } else if (e.target.closest('.btn-danger')) {
            deleteNote(id);
        } else {
            viewNote(id);
        }
    }

    function handleScheduleClick(e) {
        const classCard = e.target.closest('.class-card');
        if (classCard) {
            const id = parseInt(classCard.dataset.id);
            viewCourse(id);
        }
    }
    
    function handleCalendarClick(e) {
        if (e.target.classList.contains('calendar-note')) {
            const id = parseInt(e.target.dataset.id);
            viewNote(id);
        }
    }
    
    function handleDragStart(e) {
        if (e.target.classList.contains('calendar-event')) {
            e.dataTransfer.setData('text/plain', e.target.dataset.id);
        }
    }

    function handleDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDrop(e) {
        if (e.stopPropagation) e.stopPropagation();
        e.preventDefault();
        
        const courseId = parseInt(e.dataTransfer.getData('text/plain'));
        const targetDate = e.target.closest('.calendar-day').dataset.date;
        const targetDayName = DAY_MAP[new Date(targetDate).getDay()];

        if (targetDayName === 'Minggu' || targetDayName === 'Sabtu') {
            showToast('Tidak bisa menjadwalkan di akhir pekan.', 'error');
            return;
        }

        const course = state.courses.find(c => c.id === courseId);
        if (!course) return;

        course.hari = targetDayName;
        
        const oldKey = Object.keys(state.schedule).find(key => state.schedule[key].id === courseId);
        if (oldKey) delete state.schedule[oldKey];
        
        const newKey = `${targetDayName}|${course.jamMulai}`;
        state.schedule[newKey] = { id: course.id, hari: targetDayName, jamMulai: course.jamMulai };

        saveData();
        renderAll();
        showToast(`Jadwal ${course.nama} dipindahkan ke ${targetDayName}`, 'success');
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

    function hideModal() {
        elements.modalContainer.style.display = 'none';
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        elements.toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
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

    // --- VIEW FUNCTIONS ---
    function viewNote(id) {
        const note = state.notes.find(n => n.id === id);
        if (!note) return;
        const formattedDate = new Date(note.date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        showModal(note.title, `<p><strong>Tanggal:</strong> ${formattedDate}</p><hr><p>${note.content.replace(/\n/g, '<br>')}</p>`);
    }

    function viewCourse(id) {
        const course = state.courses.find(c => c.id === id);
        if (!course) return;
        const schedule = Object.values(state.schedule).find(s => s.id === id);
        showModal(course.nama, `
            <p><strong>SKS:</strong> ${course.sks}</p>
            <p><strong>Dosen:</strong> ${course.dosen}</p>
            <p><strong>Ruangan:</strong> ${course.ruangan}</p>
            <p><strong>Hari:</strong> ${schedule.hari}</p>
            <p><strong>Jam:</strong> ${course.jamMulai}</p>
        `);
    }

    // --- BARU: Fungsi untuk Edit Mata Kuliah ---
    function editCourse(id) {
        const course = state.courses.find(c => c.id === id);
        if (!course) return;
        
        const formHtml = `
            <form id="course-form">
                <input type="hidden" id="mk-id" value="${course.id}">
                <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" id="mk-nama" value="${course.nama}" required></div>
                <div class="form-group"><label>SKS</label><input type="number" class="form-control" id="mk-sks" value="${course.sks}" required></div>
                <div class="form-group"><label>Dosen</label><input type="text" class="form-control" id="mk-dosen" value="${course.dosen}" required></div>
                <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" id="mk-ruangan" value="${course.ruangan}" required></div>
                <div class="form-group"><label>Hari</label><select class="form-control" id="mk-hari" required>${DAYS.map(d => `<option value="${d}" ${d === course.hari ? 'selected' : ''}>${d}</option>`).join('')}</select></div>
                <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" id="mk-jam" value="${course.jamMulai}" required></div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        `;
        showModal('Edit Mata Kuliah', formHtml);
        
        document.getElementById('course-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = parseInt(document.getElementById('mk-id').value);
            const courseIndex = state.courses.findIndex(c => c.id === id);
            
            if (courseIndex !== -1) {
                const updatedCourse = {
                    ...state.courses[courseIndex],
                    nama: document.getElementById('mk-nama').value,
                    sks: document.getElementById('mk-sks').value,
                    dosen: document.getElementById('mk-dosen').value,
                    ruangan: document.getElementById('mk-ruangan').value,
                    hari: document.getElementById('mk-hari').value,
                    jamMulai: document.getElementById('mk-jam').value,
                };
                
                state.courses[courseIndex] = updatedCourse;
                
                const oldKey = Object.keys(state.schedule).find(key => state.schedule[key].id === id);
                if (oldKey) delete state.schedule[oldKey];
                
                const newKey = `${updatedCourse.hari}|${updatedCourse.jamMulai}`;
                state.schedule[newKey] = { id: updatedCourse.id, hari: updatedCourse.hari, jamMulai: updatedCourse.jamMulai };
                
                saveData();
                renderAll();
                hideModal();
                showToast("Mata Kuliah berhasil diperbarui!", 'success');
            }
        });
    }

    // --- BARU: Fungsi untuk Hapus Mata Kuliah ---
    function deleteCourse(id) {
        const course = state.courses.find(c => c.id === id);
        if (!course) return;
        
        if (confirm(`Yakin ingin menghapus mata kuliah "${course.nama}"?`)) {
            state.courses = state.courses.filter(c => c.id !== id);
            
            const key = Object.keys(state.schedule).find(k => state.schedule[k].id === id);
            if (key) delete state.schedule[key];
            
            saveData();
            renderAll();
            showToast("Mata Kuliah dihapus.", 'success');
        }
    }

    function showAddCourseModal() {
        const formHtml = `
            <form id="course-form">
                <div class="form-group"><label>Nama MK</label><input type="text" class="form-control" id="mk-nama" required></div>
                <div class="form-group"><label>SKS</label><input type="number" class="form-control" id="mk-sks" required></div>
                <div class="form-group"><label>Dosen</label><input type="text" class="form-control" id="mk-dosen" required></div>
                <div class="form-group"><label>Ruangan</label><input type="text" class="form-control" id="mk-ruangan" required></div>
                <div class="form-group"><label>Hari</label><select class="form-control" id="mk-hari" required>${DAYS.map(d => `<option value="${d}">${d}</option>`).join('')}</select></div>
                <div class="form-group"><label>Jam Mulai</label><input type="time" class="form-control" id="mk-jam" required></div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        `;
        showModal('Tambah Mata Kuliah', formHtml);
        
        document.getElementById('course-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const newCourse = {
                id: Date.now(),
                nama: document.getElementById('mk-nama').value,
                sks: document.getElementById('mk-sks').value,
                dosen: document.getElementById('mk-dosen').value,
                ruangan: document.getElementById('mk-ruangan').value,
                hari: document.getElementById('mk-hari').value,
                jamMulai: document.getElementById('mk-jam').value,
            };
            state.courses.push(newCourse);
            state.schedule[`${newCourse.hari}|${newCourse.jamMulai}`] = { id: newCourse.id, hari: newCourse.hari, jamMulai: newCourse.jamMulai };
            saveData();
            renderAll();
            hideModal();
            showToast("Mata Kuliah berhasil ditambahkan!", 'success');
        });
    }
    
    function deleteNote(id) {
        if (confirm('Yakin ingin menghapus catatan ini?')) {
            state.notes = state.notes.filter(n => n.id !== id);
            saveData();
            renderNotes();
            renderCalendar();
            showToast("Catatan dihapus.", 'success');
        }
    }

    // --- HTML TEMPLATES ---
    function createClassCard(course) {
        return `<div class="class-card" data-id="${course.id}"><strong>${course.nama}</strong><br><small>${course.jamMulai} | ${course.ruangan}</small></div>`;
    }

    // --- BARU: Template untuk item Daftar Mata Kuliah ---
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
                    <button class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Lihat</button>
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