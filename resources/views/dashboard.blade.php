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

            <div class="mb-6">
                <button id="preblockButton" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out font-semibold shadow-sm">
                    Go to Preblock
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
                window.location.href = '/login';
                return;
            }
            // Fetch user data
            try {
                const userResponse = await fetch('/api/me', { // Adjust API endpoint as needed
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
                    window.location.href = '/login';
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
                showMessage('An error occurred while fetching user data.', 'error');
                localStorage.removeItem('jwt_token');
                window.location.href = '/login';
            }

            console.log("Dashboard loaded successfully");
        });

        // Logout functionality
        document.getElementById('logoutButton').addEventListener('click', function() {
            localStorage.removeItem('jwt_token'); // Remove the token from local storage
            showMessage('You have been logged out successfully.', 'success');
            window.location.href = '/login'; // Redirect to login page
        });

        // Preblock button functionality
        document.getElementById('preblockButton').addEventListener('click', async function() {
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                showMessage('You need to log in first.', 'error');
                window.location.href = '/login';
                return;
            }

            try {
                showMessage('Loading Preblock page...', 'info');
                
                // Make a fetch request to the preblock endpoint with the JWT token
                const response = await fetch('/api/preblock', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'text/html,application/xhtml+xml'
                    }
                });
                
                if (response.ok) {
                    // If response is successful, redirect to preblock page
                    window.location.href = 'api/preblock';
                } else {
                    // If there's an error, show the error message
                    const errorData = await response.json().catch(() => ({ message: 'Failed to load Preblock page' }));
                    showMessage(errorData.message || 'Failed to load Preblock page', 'error');
                }
            } catch (error) {
                console.error('Error accessing Preblock:', error);
                showMessage('An error occurred while accessing Preblock.', 'error');
            }
        });
    </script>
</body>
</html>
