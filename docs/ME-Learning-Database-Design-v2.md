# ME-Learning Database Design (Updated v2)

> หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน  
> **22 ตาราง | 29+ Foreign Keys | 4 User Roles**

---

## สรุปการเปลี่ยนแปลงจาก v1

| รายการ | เดิม (v1) | อัปเดต (v2) |
|--------|-----------|-------------|
| จำนวนตาราง | 18 | **23 (+5 ตารางใหม่)** |
| กลุ่มผู้เรียน | ENUM ตายตัว 4 ค่า | `learner_groups` + `user_group_memberships` (ยืดหยุ่น) |
| สิทธิ์เข้าถึงหลักสูตร | ไม่มี | `course_group_access` (กำหนดกลุ่ม↔หลักสูตร) |
| สิทธิ์เข้าถึง Module | `is_locked_for_general` (boolean) | `module_group_access` (กำหนดหลายกลุ่มต่อ Module) |
| เงื่อนไขก่อนเรียน | ไม่มี (ใช้แค่ลำดับ) | `module_prerequisites` (ต้องผ่าน assessment/module ก่อน) |
| การตรวจแบบทดสอบ | `requires_expert_review` (boolean) | `grading_mode` ระดับ assessment + question |
| เงื่อนไขเกียรติบัตร | ผ่าน ≥ 60% + รีวิว | + `is_required` (module) + `is_required_for_cert` (assessment) |
| ระดับการควบคุมสิทธิ์ | `course_group_access` + `module_group_access` | **`content_group_access`** (ระดับ content item เท่านั้น) |
| เมื่อผู้ใช้เปลี่ยนกลุ่ม | จำกัดที่หลักสูตร/module | content ใหม่ปรากฏใน enrollment เดิมทันที ไม่ต้องเริ่มใหม่ |

---

## 1. Users & Groups (6 ตาราง)

### 1.1 `users`

ผู้ใช้งานทั้งหมด — role: learner / expert / admin

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `email` | VARCHAR(255) UNIQUE NOT NULL | ใช้เป็น username + ลืมรหัสผ่าน |
| `password_hash` | VARCHAR(255) NOT NULL | เข้ารหัส bcrypt |
| `prefix` | ENUM('นาย','นาง','นางสาว') | คำนำหน้าชื่อ (dropdown) |
| `first_name` | VARCHAR(100) NOT NULL | ชื่อ |
| `last_name` | VARCHAR(100) NOT NULL | นามสกุล → ใช้ออกเกียรติบัตร |
| `position_id` | INT FK → `positions.id` | ตำแหน่ง (dropdown) |
| `position_other` | VARCHAR(255) NULL | กรณีเลือก 'อื่น ๆ' |
| `experience` | ENUM('<2y','2-5y','5-10y','10-20y','>20y') | ประสบการณ์ทำงาน |
| `affiliation_id` | INT FK → `affiliations.id` | สังกัด (สพท. 245 เขต) |
| `school_name` | VARCHAR(255) NULL | สถานศึกษา |
| `phone` | VARCHAR(20) | เบอร์ติดต่อ |
| `role` | ENUM('learner','expert','admin') | บทบาทหลัก (กลุ่มผู้เรียนจัดการผ่าน learner_groups) |
| `is_active` | BOOLEAN DEFAULT TRUE | สถานะบัญชี |
| `password_reset_token` | VARCHAR(255) NULL | กรณีลืมรหัสผ่าน |
| `password_reset_expires` | TIMESTAMP NULL | หมดอายุ token |
| `created_at` | TIMESTAMP DEFAULT NOW() | วันลงทะเบียน |
| `updated_at` | TIMESTAMP ON UPDATE | แก้ไขล่าสุด |

### 1.2 `positions`

ตำแหน่งงาน (Dropdown)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `name` | VARCHAR(255) NOT NULL | เช่น นักวิชาการศึกษา, นักวิเคราะห์นโยบายฯ, นักจัดการงานทั่วไป |
| `sort_order` | INT DEFAULT 0 | ลำดับแสดงผล |

### 1.3 `affiliations`

สังกัด (สพท. 245 เขต / สำนักส่วนกลาง)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `name` | VARCHAR(255) NOT NULL | ชื่อสังกัด |
| `type` | ENUM('สพท','สำนักส่วนกลาง') | ประเภท |

### 1.4 `learner_groups` 🆕

