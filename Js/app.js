document.addEventListener('DOMContentLoaded', () => {
    // --- KONFIGURASI ---
    const API_BASE_URL = './api/'; // Sesuaikan jika folder API berada di tempat lain

    // --- STATE & CONSTANTS ---
    const state = {
        courses: [],
        schedules: [],
        notes: [],
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
    };
    // ... (konstanta DAYS, MONTH_NAMES, dll tetap sama) ...

    // --- INITIALIZATION ---
    function init() {
        setupEventListeners();
        updateDateTime();
        setInterval(updateDateTime, 1000);
        loadData();
    }

    // --- DATA MANAGEMENT (Menggunakan Fetch API) ---
    async function loadData() {
        try {
            const [coursesRes, schedulesRes, notesRes] = await Promise.all([
                fetch(API_BASE_URL + 'courses.php'),
                fetch(API_BASE_URL + 'schedules.php'),
                fetch(API_BASE_URL + 'notes.php')
            ]);

            state.courses = await coursesRes.json();
            state.schedules = await schedulesRes.json();
            state.notes = await notesRes.json();

            renderAll();
        } catch (error) {
            console.error("Failed to load data:", error);
            showToast("Gagal memuat data.", 'error');
        }
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        // ... (setup event listener tetap sama) ...
        // Pastikan semua handler (handleAddCourse, deleteNote, dll) memanggil fungsi API
    }

    // --- HANDLERS (CONTOH YANG SUDAH DIPERBAIKI) ---
    async function handleAddCourse(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const newCourse = {
            course_name: formData.get('course_name'),
            sks: formData.get('sks'),
            dosen: formData.get('dosen'),
            day_of_week: formData.get('hari'),
            start_time: formData.get('jamMulai'),
            end_time: formData.get('jamSelesai'), // Asumsi ada field jamSelesai
            room: formData.get('ruangan')
        };

        try {
            const response = await fetch(API_BASE_URL + 'courses.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newCourse)
            });

            if (!response.ok) throw new Error('Failed to add course');
            
            await loadData(); // Muat ulang data dari server
            hideModal();
            showToast("Mata Kuliah berhasil ditambahkan!", 'success');
        } catch (error) {
            console.error('Error adding course:', error);
            showToast('Gagal menambah mata kuliah.', 'error');
        }
    }

    async function deleteCourse(id) {
        if (!confirm('Yakin ingin menghapus mata kuliah ini?')) return;
        try {
            const response = await fetch(API_BASE_URL + `courses.php?id=${id}`, { method: 'DELETE' });
            if (!response.ok) throw new Error('Failed to delete course');
            
            await loadData();
            showToast("Mata Kuliah dihapus.", 'success');
        } catch (error) {
            console.error('Error deleting course:', error);
            showToast('Gagal menghapus mata kuliah.', 'error');
        }
    }
    
    // ... (Lakukan hal yang sama untuk fungsi lain: editCourse, viewNoteInModal, deleteNote, showAddNoteModal, handleDrop) ...
    // Semua fungsi yang mengubah data harus memanggil API dan kemudian loadData()

    // --- RENDER FUNCTIONS ---
    function renderAll() {
        renderScheduleTable();
        renderCourseList();
        renderCalendar();
        renderNotes();
        // ... (fungsi render lainnya) ...
    }
    
    function renderScheduleTable() {
        // Logika render diubah untuk menggunakan `state.schedules` yang sudah berisi data gabungan
        const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        let thead = `<tr><th>Jam</th>${DAYS.map(day => `<th>${day}</th>`).join('')}</tr>`;
        let tbody = '';
        // ... (logika pembuatan tabel berdasarkan state.schedules) ...
        elements.scheduleTable.innerHTML = thead + tbody;
    }
    
    // ... (fungsi render lainnya disesuaikan) ...

    // --- START THE APP ---
    init();
});