create database if not exists tutor_system;

use tutor_system;


-- Users ko lagi every user in the system must be inserted in this table first 
create table users (
	id int auto_increment primary key,
    name varchar(100),
    email varchar(150),
    password varchar(255),
    phone varchar(20),
    location varchar(100),
    role enum('admin', 'tutor', 'student') default 'student',
    created_at timestamp default current_timestamp
);


-- tutor ko profile taba create hunxa jaba users ma role tutor vanera aauxa 
-- one to one with users
create table tutor_profiles (
	id int auto_increment primary key,
    user_id int unique not null,
    bio text,
    qualification varchar(150),
    experience_years int default 0,
    hourly_rate decimal(10,2) default 0.00,
    status enum('pending', 'approved', 'rejected') not null default 'pending',
    created_at timestamp default current_timestamp,
    foreign key (user_id) references users(id) on delete cascade
);



-- master list of all the subjects which will contain unique subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);



-- eutai subject multiple teacher le padhauna sakxa
CREATE TABLE tutor_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (tutor_id) REFERENCES tutor_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tutor_subject (tutor_id, subject_id)
);


-- student le tutor euta particular subject ko lagi book garxa
CREATE TABLE booking_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME,
    message VARCHAR(255),
    status ENUM('pending', 'accepted', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutor_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);


-- if the booking_request ko status completed vayo vane review dinu parxa 
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking_requests(id) ON DELETE CASCADE
);




-- alerts tutors/ students about booking status change vayexa vane 
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);