กลุ่มผู้เรียน (สร้างได้ไม่จำกัด เช่น ผู้เรียน สตผ., ผู้เรียนทั่วไป, ผู้บริหาร...)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `name` | VARCHAR(255) NOT NULL | ชื่อกลุ่ม |
| `description` | TEXT NULL | รายละเอียดกลุ่ม |
| `is_active` | BOOLEAN DEFAULT TRUE | |
| `created_at` | TIMESTAMP | |

### 1.5 `user_group_memberships` 🆕

ผู้ใช้ ↔ กลุ่ม (Many-to-Many: คนหนึ่งอยู่ได้หลายกลุ่ม)

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `group_id` | INT FK → `learner_groups.id` | |
| `assigned_at` | TIMESTAMP | วันที่เข้ากลุ่ม |
| `assigned_by` | BIGINT FK → `users.id` NULL | Admin ที่กำหนด |
| | UNIQUE(`user_id`, `group_id`) | ไม่ซ้ำกัน |

### 1.6 `login_logs`

บันทึกการเข้าใช้ระบบ → Dashboard รายงานสถิติรายเดือน

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `logged_in_at` | TIMESTAMP DEFAULT NOW() | |
| `ip_address` | VARCHAR(45) | |

---

## 2. Courses & Modules (7 ตาราง)

### 2.1 `courses`

หลักสูตร (Admin สร้าง/แก้ไขได้)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `title` | VARCHAR(500) NOT NULL | ชื่อหลักสูตร |
| `description` | TEXT | คำอธิบายหลักสูตร |
| `duration_hours` | DECIMAL(5,1) | ระยะเวลา เช่น 6 ชั่วโมง |
| `passing_score_pct` | INT DEFAULT 60 | เกณฑ์ผ่าน ≥ 60% |
| `has_test` | BOOLEAN DEFAULT TRUE | มีแบบทดสอบ |
| `require_review` | BOOLEAN DEFAULT TRUE | ต้องรีวิวเพื่อรับเกียรติบัตร |
| `is_published` | BOOLEAN DEFAULT FALSE | สถานะเผยแพร่ |
| `created_by` | BIGINT FK → `users.id` | Admin ผู้สร้าง |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### 2.2 `course_instructors`

วิทยากร (ชื่อ-นามสกุล, ตำแหน่ง, รูปภาพ)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `course_id` | INT FK → `courses.id` | |
| `name` | VARCHAR(255) NOT NULL | ชื่อ-นามสกุล |
| `position` | VARCHAR(255) | ตำแหน่ง |
| `photo_url` | VARCHAR(500) NULL | รูปภาพ |
| `sort_order` | INT DEFAULT 0 | |

### 2.3 ~~`course_group_access`~~ ❌ ลบแล้ว

> ❌ ลบ `course_group_access` — สิทธิ์เข้าถึงย้ายไปที่ระดับ content item (`content_group_access`)  
> หลักสูตรและ Module มองเห็นได้ทุกกลุ่ม; content item ถูกกำหนดสิทธิ์เป็น item-by-item

### 2.4 `modules` ✏️

Module บทเรียน (9 Modules/หลักสูตร)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `course_id` | INT FK → `courses.id` | |
| `module_number` | INT NOT NULL | ลำดับ 1-9 |
| `title` | VARCHAR(500) NOT NULL | ชื่อ Module |
| `description` | TEXT NULL | |
| `is_required` | BOOLEAN DEFAULT TRUE | 🆕 บังคับต้องเรียนเพื่อรับเกียรติบัตร |
| `requires_expert_review` | BOOLEAN DEFAULT FALSE | 3 Modules ที่ต้องให้ ผชช. ตรวจ |
| `max_test_attempts` | INT DEFAULT 3 | ทำซ้ำได้ 3 ครั้ง |
| `sort_order` | INT | |
| `created_at` | TIMESTAMP | |

> ❌ ลบ `is_locked_for_general` — สิทธิ์เข้าถึงย้ายไปที่ระดับ content item (`content_group_access`)

### 2.5 ~~`module_group_access`~~ ❌ ลบแล้ว

> ❌ ลบ `module_group_access` — สิทธิ์เข้าถึงระดับ Module ถูกแทนที่ด้วย `content_group_access` ดูหัวข้อ 2.7

### 2.6 `module_contents`

