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

        /* Subscription List */
        .subscription-list {
            margin-bottom: 20px;
            padding: 15px;
            background: #f1f1f1;
            border-radius: 5px;
        }

        .subscription-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .subscription-item:last-child {
            border-bottom: none;
        }

        /* Total Price */
        .total-price {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
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

        <!-- Subscription List -->
        <h2>Subscription Details</h2>

        @if($subscriptions->isNotEmpty())
            <div class="subscription-list">
                @foreach($subscriptions as $subscription)
                    <div class="subscription-item">
                        <span><strong>{{ $subscription->name }}</strong></span>
                        <span>{{ number_format($subscription->price, 2) }} ILS</span>
                    </div>
                @endforeach
            </div>

            <!-- Total Price -->
            <div class="total-price">
                Total: {{ number_format($subscriptions->sum('price'), 2) }} ILS
            </div>

            <!-- Payment Form -->
            <form id="paymentForm">
                <input type="hidden" id="email" value="{{ $customer->email }}" />
                <input type="hidden" id="amount" value="{{ $subscriptions->sum('price') }}" />
                <input type="hidden" id="customerId" value="{{ $customer->id }}" />
                <input type="hidden" id="authorization_code" name="authorization_code" />
                <button type="submit" class="btn-pay">Pay Now</button>
            </form>
        @else
            <p>No active subscriptions found.</p>
        @endif
    </div>

    <!-- Payment Script -->
    <script>
        document.getElementById("paymentForm")?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const reference = "{{ $reference }}"; // Use the first reference
            const customerId = document.getElementById("customerId").value;
            const subscriptionIds = @json($subscriptions->pluck('id')); // Get all subscription IDs

            const lahza = new LahzaPopup();
            lahza.newTransaction({
                key: "{{ env('LAHZA_PUBLIC_KEY') }}",
                email: document.getElementById("email").value,
                amount: document.getElementById("amount").value * 100,
                currency: "ILS",
                reference: reference,
                onSuccess: async function(response) {
                    try {
                        const verifyResponse = await fetch(`https://api.lahza.io/transaction/verify/${reference}`, {
                            method: 'GET',
                            headers: {
                                'authorization': `Bearer {{ env('LAHZA_SECRET_KEY') }}`
                            }
                        });

                        const verifyData = await verifyResponse.json();

                        if (!verifyResponse.ok) {
                            throw new Error(verifyData.message || "Failed to verify transaction");
                        }

                        // Step 2: Send the subscription IDs and authorization code to the server
                        const payResponse = await fetch(`/subscriptions/${customerId}/pay`, {
                            method: "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                authorization_code: verifyData.data.authorization.authorization_code
                            })
                        });

                        const payData = await payResponse.json();

                        if (payData.message === "Subscriptions marked as paid") {
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
