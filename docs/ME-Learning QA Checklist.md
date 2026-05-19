# **ME-Learning: Test Cases & QA Checklist**

**Project:** ME-Learning Platform  
**Version:** 1.0  
**Target Roles:** Guest, Learner, Expert, Admin

## **1\. Authentication & User Profile (ระบบสมาชิกและสิทธิ์)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-AUTH-01 | Register with valid ID card | กรอกข้อมูลครบถ้วน โดยระบุเลขบัตรประชาชน 13 หลักที่ถูกต้อง | สมัครสมาชิกสำเร็จ (201 Created) ระบบ Login ให้อัตโนมัติ | \[ \] |
| TC-AUTH-02 | Register with duplicated email/ID | กรอก Email หรือ เลขบัตรประชาชน ที่มีในระบบแล้ว | ระบบแจ้งเตือน "ข้อมูลซ้ำ" และไม่บันทึกข้อมูล | \[ \] |
| TC-AUTH-03 | Role-based Access Control (RBAC) | นำ User (Learner) ไปเข้า URL /admin/\* หรือ /expert/\* | ระบบ Redirect หรือแจ้งเตือน 403 Forbidden | \[ \] |
| TC-AUTH-04 | Reset Password flow | กดลืมรหัสผ่าน \-\> กรอกอีเมล \-\> กดลิงก์ในอีเมล \-\> ตั้งรหัสใหม่ | สามารถ Login ด้วยรหัสผ่านใหม่ได้สำเร็จ | \[ \] |

## **2\. Course Access & Enrollment (สิทธิ์การเข้าถึงและการลงทะเบียน)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-CRS-01 | Public Course Visibility | Guest เข้าหน้า /courses และ /courses/:id | เห็นรายละเอียดหลักสูตร แต่ปุ่มเป็น "เข้าสู่ระบบเพื่อลงทะเบียน" | \[ \] |
| TC-CRS-02 | Course Group Access (Allowed) | Learner (อยู่ใน Group A) เข้าดูหลักสูตรที่เปิดให้ Group A | เห็นปุ่ม "ลงทะเบียนเรียน" และลงทะเบียนได้สำเร็จ | \[ \] |
| TC-CRS-03 | Course Group Access (Denied) | Learner (ไม่อยู่ใน Group A) เข้าดูหลักสูตรที่เปิดให้เฉพาะ Group A | ไม่เห็นหลักสูตรนี้ หรือเห็นแต่ปุ่มถูก Lock (ไม่มีสิทธิ์) | \[ \] |
| TC-CRS-04 | Module Prerequisites (Locked) | Learner พยายามเข้า Module 2 โดยที่ยังเรียน Module 1 (ที่เป็นบังคับ) ไม่จบ | เข้าไม่ได้ ระบบแจ้งเตือน "ต้องผ่าน Module 1 ก่อน" | \[ \] |
| TC-CRS-05 | Module Prerequisites (Unlocked) | Learner เรียนจบ Module 1 ที่เป็นเงื่อนไขบังคับแล้ว ไปกดเข้า Module 2 | เข้าเรียน Module 2 ได้ตามปกติ | \[ \] |

## **3\. Learning Progress (การติดตามการเรียน)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-LRN-01 | Video Resume tracking | ดูวิดีโอถึงนาทีที่ 5 \-\> ปิดหน้าต่าง \-\> กลับเข้ามาเรียนใหม่ | วิดีโอเล่นต่อจากนาทีที่ 5 (last\_position\_sec ทำงาน) | \[ \] |
| TC-LRN-02 | Lesson Completion status | ดูวิดีโอจนจบ (หรือถึงเกณฑ์ % ที่ตั้งไว้) | สถานะ Lesson เปลี่ยนเป็น "Completed" / ติ๊กถูก | \[ \] |
| TC-LRN-03 | Module Completion evaluation | เรียนจบทุก Lesson ภายใน Module นั้นๆ | สถานะ Module เปลี่ยนเป็น "Completed" | \[ \] |

