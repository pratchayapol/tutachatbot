<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- เพิ่มการเชื่อมต่อกับฟอนต์ Noto Sans Thai จาก Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* กำหนดฟอนต์ Noto Sans Thai ให้กับคลาส t1 */
        .t1 { 
            font-family: 'Noto Sans Thai', sans-serif; 
        }
        /* สไตล์สำหรับ Dark Mode */
        .dark-mode {
            background-color: #1a202c; /* Tailwind Gray-900 */
            color: #e2e8f0; /* Tailwind Gray-200 */
        }
        .dark-mode input, .dark-mode button, .dark-mode .bg-white {
            background-color: #2d3748; /* Tailwind Gray-800 */
            color: #e2e8f0; /* Tailwind Gray-200 */
        }
        .dark-mode .bg-gray-50 {
            background-color: #4a5568; /* Tailwind Gray-600 */
        }
    </style>
</head>
<body class="bg-gray-100 t1" id="main-body"> <!-- เพิ่ม ID เพื่อใช้สลับโหมด -->
    <div class="container mx-auto mt-10">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold mb-4">TuTaBot Version 2.0 ถามข้อมูลเกี่ยวกับปรัชญาพล</h1>
                <!-- ปุ่มสำหรับสลับโหมด -->
                <button id="toggle-dark-mode" class="bg-gray-500 text-white rounded p-2">Dark Mode</button>
            </div>

            <div id="chatbox" class="border p-4 h-64 overflow-y-auto bg-gray-50 mb-4">
                <!-- จะเพิ่มข้อความตรงนี้ -->
            </div>

            <form id="chat-form" class="flex">
                <input type="text" id="user-input" class="border rounded-l-lg p-2 w-full" placeholder="พิมพ์คำถามที่นี่...">
                <button type="submit" class="bg-blue-500 text-white rounded-r-lg p-2">ส่ง</button>
            </form><br>
            <center>
                <a href="/setting.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    ไปที่การตั้งค่า
</a>
            </center>
            
        </div>
        

    </div>

    <script>
        const form = document.getElementById('chat-form');
        const input = document.getElementById('user-input');
        const chatbox = document.getElementById('chatbox');
        const toggleDarkMode = document.getElementById('toggle-dark-mode');
        const mainBody = document.getElementById('main-body');
        
        // สลับ Dark Mode
        toggleDarkMode.addEventListener('click', function() {
            mainBody.classList.toggle('dark-mode');
            if (mainBody.classList.contains('dark-mode')) {
                toggleDarkMode.textContent = 'Light Mode'; // เปลี่ยนข้อความปุ่มเป็น Light Mode
            } else {
                toggleDarkMode.textContent = 'Dark Mode'; // เปลี่ยนกลับเป็น Dark Mode
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const userQuestion = input.value;
            if (userQuestion.trim() === "") {
                return;
            }

            // แสดงคำถามของผู้ใช้ใน chatbox (ข้อความอยู่ทางขวา)
            const userMessage = `<div class="text-right"><p class="text-blue-500 bg-blue-100 inline-block p-2 rounded-lg ml-auto"><strong>คุณ:</strong> ${userQuestion}</p></div>`;
            chatbox.innerHTML += userMessage;
            chatbox.scrollTop = chatbox.scrollHeight;

            // ส่งคำถามไปที่ API
            fetch('http://localhost:5000/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ question: userQuestion })
            })
            .then(response => response.json())
            .then(data => {
                // สร้างพื้นที่สำหรับการแสดงผลข้อความของบอท
                const botMessageContainer = document.createElement('div');
                botMessageContainer.classList.add('text-left');
                const botMessage = document.createElement('p');
                botMessage.classList.add('text-green-500', 'bg-green-100', 'inline-block', 'p-2', 'rounded-lg', 'mr-auto');
                botMessage.innerHTML = "<strong>บอท:</strong> ";
                botMessageContainer.appendChild(botMessage);
                chatbox.appendChild(botMessageContainer);
                chatbox.scrollTop = chatbox.scrollHeight;

                // แสดงเอฟเฟคการพิมพ์
                typeEffect(botMessage, data.answer);
            })
            .catch(error => {
                console.error('Error:', error);
            });

            // ล้างช่อง input
            input.value = '';
        });

        // ฟังก์ชันแสดงเอฟเฟคการพิมพ์
        function typeEffect(element, text, speed = 50) {
            let index = 0;
            function typing() {
                if (index < text.length) {
                    element.innerHTML += text.charAt(index);
                    index++;
                    chatbox.scrollTop = chatbox.scrollHeight;  // เลื่อนลงตามการพิมพ์
                    setTimeout(typing, speed);
                }
            }
            typing();
        }
    </script>
</body>
</html>
