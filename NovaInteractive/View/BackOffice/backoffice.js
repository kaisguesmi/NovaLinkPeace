document.addEventListener('DOMContentLoaded', () => {
    const complaintsTableBody = document.getElementById('complaintsTableBody');
    const filterStatus = document.getElementById('filterStatus');
    const filterCategory = document.getElementById('filterCategory');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    let dateSlider = document.getElementById('dateSlider');
    let currentStartDate = null;
    let currentEndDate = null;
    let currentPage = 1;
    let totalPages = 1;
    const perPage = 10;

    if (!localStorage.getItem('user_id') || localStorage.getItem('role') !== 'admin') {
        window.location.href = '../FrontOffice/login.html';
        return;
    }

    initDashboard();

    async function initDashboard() {
        if (!dateSlider) return;

        // Check if noUiSlider is loaded
        if (typeof noUiSlider === 'undefined') {
            console.error('noUiSlider library is not loaded.');
            document.getElementById('dateStartDisplay').textContent = 'Error';
            document.getElementById('dateEndDisplay').textContent = 'Library missing';
            return;
        }

        try {
            let minTimestamp, maxTimestamp;

            try {
                // Fetch metadata for slider range
                const metaResponse = await fetch('../../Controller/metadata.php');
                if (!metaResponse.ok) throw new Error('Failed to fetch metadata');
                const meta = await metaResponse.json();

                const minDate = meta.min_date ? new Date(meta.min_date.replace(' ', 'T')) : new Date(new Date().setMonth(new Date().getMonth() - 1));
                const maxDate = meta.max_date ? new Date(meta.max_date.replace(' ', 'T')) : new Date();

                minTimestamp = minDate.getTime();
                maxTimestamp = maxDate.getTime();
            } catch (fetchError) {
                console.warn('Metadata fetch failed, using defaults:', fetchError);
                // Default to last month
                const now = new Date();
                maxTimestamp = now.getTime();
                minTimestamp = new Date(now.setMonth(now.getMonth() - 1)).getTime();
            }

            // Ensure min < max
            if (minTimestamp >= maxTimestamp) {
                minTimestamp = maxTimestamp - (24 * 60 * 60 * 1000); // Subtract 1 day
            }

            let maxBuffer = maxTimestamp + (24 * 60 * 60 * 1000); // Add 1 day buffer

            // Ensure slider has height
            dateSlider.style.height = '10px';

            noUiSlider.create(dateSlider, {
                range: { 'min': minTimestamp, 'max': maxBuffer },
                step: 24 * 60 * 60 * 1000, // 1 day step
                start: [minTimestamp, maxBuffer],
                connect: true,
                format: {
                    to: function (value) { return new Date(value).toISOString().split('T')[0]; },
                    from: function (value) { return new Date(value).getTime(); }
                }
            });

            const dateStartDisplay = document.getElementById('dateStartDisplay');
            const dateEndDisplay = document.getElementById('dateEndDisplay');

            dateSlider.noUiSlider.on('update', function (values) {
                dateStartDisplay.innerHTML = values[0];
                dateEndDisplay.innerHTML = values[1];
                currentStartDate = values[0];
                currentEndDate = values[1];
            });

            // Trigger filter on change (when handle is released)
            dateSlider.noUiSlider.on('change', function () {
                applyFilters();
            });

            applyFilters();

        } catch (error) {
            console.error('Error initializing dashboard:', error);
            document.getElementById('dateStartDisplay').textContent = 'Error';
            document.getElementById('dateEndDisplay').textContent = 'Init failed';
        }
    }

    window.applyFilters = (resetPage = true) => {
        const status = filterStatus.value;
        const category = filterCategory.value;
        const start = currentStartDate;
        const end = currentEndDate;

        if (resetPage) currentPage = 1; // optionally reset to first page when filters change
        loadComplaints(status, category, start, end, currentPage);
        loadStats(start, end);
    };

    async function loadComplaints(status, category, start, end) {
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (category) params.append('category', category);
        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);
        params.append('page', currentPage);
        params.append('per_page', perPage);

        try {
            const response = await fetch(`../../Controller/list.php?${params.toString()}`);
            const data = await response.json();
            renderTable(data.records);
            // handle pagination
            if (data.pagination) {
                totalPages = data.pagination.total_pages || 1;
                renderPagination(data.pagination.total, data.pagination.per_page, data.pagination.current_page);
            } else {
                totalPages = 1;
                renderPagination(0, perPage, 1);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderPagination(total, per_page, page) {
        const container = document.getElementById('paginationControls');
        container.innerHTML = '';
        if (total <= per_page) return;

        const prev = document.createElement('button');
        prev.textContent = 'Previous';
        prev.className = 'btn-primary';
        prev.disabled = page <= 1;
        prev.addEventListener('click', () => {
            if (page > 1) {
                currentPage = page - 1;
                applyFilters(false);
            }
        });
        container.appendChild(prev);

        // show up to 5 page buttons centered on current
        const range = 2; // pages before/after
        const start = Math.max(1, page - range);
        const end = Math.min(totalPages, page + range);
        for (let p = start; p <= end; p++) {
            const btn = document.createElement('button');
            btn.textContent = p;
            btn.className = p === page ? 'btn-primary' : 'action-btn';
            btn.disabled = p === page;
            btn.addEventListener('click', () => {
                currentPage = p;
                applyFilters(false);
            });
            container.appendChild(btn);
        }

        const next = document.createElement('button');
        next.textContent = 'Next';
        next.className = 'btn-primary';
        next.disabled = page >= totalPages;
        next.addEventListener('click', () => {
            if (page < totalPages) {
                currentPage = page + 1;
                applyFilters(false);
            }
        });
        container.appendChild(next);
    }

    function renderTable(records) {
        complaintsTableBody.innerHTML = '';
        if (!records || records.length === 0) {
            complaintsTableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No complaints found.</td></tr>';
            return;
        }
        records.forEach(record => {
            const row = document.createElement('tr');
            // Add content to data attribute for hover
            row.dataset.content = record.target_content || 'Content not available';

            // Build actions: only show accept/deny/edit for pending, always show reply icon (no text)
            let actionsHtml = '';
            if (record.status === 'pending') {
                actionsHtml += `<button onclick="openResponseModal(${record.id}, 'accepted')" class="action-btn" title="Accept"><i class="fa-solid fa-check" style="color:green;"></i></button>`;
                actionsHtml += `<button onclick="openResponseModal(${record.id}, 'denied')" class="action-btn" title="Deny"><i class="fa-solid fa-xmark" style="color:red;"></i></button>`;
                actionsHtml += `<button onclick="openEditModal(${record.id}, '${record.category}')" class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>`;
            }
            actionsHtml += `<button onclick="openResponseModal(${record.id}, '${record.status}', true)" class="action-btn" title="Respond"><i class="fa-solid fa-reply"></i></button>`;

            row.innerHTML = `
                <td>${record.id}</td>
                <td>${record.author_id}</td>
                <td>${record.target_type}</td>
                <td>${record.category}</td>
                <td>${record.reason}</td>
                <td style="font-weight:bold; color: ${(record.ai_score !== null && record.ai_score > 50) ? 'red' : 'green'};">${(record.ai_score !== null) ? record.ai_score : '-'}</td>
                <td style="font-size:0.85em; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${record.ai_analysis}">${record.ai_analysis ? record.ai_analysis : 'Pending...'}</td>
                <td><span class="status-badge ${record.status}">${record.status}</span></td>
                <td>${new Date(record.created_at).toLocaleDateString()}</td>
                <td class="actions-cell">${actionsHtml}</td>
            `;

            // expose admin_response on the row for quick retrieval when opening modal
            row.dataset.id = record.id;
            row.dataset.adminResponse = record.admin_response ? record.admin_response : '';

            // Hover Logic
            row.addEventListener('mouseenter', (e) => {
                const tooltip = document.getElementById('previewTooltip');
                tooltip.textContent = row.dataset.content;
                tooltip.style.display = 'block';
                tooltip.style.left = e.pageX + 15 + 'px';
                tooltip.style.top = e.pageY + 15 + 'px';
            });
            row.addEventListener('mousemove', (e) => {
                const tooltip = document.getElementById('previewTooltip');
                tooltip.style.left = e.pageX + 15 + 'px';
                tooltip.style.top = e.pageY + 15 + 'px';
            });
            row.addEventListener('mouseleave', () => {
                document.getElementById('previewTooltip').style.display = 'none';
            });

            complaintsTableBody.appendChild(row);
        });
    }

    const responseModal = document.getElementById('responseModal');
    const responseForm = document.getElementById('responseForm');

    window.openResponseModal = (id, status, replyOnly = false) => {
        document.getElementById('responseId').value = id;
        // if replyOnly we keep the current status (no change) by setting the field to the provided status
        // otherwise this call is an action (accepted/denied)
        document.getElementById('responseStatus').value = status;
        if (replyOnly) {
            document.getElementById('responseActionText').textContent = 'Reply';
            document.getElementById('responseActionText').style.color = '#1b6cff';
        } else {
            document.getElementById('responseActionText').textContent = status.toUpperCase();
            document.getElementById('responseActionText').style.color = status === 'accepted' ? 'green' : 'red';
        }

        // populate adminResponse with existing response if any
        try {
            const tr = document.querySelector(`#complaintsTableBody tr[data-id='${id}']`);
            const existing = tr ? tr.dataset.adminResponse : '';
            const textarea = document.getElementById('adminResponse');
            if (textarea) textarea.value = existing || '';
        } catch (err) {
            console.warn('Could not populate existing admin response', err);
        }

        responseModal.style.display = 'block';
    }

    window.closeResponseModal = () => {
        responseModal.style.display = 'none';
    }

    responseForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('responseId').value;
        const status = document.getElementById('responseStatus').value;
        const adminMessage = document.getElementById('adminResponse') ? document.getElementById('adminResponse').value : '';

        try {
            const response = await fetch('../../Controller/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: status, admin_response: adminMessage })
            });
            if (response.ok) {
                // update the row dataset so subsequent opens show new message
                try {
                    const tr = document.querySelector(`#complaintsTableBody tr[data-id='${id}']`);
                    if (tr) tr.dataset.adminResponse = adminMessage;
                } catch (err) { /* ignore */ }

                responseModal.style.display = 'none';
                applyFilters(false);
            } else {
                console.error('Update failed', response.status);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    window.openEditModal = (id, currentCategory) => {
        document.getElementById('editId').value = id;
        document.getElementById('editCategory').value = currentCategory;
        editModal.style.display = 'block';
    }

    window.closeEditModal = () => {
        editModal.style.display = 'none';
    }

    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editId').value;
        const category = document.getElementById('editCategory').value;

        try {
            const response = await fetch('../../Controller/update.php', {
                method: 'POST',
                body: JSON.stringify({ id: id, status: 'pending', category: category })
            });
            if (response.ok) {
                editModal.style.display = 'none';
                applyFilters();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Charts
    let categoryChartInstance = null;
    let statusChartInstance = null;
    let timeChartInstance = null;

    async function loadStats(start, end) {
        const params = new URLSearchParams();
        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);

        try {
            const response = await fetch(`../../Controller/stats.php?${params.toString()}`);
            const stats = await response.json();
            renderCharts(stats);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    function renderCharts(stats) {
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        if (categoryChartInstance) categoryChartInstance.destroy();
        categoryChartInstance = new Chart(catCtx, {
            type: 'pie',
            data: {
                labels: stats.by_category.map(i => i.category),
                datasets: [{
                    data: stats.by_category.map(i => i.count),
                    backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#9b59b6', '#2ecc71', '#e67e22', '#1abc9c', '#34495e']
                }]
            }
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        if (statusChartInstance) statusChartInstance.destroy();
        statusChartInstance = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: stats.by_status.map(i => i.status),
                datasets: [{
                    data: stats.by_status.map(i => i.count),
                    backgroundColor: ['#f39c12', '#27ae60', '#c0392b']
                }]
            }
        });

        const timeCtx = document.getElementById('timeChart').getContext('2d');
        if (timeChartInstance) timeChartInstance.destroy();
        timeChartInstance = new Chart(timeCtx, {
            type: 'line',
            data: {
                labels: stats.over_time.map(i => i.date),
                datasets: [{
                    label: 'Reports',
                    data: stats.over_time.map(i => i.count),
                    borderColor: '#3498db',
                    fill: false
                }]
            },
            options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }

    window.logout = () => {
        localStorage.clear();
        window.location.href = '../FrontOffice/login.html';
    };
});

// AI Scan Function
window.runAIScan = async () => {
    try {
        const btn = document.querySelector('button[onclick="runAIScan()"]');
        if (btn) btn.textContent = 'Scanning...';

        const response = await fetch('../../Controller/run_ai_scan.php');
        const result = await response.json();

        alert(result.message + ' Items processed: ' + (result.processed_count || 0));

        if (btn) btn.textContent = '?? Run AI Scan';
        applyFilters(false); // Refresh list
    } catch (error) {
        console.error('Error:', error);
        alert('Error initiating AI Scan.');
        const btn = document.querySelector('button[onclick="runAIScan()"]');
        if (btn) btn.textContent = '?? Run AI Scan';
    }
};
