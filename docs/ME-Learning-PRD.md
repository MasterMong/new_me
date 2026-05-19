# Product Requirements Document (PRD)
# ระบบ ME-Learning
### Monitoring & Evaluation e-Learning Platform

| รายการ | รายละเอียด |
|--------|-----------|
| **ชื่อโครงการ** | ME-Learning — ระบบ e-Learning หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน |
| **หน่วยงาน** | สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.) |
| **Version** | 2.0 |
| **วันที่** | 23 กุมภาพันธ์ 2569 |

---

## 1. Executive Summary

### 1.1 ภาพรวมโครงการ

ME-Learning เป็นระบบ e-Learning สำหรับพัฒนาบุคลากรทางการศึกษาให้เป็น "นักติดตาม ประเมินผลมืออาชีพ" สู่การพลิกโฉมคุณภาพการศึกษาไทย ระบบรองรับการเรียนรู้แบบ Self-paced ผ่านวิดีโอ เอกสาร และแบบทดสอบ พร้อมระบบตรวจใบงานโดยผู้เชี่ยวชาญ และออกเกียรติบัตรอัตโนมัติเมื่อเรียนจบตามเงื่อนไข

### 1.2 เป้าหมาย

- พัฒนาบุคลากรทางการศึกษาด้านการติดตามและประเมินผลอย่างเป็นระบบ
- รองรับผู้เรียนหลายกลุ่ม สามารถกำหนดสิทธิ์การเข้าถึงบทเรียนได้ยืดหยุ่น
- รองรับหลายหลักสูตร แต่ละหลักสูตรมีหลาย Module
- มีระบบประเมินผลทั้งอัตโนมัติและโดยผู้เชี่ยวชาญ
- ออกเกียรติบัตรอัตโนมัติเมื่อผ่านเกณฑ์

### 1.3 กลุ่มผู้ใช้งาน

| กลุ่ม | คำอธิบาย | จำนวนโดยประมาณ |
|-------|----------|---------------|
| ผู้เรียนทั่วไป | บุคลากรทางการศึกษาทั่วไป (เข้าถึงเนื้อหาตามกลุ่มที่กำหนด) | หลายร้อย - พัน คน |
| ผู้เรียน สตผ. | บุคลากรสำนักติดตามฯ (เข้าถึงเนื้อหาครบทุก Module) | หลายสิบ คน |
| ผู้เชี่ยวชาญ | ตรวจใบงาน/แบบทดสอบที่ต้องตรวจด้วยคน (3 Modules) | 5-10 คน |
| Admin สตผ. | จัดการระบบทั้งหมด สร้างหลักสูตร ดูรายงาน | 2-5 คน |

---

## 2. User Stories

### 2.1 ผู้เรียน (Learner)

| ID | User Story | Priority |
|----|-----------|----------|
| L-01 | ในฐานะผู้เรียน ฉันต้องการลงทะเบียนด้วยอีเมลและข้อมูลส่วนบุคคล เพื่อเข้าใช้ระบบ | Must |
| L-02 | ในฐานะผู้เรียน ฉันต้องการเข้าสู่ระบบด้วยอีเมลและรหัสผ่าน เพื่อเรียนบทเรียน | Must |
| L-03 | ในฐานะผู้เรียน ฉันต้องการรีเซ็ตรหัสผ่านผ่านอีเมล กรณีลืมรหัสผ่าน | Must |
| L-04 | ในฐานะผู้เรียน ฉันต้องการเห็น Learning Path ทั้งหมด (Pre-test → Module 1-9 → Post-test) พร้อมสถานะแต่ละ Module | Must |
| L-05 | ในฐานะผู้เรียน ฉันต้องการเรียนเนื้อหาแต่ละ Module (วิดีโอ, เอกสาร) โดยระบบเก็บชั่วโมงการรับชม | Must |
| L-06 | ในฐานะผู้เรียน ฉันต้องการทำแบบทดสอบ (เลือกตอบ/เขียนบรรยาย/อัปโหลดใบงาน) โดยทำซ้ำได้สูงสุด 3 ครั้ง | Must |
| L-07 | ในฐานะผู้เรียน ฉันต้องการเห็นผลแบบทดสอบทันที (ข้อเลือกตอบ) หรือรอผลจากผู้เชี่ยวชาญ (ข้อเขียน) | Must |
| L-08 | ในฐานะผู้เรียน ฉันต้องการเรียนเนื้อหาถัดไปได้หลังจากจบเนื้อหาก่อนหน้าแล้วเท่านั้น (เมื่อเปิดใช้งานลำดับการเรียน) | Must |
| L-09 | ในฐานะผู้เรียน ฉันต้องการรีวิวหลักสูตร (ให้ดาว 1-5 บังคับ + ความคิดเห็นไม่บังคับ) | Must |
| L-10 | ในฐานะผู้เรียน ฉันต้องการรับเกียรติบัตรอัตโนมัติเมื่อผ่านครบเงื่อนไข และดาวน์โหลดเป็น PDF | Must |
| L-11 | ในฐานะผู้เรียน ฉันต้องการดูผลการเรียนรู้รวม และ Export เป็น PDF | Must |
| L-12 | ในฐานะผู้เรียน ฉันต้องการแก้ไขข้อมูลส่วนบุคคลและเปลี่ยนรหัสผ่าน | Should |
| L-13 | ในฐานะผู้เรียน ฉันต้องการได้รับแจ้งเตือนเมื่อผู้เชี่ยวชาญตรวจใบงานเสร็จ (ทั้ง in-app และ email) | Should |