เนื้อหา: คลิปวิดีโอ (เก็บชั่วโมงรับชม) / ไฟล์เอกสาร (ใบความรู้)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `module_id` | INT FK → `modules.id` | |
| `content_type` | ENUM('video','document','link') | คลิป / เอกสาร / ลิงก์ |
| `title` | VARCHAR(500) NOT NULL | ชื่อ เช่น 1.1, 1.2 |
| `file_url` | VARCHAR(1000) | URL ไฟล์ (อัปโหลด/URL) |
| `duration_minutes` | DECIMAL(8,2) NULL | ความยาววิดีโอ (นาที) |
| `sort_order` | INT DEFAULT 0 | |

### 2.7 `content_group_access` 🆕

สิทธิ์เข้าถึง content item ระดับ content (กำหนดกลุ่มที่มองเห็นแต่ละ content item)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `content_id` | INT FK → `module_contents.id` | content item ที่ถูกจำกัดสิทธิ์ |
| `group_id` | INT FK → `learner_groups.id` | กลุ่มที่มีสิทธิ์ดู content นี้ |
| | UNIQUE(`content_id`, `group_id`) | |

> ถ้าไม่มี record ใน `content_group_access` = ทุกกลุ่มเห็น content นั้น (public)  
> ถ้ามี record = เฉพาะกลุ่มที่ระบุเท่านั้นเห็น  
> **สิทธิ์ประเมิน ณ เวลา request** ต่อ `user_group_memberships` ปัจจุบัน — เมื่อผู้ใช้เข้ากลุ่มใหม่ content ใหม่ปรากฏทันที

### 2.8 `module_prerequisites` 🆕

เงื่อนไขก่อนเรียน Module (ต้องผ่าน Module หรือ Assessment ใดก่อน)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `module_id` | INT FK → `modules.id` | Module ที่ต้องการเรียน |
| `prerequisite_type` | ENUM('module','assessment') | ต้องผ่านอะไร |
| `prerequisite_module_id` | INT FK → `modules.id` NULL | ต้องผ่าน Module นี้ก่อน |
| `prerequisite_assessment_id` | INT FK → `assessments.id` NULL | ต้องผ่าน Assessment นี้ก่อน |
| `min_score_pct` | INT NULL | คะแนนขั้นต่ำ (%) ถ้ากำหนด |

> ตัวอย่าง: Module 5 ต้องผ่านแบบทดสอบ Module 3 ≥ 60%  
> → `module_id=5, prerequisite_type='assessment', prerequisite_assessment_id=3, min_score_pct=60`

---

## 3. Assessments (3 ตาราง)

### 3.1 `assessments` ✏️

แบบทดสอบ: Pre-test, Post-test, Module test, ใบงาน (Assignment)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `course_id` | INT FK → `courses.id` | |
| `module_id` | INT FK → `modules.id` NULL | NULL = course-level (Pre/Post) |
| `type` | ENUM('pre_test','post_test','module_test','assignment') | ประเภท |
| `title` | VARCHAR(500) | |
| `passing_score_pct` | INT NULL | เกณฑ์ผ่าน (%) |
| `max_attempts` | INT DEFAULT 3 | ทำซ้ำได้สูงสุด |
| `grading_mode` | ENUM('auto','manual','mixed') | 🆕 auto=เฉลยทันที, manual=ต้องตรวจ, mixed=ปนกัน |
| `is_required_for_cert` | BOOLEAN DEFAULT FALSE | 🆕 บังคับผ่านเพื่อรับเกียรติบัตร |
| `requires_expert_review` | BOOLEAN DEFAULT FALSE | ต้อง ผชช. ตรวจ |
| `created_at` | TIMESTAMP | |

### 3.2 `questions` ✏️

คำถาม: เลือกตอบ / เขียนบรรยาย / อัปโหลดใบงาน

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `assessment_id` | INT FK → `assessments.id` | |
| `question_type` | ENUM('multiple_choice','essay','file_upload') | ประเภทคำถาม |
| `question_text` | TEXT NOT NULL | เนื้อหาคำถาม |
| `points` | DECIMAL(5,2) DEFAULT 1 | คะแนนเต็ม |
| `grading_mode` | ENUM('auto','manual') | 🆕 auto=เลือกตอบเฉลยทันที, manual=ต้องตรวจ |
| `sort_order` | INT DEFAULT 0 | |

> **กลไกการทำงาน:**  
> - `question_type='multiple_choice'` + `grading_mode='auto'` → เฉลยทันทีเมื่อส่ง  
> - `question_type='essay'` + `grading_mode='manual'` → รอ ผชช. ตรวจ  
> - `question_type='file_upload'` + `grading_mode='manual'` → รอ ผชช. ตรวจ

