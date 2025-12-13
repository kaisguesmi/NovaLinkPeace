document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const feedContainer = document.getElementById('feedContainer');
    const reportModal = document.getElementById('reportModal');
    const reportForm = document.getElementById('reportForm');
    const closeBtn = document.querySelector('.close');
    const complaintsTableBody = document.getElementById('complaintsTableBody');
    const filterStatus = document.getElementById('filterStatus');
    const filterCategory = document.getElementById('filterCategory');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    let dateSlider = document.getElementById('dateSlider');
    let currentStartDate = null;
    let currentEndDate = null;

    // --- Login Logic ---
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('../Controller/login.php', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (response.ok) {
                    localStorage.setItem('user_id', result.user_id);
                    localStorage.setItem('role', result.role);
                    if (result.role === 'admin') {
                        window.location.href = 'dashboard.html';
                    } else {
                        window.location.href = 'feed.html';
                    }
                } else {
                    document.getElementById('message').textContent = result.message;
                    document.getElementById('message').className = 'message error';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    // --- Feed Logic ---
    if (feedContainer) {
        if (!localStorage.getItem('user_id')) {
            window.location.href = 'login.html';
        }
        loadFeed();
    }

    async function loadFeed() {
        try {
            const response = await fetch('../Controller/feed.php');
            const posts = await response.json();

            feedContainer.innerHTML = '';
            posts.forEach(post => {
                const postEl = document.createElement('div');
                postEl.className = 'post';
                postEl.innerHTML = `
                    <div class="post-header">
                        <span>${post.author}</span>
                        <span>${new Date(post.created_at).toLocaleDateString()}</span>
                    </div>
                    <div class="post-content">${post.content}</div>
                    <div class="post-actions">
                        <button class="report-btn" onclick="openReportModal('post', ${post.id})">Report Post</button>
                    </div>
                    <div class="comments-section">
                        ${post.comments.map(comment => `
                            <div class="comment">
                                <span><b>${comment.author}:</b> ${comment.content}</span>
                                <button class="report-btn" style="padding: 2px 5px; font-size: 10px;" onclick="openReportModal('comment', ${comment.id})">Report</button>
                            </div>
                        `).join('')}
                    </div>
                `;
                feedContainer.appendChild(postEl);
            });
        } catch (error) {
            console.error('Error loading feed:', error);
        }
    }

    // --- Report Modal Logic ---
    window.openReportModal = (type, id) => {
        document.getElementById('reportTargetType').value = type;
        document.getElementById('reportTargetId').value = id;
        reportModal.style.display = 'block';
    };

    if (closeBtn) {
        closeBtn.onclick = () => {
            reportModal.style.display = 'none';
        };
    }

    window.onclick = (event) => {
        if (event.target == reportModal) {
            reportModal.style.display = 'none';
        }
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    };

    if (reportForm) {
        reportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(reportForm);
            const data = Object.fromEntries(formData.entries());
            data.author_id = localStorage.getItem('user_id');

            try {
                const response = await fetch('../Controller/submit.php', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                const msgDiv = document.getElementById('reportMessage');
                if (response.ok) {
                    msgDiv.textContent = 'Report submitted successfully.';
                    msgDiv.className = 'message success';
                    setTimeout(() => {
                        reportModal.style.display = 'none';
                        msgDiv.textContent = '';
                        msgDiv.className = '';
                        reportForm.reset();
                    }, 1500);
                } else {
                    msgDiv.textContent = result.message;
                    msgDiv.className = 'message error';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    // --- Dashboard Logic ---
    if (complaintsTableBody) {
        if (localStorage.getItem('role') !== 'admin') {
            alert('Access denied. Admins only.');
            window.location.href = 'login.html';
        }

        // Initialize Slider
        initDashboard();
    }

    async function initDashboard() {
        if (!dateSlider) return;

        if (typeof noUiSlider === 'undefined') {
            console.error('noUiSlider library not loaded.');
            document.getElementById('dateStartDisplay').textContent = 'Error: Slider lib missing';
            return;
        }

        try {
            // Fetch metadata for slider range
            const metaResponse = await fetch('../Controller/metadata.php');
            if (!metaResponse.ok) {
                throw new Error(`HTTP error! status: ${metaResponse.status}`);
            }
            const meta = await metaResponse.json();

            // Handle dates, fallback to today if invalid
            const minDate = meta.min_date ? new Date(meta.min_date.replace(' ', 'T')) : new Date();
            const maxDate = meta.max_date ? new Date(meta.max_date.replace(' ', 'T')) : new Date();

            const minTimestamp = minDate.getTime();
            const maxTimestamp = maxDate.getTime();

            // Add a small buffer to max date to ensure it covers the full day
            // Ensure max > min
            let maxBuffer = maxTimestamp + (24 * 60 * 60 * 1000);
            if (maxBuffer <= minTimestamp) {
                maxBuffer = minTimestamp + (24 * 60 * 60 * 1000);
            }

            noUiSlider.create(dateSlider, {
                range: {
                    'min': minTimestamp,
                    'max': maxBuffer
                },
                step: 24 * 60 * 60 * 1000, // 1 day
                start: [minTimestamp, maxBuffer],
                connect: true,
                format: {
                    to: function (value) {
                        return new Date(value).toISOString().split('T')[0];
                    },
                    from: function (value) {
                        return new Date(value).getTime();
                    }
                }
            });

            const dateStartDisplay = document.getElementById('dateStartDisplay');
            const dateEndDisplay = document.getElementById('dateEndDisplay');

            dateSlider.noUiSlider.on('update', function (values, handle) {
                dateStartDisplay.innerHTML = values[0];
                dateEndDisplay.innerHTML = values[1];
                currentStartDate = values[0];
                currentEndDate = values[1];
            });

            // Initial Load
            applyFilters();

        } catch (error) {
            console.error('Error initializing dashboard:', error);
            document.getElementById('dateStartDisplay').textContent = 'Error loading data';
        }
    }

    window.applyFilters = () => {
        const status = filterStatus.value;
        const category = filterCategory.value;
        // Use slider values if available, otherwise defaults will be handled by API (though slider should be ready)
        const start = currentStartDate;
        const end = currentEndDate;

        loadComplaints(status, category, start, end);
        loadStats(start, end);
    };

    async function loadComplaints(status, category, start, end) {
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (category) params.append('category', category);
        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);

        try {
            const response = await fetch(`../Controller/list.php?${params.toString()}`);
            if (response.ok) {
                const data = await response.json();
                renderTable(data.records);
            } else {
                complaintsTableBody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No complaints found.</td></tr>';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderTable(records) {
        complaintsTableBody.innerHTML = '';
        if (!records || records.length === 0) {
            complaintsTableBody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No complaints found.</td></tr>';
            return;
        }
        records.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${record.id}</td>
                <td>${record.author_id}</td>
                <td>${record.target_type}</td>
                <td>${record.target_id}</td>
                <td>${record.category}</td>
                <td>${record.reason}</td>
                <td style="font-weight:bold; color: ${record.ai_score > 50 ? 'red' : 'green'};">${record.ai_score || '-'}</td>
                <td style="font-size:0.8em; max-width:200px;">${record.ai_analysis || '-'}</td>
                <td class="status-${record.status}">${record.status}</td>
                <td>${new Date(record.created_at).toLocaleString()}</td>
                <td>
                    ${record.status === 'pending' ? `
                        <button onclick="updateStatus(${record.id}, 'accepted')" class="action-btn" style="background-color: #27ae60;">Accept</button>
                        <button onclick="updateStatus(${record.id}, 'denied')" class="action-btn" style="background-color: #c0392b;">Deny</button>
                        <button onclick="openEditModal(${record.id}, '${record.category}')" class="action-btn" style="background-color: #f39c12;">Edit Cat</button>
                    ` : ''}
                </td>
            `;
            complaintsTableBody.appendChild(row);
        });
    }

    window.runAIScan = async () => {
        try {
            alert('Starting AI Scan... Please wait.');
            const response = await fetch('../Controller/run_ai_scan.php');
            const result = await response.json();
            alert(result.message + " Processed: " + (result.processed_count || 0));
            applyFilters(); // Refresh table
        } catch (error) {
            console.error('Error:', error);
            alert('Error running scan check console');
        }
    };

    window.updateStatus = async (id, status, category = null) => {
        if (!confirm(`Mark as ${status}?`)) return;

        const payload = { id: id, status: status };
        if (category) payload.category = category;

        try {
            const response = await fetch('../Controller/update.php', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            if (response.ok) {
                applyFilters(); // Refresh current view
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    // Edit Category Modal
    window.openEditModal = (id, currentCategory) => {
        document.getElementById('editId').value = id;
        document.getElementById('editCategory').value = currentCategory;
        editModal.style.display = 'block';
    }

    window.closeEditModal = () => {
        editModal.style.display = 'none';
    }

    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const category = document.getElementById('editCategory').value;

            try {
                const response = await fetch('../Controller/update.php', {
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
    }

    // --- Charts Logic ---
    async function loadStats(start, end) {
        const params = new URLSearchParams();
        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);

        try {
            const response = await fetch(`../Controller/stats.php?${params.toString()}`);
            const stats = await response.json();
            renderCharts(stats);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    let categoryChartInstance = null;
    let statusChartInstance = null;
    let timeChartInstance = null;

    function renderCharts(stats) {
        // Category Chart
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
            },
            options: {
                plugins: { title: { display: true, text: 'Reports by Category' } }
            }
        });

        // Status Chart
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
            },
            options: {
                plugins: { title: { display: true, text: 'Reports by Status' } }
            }
        });

        // Time Chart
        const timeCtx = document.getElementById('timeChart').getContext('2d');
        if (timeChartInstance) timeChartInstance.destroy();
        timeChartInstance = new Chart(timeCtx, {
            type: 'line',
            data: {
                labels: stats.over_time.map(i => i.date),
                datasets: [{
                    label: 'Reports per Day',
                    data: stats.over_time.map(i => i.count),
                    borderColor: '#3498db',
                    fill: false
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { title: { display: true, text: 'Reports Over Time' } }
            }
        });
    }

    window.logout = () => {
        localStorage.clear();
        window.location.href = 'login.html';
    };
});
