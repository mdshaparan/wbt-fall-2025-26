# InternConnectBD- Student Internship Management System

InternConnectBD is a web-based platform designed to bridge the gap between students seeking internships and companies looking for talent in Bangladesh. It provides a centralized dashboard for students to browse opportunities and for companies to manage postings and verify applications.

## 📝 Project Description
This application serves as a comprehensive internship management system. It allows for a multi-user ecosystem consisting of Students, Companies, and Administrators.

Key Features:
- Student Portal: Register, login, and browse available internship categories.
- Company Portal: Post new internship opportunities, manage existing ones, and view applicant details
- Admin Dashboard: Oversight of the platform, including company verification and platform statistics.
- Verification System: A dedicated flow for administrators to verify or reject company registrations to ensure platform quality.
- Dynamic Internship Management: Real-time posting, closing, and updating of internship details.

## 🚀 Tech Stack
1. Frontend: HTML5, CSS3, JavaScript
2. Backend: PHP (Server-side logic and session management)
3. Database: MySQL (Relational data storage)

## ⚙️ Installation & Setup
To run this project locally, follow these steps:

## Clone the Repository:
Bash
git clone https://github.com/mdshaparan/wbt-fall-2025-26.git

## Database Configuration:
- Open your MySQL administration tool (like PHPMyAdmin).
- Create a new database named internconnectbd_db.
- Import the provided MY_SQL file to set up the necessary tables (students_reg, users, company_reg, internships, applications).

## Ensure your db.php file matches your local server credentials:
- PHP
- $servername = "localhost";
- $username = "root";
- $password = "";
- $dbname = "internconnectbd_db";

## Run the Server:
Place the project folder in your local server directory (e.g., htdocs for XAMPP).
Start Apache and MySQL modules.
Navigate to http://localhost/wbt/InternConnectBD/index.php in your browser.

## 💡 How to Use
For Students:
Navigate to the home page to see "Browse By Popular Categories".
Register via register.php and log in via login.php to apply for positions.

For Companies:
Register your company and wait for admin verification.
Once verified, use the dashboard to "Post Internship" with details like duration, skills, and deadlines.

For Admins:
Use the default credentials (found in login.php) to access the admin.php dashboard.
Navigate to company-verification.php to approve or reject pending company sign-ups.

## 📂 File Structure
1. index.php: The landing page showcasing available internships.
2. login.php / logout.php: User authentication and session handling.
3. company.php: Main dashboard for company users.
4. admin.php: Statistical overview for administrators.
5. post_internship.php: Logic for creating new internship entries.
6. manage_internship.php: Logic for updating or closing postings.