### 2.2 ผู้เชี่ยวชาญ (Expert)

| ID | User Story | Priority |
|----|-----------|----------|
| E-01 | ในฐานะผู้เชี่ยวชาญ ฉันต้องการเห็นรายการใบงานที่รอตรวจของ Module ที่รับผิดชอบ (3 Modules) | Must |
| E-02 | ในฐานะผู้เชี่ยวชาญ ฉันต้องการตรวจใบงาน ให้คะแนน เขียนข้อเสนอแนะ และกำหนดสถานะ (ผ่าน/รอแก้ไข) | Must |
| E-03 | ในฐานะผู้เชี่ยวชาญ ฉันต้องการให้ระบบส่งผลการตรวจกลับไปทาง email ถึงผู้เรียน | Must |
| E-04 | ในฐานะผู้เชี่ยวชาญ ฉันต้องการได้รับแจ้งเตือนเมื่อผู้เรียนส่งใบงานเข้าระบบ | Should |
| E-05 | ในฐานะผู้เชี่ยวชาญ ฉันต้องการดูรายงานผลการเรียนของผู้เรียนรายบุคคล | Should |

### 2.3 Admin สตผ.

| ID | User Story | Priority |
|----|-----------|----------|
| A-01 | ในฐานะ Admin ฉันต้องการสร้างหลักสูตรใหม่ และแก้ไขข้อมูลหลักสูตรที่มีอยู่ | Must |
| A-02 | ในฐานะ Admin ฉันต้องการจัดการ Module (เพิ่ม/แก้ไข/ลบ/จัดลำดับ) และอัปโหลดเนื้อหา (วิดีโอ/เอกสาร/URL) | Must |
| A-03 | ในฐานะ Admin ฉันต้องการจัดการแบบทดสอบ (สร้างคำถาม/ตัวเลือก/เฉลย) และกำหนดโหมดการตรวจ (auto/manual/mixed) | Must |
| A-04 | ในฐานะ Admin ฉันต้องการสร้างกลุ่มผู้เรียน และกำหนดว่ากลุ่มไหนเห็น content item ใดได้ | Must |
| A-05 | ในฐานะ Admin ฉันต้องการจัดการบัญชีผู้ใช้ (เพิ่ม/แก้ไข role/รีเซ็ตรหัสผ่าน) | Must |
| A-06 | ในฐานะ Admin ฉันต้องการกำหนดเงื่อนไขก่อนเรียน Module (prerequisite) ว่าต้องผ่านอะไรก่อน | Must |
| A-07 | ในฐานะ Admin ฉันต้องการจัดการเทมเพลตเกียรติบัตร (อัปโหลดภาพ/กำหนดตำแหน่งชื่อ-วันที่) | Must |
| A-08 | ในฐานะ Admin ฉันต้องการดูรายงานผลการเรียนของผู้เรียนทุกคน และ Export ข้อมูล | Must |
| A-09 | ในฐานะ Admin ฉันต้องการดูข้อมูลผู้เรียน/รีวิวจากผู้เรียน และ Export ข้อมูล | Must |
| A-10 | ในฐานะ Admin ฉันต้องการเห็น Dashboard สถิติการใช้งานระบบ (จำนวนผู้เรียน, ผู้ผ่าน, กราฟรายเดือน) | Should |

---

## 3. Functional Requirements

### 3.1 ระบบ Authentication & User Management

| ID | Requirement | Priority |
|----|------------|----------|
| FR-AUTH-01 | ผู้ใช้ลงทะเบียนด้วย: คำนำหน้า (dropdown: นาย/นาง/นางสาว), ชื่อ, นามสกุล, อีเมล, รหัสผ่าน, ยืนยันรหัสผ่าน, ตำแหน่ง (dropdown + อื่น ๆ), ประสบการณ์ทำงาน (dropdown: <2 ปี / 2-5 / 5-10 / 10-20 / >20 ปี), สังกัด (dropdown: สพท. 245 เขต / สำนักส่วนกลาง), สถานศึกษา, เบอร์ติดต่อ | Must |
| FR-AUTH-02 | เข้าสู่ระบบด้วยอีเมล + รหัสผ่าน | Must |
| FR-AUTH-03 | ลืมรหัสผ่าน: กรอกอีเมล → ส่ง link reset ทาง email → ตั้งรหัสผ่านใหม่ | Must |
| FR-AUTH-04 | เปลี่ยนรหัสผ่าน: กรอกรหัสเดิม + รหัสใหม่ + ยืนยันรหัสใหม่ | Must |
| FR-AUTH-05 | ผู้ใช้แก้ไขข้อมูลส่วนบุคคลได้ (ชื่อ-นามสกุลเชื่อมโยงกับเกียรติบัตร) | Must |
| FR-AUTH-06 | ระบบรองรับ 3 Role หลัก: learner, expert, admin | Must |
| FR-AUTH-07 | Admin จัดการบัญชีและสิทธิ์ผู้ใช้ทุกคน รวมถึงรีเซ็ตรหัสผ่าน | Must |

