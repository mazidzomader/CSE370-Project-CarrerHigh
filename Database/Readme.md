# <h1 align = "center"> Database Queries </h1>
## 00. Create & Use Database
```sql
CREATE DATABASE Project_database;
USE Project_database;
```

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
CREATE TABLE mentor_expertise (
    MentorID int(11) NOT NULL,
    ExpertiseArea varchar(100) NOT NULL
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
CREATE TABLE research_collaboration (
    CollabID int(11) NOT NULL,
    Description text NOT NULL,
    ProjectName varchar(200) NOT NULL,
    Startdate date NOT NULL,
    MaxPeople int(11) NOT NULL,
    CurrentPeople int(11) NOT NULL
);
```
## 14. Insert data into Collaboration table
```sql
INSERT INTO research_collaboration (CollabID, Description, ProjectName, Startdate, MaxPeople, CurrentPeople) VALUES
(57, 'Developing machine learning algorithms for predictive healthcare analytics using patient data to improve diagnosis accuracy', 'AI-Driven Healthcare Diagnostics', '2024-01-15', 6, 4),
(58, 'Research on sustainable energy solutions using solar panel efficiency optimization techniques', 'Solar Energy Optimization Project', '2024-02-01', 5, 3),
(59, 'Study of blockchain technology applications in supply chain management for transparency', 'Blockchain Supply Chain Analytics', '2024-01-20', 4, 2),
(60, 'Investigation of climate change impacts on agricultural productivity in developing nations', 'Climate-Agriculture Impact Study', '2024-02-15', 8, 6),
(61, 'Development of mobile applications for mental health support and counseling services', 'Mental Health Mobile Platform', '2024-01-10', 5, 5),
(62, 'Research on quantum computing applications in cryptography and data security', 'Quantum Cryptography Research', '2024-03-01', 4, 2),
(63, 'Study of microplastics pollution in marine ecosystems and biodiversity impact', 'Marine Microplastics Study', '2024-02-10', 6, 4),
(64, 'Development of IoT-based smart city infrastructure for traffic management', 'Smart City Traffic Solutions', '2024-01-25', 7, 5),
(65, 'Research on gene therapy applications for rare genetic disorders treatment', 'Gene Therapy Innovation Lab', '2024-03-15', 5, 3),
(66, 'Investigation of renewable energy storage systems using advanced battery technology', 'Advanced Battery Storage Research', '2024-02-20', 6, 4),
(67, 'Study of social media impact on mental health among college students', 'Social Media Psychology Study', '2024-01-30', 4, 1),
(68, 'Development of virtual reality applications for educational and training purposes', 'VR Education Platform', '2024-03-05', 5, 2),
(69, 'Research on artificial intelligence ethics and bias detection in automated systems', 'AI Ethics and Bias Research', '2024-02-25', 4, 3),
(70, 'Investigation of water purification technologies for developing countries', 'Water Purification Innovation', '2024-01-18', 6, 4),
(71, 'Study of cybersecurity threats in financial institutions and prevention strategies', 'FinTech Security Research', '2024-03-10', 5, 3),
(72, 'Development of sustainable packaging materials from agricultural waste', 'Eco-Friendly Packaging Solutions', '2024-02-05', 4, 4),
(73, 'Research on autonomous vehicle navigation systems and safety protocols', 'Autonomous Vehicle Safety', '2024-01-22', 7, 6),
(74, 'Investigation of personalized medicine approaches using genomic data analysis', 'Personalized Medicine Study', '2024-03-20', 5, 3),
(75, 'Study of urban air pollution monitoring using sensor networks', 'Urban Air Quality Monitoring', '2024-02-12', 6, 4),
(76, 'Development of robotic systems for elderly care and assistance', 'Eldercare Robotics Project', '2024-01-28', 5, 3),
(77, 'Research on food security and sustainable farming techniques', 'Sustainable Agriculture Research', '2024-03-25', 8, 6),
(78, 'Investigation of 5G technology impact on healthcare telemedicine applications', '5G Healthcare Applications', '2024-02-18', 4, 2),
(79, 'Study of coral reef restoration techniques using biotechnology', 'Coral Reef Restoration Lab', '2024-01-12', 6, 4),
(80, 'Development of predictive analytics for natural disaster early warning systems', 'Disaster Prediction Systems', '2024-03-08', 7, 5),
(81, 'Research on space exploration technologies and Mars colonization feasibility', 'Mars Colonization Study', '2024-02-28', 5, 3),
(82, 'Investigation of biofuel production from algae and renewable resources', 'Algae Biofuel Research', '2024-01-16', 4, 4),
(83, 'Study of machine learning applications in financial fraud detection', 'FinTech Fraud Detection', '2024-03-12', 5, 3),
(84, 'Development of wearable health monitoring devices for chronic disease management', 'Wearable Health Tech', '2024-02-22', 6, 4),
(85, 'Research on educational technology effectiveness in online learning environments', 'EdTech Effectiveness Study', '2024-01-26', 4, 4),
(86, 'Investigation of clean water access solutions for rural communities', 'Rural Water Access Project', '2024-03-18', 7, 5),
(87, 'Study of renewable energy integration in smart grid systems', 'Smart Grid Integration', '2024-02-08', 5, 3),
(88, 'Development of AI-powered language translation tools for global communication', 'AI Translation Platform', '2024-01-14', 4, 2),
(89, 'Research on carbon capture and storage technologies for climate change mitigation', 'Carbon Capture Research', '2024-03-22', 6, 4),
(90, 'Investigation of digital divide impacts on educational equality', 'Digital Education Equity', '2024-02-16', 4, 3),
(91, 'Study of antimicrobial resistance in hospital environments', 'Hospital Antimicrobial Study', '2024-01-20', 5, 4),
(92, 'Development of drone technology for environmental monitoring and conservation', 'Conservation Drone Project', '2024-03-06', 6, 4),
(93, 'Research on virtual therapy applications for PTSD treatment', 'Virtual PTSD Therapy', '2024-02-14', 4, 3),
(94, 'Investigation of sustainable transportation systems in urban environments', 'Urban Transport Sustainability', '2024-01-24', 7, 5),
(95, 'Study of brain-computer interfaces for assistive technology development', 'BCI Assistive Technology', '2024-03-16', 5, 3),
(96, 'Development of precision agriculture using satellite imagery and AI', 'Precision Agriculture AI', '2024-02-26', 6, 4),
(97, 'Research on cybersecurity in Internet of Things (IoT) devices', 'IoT Security Research', '2024-01-08', 4, 4),
(98, 'Investigation of telehealth effectiveness in rural healthcare delivery', 'Rural Telehealth Study', '2024-03-14', 5, 3),
(99, 'Study of social robot interactions with children with autism', 'Autism Social Robotics', '2024-02-04', 4, 2),
(100, 'Development of biodegradable electronics for environmental sustainability', 'Biodegradable Electronics', '2024-01-19', 5, 4),
(101, 'Research on artificial photosynthesis for renewable energy production', 'Artificial Photosynthesis Lab', '2024-03-24', 6, 5),
(102, 'Investigation of mental health support systems in academic institutions', 'Academic Mental Health Study', '2024-02-11', 4, 4),
(103, 'Study of 3D printing applications in medical device manufacturing', '3D Medical Device Printing', '2024-01-17', 5, 5),
(104, 'Development of smart home systems for energy conservation', 'Smart Home Energy Systems', '2024-03-04', 6, 4),
(105, 'Research on facial recognition technology ethics and privacy concerns', 'Facial Recognition Ethics', '2024-02-23', 4, 2),
(106, 'Investigation of vertical farming techniques for urban food production', 'Urban Vertical Farming', '2024-01-13', 7, 5),
(107, 'Study of cryptocurrency environmental impact and sustainable alternatives', 'Sustainable Cryptocurrency', '2024-03-26', 5, 3),
(108, 'Development of augmented reality tools for medical education and training', 'AR Medical Training', '2024-02-07', 4, 4),
(109, 'Research on ocean plastic cleanup technologies and marine conservation', 'Ocean Cleanup Technology', '2024-01-21', 6, 4),
(110, 'Investigation of artificial intelligence in creative arts and music composition', 'AI Creative Arts Study', '2024-03-11', 4, 2),
(111, 'Study of indoor air quality improvement using smart ventilation systems', 'Smart Ventilation Research', '2024-02-19', 5, 3),
(112, 'Development of peer-to-peer energy trading platforms using blockchain', 'P2P Energy Trading Platform', '2024-01-29', 6, 5);
```
## 15. Track Collaboration Participants
```sql
CREATE TABLE collaboration_participants (
  CollabID int(11) NOT NULL,
  UserID int(11) NOT NULL
);
```
## 16. University
```sql
CREATE TABLE `university` (
  ID int(11) NOT NULL,
  Name varchar(100) DEFAULT NULL,
  Department varchar(100) DEFAULT NULL,
  Funding decimal(12,2) DEFAULT NULL,
  admission_Email varchar(150) DEFAULT NULL,
  Last_Date date DEFAULT NULL,
  Country varchar(100) DEFAULT NULL
);
```