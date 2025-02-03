<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Details</title>
    <script src="https://js.lahza.io/inline.min.js"></script>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        /* Container */
        .container {
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Logo & Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
        }

        .logo {
            height: 70px;
            width: auto;
        }

        .customer-details {
            text-align: right;
        }

        /* Pay Button */
        .btn-pay {
            display: block;
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 15px;
        }
        .btn-pay:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Subscription Details Container -->
    <div class="container">
        <!-- Logo & Customer Info -->
        <div class="customer-info">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            <div class="customer-details">
                <h3>{{ $customer->name }}</h3>
                <p><strong>Email:</strong> {{ $customer->email }}</p>
            </div>
        </div>

        <!-- Subscription Details -->
        <h2>Subscription Details</h2>

        @if($subscription)
            <p><strong>Name:</strong> {{ $subscription->name }}</p>
            <p><strong>Price:</strong> {{ number_format($subscription->price, 2) }} ILS</p>

            <!-- Payment Form -->
            <form id="paymentForm">
                <input type="hidden" id="email" value="{{ $customer->email }}" />
                <input type="hidden" id="amount" value="{{ $subscription->price }}" />
                <input type="hidden" id="customerId" value="{{ $customer->id }}" />
                <input type="hidden" id="authorization_code" name="authorization_code" />
                <button type="submit" class="btn-pay">Pay Now</button>
            </form>
        @else
            <p>No active subscription found.</p>
        @endif
    </div>

    <!-- Payment Script -->
    <script>
        document.getElementById("paymentForm")?.addEventListener("submit", function (e) {
            e.preventDefault();

            const reference = "{{ $reference }}";
            const customerId = document.getElementById("customerId").value;
            const lahza = new LahzaPopup();

            lahza.newTransaction({
                key: 'pk_test_NkiI4AG4Ut6SkJx8IFzbCE3YD8mAF3did',
                email: document.getElementById("email").value,
                amount: document.getElementById("amount").value * 100,
                currency: "ILS",
                reference: reference,
                onSuccess: async function(response) {
                    try {
                        const verifyResponse = await fetch(`https://api.lahza.io/transaction/verify/${reference}`, {
                            method: 'GET',
                            headers: {
                                'authorization': `Bearer sk_test_BcCjJ5PLGNxjG6sGgtsSw1rYl3K86AlzA`
                            }
                        });

                        const verifyData = await verifyResponse.json();

                        if (!verifyResponse.ok) {
                            throw new Error(verifyData.message || "Failed to verify transaction");
                        }

                        // Step 2: Send the authorization_code to your server
                        const payResponse = await fetch(`/subscriptions/${customerId}/pay`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                reference: reference,
                                authorization_code: verifyData.data.authorization.authorization_code
                            })
                        });

                        const payData = await payResponse.json();

                        if (payData.message === "Subscription marked as paid") {
                            window.location.href = "/subscription-success";
                        } else {
                            alert("Payment update failed: " + payData.message);
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        alert("Payment verification failed. Please try again.");
                    }
                },
                onError: function(error) {
                    alert("Payment failed. Please try again.");
                }
            });
        });
    </script>
</body>
</html>
