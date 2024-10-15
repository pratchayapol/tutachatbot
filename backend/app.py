# ไลบรารีที่โปรเจกต์ต้องใช้
from flask import Flask, request, jsonify
from flask_cors import CORS
from sklearn.metrics.pairwise import cosine_similarity
from sentence_transformers import SentenceTransformer
import re

app = Flask(__name__)
CORS(app)



# ข้อมูลคำถามและคำตอบ (คำถาม-คำตอบ)
data = [
    {"id": 1, "question": "สวัสดี", "answer": "ยินดีต้อนรับการใช้งาน TuTaBot ถามตอบเกี่ยวกับข้อมูลของคุณปรัชญาพล ค่ะ "},
    {"id": 2, "question": "hello", "answer": "ยินดีต้อนรับการใช้งาน TuTaBot ถามตอบเกี่ยวกับข้อมูลของคุณปรัชญาพล ค่ะ "},
    {"id": 3, "question": "ประวัติ", "answer": "ประวัติ ชื่อนายปรัชญาพล จำปาลาด (ชื่อเล่น ต๊ะ) ค่ะ"},
    {"id": 4, "question": "หมายเลขโทรศัพท์", "answer": "0909691701"},
    {"id": 5, "question": "ติดต่อ", "answer": "0909691701 email: pratchayapol2543@gmail.com"},
    {"id": 6, "question": "แฟน", "answer": "ไม่สามารถตอบได้ ต้องถามส่วนตัวนะ ค่ะ อิ ๆ"},
    {"id": 7, "question": "เกิด", "answer": "เกิดเมื่อ วันพุธ ที่ ๑๕ เดือน พฤศจิกายน พ.ศ.๒๕๔๓ ค่ะ"},
    {"id": 8, "question": "คุณเกิดเมื่อไหร่", "answer": "เกิดเมื่อ วันพุธ ที่ ๑๕ เดือน พฤศจิกายน พ.ศ.๒๕๔๓ ค่ะ"},
    {"id": 9, "question": "ที่อยู่", "answer": "ที่อยู่ตามทะเบียนบ้าน คือ 10 / 3 ตำบลโคกกกม่วง อำเภอโพนทอง จังหวัดร้อยเอ็ด 45110 ค่ะ"},
    {"id": 10, "question": "อาศัยอยู่ที่ไหน", "answer": "ที่อยู่ตามทะเบียนบ้าน คือ 10 / 3 ตำบลโคกกกม่วง อำเภอโพนทอง จังหวัดร้อยเอ็ด 45110 ค่ะ"},
    {"id": 11, "question": "การศึกษา", "answer": "กำลังศึกษาในระดับปริญญาตรี สาขาวิศวกรรมคอมพิวเตอร์ คณะวิศวกรรมศาสตร์ มหาวิทยาลัยเทศโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น ค่ะ"},
    {"id": 12, "question": "เรียนอะไร", "answer": "กำลังศึกษาในระดับปริญญาตรี สาขาวิศวกรรมคอมพิวเตอร์ คณะวิศวกรรมศาสตร์ มหาวิทยาลัยเทศโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น ค่ะ"},
    {"id": 13, "question": "ประวัติการศึกษา", "answer": "ศูนย์เด็กเล็กประจำตำบลโคกกกม่วง 2547 --> ระดับชั้นอนุบาลปีที่ ๑ – ๒ จากโรงเรียนมหาชนะชัย 2548 - 2549 --> ระดับชั้นประถมศึกษาปีที่ ๑ ที่โรงเรียนบ้านพรหมจรรย์ 2550 --> ระดับชั้นประถมศึกษาปีที่ ๒ -๓ โรงเรียนมหาชนะชัย 2551 – 2552 --> ระดับชั้นประถมศึกษาปีที่ ๔ – ๖ โรงเรียนบ้านโคกกกม่วง 2553 – 2555 --> ระดับชั้นมัธยมศึกษาปีที่ ๑ -๓ โรงเรียนม่วงมิตรวิทยาคม 2556 – 2558 --> ระดับชั้นมัธยมศึกศาปีที่ ๔ – ๖ โรงเรียนม่วงมิตรวิทยาคม 2559 – 2561 --> ระดับประกาศนียบัตรวิชาชีพสูง สาขาวิชาช่างไฟฟ้ากำลัง ที่วิทยาลัยการอาชีพโพนทอง 2562 – 2563 ค่ะ"},
    {"id": 14, "question": "อาชีพ", "answer": "ไม่มีอาชีพเพราะกำลังเรียนอยู่ ค่ะ"},
    {"id": 15, "question": "สนใจ", "answer": "สนใจด้านเทคโนโลยีและการพัฒนาโปรแกรม ค่ะ"},
    {"id": 16, "question": "งานอดิเรก", "answer": "งานอดิเรกเป็นฟรีแลนต์ เขียนโปรแกรม PHP ค่ะ"},
    {"id": 17, "question": "สัตว์เลี้ยง", "answer": "มีสัตว์เลี้ยงเป็น แมวหนึ่งตัว ชื่อ ป้ำ ค่ะ"},
    {"id": 18, "question": "ชอบกินอะไร", "answer": "จากข้อมูลที่มี คุณปรัชญาพล ชอบกิน ข้าวผัด และ กระเพราหมูกรอบ ค่ะ"},
    {"id": 19, "question": "อาหารที่ชอบ", "answer": "อาหารที่ชอบคือ ข้าวผัด และ กระเพราหมูกรอบ ค่ะ"}
]


