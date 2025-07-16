// Export popup value persistence
let exportEmpValue = null;
let exportPeriodValue = null;

async function exportData() {
    // Remove any existing popup to ensure a clean state
    $('#export-popup').remove();
    $("#exportLoadingPanel").dxLoadPanel({
        message: "Exporting, please wait...",
        visible: false,
        shadingColor: "rgba(0,0,0,0.4)",
        width: 300,
        height: 100,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false
    });
    // Show loading panel
    $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", true);

    let period = $("#header-dxform").dxForm("instance").option("formData").period;
    if (!period) {
        DevExpress.ui.notify({ message: 'Please select Period.', width: 400, type: 'warning'}, { position: 'top right', direction: 'down-push' }, 3000);
        return;
    }

    let year = null, month = null;
    try {
        const dateObj = new Date(period);
        if (!isNaN(dateObj.getTime())) {
            year = dateObj.getFullYear().toString();
            month = String(dateObj.getMonth() + 1).padStart(2, '0');
        } else {
            throw new Error('Invalid date');
        }
    } catch (e) {
        // Fallback to regex parsing
        const match = /^(\d{4})-(\d{2})/.exec(period);
        if (match) {
            year = match[1];
            month = match[2];
        }
    }

    if (!year || !month || !/^\d{4}$/.test(year) || !/^\d{2}$/.test(month)) {
        DevExpress.ui.notify({ message: 'Invalid period format.', width: 400, type: 'error'}, { position: 'top right', direction: 'down-push' }, 3000);
        return;
    }

    try {
        year = encodeURIComponent(year);
        month = encodeURIComponent(month);
        const url = `${APP_BASE_URL}/crm-visits?year=${year}&month=${month}`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (!Array.isArray(data)) {
            throw new Error('Invalid data format received');
        }

        // Populate empId from data if available
        const empId = data.length > 0 && data[0]?.emp_id ? data[0].emp_id : '';

        // Group details by visit_date
        const allDetails = data.reduce((acc, row) => {
            if (Array.isArray(row.details)) {
                return acc.concat(row.details);
            }
            return acc;
        }, []);

        // Group by visit_date with validation
        const grouped = allDetails.reduce((acc, detail) => {
            const visitDate = detail?.visit_date || 'No Date';
            if (!acc[visitDate]) acc[visitDate] = [];
            acc[visitDate].push(detail);
            return acc;
        }, {});

        // Build preview table HTML with No column
        let html = `
            <html>
            <head>
                <title>Preview - Employee: ${empId}, Period: ${year}-${month}</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
                <style>
                    body { padding: 24px; font-family: Arial, sans-serif; }
                    .visit-date-title { font-weight: bold; margin-top: 24px; margin-bottom: 8px; }
                    table { background: #fff; }
                    th, td { vertical-align: middle !important; }
                </style>
                <meta name="csrf-token" content="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}">
            </head>
            <body>
                <div style="display: flex; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; margin-right: 16px;">Preview for Employee: ${empId}, Period: ${year}-${month}</h3>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                    <button id="download-report" class="btn btn-success" style="display: flex; align-items: center; margin-left: auto;">
                        <i class="fa fa-download" style="margin-right: 6px;"></i> Download
                    </button>
                </div>
        `;

        if (Object.keys(grouped).length === 0) {
            html += `<div style="color:#888;">No details data found for this selection.</div>`;
        } else {
            const visitDates = Object.keys(grouped).sort();
            for (let i = 0; i < visitDates.length; i += 2) {
                html += `<div class="row" style="display: flex; flex-wrap: wrap; margin-bottom: 24px;">`;

                // Helper function to generate table
                const generateTable = (visitDate) => {
                    if (!grouped[visitDate]) return '';
                    return `
                        <div class="col" style="flex:1; min-width: 0; max-width: 50%; padding-${i === 0 ? 'right' : 'left'}: 8px;">
                            <div class="visit-date-title">Visit Date: ${visitDate}</div>
                            <table class="table table-bordered table-sm" style="width:100%;margin-bottom:8px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th style="width: 350px; min-width: 300px; max-width: 400px;">Institusi</th>
                                        <th>Specialty</th>
                                        <th>Individu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${grouped[visitDate].map((detail, idx) => `
                                        <tr>
                                            <td>${idx + 1}</td>
                                            <td style="width: 350px; min-width: 300px; max-width: 400px; word-break: break-word;">${detail?.account ?? ''}</td>
                                            <td>${detail?.class ?? ''}</td>
                                            <td>${detail?.contact ?? ''}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                };

                // Generate first table
                html += generateTable(visitDates[i]);

                // Generate second table or empty column
                html += i + 1 < visitDates.length 
                    ? generateTable(visitDates[i + 1])
                    : `<div class="col" style="flex:1; min-width: 0; max-width: 50%; padding-left: 8px;"></div>`;

                html += `</div>`;
            }
        }

        html += `
            </body>
            <script>
                document.getElementById('download-report').addEventListener('click', async function() {
                    try {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '${APP_BASE_URL}/crm-visits/export-pdf';

                        // Get CSRF token from meta tag in this document
                        const csrfToken = document.querySelector('meta[name="csrf-token"]');
                        if (csrfToken) {
                            const inputCsrf = document.createElement('input');
                            inputCsrf.type = 'hidden';
                            inputCsrf.name = '_token';
                            inputCsrf.value = csrfToken.getAttribute('content');
                            form.appendChild(inputCsrf);
                        } else {
                            console.error('CSRF token not found');
                            alert('Session may have expired. Please refresh the main page and try again.');
                            return;
                        }

                        const params = {
                            year: "${year}",
                            month: "${month}",
                            emp_id: "${empId}"
                        };

                        Object.entries(params).forEach(([key, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                    } catch (error) {
                        console.error('Download failed:', error);
                        alert('Download failed: ' + error.message);
                    }
                });
            </script>
            </html>
        `;

        const previewWindow = window.open('', '_blank');
        if (!previewWindow) {
            throw new Error('Popup blocked! Please allow popups for this site to see the preview.');
        }

        previewWindow.document.open();
        previewWindow.document.write(html);
        previewWindow.document.close();

        DevExpress.ui.notify({
            message: `Preview loaded for Period: ${decodeURIComponent(year)}-${decodeURIComponent(month)}`, 
            width: 400, 
            type: 'success'
        }, { position: 'top right', direction: 'down-push' }, 3000);

    } catch (error) {
        DevExpress.ui.notify({ 
            message: 'Error loading preview: ' + error.message, 
            width: 400, 
            type: 'error'
        }, { position: 'top right', direction: 'down-push' }, 4000);
    } finally {
        // Hide loading panel
        $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
    }
}