### 3.2 ระบบกลุ่มผู้เรียนและสิทธิ์การเข้าถึง

| ID | Requirement | Priority |
|----|------------|----------|
| FR-GROUP-01 | Admin สร้าง/แก้ไข/ลบกลุ่มผู้เรียนได้ไม่จำกัด (เช่น ผู้เรียนทั่วไป, ผู้เรียน สตผ., ผู้บริหาร...) | Must |
| FR-GROUP-02 | ผู้ใช้ 1 คนสังกัดได้หลายกลุ่ม (Many-to-Many) | Must |
| FR-GROUP-03 | Admin กำหนดว่ากลุ่มใดเห็น content item ใด (content_group_access) — ถ้าไม่กำหนด = ทุกกลุ่มเห็น | Must |
| FR-GROUP-04 | หลักสูตรและ Module มองเห็นได้ทุกกลุ่ม — สิทธิ์เข้าถึงควบคุมที่ระดับ content item | Must |
| FR-GROUP-05 | ผู้เรียนเห็นเฉพาะ content item ที่กลุ่มตนเองมีสิทธิ์ เมื่อเปลี่ยนกลุ่ม content ใหม่ปรากฏทันทีใน enrollment เดิม | Must |

### 3.3 ระบบหลักสูตรและเนื้อหา

| ID | Requirement | Priority |
|----|------------|----------|
| FR-COURSE-01 | Admin สร้างหลักสูตรใหม่ได้: ชื่อ, คำอธิบาย, ระยะเวลา (ชั่วโมง), เกณฑ์ผ่าน (%), วิทยากร (ชื่อ-ตำแหน่ง-รูป) | Must |
| FR-COURSE-02 | หลักสูตรมีหลาย Module (เช่น 9 Modules) เรียงลำดับ | Must |
| FR-COURSE-03 | แต่ละ Module ประกอบด้วยเนื้อหา: คลิปวิดีโอ, ไฟล์เอกสาร, ลิงก์เพิ่มเติม, และแบบทดสอบ (Quiz) | Must |
| FR-COURSE-04 | Admin อัปโหลดเนื้อหาได้: ไฟล์วิดีโอ/URL, ไฟล์เอกสาร, ลิงก์ | Must |
| FR-COURSE-05 | ระบบเก็บชั่วโมงการรับชมวิดีโอ (watch_duration, last_position, is_completed) | Must |
| FR-COURSE-06 | Module แต่ละตัวกำหนดได้ว่า `is_required` (บังคับเพื่อรับเกียรติบัตร) หรือไม่ | Must |
| FR-COURSE-07 | Module บางตัวกำหนดว่าต้องให้ผู้เชี่ยวชาญตรวจใบงาน (`requires_expert_review`) | Must |
| FR-COURSE-08 | หน้าหลักสูตร (public) แสดง: คำอธิบาย, ระยะเวลา, วิทยากร, เนื้อหา Module, จำนวนผู้เรียน, จำนวนผู้ผ่าน, คะแนนรีวิวเฉลี่ย (1-5), ความคิดเห็น | Must |

### 3.4 ระบบ Prerequisite (เงื่อนไขก่อนเรียน)

| ID | Requirement | Priority |
|----|------------|----------|
| FR-PREREQ-01 | ระบบรองรับการเรียนตามลำดับ (Sequential Learning) ทั้งระดับ Module และระดับ Content — ต้องจบเนื้อหาก่อนหน้าก่อนเข้าถึงเนื้อหาถัดไป | Must |
| FR-PREREQ-02 | Admin กำหนด prerequisite เฉพาะเจาะจงได้ เช่น Module 5 ต้องผ่านแบบทดสอบ Module 3 ≥ 60% ก่อน | Must |
| FR-PREREQ-03 | Prerequisite รองรับ 2 ประเภท: ต้องผ่าน Module ก่อนหน้า / ต้องผ่าน Assessment ก่อนหน้า | Must |
| FR-PREREQ-04 | Module ที่ยังไม่ผ่าน prerequisite แสดงเป็น "ล็อค" พร้อมข้อความบอกเงื่อนไข | Must |

### 3.5 ระบบแบบทดสอบและใบงาน

