<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Light gray background */
        }
    </style>
    <!-- DevExtreme (DevExpress JQuery) CSS - Version 24.2.x -->
    <!-- Note: Replace with actual licensed DevExtreme CDN or local paths -->
    <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/24.2.3/css/dx.light.css">
    <!-- JQuery CDN - Required for DevExtreme -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- DevExtreme (DevExpress JQuery) JavaScript - Version 24.2.x -->
    <script type="text/javascript" src="https://cdn3.devexpress.com/jslib/24.1.7/js/dx.all.js"></script>
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-blue-700 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <nav>
                <button id="logoutButton"
                        class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out font-semibold shadow-sm">
                    Logout
                </button>
            </nav>
        </div>
    </header>

    <main class="flex-grow container mx-auto p-6">
        <div class="bg-white p-8 rounded-lg shadow-xl">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Welcome to Your Dashboard!</h2>
            <p class="text-gray-700 mb-4">This is a protected area. You can view your data here.</p>

            <div class="mb-6 flex gap-4">
                <button id="preblockButton" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out font-semibold shadow-sm">
                    Go to Preblock
                </button>
                <button id="preblockVisitButton" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out font-semibold shadow-sm">
                    Preblock Visit
                </button>
                <button id="reportCustomerButton" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out font-semibold shadow-sm">
                    Report Customer
                </button>
            </div>

            <div id="userData" class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                <h3 class="text-xl font-medium text-gray-800 mb-2">Your Profile:</h3>
                <p><strong>Name:</strong> <span id="userName">Loading...</span></p>
                <p><strong>Email:</strong> <span id="userEmail">Loading...</span></p>
                <p><strong>EmployeeID:</strong> <span id="userEmployee">Loading...</span></p>
            </div>

            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">DevExpress Data Grid Example</h3>
                <p class="text-gray-600 mb-4">This is a placeholder for a DevExpress JQuery Data Grid. Data would typically be loaded from your Lumen API.</p>
                <div id="gridContainer" class="rounded-lg overflow-hidden border border-gray-300">
                    <!-- DevExpress Data Grid will be initialized here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Message Box -->
    <div id="messageBox" class="fixed bottom-4 right-4 p-3 rounded-md text-sm shadow-lg hidden" role="alert"></div>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/devextreme-license.js')!!}"></script>
    <script>
        const APP_BASE_URL = {!! json_encode(url('/')) !!};
        // Function to show messages
        function showMessage(message, type = 'info') {
            const messageBox = document.getElementById('messageBox');
            messageBox.textContent = message;
            messageBox.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700', 'bg-blue-100', 'text-blue-700');

            if (type === 'error') {
                messageBox.classList.add('bg-red-100', 'text-red-700');
            } else if (type === 'success') {
                messageBox.classList.add('bg-green-100', 'text-green-700');
            } else {
                messageBox.classList.add('bg-blue-100', 'text-blue-700');
            }
            messageBox.classList.remove('hidden');
            setTimeout(() => {
                messageBox.classList.add('hidden');
            }, 5000);
        }

        // Check for JWT token on page load
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                // If no token, redirect to login page
                window.location.href = `${APP_BASE_URL}/login`;
                return;
            }
            
            // Send token to server to store in session
            try {
                const response = await fetch(`${APP_BASE_URL}/api/auth/store-token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ token: token })
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('Error storing token in session:', errorData);
                    showMessage('Error storing token in session. Please try logging in again.', 'error');
                    localStorage.removeItem('jwt_token');
                    window.location.href = `${APP_BASE_URL}/login`;
                    return;
                }
                
                console.log('Token stored in session successfully');
            } catch (error) {
                console.error('Error storing token in session:', error);
                showMessage('Error connecting to server. Please try again later.', 'error');
            }
            // Fetch user data
            try {
                const userResponse = await fetch(`${APP_BASE_URL}/api/me`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (userResponse.ok) {
                    const userData = await userResponse.json();
                    document.getElementById('userName').textContent = userData.name;
                    document.getElementById('userEmail').textContent = userData.email;
                    document.getElementById('userEmployee').textContent = userData.employee_id;
                } else {
                    const errorData = await userResponse.json();
                    showMessage(errorData.message || 'Failed to fetch user data. Please log in again.', 'error');
                    localStorage.removeItem('jwt_token'); // Clear invalid token
                    window.location.href = `${APP_BASE_URL}/login`;
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
                showMessage('An error occurred while fetching user data.', 'error');
                localStorage.removeItem('jwt_token');
                window.location.href = `${APP_BASE_URL}/login`;
            }

            console.log("Dashboard loaded successfully");
        });

        // Logout functionality
        document.getElementById('logoutButton').addEventListener('click', function() {
            localStorage.removeItem('jwt_token');
            sessionStorage.removeItem('jwt_token');
            
            // Also clear the server-side session
            fetch(`${APP_BASE_URL}/api/auth/logout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).catch(error => {
                console.error('Error during logout:', error);
            });
            
            showMessage('You have been logged out successfully.', 'success');
            window.location.href = `${APP_BASE_URL}/login`; // Redirect to login page
        });

        // Helper function for token validation and storage
        async function handleTokenAndRedirect(destination, actionName) {
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                showMessage('You need to log in first.', 'error');
                window.location.href = `${APP_BASE_URL}/login`;
                return false;
            }

            try {
                showMessage(`Loading ${actionName} page...`, 'info');
                
                const response = await fetch(`${APP_BASE_URL}/api/auth/store-token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ token: token })
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    console.error(`Error storing token before ${actionName} redirect:`, errorData);
                    showMessage(`Error accessing ${actionName}. Please try logging in again.`, 'error');
                    return false;
                }
                
                window.location.href = destination;
                return true;
            } catch (error) {
                console.error(`Error accessing ${actionName}:`, error);
                showMessage(`An error occurred while accessing ${actionName}.`, 'error');
                return false;
            }
        }

        // Preblock button handler
        document.getElementById('preblockButton').addEventListener('click', () => {
            handleTokenAndRedirect(`${APP_BASE_URL}/api/preblock`, 'Preblock');
        });

        // Preblock Visit button handler
        document.getElementById('preblockVisitButton').addEventListener('click', () => {
            handleTokenAndRedirect(`${APP_BASE_URL}/api/preblock-visit`, 'Preblock Visit');
        });

        // Report Customer button handler
        document.getElementById('reportCustomerButton').addEventListener('click', () => {
            handleTokenAndRedirect(`${APP_BASE_URL}/api/report-customer`, 'Report Customer');
        });
    </script>
</body>
</html>