### 3.3 `question_choices`

ตัวเลือก (สำหรับ multiple_choice)

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `question_id` | BIGINT FK → `questions.id` | |
| `choice_text` | TEXT NOT NULL | เนื้อหาตัวเลือก |
| `is_correct` | BOOLEAN DEFAULT FALSE | คำตอบถูก |
| `sort_order` | INT DEFAULT 0 | |

---

## 4. Learning Progress (3 ตาราง)

### 4.1 `enrollments`

การลงทะเบียนเรียน

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `course_id` | INT FK → `courses.id` | |
| `status` | ENUM('in_progress','completed','certified') | สถานะ |
| `enrolled_at` | TIMESTAMP | |
| `completed_at` | TIMESTAMP NULL | |
| | UNIQUE(`user_id`, `course_id`) | ลงทะเบียน 1 ครั้ง/หลักสูตร |

> enrollment ไม่ผูกกับกลุ่ม — ผู้ใช้ลงทะเบียนได้ทันทีโดยไม่ต้องผ่าน course-level group gate  
> ความคืบหน้าไม่รีเซ็ตเมื่อกลุ่มเปลี่ยน; content ที่เห็นจะอัปเดตตาม `content_group_access` อัตโนมัติ

### 4.2 `module_progress`

ความคืบหน้าราย Module (ต้องจบก่อนเรียนถัดไป)

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `module_id` | INT FK → `modules.id` | |
| `status` | ENUM('locked','in_progress','completed') | |
| `started_at` | TIMESTAMP NULL | |
| `completed_at` | TIMESTAMP NULL | |
| | UNIQUE(`user_id`, `module_id`) | |

> Module ถือว่า `completed` เมื่อ content item ทั้งหมดที่ผู้ใช้มีสิทธิ์เข้าถึง (ตาม `content_group_access`) ถูก complete ครบ  
> เมื่อกลุ่มเปลี่ยนและ content ใหม่ปรากฏ → module status อาจต้องตรวจสอบใหม่ว่ายังครบเงื่อนไขหรือไม่

### 4.3 `content_views`

บันทึกการชมวิดีโอ (เก็บชั่วโมงรับชม)

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `content_id` | INT FK → `module_contents.id` | |
| `watch_duration_sec` | INT DEFAULT 0 | เวลาชมจริง (วินาที) |
| `is_completed` | BOOLEAN DEFAULT FALSE | ชมจบ |
| `last_position_sec` | INT DEFAULT 0 | ตำแหน่งล่าสุด |
| `viewed_at` | TIMESTAMP | |

---

## 5. Test Results & Expert Review (3 ตาราง)

### 5.1 `test_attempts`

การทำแบบทดสอบ (เก็บทุกครั้ง, สูงสุด 3 ครั้ง, ดาวลดตามลำดับ)

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `assessment_id` | INT FK → `assessments.id` | |
| `attempt_number` | INT NOT NULL | ครั้งที่ 1/2/3 |
| `total_score` | DECIMAL(8,2) NULL | คะแนนรวม |
| `max_score` | DECIMAL(8,2) NULL | คะแนนเต็ม |
| `score_pct` | DECIMAL(5,2) NULL | ร้อยละ |
| `star_rating` | INT NULL | ดาว (ลดตามการทำซ้ำ) |
| `status` | ENUM('in_progress','submitted','pending_review','passed','failed','revision_needed') | สถานะ |
| `started_at` | TIMESTAMP | |
| `submitted_at` | TIMESTAMP NULL | |
| `reviewed_by` | BIGINT FK → `users.id` NULL | ผชช. ที่ตรวจ |
| `reviewed_at` | TIMESTAMP NULL | |

### 5.2 `test_answers`

คำตอบรายข้อ

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `attempt_id` | BIGINT FK → `test_attempts.id` | |
| `question_id` | BIGINT FK → `questions.id` | |
| `selected_choice_id` | BIGINT FK → `question_choices.id` NULL | เลือกตอบ |
| `essay_text` | TEXT NULL | เขียนบรรยาย |
| `uploaded_file_url` | VARCHAR(1000) NULL | อัปโหลดใบงาน |
| `score` | DECIMAL(5,2) NULL | คะแนนที่ได้ |
| `is_correct` | BOOLEAN NULL | ถูก/ผิด (auto-grade) |

### 5.3 `expert_reviews`