| ID | Requirement | Priority |
|----|------------|----------|
| FR-TEST-01 | ระบบรองรับ 4 ประเภทแบบทดสอบ: Pre-test (ก่อนเรียน), Post-test (หลังเรียน), Module test (แบบทดสอบประจำ Module), Assignment/ใบงาน | Must |
| FR-TEST-02 | คำถามรองรับ 4 ประเภท: Multiple choice (เลือกตอบ), Essay (เขียนบรรยาย), File upload (อัปโหลดใบงาน), และ Short answer (ตอบสั้น) | Must |
| FR-TEST-03 | โหมดการตรวจ (grading_mode): `auto` — เฉลยอัตโนมัติทันที (multiple choice), `manual` — ต้องมีผู้เชี่ยวชาญตรวจ (essay/file upload), `mixed` — มีทั้ง 2 แบบใน assessment เดียวกัน | Must |
| FR-TEST-04 | แบบทดสอบทำซ้ำได้สูงสุด 3 ครั้ง | Must |
| FR-TEST-05 | ดาวจะลดลงตามลำดับการทำซ้ำ (ครั้งที่ 1 = 3 ดาว, ครั้งที่ 2 = 2 ดาว, ครั้งที่ 3 = 1 ดาว) | Must |
| FR-TEST-06 | ข้อมูลการทำแบบทดสอบถูกจัดเก็บครบทุกครั้งที่ทำ (ทุก attempt) | Must |
| FR-TEST-07 | Assessment แต่ละตัวกำหนดได้ว่า `is_required_for_cert` (บังคับผ่านเพื่อรับเกียรติบัตร) หรือไม่ | Must |
| FR-TEST-08 | เกณฑ์ผ่าน: คะแนน ≥ 60% (ปรับได้ต่อ assessment) | Must |

### 3.6 ระบบผู้เชี่ยวชาญตรวจใบงาน

| ID | Requirement | Priority |
|----|------------|----------|
| FR-EXPERT-01 | ผู้เชี่ยวชาญเห็นเฉพาะ Module ที่ `requires_expert_review = TRUE` (3 Modules) | Must |
| FR-EXPERT-02 | แสดงรายการใบงานที่รอตรวจ: ชื่อผู้เรียน, วันที่ส่ง, วันที่แก้ไขล่าสุด, สถานะ | Must |
| FR-EXPERT-03 | สถานะการตรวจ 3 ระดับ: `รอตรวจ` (pending) — ยังไม่ได้ตรวจ, `ผ่าน` (passed) — ตรวจแล้ว ผ่านการพิจารณา, `รอแก้ไข` (revision_needed) — ตรวจแล้ว ให้กลับไปทบทวนแก้ไข | Must |
| FR-EXPERT-04 | ผู้เชี่ยวชาญตรวจคำตอบ + ให้คะแนน + เขียนข้อเสนอแนะ/คำแนะนำ | Must |
| FR-EXPERT-05 | เมื่อตรวจเสร็จ ส่งผลกลับไปทาง email ถึงผู้เรียน | Must |
| FR-EXPERT-06 | มีแจ้งเตือนเมื่อผู้เรียนส่งใบงานเข้าระบบ | Should |

### 3.7 ระบบรีวิวหลักสูตร

| ID | Requirement | Priority |
|----|------------|----------|
| FR-REVIEW-01 | ผู้เรียนให้คะแนนความพึงพอใจ 1-5 ดาว (บังคับ): 1 = น้อยที่สุด → 5 = มากที่สุด | Must |
| FR-REVIEW-02 | แสดงความคิดเห็น (ไม่บังคับ) | Must |
| FR-REVIEW-03 | รีวิวได้ 1 ครั้งต่อหลักสูตร | Must |
| FR-REVIEW-04 | การรีวิวเป็นเงื่อนไขหนึ่งของการได้รับเกียรติบัตร | Must |
| FR-REVIEW-05 | หน้าหลักสูตรแสดงค่าเฉลี่ยรวมและความคิดเห็นจากผู้เรียนทั้งหมด | Must |

### 3.8 ระบบเกียรติบัตร

| ID | Requirement | Priority |
|----|------------|----------|
| FR-CERT-01 | เกียรติบัตรออกอัตโนมัติเมื่อครบทุกเงื่อนไข (ดูหัวข้อ 3.9) | Must |
| FR-CERT-02 | เรียนครบ 9 Module ได้รับ 1 เกียรติบัตร | Must |
| FR-CERT-03 | เกียรติบัตรมีเลขที่ไม่ซ้ำกัน (unique certificate number) | Must |
| FR-CERT-04 | ชื่อบนเกียรติบัตรดึงจากข้อมูลส่วนบุคคลผู้เรียน | Must |
| FR-CERT-05 | ผู้เรียนเรียกดูเกียรติบัตรได้ + ดาวน์โหลดเป็น PDF | Must |
| FR-CERT-06 | แสดงวันที่ออกเกียรติบัตร | Must |
| FR-CERT-07 | Admin จัดการเทมเพลตเกียรติบัตร (อัปโหลดภาพ, กำหนดตำแหน่งชื่อ/วันที่) | Must |

### 3.9 เงื่อนไขการออกเกียรติบัตร

ผู้เรียนจะได้รับเกียรติบัตรอัตโนมัติเมื่อครบ **ทุกเงื่อนไข** ต่อไปนี้:

