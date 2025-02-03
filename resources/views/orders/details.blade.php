<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
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

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
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

    <!-- Order Details Container -->
    <div class="container">
        <!-- Logo & Customer Info -->
        <div class="customer-info">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            <div class="customer-details">
                <h3>{{ $order->customer->name }}</h3>
                <p><strong>Address:</strong> {{ $order->customer->address }}</p>
                <p><strong>Phone:</strong> {{ $order->customer->phone_number }}</p>
            </div>
        </div>

        <!-- Order Details -->
        <h2>Order Details</h2>

        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->pivot->quantity }}</td>
                        <td>{{ number_format($product->pivot->price, 2) }} ILS</td>
                        <td>{{ number_format($product->pivot->quantity * $product->pivot->price, 2) }} ILS</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Price -->
        <h3>Total Price: {{ number_format($order->total_price, 2) }} ILS</h3>

        <!-- Payment Form -->
        <form id="paymentForm">
            <input type="hidden" id="email" value="{{ $order->customer->email }}" />
            <input type="hidden" id="amount" value="{{ $order->total_price }}" />
            <button type="submit" class="btn-pay">Pay Now</button>
        </form>
    </div>

    <!-- Payment Script -->
    <script>
        document.getElementById("paymentForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const orderId = "{{ $order->id }}";
            const lahza = new LahzaPopup();

            lahza.newTransaction({
                key: 'pk_test_NkiI4AG4Ut6SkJx8IFzbCE3YD8mAF3did',
                email: document.getElementById("email").value,
                amount: document.getElementById("amount").value * 100,
                currency: "ILS",
                onSuccess: function(response) {
                    fetch(`/orders/${orderId}/pay`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ transaction_id: response.transaction_id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.message === "Order marked as paid") {
                            window.location.href = "/payment-success";
                        } else {
                            alert("Payment update failed: " + data.message);
                        }
                    })
                    .catch(err => console.error("Payment update error:", err));
                },
                onError: function(error) {
                    alert("Payment failed. Please try again.");
                }
            });
        });
    </script>

</body>
</html>