ผชช. ตรวจใบงาน 3 Modules: รอตรวจ / ผ่าน / รอแก้ไข + ส่งผลทาง email

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `attempt_id` | BIGINT FK → `test_attempts.id` | |
| `expert_id` | BIGINT FK → `users.id` | ผู้เชี่ยวชาญ |
| `status` | ENUM('pending','passed','revision_needed') | รอตรวจ / ผ่าน / รอแก้ไข |
| `score` | DECIMAL(5,2) NULL | คะแนน |
| `feedback` | TEXT NULL | ข้อเสนอแนะ |
| `reviewed_at` | TIMESTAMP NULL | |

---

## 6. Reviews & Certificates (3 ตาราง)

### 6.1 `course_reviews`

รีวิวหลักสูตร: ให้ดาว (บังคับ) + ความคิดเห็น (ไม่บังคับ) → เงื่อนไขรับเกียรติบัตร

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `course_id` | INT FK → `courses.id` | |
| `rating` | TINYINT NOT NULL CHECK(1-5) | 1=น้อยที่สุด → 5=มากที่สุด |
| `comment` | TEXT NULL | ความคิดเห็น (ไม่บังคับ) |
| `created_at` | TIMESTAMP | |
| | UNIQUE(`user_id`, `course_id`) | รีวิว 1 ครั้ง/หลักสูตร |

### 6.2 `certificates`

เกียรติบัตร: ออกอัตโนมัติเมื่อครบเงื่อนไข → ดาวน์โหลด PDF

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | |
| `course_id` | INT FK → `courses.id` | |
| `certificate_number` | VARCHAR(50) UNIQUE | เลขที่เกียรติบัตร |
| `full_name_on_cert` | VARCHAR(255) | ชื่อบนเกียรติบัตร (จาก users) |
| `final_score_pct` | DECIMAL(5,2) | คะแนนรวม % |
| `issued_date` | DATE NOT NULL | วันที่ออก |
| `pdf_url` | VARCHAR(1000) NULL | ไฟล์ PDF |
| | UNIQUE(`user_id`, `course_id`) | 1 ใบ/หลักสูตร |

### 6.3 `certificate_templates`

เทมเพลตเกียรติบัตร (Admin จัดการ)

| Column | Type | Note |
|--------|------|------|
| `id` | INT PK AUTO_INCREMENT | |
| `course_id` | INT FK → `courses.id` | |
| `template_image_url` | VARCHAR(1000) | รูปเทมเพลต |
| `name_x` | INT | ตำแหน่งชื่อ X |
| `name_y` | INT | ตำแหน่งชื่อ Y |
| `date_x` | INT | ตำแหน่งวันที่ X |
| `date_y` | INT | ตำแหน่งวันที่ Y |

---

## 7. Notifications (1 ตาราง)

### 7.1 `notifications`

แจ้งเตือน: ผู้เรียนส่งใบงาน → แจ้ง ผชช. / ผชช.ตรวจเสร็จ → แจ้งผู้เรียน + email

| Column | Type | Note |
|--------|------|------|
| `id` | BIGINT PK AUTO_INCREMENT | |
| `user_id` | BIGINT FK → `users.id` | ผู้รับแจ้ง |
| `type` | ENUM('assignment_submitted','review_completed','revision_needed','certificate_issued') | ประเภท |
| `title` | VARCHAR(500) | |
| `message` | TEXT | |
| `reference_id` | BIGINT NULL | ID อ้างอิง |
| `is_read` | BOOLEAN DEFAULT FALSE | |
| `sent_email` | BOOLEAN DEFAULT FALSE | ส่ง email แล้ว |
| `created_at` | TIMESTAMP | |

---

## Foreign Key Relationships (29+)

```
users.position_id              → positions.id
users.affiliation_id           → affiliations.id
user_group_memberships.user_id → users.id
user_group_memberships.group_id→ learner_groups.id
user_group_memberships.assigned_by → users.id
login_logs.user_id             → users.id
courses.created_by             → users.id
course_instructors.course_id   → courses.id
modules.course_id              → courses.id
module_contents.module_id      → modules.id
content_group_access.content_id→ module_contents.id
content_group_access.group_id  → learner_groups.id
module_prerequisites.module_id → modules.id
module_prerequisites.prerequisite_module_id     → modules.id
module_prerequisites.prerequisite_assessment_id → assessments.id
assessments.course_id          → courses.id
assessments.module_id          → modules.id
questions.assessment_id        → assessments.id
question_choices.question_id   → questions.id
enrollments.user_id            → users.id
enrollments.course_id          → courses.id
module_progress.user_id        → users.id
module_progress.module_id      → modules.id
content_views.user_id          → users.id
content_views.content_id       → module_contents.id
test_attempts.user_id          → users.id
test_attempts.assessment_id    → assessments.id
test_attempts.reviewed_by      → users.id
test_answers.attempt_id        → test_attempts.id
test_answers.question_id       → questions.id
test_answers.selected_choice_id→ question_choices.id
expert_reviews.attempt_id      → test_attempts.id
expert_reviews.expert_id       → users.id
course_reviews.user_id         → users.id
course_reviews.course_id       → courses.id
certificates.user_id           → users.id
certificates.course_id         → courses.id
certificate_templates.course_id→ courses.id
notifications.user_id          → users.id
```