| # | เงื่อนไข | ตรวจสอบจาก |
|---|---------|-----------|
| 1 | ผ่านทุก Module ที่กำหนดว่าบังคับ (`is_required = TRUE`) | `module_progress.status = 'completed'` |
| 2 | ผ่านทุก Assessment ที่กำหนดว่าบังคับ (`is_required_for_cert = TRUE`) | `test_attempts.status = 'passed'` |
| 3 | ใบงานที่ต้องตรวจ ผ่านการพิจารณาจากผู้เชี่ยวชาญทุกตัว | `expert_reviews.status = 'passed'` |
| 4 | รีวิวหลักสูตรแล้ว (ให้ดาว 1-5) | `course_reviews` มี record |
| 5 | คะแนนรวม Post-test ≥ เกณฑ์ผ่าน (60%) | `test_attempts.score_pct >= 60` |

### 3.10 ระบบแจ้งเตือน

| ID | Requirement | Priority |
|----|------------|----------|
| FR-NOTI-01 | แจ้ง Expert เมื่อผู้เรียนส่งใบงาน | Should |
| FR-NOTI-02 | แจ้งผู้เรียนเมื่อ Expert ตรวจเสร็จ (ผ่าน) | Must |
| FR-NOTI-03 | แจ้งผู้เรียนเมื่อ Expert ตรวจเสร็จ (รอแก้ไข) พร้อมข้อเสนอแนะ | Must |
| FR-NOTI-04 | แจ้งผู้เรียนเมื่อได้รับเกียรติบัตร | Should |
| FR-NOTI-05 | ช่องทางแจ้งเตือน: In-app notification + Email | Should |

### 3.11 ระบบรายงานและ Export

| ID | Requirement | Priority |
|----|------------|----------|
| FR-REPORT-01 | รายงานผลการเรียน: ตาราง Pre-test / M1-M9 / Post-test / ผ่านเกณฑ์ / สถานะ — ดูรายบุคคลได้ | Must |
| FR-REPORT-02 | Export ข้อมูลรายชื่อและผลรวมตามตารางที่แสดง | Must |
| FR-REPORT-03 | Export ใบรายงานผลรายบุคคลเป็น PDF | Must |
| FR-REPORT-04 | รายงานข้อมูลผู้เรียน: ชื่อ, ตำแหน่ง, ประสบการณ์, สังกัด, สถานศึกษา, เบอร์ + Export | Must |
| FR-REPORT-05 | รายงานรีวิวจากผู้เรียน: ชื่อ, ความพึงพอใจ (ดาว), ความคิดเห็น + Export | Must |
| FR-REPORT-06 | Dashboard สถิติ: จำนวนผู้เรียน, จำนวนผู้ผ่าน, กราฟรายเดือน, รายงานการเข้าใช้ระบบ | Should |

---

## 4. Information Architecture

### 4.1 Site Map

```
ME-Learning
├── Public Pages
│   ├── / (Landing — รายละเอียดหลักสูตร + รีวิว)
│   ├── /courses (รายการหลักสูตร)
│   ├── /courses/:id (รายละเอียดหลักสูตร)
│   ├── /register
│   ├── /login
│   ├── /forgot-password
│   ├── /reset-password/:token
│   ├── /directory (ทำเนียบนักติดตาม)
│   └── /contact (ติดต่อเรา)
│
├── Learner (/learn)
│   ├── / (หน้าหลักผู้เรียน)
│   ├── /courses/:id (Learning Path)
│   ├── /courses/:id/modules/:id (เนื้อหา Module)
│   ├── /courses/:id/assessments/:id (ทำแบบทดสอบ)
│   ├── /courses/:id/assessments/:id/result (ผลแบบทดสอบ)
│   ├── /courses/:id/review (รีวิวหลักสูตร)
│   ├── /results (ผลการเรียนรู้)
│   ├── /certificates (เกียรติบัตร)
│   ├── /profile (ข้อมูลส่วนบุคคล)
│   └── /change-password
│
├── Expert (/expert)
│   ├── / (Dashboard ผู้เชี่ยวชาญ)
│   ├── /modules/:id/submissions (รายการใบงาน)
│   ├── /submissions/:id/review (ตรวจใบงาน)
│   ├── /reports (รายงานผลการเรียน)
│   ├── /reports/:userId (ผลรายบุคคล)
│   └── /change-password
│
└── Admin (/admin)
    ├── / (Dashboard สถิติ)
    ├── /courses (จัดการหลักสูตร)
    ├── /courses/:id/modules (จัดการ Module)
    ├── /courses/:id/modules/:id/contents (จัดการเนื้อหา)
    ├── /courses/:id/assessments (จัดการแบบทดสอบ)
    ├── /courses/:id/groups (กำหนดกลุ่ม↔หลักสูตร)
    ├── /groups (จัดการกลุ่มผู้เรียน)
    ├── /users (จัดการบัญชีและสิทธิ์)
    ├── /learners (ข้อมูลผู้เรียน)
    ├── /reports (รายงานผลการเรียน)
    ├── /reviews (รีวิวจากผู้เรียน)
    └── /certificates (จัดการเกียรติบัตร)
```

