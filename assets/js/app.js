/**
 * JavaScript Helpers for SIA Mahasiswa Portal
 */

// Toggle Mobile / Desktop Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) {
        sidebar.classList.toggle('-translate-x-full');
        if (overlay) overlay.classList.toggle('hidden');
    }
}

// Confirmation Dialog for Delete Actions
function confirmDelete(url, title = 'Apakah Anda yakin?', text = 'Data yang dihapus tidak dapat dikembalikan!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Flip E-KTM Card
function flipCard() {
    const cardInner = document.getElementById('ktm-card-inner');
    if (cardInner) {
        cardInner.classList.toggle('card-flipped');
    }
}

// Filter Table rows by input
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const textValue = td[j].textContent || td[j].innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                    break;
                }
            }
        }
        tr[i].style.display = match ? '' : 'none';
    }
}

// Print specific area or trigger print
function printDocument() {
    window.print();
}
