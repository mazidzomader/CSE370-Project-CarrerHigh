# <h1 align = "center"> Database Queries </h1>

## 01. Create LOGIN CREDENTIALS table
```sql
CREATE TABLE LOGIN_CREDENTIALS (
    UserID INT PRIMARY KEY,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    FOREIGN KEY (UserID) REFERENCES USER(UserID) ON DELETE CASCADE
);
```
## 02. Main USER table (Superclass)
```sql
CREATE TABLE USER (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    UserType ENUM('Student', 'Mentor') NOT NULL,
    Registration_Date DATE NOT NULL DEFAULT (CURRENT_DATE),
    Status ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active'
);
```

## 03. STUDENT table (Subclass)
```sql
CREATE TABLE STUDENT (
    UserID INT PRIMARY KEY,
    Degree_Programme VARCHAR(100) NOT NULL,
    MentorID INT NULL,
    No_of_Mentor TINYINT NOT NULL DEFAULT 0 CHECK (No_of_Mentor IN (0, 1)),
    FOREIGN KEY (UserID) REFERENCES USER(UserID) ON DELETE CASCADE,
    FOREIGN KEY (MentorID) REFERENCES USER(UserID) ON DELETE SET NULL
);
```
## 04. MENTOR table (Subclass)
```sql
CREATE TABLE MENTOR (
    UserID INT PRIMARY KEY,
    Availability_Schedule TEXT,
    Remuneration DECIMAL(10,2),
    Rating DECIMAL(3,2) CHECK (Rating >= 0 AND Rating <= 5.0),
    FOREIGN KEY (UserID) REFERENCES USER(UserID) ON DELETE CASCADE
);
```
## 05. Mentor's Languages (Multivalued attribute)
```sql
CREATE TABLE MENTOR_LANGUAGES (
    MentorID INT,
    Language VARCHAR(50),
    PRIMARY KEY (MentorID, Language),
    FOREIGN KEY (MentorID) REFERENCES MENTOR(UserID) ON DELETE CASCADE
);
```
## 06. Mentor's Expertise (Multivalued attribute)
```sql
CREATE TABLE `mentor_expertise` (
  `MentorID` int(11) NOT NULL,
  `ExpertiseArea` varchar(100) NOT NULL
);
```
## 07. Junction table for Mentor-Student relationships (Many-to-Many)
```sql
CREATE TABLE MENTOR_STUDENT_RELATIONSHIP (
    MentorID INT,
    StudentID INT,
    Assignment_Date DATE DEFAULT (CURRENT_DATE),
    Status ENUM('Active', 'Completed', 'Terminated') DEFAULT 'Active',
    PRIMARY KEY (MentorID, StudentID),
    FOREIGN KEY (MentorID) REFERENCES MENTOR(UserID) ON DELETE CASCADE,
    FOREIGN KEY (StudentID) REFERENCES STUDENT(UserID) ON DELETE CASCADE
);
```
## 08. Task
```sql
CREATE TABLE IF NOT EXISTS tasks (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('school', 'high-school', 'university', 'bachelor', 'master', 'phd', 'job', 'other') NOT NULL,
    due_date DATE,
    completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```
## 09. Document
```sql
CREATE TABLE IF NOT EXISTS documents (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('academic', 'grade_sheet', 'exam_result', 'competition', 'co_curricular',
                  'achievement', 'research', 'birth_certificate', 'passport', 'bank', 'other') NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    upload_path VARCHAR(500) DEFAULT NULL,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

## 10.Exam
```sql
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Upcoming', 'Completed') DEFAULT 'Upcoming',
    score VARCHAR(10) DEFAULT NULL,
    result VARCHAR(20) DEFAULT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(UserID) ON DELETE CASCADE
);
```
## 11. Research Idea
```sql
CREATE TABLE IF NOT EXISTS research_ideas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('In Progress', 'Completed') DEFAULT 'In Progress',
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(UserID) ON DELETE CASCADE
);
```
## 12. Activities
```sql
CREATE TABLE IF NOT EXISTS activities (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
type VARCHAR(100) NOT NULL,
date DATE NOT NULL,
achievements TEXT,
description TEXT,

user_id INT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES user(UserID) ON DELETE CASCADE
);
```

## 13. Research Collaboration
```sql
CREATE TABLE `research_collaboration` (
  `CollabID` int(11) NOT NULL,
  `Description` text NOT NULL,
  `ProjectName` varchar(200) NOT NULL,
  `Startdate` date NOT NULL,
  `MaxPeople` int(11) NOT NULL,
  `CurrentPeople` int(11) NOT NULL
);
```
## 14. Track Collaboration Participants
```sql
CREATE TABLE `collaboration_participants` (
  `CollabID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
);
```
## 15. University
```sql
CREATE TABLE `university` (
  `ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Funding` decimal(12,2) DEFAULT NULL,
  `admission_Email` varchar(150) DEFAULT NULL,
  `Last_Date` date DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL
);
```