### 4.2 User Flow หลัก

```
ผู้เรียนใหม่:
  ลงทะเบียน → เข้าสู่ระบบ → เลือกหลักสูตร → ทำ Pre-test
  → เรียน Module 1 (วิดีโอ + เอกสาร) → ทำแบบทดสอบ Module 1
  → เรียน Module 2 → ... → เรียน Module 9
  → ทำ Post-test → รีวิวหลักสูตร → รับเกียรติบัตร

ผู้เชี่ยวชาญ:
  เข้าสู่ระบบ → เห็น Module ที่รับผิดชอบ → คลิกดูรายการใบงาน
  → คลิกชื่อผู้เรียน → ตรวจ + ให้คะแนน + เขียนข้อเสนอแนะ
  → บันทึก (ผ่าน/รอแก้ไข) → ส่งผลทาง email

Admin:
  เข้าสู่ระบบ → Dashboard สถิติ
  → จัดการหลักสูตร/Module/เนื้อหา/แบบทดสอบ
  → จัดการกลุ่มผู้เรียน + กำหนดสิทธิ์
  → ดูรายงาน + Export ข้อมูล
```

---

## 5. Database Design

### 5.1 สรุปตาราง (22 ตาราง)

| กลุ่ม | ตาราง | จำนวน |
|-------|-------|-------|
| Users & Groups | `users`, `positions`, `affiliations`, `learner_groups` 🆕, `user_group_memberships` 🆕, `login_logs` | 6 |
| Courses & Modules | `courses`, `course_instructors`, `modules`, `module_contents`, `content_group_access` 🆕, `module_prerequisites` 🆕 | 6 |
| Assessments | `assessments`, `questions`, `question_choices` | 3 |
| Learning Progress | `enrollments`, `module_progress`, `content_views` | 3 |
| Test Results & Expert Review | `test_attempts`, `test_answers`, `expert_reviews` | 3 |
| Reviews & Certificates | `course_reviews`, `certificates`, `certificate_templates` | 3 |
| Notifications | `notifications` | 1 |

> รายละเอียดคอลัมน์ทั้งหมดอยู่ในเอกสาร **ME-Learning-Database-Design-v2.md**

### 5.2 ER Relationships สำคัญ

```
users ←M:N→ learner_groups      (ผ่าน user_group_memberships)
courses ←1:N→ modules ←1:N→ module_contents
module_contents ←M:N→ learner_groups  (ผ่าน content_group_access)
modules ←1:N→ assessments ←1:N→ questions ←1:N→ question_choices
modules ←prereq→ modules/assessments  (ผ่าน module_prerequisites)
users ←1:N→ enrollments ←N:1→ courses
users ←1:N→ test_attempts ←N:1→ assessments
test_attempts ←1:N→ test_answers
test_attempts ←1:1→ expert_reviews
users ←1:N→ course_reviews ←N:1→ courses
users ←1:1→ certificates ←N:1→ courses
```

---

## 6. Non-Functional Requirements

### 6.1 Performance

| ID | Requirement |
|----|------------|
| NFR-PERF-01 | หน้าเว็บโหลดภายใน 3 วินาที |
| NFR-PERF-02 | รองรับผู้ใช้พร้อมกันอย่างน้อย 200 คน |
| NFR-PERF-03 | วิดีโอสตรีมมิ่งต้องไม่กระตุก (buffer ≤ 2 วินาที) |
| NFR-PERF-04 | ระบบตอบสนองการส่งแบบทดสอบภายใน 2 วินาที |

### 6.2 Security

| ID | Requirement |
|----|------------|
| NFR-SEC-01 | รหัสผ่านเข้ารหัสด้วย bcrypt (ไม่เก็บ plain text) |
| NFR-SEC-02 | HTTPS ตลอดทั้ง site |
| NFR-SEC-03 | Session/Token expiration (auto logout เมื่อไม่ใช้งาน) |
| NFR-SEC-04 | Route protection ตาม role (ป้องกัน unauthorized access) |
| NFR-SEC-05 | ป้องกัน SQL Injection, XSS, CSRF |
| NFR-SEC-06 | Password reset token มีอายุจำกัด (เช่น 1 ชั่วโมง) |

### 6.3 Usability

| ID | Requirement |
|----|------------|
| NFR-USE-01 | รองรับภาษาไทยทั้งระบบ |
| NFR-USE-02 | Responsive design (ใช้งานได้บน Desktop, Tablet, Mobile) |
| NFR-USE-03 | ใช้งานง่าย ไม่ต้องอบรมก่อนใช้ |
| NFR-USE-04 | มี Feedback ชัดเจนทุกการกระทำ (success/error message) |

### 6.4 Reliability & Availability

