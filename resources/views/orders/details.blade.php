<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <script src="https://js.lahza.io/inline.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; }
        .order-details, .products { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .btn-pay { display: block; width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .btn-pay:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order Details</h2>

        <div class="order-details">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> {{ $order->customer->name }}</p>
            <p><strong>Address:</strong> {{ $order->customer->address }}</p>
            <p><strong>Phone:</strong> {{ $order->customer->phone }}</p>
        </div>

        <div class="products">
            <h3>Products</h3>
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
                            <td>${{ number_format($product->pivot->price, 2) }}</td>
                            <td>${{ number_format($product->pivot->quantity * $product->pivot->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3>Total Price: ${{ number_format($order->total_price, 2) }}</h3>

        <form id="paymentForm">
            <input type="hidden" id="email" value="{{ $order->customer->email }}" />
            <input type="hidden" id="amount" value="{{ $order->total_price }}" />
            <button type="submit" class="btn-pay">Pay Now</button>
        </form>
    </div>

    <script>
        document.getElementById("paymentForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const lahza = new LahzaPopup();
            lahza.newTransaction({
                key: 'pk_test_NkiI4AG4Ut6SkJx8IFzbCE3YD8mAF3did', // Replace with your Lahza public key
                email: document.getElementById("email").value,
                amount: document.getElementById("amount").value * 100, // Amount in cents
                currency: "USD", // Set desired currency
                onSuccess: (transaction) => {
                    window.location.href = "/payment-success";
                },
                onCancel: () => {
                    alert('Payment was canceled.');
                }
            });
        });
    </script>
</body>
</html>