# การตั้งค่าและจัดการ embedding สำหรับการเปรียบเทียบความคล้ายคลึงของคำถาม
# ตั้งค่า ID สำหรับคำถามใหม่
next_id = len(data) + 1

# ฟังก์ชันอัพเดต embedding สำหรับชุดข้อมูลคำถาม
def update_embeddings():
    global question_embeddings
    questions = [item["question"] for item in data]
    question_embeddings = model_embedding.encode(questions)

# โหลดโมเดลสำหรับทำ sentence embedding
model_embedding = SentenceTransformer('paraphrase-multilingual-mpnet-base-v2')

# สร้าง embedding ของคำถามในชุดข้อมูลเดิม
update_embeddings()



# ฟังก์ชันสำหรับการจับคู่คำถามโดยใช้ cosine similarity โคไซน์ ซิมิลาริตี้
# def get_answer_by_similarity(user_question):
#     try:
#         if not user_question.strip():
#             return "กรุณาพิมพ์คำถามของคุณ"
#         if re.match(r'^[ก-ฮ]{1,2}$', user_question) or re.match(r'^[a-zA-Z\s]{1,2}$', user_question):
#             return "กรุณาถามเป็นภาษามนุษย์ให้ฉันเข้าใจหน่อย !!"
#         if re.match(r'^[เแไใาาำะาิีึืุู็่้๊๋์ๅ]+$', user_question):
#             return "กรุณาอย่าพิมพ์เฉพาะสระหรือวรรณยุกต์เท่านั้น"
#         if re.search(r'[!@#$%^&*(),.?":{}|<>]', user_question):
#             return "กรุณาอย่าใช้อักขระพิเศษในคำถามของคุณ"
#         if re.search(r'[เแโใไ]+[่้๊๋]', user_question):
#             return "กรุณาอย่าพิมพ์สระไทยที่ไม่ถูกต้อง"

#         user_question_embedding = model_embedding.encode([user_question])
#         similarities = cosine_similarity(user_question_embedding, question_embeddings)
#         most_similar_index = similarities.argmax()

#         threshold = 0.7
#         if similarities[0][most_similar_index] < threshold:
#             return "ขออภัย ไม่ทราบคำตอบค่ะ"
#         return data[most_similar_index]["answer"]
#     except Exception as e:
#         return f"เกิดข้อผิดพลาด: {e}"

