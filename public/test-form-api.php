<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form API Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        input[type="email"],
        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }
        .response.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .response.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Form API Test</h1>
        <p style="color: #666; margin-bottom: 20px;">Test form for Form API endpoint</p>

        <form id="testForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="menu_id">Menu ID:</label>
                <input type="number" id="menu_id" name="menu_id" value="2" required>
                <small style="color: #999;">Enter menu ID (default: 2)</small>
            </div>

            <div class="form-group">
                <label for="label_id_1">Label ID 1 (Email):</label>
                <input type="number" id="label_id_1" name="labels_data[0][label_id]" value="1" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="labels_data[0][value]" value="test@example.com" required>
            </div>

            <div class="form-group">
                <label for="label_id_2">Label ID 2 (Phone):</label>
                <input type="number" id="label_id_2" name="labels_data[1][label_id]" value="2" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="labels_data[1][value]" value="+994501234567" required>
            </div>

            <div class="form-group">
                <label for="label_id_3">Label ID 3 (File):</label>
                <input type="number" id="label_id_3" name="labels_data[2][label_id]" value="5" required>
            </div>

            <div class="form-group">
                <label for="file">File Upload:</label>
                <input type="file" id="file" name="labels_data[2][value]" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                <small style="color: #999;">Select a file to upload (optional)</small>
            </div>

            <button type="submit">Send Request 🚀</button>
        </form>

        <div id="response" class="response"></div>
    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const responseDiv = document.getElementById('response');
            const menuId = document.getElementById('menu_id').value;
            const formData = new FormData(this);

            // Remove menu_id from formData as it goes in URL
            formData.delete('menu_id');

            // DEBUG: Log FormData contents
            console.log('=== FormData Contents ===');
            for (let pair of formData.entries()) {
                console.log(pair[0], ':', pair[1]);
            }
            console.log('========================');

            responseDiv.style.display = 'block';
            responseDiv.className = 'response';
            responseDiv.innerHTML = '⏳ Sending request...';

            try {
                const response = await fetch(`/api/v1/form/${menuId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-API-Nonce': generateUUID(),
                        'X-API-Timestamp': Math.floor(Date.now() / 1000)
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    responseDiv.className = 'response success';
                    responseDiv.innerHTML = `
                        <strong>✅ Success!</strong>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    responseDiv.className = 'response error';
                    responseDiv.innerHTML = `
                        <strong>❌ Error (${response.status})</strong>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
            } catch (error) {
                responseDiv.className = 'response error';
                responseDiv.innerHTML = `
                    <strong>❌ Network Error</strong>
                    <pre>${error.message}</pre>
                `;
            }
        });

        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
    </script>
</body>
</html>
