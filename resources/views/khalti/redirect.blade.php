<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to khalti...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .loading {
            text-align: center;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="loading">
        <p>Redirecting to khalti... Please wait.</p>
        <form action="{{ $khaltiUrl }}/api/v2/epayment/initiate/" method="POST" id="khaltiForm">
            @foreach ($paymentData as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn btn-primary">Proceed to khalti</button>
        </form>
        <p class="error" id="error" style="display: none;">Failed to redirect. Please try again or contact support.</p>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('khaltiForm');
            try {
                form.submit();
            } catch (error) {
                document.getElementById('error').style.display = 'block';
                console.error('Form submission failed:', error);
            }
        });
    </script>
</body>
</html>