def get_answer_by_similarity(user_question):
    try:
        if not user_question.strip():
            return "กรุณาพิมพ์คำถามของคุณ"
        if re.match(r'^[ก-ฮ]{1,2}$', user_question) or re.match(r'^[a-zA-Z\s]{1,2}$', user_question):
            return "กรุณาถามเป็นภาษามนุษย์ให้ฉันเข้าใจหน่อย !!"
        if re.match(r'^[เแไใาาำะาิีึืุู็่้๊๋์ๅ]+$', user_question):
            return "กรุณาอย่าพิมพ์เฉพาะสระหรือวรรณยุกต์เท่านั้น"
        if re.search(r'[!@#$%^&*(),.?":{}|<>]', user_question):
            return "กรุณาอย่าใช้อักขระพิเศษในคำถามของคุณ"
        if re.search(r'[เแโใไ]+[่้๊๋]', user_question):
            return "กรุณาอย่าพิมพ์สระไทยที่ไม่ถูกต้อง"

        # คำนวณ embedding ของคำถามที่ผู้ใช้ป้อนมา
        user_question_embedding = model_embedding.encode([user_question])
        similarities = cosine_similarity(user_question_embedding, question_embeddings)[0]  # รับผลลัพธ์เป็น array
        
        # หาค่าเฉลี่ยของความคล้ายคลึง
        avg_similarity = similarities.mean()
        
        # หาคำถามที่คล้ายที่สุด
        most_similar_index = similarities.argmax()

        # เกณฑ์ในการตัดสินว่าจะตอบคำถามหรือไม่
        threshold = 0.7
        if similarities[most_similar_index] < threshold:
            return f"ขออภัย ไม่ทราบคำตอบค่ะ (ค่าเฉลี่ยของความคล้ายคลึงคือ {avg_similarity:.2f}) (ค่าเกณฑ์ในการตัดสินว่าจะตอบคำถาม {similarities[most_similar_index]:.2f} < 0.7)"
        
        # ตอบคำถามและส่งค่าเฉลี่ยความคล้ายคลึงกลับมาด้วย
        return f"{data[most_similar_index]['answer']} (ค่าเฉลี่ยของความคล้ายคลึงคือ {avg_similarity:.2f}) (ค่าเกณฑ์ในการตัดสินว่าจะตอบคำถาม {similarities[most_similar_index]:.2f} > 0.7)"
    
    except Exception as e:
        return f"เกิดข้อผิดพลาด: {e}"



# ส่วนของ API 
# Endpoint สำหรับรับคำถามจากผู้ใช้ (POST)
@app.route('/ask', methods=['POST'])
def ask():
    if request.method == 'POST':
        data = request.json
        if not data or 'question' not in data:
            return jsonify({'answer': 'กรุณาส่งคำถามในรูปแบบที่ถูกต้อง'}), 400
        user_question = data.get('question', '')
        answer = get_answer_by_similarity(user_question)
        return jsonify({'answer': answer})
    else:
        return jsonify({'error': 'Method Not Allowed'}), 405


# Endpoint สำหรับแสดงคำถามทั้งหมด
@app.route('/questions', methods=['GET'])
def get_questions():
    return jsonify(data)

# Endpoint สำหรับเพิ่มคำถามใหม่
@app.route('/questions', methods=['POST'])
def add_question():
    global next_id
    new_question = request.json
    if 'question' not in new_question or 'answer' not in new_question:
        return jsonify({'error': 'Invalid input'}), 400
    new_question['id'] = next_id
    next_id += 1
    data.append(new_question)
    update_embeddings()  # อัพเดต embedding
    return jsonify(new_question), 201

# Endpoint สำหรับแก้ไขคำถาม
@app.route('/questions/<int:id>', methods=['PUT'])
def update_question(id):
    question = next((item for item in data if item['id'] == id), None)
    if not question:
        return jsonify({'error': 'Question not found'}), 404
    updated_data = request.json
    if 'question' in updated_data:
        question['question'] = updated_data['question']
    if 'answer' in updated_data:
        question['answer'] = updated_data['answer']
    update_embeddings()  # อัพเดต embedding
    return jsonify(question)

# Endpoint สำหรับลบคำถาม
@app.route('/questions/<int:id>', methods=['DELETE'])
def delete_question(id):
    global data
    question = next((item for item in data if item['id'] == id), None)
    if not question:
        return jsonify({'error': 'Question not found'}), 404
    data = [item for item in data if item['id'] != id]
    update_embeddings()  # อัพเดต embedding
    return jsonify({'message': 'Question deleted successfully'}), 200

# เปิด cors เพื่อให้ทุก ip สามารถเข้าถึง api ได้
if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)