## **4\. Assessments & Grading (แบบทดสอบและการตรวจให้คะแนน)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-ASM-01 | Auto-Grading (MCQ) | ทำข้อสอบปรนัยและกดส่ง | ทราบผลคะแนนทันที (Passed/Failed) ตามเกณฑ์ที่ตั้ง | \[ \] |
| TC-ASM-02 | Max Attempts Quota (3 Stars) | ทำข้อสอบไม่ผ่าน 1 ครั้ง | โควต้าดาวลดลงจาก 3 เหลือ 2 แจ้งเตือนให้ทำใหม่ได้ | \[ \] |
| TC-ASM-03 | Exceed Max Attempts | ทำข้อสอบไม่ผ่านครบ 3 ครั้ง | ปุ่มทำข้อสอบถูก Lock ไม่สามารถทำข้อสอบชุดเดิมได้อีก | \[ \] |
| TC-ASM-04 | Manual Grading (File Upload) | Learner ส่งไฟล์ใบงานแบบ Upload | สถานะแสดง "รอการตรวจ (Pending Review)" | \[ \] |
| TC-ASM-05 | Expert Review Flow | Expert เข้าเมนู "รอตรวจ" \-\> ให้คะแนน \-\> เขียน Feedback \-\> กดผ่าน | สถานะฝั่ง Learner เปลี่ยนเป็น "Passed" \+ แจ้งเตือนทำงาน | \[ \] |
| TC-ASM-06 | Expert Revision Request | Expert ตรวจและเลือกสถานะ "รอแก้ไข (Needs Revision)" | โควต้าของ Learner ลดลง 1 ดาว และปุ่มอัปโหลดเปิดให้ส่งใหม่ | \[ \] |

## **5\. Course Review & Certificate (เงื่อนไขการจบหลักสูตรและเกียรติบัตร)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-CRT-01 | Not met conditions for Cert | Learner เรียนจบ แต่ *ยังไม่ทำ Course Review* | ยังไม่ได้เกียรติบัตร (หน้า Dashboard ยังไม่ขึ้นให้โหลด) | \[ \] |
| TC-CRT-02 | Met ALL conditions for Cert | Learner ผ่านทุก Module(บังคับ) \+ ทุก Assessment(บังคับ) \+ Score \>= 60% \+ ทำ Review | ระบบสร้าง Certificate อัตโนมัติ แสดงปุ่ม Download PDF | \[ \] |
| TC-CRT-03 | Certificate Data Validation | โหลดไฟล์ PDF เกียรติบัตรที่ระบบสร้างออกมาตรวจสอบ | ชื่อ-นามสกุล, ชื่อหลักสูตร, วันที่ออก (Issue Date) ถูกต้องตามเทมเพลต | \[ \] |

## **6\. Admin Backoffice (ระบบจัดการหลังบ้าน)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-ADM-01 | Manage Learner Groups | Admin สร้างกลุ่มผู้เรียนใหม่ และ Add User เข้ากลุ่ม | User ที่ถูก Add สามารถเห็น Course ของกลุ่มนั้นได้ทันที | \[ \] |
| TC-ADM-02 | Course Management | Admin สร้าง Course ใหม่ \-\> เพิ่ม Module \-\> กำหนดเงื่อนไข | Course ปรากฏในระบบตามสิทธิ์ที่ตั้งไว้ | \[ \] |
| TC-ADM-03 | Report Export | Admin เข้าดู Report ผลการเรียน และกดปุ่ม Export (CSV/Excel) | ดาวน์โหลดไฟล์สำเร็จ และข้อมูลในไฟล์ตรงกับ Data จริง | \[ \] |
| TC-ADM-04 | Certificate Template Setup | Admin อัปโหลดภาพ Template และกำหนดพิกัด X,Y ของชื่อผู้เรียน | พิกัดถูกบันทึก และเมื่อออกใบจริง ชื่อตรงตำแหน่งที่ตั้งไว้ | \[ \] |

## **7\. Notifications (ระบบแจ้งเตือน)**

| ID | Test Scenario | Steps / Conditions | Expected Result | Status |
| :---- | :---- | :---- | :---- | :---- |
| TC-NOT-01 | Submission Notification | Learner ส่งใบงาน (Manual Grading) | มีแจ้งเตือนเข้าสู่ระบบ (และ/หรือ Email) ของ Expert | \[ \] |
| TC-NOT-02 | Result Notification | Expert ตรวจใบงานเสร็จสิ้น | มีแจ้งเตือนเข้าสู่ระบบ (และ/หรือ Email) ของ Learner | \[ \] |

