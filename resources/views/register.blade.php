<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Light gray background */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Register</h2>

        <form id="registerForm" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
            </div>
            <button type="submit"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out font-semibold text-lg shadow-md">
                Register
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="/login" class="font-medium text-green-600 hover:text-green-500">Login here</a>
        </p>

        <!-- Message Box -->
        <div id="messageBox" class="hidden mt-4 p-3 rounded-md text-sm text-center" role="alert"></div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;
            const messageBox = document.getElementById('messageBox');

            messageBox.classList.add('hidden'); // Hide previous messages
            messageBox.textContent = '';
            messageBox.classList.remove('bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');

            try {
                const response = await fetch('/api/auth/register', { // Adjust API endpoint as needed
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, email, password, password_confirmation })
                });

                const data = await response.json();

                if (response.ok) {
                    // Store the JWT token (e.g., in localStorage)
                    localStorage.setItem('jwt_token', data.token);
                    messageBox.classList.remove('hidden');
                    messageBox.classList.add('bg-green-100', 'text-green-700');
                    messageBox.textContent = data.message || 'Registration successful! Redirecting to dashboard...';
                    // Redirect to dashboard or home page
                    window.location.href = '/dashboard';
                } else {
                    messageBox.classList.remove('hidden');
                    messageBox.classList.add('bg-red-100', 'text-red-700');
                    // Display validation errors if available
                    if (data.errors) {
                        let errorMessages = Object.values(data.errors).flat().join('<br>');
                        messageBox.innerHTML = errorMessages;
                    } else {
                        messageBox.textContent = data.message || 'Registration failed. Please try again.';
                    }
                }
            } catch (error) {
                console.error('Error during registration:', error);
                messageBox.classList.remove('hidden');
                messageBox.classList.add('bg-red-100', 'text-red-700');
                messageBox.textContent = 'An error occurred. Please try again later.';
            }
        });
    </script>
</body>
</html>