| ID | Requirement |
|----|------------|
| NFR-REL-01 | Uptime ≥ 99% |
| NFR-REL-02 | สำรองข้อมูลอัตโนมัติทุกวัน |
| NFR-REL-03 | วิดีโอและไฟล์เอกสารมี backup |

### 6.5 Scalability

| ID | Requirement |
|----|------------|
| NFR-SCA-01 | รองรับหลายหลักสูตร (Admin สร้างเพิ่มได้) |
| NFR-SCA-02 | รองรับกลุ่มผู้เรียนไม่จำกัด |
| NFR-SCA-03 | รองรับผู้เรียนเพิ่มขึ้นในอนาคต (ถึงหลักพัน) |

---

## 7. UI/UX Guidelines

### 7.1 Navigation

| ส่วน | รายละเอียด |
|------|-----------|
| **Top Navbar** | โลโก้ ME-Learning / หน้าหลัก / ทำเนียบนักติดตาม / ติดต่อเรา / ปุ่ม Login หรือชื่อผู้ใช้ |
| **Sidebar (Learner)** | ผลการเรียนรู้ / เกียรติบัตร / ข้อมูลส่วนบุคคล / เปลี่ยนรหัสผ่าน / ออกจากระบบ |
| **Sidebar (Expert)** | ตรวจแบบทดสอบ / รายงานผลการเรียนรู้ / ออกจากระบบ |
| **Sidebar (Admin)** | เกี่ยวกับหลักสูตร / จัดการบัญชี / ข้อมูลผู้เรียน / รายงานผลการเรียน / รีวิวจากผู้เรียน / ทำเนียบนักติดตาม / จัดการเกียรติบัตร / Dashboard |

### 7.2 Key Pages

| หน้า | องค์ประกอบหลัก |
|------|---------------|
| **Landing / หน้าหลักสูตร** | ชื่อหลักสูตร + banner, Tab: รายละเอียด / วิทยากร / เนื้อหา, คะแนนรีวิวเฉลี่ย (ดาว), จำนวนผู้เรียน + ผู้ผ่าน, ความคิดเห็น, ปุ่ม CTA "เข้าสู่บทเรียน" |
| **Learning Path** | แสดง Module เรียงลำดับ: ไอคอนสถานะ (🔒ล็อค / 📖กำลังเรียน / ✅จบ), ชื่อ + หัวข้อย่อย, ระยะเวลา, Pre-test อยู่บนสุด / Post-test อยู่ล่างสุด |
| **เนื้อหา Module** | วิดีโอ player (เก็บชั่วโมง), รายการเอกสาร (ดาวน์โหลดได้), เรียงตามหมายเลข (1.1, 1.2, 1.3), ปุ่ม "ทำแบบทดสอบ" เมื่อดูเนื้อหาครบ |
| **ทำแบบทดสอบ** | แสดงคำถามทีละข้อ/ทั้งหมด, ตัวเลือก (radio button), พื้นที่เขียน (textarea), ปุ่มอัปโหลดไฟล์, แสดงครั้งที่ทำ (1/3), ปุ่ม "ส่งคำตอบ" |
| **ผลแบบทดสอบ** | คะแนน / เต็ม / %, ดาว, ผ่าน/ไม่ผ่าน, ข้อ auto → เห็นผลทันที, ข้อ manual → "รอตรวจ", ปุ่ม "ทำใหม่" (ถ้ายังไม่ครบ 3 ครั้ง) |
| **ตรวจใบงาน (Expert)** | ข้อมูลผู้เรียน, คำตอบ/ไฟล์อัปโหลด, ช่องให้คะแนน, ช่องเขียนข้อเสนอแนะ, dropdown สถานะ (ผ่าน/รอแก้ไข), ปุ่ม "บันทึก" |
| **เกียรติบัตร** | แสดงภาพเกียรติบัตร (preview), ปุ่ม "ดาวน์โหลด PDF", วันที่ออก, ถ้ายังไม่ผ่าน → แสดงเงื่อนไขที่เหลือ |

---

## 8. API Endpoints (ภาพรวม)

### 8.1 Auth

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| POST | `/api/auth/register` | ลงทะเบียน |
| POST | `/api/auth/login` | เข้าสู่ระบบ |
| POST | `/api/auth/logout` | ออกจากระบบ |
| POST | `/api/auth/forgot-password` | ขอรีเซ็ตรหัสผ่าน |
| POST | `/api/auth/reset-password` | ตั้งรหัสผ่านใหม่ |
| PUT | `/api/auth/change-password` | เปลี่ยนรหัสผ่าน |

### 8.2 User & Profile

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| GET | `/api/users/me` | ดูข้อมูลตัวเอง |
| PUT | `/api/users/me` | แก้ไขข้อมูลส่วนบุคคล |
| GET | `/api/positions` | รายการตำแหน่ง (dropdown) |
| GET | `/api/affiliations` | รายการสังกัด (dropdown) |

