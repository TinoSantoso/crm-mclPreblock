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

        // Create and submit form for direct PDF download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${APP_BASE_URL}/crm-visits/export-pdf`;
        form.target = '_blank'; // Still open in new tab but will directly download

        // Use CSRF token if available
        /* const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken && csrfToken.getAttribute('content')) {
            const inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = '_token';
            inputCsrf.value = csrfToken.getAttribute('content');
            form.appendChild(inputCsrf);
        } else {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
        } */

        // Add parameters
        const params = {
            year: year,
            month: month,
            emp_id: empId
        };

        Object.entries(params).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });

        // Append form to body and submit it
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        DevExpress.ui.notify({
            message: `Downloading PDF for Period: ${decodeURIComponent(year)}-${decodeURIComponent(month)}`, 
            width: 400, 
            type: 'success'
        }, { position: 'top right', direction: 'down-push' }, 3000);

    } catch (error) {
        DevExpress.ui.notify({ 
            message: 'Error exporting PDF: ' + error.message, 
            width: 400, 
            type: 'error'
        }, { position: 'top right', direction: 'down-push' }, 4000);
    } finally {
        // Hide loading panel
        $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
    }
}