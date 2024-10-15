<?php
$username = "ppl"; // ชื่อผู้ใช้ที่กำหนดไว้
$password = "151143"; // รหัสผ่านที่กำหนดไว้
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำถาม</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- เพิ่มการเชื่อมต่อกับฟอนต์ Noto Sans Thai จาก Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* กำหนดฟอนต์ Noto Sans Thai ให้กับคลาส t1 */
        .t1 {
            font-family: 'Noto Sans Thai', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 t1">

    <script>
        // ฟังก์ชันสำหรับแสดงป๊อปอัพล็อกอิน
        function showLoginPopup() {
            Swal.fire({
                title: 'ล็อกอิน',
                html: `
                    <input type="text" id="username" class="swal2-input" placeholder="ชื่อผู้ใช้">
                    <input type="password" id="password" class="swal2-input" placeholder="รหัสผ่าน">
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const username = Swal.getPopup().querySelector('#username').value;
                    const password = Swal.getPopup().querySelector('#password').value;

                    // ตรวจสอบชื่อผู้ใช้และรหัสผ่าน
                    if (username !== "<?php echo $username; ?>" || password !== "<?php echo $password; ?>") {
                        Swal.showValidationMessage(`ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง`);
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    // หากล็อกอินไม่สำเร็จ ให้แสดงป๊อปอัพล็อกอินอีกครั้ง
                    showLoginPopup();
                }
            });
        }

        $(document).ready(function() {
            // เรียกฟังก์ชันล็อกอินเมื่อโหลดหน้าเว็บ
            showLoginPopup();
        });
    </script>

    <div class="container mx-auto mt-8">
        <h1 class="text-3xl font-bold text-center mb-6">จัดการคำถาม</h1>

        <table id="questions-table" class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="w-1/10 py-3 px-4">ID</th>
                    <th class="w-1/5 py-3 px-4">คำถาม</th>
                    <th class="w-1/2 py-3 px-4">คำตอบ</th>
                    <th class="w-1/6 py-3 px-4">การจัดการ</th>
                </tr>
            </thead>

            <tbody>
            </tbody>
        </table>

        <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6 mt-8">
            <h2 class="text-2xl font-semibold mb-6">เพิ่มคำถามใหม่</h2>
            <div class="form-group mb-4">
                <label for="new-question" class="block text-lg font-medium">คำถาม:</label>
                <input type="text" id="new-question" class="mt-1 p-2 w-full border rounded" required>
            </div>
            <div class="form-group mb-4">
                <label for="new-answer" class="block text-lg font-medium">คำตอบ:</label>
                <textarea id="new-answer" class="mt-1 p-2 w-full border rounded" required></textarea>
            </div>
            <center>
                <button id="add-question" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    เพิ่มคำถาม
                </button>
            </center>
        </div>
        <br>
    </div>

    <script>
        function loadQuestions() {
            $.ajax({
                url: "http://localhost:5000/questions",
                method: "GET",
                success: function(data) {
                    let rows = '';
                    data.forEach(question => {
                        rows += `
                            <tr>
                                <td class="border px-4 py-2 text-center">${question.id}</td>
                                <td class="border px-4 py-2"><input type="text" class="edit-question border rounded w-full" data-id="${question.id}" value="${question.question}"></td>
                                <td class="border px-4 py-2"><textarea class="edit-answer border rounded w-full" data-id="${question.id}">${question.answer}</textarea></td>
                                <td class="border px-4 py-2 text-center">
                                    <button class="save-btn bg-green-500 hover:bg-green-700 text-white py-1 px-3 rounded" data-id="${question.id}">บันทึก</button>
                                    <button class="delete-btn bg-red-500 hover:bg-red-700 text-white py-1 px-3 rounded" data-id="${question.id}">ลบ</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#questions-table tbody').html(rows);
                }
            });
        }

        $(document).ready(function() {
            loadQuestions();

            // Add new question
            $('#add-question').on('click', function() {
                const newQuestion = $('#new-question').val();
                const newAnswer = $('#new-answer').val();

                $.ajax({
                    url: "http://localhost:5000/questions",
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        question: newQuestion,
                        answer: newAnswer
                    }),
                    success: function(data) {
                        loadQuestions();
                        $('#new-question').val('');
                        $('#new-answer').val('');
                        Swal.fire({
                            icon: 'success',
                            title: 'เพิ่มสำเร็จ!',
                            text: 'คำถามใหม่ของคุณถูกเพิ่มเรียบร้อยแล้ว',
                            confirmButtonColor: '#3085d6',
                            timer: 2000
                        });
                    }
                });
            });

            // Delete question
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `http://localhost:5000/questions/${id}`,
                    method: "DELETE",
                    success: function(data) {
                        loadQuestions();
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ!',
                            text: 'คำถามนี้ถูกลบเรียบร้อยแล้ว',
                            confirmButtonColor: '#3085d6',
                            timer: 2000
                        });
                    }
                });
            });

            // Save updated question and answer
            $(document).on('click', '.save-btn', function() {
                const id = $(this).data('id');
                const updatedQuestion = $(`.edit-question[data-id=${id}]`).val();
                const updatedAnswer = $(`.edit-answer[data-id=${id}]`).val();

                $.ajax({
                    url: `http://localhost:5000/questions/${id}`,
                    method: "PUT",
                    contentType: "application/json",
                    data: JSON.stringify({
                        question: updatedQuestion,
                        answer: updatedAnswer
                    }),
                    success: function(data) {
                        loadQuestions();
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกแล้ว',
                            confirmButtonColor: '#3085d6',
                            timer: 2000
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>