### 8.3 Courses & Modules

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| GET | `/api/courses` | รายการหลักสูตร (filter ตามกลุ่ม) |
| GET | `/api/courses/:id` | รายละเอียดหลักสูตร |
| POST | `/api/courses/:id/enroll` | ลงทะเบียนเรียน |
| GET | `/api/courses/:id/modules` | รายการ Module + สถานะ |
| GET | `/api/modules/:id` | รายละเอียด Module |
| GET | `/api/modules/:id/contents` | เนื้อหา Module |
| POST | `/api/contents/:id/view` | บันทึกการชมวิดีโอ |

### 8.4 Assessments

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| GET | `/api/assessments/:id` | ข้อมูลแบบทดสอบ + คำถาม |
| POST | `/api/assessments/:id/attempts` | เริ่มทำแบบทดสอบ (สร้าง attempt) |
| PUT | `/api/attempts/:id` | ส่งคำตอบ |
| GET | `/api/attempts/:id/result` | ดูผลแบบทดสอบ |

### 8.5 Expert Review

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| GET | `/api/expert/modules` | Module ที่รับผิดชอบ |
| GET | `/api/expert/modules/:id/submissions` | รายการใบงานรอตรวจ |
| GET | `/api/expert/submissions/:attemptId` | ดูคำตอบผู้เรียน |
| POST | `/api/expert/submissions/:attemptId/review` | บันทึกผลการตรวจ |

### 8.6 Reviews & Certificates

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| POST | `/api/courses/:id/reviews` | ส่งรีวิวหลักสูตร |
| GET | `/api/courses/:id/reviews` | รายการรีวิว |
| GET | `/api/certificates` | เกียรติบัตรของฉัน |
| GET | `/api/certificates/:id/download` | ดาวน์โหลด PDF |

### 8.7 Admin

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| POST | `/api/admin/courses` | สร้างหลักสูตร |
| PUT | `/api/admin/courses/:id` | แก้ไขหลักสูตร |
| CRUD | `/api/admin/courses/:id/modules` | จัดการ Module |
| CRUD | `/api/admin/courses/:id/modules/:id/contents` | จัดการเนื้อหา |
| CRUD | `/api/admin/courses/:id/assessments` | จัดการแบบทดสอบ |
| CRUD | `/api/admin/groups` | จัดการกลุ่มผู้เรียน |
| CRUD | `/api/admin/groups/:id/members` | จัดการสมาชิกกลุ่ม |
| PUT | `/api/admin/courses/:id/group-access` | กำหนดกลุ่ม↔หลักสูตร |
| PUT | `/api/admin/modules/:id/group-access` | กำหนดกลุ่ม↔Module |
| PUT | `/api/admin/modules/:id/prerequisites` | กำหนด prerequisite |
| CRUD | `/api/admin/users` | จัดการบัญชีผู้ใช้ |
| CRUD | `/api/admin/certificates/templates` | จัดการเทมเพลตเกียรติบัตร |
| GET | `/api/admin/reports/learners` | รายงานผู้เรียน + Export |
| GET | `/api/admin/reports/results` | รายงานผลการเรียน + Export |
| GET | `/api/admin/reports/reviews` | รายงานรีวิว + Export |
| GET | `/api/admin/dashboard` | สถิติภาพรวม |

### 8.8 Notifications

| Method | Endpoint | คำอธิบาย |
|--------|----------|----------|
| GET | `/api/notifications` | รายการแจ้งเตือน |
| PUT | `/api/notifications/:id/read` | อ่านแจ้งเตือน |

---

## 9. Out of Scope (ไม่รวมใน Phase นี้)

- ระบบ Social / Forum / กระดานสนทนา
- ระบบ Live class / Video conference
- Mobile application (Native iOS/Android)
- ระบบชำระเงิน
- Multi-language (ภาษาอังกฤษ)
- ระบบ Gamification ขั้นสูง (leaderboard, badge)
- Integration กับระบบ HR ภายนอก
- ระบบ Single Sign-On (SSO)

---

## 10. Success Metrics

| Metric | เป้าหมาย |
|--------|---------|
| ผู้เรียนลงทะเบียนสำเร็จ | ≥ 90% ของผู้ที่เริ่มลงทะเบียน |
| ผู้เรียนเรียนจบหลักสูตร | ≥ 60% ของผู้ลงทะเบียน |
| ผู้เรียนผ่านเกณฑ์ + รับเกียรติบัตร | ≥ 50% ของผู้ลงทะเบียน |
| คะแนนความพึงพอใจเฉลี่ย | ≥ 4.0 / 5.0 |
| ระยะเวลาตรวจใบงาน | ≤ 3 วันทำการ |
| System uptime | ≥ 99% |

---

## 11. เอกสารที่เกี่ยวข้อง

| เอกสาร | คำอธิบาย |
|--------|----------|
| ME-Learning-Database-Design-v2.md | รายละเอียดฐานข้อมูล 22 ตาราง + FK + Business Rules |
| ME-Learning-Frontend-Routes.md | รายการ Route 35 เส้นทาง + คำอธิบาย + Route Guard Logic |
| โครงสร้าง_ME-Learning.pdf | เอกสารโครงสร้างระบบต้นฉบับ (Wireframe + Flow) |
