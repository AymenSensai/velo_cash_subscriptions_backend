<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Card Container */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Checkmark Icon */
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #48bb78;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            animation: bounce 1s ease-in-out;
        }

        .checkmark img {
            width: 50px;
            height: 50px;
        }

        /* Heading */
        h2 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
        }

        /* Paragraph */
        p {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 20px;
        }

        .btn:hover {
            background-color: #38a169;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <!-- Checkmark Icon -->
        <div class="checkmark">
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Checkmark">
        </div>

        <!-- Heading -->
        <h2>Payment Completed!</h2>

        <!-- Message -->
        <p>Thank you for your purchase. Your payment was successful.</p>
    </div>
</body>
</html>