---

## Business Rules

1. **Sequential Learning** — ต้องเรียนจบ 1 Module ก่อนจะเรียน Module ถัดไป (ใช้ `module_prerequisites`)
2. **Prerequisite** — บาง Module ต้องผ่านแบบทดสอบก่อนหน้าก่อน (ใช้ `module_prerequisites.min_score_pct`)
3. **Content-level Access** — สิทธิ์เข้าถึงกำหนดที่ระดับ content item (`content_group_access`); หลักสูตรและ Module มองเห็นได้ทุกคน; ประเมิน ณ request time ต่อกลุ่มปัจจุบัน → เมื่อผู้ใช้เข้ากลุ่มใหม่ content ใหม่ปรากฏทันที; content ที่เคย complete แล้วไม่รีเซ็ต
4. **Test Retry** — แบบทดสอบทำซ้ำได้ 3 ครั้ง ดาวลดตามลำดับ ข้อมูลเก็บทุกครั้ง
5. **Auto + Manual Grading** — เลือกตอบเฉลยทันที / เขียน-อัปโหลดรอตรวจ (ใช้ `grading_mode`)
6. **Expert Review** — ผชช. ตรวจใบงาน 3 Modules (รอตรวจ / ผ่าน / รอแก้ไข)
7. **Review Required** — ต้องรีวิวหลักสูตร (ให้ดาวบังคับ, ความคิดเห็นไม่บังคับ)
8. **Certificate Conditions** — ออกอัตโนมัติเมื่อ: ผ่านทุก module ที่ `is_required=TRUE` + ผ่านทุก assessment ที่ `is_required_for_cert=TRUE` + รีวิวแล้ว + คะแนนรวม ≥ 60%
9. **Notifications** — แจ้งเตือนเมื่อส่งใบงาน / ตรวจเสร็จ / ออกเกียรติบัตร + ส่ง email
10. **Admin Power** — สร้างหลักสูตรใหม่ / จัดการทุกอย่าง / Dashboard สถิติ
11. **Dynamic Content Visibility** — content item ที่ผู้ใช้มองเห็นคำนวณจาก: (1) ไม่มี record ใน `content_group_access` → ทุกคนเห็น (2) มี record → ตรวจ `user_group_memberships` ปัจจุบัน (3) Module prerequisite ยังมีผลปกติ — ต้องผ่าน prerequisite ก่อน content จึง access ได้จริง

---

## เงื่อนไขการออกเกียรติบัตร (Pseudocode)

```sql
-- ตรวจสอบเงื่อนไขทั้งหมดก่อนออกเกียรติบัตร

-- 1. ผ่านทุก Module ที่บังคับ
SELECT COUNT(*) = 0 FROM modules m
  LEFT JOIN module_progress mp ON m.id = mp.module_id AND mp.user_id = ?
  WHERE m.course_id = ? AND m.is_required = TRUE
  AND (mp.status IS NULL OR mp.status != 'completed');

-- 2. ผ่านทุก Assessment ที่บังคับ
SELECT COUNT(*) = 0 FROM assessments a
  WHERE a.course_id = ? AND a.is_required_for_cert = TRUE
  AND NOT EXISTS (
    SELECT 1 FROM test_attempts ta
    WHERE ta.assessment_id = a.id AND ta.user_id = ?
    AND ta.status = 'passed'
  );

-- 3. รีวิวหลักสูตรแล้ว
SELECT COUNT(*) > 0 FROM course_reviews
  WHERE user_id = ? AND course_id = ?;

-- 4. คะแนนรวม ≥ เกณฑ์ผ่าน
-- คำนวณจาก test_attempts ทุกตัวที่ is_required_for_cert
-- ถ้าครบทุกเงื่อนไข → INSERT INTO certificates
```
