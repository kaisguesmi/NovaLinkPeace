document.addEventListener('DOMContentLoaded', () => {
    const complaintForm = document.getElementById('complaintForm');
    const complaintsTableBody = document.getElementById('complaintsTableBody');
    const filterStatus = document.getElementById('filterStatus');

    // Handle Form Submission
    if (complaintForm) {
        complaintForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(complaintForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('../Controller/submit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                const messageDiv = document.getElementById('message');

                if (response.ok) {
                    messageDiv.textContent = result.message;
                    messageDiv.className = 'message success';
                    complaintForm.reset();
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.className = 'message error';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    // Handle Dashboard Loading
    if (complaintsTableBody) {
        loadComplaints();

        if (filterStatus) {
            filterStatus.addEventListener('change', () => {
                loadComplaints(filterStatus.value);
            });
        }
    }

    async function loadComplaints(status = '') {
        let url = '../Controller/list.php';
        if (status) {
            url += `?status=${status}`;
        }

        try {
            const response = await fetch(url);
            if (response.ok) {
                const data = await response.json();
                renderTable(data.records);
            } else {
                complaintsTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No complaints found.</td></tr>';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderTable(records) {
        complaintsTableBody.innerHTML = '';
        records.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${record.id}</td>
                <td>${record.author_id}</td>
                <td>${record.target_type}</td>
                <td>${record.target_id}</td>
                <td>${record.reason}</td>
                <td class="status-${record.status}">${record.status}</td>
                <td>${new Date(record.created_at).toLocaleString()}</td>
                <td>
                    ${record.status === 'pending' ? `<button onclick="resolveComplaint(${record.id})" class="action-btn">Resolve</button>` : ''}
                </td>
            `;
            complaintsTableBody.appendChild(row);
        });
    }

    window.resolveComplaint = async (id) => {
        if (!confirm('Are you sure you want to mark this complaint as resolved?')) return;

        try {
            const response = await fetch('../Controller/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, status: 'resolved' })
            });

            if (response.ok) {
                loadComplaints(filterStatus ? filterStatus.value : '');
            } else {
                alert('Failed to update status.');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };
});
