/* ============================================================
    STATE
============================================================ */
const state = {
    courses: [],

    schedule: {
        Senin: [],
        Selasa: [],
        Rabu: [],
        Kamis: [],
        Jumat: []
    },

    notes: []
};

/* ============================================================
    ELEMENTS
============================================================ */
const elements = {
    courseList: document.getElementById("course-list"),
    searchCourse: document.getElementById("search-course"),
    addCourseBtn: document.getElementById("add-course-btn"),
    scheduleTable: document.getElementById("schedule-table"),

    modal: document.getElementById("modal-container"),
    modalBody: document.getElementById("modal-body"),
    modalTitle: document.getElementById("modal-title"),
    modalClose: document.querySelector(".modal-close"),

    toast: document.getElementById("toast-container")
};

/* ============================================================
    TOOLS — MODAL & TOAST
============================================================ */


elements.modalClose.addEventListener("click", closeModal);

function showToast(msg, type = "success") {
    const div = document.createElement("div");
    div.className = `toast ${type}`;
    div.innerText = msg;

    elements.toast.appendChild(div);

    setTimeout(() => {
        div.remove();
    }, 3000);
}

/* ============================================================
    CREATE COURSE LIST ITEM (ADA EDIT + DELETE)
============================================================ */
function createCourseListItem(course) {
    return `
        <div class="course-item">
            <div>
                <strong>${course.nama}</strong><br>
                <small>${course.dosen}</small>
            </div>
            <div class="course-actions">
                <button class="btn-edit" onclick="openEditCourse(${course.id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn-delete" onclick="deleteCourse(${course.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
}

/* ============================================================
    RENDER COURSE LIST
============================================================ */
function renderCourseList(searchTerm = "") {
    const filtered = state.courses.filter(c =>
        c.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
        c.dosen.toLowerCase().includes(searchTerm.toLowerCase())
    );

    elements.courseList.innerHTML =
        filtered.length > 0
            ? filtered.map(c => createCourseListItem(c)).join("")
            : '<p class="text-center text-muted">Tidak ada mata kuliah.</p>';
}

/* ============================================================
    OPEN MODAL (UMUM)
============================================================ */
function openModal(title, bodyHTML) {
    document.getElementById("modal-title").innerText = title;
    document.getElementById("modal-body").innerHTML = bodyHTML;
    document.getElementById("modal-container").classList.add("active");

    document.querySelector(".modal-close").onclick = closeModal;
}

function closeModal() {
    document.getElementById("modal-container").classList.remove("active");
}

/* ============================================================
    TAMBAH MATA KULIAH (OPEN FORM)
============================================================ */
function openAddCourse() {
    openModal("Tambah Mata Kuliah", `
        <label>Nama Mata Kuliah</label>
        <input id="course-name" class="form-control">

        <label>Dosen</label>
        <input id="course-lecturer" class="form-control">

        <button class="btn btn-primary w-100 mt-3" onclick="saveAddCourse()">Simpan</button>
    `);
}

/* ============================================================
    SIMPAN MATA KULIAH BARU
============================================================ */
function saveAddCourse() {
    const nama = document.getElementById("course-name").value.trim();
    const dosen = document.getElementById("course-lecturer").value.trim();

    if (!nama || !dosen) {
        alert("Mohon isi semua data!");
        return;
    }

    const newCourse = {
        id: Date.now(),
        nama,
        dosen
    };

    state.courses.push(newCourse);

    closeModal();
    renderCourseList();
    saveAll();
}



/* ============================================================
    SIMPAN EDIT COURSE
============================================================ */
function saveEditCourse(id) {
    const nama = document.getElementById("course-name").value.trim();
    const dosen = document.getElementById("course-lecturer").value.trim();

    const index = state.courses.findIndex(c => c.id === id);
    if (index === -1) return;

    state.courses[index].nama = nama;
    state.courses[index].dosen = dosen;

    closeModal();
    renderCourseList();
    saveAll();
}


/* ============================================================
   LOAD DATA (YANG SUDAH DIPERBAIKI)
============================================================ */
async function loadAllData() {
    try {
        const res = await fetch("../Api/get_schedules.php");
        const data = await res.json();

        state.courses = [];
        
        state.schedule = {
           Senin:  [],
           Selasa: [],
           Rabu:   [],
           Kamis:  [],
           Jumat:  []
        };

        data.forEach(item => {
            // 1. Masukkan ke list mata kuliah (untuk fitur Edit/Hapus)
            if (!state.courses.find(c => c.id === item.id)) {
                state.courses.push({
                    id: item.id,
                    nama: item.nama_mk,
                    sks: item.sks,
                    dosen: item.dosen,
                    ruangan: item.ruangan
                });
            }

            // 2. MASUKKAN JADWAL KE LACI HARI YANG BENAR (FIX BUG DISINI)
            // Pastikan nama hari di database sama persis (huruf besar/kecilnya)
            // Jika di database "Senin", kode ini akan memasukkan ke laci "Senin"
            if(state.schedule[item.hari]) {
                state.schedule[item.hari].push({
                    id: item.id,
                    hari: item.hari,
                    jam: item.jam_mulai, // Pastikan ini ada isinya
                    nama: item.nama_mk,
                    ruangan: item.ruangan,
                    course_id: item.id
                });
            }
        });

        renderAll();
    } catch (e) {
        console.error("Gagal load data:", e);
    }
}

/* ============================================================
   DELETE COURSE + SCHEDULE
============================================================ */
async function deleteCourse(id) {
    if (!confirm("Hapus mata kuliah ini?")) return;

    const form = new FormData();
    form.append("id", id);

    const res = await fetch("../Api/delete_course.php", {
        method: "POST",
        body: form
    });

    const json = await res.json();
    if (json.success) {
        showToast("Mata kuliah berhasil dihapus", "success");
        await loadAllData();
    } else {
        showToast("Gagal menghapus", "danger");
    }
}

/* ============================================================
   OPEN EDIT MODAL
============================================================ */
function openEditCourse(id) {
    const course = state.courses.find(c => c.id === id);
    if (!course) return;

    elements.modalTitle.innerHTML = "Edit Mata Kuliah";

    elements.modalBody.innerHTML = `
        <label>Nama Mata Kuliah</label>
        <input id="edit-nama" class="form-control" value="${course.nama}">

        <label>SKS</label>
        <input id="edit-sks" class="form-control" value="${course.sks}">

        <label>Dosen</label>
        <input id="edit-dosen" class="form-control" value="${course.dosen}">

        <label>Ruangan</label>
        <input id="edit-ruangan" class="form-control" value="${course.ruangan}">

        <button class="btn btn-primary mt-3 w-100" onclick="saveEditCourse(${id})">
            Simpan Perubahan
        </button>
    `;

    openModal("Edit Mata Kuliah", elements.modalBody.innerHTML);
}


/* ============================================================
   SAVE EDIT COURSE
============================================================ */
async function saveEditCourse(id) {
    const nama = document.getElementById("edit-nama").value.trim();
    const sks = document.getElementById("edit-sks").value.trim();
    const dosen = document.getElementById("edit-dosen").value.trim();
    const ruangan = document.getElementById("edit-ruangan").value.trim();

    const form = new FormData();
    form.append("id", id);
    form.append("nama_mk", nama);
    form.append("sks", sks);
    form.append("dosen", dosen);
    form.append("ruangan", ruangan);

    const res = await fetch("../Api/edit_course.php", {
        method: "POST",
        body: form
    });

    const json = await res.json();

    if (json.success) {
        showToast("Berhasil disimpan", "success");
        closeModal();
        await loadAllData();
    } else {
        showToast("Gagal menyimpan", "danger");
    }
}

/* ============================================================
   RENDER ULANG SETELAH ADD/EDIT/DELETE
============================================================ */
function renderAll() {
    renderScheduleTable();
    renderCourseList();
    renderCalendar();
    renderNotes();
    updateCurrentWeekDate();
}

/* ============================================================
   INISIALISASI SAAT HALAMAN DIBUKA
============================================================ */
document.addEventListener("DOMContentLoaded", () => {
    loadAllData();

    document.getElementById("add-course-btn").onclick = openAddCourse;
    document.getElementById("search-course").addEventListener("input", (e) => {
        renderCourseList(e.target.value);
    });
});

/* ============================================================
   RENDER TABEL JADWAL (HILANGKAN KOLOM WAKTU)
============================================================ */
function renderScheduleTable() {
    const table = document.getElementById("schedule-table");
    const days = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"];

    // 1. BUAT HEADER (Tanpa Kolom Waktu)
    let html = `
        <thead>
            <tr style="background-color: #0d47a1; color: white;">
                ${days.map(day => <th style="padding: 15px;">${day}</th>).join('')}
            </tr>
        </thead>
        <tbody>
    `;

    // 2. LOGIKA PENYUSUNAN BARIS
    // Kita cari hari apa yang punya mata kuliah paling banyak (misal Senin ada 3 matkul)
    // Maka kita buat 3 baris ke bawah.
    let maxRows = 0;
    days.forEach(day => {
        if (state.schedule[day] && state.schedule[day].length > maxRows) {
            maxRows = state.schedule[day].length;
        }
    });

    // Jika tidak ada data sama sekali, buat 1 baris kosong
    if (maxRows === 0) maxRows = 1;

    // 3. RENDER ISI TABEL
    for (let i = 0; i < maxRows; i++) {
        html += "<tr>";
        days.forEach(day => {
            // Ambil matkul urutan ke-i di hari tersebut
            const matkul = state.schedule[day] ? state.schedule[day][i] : null;

            if (matkul) {
                // Tampilan Kartu Mata Kuliah
                html += `
                    <td style="vertical-align: top; padding: 10px;">
                        <div style="background-color: #6c9bcf; color: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <div style="font-weight: bold; font-size: 1.1em;">${matkul.nama}</div>
                            <div style="margin-top: 5px; font-size: 0.9em;">
                                <i class="bi bi-clock"></i> ${matkul.jam} <br>
                                <i class="bi bi-geo-alt"></i> ${matkul.ruangan || 'Online'}
                            </div>
                        </div>
                    </td>
                `;
            } else {
                // Kolom kosong jika tidak ada matkul di jam segitu
                html += "<td></td>";
            }
        });
        html += "</tr>";
    }

    html += "</tbody>";
    table.innerHTML = html;
}